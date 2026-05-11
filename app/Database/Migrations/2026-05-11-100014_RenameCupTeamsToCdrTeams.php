<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameCupTeamsToCdrTeams extends Migration
{
    public function up(): void
    {
        $this->db->query('RENAME TABLE cup_teams TO cdr_teams');
    }

    public function down(): void
    {
        $this->db->query('RENAME TABLE cdr_teams TO cup_teams');
    }
}
