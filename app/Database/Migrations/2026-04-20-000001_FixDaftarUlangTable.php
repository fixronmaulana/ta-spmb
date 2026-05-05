<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * FIX migrasi daftar_ulangs:
 *  1. ENUM status: tambah 'pending' (alias menunggu) dan ubah ENUM
 *     agar konsisten. Nilai lama 'menunggu' di-migrate ke 'pending'.
 *  2. Tambah kolom nis          — NIS yang ditetapkan admin saat konfirmasi
 *  3. Tambah kolom nama_kelas   — nama kelas yang ditetapkan admin
 *  4. Tambah kolom nama_file_bukti — nama asli file bukti pembayaran
 */
class FixDaftarUlangTable extends Migration
{
    public function up(): void
    {
        // 1. Ubah ENUM status menjadi VARCHAR agar fleksibel
        //    (ENUM sulit di-ALTER di beberapa versi MySQL)
        $this->db->query("
            ALTER TABLE `daftar_ulangs`
            MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'pending'
        ");

        // 2. Migrate nilai lama 'menunggu' → 'pending'
        $this->db->query("UPDATE `daftar_ulangs` SET `status` = 'pending' WHERE `status` = 'menunggu'");

        // 3. Tambah kolom nis jika belum ada
        $fields = $this->db->getFieldNames('daftar_ulangs');

        if (! in_array('nis', $fields)) {
            $this->forge->addColumn('daftar_ulangs', [
                'nis' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'kelas_id',
                ],
            ]);
        }

        if (! in_array('nama_kelas', $fields)) {
            $this->forge->addColumn('daftar_ulangs', [
                'nama_kelas' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'nis',
                ],
            ]);
        }

        if (! in_array('nama_file_bukti', $fields)) {
            $this->forge->addColumn('daftar_ulangs', [
                'nama_file_bukti' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'bukti_pembayaran_path',
                ],
            ]);
        }
    }

    public function down(): void
    {
        // Kembalikan ENUM lama
        $this->db->query("
            ALTER TABLE `daftar_ulangs`
            MODIFY COLUMN `status` ENUM('pending','dikonfirmasi','ditolak') NOT NULL DEFAULT 'pending'
        ");

        // Drop kolom tambahan
        $this->forge->dropColumn('daftar_ulangs', 'nis');
        $this->forge->dropColumn('daftar_ulangs', 'nama_kelas');
        $this->forge->dropColumn('daftar_ulangs', 'nama_file_bukti');
    }
}