<?php

namespace App\Modules\BukuInduk\Services;

use App\Modules\BukuInduk\Models\BukuIndukModel;
use App\Modules\BukuInduk\Libraries\NISGenerator;
use App\Modules\DaftarUlang\Models\DaftarUlangModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Models\DataDiriSiswaModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\Notifikasi\Services\NotifikasiService;

class BukuIndukService
{
    protected BukuIndukModel     $bukuIndukModel;
    protected DaftarUlangModel   $daftarUlangModel;
    protected PendaftaranModel   $pendaftaranModel;
    protected DataDiriSiswaModel $dataDiriModel;
    protected JurusanModel       $jurusanModel;
    protected NISGenerator       $nisGenerator;
    protected NotifikasiService  $notifService;

    public function __construct()
    {
        $this->bukuIndukModel   = new BukuIndukModel();
        $this->daftarUlangModel = new DaftarUlangModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dataDiriModel    = new DataDiriSiswaModel();
        $this->jurusanModel     = new JurusanModel();
        $this->nisGenerator     = new NISGenerator();
        $this->notifService     = new NotifikasiService();
    }

    /**
     * Konversi satu pendaftaran ke Buku Induk.
     *
     * BUGS YANG DIPERBAIKI:
     *
     * BUG 1 — Nested transaction menyebabkan deadlock/race condition:
     *   NISGenerator::generate() memanggil transStart()/transComplete() sendiri,
     *   lalu service ini juga membungkus semuanya dengan transStart()/transComplete().
     *   MySQL tidak mendukung real nested transaction via MySQLi — inner COMMIT/ROLLBACK
     *   berpengaruh ke outer transaction secara tidak terduga.
     *   FIX: Generate NIS SEBELUM outer transaction dimulai. NISGenerator tetap punya
     *   lock-nya sendiri yang selesai sebelum outer trans mulai.
     *
     * BUG 2 — Mixed transaction pattern (transStart/Complete + manual try/catch/transRollback):
     *   CI4 transStart/Complete mengatur auto-rollback via transStatus() sendiri,
     *   tapi kode juga memanggil transRollback() secara manual di catch.
     *   FIX: Gunakan manual $db->query('START TRANSACTION') / commit / rollback saja,
     *   tanpa CI4 transStart/Complete, agar kontrol penuh ada di tangan kita.
     *
     * BUG 3 — DaftarUlang NIS tidak divalidasi uniqueness-nya saat konfirmasi admin.
     *   (Fix ada di DaftarUlangAdminController::konfirmasi())
     *   Di sini: tambahkan double-check NIS uniqueness sebelum insert sebagai safety net.
     */
    public function konversi(int $pendaftaranId, int $adminId, ?int $kelasId = null): array
    {
        // ── 1. Validasi pendaftaran ──────────────────────────────────────────
        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaranId);

        if (! $pendaftaran) {
            return ['success' => false, 'message' => 'Pendaftaran tidak ditemukan.'];
        }

        if (! in_array($pendaftaran->status, ['daftar_ulang', 'siswa_aktif'])) {
            return [
                'success' => false,
                'message' => "Status pendaftaran harus 'daftar_ulang'. Status saat ini: {$pendaftaran->status}. "
                    . "Pastikan siswa sudah submit dan admin sudah konfirmasi daftar ulang.",
            ];
        }

        // Cek apakah sudah pernah dikonversi
        if ($this->bukuIndukModel->getByPendaftaranId($pendaftaranId)) {
            return ['success' => false, 'message' => 'Pendaftaran ini sudah dikonversi ke buku induk.'];
        }

        // ── 2. Validasi daftar ulang — wajib sudah dikonfirmasi ─────────────
        $daftarUlang = $this->daftarUlangModel->getByPendaftaranId($pendaftaranId);

        if (! $daftarUlang) {
            return [
                'success' => false,
                'message' => 'Siswa belum mengajukan daftar ulang. Minta siswa untuk mengupload bukti pembayaran terlebih dahulu.',
            ];
        }

