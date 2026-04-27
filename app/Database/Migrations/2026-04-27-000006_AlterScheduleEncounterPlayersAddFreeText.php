<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncounterPlayersAddFreeText extends Migration
{
    public function up()
    {
        // member_id devient nullable (joueur libre en mode finale)
        $this->forge->modifyColumn('schedule_encounter_players', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // Nom libre du joueur domicile (utilisé quand member_id est null)
        $this->forge->addColumn('schedule_encounter_players', [
            'player_home_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'member_id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('schedule_encounter_players', 'player_home_name');

        $this->forge->modifyColumn('schedule_encounter_players', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
