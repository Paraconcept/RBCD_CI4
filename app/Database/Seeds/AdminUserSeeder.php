<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = 'Admin@2026';
        $hash            = password_hash($defaultPassword, PASSWORD_BCRYPT);

        $this->db->table('admin_users')->insert([
            'last_name'             => 'Administrateur',
            'first_name'            => 'Super',
            'email'                 => 'admin@rbcdisonais.be',
            'password_hash'         => $hash,
            'password_default_hash' => $hash,
            'role'                  => 'superadmin',
            'is_active'             => 1,
        ]);
    }
}
