<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel 'daftar_ulangs' — dipakai DaftarUlangModel.
 * Field sesuai allowedFields di DaftarUlangModel.
 */
class CreateDaftarUlangTable extends Migration
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
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'kelas_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'bukti_pembayaran_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'nominal_pembayaran' => [
                'type'    => 'BIGINT',
                'null'    => true,
            ],
            'catatan_siswa' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'dikonfirmasi', 'ditolak'],
                'default'    => 'pending',
            ],
            'catatan_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'dikonfirmasi_oleh' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'dikonfirmasi_pada' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey('pendaftaran_id', false, false, 'idx_pendaftaran');
        $this->forge->addKey('user_id',        false, false, 'idx_user');
        $this->forge->addKey('status',         false, false, 'idx_status');
        $this->forge->addForeignKey('pendaftaran_id',    'pendaftaran', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('user_id',           'users',       'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('kelas_id',          'kelas',       'id', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('dikonfirmasi_oleh', 'users',       'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('daftar_ulangs', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `daftar_ulangs`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('daftar_ulangs', true);
    }
}
