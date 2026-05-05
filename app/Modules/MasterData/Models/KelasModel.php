<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class KelasModel extends Model
{
    protected $table         = 'kelas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['jurusan_id', 'nama', 'tingkat', 'kapasitas', 'wali_kelas', 'is_active'];
    protected $useTimestamps = true;

    public function getKelasAktif(int $jurusanId = 0): array
    {
        $builder = $this->where('kelas.is_active', 1);
        if ($jurusanId) {
            $builder = $builder->where('jurusan_id', $jurusanId);
        }
        return $builder->findAll();
    }

    /**
     * FIXED: join 'jurusan' bukan 'jurusans'
     */
    public function getWithJurusan(): array
    {
        return $this->select('kelas.*, jurusan.nama as nama_jurusan, jurusan.kode as kode_jurusan')
            // FIXED: 'jurusan' bukan 'jurusans'
            ->join('jurusan', 'jurusan.id = kelas.jurusan_id')
            ->where('kelas.is_active', 1)
            ->orderBy('tingkat')
            ->orderBy('kelas.nama')
            ->findAll();
    }

    public function getForSelect(int $jurusanId = 0): array
    {
        $result = [];
        $items  = $this->getKelasAktif($jurusanId);
        foreach ($items as $item) {
            $result[$item->id] = $item->nama;
        }
        return $result;
    }
}
