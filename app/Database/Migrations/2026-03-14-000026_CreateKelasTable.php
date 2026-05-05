<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel 'kelas' — dipakai KelasModel & KelasSeeder.
 * Field sesuai KelasModel allowedFields: jurusan_id, nama, tingkat, kapasitas, wali_kelas, is_active
 */
class CreateKelasTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'jurusan_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'tingkat' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
                'comment'    => 'X, XI, XII',
            ],
            'kapasitas' => [
                'type'    => 'INT',
                'default' => 36,
            ],
            'wali_kelas' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
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
        $this->forge->addKey('jurusan_id', false, false, 'idx_jurusan');
        $this->forge->addKey('tingkat',    false, false, 'idx_tingkat');
        $this->forge->addKey('is_active',  false, false, 'idx_active');
        $this->forge->addForeignKey('jurusan_id', 'jurusan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kelas', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `kelas`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('kelas', true);
    }
}
