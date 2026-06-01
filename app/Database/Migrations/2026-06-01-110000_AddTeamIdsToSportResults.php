<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTeamIdsToSportResults extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE sport_results
            ADD COLUMN cdr_team_id  INT UNSIGNED NULL DEFAULT NULL AFTER is_published,
            ADD COLUMN intm_team_id INT UNSIGNED NULL DEFAULT NULL AFTER cdr_team_id");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE sport_results
            DROP COLUMN cdr_team_id,
            DROP COLUMN intm_team_id");
    }
}
