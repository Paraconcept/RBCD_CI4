<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWinnerPhotoToSportResults extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE sport_results ADD COLUMN winner_photo VARCHAR(255) NULL DEFAULT NULL AFTER winner_name");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE sport_results DROP COLUMN winner_photo");
    }
}
