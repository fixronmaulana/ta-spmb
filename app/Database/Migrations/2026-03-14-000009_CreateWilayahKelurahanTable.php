<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahKelurahanTable extends Migration
{
    public function up(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS wilayah_kelurahan (
                id            VARCHAR(10)  NOT NULL,
                kecamatan_id  VARCHAR(6)   NOT NULL,
                nama          VARCHAR(100) NOT NULL,
                created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                FOREIGN KEY (kecamatan_id) REFERENCES wilayah_kecamatan(id) ON DELETE CASCADE,
                INDEX idx_kecamatan (kecamatan_id),
                INDEX idx_nama      (nama)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('wilayah_kelurahan', true);
    }
}
