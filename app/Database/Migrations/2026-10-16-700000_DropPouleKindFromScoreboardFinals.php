<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropPouleKindFromScoreboardFinals extends Migration
{
    public function up(): void
    {
        // Seules restent les feuilles officielles : championnat régional (par défaut) et coupe
        $this->db->table('scoreboard_finals')->where('kind', 'poule')->update(['kind' => 'championship']);
        $this->forge->modifyColumn('scoreboard_finals', [
            'kind' => ['name' => 'kind', 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'championship'],
        ]);
    }

    public function down(): void
    {
        $this->forge->modifyColumn('scoreboard_finals', [
            'kind' => ['name' => 'kind', 'type' => 'VARCHAR', 'constraint' => 20, 'default' => 'poule'],
        ]);
    }
}
