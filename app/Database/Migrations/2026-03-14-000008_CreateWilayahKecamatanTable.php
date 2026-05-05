<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahKecamatanTable extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS wilayah_kecamatan (
                id           VARCHAR(6)   NOT NULL,
                kabupaten_id VARCHAR(4)   NOT NULL,
                nama         VARCHAR(100) NOT NULL,
                created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (kabupaten_id) REFERENCES wilayah_kabupaten(id) ON DELETE CASCADE,
                INDEX idx_kabupaten (kabupaten_id),
                INDEX idx_nama      (nama)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('wilayah_kecamatan', true);
    }
}
