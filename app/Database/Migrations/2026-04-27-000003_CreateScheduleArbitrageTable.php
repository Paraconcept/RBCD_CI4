<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateScheduleArbitrageTable extends Migration
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
            'encounter_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'admin_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'assignment_type' => [
                'type'       => 'ENUM',
                'constraint' => ['volunteer', 'designated'],
                'default'    => 'volunteer',
            ],
            'confirmed' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'confirmed_at' => [
                'type' => 'DATETIME',
                'null' => true,
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
        $this->forge->addUniqueKey('encounter_id');
        $this->forge->addForeignKey('encounter_id', 'schedule_encounters', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('admin_user_id', 'admin_users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('schedule_arbitrage');
    }

    public function down(): void
    {
        $this->forge->dropTable('schedule_arbitrage');
    }
}
