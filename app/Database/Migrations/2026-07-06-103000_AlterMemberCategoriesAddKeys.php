<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * La table `members_categories` a été importée manuellement (copie de la
 * structure FRBB) avant que le code applicatif n'existe : pas de clé primaire,
 * pas de contrainte sur member_id. Cette migration ajoute les clés attendues.
 */
class AlterMemberCategoriesAddKeys extends Migration
{
    public function up(): void
    {
        $this->db->query(
            'ALTER TABLE `members_categories` '
            . 'MODIFY `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, '
            . 'ADD PRIMARY KEY (`id`), '
            . 'ADD UNIQUE KEY `member_id` (`member_id`), '
            . 'ADD CONSTRAINT `members_categories_member_id_foreign` '
            . 'FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE'
        );
    }

    public function down(): void
    {
        $this->db->query(
            'ALTER TABLE `members_categories` '
            . 'DROP FOREIGN KEY `members_categories_member_id_foreign`, '
            . 'DROP PRIMARY KEY, DROP INDEX `member_id`, '
            . 'MODIFY `id` INT(10) UNSIGNED NOT NULL'
        );
    }
}
