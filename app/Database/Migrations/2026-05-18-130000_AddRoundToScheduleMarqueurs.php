<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoundToScheduleMarqueurs extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('schedule_marqueurs', [
            'round' => [
                'type'       => 'TINYINT',
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'member_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('schedule_marqueurs', 'round');
    }
}
