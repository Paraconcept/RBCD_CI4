<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncountersAddType extends Migration
{
    public function up()
    {
        $this->forge->addColumn('schedule_encounters', [
            'encounter_type' => [
                'type'       => 'ENUM',
                'constraint' => ['normal', 'finale'],
                'default'    => 'normal',
                'null'       => false,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('schedule_encounters', 'encounter_type');
    }
}
