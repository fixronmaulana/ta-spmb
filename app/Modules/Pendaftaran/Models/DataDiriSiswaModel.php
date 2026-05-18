<?php

namespace App\Modules\Pendaftaran\Models;

use CodeIgniter\Model;

class DataDiriSiswaModel extends Model
{
    protected $table         = 'data_diri_siswas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'pendaftaran_id', 'nik', 'nisn', 'nama_lengkap', 'nama_panggilan',
        'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'kewarganegaraan',
        'anak_ke', 'jumlah_saudara', 'status_anak',
        'alamat', 'dusun', 'rt', 'rw', 'kelurahan',
        'kecamatan', 'kabupaten', 'provinsi', 'kode_pos',
        'no_hp', 'email_siswa',
        'asal_sekolah', 'alamat_sekolah', 'tahun_lulus',
        'nama_ayah', 'pekerjaan_ayah', 'pendidikan_ayah', 'penghasilan_ayah',
        'nama_ibu', 'pekerjaan_ibu', 'pendidikan_ibu', 'penghasilan_ibu',
        'no_hp_ortu', 'no_hp_ibu', 'nama_wali', 'no_hp_wali', 'foto_path',
    ];

    public function getByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)->first();
    }

    public function upsert(int $pendaftaranId, array $data): bool
    {
        $existing               = $this->getByPendaftaranId($pendaftaranId);
        $data['pendaftaran_id'] = $pendaftaranId;

        if ($existing) {
            return $this->update($existing->id, $data);
        }
        return (bool) $this->insert($data);
    }
}