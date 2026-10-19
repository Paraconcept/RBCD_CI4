<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catalogue de départ repris de la feuille de tickets papier 2026-2027, et un client par membre actif.
 * Les snacks sont en TVA « G » (gougouilles), le reste en « B » (bar).
 */
class SeedBarCatalogAndClients extends Migration
{
    private const CATALOG = [
        'Softs' => [
            ['Eau plate', 2], ['Eau pétillante', 2], ['Coca Cola', 2], ['Coca Zéro', 2], ['Fanta', 2],
            ['FuzeTea Lemon', 2], ['FuzeTea Pêche', 2], ['Tonic', 2], ['Agrumes', 2], ['Minute Maid', 2],
        ],
        'Bières' => [
            ['Jupiler', 2], ['Stella 0,0', 2], ['Carlsberg', 2.5], ['Carlsberg 0,0', 2.5], ['Hoegaarden blanche', 3],
            ['Leffe 0,0', 3.5], ['Chimay Bleue', 3.5], ['Chimay Dorée', 3.5], ['Duvel', 3.5],
        ],
        'Vins & alcools' => [['Vin blanc', 2.5], ['Vin rouge', 2.5], ['Blanc Coca', 3.5], ['Genièvre', 2]],
        'Café & thé'     => [['Café', 2], ['Déca', 2], ['Thé', 2], ['Chocolat chaud', 2]],
        'Snacks'         => [['Chips', 2], ['Gaufre', 2], ['Chocolat', 2], ['Boulette', 2.5], ['Soupe', 2]],
        'Divers'         => [],
    ];

    public function up(): void
    {
        $now   = date('Y-m-d H:i:s');
        $order = 0;
        foreach (self::CATALOG as $category => $products) {
            $this->db->table('bar_categories')->insert(['name' => $category, 'sort_order' => ++$order, 'is_active' => 1]);
            $categoryId = $this->db->insertID();
            foreach ($products as $i => [$name, $price]) {
                $this->db->table('bar_products')->insert([
                    'category_id' => $categoryId,
                    'name'        => $name,
                    'price'       => $price,
                    'vat_group'   => $category === 'Snacks' ? 'G' : 'B',
                    'position'    => $i + 1,
                    'is_active'   => 1,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }

        $members = $this->db->table('members')->select('id, last_name, first_name')->where('is_active', 1)->get()->getResultObject();
        foreach ($members as $m) {
            $this->db->table('bar_clients')->insert([
                'name'       => mb_strtoupper(trim(preg_replace('/\s+/', ' ', $m->last_name . ' ' . $m->first_name)), 'UTF-8'),
                'member_id'  => $m->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $this->db->table('bar_clients')->truncate();
        $this->db->query('DELETE FROM bar_products');
        $this->db->query('DELETE FROM bar_categories');
    }
}
