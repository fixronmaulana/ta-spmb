<?php

namespace App\Modules\Verifikasi\Models;

use CodeIgniter\Model;

class VerifikasiModel extends Model
{
    protected $table = 'verifikasi_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $allowedFields = ['pendaftaran_id', 'admin_id', 'aksi', 'target_type', 'target_id', 'keterangan', 'data_sebelum', 'data_sesudah'];
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    public function log(int $pendaftaranId, int $adminId, string $aksi, array $extra = []): void
    {
        $this->insert(array_merge([
            'pendaftaran_id' => $pendaftaranId,
            'admin_id'       => $adminId,
            'aksi'           => $aksi,
            'created_at'     => date('Y-m-d H:i:s'),
        ], $extra));
    }

    public function getByPendaftaran(int $pendaftaranId): array
    {
        return $this->select('verifikasi_logs.*, u.nama_lengkap as admin_name')
            ->join('users u', 'u.id = verifikasi_logs.admin_id')
            ->where('pendaftaran_id', $pendaftaranId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }
}
