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

    /**
     * Hitung jumlah slot TERPAKAI (sudah ditetapkan lulus/diterima) per jurusan.
     * Dihitung dari jurusan_diterima_id untuk status pasca-seleksi.
     *
     * @param  int      $jurusanId
     * @param  int|null $periodeId  null = semua periode
     * @return int
     */
    public function getTerpakai(int $jurusanId, ?int $periodeId = null): int
    {
        $db = db_connect();

        // Siswa yang sudah ditetapkan LULUS dan masuk ke jurusan ini
        $builderLulus = $db->table('pendaftaran')
            ->where('jurusan_diterima_id', $jurusanId)
            ->whereIn('status', ['lulus', 'daftar_ulang', 'siswa_aktif'])
            ->where('deleted_at IS NULL');

        if ($periodeId !== null) {
            $builderLulus->where('periode_id', $periodeId);
        }

        return (int) $builderLulus->countAllResults();
    }

    /**
     * Hitung estimasi demand (antrean) per jurusan dari pilihan 1.
     * Digunakan untuk info dashboard, bukan untuk kuota.
     *
     * @param  int      $jurusanId
     * @param  int|null $periodeId
     * @return int
     */
    public function getEstimasiDemand(int $jurusanId, ?int $periodeId = null): int
    {
        $db = db_connect();

        $builder = $db->table('pendaftaran')
            ->where('jurusan_pilihan1_id', $jurusanId)
            ->whereIn('status', ['submitted', 'verifikasi', 'seleksi'])
            ->where('deleted_at IS NULL');

        if ($periodeId !== null) {
            $builder->where('periode_id', $periodeId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     *
     * @param  int      $jurusanId
     * @param  int|null $periodeId             null = semua periode
     * @param  int|null $excludePendaftaranId  ID pendaftaran milik pengisi form saat ini,
     *                                         dikecualikan supaya tidak memblokir dirinya sendiri saat edit ulang
     * @return int
     */
    public function getTerpakaiPendaftar(int $jurusanId, ?int $periodeId = null, ?int $excludePendaftaranId = null): int
    {
        $db      = db_connect();
        $builder = $db->table('pendaftaran')
            ->where('jurusan_pilihan1_id', $jurusanId)
            ->whereIn('status', ['submitted', 'verifikasi', 'revisi', 'seleksi', 'lulus', 'daftar_ulang', 'siswa_aktif'])
            ->where('deleted_at IS NULL');

        if ($periodeId !== null) {
            $builder->where('periode_id', $periodeId);
        }

        if ($excludePendaftaranId !== null) {
            $builder->where('id !=', $excludePendaftaranId);
        }

        return (int) $builder->countAllResults();
    }

    /**
     * Ambil semua jurusan aktif beserta kuota TERPAKAI BERBASIS PENDAFTAR
     * (bukan hanya yang sudah lulus). Dipakai khusus untuk FORMULIR
     * PENDAFTARAN (step2) agar indikator kuota yang dilihat calon siswa
     * sinkron 1:1 dengan validasi backend saat submit.
     *
     * Properti tambahan per object:
     *   ->terpakai    (int)  jumlah pendaftar yang sudah memesan slot ini
     *   ->sisa_kuota  (int)  sisa slot tersisa
     *   ->penuh       (bool) true jika sisa_kuota == 0
     *
     * @param  int|null $periodeId
     * @param  int|null $excludePendaftaranId  ID pendaftaran milik pengisi form saat ini
     * @return array
     */
    public function getAllActiveWithKuotaPendaftaran(?int $periodeId = null, ?int $excludePendaftaranId = null): array
    {
        $jurusans = $this->getAllActive();

        foreach ($jurusans as $j) {
            $terpakai      = $this->getTerpakaiPendaftar($j->id, $periodeId, $excludePendaftaranId);
            $j->terpakai   = $terpakai;
            $j->sisa_kuota = max(0, (int) $j->kuota - $terpakai);
            $j->penuh      = ($j->sisa_kuota === 0);
        }

        return $jurusans;
    }

    /**
     * Hitung sisa kuota = kuota - terpakai (dari jurusan_diterima_id).
     *
     * @param  int      $jurusanId
     * @param  int      $kuota
     * @param  int|null $periodeId
     * @return int
     */
    public function getSisaKuota(int $jurusanId, int $kuota, ?int $periodeId = null): int
    {
        $terpakai = $this->getTerpakai($jurusanId, $periodeId);
        return max(0, $kuota - $terpakai);
    }

    /**
     * Ambil semua jurusan aktif beserta kuota terpakai (BERBASIS YANG SUDAH LULUS)
     * dan sisa. Dipakai untuk kebutuhan ADMIN/LAPORAN/SELEKSI (mis. dashboard
     * kepala sekolah, laporan per jurusan) — bukan untuk form pendaftaran publik.
     * Untuk form pendaftaran (step2), gunakan getAllActiveWithKuotaPendaftaran().
     *
     * Properti tambahan per object:
     *   ->terpakai    (int)  jumlah slot sudah diterima (dari jurusan_diterima_id)
     *   ->sisa_kuota  (int)  sisa slot tersisa
     *   ->penuh       (bool) true jika sisa_kuota == 0
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
