<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResultRecipientsToScoreboardMatches extends Migration
{
    public function up(): void
    {
        // Destinataires des résultats : seule l'adresse du DS du club organisateur est propre au match,
        // celles du DS du club et du DSR sont lues au moment de l'envoi
        $this->forge->addColumn('scoreboard_matches', [
            'send_to_club_ds'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'signature_required'],
            'send_to_dsr'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'send_to_club_ds'],
            'send_to_organizer_ds' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'send_to_dsr'],
            'organizer_ds_email'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_organizer_ds'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', ['send_to_club_ds', 'send_to_dsr', 'send_to_organizer_ds', 'organizer_ds_email']);
    }
}
