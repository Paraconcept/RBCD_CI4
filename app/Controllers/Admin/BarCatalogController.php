<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarCategoryModel;
use App\Models\BarProductModel;

/**
 * Catalogue de la caisse : catégories (onglets de la caisse) et produits, chacun dans une case de la grille 4 × 4.
 */
class BarCatalogController extends BaseController
{
    private BarCategoryModel $categories;
    private BarProductModel $products;

    public function __construct()
    {
        $this->categories = new BarCategoryModel();
        $this->products   = new BarProductModel();
    }

    public function index(): string
    {
        $categories = $this->categories->orderBy('sort_order')->orderBy('name')->findAll();
        $products   = [];
        foreach ($this->products->orderBy('is_active', 'DESC')->orderBy('position')->orderBy('name')->findAll() as $p) {
            $products[(int) $p->category_id][] = $p;
        }
        $sold = array_map('intval', array_column(
            db_connect()->table('bar_ticket_lines')->distinct()->select('product_id')->where('product_id IS NOT NULL')->get()->getResultArray(),
            'product_id'
        ));

        return view('admin/bar_catalog/index', [
            'title'       => 'Caisse — Catalogue',
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Catalogue']],
            'categories'  => $categories,
            'products'    => $products,
            'sold'        => array_flip($sold),
            'slots'       => BarProductModel::SLOTS,
        ]);
    }

    // ------------------------------------------------------------------ catégories

    public function storeCategory()
    {
        $name = $this->cleanName('name', 40);
        if ($name === '') {
            return redirect()->back()->with('error', 'Le nom de la catégorie est obligatoire.');
        }
        $this->categories->insert([
            'name'       => $name,
            'sort_order' => (int) ($this->categories->selectMax('sort_order')->first()->sort_order ?? 0) + 1,
            'is_active'  => 1,
        ]);

        return redirect()->to(base_url('admin/caisse/catalogue'))->with('success', 'Catégorie « ' . esc($name) . ' » ajoutée.');
    }

    public function updateCategory(int $id)
    {
        $name = $this->cleanName('name', 40);
        if ($name === '') {
            return redirect()->back()->with('error', 'Le nom de la catégorie est obligatoire.');
        }
        $this->categories->update($id, [
            'name'       => $name,
            'sort_order' => max(0, (int) $this->request->getPost('sort_order')),
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        return redirect()->to(base_url('admin/caisse/catalogue'))->with('success', 'Catégorie modifiée.');
    }

    public function deleteCategory(int $id)
    {
        if ($this->products->where('category_id', $id)->countAllResults()) {
            return redirect()->back()->with('error', 'Cette catégorie contient des produits : déplacez-les ou désactivez la catégorie.');
        }
        $this->categories->delete($id);

        return redirect()->to(base_url('admin/caisse/catalogue'))->with('success', 'Catégorie supprimée.');
    }

    // ------------------------------------------------------------------ produits

    public function storeProduct()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        $data       = $this->productData($categoryId, null);
        if (is_string($data)) {
            return redirect()->back()->withInput()->with('error', $data);
        }
        $this->products->insert($data + ['category_id' => $categoryId, 'is_active' => 1]);

        return redirect()->to(base_url('admin/caisse/catalogue') . '#cat' . $categoryId)->with('success', 'Produit « ' . esc($data['name']) . ' » ajouté.');
    }

    public function updateProduct(int $id)
    {
        $product = $this->products->find($id);
        if (!$product) {
            return redirect()->back()->with('error', 'Produit introuvable.');
        }
        $categoryId = (int) ($this->request->getPost('category_id') ?: $product->category_id);
        $active     = $this->request->getPost('is_active') ? 1 : 0;
        $data       = $this->productData($categoryId, $id, (bool) $active);
        if (is_string($data)) {
            return redirect()->back()->with('error', $data);
        }
        $this->products->update($id, $data + ['category_id' => $categoryId, 'is_active' => $active]);

        return redirect()->to(base_url('admin/caisse/catalogue') . '#cat' . $categoryId)->with('success', 'Produit « ' . esc($data['name']) . ' » modifié.');
    }

    public function deleteProduct(int $id)
    {
        $product = $this->products->find($id);
        if (!$product) {
            return redirect()->back();
        }
        // Un produit déjà vendu reste dans l'historique : on le désactive au lieu de le supprimer
        if (db_connect()->table('bar_ticket_lines')->where('product_id', $id)->countAllResults()) {
            $this->products->update($id, ['is_active' => 0, 'position' => null]);
            return redirect()->to(base_url('admin/caisse/catalogue') . '#cat' . $product->category_id)
                ->with('success', '« ' . esc($product->name) . ' » a déjà été vendu : il est désactivé (retiré de la caisse) et reste dans l\'historique.');
        }
        $this->products->delete($id);

        return redirect()->to(base_url('admin/caisse/catalogue') . '#cat' . $product->category_id)->with('success', 'Produit supprimé.');
    }

    /** Données validées d'un produit, ou message d'erreur. Un produit inactif libère sa case. */
    private function productData(int $categoryId, ?int $productId, bool $active = true): array|string
    {
        if (!$this->categories->find($categoryId)) {
            return 'Choisissez une catégorie.';
        }
        $name  = $this->cleanName('name', 40);
        $price = str_replace(',', '.', trim((string) $this->request->getPost('price')));
        if ($name === '') {
            return 'Le nom du produit est obligatoire.';
        }
        if (!is_numeric($price) || (float) $price < 0 || (float) $price > 999) {
            return 'Prix invalide pour « ' . esc($name) . ' ».';
        }

        $position = null;
        if ($active) {
            $free     = $this->products->freeSlots($categoryId, $productId);
            $position = (int) $this->request->getPost('position');
            if (!in_array($position, $free, true)) {
                if (!$free) {
                    return 'La grille de cette catégorie est pleine (' . BarProductModel::SLOTS . ' cases) : désactivez un produit ou choisissez une autre catégorie.';
                }
                $position = $free[0];
            }
        }

        return [
            'name'      => $name,
            'price'     => round((float) $price, 2),
            'vat_group' => $this->request->getPost('vat_group') === 'G' ? 'G' : 'B',
            'position'  => $position,
        ];
    }

    private function cleanName(string $field, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $this->request->getPost($field))), 0, $max);
    }
}
