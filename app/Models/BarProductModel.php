<?php

namespace App\Models;

use CodeIgniter\Model;

class BarProductModel extends Model
{
    // Grille de la caisse : 4 colonnes × 4 rangées par catégorie
    public const SLOTS = 16;

    protected $table         = 'bar_products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $allowedFields = ['category_id', 'name', 'price', 'vat_group', 'position', 'is_active'];

    /** Produits actifs rangés par catégorie puis par case. */
    public function getActiveByCategory(): array
    {
        $byCategory = [];
        foreach ($this->where('is_active', 1)->where('position IS NOT NULL')->orderBy('position')->findAll() as $p) {
            $byCategory[(int) $p->category_id][] = $p;
        }
        return $byCategory;
    }

    /** Cases 1 à 16 encore libres dans une catégorie (en gardant celle du produit modifié). */
    public function freeSlots(int $categoryId, ?int $exceptProductId = null): array
    {
        $builder = $this->select('position')->where('category_id', $categoryId)->where('position IS NOT NULL');
        if ($exceptProductId) {
            $builder->where('id !=', $exceptProductId);
        }
        $taken = array_map('intval', array_column($builder->findAll(), 'position'));
        return array_values(array_diff(range(1, self::SLOTS), $taken));
    }
}
