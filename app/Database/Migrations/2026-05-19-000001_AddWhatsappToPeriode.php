<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi: Tambah kolom WA Grup & WA CP ke tabel periode.
 *
 * * Kolom:
 *   wa_grup_link  VARCHAR(500) NULL — link join grup WA pendaftar per-periode
 *   wa_cp_no      VARCHAR(20)  NULL — nomor WA contact person panitia per-periode
 */
class AddWhatsappToPeriode extends Migration
{
    public function up(): void
    {
        $fields = [
            'wa_grup_link' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Link grup WhatsApp pendaftar untuk periode ini',
                'after'      => 'deskripsi',
            ],
            'wa_cp_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Nomor WA contact person panitia (format: 081234567890)',
                'after'      => 'wa_grup_link',
            ],
        ];

        $this->forge->addColumn('periode', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('periode', ['wa_grup_link', 'wa_cp_no']);
    }
}