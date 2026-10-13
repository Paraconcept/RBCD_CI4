<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSetsToScoreboard extends Migration
{
    public function up(): void
    {
        // sets_to_win NULL = partie classique ; sinon p1/p2_distance = points d'un set
        $this->forge->addColumn('scoreboard_matches', [
            'sets_to_win' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true, 'after' => 'unequal_innings'],
            'p1_sets'     => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 0, 'after' => 'p1_high_run'],
            'p2_sets'     => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 0, 'after' => 'p2_high_run'],
        ]);
        $this->forge->addColumn('scoreboard_innings', [
            'set_no' => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 1, 'after' => 'match_id'],
        ]);
        // Les reprises sont numérotées par set : la clé unique doit inclure le set.
        // Nouvel index créé avant de supprimer l'ancien, qui sert aussi à la clé étrangère match_id.
        $this->db->query('ALTER TABLE scoreboard_innings ADD UNIQUE KEY match_id_set_no_inning_no_player (match_id, set_no, inning_no, player)');
        $this->db->query('ALTER TABLE scoreboard_innings DROP INDEX match_id_inning_no_player');
    }

    public function down(): void
    {
        $this->db->table('scoreboard_innings')->where('set_no >', 1)->delete();
        $this->db->query('ALTER TABLE scoreboard_innings ADD UNIQUE KEY match_id_inning_no_player (match_id, inning_no, player)');
        $this->db->query('ALTER TABLE scoreboard_innings DROP INDEX match_id_set_no_inning_no_player');
        $this->forge->dropColumn('scoreboard_innings', 'set_no');
        $this->forge->dropColumn('scoreboard_matches', ['sets_to_win', 'p1_sets', 'p2_sets']);
    }
}
