<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Soirée Poule 5 Quilles (tournoi régional, officiel) : trois joueurs A, B, C, trois matchs A/B, A/C, B/C
 * en deux sets gagnants, une feuille récapitulative signée par les trois joueurs.
 */
class CreateScoreboardSkittlesPools extends Migration
{
    public function up(): void
    {
        $player = static function (string $s): array {
            return [
                $s . '_member_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                $s . '_last_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
                $s . '_first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
                $s . '_club'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                $s . '_license'    => ['type' => 'VARCHAR', 'constraint' => 25, 'null' => true],
            ];
        };

        $this->forge->addField(array_merge([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'               => ['type' => 'VARCHAR', 'constraint' => 150],
            'scheduled_at'       => ['type' => 'DATETIME', 'null' => true],
            'table_id'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            // Billard de 2,30 m (100 points par set) ou de 2,84 m (60 points)
            'billiard'           => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => '284'],
            'set_points'         => ['type' => 'INT', 'constraint' => 5, 'default' => 60],
        ], $player('a'), $player('b'), $player('c'), [
            'comment'            => ['type' => 'TEXT', 'null' => true],
            'signature_required' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'send_to_regional'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'regional_email'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'send_to_club_ds'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'club_ds_email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'sent_at'            => ['type' => 'DATETIME', 'null' => true],
            'sent_to'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'send_error'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey('scheduled_at');
        $this->forge->createTable('scoreboard_skittles_pools');

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pool_id'     => ['type' => 'INT', 'unsigned' => true],
            'a_signature' => ['type' => 'MEDIUMTEXT'],
            'b_signature' => ['type' => 'MEDIUMTEXT'],
            'c_signature' => ['type' => 'MEDIUMTEXT'],
            'signed_at'   => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('pool_id');
        $this->forge->addForeignKey('pool_id', 'scoreboard_skittles_pools', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scoreboard_skittles_signatures');

        // Le joueur qui commence passe en p1 : chaque côté garde sa lettre (1 = A, 2 = B, 3 = C)
        $this->forge->addColumn('scoreboard_matches', [
            'skittles_pool_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'final_placement'],
            'p1_pool_slot'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'after' => 'p1_member_id'],
            'p2_pool_slot'     => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true, 'after' => 'p2_member_id'],
        ]);
        $this->db->query('ALTER TABLE scoreboard_matches ADD KEY skittles_pool_id (skittles_pool_id)');
        $this->db->query('ALTER TABLE scoreboard_matches ADD CONSTRAINT scoreboard_matches_skittles_pool_fk
            FOREIGN KEY (skittles_pool_id) REFERENCES scoreboard_skittles_pools (id) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE scoreboard_matches DROP FOREIGN KEY scoreboard_matches_skittles_pool_fk');
        $this->db->query('ALTER TABLE scoreboard_matches DROP KEY skittles_pool_id');
        $this->forge->dropColumn('scoreboard_matches', ['skittles_pool_id', 'p1_pool_slot', 'p2_pool_slot']);
        $this->forge->dropTable('scoreboard_skittles_signatures', true);
        $this->forge->dropTable('scoreboard_skittles_pools', true);
    }
}
