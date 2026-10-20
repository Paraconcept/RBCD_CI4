<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php
$eur   = static fn ($n): string => number_format((float) $n, 2, ',', ' ') . ' €';
$fr    = static fn (?string $d): string => $d ? date('d/m/Y', strtotime($d)) : '';
$max   = max(1, ...array_map(static fn ($r) => (int) $r->quantity, $rows ?: [(object) ['quantity' => 0]]));
$total = ['qty' => 0, 'amount' => 0.0];
$today = date('Y-m-d');
$presets = [
    '7 derniers jours'  => date('Y-m-d', strtotime('-6 days')),
    '30 derniers jours' => date('Y-m-d', strtotime('-29 days')),
    'Depuis le début'   => $first ?? $today,
];
$link = static fn (string $f): string => base_url('admin/caisse/stocks') . '?' . http_build_query(array_filter(['from' => $f, 'to' => $today, 'category' => $categoryId]));
?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center flex-wrap">
    <h3 class="card-title"><i class="fas fa-boxes mr-2"></i>Produits vendus du <?= $fr($from) ?> au <?= $fr($to) ?></h3>
    <div class="ml-auto">
      <?php foreach ($presets as $label => $f): ?>
        <a href="<?= $link($f) ?>" class="btn btn-sm <?= $from === $f && $to === $today ? 'btn-primary' : 'btn-outline-primary' ?> ml-1"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Quantités vendues à la caisse (tickets payés, services ouverts compris), du plus vendu au moins vendu.
      Pour préparer la commande, choisissez comme date de début le dernier passage du brasseur.
      Les produits du catalogue qui ne se sont pas vendus sur la période sont en fin de liste.
    </p>

    <form method="get" class="form-inline mb-3">
      <label for="from" class="mr-2 font-weight-bold">Du</label>
      <input type="date" name="from" id="from" class="form-control form-control-sm mr-3" value="<?= esc($from) ?>" max="<?= $today ?>">
      <label for="to" class="mr-2 font-weight-bold">au</label>
      <input type="date" name="to" id="to" class="form-control form-control-sm mr-3" value="<?= esc($to) ?>">
      <select name="category" class="form-control form-control-sm mr-2">
        <option value="">Toutes les catégories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c->id ?>" <?= (int) $c->id === $categoryId ? 'selected' : '' ?>><?= esc($c->name) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Afficher</button>
    </form>

    <p class="mb-2"><b><?= $services ?></b> service<?= $services > 1 ? 's' : '' ?> sur la période.</p>

    <?php if (!$rows): ?>
      <p class="text-muted text-center mb-0">Aucun produit.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th class="text-center" style="width:3rem">#</th><th>Produit</th><th>Catégorie</th>
          <th class="text-right" style="width:7rem">Quantité</th><th style="width:30%"></th>
          <th class="text-right">Par service</th><th class="text-right">Montant</th><th>Dernière vente</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $i => $r):
            $qty = (int) $r->quantity;
            $total['qty']    += $qty;
            $total['amount'] += (float) $r->amount;
        ?>
        <tr class="<?= $qty === 0 ? 'text-muted' : '' ?>">
          <td class="text-center"><?= $qty ? $i + 1 : '' ?></td>
          <td>
            <?= esc($r->name) ?>
            <?php if ($r->price !== null): ?><small class="text-muted">· <?= $eur($r->price) ?></small><?php endif; ?>
            <?php if ($r->product_id === null || (int) $r->is_active === 0): ?><span class="badge badge-secondary ml-1">retiré du catalogue</span><?php endif; ?>
          </td>
          <td><?= esc($r->category ?? 'Divers') ?></td>
          <td class="text-right font-weight-bold" style="font-size:1.05rem"><?= $qty ?></td>
          <td class="align-middle">
            <?php if ($qty): ?>
            <div class="progress" style="height:.9rem"><div class="progress-bar bg-primary" style="width:<?= round($qty / $max * 100, 1) ?>%"></div></div>
            <?php endif; ?>
          </td>
          <td class="text-right"><?= $qty && $services ? number_format($qty / $services, 1, ',', ' ') : '' ?></td>
          <td class="text-right"><?= $qty ? $eur($r->amount) : '' ?></td>
          <td><?= $fr($r->last_sold) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="3">Total</td>
          <td class="text-right"><?= $total['qty'] ?></td>
          <td colspan="2"></td>
          <td class="text-right"><?= $eur($total['amount']) ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
