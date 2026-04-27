<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleEncountersAddRoundsCount extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE schedule_encounters
                ADD COLUMN `rounds_count` TINYINT UNSIGNED NOT NULL DEFAULT 3
                    AFTER `encounter_type`
        ");
    }

    public function down(): void
    {
        $this->db->query("ALTER TABLE schedule_encounters DROP COLUMN `rounds_count`");
    }
}
