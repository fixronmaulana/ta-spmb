<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Backfill kolom yang kosong di buku_induks
 * dari data_diri_siswas untuk record yang sudah dikonversi.
 *
 * Kolom yang sebelumnya tidak di-copy saat konversi:
 *   - nik
 *   - nama_panggilan
 *   - email_siswa
 *   - pekerjaan_ayah
 *   - pekerjaan_ibu
 *   - asal_sekolah
 *   - tahun_lulus_smp  (dari kolom tahun_lulus di data_diri_siswas)
 *   - no_hp_ibu
 *   - kewarganegaraan
 *
 * Jalankan: php spark migrate
 */
class BackfillBukuIndukFromDataDiri extends Migration
{
    public function up(): void
    {
        // Update semua buku_induk yang punya pendaftaran_id,
        // JOIN ke data_diri_siswas dan isi kolom yang masih NULL.
        $this->db->query("
            UPDATE buku_induks bi
            INNER JOIN data_diri_siswas dds ON dds.pendaftaran_id = bi.pendaftaran_id
            SET
                bi.nik             = COALESCE(NULLIF(bi.nik, ''),             dds.nik),
                bi.nama_panggilan  = COALESCE(NULLIF(bi.nama_panggilan, ''),  dds.nama_panggilan),
                bi.email_siswa     = COALESCE(NULLIF(bi.email_siswa, ''),     dds.email_siswa),
                bi.kewarganegaraan = COALESCE(NULLIF(bi.kewarganegaraan, ''), dds.kewarganegaraan, 'Indonesia'),
                bi.pekerjaan_ayah  = COALESCE(NULLIF(bi.pekerjaan_ayah, ''), dds.pekerjaan_ayah),
                bi.no_hp_ayah      = COALESCE(NULLIF(bi.no_hp_ayah, ''),     dds.no_hp_ortu),
                bi.pekerjaan_ibu   = COALESCE(NULLIF(bi.pekerjaan_ibu, ''),  dds.pekerjaan_ibu),
                bi.no_hp_ibu       = COALESCE(NULLIF(bi.no_hp_ibu, ''),      dds.no_hp_ibu),
                bi.asal_sekolah    = COALESCE(NULLIF(bi.asal_sekolah, ''),   dds.asal_sekolah),
                bi.tahun_lulus_smp = COALESCE(NULLIF(bi.tahun_lulus_smp,''), dds.tahun_lulus),
                bi.updated_at      = NOW()
            WHERE bi.pendaftaran_id IS NOT NULL
        ");

        log_message('info', '[Migration BackfillBukuIndukFromDataDiri] Backfill selesai.');
    }

    public function down(): void
    {
               log_message('info', '[Migration BackfillBukuIndukFromDataDiri] down() dipanggil — tidak ada aksi.');
    }
}