<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardDevices extends Migration
{
    public function up(): void
    {
        // Chromebooks et tablette du bar autorisés un par un par un membre du comité : remplace le code PIN commun
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 60],
            'token_hash'   => ['type' => 'CHAR', 'constraint' => 64],
            'created_by'   => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'last_seen_at' => ['type' => 'DATETIME', 'null' => true],
            'last_ip'      => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('token_hash');
        $this->forge->createTable('scoreboard_devices');

        $this->db->table('site_settings')->where('key', 'scoreboard_pin')->delete();
    }

    public function down(): void
    {
        $this->forge->dropTable('scoreboard_devices', true);
    }
}
