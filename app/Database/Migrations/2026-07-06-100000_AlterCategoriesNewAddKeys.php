<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * La table `categories_new` (barème catégories → points de la FRBB) est importée
 * telle quelle depuis frbbll_ci4_2026 : copie brute, sans clé primaire ni index.
 * Cette migration ajoute juste les clés nécessaires pour un usage propre côté RBCD.
 */
class AlterCategoriesNewAddKeys extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'ALTER TABLE `categories_new` '
            . 'MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, '
            . 'ADD PRIMARY KEY (`id`), '
            . 'ADD UNIQUE KEY `game_mode_categories` (`game_mode`, `categories`)'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `categories_new` '
            . 'DROP PRIMARY KEY, DROP INDEX `game_mode_categories`, '
            . 'MODIFY `id` INT(10) UNSIGNED NOT NULL'
        );
    }
}
