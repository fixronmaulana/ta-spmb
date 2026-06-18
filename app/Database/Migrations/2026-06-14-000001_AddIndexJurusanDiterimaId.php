<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah index pada pendaftaran.jurusan_diterima_id
 * agar query getCountLulusPerJurusan() lebih cepat.
 *
 * Juga pastikan kolom selected_at ada (untuk mencatat waktu ditetapkan lulus/tidak lulus).
 */
class AddIndexJurusanDiterimaId extends Migration
{
    public function up(): void
    {
        // Tambah index jurusan_diterima_id jika belum ada
        $indexes = $this->db->query("SHOW INDEX FROM `pendaftaran` WHERE Key_name = 'idx_jurusan_diterima'")->getResultArray();
        if (empty($indexes)) {
            $this->db->query("ALTER TABLE `pendaftaran` ADD INDEX `idx_jurusan_diterima` (`jurusan_diterima_id`)");
        }

        // Pastikan kolom selected_at ada
        $fields = $this->db->getFieldNames('pendaftaran');
        if (! in_array('selected_at', $fields)) {
            $this->forge->addColumn('pendaftaran', [
                'selected_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'after' => 'jurusan_diterima_id',
                ],
            ]);
        }
    }

    public function down(): void
    {
        $indexes = $this->db->query("SHOW INDEX FROM `pendaftaran` WHERE Key_name = 'idx_jurusan_diterima'")->getResultArray();
        if (! empty($indexes)) {
            $this->db->query("ALTER TABLE `pendaftaran` DROP INDEX `idx_jurusan_diterima`");
        }
    }
}