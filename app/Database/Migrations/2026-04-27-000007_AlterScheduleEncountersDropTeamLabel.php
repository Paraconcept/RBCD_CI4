<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncountersDropTeamLabel extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('schedule_encounters', 'team_label');
    }

    public function down()
    {
        $this->forge->addColumn('schedule_encounters', [
            'team_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'competition',
            ],
        ]);
    }
}
