<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoriesToScoreboard extends Migration
{
    public function up(): void
    {
        // La moyenne minimum est figée au moment de l'encodage : le barème peut évoluer d'une saison à l'autre
        $fields = [];
        foreach (['p1', 'p2'] as $p) {
            $fields[$p . '_category_id']  = ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => $p . '_license'];
            $fields[$p . '_category']     = ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => $p . '_category_id'];
            $fields[$p . '_average_min']  = ['type' => 'DECIMAL', 'constraint' => '6,3', 'null' => true, 'after' => $p . '_category'];
        }
        $this->forge->addColumn('scoreboard_matches', $fields);

        $this->forge->addColumn('scoreboard_players', [
            'categories' => ['type' => 'TEXT', 'null' => true, 'after' => 'license'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', [
            'p1_category_id', 'p1_category', 'p1_average_min',
            'p2_category_id', 'p2_category', 'p2_average_min',
        ]);
        $this->forge->dropColumn('scoreboard_players', ['categories']);
    }
}
