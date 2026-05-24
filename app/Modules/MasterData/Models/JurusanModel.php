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

    // =========================================================
    // KUOTA: hitung berapa slot yang sudah terpakai per jurusan
    // =========================================================

    /**
     * Hitung jumlah pendaftar yang sudah "mengisi" kuota suatu jurusan
     * pada periode tertentu (opsional).
     *
     * Yang dihitung = pendaftaran dengan status seleksi atau sudah diterima:
     *   submitted, verifikasi, seleksi, lulus, daftar_ulang, siswa_aktif
     * Status draft & tidak_lulus TIDAK dihitung (belum pasti / sudah gugur).
     *
     * Dihitung dari jurusan_pilihan1_id (pilihan utama).
     *
     * @param  int      $jurusanId
     * @param  int|null $periodeId  null = semua periode
     * @return int
     */
    public function getTerpakai(int $jurusanId, ?int $periodeId = null): int
    {
        $db = db_connect();

        $builder = $db->table('pendaftaran')
            ->where('jurusan_pilihan1_id', $jurusanId)
            ->whereIn('status', ['submitted', 'verifikasi', 'revisi', 'seleksi', 'lulus', 'daftar_ulang', 'siswa_aktif'])
            ->where('deleted_at IS NULL');

        if ($periodeId !== null) {
            $builder->where('periode_id', $periodeId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Hitung sisa kuota = kuota - terpakai.
     * Mengembalikan 0 jika sudah penuh atau melebihi kuota.
     *
     * @param  int      $jurusanId
     * @param  int      $kuota      Kuota maksimal jurusan ini
     * @param  int|null $periodeId
     * @return int
     */
    public function getSisaKuota(int $jurusanId, int $kuota, ?int $periodeId = null): int
    {
        $terpakai = $this->getTerpakai($jurusanId, $periodeId);
        return max(0, $kuota - $terpakai);
    }

    /**
     * Ambil semua jurusan aktif beserta informasi kuota terpakai dan sisa.
     * Digunakan di form step2 (tampilan ke pendaftar) dan di dashboard admin.
     *
     * Tiap object hasil memiliki properti tambahan:
     *   ->terpakai   (int)  jumlah slot sudah diambil
     *   ->sisa_kuota (int)  sisa slot tersisa
     *   ->penuh      (bool) true jika sisa_kuota == 0
     *
     * @param  int|null $periodeId
     * @return array
     */
    public function getAllActiveWithKuota(?int $periodeId = null): array
    {
        $jurusans = $this->getAllActive();

        foreach ($jurusans as $j) {
            $terpakai       = $this->getTerpakai($j->id, $periodeId);
            $j->terpakai    = $terpakai;
            $j->sisa_kuota  = max(0, (int) $j->kuota - $terpakai);
            $j->penuh       = ($j->sisa_kuota === 0);
        }

        return $jurusans;
    }
}
