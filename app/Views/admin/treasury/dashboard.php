<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$saison = $season . '-' . ($season + 1);
$pct = fn(int $n, int $t) => $t > 0 ? round($n / $t * 100) : 0;
$fmt = fn(?string $d) => $d ? date('d/m/Y', strtotime($d)) : 'Payé';

$paidCell = function (bool $paid, ?string $date, bool $pending = false) use ($fmt): string {
    if ($paid) {
        return '<span class="badge badge-success"><i class="fas fa-check"></i> ' . $fmt($date) . '</span>';
    }
    return $pending
        ? '<span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> En attente</span>'
        : '<span class="badge badge-danger"><i class="fas fa-times"></i> Non payé</span>';
};

$cards = [
    ['n' => $stats['frbbPaid'],   't' => $stats['frbbTotal'], 'label' => "FRBB {$saison}",   'unit' => 'des fédérés',  'bar' => 'bg-warning', 'img' => 'Ecusson_FRBB-LL.png', 'h' => 70],
    ['n' => $stats['rbcdH1Paid'], 't' => $stats['total'],     'label' => 'RBCD 1 (jan–juin)', 'unit' => 'des membres',  'bar' => 'bg-rbcd',    'img' => 'Ecusson_RBCD.png',    'h' => 70],
    ['n' => $stats['rbcdH2Paid'], 't' => $stats['total'],     'label' => 'RBCD 2 (juil–déc)', 'unit' => 'des membres',  'bar' => 'bg-rbcd',    'img' => 'Ecusson_RBCD.png',    'h' => 70],
    ['n' => $stats['f1Paid'],     't' => $stats['f1Total'],   'label' => 'Effectif 1',        'unit' => 'des souscrits', 'bar' => 'bg-success', 'img' => '75euros.gif',         'h' => 80],
    ['n' => $stats['f2Paid'],     't' => $stats['f2Total'],   'label' => 'Effectif 2',        'unit' => 'des souscrits', 'bar' => 'bg-success', 'img' => '75euros.gif',         'h' => 80],
];
?>

<style>
    .bg-rbcd { background-color: #84252B; }
</style>

<!-- Sélecteur d'année -->
<div class="d-flex align-items-center mb-3">
    <form method="get" class="d-flex align-items-center">
        <label class="mr-2 mb-0 font-weight-bold">Année :</label>
        <select name="year" class="form-control form-control-sm mr-2" style="width:110px" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="<?= base_url('admin/treasury/export?year=' . $year) ?>" class="btn btn-success btn-sm mr-3">
        <i class="fas fa-download mr-1"></i> Exporter Excel
    </a>
    <span class="text-muted small"><?= $stats['total'] ?> membres actifs</span>
</div>

<!-- ── Cartes de stats ───────────────────────────────────────────── -->
<div class="row">
    <?php foreach ($cards as $c): ?>
    <div class="col-lg col-sm-6">
        <div class="small-box bg-white border">
            <div class="inner">
                <h3><?= $c['n'] ?> <sup class="text-muted" style="font-size:.5em">/ <?= $c['t'] ?></sup></h3>
                <p><?= esc($c['label']) ?></p>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar <?= $c['bar'] ?>" style="width:<?= $pct($c['n'], $c['t']) ?>%"></div>
                </div>
                <small class="text-muted"><?= $pct($c['n'], $c['t']) ?> % <?= $c['unit'] ?></small>
            </div>
            <div class="icon" style="position:absolute;right:10px;bottom:60px;z-index:0;">
                <img src="<?= base_url('assets/images/' . $c['img']) ?>"
                     style="height:<?= $c['h'] ?>px;width:auto;opacity:1;object-fit:contain;">
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Tableau ───────────────────────────────────────────────────── -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-2"></i>État des paiements — Année <?= $year ?>
            <small class="text-muted ml-1">(FRBB : saison <?= esc($saison) ?>)</small>
        </h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive table-sticky-head">
            <table id="treasuryTable" class="table table-bordered table-hover table-striped table-sm mb-0">
                <thead class="thead-rbcd">
                    <tr>
                        <th>Membre</th>
                        <th class="text-center no-sort" style="width:20px"></th>
                        <th class="text-center">FRBB<br><small><?= esc($saison) ?></small></th>
                        <th class="text-center">RBCD 1<br><small>jan–juin</small></th>
                        <th class="text-center">RBCD 2<br><small>juil–déc</small></th>
                        <th class="text-center">Effectif 1<br><small>jan–juin</small></th>
                        <th class="text-center">Effectif 2<br><small>juil–déc</small></th>
                        <th class="text-center no-sort">Fiche</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                <?php $editUrl = base_url("admin/members/{$r->id}/payments/{$year}") . '?ref=treasury'; ?>
                <tr>
                    <td>
                        <a href="<?= $editUrl ?>">
                            <?= esc($r->last_name . ' ' . $r->first_name) ?>
                        </a>
                    </td>

                    <!-- Logo FRBB -->
                    <td class="text-center p-0" style="width:20px;vertical-align:middle;">
                        <?php if ($r->is_federated): ?>
                            <img src="<?= base_url('assets/images/frbb_kbbb_logo_100.png') ?>"
                                style="height:24px;width:auto;" title="Fédéré FRBB">
                        <?php endif; ?>
                    </td>

                    <!-- FRBB -->
                    <td class="text-center">
                        <?php if (!$r->is_federated): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <?= $paidCell((bool) $r->frbb_paid, $r->frbb_paid_date) ?>
                        <?php endif; ?>
                    </td>

                    <!-- RBCD 1 / RBCD 2 -->
                    <td class="text-center"><?= $paidCell((bool) $r->rbcd_h1_paid, $r->rbcd_h1_paid_date) ?></td>
                    <td class="text-center"><?= $paidCell((bool) $r->rbcd_h2_paid, $r->rbcd_h2_paid_date) ?></td>

                    <!-- Effectif 1 / Effectif 2 -->
                    <?php foreach (['h1', 'h2'] as $h): ?>
                    <td class="text-center">
                        <?php if (!$r->{"forfait_{$h}_choice"}): ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <?= $paidCell((bool) $r->{"forfait_{$h}_paid"}, $r->{"forfait_{$h}_paid_date"}, true) ?>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>

                    <!-- Lien fiche -->
                    <td class="text-center">
                        <a href="<?= $editUrl ?>" class="btn btn-xs btn-info tt-rbcd"
                           data-toggle="tooltip" data-placement="top"
                           title="Modifier<br>les paiements de<br><?= esc($r->last_name . ' ' . $r->first_name) ?>">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    const tooltipOpts = {
        html:     true,
        template: '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
    };
    const table = $('#treasuryTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 50,
        columnDefs: [{ orderable: false, targets: 'no-sort' }],
        language: {
            search: 'Rechercher :',
            lengthMenu: 'Afficher _MENU_ membres',
            info: '_START_ à _END_ sur _TOTAL_ membres',
            paginate: { previous: 'Préc.', next: 'Suiv.' },
        }
    });
    $('.tt-rbcd').tooltip(tooltipOpts);
    table.on('draw.dt', function() {
        $('.tt-rbcd').tooltip('dispose').tooltip(tooltipOpts);
    });
});
</script>
<?= $this->endSection() ?>
