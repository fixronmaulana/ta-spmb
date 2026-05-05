<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FIXED:
 *  - 'jurusans' → 'jurusan'  (sesuai migration CreateJurusanTable)
 *  - Ambil berdasarkan kolom 'kode' bukan 'kode_jurusan'
 */
class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // FIXED: 'jurusans' → 'jurusan', kolom 'kode' bukan 'kode_jurusan'
        $jurusans   = $this->db->table('jurusan')->get()->getResultArray();
        $jurusanMap = array_column($jurusans, 'id', 'kode');

        if (empty($jurusanMap)) {
            echo "KelasSeeder ERROR: Tabel jurusan kosong. Jalankan JurusanSeeder dulu.\n";
            return;
        }

        $kelas    = [];
        $tingkats = ['X', 'XI', 'XII'];

        foreach ($jurusanMap as $kode => $jurusanId) {
            foreach ($tingkats as $tingkat) {
                $kelas[] = [
                    'jurusan_id' => $jurusanId,
                    'nama'       => $tingkat . ' ' . $kode . ' 1',
                    'tingkat'    => $tingkat,
                    'kapasitas'  => 36,
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->db->table('kelas')->insertBatch($kelas);
        echo "KelasSeeder: " . count($kelas) . " kelas created.\n";
    }
}
