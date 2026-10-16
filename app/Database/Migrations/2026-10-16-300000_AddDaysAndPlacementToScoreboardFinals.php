<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDaysAndPlacementToScoreboardFinals extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_finals', [
            // Finale sans éliminatoires : chacun contre chacun puis 1er-2e, 3e-4e… d'après le classement de la poule
            'placement_matches'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'table_ids'],
            // Finale sur deux jours : les matchs à partir de la position day2_from se jouent le jour 2
            'day2_at'            => ['type' => 'DATETIME', 'null' => true, 'after' => 'scheduled_at'],
            'day2_from'          => ['type' => 'TINYINT', 'constraint' => 3, 'null' => true, 'after' => 'day2_at'],
            // Calendrier fédéral à 4 joueurs : moment où les matchs 3 à 6 ont été placés d'après les matchs 1 et 2
            'results_ordered_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'placement_matches'],
        ]);
        $this->forge->addColumn('scoreboard_matches', [
            // Match de classement « 1-2 », « 3-4 »… : joueurs inconnus tant que la poule n'est pas terminée
            'final_placement' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true, 'after' => 'final_order'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', 'final_placement');
        $this->forge->dropColumn('scoreboard_finals', ['placement_matches', 'day2_at', 'day2_from', 'results_ordered_at']);
    }
}
