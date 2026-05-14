<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLessonPriceToTreasurySettings extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('treasury_settings', [
            'lesson_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'default'    => 5.00,
                'after'      => 'forfait_price',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('treasury_settings', 'lesson_price');
    }
}
