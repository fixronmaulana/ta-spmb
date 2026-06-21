<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $periodes = [
            [
                'nama'                         => 'SPMB 2025/2026 - Gelombang 1',
                'tahun_ajaran'                 => '2025/2026',
                'tanggal_mulai'                => '2026-01-1',
                'tanggal_selesai'              => '2026-03-31',
                'tanggal_pengumuman'           => '2026-07-10',
                'tanggal_daftar_ulang_mulai'   => '2026-07-11',
                'tanggal_daftar_ulang_selesai' => '2026-07-20',
                'is_active'                    => 1,
                'is_published'                 => 0,
                'deskripsi'                    => 'Sistem Penerimaan Murid Baru Tahun Ajaran 2025/2026',
                'created_at'                   => $now,
                'updated_at'                   => $now,
            ],
        ];

        $this->db->table('periode')->insertBatch($periodes);
        echo "PeriodeSeeder: Periode aktif 2025/2026 created.\n";
    }
}
