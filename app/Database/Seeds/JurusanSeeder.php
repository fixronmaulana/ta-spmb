<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $jurusans = [
            [
                'kode'       => 'TJKT',
                'kode_nis'   => '01',
                'nama'       => 'Teknik Jaringan Komputer dan Telekomunikasi',
                'deskripsi'  => 'Jurusan yang mempelajari jaringan komputer, telekomunikasi, dan infrastruktur teknologi informasi.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'AK',
                'kode_nis'   => '02',
                'nama'       => 'Akuntansi',
                'deskripsi'  => 'Jurusan yang mempelajari akuntansi, pembukuan, dan pengelolaan keuangan.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'ATU',
                'kode_nis'   => '03',
                'nama'       => 'Agribisnis Ternak Unggas',
                'deskripsi'  => 'Jurusan yang mempelajari budidaya, manajemen, dan agribisnis peternakan unggas.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'DPIB',
                'kode_nis'   => '04',
                'nama'       => 'Desain Pemodelan dan Informasi Bangunan',
                'deskripsi'  => 'Jurusan yang mempelajari desain arsitektur, pemodelan bangunan, dan teknologi konstruksi.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'DKV',
                'kode_nis'   => '05',
                'nama'       => 'Desain Komunikasi dan Visual',
                'deskripsi'  => 'Jurusan yang mempelajari desain grafis, komunikasi visual, dan media kreatif.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('jurusan')->insertBatch($jurusans);
        echo "JurusanSeeder: " . count($jurusans) . " jurusan created.\n";
    }
}
