<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BarCategoryModel;

/**
 * Quantités vendues par produit (tickets payés) sur une période, du plus vendu au moins vendu : base de la commande au brasseur.
 */
class BarStockController extends BaseController
{
    public function index(): string
    {
        $db    = db_connect();
        $first = $db->table('bar_services')->selectMin('service_date', 'd')->get()->getRowObject()->d;
        $today = date('Y-m-d');

        $from = $this->validDate($this->request->getGet('from')) ?? $first ?? $today;
        $to   = $this->validDate($this->request->getGet('to')) ?? $today;
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        $categoryId = (int) $this->request->getGet('category') ?: null;

        // Regroupé par produit et non par nom : un produit renommé ou dont le prix a changé reste une seule ligne
        $builder = $db->table('bar_ticket_lines l')
            ->select("MAX(l.product_id) AS product_id, COALESCE(MAX(p.name), MAX(l.product_name)) AS name, COALESCE(MAX(c.name), 'Divers') AS category,
                      MAX(p.price) AS price, MAX(p.is_active) AS is_active,
                      SUM(l.quantity) AS quantity, SUM(l.unit_price * l.quantity) AS amount, MAX(s.service_date) AS last_sold", false)
            ->join('bar_tickets t', 't.id = l.ticket_id')
            ->join('bar_services s', 's.id = t.service_id')
            ->join('bar_products p', 'p.id = l.product_id', 'left')
            ->join('bar_categories c', 'c.id = p.category_id', 'left')
            ->where('t.status', 'paid')
            ->where('s.service_date >=', $from)->where('s.service_date <=', $to)
            ->groupBy("COALESCE(CONCAT('p', l.product_id), CONCAT('n', l.product_name))", false)
            ->orderBy('quantity', 'DESC')->orderBy('name');
        if ($categoryId) {
            $builder->where('p.category_id', $categoryId);
        }
        $rows = $builder->get()->getResultObject();

        // Les produits du catalogue jamais vendus sur la période ferment la liste (les moins vendus de tous)
        $soldIds = array_filter(array_map(static fn ($r) => (int) $r->product_id, $rows));
        $unsold  = $db->table('bar_products p')
            ->select("p.id AS product_id, p.name, c.name AS category, p.price, p.is_active, 0 AS quantity, 0 AS amount, NULL AS last_sold", false)
            ->join('bar_categories c', 'c.id = p.category_id', 'left')
            ->where('p.is_active', 1)
            ->orderBy('c.sort_order')->orderBy('p.name');
        if ($soldIds) {
            $unsold->whereNotIn('p.id', $soldIds);
        }
        if ($categoryId) {
            $unsold->where('p.category_id', $categoryId);
        }
        $rows = array_merge($rows, $unsold->get()->getResultObject());

        $services = $db->table('bar_services')->where('service_date >=', $from)->where('service_date <=', $to)->countAllResults();

        return view('admin/bar_stock/index', [
            'title'       => 'Caisse — Stocks',
            'breadcrumbs' => [['title' => 'Caisse'], ['title' => 'Stocks']],
            'rows'        => $rows,
            'from'        => $from,
            'to'          => $to,
            'first'       => $first,
            'services'    => $services,
            'categoryId'  => $categoryId,
            'categories'  => (new BarCategoryModel())->orderBy('sort_order')->findAll(),
        ]);
    }

    private function validDate($value): ?string
    {
        $d = \DateTime::createFromFormat('!Y-m-d', (string) $value);

        return $d && $d->format('Y-m-d') === $value ? $value : null;
    }
}
