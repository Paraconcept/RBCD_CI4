<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FuseAdminUsersIntoMembersLogin extends Migration
{
    public function up(): void
    {
        // 1. Copy admin passwords to members_login
        $this->db->query("
            INSERT INTO members_login (member_id, password_hash, is_active, created_at, updated_at)
            SELECT au.member_id, au.password_hash, 1, NOW(), NOW()
            FROM admin_users au
            WHERE au.member_id IS NOT NULL
              AND NOT EXISTS (
                  SELECT 1 FROM members_login ml WHERE ml.member_id = au.member_id
              )
        ");

        // 2. Activate existing members_login for admins (in case they had an is_active=0 stub)
        $this->db->query("
            UPDATE members_login ml
            JOIN admin_users au ON au.member_id = ml.member_id
            SET ml.is_active = 1,
                ml.password_hash = IF(ml.is_active = 0, au.password_hash, ml.password_hash)
            WHERE au.member_id IS NOT NULL
        ");

        // 3. admin_user_roles: rename admin_user_id → member_id
        $this->db->query("ALTER TABLE admin_user_roles ADD COLUMN member_id INT(11) UNSIGNED NULL AFTER id");
        $this->db->query("
            UPDATE admin_user_roles aur
            JOIN admin_users au ON au.id = aur.admin_user_id
            SET aur.member_id = au.member_id
        ");
        $this->db->query("DELETE FROM admin_user_roles WHERE member_id IS NULL");
        $this->db->query("ALTER TABLE admin_user_roles DROP FOREIGN KEY admin_user_roles_admin_user_id_foreign");
        $this->db->query("ALTER TABLE admin_user_roles DROP COLUMN admin_user_id");
        $this->db->query("ALTER TABLE admin_user_roles MODIFY COLUMN member_id INT(11) UNSIGNED NOT NULL");
        $this->db->query("ALTER TABLE admin_user_roles ADD CONSTRAINT admin_user_roles_member_id_foreign FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE");

        // 4. treasury_expenses: rename admin_user_id → member_id
        $this->db->query("ALTER TABLE treasury_expenses CHANGE admin_user_id member_id INT(10) UNSIGNED NULL");

        // 5. treasury_revenues: rename admin_user_id → member_id
        $this->db->query("ALTER TABLE treasury_revenues CHANGE admin_user_id member_id INT(10) UNSIGNED NULL");

        // 6. Drop FKs from schedule tables (columns stay as legacy data)
        $this->db->query("ALTER TABLE schedule_arbitrage DROP FOREIGN KEY schedule_arbitrage_admin_user_id_foreign");
        $this->db->query("ALTER TABLE schedule_bar_duties DROP FOREIGN KEY schedule_bar_duties_admin_user_id_foreign");

        // 7. Drop admin_users
        $this->db->query("DROP TABLE admin_users");
    }

    public function down(): void
    {
        throw new \RuntimeException('Migration non réversible. Restaurer depuis une sauvegarde.');
    }
}
