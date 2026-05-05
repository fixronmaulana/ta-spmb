<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Tambah kolom dusun dan status_anak ke tabel data_diri_siswas
 *
 * FILE: app/Database/Migrations/2026-04-09-000001_AddMissingColumnsToDataDiriSiswa.php
 *
 * Jalankan dengan:
 *   php spark migrate
 */
class AddMissingColumnsToDataDiriSiswa extends Migration
{
    public function up(): void
    {
        // Cek apakah kolom sudah ada sebelum ditambahkan (aman untuk dijalankan ulang)
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        $fields = $db->getFieldNames('data_diri_siswas');

        if (! in_array('dusun', $fields)) {
            $this->forge->addColumn('data_diri_siswas', [
                'dusun' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'alamat',   // letakkan setelah kolom alamat
                ],
            ]);
        }

        if (! in_array('status_anak', $fields)) {
            $this->forge->addColumn('data_diri_siswas', [
                'status_anak' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'jumlah_saudara',
                ],
            ]);
        }
    }

    public function down(): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        $fields = $db->getFieldNames('data_diri_siswas');

        if (in_array('dusun', $fields)) {
            $this->forge->dropColumn('data_diri_siswas', 'dusun');
        }

        if (in_array('status_anak', $fields)) {
            $this->forge->dropColumn('data_diri_siswas', 'status_anak');
        }
    }
}
