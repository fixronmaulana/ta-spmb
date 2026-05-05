<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJenisDokumenTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'nama_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_wajib' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'urutan' => [
                'type'    => 'INT',
                'default' => 0,
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
        $this->forge->addUniqueKey('kode');
        $this->forge->addKey('kode',      false, false, 'idx_kode');
        $this->forge->addKey('is_active', false, false, 'idx_active');
        $this->forge->addKey('urutan',    false, false, 'idx_urutan');
        $this->forge->createTable('jenis_dokumen', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `jenis_dokumen`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");

        // Seed initial jenis dokumen
        $this->db->table('jenis_dokumen')->insertBatch([
            ['kode' => 'ijazah',   'nama_dokumen' => 'Ijazah Terakhir',              'keterangan' => 'Scan/foto ijazah SMP atau sederajat',        'is_wajib' => 1, 'is_active' => 1, 'urutan' => 1],
            ['kode' => 'skhun',    'nama_dokumen' => 'SKHUN',                        'keterangan' => 'Surat Keterangan Hasil Ujian Nasional',       'is_wajib' => 0, 'is_active' => 1, 'urutan' => 2],
            ['kode' => 'kk',       'nama_dokumen' => 'Kartu Keluarga (KK)',          'keterangan' => 'Scan KK yang masih berlaku',                 'is_wajib' => 1, 'is_active' => 1, 'urutan' => 3],
            ['kode' => 'akte',     'nama_dokumen' => 'Akta Kelahiran',               'keterangan' => 'Scan akta kelahiran siswa',                  'is_wajib' => 1, 'is_active' => 1, 'urutan' => 4],
            ['kode' => 'foto',     'nama_dokumen' => 'Pas Foto',                     'keterangan' => 'Pas foto 3x4 dengan latar belakang merah',   'is_wajib' => 1, 'is_active' => 1, 'urutan' => 5],
            ['kode' => 'raport',   'nama_dokumen' => 'Rapor Semester Terakhir',      'keterangan' => 'Scan rapor semester 5 dan 6',                'is_wajib' => 0, 'is_active' => 1, 'urutan' => 6],
            ['kode' => 'prestasi', 'nama_dokumen' => 'Sertifikat Prestasi',          'keterangan' => 'Sertifikat prestasi (jika ada)',             'is_wajib' => 0, 'is_active' => 1, 'urutan' => 7],
            ['kode' => 'kip',      'nama_dokumen' => 'Kartu Indonesia Pintar (KIP)', 'keterangan' => 'Kartu KIP/PKH (jika memiliki)',              'is_wajib' => 0, 'is_active' => 1, 'urutan' => 8],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropTable('jenis_dokumen', true);
    }
}
