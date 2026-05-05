<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nama_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'description' => [
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
        $this->forge->addUniqueKey('nama_role');
        $this->forge->addKey('nama_role', false, false, 'idx_nama_role');
        $this->forge->createTable('roles', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `roles`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");

        // Seed initial roles
        $this->db->table('roles')->insertBatch([
            ['nama_role' => 'admin_tu', 'description' => 'Admin Tata Usaha untuk verifikasi dan manajemen pendaftaran'],
            ['nama_role' => 'kepala_sekolah', 'description' => 'Kepala Sekolah untuk akses laporan'],
            ['nama_role' => 'calon_siswa', 'description' => 'Siswa/Calon Siswa'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('roles', true);
    }
}
