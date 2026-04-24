<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$saison = $year . '-' . ($year + 1);
$pct = fn(int $n, int $t) => $t > 0 ? round($n / $t * 100) : 0;
?>

<!-- Sélecteur de saison -->
<div class="d-flex align-items-center mb-3">
    <form method="get" class="d-flex align-items-center">
        <label class="mr-2 mb-0 font-weight-bold">Saison :</label>
        <select name="year" class="form-control form-control-sm mr-2" style="width:140px" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>>
                    <?= $y ?>&ndash;<?= $y + 1 ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <span class="text-muted small"><?= $stats['total'] ?> membres actifs</span>
</div>

<!-- ── Cartes de stats ───────────────────────────────────────────── -->
<div class="row">

    <!-- RBCD -->
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-white border">
            <div class="inner">
                <h3><?= $stats['rbcdPaid'] ?> <sup class="text-muted" style="font-size:.5em">/ <?= $stats['total'] ?></sup></h3>
                <p>Cotisation RBCD payée</p>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar" style="width:<?= $pct($stats['rbcdPaid'], $stats['total']) ?>%;background-color:#84252B"></div>
                </div>
                <small class="text-muted"><?= $pct($stats['rbcdPaid'], $stats['total']) ?> % des membres</small>
            </div>
            <div class="icon" style="position:absolute;right:10px;bottom:60px;z-index:0;">
                <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>"
                     style="height:70px;width:auto;opacity:1;object-fit:contain;">
            </div>
        </div>
    </div>

    <!-- FRBB -->
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-white border">
            <div class="inner">
                <h3><?= $stats['frbbPaid'] ?> <sup class="text-muted" style="font-size:.5em">/ <?= $stats['frbbTotal'] ?></sup></h3>
                <p>Cotisation FRBB payée</p>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-warning" style="width:<?= $pct($stats['frbbPaid'], $stats['frbbTotal']) ?>%"></div>
                </div>
                <small class="text-muted"><?= $pct($stats['frbbPaid'], $stats['frbbTotal']) ?> % des fédérés</small>
            </div>
            <div class="icon" style="position:absolute;right:10px;bottom:60px;z-index:0;">
                <img src="<?= base_url('assets/images/Ecusson_FRBB-LL.png') ?>"
                     style="height:70px;width:auto;opacity:1;object-fit:contain;">
            </div>
        </div>
    </div>

    <!-- Forfait H1 -->
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-white border">
            <div class="inner">
                <h3><?= $stats['h1Paid'] ?> <sup class="text-muted" style="font-size:.5em">/ <?= $stats['h1Total'] ?></sup></h3>
                <p>Forfait H1 payé</p>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-success" style="width:<?= $pct($stats['h1Paid'], $stats['h1Total']) ?>%"></div>
                </div>
                <small class="text-muted"><?= $pct($stats['h1Paid'], $stats['h1Total']) ?> % des souscrits</small>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>

    <!-- Forfait H2 -->
    <div class="col-lg-3 col-sm-6">
        <div class="small-box bg-white border">
            <div class="inner">
                <h3><?= $stats['h2Paid'] ?> <sup class="text-muted" style="font-size:.5em">/ <?= $stats['h2Total'] ?></sup></h3>
                <p>Forfait H2 payé</p>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-success" style="width:<?= $pct($stats['h2Paid'], $stats['h2Total']) ?>%"></div>
                </div>
                <small class="text-muted"><?= $pct($stats['h2Paid'], $stats['h2Total']) ?> % des souscrits</small>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
    </div>

</div>

<!-- ── Tableau ───────────────────────────────────────────────────── -->
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list mr-2"></i>État des paiements — Saison <?= esc($saison) ?>
        </h3>
    </div>
    <div class="card-body p-0">
        <table id="treasuryTable" class="table table-bordered table-hover table-striped table-sm mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>Membre</th>
                    <th class="text-center">RBCD</th>
                    <th class="text-center">FRBB</th>
                    <th class="text-center">Forfait H1</th>
                    <th class="text-center">Forfait H2</th>
                    <th class="text-center no-sort">Fiche</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
            <?php
                $editUrl = $r->payment_id
                    ? base_url("admin/members/{$r->id}/payments/{$r->payment_id}/edit")
                    : base_url("admin/members/{$r->id}/payments/add");
            ?>
            <tr>
                <td>
                    <a href="<?= $editUrl ?>">
                        <?= esc($r->last_name . ' ' . $r->first_name) ?>
                    </a>
                </td>

                <!-- RBCD -->
                <td class="text-center">
                    <?php if ($r->payment_id === null): ?>
                        <span class="badge badge-secondary">—</span>
                    <?php elseif ($r->rbcd_paid): ?>
                        <span class="badge badge-success" title="<?= $r->rbcd_paid_date ? date('d/m/Y', strtotime($r->rbcd_paid_date)) : '' ?>">
                            <i class="fas fa-check"></i>
                            <?= $r->rbcd_paid_date ? date('d/m/Y', strtotime($r->rbcd_paid_date)) : 'Payé' ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger"><i class="fas fa-times"></i> Non payé</span>
                    <?php endif; ?>
                </td>

                <!-- FRBB -->
                <td class="text-center">
                    <?php if (!$r->is_federated): ?>
                        <span class="text-muted">—</span>
                    <?php elseif ($r->payment_id === null): ?>
                        <span class="badge badge-secondary">—</span>
                    <?php elseif ($r->frbb_paid): ?>
                        <span class="badge badge-success" title="<?= $r->frbb_paid_date ? date('d/m/Y', strtotime($r->frbb_paid_date)) : '' ?>">
                            <i class="fas fa-check"></i>
                            <?= $r->frbb_paid_date ? date('d/m/Y', strtotime($r->frbb_paid_date)) : 'Payé' ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger"><i class="fas fa-times"></i> Non payé</span>
                    <?php endif; ?>
                </td>

                <!-- Forfait H1 -->
                <td class="text-center">
                    <?php if (!$r->payment_id || !$r->forfait_h1_choice): ?>
                        <span class="text-muted">—</span>
                    <?php elseif ($r->forfait_h1_paid): ?>
                        <span class="badge badge-success" title="<?= $r->forfait_h1_paid_date ? date('d/m/Y', strtotime($r->forfait_h1_paid_date)) : '' ?>">
                            <i class="fas fa-check"></i>
                            <?= $r->forfait_h1_paid_date ? date('d/m/Y', strtotime($r->forfait_h1_paid_date)) : 'Payé' ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> En attente</span>
                    <?php endif; ?>
                </td>

                <!-- Forfait H2 -->
                <td class="text-center">
                    <?php if (!$r->payment_id || !$r->forfait_h2_choice): ?>
                        <span class="text-muted">—</span>
                    <?php elseif ($r->forfait_h2_paid): ?>
                        <span class="badge badge-success" title="<?= $r->forfait_h2_paid_date ? date('d/m/Y', strtotime($r->forfait_h2_paid_date)) : '' ?>">
                            <i class="fas fa-check"></i>
                            <?= $r->forfait_h2_paid_date ? date('d/m/Y', strtotime($r->forfait_h2_paid_date)) : 'Payé' ?>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning text-dark"><i class="fas fa-clock"></i> En attente</span>
                    <?php endif; ?>
                </td>

                <!-- Lien fiche -->
                <td class="text-center">
                    <a href="<?= $editUrl ?>" class="btn btn-xs btn-info" title="Modifier les paiements">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    $('#treasuryTable').DataTable({
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
});
</script>
<?= $this->endSection() ?>
