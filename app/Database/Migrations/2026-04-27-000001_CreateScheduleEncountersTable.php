<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleEncountersTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'match_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'match_time' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'is_home' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'venue' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
            ],
            'competition' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'team_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('match_date');
        $this->forge->addKey(['match_date', 'match_time']);
        $this->forge->createTable('schedule_encounters');
    }

    public function down(): void
    {
        $this->forge->dropTable('schedule_encounters');
    }
}
