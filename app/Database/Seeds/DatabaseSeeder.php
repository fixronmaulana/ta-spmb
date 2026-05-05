<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call('UserSeeder');
        $this->call('PeriodeSeeder');
        $this->call('JurusanSeeder');
        $this->call('KelasSeeder');
    }
}
