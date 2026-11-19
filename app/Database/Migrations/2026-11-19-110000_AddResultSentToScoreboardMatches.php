<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResultSentToScoreboardMatches extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', [
            'result_sent_at' => ['type' => 'DATETIME', 'null' => true, 'after' => 'organizer_ds_email'],
            'result_sent_to' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true, 'after' => 'result_sent_at'],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', ['result_sent_at', 'result_sent_to']);
    }
}
