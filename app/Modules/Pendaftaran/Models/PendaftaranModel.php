<?php

namespace App\Modules\Pendaftaran\Models;

use CodeIgniter\Model;

class PendaftaranModel extends Model
{
    protected $table          = 'pendaftaran';
    protected $primaryKey     = 'id';
    protected $returnType     = 'object';
    protected $useSoftDeletes = true;
    protected $allowedFields  = [
        'user_id',
        'periode_id',
        'no_pendaftaran',
        'jurusan_pilihan1_id',
        'jurusan_pilihan2_id',
        'jurusan_diterima_id',
        'status',
        'step_terakhir',
        'data_draft',
        'catatan_admin',
        'alasan_penolakan',
        'submitted_at',
        'verified_at',
        'verified_by',
        'selected_at',
        'nilai_seleksi',
        'keterangan_seleksi',
        'approved_by',
        'approved_at',
    ];
    protected $useTimestamps = true;

    public function getByUserId(int $userId): ?object
    {
        return $this->select('pendaftaran.*,
                j1.nama as jurusan_pilihan1_nama, j1.kode as jurusan_pilihan1_kode,
                j2.nama as jurusan_pilihan2_nama, j2.kode as jurusan_pilihan2_kode,
                jd.nama as jurusan_diterima_nama,
                jd.kode as jurusan_diterima_kode,
                u.nama_lengkap as nama_akun, u.email')
            ->join('users u',    'u.id = pendaftaran.user_id')
            ->join('jurusan j1', 'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2', 'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->join('jurusan jd', 'jd.id = pendaftaran.jurusan_diterima_id', 'left')
            ->where('pendaftaran.user_id', $userId)
            ->first();
    }

    public function countByStatus(string $status): int
    {
        return $this->where('status', $status)->countAllResults();
    }

    public function getStatistikByStatus(): array
    {
        $statuses = ['draft', 'submitted', 'verifikasi', 'seleksi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif'];
        $result   = array_fill_keys($statuses, 0);
        $result['total'] = 0;

        $rows = $this->select('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->findAll();

        foreach ($rows as $row) {
            $result[$row->status] = (int) $row->jumlah;
            $result['total']     += (int) $row->jumlah;
        }

        return $result;
    }

    public function getStatsByJurusan(): array
    {
        return $this->select('
                jurusan.id       AS jurusan_id,
                jurusan.nama     AS jurusan,
                jurusan.kode,
                jurusan.kuota,
                COUNT(pendaftaran.id) AS total_daftar,
                SUM(CASE WHEN pendaftaran.status = "lulus"        THEN 1 ELSE 0 END) AS total_lulus,
                SUM(CASE WHEN pendaftaran.status = "daftar_ulang" THEN 1 ELSE 0 END) AS total_daftar_ulang,
                SUM(CASE WHEN pendaftaran.status = "siswa_aktif"  THEN 1 ELSE 0 END) AS total_siswa_aktif
            ')
            ->join('jurusan', 'jurusan.id = pendaftaran.jurusan_pilihan1_id', 'right')
            ->groupBy('jurusan.id')
            ->findAll();
    }

    public function getStatsByGelombang(): array
    {
        $db = db_connect();

        $rows = $db->query("
            SELECT
                p.nama                   AS nama,
                p.tanggal_mulai,
                COUNT(pend.id)           AS total
            FROM periode p
            LEFT JOIN pendaftaran pend
                ON  pend.submitted_at >= p.tanggal_mulai
                AND pend.submitted_at <= IFNULL(p.tanggal_selesai, NOW())
                AND pend.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.tanggal_mulai ASC
        ")->getResultObject();

        if (empty($rows)) {
            $rows = $db->query("
                SELECT
                    CONCAT('Gelombang ', ROW_NUMBER() OVER (ORDER BY MIN(submitted_at))) AS nama,
                    COUNT(*) AS total
                FROM pendaftaran
                WHERE submitted_at IS NOT NULL AND deleted_at IS NULL
                GROUP BY QUARTER(submitted_at)
                ORDER BY MIN(submitted_at)
            ")->getResultObject();
        }

        return $rows ?: [];
    }

    public function getPendaftaranTerbaru(int $limit = 10, ?int $periodeId = null): array
    {
        $q = $this->select('pendaftaran.*, u.nama_lengkap as nama_calon, u.email as email_calon,
                j1.kode as jurusan_pilihan1_kode, j1.nama as jurusan_pilihan1_nama')
            ->join('users u',    'u.id = pendaftaran.user_id')
            ->join('jurusan j1', 'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->orderBy('pendaftaran.created_at', 'DESC')
            ->limit($limit);

        if ($periodeId !== null) {
            $q->where('pendaftaran.periode_id', $periodeId);
        }

        return $q->findAll();
    }

    /**
     * Statistik per status, difilter opsional per periode.
     */
    public function getStatistikByStatusPerPeriode(?int $periodeId = null): array
    {
        $statuses = ['draft', 'submitted', 'verifikasi', 'seleksi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif'];
        $result   = array_fill_keys($statuses, 0);
        $result['total'] = 0;

        $q = $this->select('status, COUNT(*) as jumlah')->groupBy('status');
        if ($periodeId !== null) {
            $q->where('periode_id', $periodeId);
        }

        foreach ($q->findAll() as $row) {
            $result[$row->status]  = (int) $row->jumlah;
            $result['total']      += (int) $row->jumlah;
        }

        return $result;
    }

    /**
     * Stats per jurusan, difilter opsional per periode.
     */
    public function getStatsByJurusanPerPeriode(?int $periodeId = null): array
    {
        $q = $this->select('
                jurusan.id       AS jurusan_id,
                jurusan.nama     AS jurusan,
                jurusan.kode,
                jurusan.kuota,
                COUNT(pendaftaran.id) AS total_daftar,
                SUM(CASE WHEN pendaftaran.status = "lulus"        THEN 1 ELSE 0 END) AS total_lulus,
                SUM(CASE WHEN pendaftaran.status = "daftar_ulang" THEN 1 ELSE 0 END) AS total_daftar_ulang,
                SUM(CASE WHEN pendaftaran.status = "siswa_aktif"  THEN 1 ELSE 0 END) AS total_siswa_aktif
            ')
            ->join('jurusan', 'jurusan.id = pendaftaran.jurusan_pilihan1_id', 'right')
            ->groupBy('jurusan.id');

        if ($periodeId !== null) {
            $q->where('pendaftaran.periode_id', $periodeId);
        }

        return $q->findAll();
    }

    public function getPaginatedByStatus(string $status, int $perPage = 20): array
    {
        return $this->select('pendaftaran.*, u.nama_lengkap as nama_calon, u.email as email_calon,
                dds.nama_lengkap, dds.asal_sekolah,
                j1.nama as jurusan_pilihan1_nama, j1.kode as jurusan_pilihan1_kode')
            ->join('users u',               'u.id = pendaftaran.user_id')
            ->join('data_diri_siswas dds',  'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('jurusan j1',            'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->where('pendaftaran.status', $status)
            ->orderBy('pendaftaran.created_at', 'DESC')
            ->paginate($perPage, 'default');
    }

    public function getAllForSeleksi(): array
    {
        return $this->select('pendaftaran.*, dds.nama_lengkap, dds.nisn,
                dds.asal_sekolah, dds.jenis_kelamin,
                j1.nama as jurusan_pilihan1_nama, j1.kode as jurusan_pilihan1_kode,
                j2.nama as jurusan_pilihan2_nama,
                u.email as email_calon')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('users u',              'u.id = pendaftaran.user_id')
            ->join('jurusan j1',           'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2',           'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->whereIn('pendaftaran.status', ['verifikasi', 'seleksi', 'lulus', 'tidak_lulus'])
            ->orderBy('pendaftaran.created_at', 'ASC')
            ->findAll();
    }

    public function getDraft(int $pendaftaranId, int $stepNum): array
    {
        $pendaftaran = $this->select('data_draft')->find($pendaftaranId);

        if (! $pendaftaran || empty($pendaftaran->data_draft)) {
            return [];
        }

        $allDraft = is_string($pendaftaran->data_draft)
            ? json_decode($pendaftaran->data_draft, true)
            : (array) $pendaftaran->data_draft;

        return $allDraft["step{$stepNum}"] ?? [];
    }

    public function saveDraft(int $pendaftaranId, array $data, int $stepNum): bool
    {
        $pendaftaran = $this->select('data_draft')->find($pendaftaranId);

        if (! $pendaftaran) {
            return false;
        }

        $allDraft = [];
        if (! empty($pendaftaran->data_draft)) {
            $allDraft = is_string($pendaftaran->data_draft)
                ? json_decode($pendaftaran->data_draft, true)
                : (array) $pendaftaran->data_draft;
        }

        unset($data['csrf_token'], $data['_method'], $data['step']);
        $allDraft["step{$stepNum}"] = $data;

        return $this->update($pendaftaranId, [
            'data_draft' => json_encode($allDraft),
        ]);
    }

    public function getWithRelations(int $pendaftaranId): ?object
    {
        return $this->select('pendaftaran.*,
                j1.nama as jurusan_pilihan1_nama, j1.kode as jurusan_pilihan1_kode,
                j2.nama as jurusan_pilihan2_nama, j2.kode as jurusan_pilihan2_kode,
                jd.nama as jurusan_diterima_nama, jd.kode as jurusan_diterima_kode,
                u.nama_lengkap as nama_akun, u.email,
                p.nama as nama_periode, p.tanggal_mulai, p.tanggal_selesai')
            ->join('users u',    'u.id = pendaftaran.user_id')
            ->join('jurusan j1', 'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2', 'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->join('jurusan jd', 'jd.id = pendaftaran.jurusan_diterima_id', 'left')
            ->join('periode p',  'p.id = pendaftaran.periode_id', 'left')
            ->where('pendaftaran.id', $pendaftaranId)
            ->first();
    }

    /**
     * Update status pendaftaran beserta field tambahan (verified_at, catatan_admin, dsb.)
     * Dipakai oleh VerifikasiService & SeleksiService.
     */
    public function updateStatus(int $pendaftaranId, string $status, array $extra = []): bool
    {
        $data = array_merge(['status' => $status], $extra);
        return $this->update($pendaftaranId, $data);
    }

    /**
     * Ambil ID pendaftaran sebelumnya dan sesudahnya (untuk navigasi prev/next di halaman detail verifikasi).
     * Mengembalikan ['prev' => id|null, 'next' => id|null].
     */
    public function getPrevNextId(int $currentId, string $status = 'submitted'): array
    {
        $all = $this->select('pendaftaran.id')
            ->whereIn('status', $status === 'all' ? ['submitted', 'verifikasi'] : [$status])
            ->orderBy('submitted_at', 'ASC')
            ->findAll();

        $ids = array_column(array_map(fn($r) => (array)$r, $all), 'id');
        $pos = array_search($currentId, $ids);

        return [
            'prev' => ($pos !== false && $pos > 0)                  ? $ids[$pos - 1] : null,
            'next' => ($pos !== false && $pos < count($ids) - 1)    ? $ids[$pos + 1] : null,
        ];
    }

    public function submitPendaftaran(int $pendaftaranId): bool
    {
        $noPendaftaran = $this->generateNoPendaftaran();

        return $this->update($pendaftaranId, [
            'status'         => 'submitted',
            'no_pendaftaran' => $noPendaftaran,
            'submitted_at'   => date('Y-m-d H:i:s'),
            'step_terakhir'  => 5,
        ]);
    }

    protected function generateNoPendaftaran(): string
    {
        $tahun  = date('Y');
        $prefix = "SPMB-{$tahun}-";

        $last = $this->db->table($this->table)
            ->like('no_pendaftaran', $prefix, 'after')
            ->orderBy('no_pendaftaran', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        $urut = 1;
        if ($last && ! empty($last->no_pendaftaran)) {
            $parts = explode('-', $last->no_pendaftaran);
            $urut  = (int) end($parts) + 1;
        }

        return $prefix . str_pad($urut, 6, '0', STR_PAD_LEFT);
    }
}
