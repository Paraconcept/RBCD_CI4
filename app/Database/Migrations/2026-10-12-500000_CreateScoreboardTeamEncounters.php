<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScoreboardTeamEncounters extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'code'          => ['type' => 'VARCHAR', 'constraint' => 20],
            'competition'   => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'CDR'],
            // Équipe du club (cdr_teams.id) ; son nom est recopié pour l'historique
            'team_id'       => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'team_name'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'opponent_club' => ['type' => 'VARCHAR', 'constraint' => 100],
            'scheduled_at'  => ['type' => 'DATETIME', 'null' => true],
            'discipline'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'libre'],
            'format'        => ['type' => 'ENUM', 'constraint' => ['PB', 'GB'], 'default' => 'PB'],
            'notes'         => ['type' => 'TEXT', 'null' => true],
            'created_by'    => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('code');
        $this->forge->addKey('scheduled_at');
        $this->forge->createTable('scoreboard_team_encounters');

        $this->forge->addColumn('scoreboard_matches', [
            'team_encounter_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true, 'after' => 'type'],
        ]);
        $this->db->query('ALTER TABLE scoreboard_matches ADD KEY team_encounter_id (team_encounter_id)');
        // Supprimer une fiche d'équipe ne supprime pas ses matchs : ils redeviennent des matchs individuels
        $this->db->query('ALTER TABLE scoreboard_matches ADD CONSTRAINT scoreboard_matches_team_encounter_fk
            FOREIGN KEY (team_encounter_id) REFERENCES scoreboard_team_encounters (id) ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down(): void
    {
        $this->db->query('ALTER TABLE scoreboard_matches DROP FOREIGN KEY scoreboard_matches_team_encounter_fk');
        $this->forge->dropColumn('scoreboard_matches', 'team_encounter_id');
        $this->forge->dropTable('scoreboard_team_encounters', true);
    }
}
