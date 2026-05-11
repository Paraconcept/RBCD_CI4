<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCupTeamsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'game_mode'  => ['type' => 'ENUM', 'constraint' => ['Libre', '3 Bandes PF', '3 Bandes GF'], 'default' => 'Libre'],
            'player1_id' => ['type' => 'INT', 'unsigned' => true],
            'player2_id' => ['type' => 'INT', 'unsigned' => true],
            'player3_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cup_teams');
    }

    public function down(): void
    {
        $this->forge->dropTable('cup_teams', true);
    }
}
