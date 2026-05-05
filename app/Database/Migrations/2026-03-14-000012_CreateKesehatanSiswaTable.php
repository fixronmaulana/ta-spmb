<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKesehatanSiswaTable extends Migration
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
            'golongan_darah' => [
                'type'       => 'ENUM',
                'constraint' => ['A', 'B', 'AB', 'O', 'tidak_tahu'],
                'null'       => true,
            ],
            'tinggi_badan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'berat_badan' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
            ],
            'riwayat_penyakit' => [
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
        $this->forge->addKey('calon_siswa_id', false, false, 'idx_siswa');
        $this->forge->addForeignKey('calon_siswa_id', 'calon_siswa', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kesehatan_siswa', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `kesehatan_siswa`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('kesehatan_siswa', true);
    }
}
