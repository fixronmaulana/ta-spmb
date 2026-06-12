<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $jurusans   = $this->db->table('jurusan')->get()->getResultArray();
        $jurusanMap = array_column($jurusans, 'id', 'kode');

        if (empty($jurusanMap)) {
            echo "KelasSeeder ERROR: Tabel jurusan kosong. Jalankan JurusanSeeder dulu.\n";
            return;
        }

        $kelasConfig = [
            'TJKT' => ['X TJKT 1', 'X TJKT 2'],
            'AK'   => ['X AK 1', 'X AK 2'],
            'ATU'  => ['X ATU 1', 'X ATU 2'],
            'DPIB' => ['X DPIB 1', 'X DPIB 2'],
            'DKV'  => ['X DKV 1', 'X DKV 2'],
        ];

        $kelas = [];

        foreach ($kelasConfig as $kode => $namaList) {
            if (!isset($jurusanMap[$kode])) {
                echo "KelasSeeder WARNING: Jurusan dengan kode '{$kode}' tidak ditemukan, dilewati.\n";
                continue;
            }

            foreach ($namaList as $namaKelas) {
                $kelas[] = [
                    'jurusan_id' => $jurusanMap[$kode],
                    'nama'       => $namaKelas,
                    'tingkat'    => 'X',
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