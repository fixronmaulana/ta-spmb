<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah kolom no_hp_ibu ke tabel data_diri_siswas.
 *
 * BUG FIX: Sebelumnya step 3 hanya menyimpan no_hp_ortu (nomor ayah).
 * Kolom no_hp_ibu belum ada sehingga nomor HP ibu tidak pernah tersimpan ke DB.
 */
class AddNoHpIbuToDataDiriSiswa extends Migration
{
    public function up(): void
    {
        // Tambah kolom no_hp_ibu setelah no_hp_ortu
        $this->db->query("
            ALTER TABLE `data_diri_siswas`
            ADD COLUMN `no_hp_ibu` VARCHAR(20) NULL DEFAULT NULL
            AFTER `no_hp_ortu`
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE `data_diri_siswas`
            DROP COLUMN IF EXISTS `no_hp_ibu`
        ");
    }
}