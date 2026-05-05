<?php

namespace App\Modules\Pendaftaran\Models;

use CodeIgniter\Model;

class DokumenModel extends Model
{
    protected $table         = 'dokumen_pendaftaran';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'pendaftaran_id',
        'jenis_dokumen',
        'nama_file_asli',
        'nama_file_simpan',
        'path_file',
        'ukuran_file',
        'tipe_mime',
        'status_verifikasi',
        'catatan_verifikasi',
        'diverifikasi_oleh',
        'diverifikasi_pada',
    ];

    public function getByPendaftaranId(int $pendaftaranId): array
    {
        return $this->where('pendaftaran_id', $pendaftaranId)->findAll();
    }

    public function getByJenis(int $pendaftaranId, string $jenis): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->where('jenis_dokumen', $jenis)
            ->first();
    }

    /**
     * Cek apakah semua dokumen wajib sudah diupload
     */
    public function isComplete(int $pendaftaranId): bool
    {
        $wajib    = jenis_dokumen_wajib();
        $uploaded = $this->select('jenis_dokumen')
            ->where('pendaftaran_id', $pendaftaranId)
            ->asArray()
            ->findAll();

        $uploadedJenis = array_column($uploaded, 'jenis_dokumen', 'jenis_dokumen');

        foreach ($wajib as $jenis) {
            if (! isset($uploadedJenis[$jenis])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Cek apakah semua dokumen sudah diapprove
     */
    public function isAllApproved(int $pendaftaranId): bool
    {
        $pending = $this->where('pendaftaran_id', $pendaftaranId)
            ->whereIn('status_verifikasi', ['pending', 'rejected'])
            ->countAllResults();

        return $pending === 0;
    }

    public function approveAll(int $pendaftaranId, int $adminId): bool
    {
        return $this->where('pendaftaran_id', $pendaftaranId)
            ->set([
                'status_verifikasi' => 'approved',
                'diverifikasi_oleh' => $adminId,
                'diverifikasi_pada' => date('Y-m-d H:i:s'),
            ])
            ->update();
    }

    public function approve(int $id, int $adminId): bool
    {
        return $this->update($id, [
            'status_verifikasi'  => 'approved',
            'diverifikasi_oleh'  => $adminId,
            'diverifikasi_pada'  => date('Y-m-d H:i:s'),
            'catatan_verifikasi' => null,
        ]);
    }

    public function reject(int $id, int $adminId, string $catatan): bool
    {
        return $this->update($id, [
            'status_verifikasi'  => 'rejected',
            'diverifikasi_oleh'  => $adminId,
            'diverifikasi_pada'  => date('Y-m-d H:i:s'),
            'catatan_verifikasi' => $catatan,
        ]);
    }

    /**
     * Kembalikan daftar jenis dokumen wajib yang belum diupload
     *
     */
    public function getMissing(int $pendaftaranId): array
    {
        $wajib    = jenis_dokumen_wajib();
        $existing = $this->select('jenis_dokumen')
            ->where('pendaftaran_id', $pendaftaranId)
            ->asArray()
            ->findAll();

        $existingJenis = array_column($existing, 'jenis_dokumen', 'jenis_dokumen');

        $missing = [];
        foreach ($wajib as $j) {
            if (! isset($existingJenis[$j])) {
                $missing[] = $j;
            }
        }
        return $missing;
    }
}
