<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleArbitrageAddMemberId extends Migration
{
    public function up(): void
    {
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                ADD COLUMN `member_id` INT UNSIGNED NULL AFTER `encounter_id`,
                MODIFY COLUMN `admin_user_id` INT UNSIGNED NULL
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                DROP COLUMN `member_id`,
                MODIFY COLUMN `admin_user_id` INT UNSIGNED NOT NULL
        ");
    }
}
