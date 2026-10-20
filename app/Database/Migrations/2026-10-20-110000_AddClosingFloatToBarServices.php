<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClosingFloatToBarServices extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('bar_services', [
            'closing_float' => [
                'type'       => 'DECIMAL',
                'constraint' => '8,2',
                'null'       => true,
                'default'    => null,
                'after'      => 'closed_by_member_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('bar_services', ['closing_float']);
    }
}
