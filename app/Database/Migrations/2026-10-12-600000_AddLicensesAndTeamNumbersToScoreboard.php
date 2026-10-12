<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLicensesAndTeamNumbersToScoreboard extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'p1_license' => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => 'p1_club'],
            'p2_license' => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => 'p2_club'],
        ]);
        $this->forge->addColumn('scoreboard_players', [
            'license' => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => 'club'],
        ]);
        // Club et numéro d'équipe séparés, comme sur la feuille récapitulative de la Coupe des Régions
        $this->forge->addColumn('scoreboard_team_encounters', [
            'team_club'       => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'RBC Disonais', 'after' => 'team_name'],
            'team_number'     => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'after' => 'team_club'],
            'opponent_number' => ['type' => 'TINYINT', 'constraint' => 3, 'unsigned' => true, 'null' => true, 'after' => 'opponent_club'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_team_encounters', ['team_club', 'team_number', 'opponent_number']);
        $this->forge->dropColumn('scoreboard_players', 'license');
        $this->forge->dropColumn('scoreboard_matches', ['p1_license', 'p2_license']);
    }
}
