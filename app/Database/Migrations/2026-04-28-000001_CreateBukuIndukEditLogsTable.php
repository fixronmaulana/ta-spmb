<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel buku_induk_edit_logs
 * Menyimpan setiap perubahan field pada buku_induks (per-field diff).
 *
 * Satu baris = satu field yang berubah.
 * section : 'Data Pribadi' | 'Data Kesehatan' | 'Penempatan Kelas'
 */
class CreateBukuIndukEditLogsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'buku_induk_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'edited_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'section' => [
                'type'       => 'ENUM',
                'constraint' => ['Data Pribadi', 'Data Kesehatan', 'Penempatan Kelas'],
                'null'       => false,
            ],
            'field_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => false,
                'comment'    => 'Nama kolom DB yang berubah',
            ],
            'field_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'comment'    => 'Label display Indonesia',
            ],
            'old_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'new_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'edited_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('buku_induk_id', false, false, 'idx_bi_id');
        $this->forge->addKey('edited_by',     false, false, 'idx_edited_by');
        $this->forge->addKey('edited_at',     false, false, 'idx_edited_at');
        $this->forge->addKey('section',       false, false, 'idx_section');
        $this->forge->addForeignKey('buku_induk_id', 'buku_induks', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('edited_by',     'users',       'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('buku_induk_edit_logs', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);

        $this->db->query("
            ALTER TABLE `buku_induk_edit_logs`
            MODIFY `edited_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('buku_induk_edit_logs', true);
    }
}
