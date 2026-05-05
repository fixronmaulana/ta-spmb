<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAlamatSiswaTable extends Migration
{
    public function up(): void
    {
        // Raw query karena FK ke tabel wilayah yang ber-PK VARCHAR
        $this->db->query("
            CREATE TABLE IF NOT EXISTS alamat_siswa (
                id                     BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                calon_siswa_id         BIGINT UNSIGNED NOT NULL,
                alamat_lengkap         TEXT            NOT NULL,
                dusun                  VARCHAR(100),
                rt                     VARCHAR(10),
                rw                     VARCHAR(10),
                kelurahan_id           VARCHAR(10),
                kecamatan_id           VARCHAR(6),
                kabupaten_id           VARCHAR(4),
                provinsi_id            VARCHAR(2),
                kode_pos               VARCHAR(10),
                tingkat_tempat_tinggal ENUM('dusun','desa','kecamatan','kabupaten','provinsi'),
                tinggal_dengan         ENUM('orang_tua','kakek_nenek','asrama','mondok') DEFAULT 'orang_tua',
                jarak_ke_sekolah       ENUM('kurang_1km','lebih_1km'),
                created_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at             TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                FOREIGN KEY (calon_siswa_id) REFERENCES calon_siswa(id)      ON DELETE CASCADE,
                FOREIGN KEY (kelurahan_id)   REFERENCES wilayah_kelurahan(id) ON DELETE SET NULL,
                FOREIGN KEY (kecamatan_id)   REFERENCES wilayah_kecamatan(id) ON DELETE SET NULL,
                FOREIGN KEY (kabupaten_id)   REFERENCES wilayah_kabupaten(id) ON DELETE SET NULL,
                FOREIGN KEY (provinsi_id)    REFERENCES wilayah_provinsi(id)  ON DELETE SET NULL,
                INDEX idx_siswa     (calon_siswa_id),
                INDEX idx_kelurahan (kelurahan_id),
                INDEX idx_kecamatan (kecamatan_id),
                INDEX idx_kabupaten (kabupaten_id),
                INDEX idx_provinsi  (provinsi_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('alamat_siswa', true);
    }
}
