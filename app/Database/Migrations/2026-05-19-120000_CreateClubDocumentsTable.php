<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateClubDocumentsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'auto_increment' => true],
            'slug'        => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'title'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'filename'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'uploaded_at' => ['type' => 'DATE', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('club_documents');
    }

    public function down(): void
    {
        $this->forge->dropTable('club_documents');
    }
}
