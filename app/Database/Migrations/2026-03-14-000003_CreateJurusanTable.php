<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * FIXED: Field names disesuaikan dengan JurusanModel allowedFields dan JurusanSeeder.
 *
 * Perubahan dari versi lama:
 *  - 'kode_jurusan'  → 'kode'      (sesuai JurusanModel & JurusanSeeder)
 *  - 'nama_jurusan'  → 'nama'      (sesuai JurusanModel & JurusanSeeder)
 *  - tambah 'kode_nis'             (sesuai JurusanSeeder)
 *  - tambah 'urutan'               (sesuai JurusanModel orderBy & JurusanSeeder)
 *  - hapus 'bidang_keahlian', 'program_keahlian', 'kompetensi_keahlian' (tidak dipakai model/seeder)
 */
class CreateJurusanTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => false,
            ],
            'kode_nis' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'kuota' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'is_active' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'urutan' => [
                'type'    => 'INT',
                'default' => 0,
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
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('kode',      false, false, 'idx_kode');
        $this->forge->addKey('is_active', false, false, 'idx_active');
        $this->forge->addKey('nama',      false, false, 'idx_nama');
        $this->forge->createTable('jurusan', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `jurusan`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('jurusan', true);
    }
}
