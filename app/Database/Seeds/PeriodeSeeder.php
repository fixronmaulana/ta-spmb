<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FIXED: 'periodes' → 'periode' (sesuai migration CreatePeriodeTable)
 */
class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $periodes = [
            [
                'nama'                         => 'SPMB 2025/2026',
                'tahun_ajaran'                 => '2025/2026',
                'tanggal_mulai'                => '2025-01-15',
                'tanggal_selesai'              => '2025-06-30',
                'tanggal_pengumuman'           => '2025-07-10',
                'tanggal_daftar_ulang_mulai'   => '2025-07-11',
                'tanggal_daftar_ulang_selesai' => '2025-07-20',
                'is_active'                    => 1,
                'is_published'                 => 0,
                'deskripsi'                    => 'Sistem Penerimaan Murid Baru Tahun Ajaran 2025/2026',
                'created_at'                   => $now,
                'updated_at'                   => $now,
            ],
        ];

        // FIXED: 'periodes' → 'periode'
        $this->db->table('periode')->insertBatch($periodes);
        echo "PeriodeSeeder: Periode aktif 2025/2026 created.\n";
    }
}
