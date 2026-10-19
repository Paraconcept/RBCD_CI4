<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.bar-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; width: 260px; }
.bar-grid > div { height: 44px; border: 1px solid #d8d7e3; border-radius: 6px; font-size: 11px; line-height: 1.15; padding: 3px 5px; background: #fff; overflow: hidden; }
.bar-grid > div.free { border-style: dashed; background: #f6f6fa; color: #9aa0b2; text-align: center; padding-top: 13px; }
.bar-grid b { display: block; color: #1c447c; }
tr.inactive td { background: #f4f4f6; color: #8d91a3; }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php
use App\Models\BarProductModel;

$eur = static fn ($n): string => number_format((float) $n, 2, ',', ' ');
?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Catégories</h3>
    <div class="ml-auto"><a href="<?= base_url('caisse') ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-cash-register mr-1"></i>Ouvrir la caisse</a></div>
  </div>
  <div class="card-body">
    <p class="text-muted">Les catégories sont les onglets de la caisse, dans l'ordre indiqué. Chacune a une grille de <?= $slots ?> cases (4 × 4).</p>
    <table class="table table-sm table-bordered mb-3" style="max-width:760px">
      <thead class="thead-rbcd">
        <tr><th>Nom</th><th width="90">Ordre</th><th width="70" class="text-center">Active</th><th width="90" class="text-center">Produits</th><th width="100" class="text-right">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $c): ?>
        <tr>
          <form method="post" action="<?= base_url('admin/caisse/catalogue/categories/' . $c->id . '/update') ?>" id="cat-f<?= $c->id ?>"><?= csrf_field() ?></form>
          <td><input type="text" name="name" form="cat-f<?= $c->id ?>" class="form-control form-control-sm" maxlength="40" value="<?= esc($c->name) ?>" required></td>
          <td><input type="number" name="sort_order" form="cat-f<?= $c->id ?>" class="form-control form-control-sm" min="0" value="<?= (int) $c->sort_order ?>"></td>
          <td class="text-center align-middle"><input type="checkbox" name="is_active" value="1" form="cat-f<?= $c->id ?>" <?= $c->is_active ? 'checked' : '' ?>></td>
          <td class="text-center align-middle"><?= count(array_filter($products[(int) $c->id] ?? [], static fn ($p) => $p->is_active)) ?> / <?= $slots ?></td>
          <td class="text-right text-nowrap align-middle">
            <button form="cat-f<?= $c->id ?>" class="btn btn-xs btn-warning" title="Enregistrer"><i class="fas fa-save"></i></button>
            <?php if (empty($products[(int) $c->id])): ?>
            <form method="post" action="<?= base_url('admin/caisse/catalogue/categories/' . $c->id . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie vide ?')">
              <?= csrf_field() ?><button class="btn btn-xs btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <form method="post" action="<?= base_url('admin/caisse/catalogue/categories') ?>" class="form-inline">
      <?= csrf_field() ?>
      <input type="text" name="name" class="form-control mr-2" maxlength="40" placeholder="ex. Apéritifs" required>
      <button class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Ajouter une catégorie</button>
    </form>
  </div>
</div>

<?php foreach ($categories as $c):
    $list  = $products[(int) $c->id] ?? [];
    $bySlot = [];
    foreach ($list as $p) {
        if ($p->is_active && $p->position) {
            $bySlot[(int) $p->position] = $p;
        }
    }
    $free = array_values(array_diff(range(1, $slots), array_keys($bySlot)));
?>
<div class="card card-outline card-primary" id="cat<?= $c->id ?>">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-th mr-2"></i><?= esc($c->name) ?><?= $c->is_active ? '' : ' <span class="badge badge-secondary ml-2">catégorie inactive</span>' ?></h3>
    <div class="ml-auto text-muted small"><?= count($bySlot) ?> produit(s) en caisse · <?= count($free) ?> case(s) libre(s)</div>
  </div>
  <div class="card-body">
    <div class="d-flex flex-wrap" style="gap:24px">
      <div>
        <div class="text-muted small mb-1">Aperçu de la grille</div>
        <div class="bar-grid">
          <?php for ($i = 1; $i <= $slots; $i++): $p = $bySlot[$i] ?? null; ?>
          <div class="<?= $p ? '' : 'free' ?>" title="Case <?= $i ?>"><?php if ($p): ?><?= esc($p->name) ?><b><?= $eur($p->price) ?> €</b><?php else: ?><?= $i ?><?php endif; ?></div>
          <?php endfor; ?>
        </div>
      </div>
      <div style="flex:1;min-width:520px">
        <table class="table table-sm table-bordered mb-2">
          <thead class="thead-rbcd">
            <tr><th>Produit</th><th width="95">Prix (€)</th><th width="120" title="B = bar, G = gougouilles">TVA 21 %</th><th width="80">Case</th><th width="140">Catégorie</th><th width="60" class="text-center">Actif</th><th width="80" class="text-right">Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($list as $p):
                $mine = $p->position ? array_merge([(int) $p->position], $free) : $free;
                sort($mine);
            ?>
            <tr class="<?= $p->is_active ? '' : 'inactive' ?>">
              <form method="post" action="<?= base_url('admin/caisse/catalogue/produits/' . $p->id . '/update') ?>" id="p-f<?= $p->id ?>"><?= csrf_field() ?></form>
              <td><input type="text" name="name" form="p-f<?= $p->id ?>" class="form-control form-control-sm" maxlength="40" value="<?= esc($p->name) ?>" required></td>
              <td><input type="text" name="price" form="p-f<?= $p->id ?>" class="form-control form-control-sm text-right" inputmode="decimal" value="<?= $eur($p->price) ?>" required></td>
              <td>
                <select name="vat_group" form="p-f<?= $p->id ?>" class="form-control form-control-sm">
                  <option value="B" <?= $p->vat_group === 'B' ? 'selected' : '' ?>>B · bar</option>
                  <option value="G" <?= $p->vat_group === 'G' ? 'selected' : '' ?>>G · gougouilles</option>
                </select>
              </td>
              <td>
                <select name="position" form="p-f<?= $p->id ?>" class="form-control form-control-sm">
                  <?php if (!$p->position): ?><option value="">—</option><?php endif; ?>
                  <?php foreach ($mine as $slot): ?>
                  <option value="<?= $slot ?>" <?= (int) $p->position === $slot ? 'selected' : '' ?>><?= $slot ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <select name="category_id" form="p-f<?= $p->id ?>" class="form-control form-control-sm">
                  <?php foreach ($categories as $cc): ?>
                  <option value="<?= $cc->id ?>" <?= (int) $cc->id === (int) $p->category_id ? 'selected' : '' ?>><?= esc($cc->name) ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td class="text-center align-middle"><input type="checkbox" name="is_active" value="1" form="p-f<?= $p->id ?>" <?= $p->is_active ? 'checked' : '' ?>></td>
              <td class="text-right text-nowrap align-middle">
                <button form="p-f<?= $p->id ?>" class="btn btn-xs btn-warning" title="Enregistrer"><i class="fas fa-save"></i></button>
                <form method="post" action="<?= base_url('admin/caisse/catalogue/produits/' . $p->id . '/delete') ?>" class="d-inline"
                      onsubmit="return confirm('<?= isset($sold[(int) $p->id]) ? 'Ce produit a déjà été vendu : il sera désactivé (retiré de la caisse) et restera dans l\\\'historique. Continuer ?' : 'Supprimer ce produit ?' ?>')">
                  <?= csrf_field() ?><button class="btn btn-xs btn-danger" title="<?= isset($sold[(int) $p->id]) ? 'Désactiver' : 'Supprimer' ?>"><i class="fas fa-trash"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?>
            <tr><td colspan="7" class="text-muted text-center">Aucun produit dans cette catégorie.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if ($free): ?>
        <form method="post" action="<?= base_url('admin/caisse/catalogue/produits') ?>" class="form-inline">
          <?= csrf_field() ?>
          <input type="hidden" name="category_id" value="<?= $c->id ?>">
          <input type="text" name="name" class="form-control form-control-sm mr-2" maxlength="40" placeholder="Nouveau produit" required style="width:200px">
          <input type="text" name="price" class="form-control form-control-sm mr-2 text-right" inputmode="decimal" placeholder="Prix" required style="width:80px">
          <select name="vat_group" class="form-control form-control-sm mr-2">
            <option value="B" <?= $c->name === 'Snacks' ? '' : 'selected' ?>>B · bar</option>
            <option value="G" <?= $c->name === 'Snacks' ? 'selected' : '' ?>>G · gougouilles</option>
          </select>
          <select name="position" class="form-control form-control-sm mr-2" title="Case dans la grille">
            <?php foreach ($free as $slot): ?><option value="<?= $slot ?>">Case <?= $slot ?></option><?php endforeach; ?>
          </select>
          <button class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i>Ajouter</button>
        </form>
        <?php else: ?>
        <p class="text-muted small mb-0">Grille pleine : désactivez un produit pour libérer une case.</p>
        <?php endif; ?>
      </div>
    </div>
    <p class="text-muted small mt-3 mb-0">
      Un changement de prix ne modifie pas les tickets déjà enregistrés. Un produit déjà vendu n'est jamais supprimé : il est désactivé et libère sa case.
      La TVA « G » (gougouilles) et « B » (bar) alimente la ventilation de l'enveloppe.
    </p>
  </div>
</div>
<?php endforeach; ?>

<?= $this->endSection() ?>
