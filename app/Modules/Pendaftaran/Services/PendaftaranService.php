<?php

namespace App\Modules\Pendaftaran\Services;

use App\Modules\MasterData\Models\PeriodeModel;
use App\Modules\Notifikasi\Services\NotifikasiService;
use App\Modules\Pendaftaran\Models\DataDiriSiswaModel;
use App\Modules\Pendaftaran\Models\DokumenModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;

class PendaftaranService
{
    protected PendaftaranModel   $pendaftaranModel;
    protected DataDiriSiswaModel $dataDiriModel;
    protected DokumenModel       $dokumenModel;
    protected PeriodeModel       $periodeModel;
    protected NotifikasiService  $notifService;

    /** Cache field names agar tidak query berulang kali */
    private ?array $dataDiriFields = null;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dataDiriModel    = new DataDiriSiswaModel();
        $this->dokumenModel     = new DokumenModel();
        $this->periodeModel     = new PeriodeModel();
        $this->notifService     = new NotifikasiService();
    }

    public function getOrCreate(int $userId): object
    {
        $existing = $this->pendaftaranModel->getByUserId($userId);
        if ($existing) return $existing;

        $periode = $this->periodeModel->getPeriodeAktif();

        $id = $this->pendaftaranModel->insert([
            'user_id'       => $userId,
            'periode_id'    => $periode ? $periode->id : null,
            'status'        => 'draft',
            'step_terakhir' => 1,
        ]);

        $result = $this->pendaftaranModel->find($id);
        if (! is_object($result)) {
            throw new \RuntimeException('Gagal membuat pendaftaran baru.');
        }
        return $result;
    }

    // =========================================================
    // HELPER: filter field yang benar-benar ada di DB
    // =========================================================

    /**
     * Filter array data agar hanya berisi key yang kolom-nya
     * ada di tabel data_diri_siswas.
     * Mencegah error "Unknown column" jika ada field baru yang belum di-migrate.
     */
    private function filterExistingFields(array $data): array
    {
        if ($this->dataDiriFields === null) {
            $this->dataDiriFields = \Config\Database::connect()
                ->getFieldNames('data_diri_siswas');
        }

        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $this->dataDiriFields)) {
                $filtered[$key] = $value;
            } else {
                log_message('warning', "PendaftaranService: kolom `{$key}` tidak ada di tabel data_diri_siswas — dilewati. Jalankan: php spark migrate");
            }
        }
        return $filtered;
    }

    // =========================================================
    // STEP 1 — Data Pribadi Siswa
    // =========================================================

    public function saveStep1(int $pendaftaranId, array $data): array
    {
        try {
            $payload = $this->filterExistingFields([
                'nik'             => $data['nik']             ?? null,
                'nisn'            => $data['nisn']            ?? null,
                'nama_lengkap'    => $data['nama_lengkap'],
                'nama_panggilan'  => $data['nama_panggilan']  ?? null,
                'jenis_kelamin'   => $data['jenis_kelamin'],
                'tempat_lahir'    => $data['tempat_lahir'],
                'tanggal_lahir'   => $data['tanggal_lahir'],
                'agama'           => $data['agama'],
                'kewarganegaraan' => $data['kewarganegaraan'] ?? 'WNI',
                'status_anak'     => $data['status_anak']     ?? null,
                'anak_ke'         => $data['anak_ke']         ?? null,
                'jumlah_saudara'  => $data['jumlah_saudara']  ?? null,
                'alamat'          => $data['alamat'],
                'dusun'           => $data['dusun']           ?? null,
                'rt'              => $data['rt']              ?? null,
                'rw'              => $data['rw']              ?? null,
                'kelurahan'       => $data['kelurahan']       ?? null,
                'kecamatan'       => $data['kecamatan']       ?? null,
                'kabupaten'       => $data['kabupaten']       ?? null,
                'provinsi'        => $data['provinsi']        ?? null,
                'kode_pos'        => $data['kode_pos']        ?? null,
                'no_hp'           => $data['no_hp'],
                'email_siswa'     => $data['email_siswa']     ?? null,
                'asal_sekolah'    => $data['asal_sekolah']    ?? null,
                'alamat_sekolah'  => $data['alamat_sekolah']  ?? null,
                'tahun_lulus'     => $data['tahun_lulus']     ?? null,
            ]);

            if (empty($payload)) {
                return ['success' => false, 'message' => 'Tidak ada data yang dapat disimpan. Pastikan migration sudah dijalankan (php spark migrate).'];
            }

            $this->dataDiriModel->upsert($pendaftaranId, $payload);

            $current = $this->pendaftaranModel->find($pendaftaranId);
            $this->pendaftaranModel->update($pendaftaranId, [
                'step_terakhir' => max(2, $current->step_terakhir ?? 1),
            ]);

            $this->pendaftaranModel->saveDraft($pendaftaranId, $data, 1);

            return ['success' => true];
        } catch (\Exception $e) {
            log_message('error', 'PendaftaranService::saveStep1 - ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal menyimpan data pribadi: ' . $e->getMessage()];
        }
    }

    // =========================================================
    // STEP 2 — Pilihan Jurusan
    // =========================================================

    public function saveStep2(int $pendaftaranId, array $data): array
    {
        try {
            if (
                ! empty($data['jurusan_pilihan2_id']) &&
                ($data['jurusan_pilihan1_id'] ?? '') === ($data['jurusan_pilihan2_id'] ?? '')
            ) {
                return ['success' => false, 'message' => 'Pilihan jurusan 1 dan 2 tidak boleh sama.'];
            }

            $current = $this->pendaftaranModel->find($pendaftaranId);
            $this->pendaftaranModel->update($pendaftaranId, [
                'jurusan_pilihan1_id' => $data['jurusan_pilihan1_id'] ?? null,
                'jurusan_pilihan2_id' => $data['jurusan_pilihan2_id'] ?? null,
                'step_terakhir'       => max(3, $current->step_terakhir ?? 2),
            ]);
            $this->pendaftaranModel->saveDraft($pendaftaranId, $data, 2);
            return ['success' => true];
        } catch (\Exception $e) {
            log_message('error', 'PendaftaranService::saveStep2 - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    // STEP 3 — Data Orang Tua / Wali
    // =========================================================

    public function saveStep3(int $pendaftaranId, array $data): array
    {
        try {
            $payload = $this->filterExistingFields([
                'nama_ayah'        => $data['nama_ayah']        ?? null,
                'pekerjaan_ayah'   => $data['pekerjaan_ayah']   ?? null,
                'pendidikan_ayah'  => $data['pendidikan_ayah']  ?? null,
                'penghasilan_ayah' => $data['penghasilan_ayah'] ?? null,
                'nama_ibu'         => $data['nama_ibu']         ?? null,
                'pekerjaan_ibu'    => $data['pekerjaan_ibu']    ?? null,
                'pendidikan_ibu'   => $data['pendidikan_ibu']   ?? null,
                'penghasilan_ibu'  => $data['penghasilan_ibu']  ?? null,
                'no_hp_ortu'       => $data['no_hp_ortu']       ?? null,
                'no_hp_ibu'        => $data['no_hp_ibu']        ?? null,
                'nama_wali'        => $data['nama_wali']        ?? null,
                'no_hp_wali'       => $data['no_hp_wali']       ?? null,
            ]);

            $this->dataDiriModel->upsert($pendaftaranId, $payload);

            $current = $this->pendaftaranModel->find($pendaftaranId);
            $this->pendaftaranModel->update($pendaftaranId, [
                'step_terakhir' => max(4, $current->step_terakhir ?? 3),
            ]);
            $this->pendaftaranModel->saveDraft($pendaftaranId, $data, 3);
            return ['success' => true];
        } catch (\Exception $e) {
            log_message('error', 'PendaftaranService::saveStep3 - ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================
    // SUBMIT dari Step 4 — Entry point dari controller
    // =========================================================

    public function submitDariStep4(int $pendaftaranId, int $userId): array
    {
        // 1. Cek data diri (Step 1) sudah tersimpan
        $dataDiri = $this->dataDiriModel->getByPendaftaranId($pendaftaranId);
        if (! $dataDiri || empty($dataDiri->nama_lengkap)) {
            return [
                'success' => false,
                'message' => 'Data pribadi (Step 1) belum tersimpan dengan benar. '
                    . 'Kembali ke Step 1 → isi ulang data → klik "Simpan & Lanjut".',
            ];
        }

        // 2. Cek jurusan (Step 2) sudah dipilih
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
        if (! $pendaftaran->jurusan_pilihan1_id) {
            return [
                'success' => false,
                'message' => 'Pilihan jurusan (Step 2) belum tersimpan. '
                    . 'Kembali ke Step 2 → pilih jurusan → klik "Simpan & Lanjut".',
            ];
        }

        // 3. Cek dokumen wajib
        $missingDocs = $this->dokumenModel->getMissing($pendaftaranId);
        if (! empty($missingDocs)) {
            $labels = array_map('jenis_dokumen_label', $missingDocs);
            return [
                'success' => false,
                'message' => 'Dokumen wajib belum lengkap: ' . implode(', ', $labels),
            ];
        }

        return $this->submit($pendaftaranId, $userId);
    }

    // =========================================================
    // SUBMIT — Proses akhir
    // =========================================================

    public function submit(int $pendaftaranId, int $userId): array
    {
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);

        if (! $pendaftaran) {
            return ['success' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        if ($pendaftaran->status !== 'draft' && $pendaftaran->status !== 'revisi') {
            return ['success' => false, 'message' => 'Formulir sudah pernah disubmit.'];
        }

        $dataDiri = $this->dataDiriModel->getByPendaftaranId($pendaftaranId);
        if (! $dataDiri || empty($dataDiri->nama_lengkap)) {
            return [
                'success' => false,
                'message' => 'Data diri (Step 1) belum diisi. Silakan kembali ke Step 1 dan simpan data terlebih dahulu.',
            ];
        }

        if (! $this->dokumenModel->isComplete($pendaftaranId)) {
            return [
                'success' => false,
                'message' => 'Dokumen wajib belum lengkap. Silakan upload semua dokumen yang diperlukan.',
            ];
        }

        if (! $pendaftaran->jurusan_pilihan1_id) {
            return [
                'success' => false,
                'message' => 'Pilihan jurusan (Step 2) belum ditentukan. Silakan kembali ke Step 2.',
            ];
        }

        // Proses submit
        $this->pendaftaranModel->submitPendaftaran($pendaftaranId);
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);

        // Kirim notifikasi — wrap try/catch agar error notifikasi
        // tidak membatalkan proses submit yang sudah berhasil
        try {
            $this->notifService->notifikasiKeAdmin(
                'formulir_baru',
                'Formulir Baru Masuk',
                "Pendaftaran {$pendaftaran->no_pendaftaran} menunggu verifikasi.",
                ['pendaftaran_id' => $pendaftaranId, 'url' => base_url("admin/verifikasi/{$pendaftaranId}")]
            );
        } catch (\Throwable $e) {
            log_message('warning', 'PendaftaranService::submit - notif admin gagal: ' . $e->getMessage());
        }

        try {
            $this->notifService->send(
                $userId,
                'formulir_submitted',
                'Formulir Berhasil Dikirim',
                "Formulir Anda dengan nomor {$pendaftaran->no_pendaftaran} telah dikirim dan sedang dalam proses verifikasi.",
                ['url' => base_url('dashboard/status')]
            );
        } catch (\Throwable $e) {
            log_message('warning', 'PendaftaranService::submit - notif user gagal: ' . $e->getMessage());
        }

        return [
            'success'        => true,
            'no_pendaftaran' => $pendaftaran->no_pendaftaran,
            'message'        => 'Formulir berhasil dikirim! Nomor pendaftaran Anda: ' . $pendaftaran->no_pendaftaran,
        ];
    }
}
