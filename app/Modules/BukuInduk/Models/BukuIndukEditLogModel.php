<?php

namespace App\Modules\BukuInduk\Models;

use CodeIgniter\Model;

/**
 * File: app/Modules/BukuInduk/Models/BukuIndukEditLogModel.php
 *
 * Menyimpan & mengambil riwayat perubahan field buku induk.
 */
class BukuIndukEditLogModel extends Model
{
    protected $table         = 'buku_induk_edit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;   // kita isi edited_at manual

    protected $allowedFields = [
        'buku_induk_id',
        'edited_by',
        'section',
        'field_name',
        'field_label',
        'old_value',
        'new_value',
        'edited_at',
    ];

    // ─── Label mapping field_name → label Indonesia ────────────────
    public const FIELD_LABELS = [
        // Pribadi
        'nisn'            => 'NISN',
        'nik'             => 'NIK',
        'nama_lengkap'    => 'Nama Lengkap',
        'nama_panggilan'  => 'Nama Panggilan',
        'tempat_lahir'    => 'Tempat Lahir',
        'tanggal_lahir'   => 'Tanggal Lahir',
        'jenis_kelamin'   => 'Jenis Kelamin',
        'agama'           => 'Agama',
        'kewarganegaraan' => 'Kewarganegaraan',
        'asal_sekolah'    => 'Sekolah Asal',
        'tahun_lulus_smp' => 'Tahun Lulus SMP',
        // Kontak & Ortu
        'alamat'          => 'Alamat Lengkap',
        'no_hp'           => 'No. Telepon',
        'email_siswa'     => 'Email',
        'nama_ayah'       => 'Nama Ayah',
        'pekerjaan_ayah'  => 'Pekerjaan Ayah',
        'no_hp_ayah'      => 'No. HP Ayah',
        'nama_ibu'        => 'Nama Ibu',
        'pekerjaan_ibu'   => 'Pekerjaan Ibu',
        'no_hp_ibu'       => 'No. HP Ibu',
        // Kesehatan
        'golongan_darah'    => 'Golongan Darah',
        'tinggi_badan'      => 'Tinggi Badan',
        'berat_badan'       => 'Berat Badan',
        'riwayat_penyakit'  => 'Riwayat Penyakit',
        'catatan_kesehatan' => 'Catatan Tambahan',
        // Kelas
        'kelas_id'    => 'Kelas',
        'tahun_masuk' => 'Tahun Masuk',
    ];

    /**
     * Rekam batch perubahan field dari satu sesi edit.
     *
     * @param int    $bukuIndukId
     * @param int    $editedBy     user_id admin
     * @param string $section      'Data Pribadi' | 'Data Kesehatan' | 'Penempatan Kelas'
     * @param array  $oldData      nilai lama (assoc field => value)
     * @param array  $newData      nilai baru (assoc field => value)
     * @return int   jumlah field yang berubah & dicatat
     */
    public function recordChanges(
        int    $bukuIndukId,
        int    $editedBy,
        string $section,
        array  $oldData,
        array  $newData
    ): int {
        $rows      = [];
        $timestamp = date('Y-m-d H:i:s');

        foreach ($newData as $field => $newVal) {
            $oldVal = (string) ($oldData[$field] ?? '');
            $newVal = (string) ($newVal ?? '');

            // Lewati jika tidak berubah
            if ($oldVal === $newVal) {
                continue;
            }

            $rows[] = [
                'buku_induk_id' => $bukuIndukId,
                'edited_by'     => $editedBy,
                'section'       => $section,
                'field_name'    => $field,
                'field_label'   => self::FIELD_LABELS[$field] ?? ucwords(str_replace('_', ' ', $field)),
                'old_value'     => $oldVal ?: null,
                'new_value'     => $newVal ?: null,
                'edited_at'     => $timestamp,
            ];
        }

        if (! empty($rows)) {
            $this->insertBatch($rows);
        }

        return count($rows);
    }

    /**
     * Ambil semua log untuk satu buku_induk, terbaru di atas.
     * Join ke users untuk nama admin.
     */
    public function getLogsForSiswa(int $bukuIndukId): array
    {
        return $this
            ->select('buku_induk_edit_logs.*, u.nama_lengkap as editor_name')
            ->join('users u', 'u.id = buku_induk_edit_logs.edited_by', 'left')
            ->where('buku_induk_id', $bukuIndukId)
            ->orderBy('edited_at', 'DESC')
            ->findAll();
    }
}
