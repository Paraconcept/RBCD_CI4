<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Au club, les grands billards sont « Billard 1 » et « Billard 2 » ; les autres sont des petits billards.
 */
class SetGrandBilliardTables extends Migration
{
    public function up(): void
    {
        $this->db->query("UPDATE `scoreboard_tables` SET `format` = IF(TRIM(`name`) IN ('Billard 1', 'Billard 2'), 'GB', 'PB')");
    }

    public function down(): void
    {
        $this->db->query("UPDATE `scoreboard_tables` SET `format` = 'PB'");
    }
}
