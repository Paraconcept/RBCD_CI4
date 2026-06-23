<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGalleryIdToNews extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('news', [
            'gallery_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'image',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('news', 'gallery_id');
    }
}
