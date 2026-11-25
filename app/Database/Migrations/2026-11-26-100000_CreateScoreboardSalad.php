<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Partie amicale à la Salade : liste des figures du club (commune à tous les billards, avec les derniers réglages
 * utilisés) ; sur la partie, les figures jouées (JSON figé à la création) et la suite des coups (JSON).
 */
class CreateScoreboardSalad extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'label'      => ['type' => 'VARCHAR', 'constraint' => 60],
            'is_builtin' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'sort_order' => ['type' => 'INT', 'default' => 0],
            'active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'p1_points'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
            'p2_points'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 3],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('scoreboard_salad_figures', false, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        $now = date('Y-m-d H:i:s');
        foreach (['Direct', 'Rouge', '1 Bande', '2 Bandes', '3 Bandes', 'Bricole'] as $i => $label) {
            $this->db->table('scoreboard_salad_figures')->insert([
                'label' => $label, 'is_builtin' => 1, 'sort_order' => $i + 1, 'active' => 1,
                'p1_points' => 3, 'p2_points' => 3, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $this->forge->addColumn('scoreboard_matches', [
            'salad_figures' => ['type' => 'TEXT', 'null' => true, 'after' => 'sets_to_win'],
            'salad_state'   => ['type' => 'MEDIUMTEXT', 'null' => true, 'after' => 'salad_figures'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('scoreboard_matches', ['salad_figures', 'salad_state']);
        $this->forge->dropTable('scoreboard_salad_figures', true);
    }
}
