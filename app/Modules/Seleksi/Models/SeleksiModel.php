<?php

namespace App\Modules\Seleksi\Models;

use CodeIgniter\Model;

class SeleksiModel extends Model
{
    protected $table      = 'pendaftaran';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    /**
     * Status yang TIDAK BOLEH diubah sama sekali.
     * Siswa yang sudah daftar ulang atau sudah jadi siswa aktif
     * tidak bisa di-revert via aksi seleksi.
     */
    public const LOCKED_STATUSES = ['daftar_ulang', 'siswa_aktif'];

    /**
     * Status yang BOLEH diubah via tetapkanLulus().
     *
     * 'seleksi'     → belum diproses, normal flow
     * 'lulus'       → sudah ditetapkan, bisa diubah jurusannya atau di-koreksi
     *                 ke tidak_lulus (selama belum published & belum daftar_ulang)
     * 'tidak_lulus' → sudah ditolak, bisa dikoreksi ke lulus
     *
     * Defense-in-depth: meski Controller sudah validasi, query UPDATE di sini
     * tetap whereIn EDITABLE_STATUSES — sehingga LOCKED_STATUSES tidak bisa
     * diubah sama sekali bahkan jika ada bypass di layer Controller.
     */
    public const EDITABLE_STATUSES = ['seleksi', 'lulus', 'tidak_lulus'];

    /**
     * Ambil semua peserta di tahap seleksi dan sesudahnya.
     *
     * Mengambil status seleksi, lulus, tidak_lulus, daftar_ulang, siswa_aktif
     * agar semua tetap tampil di halaman admin (dengan badge status berbeda).
     * Status sebelumnya (draft, submitted, verifikasi, revisi) tidak ditampilkan
     * karena belum masuk tahap seleksi.
     */
    public function getForSeleksiByJurusan(): array
    {
        return $this->select('
                pendaftaran.id,
                pendaftaran.status,
                pendaftaran.user_id,
                pendaftaran.periode_id,
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
            ->whereIn('pendaftaran.status', [
                'seleksi',
                'lulus',
                'tidak_lulus',
                'daftar_ulang',
                'siswa_aktif'
            ])
            ->orderBy('pendaftaran.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Tetapkan lulus / tidak lulus ke DB.
     *
     * Tidak mengirim notifikasi apapun.
     * Notifikasi hanya dikirim oleh SeleksiController::publish().
     *
     * Guard berlapis:
     * 1. Race condition guard  → cek LOCKED_STATUSES sebelum loop update
     * 2. Query-level guard     → whereIn('status', EDITABLE_STATUSES) pada UPDATE
     *
     * @param  array $lulusIds           [pendaftaran_id, ...] — sudah divalidasi V1-V5
     * @param  array $tidakLulusIds      [pendaftaran_id, ...] — sudah divalidasi V1-V3
     * @param  array $jurusanDiterimaMap [pendaftaran_id => jurusan_id]
     */
    public function tetapkanLulus(array $lulusIds, array $tidakLulusIds, array $jurusanDiterimaMap): array
    {
        $db = db_connect();

        // Race condition guard
        $allIds    = array_unique(array_merge($lulusIds, $tidakLulusIds));
        $lockedIds = [];
        if (! empty($allIds)) {
            $rows      = $db->table('pendaftaran')
                ->select('id')
                ->whereIn('id', $allIds)
                ->whereIn('status', self::LOCKED_STATUSES)
                ->get()
                ->getResultArray();
            $lockedIds = array_column($rows, 'id');
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
                $affected = $db->table('pendaftaran')
                    ->where('id', $id)
                    ->whereIn('status', self::EDITABLE_STATUSES) // defense-in-depth
                    ->update([
                        'status'              => 'lulus',
                        'jurusan_diterima_id' => $jurusanDiterimaMap[$id] ?? null,
                        'selected_at'         => date('Y-m-d H:i:s'),
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ]);
                if ($affected) $lulusUpdated++;
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
                if ($affected) $tidakLulusUpdated++;
            }

            $db->transComplete();
            $success = $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'SeleksiModel::tetapkanLulus — ' . $e->getMessage());
            $success = false;
        }

        return [
            'success'                  => $success,
            'lulus_updated'            => $lulusUpdated,
            'lulus_locked'             => $lulusLockedCount,
            'tidak_lulus_updated'      => $tidakLulusUpdated,
            'tidak_lulus_locked'       => $tidakLulusLockedCount,
            'lulus_editable_ids'       => $lulusEditable,
            'tidak_lulus_editable_ids' => $tidakLulusEditable,
        ];
    }

    /**
     * Jumlah siswa diterima per jurusan (dari jurusan_diterima_id).
     * Dipakai untuk kartu kuota di halaman admin seleksi.
     *
     * Dihitung dari semua status pasca-kelulusan agar tidak turun
     * ketika siswa naik ke status daftar_ulang / siswa_aktif.
     *
     * @return array [jurusan_id => count]
     */
    public function getCountLulusPerJurusan(): array
    {
        $rows = db_connect()->table('pendaftaran')
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

    /**
     * Cek apakah pendaftaran sudah terkunci.
     */
    public function isLocked(int $id): bool
    {
        $row = db_connect()->table('pendaftaran')->where('id', $id)->get()->getRow();
        return $row && in_array($row->status, self::LOCKED_STATUSES);
    }
}
