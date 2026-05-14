<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMembersPrivacyMembersFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('members', [
            'show_photo_members'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_photo'],
            'show_phone_members'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_phone'],
            'show_mobile_members'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_mobile'],
            'show_email_members'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_email'],
            'show_address_members'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_address'],
            'show_birth_date_members' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'show_birth_date'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('members', [
            'show_photo_members',
            'show_phone_members',
            'show_mobile_members',
            'show_email_members',
            'show_address_members',
            'show_birth_date_members',
        ]);
    }
}
