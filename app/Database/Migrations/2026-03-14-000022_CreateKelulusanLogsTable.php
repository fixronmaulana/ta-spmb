<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKelulusanLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pendaftaran_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'keputusan' => [
                'type'       => 'ENUM',
                'constraint' => ['lulus', 'tidak_lulus', 'cadangan'],
                'null'       => false,
            ],
            'nilai_tes' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'diputuskan_oleh' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'disetujui_oleh' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
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
        $this->forge->addKey('pendaftaran_id',  false, false, 'idx_pendaftaran');
        $this->forge->addKey('keputusan',       false, false, 'idx_keputusan');
        $this->forge->addKey('diputuskan_oleh', false, false, 'idx_diputuskan');
        $this->forge->addKey('disetujui_oleh',  false, false, 'idx_disetujui');
        $this->forge->addForeignKey('pendaftaran_id',  'pendaftaran', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('diputuskan_oleh', 'users',       'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('disetujui_oleh',  'users',       'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('kelulusan_logs', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `kelulusan_logs`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('kelulusan_logs', true);
    }
}
