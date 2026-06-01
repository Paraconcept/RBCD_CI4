<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPublishedToSportResults extends Migration
{
    public function up(): void
    {
        $this->db->query("ALTER TABLE sport_results ADD COLUMN is_published TINYINT(1) NOT NULL DEFAULT 1 AFTER pdf_file");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE sport_results DROP COLUMN is_published");
    }
}
