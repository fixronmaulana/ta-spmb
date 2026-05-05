<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class JurusanModel extends Model
{
    protected $table            = 'jurusan';
    protected $primaryKey       = 'id';
    protected $returnType       = 'object';
    protected $allowedFields    = ['kode', 'kode_nis', 'nama', 'deskripsi', 'kuota', 'is_active', 'urutan'];
    protected $useTimestamps    = true;

    public function getAllActive(): array
    {
        return $this->where('is_active', 1)->orderBy('urutan')->findAll();
    }

    public function getForSelect(): array
    {
        $result = [];
        $items  = $this->getAllActive();
        foreach ($items as $item) {
            $result[$item->id] = $item->nama . ' (' . $item->kode . ')';
        }
        return $result;
    }
}
