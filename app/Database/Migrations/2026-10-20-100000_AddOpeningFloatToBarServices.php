<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOpeningFloatToBarServices extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('bar_services', [
            'opening_float' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'bartender_member_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bar_services', ['opening_float']);
    }
}