        if ($daftarUlang->status !== DaftarUlangModel::STATUS_DIKONFIRMASI) {
            $statusLabel = match ($daftarUlang->status) {
                'pending' => 'menunggu konfirmasi admin',
                'ditolak' => 'ditolak (perlu upload ulang)',
                default   => $daftarUlang->status,
            };
            return [
                'success' => false,
                'message' => "Daftar ulang siswa ini masih {$statusLabel}. Konfirmasi pembayaran di menu Daftar Ulang terlebih dahulu.",
            ];
        }

        // ── 3. Validasi data diri ────────────────────────────────────────────
        $dataDiri = $this->dataDiriModel->getByPendaftaranId($pendaftaranId);

        if (! $dataDiri || ! $dataDiri->nama_lengkap) {
            return ['success' => false, 'message' => 'Data diri tidak lengkap. Tidak dapat dikonversi.'];
        }

        // ── 4. Validasi jurusan ──────────────────────────────────────────────
        $jurusanId = $pendaftaran->jurusan_diterima_id ?? $pendaftaran->jurusan_pilihan1_id;
        $jurusan   = $this->jurusanModel->find($jurusanId);

        if (! $jurusan) {
            return ['success' => false, 'message' => 'Data jurusan tidak valid.'];
        }

        $resolvedKelasId = $kelasId ?? $daftarUlang->kelas_id ?? null;
        $tahunMasuk      = date('Y');

        // ── 5. Generate / ambil NIS SEBELUM transaction dimulai ─────────────
        // FIX BUG 1: NIS di-resolve di luar outer transaction agar tidak ada
        // nested transaction. NISGenerator punya lock-nya sendiri yang atomik.
        $nisFromDaftarUlang = $daftarUlang->nis ?? null;

        try {
            if ($nisFromDaftarUlang) {
                $nis = $nisFromDaftarUlang;
            } else {
                $nis = $this->nisGenerator->generate($jurusan->kode_nis, $tahunMasuk);
            }
        } catch (\Exception $e) {
            log_message('error', 'BukuIndukService::konversi - NIS generation gagal: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal generate NIS: ' . $e->getMessage()];
        }

        // ── 6. Safety net: double-check NIS uniqueness sebelum insert ────────
        // Mencegah duplicate key error jika admin salah input NIS yang sama
        // untuk dua siswa berbeda (validasi utama ada di DaftarUlangAdminController).
        $db = db_connect();
        $nisAlreadyExist = $db->table('buku_induks')->where('nis', $nis)->countAllResults();
        if ($nisAlreadyExist > 0) {
            return [
                'success' => false,
                'message' => "NIS '{$nis}' sudah digunakan siswa lain di Buku Induk. "
                    . "Buka menu Daftar Ulang, edit konfirmasi siswa ini, dan ganti NIS-nya.",
            ];
        }

