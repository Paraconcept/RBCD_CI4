<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleArbitrageAddRound extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                DROP INDEX `encounter_id`,
                ADD COLUMN `round` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `encounter_id`,
                ADD UNIQUE KEY `arbitrage_enc_round` (`encounter_id`, `round`)
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                DROP INDEX `arbitrage_enc_round`,
                DROP COLUMN `round`,
                ADD UNIQUE KEY `encounter_id` (`encounter_id`)
        ");
    }
}
