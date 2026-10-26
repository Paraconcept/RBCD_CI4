<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageToBarProducts extends Migration
{
    // Photos livrées avec le code (public/assets/cash-register/products) ; celles envoyées depuis l'admin vont dans uploads/
    private const IMAGES = [
        'Eau plate'          => 'eau-plate',
        'Eau pétillante'     => 'eau-petillante',
        'Coca Cola'          => 'coca-cola',
        'Coca Zéro'          => 'coca-zero',
        'Fanta'              => 'fanta',
        'FuzeTea Lemon'      => 'fuzetea-lemon',
        'FuzeTea Pêche'      => 'fuzetea-peche',
        'Agrumes'            => 'agrumes',
        'Minute Maid'        => 'minute-maid',
        'Jupiler'            => 'jupiler',
        'Stella 0,0'         => 'stella-0-0',
        'Carlsberg'          => 'carlsberg',
        'Carlsberg 0,0'      => 'carlsberg-0-0',
        'Hoegaarden blanche' => 'hoegaarden-blanche',
        'Leffe 0,0'          => 'leffe-0-0',
        'Chimay Bleue'       => 'chimay-bleue',
        'Chimay Dorée'       => 'chimay-doree',
        'Duvel'              => 'duvel',
        'Vin blanc'          => 'vin-blanc',
        'Vin rouge'          => 'vin-rouge',
        'Blanc Coca'         => 'blanc-coca',
        'Genièvre'           => 'genievre',
        'Café'               => 'cafe',
        'Déca'               => 'deca',
        'Thé'                => 'the',
        'Chocolat chaud'     => 'chocolat-chaud',
        'Chips'              => 'chips',
        'Gaufre'             => 'gaufre',
        'Chocolat'           => 'chocolat',
        'Boulette'           => 'boulette',
        'Soupe'              => 'soupe',
    ];

    public function up(): void
    {
        $this->forge->addColumn('bar_products', [
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
                'default'    => null,
                'after'      => 'vat_group',
            ],
        ]);

        foreach (self::IMAGES as $name => $file) {
            $this->db->table('bar_products')->where('name', $name)->where('image IS NULL')
                ->update(['image' => 'assets/cash-register/products/' . $file . '.webp']);
        }
    }

    public function down(): void
    {
        $this->forge->dropColumn('bar_products', ['image']);
    }
}
