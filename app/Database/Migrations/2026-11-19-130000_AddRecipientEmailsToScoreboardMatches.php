<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecipientEmailsToScoreboardMatches extends Migration
{
    public function up(): void
    {
        // Adresses modifiables match par match ; NULL = adresse par défaut (DS du club, paramètres DSR / CDR)
        $this->forge->addColumn('scoreboard_matches', [
            'club_ds_email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_club_ds'],
            'dsr_email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_dsr'],
            'cdr_email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'send_to_cdr'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', ['club_ds_email', 'dsr_email', 'cdr_email']);
    }
}
