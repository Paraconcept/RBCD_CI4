<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterAdminUsersTable extends Migration
{
    public function up(): void
    {
        // Supprime la colonne role (remplacée par admin_user_roles)
        $this->forge->dropColumn('admin_users', 'role');

        // Ajoute member_id — lien optionnel vers la table members
        $this->forge->addColumn('admin_users', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'email',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('admin_users', 'member_id');

        $this->forge->addColumn('admin_users', [
            'role' => [
                'type'    => "ENUM('Webmaster','Président','Secrétaire','Directeur Sportif','Trésorier')",
                'default' => 'Webmaster',
                'after'   => 'email',
            ],
        ]);
    }
}
