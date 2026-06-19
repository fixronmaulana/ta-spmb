<?php

namespace App\Modules\DaftarUlang\Models;

use CodeIgniter\Model;

class DaftarUlangModel extends Model
{
    protected $table         = 'daftar_ulangs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'pendaftaran_id',
        'user_id',
        'kelas_id',
        'nis',
        'nama_kelas',
        'bukti_pembayaran_path',
        'nama_file_bukti',
        'nominal_pembayaran',
        'catatan_siswa',
        'status',
        'catatan_admin',
        'dikonfirmasi_oleh',
        'dikonfirmasi_pada',
    ];

    // ── Status constants ─────────────────────────────────────────────────
    const STATUS_PENDING      = 'pending';
    const STATUS_DIKONFIRMASI = 'dikonfirmasi';
    const STATUS_DITOLAK      = 'ditolak';

    // =========================================================
    // getByPendaftaranId sekarang ambil yang TERBARU
    // (dipakai untuk cek status terkini / redirect guard)
    // =========================================================
    public function getByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /**
     * Ambil semua riwayat daftar ulang satu pendaftar,
     * diurutkan dari yang terbaru.
     */
    public function getHistoryByPendaftaranId(int $pendaftaranId): array
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Cek apakah ada row dengan status dikonfirmasi
     * untuk pendaftaran ini (kunci agar tidak bisa submit ulang
     * setelah dikonfirmasi).
     */
    public function getDikonfirmasiByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->where('status', self::STATUS_DIKONFIRMASI)
            ->first();
    }

    /**
     * Cek apakah ada row dengan status PENDING (sedang diproses).
     * Dipakai untuk mencegah double submit.
     */
    public function getPendingByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->where('status', self::STATUS_PENDING)
            ->first();
    }

    /**
     * getWithRelations — include jurusan diterima + pilihan 1 & 2.
     */
    public function getWithRelations(int $id): ?object
    {
        return $this->select('
                daftar_ulangs.*,
                u.nama_lengkap AS nama_calon,
                u.email        AS email_calon,
                dds.nama_lengkap,
                p.no_pendaftaran,
                p.jurusan_pilihan1_id,
                p.jurusan_pilihan2_id,
                p.jurusan_diterima_id,
                jd.nama AS jurusan_nama,
                jd.kode AS jurusan_kode,
                j1.nama AS jurusan_pilihan1_nama,
                j1.kode AS jurusan_pilihan1_kode,
                j2.nama AS jurusan_pilihan2_nama,
                j2.kode AS jurusan_pilihan2_kode,
                k.nama  AS kelas_nama_rel
            ')
            ->join('users u',              'u.id = daftar_ulangs.user_id')
            ->join('pendaftaran p',        'p.id = daftar_ulangs.pendaftaran_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = daftar_ulangs.pendaftaran_id', 'left')
            ->join('jurusan jd',           'jd.id = p.jurusan_diterima_id',   'left')
            ->join('jurusan j1',           'j1.id = p.jurusan_pilihan1_id',   'left')
            ->join('jurusan j2',           'j2.id = p.jurusan_pilihan2_id',   'left')
            ->join('kelas k',              'k.id  = daftar_ulangs.kelas_id',  'left')
            ->find($id);
    }

    /**
     * getAllWithRelations — untuk tabel admin, ambil semua pengajuan
     * beserta jurusan diterima.
     */
    public function getAllWithRelations(string $status = '', string $search = ''): array
    {
        $q = $this->select('
                daftar_ulangs.*,
                u.nama_lengkap AS nama_calon,
                u.email        AS email_calon,
                COALESCE(dds.nama_lengkap, u.nama_lengkap) AS nama_tampil,
                p.no_pendaftaran,
                p.jurusan_pilihan1_id,
                p.jurusan_pilihan2_id,
                p.jurusan_diterima_id,
                jd.nama AS jurusan_nama,
                jd.kode AS jurusan_kode,
                j1.nama AS jurusan_pilihan1_nama,
                j1.kode AS jurusan_pilihan1_kode,
                j2.nama AS jurusan_pilihan2_nama,
                j2.kode AS jurusan_pilihan2_kode
            ')
            ->join('users u',              'u.id = daftar_ulangs.user_id')
            ->join('pendaftaran p',        'p.id = daftar_ulangs.pendaftaran_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = daftar_ulangs.pendaftaran_id', 'left')
            ->join('jurusan jd',           'jd.id = p.jurusan_diterima_id',   'left')
            ->join('jurusan j1',           'j1.id = p.jurusan_pilihan1_id',   'left')
            ->join('jurusan j2',           'j2.id = p.jurusan_pilihan2_id',   'left')
            ->orderBy('daftar_ulangs.created_at', 'DESC');

        if ($status) {
            $q->where('daftar_ulangs.status', $status);
        }

        if ($search) {
            $q->groupStart()
                ->like('u.nama_lengkap',     $search)
                ->orLike('dds.nama_lengkap', $search)
                ->orLike('p.no_pendaftaran', $search)
                ->groupEnd();
        }

        return $q->findAll();
    }

    public function getStatsByStatus(): array
    {
        $rows = $this->select('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->findAll();

        $stats = ['total' => 0, 'pending' => 0, 'dikonfirmasi' => 0, 'ditolak' => 0];
        foreach ($rows as $row) {
            $stats[$row->status] = (int) $row->jumlah;
            $stats['total']     += (int) $row->jumlah;
        }
        return $stats;
    }
}
