<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenameLibreToLibrePF extends Migration
{
    public function up(): void
    {
        $this->db->query("UPDATE cup_teams SET game_mode = 'Libre PF' WHERE game_mode = 'Libre'");
        $this->db->query("ALTER TABLE cup_teams MODIFY game_mode ENUM('Libre PF','Libre GF','3 Bandes PF','3 Bandes GF') NOT NULL DEFAULT 'Libre PF'");
    }

    public function down(): void
    {
        $this->db->query("UPDATE cup_teams SET game_mode = 'Libre' WHERE game_mode = 'Libre PF'");
        $this->db->query("ALTER TABLE cup_teams MODIFY game_mode ENUM('Libre','3 Bandes PF','3 Bandes GF') NOT NULL DEFAULT 'Libre'");
    }
}
