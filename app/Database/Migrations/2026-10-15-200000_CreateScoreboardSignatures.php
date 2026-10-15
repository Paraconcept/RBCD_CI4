<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardSignatures extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'match_id'          => ['type' => 'INT', 'unsigned' => true],
            'referee_name'      => ['type' => 'VARCHAR', 'constraint' => 100],
            // Images PNG en data URI, insérées telles quelles dans la feuille de match PDF
            'p1_signature'      => ['type' => 'MEDIUMTEXT'],
            'p2_signature'      => ['type' => 'MEDIUMTEXT'],
            'referee_signature' => ['type' => 'MEDIUMTEXT'],
            'signed_at'         => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('match_id');
        $this->forge->addForeignKey('match_id', 'scoreboard_matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scoreboard_signatures');
    }

    public function down(): void
    {
        $this->forge->dropTable('scoreboard_signatures');
    }
}
