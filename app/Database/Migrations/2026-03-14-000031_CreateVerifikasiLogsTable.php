<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel 'verifikasi_logs' — dipakai VerifikasiModel.
 * Field sesuai allowedFields di VerifikasiModel.
 */
class CreateVerifikasiLogsTable extends Migration
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
            'admin_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'aksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'target_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'target_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'data_sebelum' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'data_sesudah' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pendaftaran_id', false, false, 'idx_pendaftaran');
        $this->forge->addKey('admin_id',       false, false, 'idx_admin');
        $this->forge->addForeignKey('pendaftaran_id', 'pendaftaran', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('admin_id',       'users',       'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('verifikasi_logs', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `verifikasi_logs`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('verifikasi_logs', true);
    }
}
