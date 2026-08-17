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
        // La table peut avoir été importée avec ces clés déjà en place selon
        // l'environnement : on vérifie avant d'altérer pour rester idempotent.
        if (!$this->indexExists('categories_new', 'PRIMARY')) {
            $this->db->query(
                'ALTER TABLE `categories_new` '
                . 'MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, '
                . 'ADD PRIMARY KEY (`id`)'
            );
        }

        if (!$this->indexExists('categories_new', 'game_mode_categories')) {
            $this->db->query(
                'ALTER TABLE `categories_new` '
                . 'ADD UNIQUE KEY `game_mode_categories` (`game_mode`, `categories`)'
            );
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = $this->db->query(
            'SELECT COUNT(*) AS c FROM information_schema.STATISTICS '
            . 'WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        )->getRow();

        return $row && (int) $row->c > 0;
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
