<?php

namespace App\Modules\DaftarUlang\Models;

use CodeIgniter\Model;

class DaftarUlangModel extends Model
{
    protected $table         = 'daftar_ulangs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    // FIX: tambah kolom nis, nama_kelas, nama_file_bukti
    protected $allowedFields = [
        'pendaftaran_id', 'user_id', 'kelas_id',
        'nis',              // NIS yang ditetapkan admin
        'nama_kelas',       // Nama kelas yang ditetapkan admin
        'bukti_pembayaran_path',
        'nama_file_bukti',  // Nama asli file bukti (untuk ditampilkan)
        'nominal_pembayaran', 'catatan_siswa', 'status',
        'catatan_admin', 'dikonfirmasi_oleh', 'dikonfirmasi_pada',
    ];

    // ── Status constants ─────────────────────────────────────────────────
    const STATUS_PENDING       = 'pending';
    const STATUS_DIKONFIRMASI  = 'dikonfirmasi';
    const STATUS_DITOLAK       = 'ditolak';

    public function getByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)->first();
    }

    public function getWithRelations(int $id): ?object
    {
        return $this->select('daftar_ulangs.*, u.nama_lengkap as nama_calon, u.email as email_calon,
                dds.nama_lengkap, j1.nama as jurusan_nama, j1.kode as jurusan_kode,
                k.nama as kelas_nama_rel, p.no_pendaftaran')
            ->join('users u',              'u.id = daftar_ulangs.user_id')
            ->join('pendaftaran p',        'p.id = daftar_ulangs.pendaftaran_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = daftar_ulangs.pendaftaran_id', 'left')
            ->join('jurusan j1',           'j1.id = p.jurusan_diterima_id', 'left')
            ->join('kelas k',              'k.id = daftar_ulangs.kelas_id', 'left')
            ->find($id);
    }

    public function getAllWithRelations(string $status = '', string $search = ''): array
    {
        $q = $this->select('daftar_ulangs.*, u.nama_lengkap as nama_calon, u.email as email_calon,
                COALESCE(dds.nama_lengkap, u.nama_lengkap) as nama_tampil,
                j1.nama as jurusan_nama, j1.kode as jurusan_kode,
                p.no_pendaftaran')
            ->join('users u',              'u.id = daftar_ulangs.user_id')
            ->join('pendaftaran p',        'p.id = daftar_ulangs.pendaftaran_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = daftar_ulangs.pendaftaran_id', 'left')
            ->join('jurusan j1',           'j1.id = p.jurusan_diterima_id', 'left')
            ->orderBy('daftar_ulangs.created_at', 'DESC');

        if ($status) {
            $q->where('daftar_ulangs.status', $status);
        }

        if ($search) {
            $q->groupStart()
              ->like('u.nama_lengkap',            $search)
              ->orLike('dds.nama_lengkap',         $search)
              ->orLike('p.no_pendaftaran',          $search)
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