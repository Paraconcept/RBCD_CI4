<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Parties amicales à 3 joueurs : p3_last_name renseigné = partie à trois (NULL pour toutes les autres).
 */
class AddThirdPlayerToScoreboardMatches extends Migration
{
    private const FIELDS = [
        'p3_member_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'p2_extensions_used'],
        'p3_last_name'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'p3_member_id'],
        'p3_first_name'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'p3_last_name'],
        'p3_club'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'p3_first_name'],
        'p3_license'     => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => 'p3_club'],
        'p3_category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'p3_license'],
        'p3_category'    => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true, 'after' => 'p3_category_id'],
        'p3_average_min' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'null' => true, 'after' => 'p3_category'],
        'p3_distance'    => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p3_average_min'],
        'p3_points'      => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p3_distance'],
        'p3_innings'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p3_points'],
        'p3_high_run'    => ['type' => 'INT', 'constraint' => 5, 'default' => 0, 'after' => 'p3_innings'],
    ];

    public function up(): void
    {
        $this->forge->addColumn('scoreboard_matches', self::FIELDS);
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_matches', array_keys(self::FIELDS));
    }
}
