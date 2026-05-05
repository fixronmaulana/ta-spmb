<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * FIXED:
 *  - 'name'  → 'nama_lengkap'  (sesuai users migration & UserModel)
 *  - 'role'  → 'role_id'       (FK ke tabel roles, bukan string langsung)
 *  - Tambah 'username' (required di users migration)
 *  - Lookup role_id dari tabel roles berdasarkan nama_role
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Ambil role_id dari tabel roles
        $roles = $this->db->table('roles')->get()->getResultArray();
        $roleMap = array_column($roles, 'id', 'nama_role');

        if (empty($roleMap)) {
            echo "UserSeeder ERROR: Tabel roles kosong. Jalankan migration dulu.\n";
            return;
        }

        $users = [
            [
                'role_id'           => $roleMap['admin_tu'] ?? 1,
                'username'          => 'admin_tu',
                'nama_lengkap'      => 'Administrator TU',
                'email'             => 'admin@almunawiir.sch.id',
                'password'          => password_hash('Admin@12345', PASSWORD_BCRYPT, ['cost' => 12]),
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'role_id'           => $roleMap['kepala_sekolah'] ?? 2,
                'username'          => 'kepala_sekolah',
                'nama_lengkap'      => 'Drs. H. Ahmad Fauzi, M.Pd',
                'email'             => 'kepsek@almunawiir.sch.id',
                'password'          => password_hash('Kepsek@12345', PASSWORD_BCRYPT, ['cost' => 12]),
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
            [
                'role_id'           => $roleMap['calon_siswa'] ?? 3,
                'username'          => 'siswa_demo',
                'nama_lengkap'      => 'Ahmad Fauzan Hakim',
                'email'             => 'siswa1@demo.com',
                'password'          => password_hash('Siswa@12345', PASSWORD_BCRYPT, ['cost' => 12]),
                'is_active'         => 1,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ],
        ];

        $this->db->table('users')->insertBatch($users);

        echo "UserSeeder: " . count($users) . " users created.\n";
        echo "Admin    → admin@almunawiir.sch.id / Admin@12345\n";
        echo "Kepsek   → kepsek@almunawiir.sch.id / Kepsek@12345\n";
        echo "Demo     → siswa1@demo.com / Siswa@12345\n";
    }
}
