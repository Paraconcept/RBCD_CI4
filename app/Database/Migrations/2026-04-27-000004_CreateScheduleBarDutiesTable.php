<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleBarDutiesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'duty_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'period' => [
                'type'       => 'ENUM',
                'constraint' => ['am', 'soir'],
                'null'       => false,
            ],
            'admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['duty_date', 'period']);
        $this->forge->addForeignKey('admin_user_id', 'admin_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('schedule_bar_duties');
    }

    public function down(): void
    {
        $this->forge->dropTable('schedule_bar_duties');
    }
}
