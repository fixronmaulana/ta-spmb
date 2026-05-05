<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel orang_tua — side-by-side layout (Ayah | Ibu dalam 1 record).
 * Sesuai desain form mockup: kolom Ayah dan Ibu berdampingan.
 */
class CreateOrangTuaTable extends Migration
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

            // ── DATA AYAH ─────────────────────────────────────
            'nama_ayah' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nik_ayah' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'pendidikan_ayah' => [
                'type'       => 'ENUM',
                'constraint' => $pendidikanEnum,
                'null'       => true,
            ],
            'pekerjaan_ayah' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'penghasilan_ayah' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'no_telp_ayah' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'status_ayah' => [
                'type'       => 'ENUM',
                'constraint' => ['hidup', 'meninggal'],
                'default'    => 'hidup',
            ],

            // ── DATA IBU ──────────────────────────────────────
            'nama_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nik_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'pendidikan_ibu' => [
                'type'       => 'ENUM',
                'constraint' => $pendidikanEnum,
                'null'       => true,
            ],
            'pekerjaan_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'penghasilan_ibu' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'no_telp_ibu' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'status_ibu' => [
                'type'       => 'ENUM',
                'constraint' => ['hidup', 'meninggal'],
                'default'    => 'hidup',
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
        $this->forge->createTable('orang_tua', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
            'COMMENT' => 'Side-by-side form: Ayah | Ibu fields in same record for simpler mapping',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `orang_tua`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('orang_tua', true);
    }
}
