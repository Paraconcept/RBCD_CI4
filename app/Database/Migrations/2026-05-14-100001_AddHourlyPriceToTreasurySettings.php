<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHourlyPriceToTreasurySettings extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_settings', [
            'hourly_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'default'    => 2.50,
                'after'      => 'lesson_price',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_settings', 'hourly_price');
    }
}
