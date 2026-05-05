<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class PeriodeModel extends Model
{
    protected $table         = 'periode';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'nama', 'tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai',
        'tanggal_pengumuman', 'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai', 'is_active', 'is_published', 'deskripsi',
    ];
    protected $useTimestamps = true;

    public function getPeriodeAktif(): ?object
    {
        return $this->where('is_active', 1)->first();
    }

    /**
     * Aktifkan periode ini, nonaktifkan yang lain.
     * FIXED: 'periodes' → 'periode'
     */
    public function setAktif(int $id): bool
    {
        $db = db_connect();
        // FIXED: nama tabel 'periode', bukan 'periodes'
        $db->table('periode')->update(['is_active' => 0]);
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Publish pengumuman kelulusan
     */
    public function publish(int $id): bool
    {
        return $this->update($id, ['is_published' => 1]);
    }

    public function isPengumumanPublished(): bool
    {
        $p = $this->getPeriodeAktif();
        return $p && (bool) $p->is_published;
    }
}
