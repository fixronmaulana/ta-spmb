<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahProvinsiTable extends Migration
{
    public function up(): void
    {
        // Menggunakan raw query karena PK bertipe VARCHAR(2) — kode BPS
        $this->db->query("
            CREATE TABLE IF NOT EXISTS wilayah_provinsi (
                id         VARCHAR(2)   NOT NULL,
                nama       VARCHAR(100) NOT NULL,
                created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_nama (nama)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('wilayah_provinsi', true);
    }
}
