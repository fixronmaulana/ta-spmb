<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Struktur tabel pendaftaran sesuai flow SPMB terbaru.
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
                    'draft',
                    'submitted',
                    'revisi',
                    'verifikasi',
                    'seleksi',
                    'lulus',
                    'tidak_lulus',
                    'daftar_ulang',
                    'siswa_aktif',
                ],
                'default' => 'draft',
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
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
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

        /*
         * PRIMARY KEY
         */
        $this->forge->addKey('id', true);

        /*
         * UNIQUE KEY
         * Satu user hanya boleh memiliki satu pendaftaran.
         * Nomor pendaftaran harus unik.
         */
        $this->forge->addUniqueKey(
            'user_id',
            'uk_pendaftaran_user'
        );

        $this->forge->addUniqueKey(
            'no_pendaftaran',
            'uk_no_pendaftaran'
        );

        /*
         * INDEX
         */
        $this->forge->addKey(
            'periode_id',
            false,
            false,
            'idx_periode'
        );

        $this->forge->addKey(
            'status',
            false,
            false,
            'idx_status'
        );

        $this->forge->addKey(
            'jurusan_pilihan1_id',
            false,
            false,
            'idx_jurusan1'
        );

        /*
         * Foreign Key (aktifkan jika seluruh tabel sudah stabil)
         */
        // $this->forge->addForeignKey(
        //     'user_id',
        //     'users',
        //     'id',
        //     'CASCADE',
        //     'CASCADE'
        // );

        // $this->forge->addForeignKey(
        //     'periode_id',
        //     'periode',
        //     'id',
        //     'SET NULL',
        //     'CASCADE'
        // );

        $this->forge->createTable(
            'pendaftaran',
            true,
            [
                'ENGINE'  => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ]
        );

        $this->db->query("
            ALTER TABLE `pendaftaran`
            MODIFY `created_at`
                TIMESTAMP NOT NULL
                DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at`
                TIMESTAMP NOT NULL
                DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('pendaftaran', true);
    }
}
