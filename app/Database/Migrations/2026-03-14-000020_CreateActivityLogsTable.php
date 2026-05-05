<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLogsTable extends Migration
{
    public function up(): void
    {
        // Raw query karena kolom JSON tidak didukung penuh oleh forge CI4
        $this->db->query("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id              BIGINT UNSIGNED NOT NULL,
                calon_siswa_id       BIGINT UNSIGNED NULL,
                pendaftaran_id       BIGINT UNSIGNED NULL,
                activity_type        VARCHAR(50)     NOT NULL,
                activity_description TEXT            NOT NULL,
                ip_address           VARCHAR(45),
                user_agent           TEXT,
                old_data             JSON,
                new_data             JSON,
                created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE,
                FOREIGN KEY (calon_siswa_id) REFERENCES calon_siswa(id)  ON DELETE SET NULL,
                FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id)  ON DELETE SET NULL,
                INDEX idx_user        (user_id),
                INDEX idx_siswa       (calon_siswa_id),
                INDEX idx_pendaftaran (pendaftaran_id),
                INDEX idx_type        (activity_type),
                INDEX idx_created     (created_at),
                INDEX idx_user_date   (user_id, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('activity_logs', true);
    }
}
