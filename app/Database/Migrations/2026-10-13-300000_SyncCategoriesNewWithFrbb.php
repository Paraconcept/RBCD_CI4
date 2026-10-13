<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Alignement de `categories_new` sur le barème publié par la FRBB
 * (https://www.frbb-kbbb.be/sports/individual/categories, relevé du 13/10/2026) :
 * catégories 6A–6D du libre petit billard manquantes, 3B du 3 bandes grand billard erronée.
 */
class SyncCategoriesNewWithFrbb extends Migration
{
    public function up(): void
    {
        $this->db->query(
            "INSERT INTO `categories_new` (`game_mode`, `game_mode_text`, `format`, `categories`, `categories_text`, `points`, `average_min`, `average_max`) VALUES
                ('PLPF', 'Partie Libre', 'PF', '6A', '6 A', 21, 0.700, 0.960),
                ('PLPF', 'Partie Libre', 'PF', '6B', '6 B', 18, 0.600, 0.699),
                ('PLPF', 'Partie Libre', 'PF', '6C', '6 C', 15, 0.500, 0.599),
                ('PLPF', 'Partie Libre', 'PF', '6D', '6 D', 12, 0.400, 0.499)
             ON DUPLICATE KEY UPDATE `points` = VALUES(`points`), `average_min` = VALUES(`average_min`), `average_max` = VALUES(`average_max`)"
        );

        $this->db->query(
            "UPDATE `categories_new` SET `points` = 15, `average_max` = 0.319 WHERE `game_mode` = 'B3GF' AND `categories` = '3B'"
        );
    }

    public function down(): void
    {
        $this->db->query("DELETE FROM `categories_new` WHERE `game_mode` = 'PLPF' AND `categories` IN ('6A', '6B', '6C', '6D')");
        $this->db->query("UPDATE `categories_new` SET `points` = 16, `average_max` = 0.318 WHERE `game_mode` = 'B3GF' AND `categories` = '3B'");
    }
}
