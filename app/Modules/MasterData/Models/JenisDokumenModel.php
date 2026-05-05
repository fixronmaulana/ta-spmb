<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class JenisDokumenModel extends Model
{
    protected $table         = 'jenis_dokumen';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'kode',
        'nama_dokumen',
        'keterangan',
        'is_wajib',
        'is_active',
        'urutan',
    ];

    // ── Ambil semua yang aktif, urut berdasarkan urutan ──────────────────
    public function getAllActive(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('urutan', 'ASC')
            ->orderBy('nama_dokumen', 'ASC')
            ->findAll();
    }

    // ── Ambil hanya yang wajib dan aktif ─────────────────────────────────
    public function getWajib(): array
    {
        return $this->where('is_active', 1)
            ->where('is_wajib', 1)
            ->orderBy('urutan', 'ASC')
            ->findAll();
    }

    // ── Ambil semua (termasuk nonaktif) untuk halaman admin ──────────────
    public function getAllForAdmin(): array
    {
        return $this->orderBy('urutan', 'ASC')
            ->orderBy('nama_dokumen', 'ASC')
            ->findAll();
    }

    // ── Format [kode => nama_dokumen] untuk dropdown / helper ────────────
    public function getMapKodeNama(bool $onlyActive = true): array
    {
        $rows = $onlyActive ? $this->getAllActive() : $this->getAllForAdmin();
        $map  = [];
        foreach ($rows as $row) {
            $map[$row->kode] = $row->nama_dokumen;
        }
        return $map;
    }

    // ── Cek apakah kode sudah dipakai (untuk validasi unik) ──────────────
    public function isKodeTaken(string $kode, ?int $excludeId = null): bool
    {
        $builder = $this->where('kode', $kode);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }

    // ── Urutan maksimum (untuk default urutan baru) ───────────────────────
    public function getMaxUrutan(): int
    {
        $row = $this->selectMax('urutan')->first();
        return $row ? (int) $row->urutan : 0;
    }
}