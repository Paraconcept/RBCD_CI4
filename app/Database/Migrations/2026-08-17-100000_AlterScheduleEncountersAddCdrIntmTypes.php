<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncountersAddCdrIntmTypes extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('schedule_encounters', [
            'encounter_type' => [
                'name'       => 'encounter_type',
                'type'       => 'ENUM',
                'constraint' => ['normal', 'finale', 'cdr', 'intm'],
                'default'    => 'normal',
                'null'       => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('schedule_encounters', [
            'encounter_type' => [
                'name'       => 'encounter_type',
                'type'       => 'ENUM',
                'constraint' => ['normal', 'finale'],
                'default'    => 'normal',
                'null'       => false,
            ],
        ]);
    }
}