        // ── 7. Simpan ke DB dalam satu transaction ───────────────────────────
        // FIX BUG 2: Gunakan raw query START TRANSACTION / COMMIT / ROLLBACK
        // agar tidak ada konflik dengan CI4 transStart/Complete pattern.
        try {
            $db->query('START TRANSACTION');

            $inserted = $db->table('buku_induks')->insert([
                'pendaftaran_id' => $pendaftaranId,
                'user_id'        => $pendaftaran->user_id,
                'kelas_id'       => $resolvedKelasId,
                'jurusan_id'     => $jurusanId,
                'nis'            => $nis,
                // ── Identitas ──────────────────────────────────────────────
                'nik'            => $dataDiri->nik            ?? null,
                'nisn'           => $dataDiri->nisn           ?? null,
                'nama_lengkap'   => $dataDiri->nama_lengkap,
                'nama_panggilan' => $dataDiri->nama_panggilan ?? null,
                'jenis_kelamin'  => $dataDiri->jenis_kelamin  ?? null,
                'tempat_lahir'   => $dataDiri->tempat_lahir   ?? null,
                'tanggal_lahir'  => $dataDiri->tanggal_lahir  ?? null,
                'agama'          => $dataDiri->agama          ?? null,
                'kewarganegaraan' => $dataDiri->kewarganegaraan ?? 'Indonesia',
                // ── Alamat & Kontak ────────────────────────────────────────
                'alamat'         => $dataDiri->alamat         ?? null,
                'no_hp'          => $dataDiri->no_hp          ?? null,
                'email_siswa'    => $dataDiri->email_siswa    ?? null,
                // ── Orang Tua ──────────────────────────────────────────────
                'nama_ayah'      => $dataDiri->nama_ayah      ?? null,
                'pekerjaan_ayah' => $dataDiri->pekerjaan_ayah ?? null,
                'no_hp_ayah'     => $dataDiri->no_hp_ortu     ?? null, // no_hp_ortu = no HP ayah di form pendaftaran
                'nama_ibu'       => $dataDiri->nama_ibu       ?? null,
                'pekerjaan_ibu'  => $dataDiri->pekerjaan_ibu  ?? null,
                'no_hp_ibu'      => $dataDiri->no_hp_ibu      ?? null,
                'no_hp_ortu'     => $dataDiri->no_hp_ortu     ?? null,
                // ── Pendidikan Asal ────────────────────────────────────────
                'asal_sekolah'   => $dataDiri->asal_sekolah   ?? null,
                'tahun_lulus_smp' => $dataDiri->tahun_lulus     ?? null, // di data_diri_siswas nama kolom = tahun_lulus
                // ── Meta ───────────────────────────────────────────────────
                'tahun_masuk'    => $tahunMasuk,
                'status_siswa'   => 'aktif',
                'converted_at'   => date('Y-m-d H:i:s'),
                'converted_by'   => $adminId,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            if (! $inserted) {
                throw new \RuntimeException('Query insert buku_induks tidak mengembalikan hasil.');
            }

            // Update status pendaftaran → siswa_aktif
            $updated = $db->table('pendaftaran')
                ->where('id', $pendaftaranId)
                ->update(['status' => 'siswa_aktif', 'updated_at' => date('Y-m-d H:i:s')]);

            if (! $updated) {
                throw new \RuntimeException('Gagal update status pendaftaran ke siswa_aktif.');
            }

            $db->query('COMMIT');
        } catch (\Exception $e) {
            $db->query('ROLLBACK');
            log_message('error', 'BukuIndukService::konversi - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal konversi: ' . $e->getMessage()];
        }

        // ── 8. Notifikasi (di luar transaction) ─────────────────────────────
        try {
            $this->notifService->send(
                $pendaftaran->user_id,
                'buku_induk_dibuat',
                'Selamat! Anda Resmi Menjadi Siswa Aktif',
                "NIS Anda: {$nis}. Anda telah terdaftar sebagai siswa aktif SMK Al-Munawwir IIBS.",
                ['url' => base_url('dashboard/info-siswa-baru')]
            );
        } catch (\Throwable $e) {
            log_message('warning', 'BukuIndukService::konversi - notif gagal (diabaikan): ' . $e->getMessage());
        }

        return [
            'success' => true,
            'nis'     => $nis,
            'message' => "Konversi berhasil! NIS: {$nis}",
        ];
    }

    /**
     * Konversi massal — semua yang sudah dikonfirmasi daftar ulang.
     */
    public function konversiBulk(int $adminId, ?int $kelasId = null): array
    {
        $pendaftarans = $this->pendaftaranModel
            ->where('status', 'daftar_ulang')
            ->findAll();

        $sukses = 0;
        $gagal  = 0;
        $skip   = 0;
        $errors = [];

        foreach ($pendaftarans as $p) {
            if ($this->bukuIndukModel->getByPendaftaranId($p->id)) {
                $skip++;
                continue;
            }

            $du = $this->daftarUlangModel->getByPendaftaranId($p->id);
            if (! $du || $du->status !== DaftarUlangModel::STATUS_DIKONFIRMASI) {
                $skip++;
                continue;
            }

            $result = $this->konversi($p->id, $adminId, $kelasId);

            if ($result['success']) {
                $sukses++;
            } else {
                $gagal++;
                $errors[] = "Pendaftaran #{$p->id}: " . $result['message'];
            }
        }

        return [
            'success' => true,
            'sukses'  => $sukses,
            'gagal'   => $gagal,
            'skip'    => $skip,
            'errors'  => $errors,
            'message' => "{$sukses} siswa berhasil dikonversi, {$gagal} gagal, {$skip} dilewati.",
        ];
    }
}
