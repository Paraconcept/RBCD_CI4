<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TreasurySettingSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('treasury_settings')->insert([
            'annual_cotisation' => 50.00,
            'forfait_price'     => 75.00,
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
    }
}
