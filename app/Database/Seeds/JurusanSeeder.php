<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FIXED: 'jurusans' → 'jurusan' (sesuai migration CreateJurusanTable)
 */
class JurusanSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $jurusans = [
            [
                'kode'       => 'RPL',
                'kode_nis'   => '01',
                'nama'       => 'Rekayasa Perangkat Lunak',
                'deskripsi'  => 'Jurusan yang mempelajari pengembangan software, pemrograman, dan sistem informasi.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'TKJ',
                'kode_nis'   => '02',
                'nama'       => 'Teknik Komputer dan Jaringan',
                'deskripsi'  => 'Jurusan yang mempelajari jaringan komputer, administrasi sistem, dan keamanan jaringan.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'AKL',
                'kode_nis'   => '03',
                'nama'       => 'Akuntansi dan Keuangan Lembaga',
                'deskripsi'  => 'Jurusan yang mempelajari akuntansi, keuangan, dan manajemen bisnis.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'kode'       => 'BDP',
                'kode_nis'   => '04',
                'nama'       => 'Bisnis Daring dan Pemasaran',
                'deskripsi'  => 'Jurusan yang mempelajari pemasaran digital, e-commerce, dan bisnis online.',
                'kuota'      => 36,
                'is_active'  => 1,
                'urutan'     => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // FIXED: 'jurusans' → 'jurusan'
        $this->db->table('jurusan')->insertBatch($jurusans);
        echo "JurusanSeeder: " . count($jurusans) . " jurusan created.\n";
    }
}
