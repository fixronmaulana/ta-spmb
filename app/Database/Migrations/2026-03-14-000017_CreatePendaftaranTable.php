<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * FIXED: Struktur tabel pendaftaran disesuaikan dengan PendaftaranModel.
 *
 * Perubahan dari versi lama:
 *  - Hapus foreign key ke calon_siswa & gelombang (tidak ada tabel itu lagi di flow baru)
 *  - Ganti 'calon_siswa_id' → 'user_id' (FK ke users)
 *  - Ganti 'gelombang_id'   → 'periode_id' (FK ke periode)
 *  - Hapus 'jurusan_id' tunggal → pisah jadi 'jurusan_pilihan1_id' & 'jurusan_pilihan2_id' & 'jurusan_diterima_id'
 *  - Tambah: 'no_pendaftaran', 'step_terakhir', 'data_draft', 'catatan_admin',
 *            'alasan_penolakan', 'submitted_at', 'verified_at', 'verified_by',
 *            'selected_at', 'nilai_seleksi', 'keterangan_seleksi', 'approved_by', 'approved_at', 'deleted_at'
 *  - Status enum diperluas sesuai flow SPMB nyata
 */
class CreatePendaftaranTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => false,
            ],
            'periode_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'no_pendaftaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
            ],
            'jurusan_pilihan1_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'jurusan_pilihan2_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'jurusan_diterima_id' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'draft', 'submitted', 'verifikasi', 'seleksi',
                    'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif',
                ],
                'default'    => 'draft',
            ],
            'step_terakhir' => [
                'type'    => 'TINYINT',
                'default' => 1,
            ],
            'data_draft' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'catatan_admin' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'alasan_penolakan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verified_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'verified_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'selected_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'nilai_seleksi' => [
                'type'    => 'DECIMAL',
                'constraint' => '5,2',
                'null'    => true,
            ],
            'keterangan_seleksi' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'approved_by' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'approved_at' => [
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id',              false, false, 'idx_user');
        $this->forge->addKey('periode_id',           false, false, 'idx_periode');
        $this->forge->addKey('status',               false, false, 'idx_status');
        $this->forge->addKey('no_pendaftaran',       false, false, 'idx_no_pendaftaran');
        $this->forge->addKey('jurusan_pilihan1_id',  false, false, 'idx_jurusan1');
        // $this->forge->addForeignKey('user_id',    'users',   'id', 'CASCADE',  'CASCADE');
        // $this->forge->addForeignKey('periode_id','periode','id','SET NULL','CASCADE');
        $this->forge->createTable('pendaftaran', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        $this->db->query("
            ALTER TABLE `pendaftaran`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('pendaftaran', true);
    }
}
