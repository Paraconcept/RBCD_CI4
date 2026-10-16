<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClubCodeToScoreboardPlayersAndUppercaseClubs extends Migration
{
    public function up(): void
    {
        // Code FRBB du club retenu avec le joueur externe, proposé par l'auto-complétion (finales)
        $this->forge->addColumn('scoreboard_players', [
            'club_code' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true, 'after' => 'club'],
        ]);
        $this->db->query('UPDATE scoreboard_players p
            JOIN (SELECT last_name, first_name, club, MAX(club_code) AS club_code FROM scoreboard_final_players
                  WHERE club_code IS NOT NULL GROUP BY last_name, first_name, club) f
              ON f.last_name = p.last_name AND f.first_name = p.first_name AND f.club = p.club
            SET p.club_code = f.club_code');

        // Noms de club en majuscules, comme les noms de famille
        $this->db->query('UPDATE scoreboard_players SET club = UPPER(club)');
        $this->db->query('UPDATE scoreboard_final_players SET club = UPPER(club)');
        $this->db->query('UPDATE scoreboard_matches SET p1_club = UPPER(p1_club), p2_club = UPPER(p2_club), p3_club = UPPER(p3_club)');
        $this->db->query('UPDATE scoreboard_team_encounters SET team_club = UPPER(team_club), opponent_club = UPPER(opponent_club)');
        $this->forge->modifyColumn('scoreboard_team_encounters', [
            'team_club' => ['name' => 'team_club', 'type' => 'VARCHAR', 'constraint' => 100, 'default' => 'RBC DISONAIS'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_players', 'club_code');
    }
}
