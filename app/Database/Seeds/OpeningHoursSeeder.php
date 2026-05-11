<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class OpeningHoursSeeder extends Seeder
{
    public function run(): void
    {
        $days = [
            ['day_order' => 1, 'day_name' => 'Lundi',    'is_closed' => 0, 'evening_open' => '19:00', 'evening_close' => '23:00'],
            ['day_order' => 2, 'day_name' => 'Mardi',    'is_closed' => 0, 'evening_open' => '19:00', 'evening_close' => '23:00'],
            ['day_order' => 3, 'day_name' => 'Mercredi', 'is_closed' => 0, 'evening_open' => '19:00', 'evening_close' => '23:00'],
            ['day_order' => 4, 'day_name' => 'Jeudi',    'is_closed' => 0, 'evening_open' => '19:00', 'evening_close' => '23:00'],
            ['day_order' => 5, 'day_name' => 'Vendredi', 'is_closed' => 0, 'evening_open' => '19:00', 'evening_close' => '23:00'],
            ['day_order' => 6, 'day_name' => 'Samedi',   'is_closed' => 0, 'afternoon_open' => '14:00', 'afternoon_close' => '23:00'],
            ['day_order' => 7, 'day_name' => 'Dimanche', 'is_closed' => 1],
        ];

        foreach ($days as $day) {
            $row = [
                'day_order'       => $day['day_order'],
                'day_name'        => $day['day_name'],
                'is_closed'       => $day['is_closed'],
                'morning_open'    => $day['morning_open']    ?? null,
                'morning_close'   => $day['morning_close']   ?? null,
                'afternoon_open'  => $day['afternoon_open']  ?? null,
                'afternoon_close' => $day['afternoon_close'] ?? null,
                'evening_open'    => $day['evening_open']    ?? null,
                'evening_close'   => $day['evening_close']   ?? null,
            ];
            $this->db->table('opening_hours')->insert($row);
        }
    }
}
