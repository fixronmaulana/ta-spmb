<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * FIXED: Field names disesuaikan dengan DokumenModel allowedFields.
 *
 * Perubahan dari versi lama:
 *  - Hapus FK ke jenis_dokumen (model pakai string, bukan FK)
 *  - Hapus 'jenis_dokumen_id' → ganti dengan 'jenis_dokumen' VARCHAR
 *  - Rename 'original_filename' → 'nama_file_asli'
 *  - Tambah 'nama_file_simpan'
 *  - Rename 'file_size'  → 'ukuran_file'
 *  - Rename 'mime_type'  → 'tipe_mime'
 *  - Rename 'status'     → 'status_verifikasi' (ENUM disesuaikan)
 *  - Rename 'verified_by' → 'diverifikasi_oleh'
 *  - Rename 'verified_at' → 'diverifikasi_pada'
 */
class CreateDokumenPendaftaranTable extends Migration
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
            'jenis_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nama_file_asli' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'nama_file_simpan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'path_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'ukuran_file' => [
                'type' => 'BIGINT',
                'null' => true,
            ],
            'tipe_mime' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'status_verifikasi' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default'    => 'pending',
            ],
            'catatan_verifikasi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'diverifikasi_oleh' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'diverifikasi_pada' => [
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
        $this->forge->addKey('pendaftaran_id',    false, false, 'idx_pendaftaran');
        $this->forge->addKey('jenis_dokumen',     false, false, 'idx_jenis');
        $this->forge->addKey('status_verifikasi', false, false, 'idx_status');
        $this->forge->addForeignKey('pendaftaran_id',    'pendaftaran', 'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('diverifikasi_oleh', 'users',       'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('dokumen_pendaftaran', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `dokumen_pendaftaran`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('dokumen_pendaftaran', true);
    }
}
