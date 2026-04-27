<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleArbitrageUniquePerUser extends Migration
{
    public function up(): void
    {
        // Passer de UNIQUE(encounter_id, round) → UNIQUE(encounter_id, admin_user_id)
        // round devient "nb de tours à arbitrer" (1/2/3 pour finale, 0 pour normal)
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                DROP INDEX `arbitrage_enc_round`,
                ADD UNIQUE KEY `arbitrage_enc_user` (`encounter_id`, `admin_user_id`)
        ");
    }

    public function down(): void
    {
        $this->db->query("
            ALTER TABLE schedule_arbitrage
                DROP INDEX `arbitrage_enc_user`,
                ADD UNIQUE KEY `arbitrage_enc_round` (`encounter_id`, `round`)
        ");
    }
}
