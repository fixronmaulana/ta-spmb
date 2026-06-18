<?php

namespace App\Modules\Seleksi\Models;

use CodeIgniter\Model;

class SeleksiModel extends Model
{
    protected $table      = 'pendaftaran';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    /**
     * Status yang TIDAK BOLEH diubah lagi melalui aksi penetapan kelulusan
     * (Lulus/Tolak), baik individu maupun bulk. Begitu siswa sudah masuk
     * status ini, siklus seleksi untuknya sudah selesai dan dikunci —
     * perubahan jurusan/kelas/keputusan lanjut harus melalui menu
     * Daftar Ulang atau Konversi Buku Induk, bukan dari sini.
     */
    public const LOCKED_STATUSES = ['daftar_ulang', 'siswa_aktif'];

    /**
     * Status yang MASIH BOLEH diubah lewat aksi Lulus/Tolak.
     */
    public const EDITABLE_STATUSES = ['seleksi', 'lulus', 'tidak_lulus'];

    /**
     * Ambil semua peserta yang sudah masuk tahap seleksi/lulus/tidak_lulus.
     * Sertakan data jurusan pilihan 1, pilihan 2, dan jurusan diterima.
     *
     * PERBAIKAN: tambahkan status 'daftar_ulang' dan 'siswa_aktif' agar siswa
     * yang sudah lanjut ke tahap daftar ulang tetap tampil di halaman seleksi
     * (dengan badge status yang sesuai), bukan hilang dari daftar.
     */
    public function getForSeleksiByJurusan(): array
    {
        $rows = $this->select('
                pendaftaran.id,
                pendaftaran.status,
                pendaftaran.user_id,
                pendaftaran.jurusan_pilihan1_id,
                pendaftaran.jurusan_pilihan2_id,
                pendaftaran.jurusan_diterima_id,
                pendaftaran.no_pendaftaran,
                dds.nama_lengkap,
                dds.nisn,
                dds.asal_sekolah,
                dds.jenis_kelamin,
                dds.tanggal_lahir,
                j1.nama  AS jurusan_pilihan1_nama,
                j1.kode  AS jurusan_pilihan1_kode,
                j1.kuota AS kuota1,
                j1.id    AS j1_id,
                j2.nama  AS jurusan_pilihan2_nama,
                j2.kode  AS jurusan_pilihan2_kode,
                j2.id    AS j2_id,
                jd.nama  AS jurusan_diterima_nama,
                jd.kode  AS jurusan_diterima_kode,
                u.email  AS email_calon
            ')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('users u',              'u.id = pendaftaran.user_id')
            ->join('jurusan j1',           'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2',           'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->join('jurusan jd',           'jd.id = pendaftaran.jurusan_diterima_id', 'left')
            ->whereIn('pendaftaran.status', ['seleksi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif'])
            ->orderBy('pendaftaran.created_at', 'ASC')
            ->findAll();

        return $rows;
    }

    /**
     * Tetapkan lulus/tidak lulus.
     *
     * PERBAIKAN:
     * - Setiap siswa yang lulus WAJIB punya jurusan_diterima_id (dipilih admin per-siswa).
     * - jurusan_diterima_id disimpan ke DB → muncul di halaman daftar-ulang.
     * - Kuota jurusan berkurang secara real-time karena dihitung dari DB (bukan field statis).
     *
     * GUARD KEAMANAN (PENTING):
     * - Update HANYA menyasar baris yang status-nya masih ada di EDITABLE_STATUSES
     *   ('seleksi', 'lulus', 'tidak_lulus'). Ditambahkan kondisi
     *   `whereIn('status', EDITABLE_STATUSES)` langsung di query UPDATE.
     * - Ini mencegah siswa yang sudah 'daftar_ulang' atau 'siswa_aktif' diubah
     *   statusnya lagi, BAHKAN JIKA id-nya dikirim secara manual lewat
     *   request POST (misalnya lewat curl/devtools) tanpa lewat UI.
     * - Hasil mengembalikan detail: berapa baris benar-benar berhasil diupdate
     *   vs berapa yang ditolak karena sudah terkunci, supaya Controller bisa
     *   memberi pesan yang akurat ke admin.
     *
     * @param  array $lulusIds        [pendaftaran_id, ...]
     * @param  array $tidakLulusIds   [pendaftaran_id, ...]
     * @param  array $jurusanDiterimaMap  [pendaftaran_id => jurusan_id]
     * @return array{success: bool, lulus_updated: int, lulus_locked: int, tidak_lulus_updated: int, tidak_lulus_locked: int}
     */
    public function tetapkanLulus(array $lulusIds, array $tidakLulusIds, array $jurusanDiterimaMap): array
    {
        $db = db_connect();

        // Cek dulu mana saja id yang statusnya sudah terkunci (daftar_ulang/siswa_aktif),
        // supaya bisa dilaporkan ke admin meskipun query update sudah otomatis melewatkannya.
        $allIds = array_unique(array_merge($lulusIds, $tidakLulusIds));
        $lockedIds = [];
        if (! empty($allIds)) {
            $lockedRows = $db->table('pendaftaran')
                ->select('id')
                ->whereIn('id', $allIds)
                ->whereIn('status', self::LOCKED_STATUSES)
                ->get()
                ->getResultArray();
            $lockedIds = array_column($lockedRows, 'id');
        }

        $lulusEditable      = array_values(array_diff($lulusIds, $lockedIds));
        $tidakLulusEditable = array_values(array_diff($tidakLulusIds, $lockedIds));

        $lulusLockedCount      = count($lulusIds) - count($lulusEditable);
        $tidakLulusLockedCount = count($tidakLulusIds) - count($tidakLulusEditable);

        $db->transStart();
        $lulusUpdated      = 0;
        $tidakLulusUpdated = 0;

        try {
            foreach ($lulusEditable as $id) {
                $jurusanDiterima = $jurusanDiterimaMap[$id] ?? null;

                // Guard ganda: whereIn('status', EDITABLE_STATUSES) langsung di query UPDATE.
                // Walaupun $lulusEditable sudah difilter di atas, baris ini memastikan
                // tidak ada race condition antara cek dan update (defense in depth).
                $affected = $db->table('pendaftaran')
                    ->where('id', $id)
                    ->whereIn('status', self::EDITABLE_STATUSES)
                    ->update([
                        'status'              => 'lulus',
                        'jurusan_diterima_id' => $jurusanDiterima,
                        'selected_at'         => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ]);

                if ($affected) {
                    $lulusUpdated++;
                }
            }

            foreach ($tidakLulusEditable as $id) {
                $affected = $db->table('pendaftaran')
                    ->where('id', $id)
                    ->whereIn('status', self::EDITABLE_STATUSES)
                    ->update([
                        'status'              => 'tidak_lulus',
                        'jurusan_diterima_id' => null,
                        'selected_at'         => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ]);

                if ($affected) {
                    $tidakLulusUpdated++;
                }
            }

            $db->transComplete();
            $success = $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'SeleksiModel::tetapkanLulus - ' . $e->getMessage());
            $success = false;
        }

        return [
            'success'             => $success,
            'lulus_updated'       => $lulusUpdated,
            'lulus_locked'        => $lulusLockedCount,
            'tidak_lulus_updated' => $tidakLulusUpdated,
            'tidak_lulus_locked'  => $tidakLulusLockedCount,
            'lulus_editable_ids'      => $lulusEditable,
            'tidak_lulus_editable_ids' => $tidakLulusEditable,
        ];
    }

    /**
     * Hitung jumlah siswa yang DITERIMA per jurusan (berdasarkan jurusan_diterima_id).
     * Ini yang dipakai untuk kartu kuota di halaman seleksi.
     *
     * PERBAIKAN: sebelumnya hanya menghitung status = 'lulus'. Begitu siswa
     * upload bukti daftar ulang, status pendaftaran berubah ke 'daftar_ulang'
     * sehingga keluar dari hitungan dan kartu kuota balik ke 0/kosong.
     * Sekarang dihitung dari semua status pasca-kelulusan:
     * lulus → daftar_ulang → siswa_aktif (siklus hidup yang sama, satu siswa).
     *
     * @return array [jurusan_id => count]
     */
    public function getCountLulusPerJurusan(): array
    {
        $db   = db_connect();
        $rows = $db->table('pendaftaran')
            ->select('jurusan_diterima_id, COUNT(*) as jumlah')
            ->whereIn('status', ['lulus', 'daftar_ulang', 'siswa_aktif'])
            ->where('jurusan_diterima_id IS NOT NULL')
            ->groupBy('jurusan_diterima_id')
            ->get()
            ->getResultArray();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['jurusan_diterima_id']] = (int) $row['jumlah'];
        }
        return $result;
    }

    public function getByPendaftaranId(int $id): ?object
    {
        return db_connect()->table('pendaftaran')->where('id', $id)->get()->getRow();
    }

    /**
     * Cek apakah status pendaftaran tertentu sudah terkunci
     * (daftar_ulang / siswa_aktif) — tidak boleh diubah lagi via Lulus/Tolak.
     */
    public function isLocked(int $id): bool
    {
        $row = $this->getByPendaftaranId($id);
        return $row && in_array($row->status, self::LOCKED_STATUSES);
    }
}
