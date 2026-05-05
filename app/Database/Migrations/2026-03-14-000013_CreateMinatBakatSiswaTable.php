<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMinatBakatSiswaTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'calon_siswa_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'kesenian' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'olahraga' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'organisasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
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
        $this->forge->addKey('calon_siswa_id', false, false, 'idx_siswa');
        $this->forge->addForeignKey('calon_siswa_id', 'calon_siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('minat_bakat_siswa', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `minat_bakat_siswa`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('minat_bakat_siswa', true);
    }
}
