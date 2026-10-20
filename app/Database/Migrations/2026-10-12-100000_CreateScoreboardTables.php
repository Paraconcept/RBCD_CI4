<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 50],
            'sort_order' => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'is_active'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('scoreboard_tables');

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'last_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'club'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['last_name', 'first_name', 'club']);
        $this->forge->createTable('scoreboard_players');

        $player = static fn (string $p): array => [
            $p . '_member_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            $p . '_last_name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            $p . '_first_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            $p . '_club'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            $p . '_points'     => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            $p . '_innings'    => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            $p . '_high_run'   => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            $p . '_extensions_used' => ['type' => 'TINYINT', 'constraint' => 2, 'default' => 0],
        ];

        $this->forge->addField(array_merge([
            'id'                 => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'type'               => ['type' => 'ENUM', 'constraint' => ['official', 'friendly'], 'default' => 'friendly'],
            'status'             => ['type' => 'ENUM', 'constraint' => ['scheduled', 'live', 'finished', 'cancelled'], 'default' => 'scheduled'],
            'table_id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'scheduled_at'       => ['type' => 'DATETIME', 'null' => true],
            'competition'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'discipline'         => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'libre'],
            'distance'           => ['type' => 'INT', 'constraint' => 5],
            'innings_limit'      => ['type' => 'INT', 'constraint' => 5, 'null' => true],
            'unequal_innings'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'shot_clock_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'shot_clock_seconds' => ['type' => 'INT', 'constraint' => 4, 'default' => 40],
        ], $player('p1'), $player('p2'), [
            // 1 ou 2 : le joueur qui commence ; la reprise courante se lit depuis ses reprises
            'starting_player'    => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'current_run'        => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'winner'             => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            // Compteur de version pour arbitrer entre l'état local (navigateur) et le serveur
            'rev'                => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'default' => 0],
            'started_at'         => ['type' => 'DATETIME', 'null' => true],
            'finished_at'        => ['type' => 'DATETIME', 'null' => true],
            'created_by'         => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]));
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'table_id']);
        $this->forge->addKey('scheduled_at');
        $this->forge->addForeignKey('table_id', 'scoreboard_tables', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('scoreboard_matches');

        $this->forge->addField([
            'id'        => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'match_id'  => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'inning_no' => ['type' => 'INT', 'constraint' => 5],
            'player'    => ['type' => 'TINYINT', 'constraint' => 1],
            'points'    => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['match_id', 'inning_no', 'player']);
        $this->forge->addForeignKey('match_id', 'scoreboard_matches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('scoreboard_innings');

        $now = date('Y-m-d H:i:s');
        $this->db->table('scoreboard_tables')->insert([
            'name' => 'Billard 1', 'sort_order' => 1, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach ([
            'scoreboard_shot_clock_seconds'    => '40',
            'scoreboard_shot_clock_extensions' => '2',
            'scoreboard_innings_limit_3c'      => '60',
            'scoreboard_pin'                   => '',
        ] as $key => $value) {
            if ($this->db->table('site_settings')->where('key', $key)->countAllResults() === 0) {
                $this->db->table('site_settings')->insert([
                    'key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('scoreboard_innings', true);
        $this->forge->dropTable('scoreboard_matches', true);
        $this->forge->dropTable('scoreboard_players', true);
        $this->forge->dropTable('scoreboard_tables', true);
        $this->db->table('site_settings')->like('key', 'scoreboard_', 'after')->delete();
    }
}
