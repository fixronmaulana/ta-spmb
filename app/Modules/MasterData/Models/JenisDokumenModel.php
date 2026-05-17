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

    // ── Geser urutan ke atas agar slot kosong untuk urutan yang diinginkan ─
    // Semua baris dengan urutan >= $targetUrutan (kecuali $excludeId) dinaikkan +1
    public function shiftUrutanUp(int $targetUrutan, ?int $excludeId = null): void
    {
        $builder = $this->db->table($this->table)
            ->where('urutan >=', $targetUrutan);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        $builder->set('urutan', 'urutan + 1', false)
            ->update();
    }

    // ── Normalisasi urutan: pastikan semua data punya urutan 1,2,3,… tanpa gap/duplikat ─
    // Dipanggil setelah setiap insert/update/delete agar urutan selalu rapi
    public function normalizeUrutan(): void
    {
        $rows = $this->orderBy('urutan', 'ASC')
            ->orderBy('id', 'ASC')   // tiebreaker: id kecil duluan
            ->findAll();

        foreach ($rows as $i => $row) {
            $newUrutan = $i + 1;
            if ((int) $row->urutan !== $newUrutan) {
                $this->db->table($this->table)
                    ->where('id', $row->id)
                    ->set('urutan', $newUrutan)
                    ->update();
            }
        }
    }

    // ── Cek apakah nilai urutan sudah dipakai oleh baris lain ────────────
    public function isUrutanTaken(int $urutan, ?int $excludeId = null): bool
    {
        $builder = $this->where('urutan', $urutan);
        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}
