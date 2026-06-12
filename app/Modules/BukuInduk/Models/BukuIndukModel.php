<?php

namespace App\Modules\BukuInduk\Models;

use CodeIgniter\Model;

/**
 * FIXED:
 *  - join 'jurusans' → 'jurusan'
 *  - join 'jurusans j' → 'jurusan j'  (semua alias)
 */
class BukuIndukModel extends Model
{
    protected $table         = 'buku_induks';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'pendaftaran_id',
        'user_id',
        'kelas_id',
        'jurusan_id',
        'nis',
        'nik',
        'nisn',
        'nama_lengkap',
        'nama_panggilan',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'kewarganegaraan',
        'alamat',
        'no_hp',
        'email_siswa',
        'nama_ayah',
        'pekerjaan_ayah',
        'no_hp_ayah',
        'nama_ibu',
        'pekerjaan_ibu',
        'no_hp_ibu',
        'no_hp_ortu',
        'asal_sekolah',
        'tahun_lulus_smp',
        'golongan_darah',
        'tinggi_badan',
        'berat_badan',
        'riwayat_penyakit',
        'catatan_kesehatan',
        'tahun_masuk',
        'status_siswa',
        'converted_at',
        'converted_by',
    ];

    public function getWithRelations(int $id): ?object
    {
        return $this->select(
            'buku_induks.*,
                 j.nama        as jurusan_nama,   j.kode as jurusan_kode,
                 k.nama        as kelas_nama,      k.tingkat as kelas_tingkat,
                 k.wali_kelas,
                 u.nama_lengkap as admin_name,
                 dds.status_anak    as dds_status_anak,
                 dds.alamat_sekolah as dds_alamat_sekolah,
                 ks.nama_lengkap    as kepala_sekolah_nama'
        )
            ->join('jurusan j',          'j.id = buku_induks.jurusan_id')
            ->join('kelas k',            'k.id = buku_induks.kelas_id',                       'left')
            ->join('users u',            'u.id = buku_induks.converted_by',                   'left')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = buku_induks.pendaftaran_id',   'left')
            ->join('users ks',           'ks.role_id = 2',                                    'left')
            ->find($id);
    }

    public function getAllWithRelations(array $filters = []): array
    {
        $q = $this->select(
            'buku_induks.id, buku_induks.nis, buku_induks.nisn,
                 buku_induks.nama_lengkap, buku_induks.jenis_kelamin,
                 buku_induks.tahun_masuk, buku_induks.status_siswa,
                 j.nama as jurusan_nama, j.kode as jurusan_kode,
                 k.nama as kelas_nama'
        )
            // FIXED: 'jurusan j' bukan 'jurusans j'
            ->join('jurusan j', 'j.id = buku_induks.jurusan_id')
            ->join('kelas k',   'k.id = buku_induks.kelas_id', 'left')
            ->orderBy('buku_induks.nis', 'ASC');

        if (! empty($filters['jurusan_id'])) {
            $q->where('buku_induks.jurusan_id', $filters['jurusan_id']);
        }
        if (! empty($filters['status_siswa'])) {
            $q->where('buku_induks.status_siswa', $filters['status_siswa']);
        }
        if (! empty($filters['search'])) {
            $q->groupStart()
                ->like('buku_induks.nama_lengkap', $filters['search'])
                ->orLike('buku_induks.nis',        $filters['search'])
                ->orLike('buku_induks.nisn',       $filters['search'])
                ->groupEnd();
        }

        return $q->findAll();
    }

    /**
     * Update data pribadi + kontak + ortu.
     * Jika $oldData & $editedBy diberikan, langsung rekam diff ke edit_log.
     */
    public function updatePribadi(int $id, array $data, array $oldData = [], int $editedBy = 0): bool
    {
        $allowed = [
            'nisn',
            'nik',
            'nama_lengkap',
            'nama_panggilan',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama',
            'kewarganegaraan',
            'asal_sekolah',
            'tahun_lulus_smp',
            'alamat',
            'no_hp',
            'email_siswa',
            'nama_ayah',
            'pekerjaan_ayah',
            'no_hp_ayah',
            'nama_ibu',
            'pekerjaan_ibu',
            'no_hp_ibu',
        ];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered = array_filter($filtered, fn($v) => $v !== null);

        $ok = $this->update($id, $filtered);

        if ($ok && $editedBy > 0 && ! empty($oldData)) {
            (new \App\Modules\BukuInduk\Models\BukuIndukEditLogModel())
                ->recordChanges($id, $editedBy, 'Data Pribadi', $oldData, $filtered);
        }

        return $ok;
    }

    public function updateKesehatan(int $id, array $data, array $oldData = [], int $editedBy = 0): bool
    {
        $allowed  = ['golongan_darah', 'tinggi_badan', 'berat_badan', 'riwayat_penyakit', 'catatan_kesehatan'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered = array_filter($filtered, fn($v) => $v !== null);

        $ok = $this->update($id, $filtered);

        if ($ok && $editedBy > 0 && ! empty($oldData)) {
            (new \App\Modules\BukuInduk\Models\BukuIndukEditLogModel())
                ->recordChanges($id, $editedBy, 'Data Kesehatan', $oldData, $filtered);
        }

        return $ok;
    }

    public function updateKelas(int $id, array $data, array $oldData = [], int $editedBy = 0): bool
    {
        $allowed  = ['kelas_id', 'tahun_masuk'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        $filtered = array_filter($filtered, fn($v) => $v !== null);

        $ok = $this->update($id, $filtered);

        if ($ok && $editedBy > 0 && ! empty($oldData)) {
            (new \App\Modules\BukuInduk\Models\BukuIndukEditLogModel())
                ->recordChanges($id, $editedBy, 'Penempatan Kelas', $oldData, $filtered);
        }

        return $ok;
    }

    /**
     * Ambil semua field yang dibutuhkan untuk export Excel.
     * Berbeda dengan getAllWithRelations() yang hanya mengambil field ringkas.
     */
    public function getAllForExport(array $filters = []): array
    {
        $q = $this->select(
            'buku_induks.*,
             j.nama  as jurusan_nama, j.kode  as jurusan_kode,
             k.nama  as kelas_nama,   k.wali_kelas'
        )
            ->join('jurusan j', 'j.id = buku_induks.jurusan_id')
            ->join('kelas k',   'k.id = buku_induks.kelas_id', 'left')
            ->orderBy('buku_induks.nis', 'ASC');

        if (! empty($filters['jurusan_id'])) {
            $q->where('buku_induks.jurusan_id', $filters['jurusan_id']);
        }
        if (! empty($filters['status_siswa'])) {
            $q->where('buku_induks.status_siswa', $filters['status_siswa']);
        }
        if (! empty($filters['search'])) {
            $q->groupStart()
                ->like('buku_induks.nama_lengkap', $filters['search'])
                ->orLike('buku_induks.nis',        $filters['search'])
                ->orLike('buku_induks.nisn',       $filters['search'])
                ->groupEnd();
        }
        // Export IDs tertentu (untuk export selected)
        if (! empty($filters['ids']) && is_array($filters['ids'])) {
            $q->whereIn('buku_induks.id', array_map('intval', $filters['ids']));
        }

        return $q->findAll();
    }

    public function countByStatus(string $status): int
    {
        return $this->where('status_siswa', $status)->countAllResults();
    }

    public function getByPendaftaranId(int $pendaftaranId): ?object
    {
        return $this->where('pendaftaran_id', $pendaftaranId)->first();
    }
}
