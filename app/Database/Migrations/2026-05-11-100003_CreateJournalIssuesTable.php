<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJournalIssuesTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'          => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'published_date' => ['type' => 'DATE', 'null' => true],
            'description'    => ['type' => 'TEXT', 'null' => true],
            'file_path'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_published'   => ['type' => 'TINYINT', 'unsigned' => true, 'null' => false, 'default' => 1],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('journal_issues');
    }

    public function down(): void
    {
        $this->forge->dropTable('journal_issues');
    }
}
