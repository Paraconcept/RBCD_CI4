<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Code « C/Sup » hérité de l'import FRBB-LL : absent du barème actuel, il correspond à la
 * catégorie Supérieur (Sup) du 3 bandes grand billard (confirmé pour AUSSEMS Max).
 */
class FixLegacySupCategory extends Migration
{
    public function up(): void
    {
        $this->db->query("UPDATE `members_categories` SET `B3GF` = 'Sup' WHERE `B3GF` = 'C/Sup'");
    }

    public function down(): void
    {
        // Irréversible sans perdre l'information : on ne sait plus quelles lignes portaient l'ancien code.
    }
}
