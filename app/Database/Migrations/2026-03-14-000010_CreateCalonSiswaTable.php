<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalonSiswaTable extends Migration
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
            'no_pendaftaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'nama_lengkap' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'nama_panggilan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'nik' => [
                'type'       => 'VARCHAR',
                'constraint' => 16,
                'null'       => true,
            ],
            'nisn' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'jenis_kelamin' => [
                'type'       => 'ENUM',
                'constraint' => ['L', 'P'],
                'null'       => false,
            ],
            'tempat_lahir' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'tanggal_lahir' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'agama' => [
                'type'       => 'ENUM',
                'constraint' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'],
                'null'       => false,
            ],
            'kewarganegaraan' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'Indonesia',
            ],
            'status_dalam_keluarga' => [
                'type'       => 'ENUM',
                'constraint' => ['anak_kandung', 'anak_angkat', 'yatim', 'piatu', 'yatim_piatu', 'anak_tiri'],
                'null'       => true,
            ],
            'jumlah_saudara' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'anak_ke' => [
                'type'    => 'INT',
                'default' => 1,
            ],
            'bahasa_sehari_hari' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'no_telp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'foto_profil' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
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
        $this->forge->addUniqueKey('no_pendaftaran');
        $this->forge->addUniqueKey('nik');
        $this->forge->addUniqueKey('nisn');
        $this->forge->addKey('user_id',        false, false, 'idx_user');
        $this->forge->addKey('nik',            false, false, 'idx_nik');
        $this->forge->addKey('nisn',           false, false, 'idx_nisn');
        $this->forge->addKey('nama_lengkap',   false, false, 'idx_nama');
        $this->forge->addKey('no_pendaftaran', false, false, 'idx_no_pendaftaran');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('calon_siswa', true, [
            'ENGINE'  => 'InnoDB',
            'CHARSET' => 'utf8mb4',
            'COLLATE' => 'utf8mb4_unicode_ci',
        ]);
        // Fix timestamps: ALTER setelah createTable agar DEFAULT CURRENT_TIMESTAMP bekerja
        $this->db->query("
            ALTER TABLE `calon_siswa`
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        $this->forge->dropTable('calon_siswa', true);
    }
}
