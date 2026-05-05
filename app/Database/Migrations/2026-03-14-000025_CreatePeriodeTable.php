<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel 'periode' — dipakai PeriodeModel.
 * Root cause error 500 di landing page.
 */
class CreatePeriodeTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'tahun_ajaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tanggal_pengumuman' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_daftar_ulang_mulai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'tanggal_daftar_ulang_selesai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'is_published' => [
                'type'    => 'TINYINT',
                'default' => 0,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('is_active',    false, false, 'idx_active');
        $this->forge->addKey('tahun_ajaran', false, false, 'idx_tahun');
        $this->forge->createTable('periode', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `periode`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('periode', true);
    }
}
