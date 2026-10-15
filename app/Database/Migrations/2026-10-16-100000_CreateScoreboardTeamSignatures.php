<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardTeamSignatures extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_team_encounters', [
            'recap_signature_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'recap_comment'],
        ]);

        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'encounter_id'   => ['type' => 'INT', 'unsigned' => true],
            // Images PNG en data URI ; pas de nom de capitaine (demande du club)
            'home_signature' => ['type' => 'MEDIUMTEXT'],
            'away_signature' => ['type' => 'MEDIUMTEXT'],
            'signed_at'      => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('encounter_id');
        $this->forge->addForeignKey('encounter_id', 'scoreboard_team_encounters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scoreboard_team_signatures');
    }

    public function down(): void
    {
        $this->forge->dropTable('scoreboard_team_signatures');
        $this->forge->dropColumn('scoreboard_team_encounters', 'recap_signature_required');
    }
}
