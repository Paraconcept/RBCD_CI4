<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SplitScoreboardDistancePerPlayer extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'p1_distance' => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p1_club'],
            'p2_distance' => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p2_club'],
        ]);
        $this->db->query('UPDATE scoreboard_matches SET p1_distance = distance, p2_distance = distance');
        $this->forge->dropColumn('scoreboard_matches', 'distance');
    }

    public function down(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'distance' => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'discipline'],
        ]);
        $this->db->query('UPDATE scoreboard_matches SET distance = p1_distance');
        $this->forge->dropColumn('scoreboard_matches', ['p1_distance', 'p2_distance']);
    }
}
