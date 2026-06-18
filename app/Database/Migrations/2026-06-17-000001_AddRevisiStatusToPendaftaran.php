<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Menambahkan status 'revisi' pada ENUM kolom status
 * tabel pendaftaran.
 *
 * FILE:
 * app/Database/Migrations/2026-06-17-000001_AddRevisiStatusToPendaftaran.php
 *
 * Jalankan:
 *   php spark migrate
 *
 * Rollback:
 *   php spark migrate:rollback
 */
class AddRevisiStatusToPendaftaran extends Migration
{
    public function up(): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        $db->query("
            ALTER TABLE pendaftaran
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'verifikasi',
                'revisi',
                'seleksi',
                'lulus',
                'tidak_lulus',
                'daftar_ulang',
                'siswa_aktif'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        /*
         * Pastikan tidak ada data yang masih menggunakan
         * status 'revisi' sebelum rollback.
         */
        $db->query("
            UPDATE pendaftaran
            SET status = 'submitted'
            WHERE status = 'revisi'
        ");

        $db->query("
            ALTER TABLE pendaftaran
            MODIFY COLUMN status ENUM(
                'draft',
                'submitted',
                'verifikasi',
                'seleksi',
                'lulus',
                'tidak_lulus',
                'daftar_ulang',
                'siswa_aktif'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
}