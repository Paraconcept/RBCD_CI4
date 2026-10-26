<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTonicImageToBarProducts extends Migration
{
    public function up(): void
    {
        $this->db->table('bar_products')->where('name', 'Tonic')->where('image IS NULL')
            ->update(['image' => 'assets/cash-register/products/tonic.webp']);
    }

    public function down(): void
    {
        $this->db->table('bar_products')->where('image', 'assets/cash-register/products/tonic.webp')
            ->update(['image' => null]);
    }
}
