<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlayerEmailsToScoreboard extends Migration
{
    public function up(): void
    {
        // Feuille de match envoyée aux deux joueurs par défaut ; adresse vide = recherchée au moment de l'envoi
        $this->forge->addColumn('scoreboard_matches', [
            'send_to_p1'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'organizer_ds_email'],
            'p1_email'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_p1'],
            'send_to_p2'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'p1_email'],
            'p2_email'          => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_p2'],
            'result_send_error' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'result_sent_to'],
        ]);
        $this->forge->addColumn('scoreboard_players', [
            'email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'license'],
        ]);
        $this->forge->addColumn('scoreboard_team_encounters', [
            'recap_send_error' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'recap_sent_to'],
        ]);

        // L'envoi automatique ne concerne que les matchs terminés à partir d'aujourd'hui : pas de rafale d'e-mails sur l'historique
        $now = date('Y-m-d H:i:s');
        $this->db->table('site_settings')->insert(['key' => 'scoreboard_auto_send_since', 'value' => $now, 'created_at' => $now, 'updated_at' => $now]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', ['send_to_p1', 'p1_email', 'send_to_p2', 'p2_email', 'result_send_error']);
        $this->forge->dropColumn('scoreboard_players', ['email']);
        $this->forge->dropColumn('scoreboard_team_encounters', ['recap_send_error']);
        $this->db->table('site_settings')->where('key', 'scoreboard_auto_send_since')->delete();
    }
}
