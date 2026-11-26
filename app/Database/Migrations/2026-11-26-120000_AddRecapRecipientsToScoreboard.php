<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecapRecipientsToScoreboard extends Migration
{
    public function up(): void
    {
        // Feuille récapitulative d'une rencontre d'équipe : aux joueurs de chaque match (cochés par défaut)…
        $this->forge->addColumn('scoreboard_matches', [
            'recap_to_p1' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'p2_email'],
            'recap_to_p2' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'recap_to_p1'],
        ]);
        // … et aux deux clubs et au responsable CDR, chacun décochable
        $this->forge->addColumn('scoreboard_team_encounters', [
            'recap_to_team'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'opponent_email'],
            'recap_to_opponent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'recap_to_team'],
            'recap_to_cdr'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'recap_to_opponent'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', ['recap_to_p1', 'recap_to_p2']);
        $this->forge->dropColumn('scoreboard_team_encounters', ['recap_to_team', 'recap_to_opponent', 'recap_to_cdr']);
    }
}
