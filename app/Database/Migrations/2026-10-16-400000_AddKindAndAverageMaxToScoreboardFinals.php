<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKindAndAverageMaxToScoreboardFinals extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('scoreboard_finals', [
            // poule : feuille récapitulative du club ; championship : classement FRBB régional ; cup : coupe du club
            'kind'             => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'poule', 'after' => 'name'],
            'regional_contact' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'kind'],
        ]);
        // Moyenne maximum de la sous-catégorie : reclassé, promu ou double promotion sur les feuilles FRBB
        $this->forge->addColumn('scoreboard_final_players', [
            'average_max' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'null' => true, 'after' => 'average_min'],
        ]);
        $this->db->query('UPDATE scoreboard_final_players fp JOIN categories_new c ON c.id = fp.category_id
                          SET fp.average_max = NULLIF(c.average_max, 0)');
    }

    public function down(): void
    {
        $this->forge->dropColumn('scoreboard_final_players', 'average_max');
        $this->forge->dropColumn('scoreboard_finals', ['kind', 'regional_contact']);
    }
}
