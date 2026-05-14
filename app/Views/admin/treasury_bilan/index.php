<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$months = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

$soldeN   = $totalRevN - $totalExpN;
$soldeNm1 = $totalRevNm1 - $totalExpNm1;
$deltaRev   = $totalRevN - $totalRevNm1;
$deltaExp   = $totalExpN - $totalExpNm1;
$deltaSolde = $soldeN - $soldeNm1;

$fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
$delta = function(float $d) use ($fmt): string {
    if ($d > 0) return '<span class="text-success"><i class="fas fa-arrow-up mr-1"></i>' . $fmt($d) . '</span>';
    if ($d < 0) return '<span class="text-danger"><i class="fas fa-arrow-down mr-1"></i>' . $fmt(abs($d)) . '</span>';
    return '<span class="text-muted">—</span>';
};
?>

<!-- Filtre année -->
<div class="d-flex align-items-center mb-4">
    <form method="get" class="d-flex align-items-center">
        <label class="mr-2 mb-0 font-weight-bold">Année :</label>
        <select name="year" class="form-control form-control-sm" style="width:100px" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Cartes résumé -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Recettes <?= $year ?></span>
                <span class="info-box-number"><?= $fmt($totalRevN) ?></span>
                <div class="progress"><div class="progress-bar bg-success" style="width:100%"></div></div>
                <span class="progress-description"><?= $delta($deltaRev) ?> vs <?= $prevYear ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-danger"><i class="fas fa-arrow-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Dépenses <?= $year ?></span>
                <span class="info-box-number"><?= $fmt($totalExpN) ?></span>
                <div class="progress"><div class="progress-bar bg-danger" style="width:100%"></div></div>
                <span class="progress-description"><?= $delta($deltaExp) ?> vs <?= $prevYear ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon <?= $soldeN >= 0 ? 'bg-primary' : 'bg-warning' ?>">
                <i class="fas fa-balance-scale"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Solde <?= $year ?></span>
                <span class="info-box-number <?= $soldeN >= 0 ? 'text-success' : 'text-danger' ?>">
                    <?= $fmt($soldeN) ?>
                </span>
                <div class="progress">
                    <div class="progress-bar <?= $soldeN >= 0 ? 'bg-success' : 'bg-danger' ?>" style="width:100%"></div>
                </div>
                <span class="progress-description"><?= $delta($deltaSolde) ?> vs <?= $prevYear ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Évolution mensuelle -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Évolution mensuelle</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>Mois</th>
                    <th class="text-right">Recettes <?= $year ?></th>
                    <th class="text-right">Dépenses <?= $year ?></th>
                    <th class="text-right">Solde <?= $year ?></th>
                    <th class="text-right text-muted" style="border-left:2px solid #dee2e6">Recettes <?= $prevYear ?></th>
                    <th class="text-right text-muted">Dépenses <?= $prevYear ?></th>
                    <th class="text-right text-muted">Solde <?= $prevYear ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($m = 1; $m <= 12; $m++):
                    $rN   = $revByMonthN[$m]   ?? 0;
                    $eN   = $expByMonthN[$m]   ?? 0;
                    $rNm1 = $revByMonthNm1[$m] ?? 0;
                    $eNm1 = $expByMonthNm1[$m] ?? 0;
                    $sN   = $rN - $eN;
                    $sNm1 = $rNm1 - $eNm1;
                    $emptyN   = ($rN == 0 && $eN == 0);
                    $emptyNm1 = ($rNm1 == 0 && $eNm1 == 0);
                ?>
                <tr>
                    <td class="font-weight-bold"><?= $months[$m - 1] ?></td>
                    <td class="text-right <?= $rN > 0 ? 'text-success font-weight-bold' : 'text-muted' ?>">
                        <?= $rN > 0 ? $fmt($rN) : '—' ?>
                    </td>
                    <td class="text-right <?= $eN > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                        <?= $eN > 0 ? $fmt($eN) : '—' ?>
                    </td>
                    <td class="text-right <?= !$emptyN ? ($sN >= 0 ? 'text-success' : 'text-danger') : 'text-muted' ?>">
                        <?= !$emptyN ? $fmt($sN) : '—' ?>
                    </td>
                    <td class="text-right text-muted" style="border-left:2px solid #dee2e6">
                        <?= $rNm1 > 0 ? $fmt($rNm1) : '—' ?>
                    </td>
                    <td class="text-right text-muted"><?= $eNm1 > 0 ? $fmt($eNm1) : '—' ?></td>
                    <td class="text-right text-muted">
                        <?= !$emptyNm1 ? $fmt($sNm1) : '—' ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td class="font-weight-bold">TOTAL</td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalRevN) ?></td>
                    <td class="text-right font-weight-bold text-danger"><?= $fmt($totalExpN) ?></td>
                    <td class="text-right font-weight-bold <?= $soldeN >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= $fmt($soldeN) ?>
                    </td>
                    <td class="text-right text-muted" style="border-left:2px solid #dee2e6"><?= $fmt($totalRevNm1) ?></td>
                    <td class="text-right text-muted"><?= $fmt($totalExpNm1) ?></td>
                    <td class="text-right text-muted"><?= $fmt($soldeNm1) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Ventilation par catégorie -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-arrow-up mr-2 text-success"></i>Recettes par catégorie — <?= $year ?>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($revByCatN)): ?>
                    <div class="p-3 text-muted">Aucune recette enregistrée.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-rbcd">
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-right">Montant</th>
                            <th class="text-right" style="width:60px">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($revCategories as $key => $label):
                            $m = $revByCatN[$key] ?? 0;
                            if ($m <= 0) continue;
                            $pct = $totalRevN > 0 ? round($m / $totalRevN * 100) : 0;
                        ?>
                        <tr>
                            <td><?= esc($label) ?></td>
                            <td class="text-right text-success font-weight-bold"><?= $fmt($m) ?></td>
                            <td class="text-right text-muted"><?= $pct ?> %</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="tfoot-total">
                        <tr>
                            <td class="font-weight-bold">TOTAL</td>
                            <td class="text-right font-weight-bold text-success"><?= $fmt($totalRevN) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-arrow-down mr-2 text-danger"></i>Dépenses par catégorie — <?= $year ?>
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($expByCatN)): ?>
                    <div class="p-3 text-muted">Aucune dépense enregistrée.</div>
                <?php else: ?>
                <table class="table table-sm mb-0">
                    <thead class="thead-rbcd">
                        <tr>
                            <th>Catégorie</th>
                            <th class="text-right">Montant</th>
                            <th class="text-right" style="width:60px">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expCategories as $key => $label):
                            $m = $expByCatN[$key] ?? 0;
                            if ($m <= 0) continue;
                            $pct = $totalExpN > 0 ? round($m / $totalExpN * 100) : 0;
                        ?>
                        <tr>
                            <td><?= esc($label) ?></td>
                            <td class="text-right text-danger font-weight-bold"><?= $fmt($m) ?></td>
                            <td class="text-right text-muted"><?= $pct ?> %</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="tfoot-total">
                        <tr>
                            <td class="font-weight-bold">TOTAL</td>
                            <td class="text-right font-weight-bold text-danger"><?= $fmt($totalExpN) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
