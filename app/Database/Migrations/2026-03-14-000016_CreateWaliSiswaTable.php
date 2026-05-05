<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel wali_siswa — opsional, hanya untuk siswa yatim/piatu
 * yang memiliki wali selain orang tua kandung.
 */
class CreateWaliSiswaTable extends Migration
{
    public function up(): void
    {
        $pendidikanEnum = [
            'tidak_tamat_sd', 'sd', 'smp', 'sma',
            'd1', 'd2', 'd3', 's1', 's2', 's3',
        ];

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
            'nama_wali' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nik_wali' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'hubungan' => [
                'type'       => 'ENUM',
                'constraint' => ['kakek', 'nenek', 'paman', 'bibi', 'saudara', 'lainnya'],
                'null'       => false,
            ],
            'pendidikan_wali' => [
                'type'       => 'ENUM',
                'constraint' => $pendidikanEnum,
                'null'       => true,
            ],
            'pekerjaan_wali' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'penghasilan_wali' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'no_telp_wali' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'alamat_wali' => [
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
        $this->forge->createTable('wali_siswa', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => 'Optional: Only for students with guardian (separate form/modal)',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `wali_siswa`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('wali_siswa', true);
    }
}
