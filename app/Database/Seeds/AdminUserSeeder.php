<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = 'Admin@2026';
        $hash            = password_hash($defaultPassword, PASSWORD_BCRYPT);

        $userId = $this->db->table('admin_users')->insert([
            'last_name'             => 'Administrateur',
            'first_name'            => 'Super',
            'email'                 => 'admin@rbcdisonais.be',
            'password_hash'         => $hash,
            'password_default_hash' => $hash,
            'is_active'             => 1,
        ]);

        $id = $this->db->insertID();

        $this->db->table('admin_user_roles')->insertBatch([
            ['admin_user_id' => $id, 'role' => 'Webmaster'],
        ]);
    }
}
