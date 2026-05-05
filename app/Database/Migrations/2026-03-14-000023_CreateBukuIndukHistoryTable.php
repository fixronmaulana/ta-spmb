<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBukuIndukHistoryTable extends Migration
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
            'generated_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'generated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'format' => [
                'type'       => 'ENUM',
                'constraint' => ['pdf', 'excel', 'print'],
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'final', 'archived'],
                'default'    => 'draft',
            ],
            'catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('calon_siswa_id', false, false, 'idx_siswa');
        $this->forge->addKey('generated_by',   false, false, 'idx_generated_by');
        $this->forge->addKey('generated_at',   false, false, 'idx_generated_at');
        $this->forge->addKey('format',         false, false, 'idx_format');
        $this->forge->addKey('status',         false, false, 'idx_status');
        $this->forge->addForeignKey('calon_siswa_id', 'calon_siswa', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('generated_by',   'users',       'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('buku_induk_history', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `buku_induk_history`
            MODIFY `generated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('buku_induk_history', true);
    }
}
