<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Tambah kolom alamat_sekolah ke tabel data_diri_siswas
 *
 * FILE: app/Database/Migrations/2026-04-09-000002_AddAlamatSekolahToDataDiriSiswa.php
 *
 * Jalankan dengan:
 *   php spark migrate
 *
 * Rollback dengan:
 *   php spark migrate:rollback
 */
class AddAlamatSekolahToDataDiriSiswa extends Migration
{
    public function up(): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        $fields = $db->getFieldNames('data_diri_siswas');

        if (! in_array('alamat_sekolah', $fields)) {
            $this->forge->addColumn('data_diri_siswas', [
                'alamat_sekolah' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'asal_sekolah', // letakkan setelah kolom asal_sekolah
                ],
            ]);
        }
    }

    public function down(): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        $fields = $db->getFieldNames('data_diri_siswas');

        if (in_array('alamat_sekolah', $fields)) {
            $this->forge->dropColumn('data_diri_siswas', 'alamat_sekolah');
        }
    }
}
