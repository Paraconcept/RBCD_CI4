<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardFinals extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name'                  => ['type' => 'VARCHAR', 'constraint' => 150],
            'scheduled_at'          => ['type' => 'DATETIME', 'null' => true],
            'discipline'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'libre'],
            'format'                => ['type' => 'ENUM', 'constraint' => ['PB', 'GB'], 'default' => 'PB'],
            'innings_limit'         => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'unequal_innings'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'shot_clock_enabled'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'shot_clock_seconds'    => ['type' => 'INT', 'constraint' => 4, 'default' => 40],
            'shot_clock_extensions' => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 3],
            // Billards utilisés, dans l'ordre de répartition des matchs (ids séparés par des virgules)
            'table_ids'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'comment'               => ['type' => 'TEXT', 'null' => true],
            'created_by'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('scheduled_at');
        $this->forge->createTable('scoreboard_finals');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'final_id'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'position'    => ['type' => 'TINYINT', 'constraint' => 2],
            'member_id'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'last_name'   => ['type' => 'VARCHAR', 'constraint' => 100],
            'first_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'club'        => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'license'     => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            'category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'category'    => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'average_min' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'null' => true],
            'distance'    => ['type' => 'INT', 'constraint' => 5],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('final_id');
        $this->forge->addForeignKey('final_id', 'scoreboard_finals', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scoreboard_final_players');

        // Le joueur qui commence passe en p1 : chaque côté garde donc la référence de son joueur de finale
        $this->forge->addColumn('scoreboard_matches', [
            'final_id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'team_encounter_id'],
            'final_order'        => ['type' => 'TINYINT', 'constraint' => 3, 'null' => true, 'after' => 'final_id'],
            'p1_final_player_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'p1_member_id'],
            'p2_final_player_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'p2_member_id'],
        ]);
        $this->db->query('ALTER TABLE scoreboard_matches ADD KEY final_id (final_id)');
        $this->db->query('ALTER TABLE scoreboard_matches ADD CONSTRAINT scoreboard_matches_final_fk
            FOREIGN KEY (final_id) REFERENCES scoreboard_finals (id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE scoreboard_matches DROP FOREIGN KEY scoreboard_matches_final_fk');
        $this->forge->dropColumn('scoreboard_matches', ['final_id', 'final_order', 'p1_final_player_id', 'p2_final_player_id']);
        $this->forge->dropTable('scoreboard_final_players', true);
        $this->forge->dropTable('scoreboard_finals', true);
    }
}
