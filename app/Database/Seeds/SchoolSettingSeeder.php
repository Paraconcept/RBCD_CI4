<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SchoolSettingSeeder extends Seeder
{
    public function run(): void
    {
        $this->db->table('school_settings')->insert([
            'teacher_member_id'   => 4,  // AUSSEMS Max
            'contact_member_id'   => 9,  // CAVELIER Nathalie
            'schedule'            => 'Samedi, 10h00 — 12h00',
            'frequency_per_month' => 4,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }
}
