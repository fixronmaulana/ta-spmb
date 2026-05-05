<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: Pastikan kolom status_anak, dusun, dan alamat_sekolah
 * ada di tabel data_diri_siswas menggunakan raw SQL murni.
 *
 * FILE: app/Database/Migrations/2026-04-10-000002_FixDataDiriSiswaColumns.php
 *
 * Jalankan dengan:
 *   php spark migrate
 */
class FixDataDiriSiswaColumns extends Migration
{
    public function up(): void
    {
        // Ambil kolom yang sudah ada via raw SQL — paling kompatibel
        $existingFields = $this->getExistingColumns('data_diri_siswas');

        // ── dusun ──────────────────────────────────────────────────
        if (! in_array('dusun', $existingFields)) {
            $this->db->query("
                ALTER TABLE `data_diri_siswas`
                ADD COLUMN `dusun` VARCHAR(100) NULL AFTER `alamat`
            ");
            log_message('info', '[Migration] Kolom dusun ditambahkan ke data_diri_siswas.');
        }

        // ── status_anak ────────────────────────────────────────────
        if (! in_array('status_anak', $existingFields)) {
            $this->db->query("
                ALTER TABLE `data_diri_siswas`
                ADD COLUMN `status_anak` VARCHAR(50) NULL AFTER `jumlah_saudara`
            ");
            log_message('info', '[Migration] Kolom status_anak ditambahkan ke data_diri_siswas.');
        }

        // ── alamat_sekolah ─────────────────────────────────────────
        if (! in_array('alamat_sekolah', $existingFields)) {
            $this->db->query("
                ALTER TABLE `data_diri_siswas`
                ADD COLUMN `alamat_sekolah` VARCHAR(255) NULL AFTER `asal_sekolah`
            ");
            log_message('info', '[Migration] Kolom alamat_sekolah ditambahkan ke data_diri_siswas.');
        }
    }

    public function down(): void
    {
        $existingFields = $this->getExistingColumns('data_diri_siswas');

        foreach (['dusun', 'status_anak', 'alamat_sekolah'] as $col) {
            if (in_array($col, $existingFields)) {
                $this->db->query("ALTER TABLE `data_diri_siswas` DROP COLUMN `{$col}`");
                log_message('info', "[Migration] Kolom {$col} dihapus dari data_diri_siswas.");
            }
        }
    }

    /**
     * Ambil daftar nama kolom dari tabel menggunakan SHOW COLUMNS.
     * Lebih aman daripada getFieldNames() di dalam konteks Migration.
     */
    private function getExistingColumns(string $table): array
    {
        $query  = $this->db->query("SHOW COLUMNS FROM `{$table}`");
        $rows   = $query->getResultArray();
        $fields = [];

        foreach ($rows as $row) {
            $fields[] = $row['Field'];
        }

        return $fields;
    }
}