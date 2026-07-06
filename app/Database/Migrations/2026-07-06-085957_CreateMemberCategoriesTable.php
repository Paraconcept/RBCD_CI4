<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMemberCategoriesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'member_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Petit Billard (2m30)
            'PLPF'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'PLPF_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'BPF'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'BPF_st'  => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'C38_2'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'C38_2_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'C57_2'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'C57_2_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'B3PF'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'B3PF_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            // Grand Billard (2m84)
            'PLGF'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'PLGF_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'BGF'     => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'BGF_st'  => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'C47_2'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'C47_2_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'C47_1'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'C47_1_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'C71_2'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'C71_2_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'B3GF'    => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
            'B3GF_st' => ['type' => 'ENUM', 'constraint' => ['NJ', 'JR', 'NJR', 'REP'], 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('member_id');
        $this->forge->addForeignKey('member_id', 'members', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('members_categories');
    }

    public function down(): void
    {
        $this->forge->dropTable('members_categories', true);
    }
}
