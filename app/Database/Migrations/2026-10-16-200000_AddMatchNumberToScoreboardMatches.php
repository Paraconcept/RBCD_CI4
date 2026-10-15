<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMatchNumberToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            // N° du calendrier fédéral (ex. Coupe de Belgique), imprimé sur la feuille de match par sets
            'match_number' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'competition'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'match_number');
    }
}
