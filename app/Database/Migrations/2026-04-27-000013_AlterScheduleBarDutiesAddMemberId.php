<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterScheduleBarDutiesAddMemberId extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('schedule_bar_duties', [
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'admin_user_id',
            ],
        ]);

        // admin_user_id devient nullable (le DS peut assigner un membre sans compte)
        $this->forge->modifyColumn('schedule_bar_duties', [
            'admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('schedule_bar_duties', 'member_id');

        $this->forge->modifyColumn('schedule_bar_duties', [
            'admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
