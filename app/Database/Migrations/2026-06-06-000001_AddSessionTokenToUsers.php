<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSessionTokenToUsers extends Migration
{
    public function up(): void
    {
        // Tambah kolom session_token untuk kontrol single active session
        $this->forge->addColumn('users', [
            'session_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'default'    => null,
                'after'      => 'remember_token',
            ],
        ]);

        // Index untuk mempercepat lookup session_token
        $this->db->query('ALTER TABLE `users` ADD INDEX `idx_session_token` (`session_token`)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('users', 'session_token');
    }
}