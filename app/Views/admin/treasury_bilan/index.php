<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$months   = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
$soldeN   = $totalRevAllN - $totalExpN;
$soldeNm1 = $totalRevAllNm1 - $totalExpNm1;
$deltaRev   = $totalRevAllN - $totalRevAllNm1;
$deltaExp   = $totalExpN    - $totalExpNm1;
$deltaSolde = $soldeN       - $soldeNm1;

$fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';
$delta = function(float $d) use ($fmt): string {
    if ($d > 0) return '<span class="text-success"><i class="fas fa-arrow-up mr-1"></i>' . $fmt($d) . '</span>';
    if ($d < 0) return '<span class="text-danger"><i class="fas fa-arrow-down mr-1"></i>' . $fmt(abs($d)) . '</span>';
    return '<span class="text-muted">—</span>';
};
?>

<!-- Filtre année + bouton export -->
<div class="d-flex align-items-center mb-4" style="gap:.75rem">
    <form method="get" class="d-flex align-items-center">
        <label class="mr-2 mb-0 font-weight-bold">Année :</label>
        <select name="year" class="form-control form-control-sm" style="width:100px" onchange="this.form.submit()">
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <a href="<?= base_url("admin/treasury/bilan/export?year={$year}") ?>" class="btn btn-sm btn-outline-success">
        <i class="fas fa-file-excel mr-1"></i> Export Excel
    </a>
</div>

<!-- Cartes résumé (toutes sources confondues) -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-success"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Recettes totales <?= $year ?></span>
                <span class="info-box-number"><?= $fmt($totalRevAllN) ?></span>
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

<!-- Sources des recettes -->
<div class="card card-outline card-success mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-layer-group mr-2"></i>Sources des recettes <?= $year ?></h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <tbody>
                <?php $pctM = $totalRevAllN > 0 ? round($totalRevManualN / $totalRevAllN * 100) : 0; ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-pencil-alt text-muted mr-2"></i>Recettes manuelles
                    </td>
                    <td class="text-right font-weight-bold text-success" style="width:160px"><?= $fmt($totalRevManualN) ?></td>
                    <td class="text-right text-muted" style="width:60px"><?= $pctM ?> %</td>
                </tr>
                <?php foreach ($revCategories as $key => $label):
                    $m = $revByCatN[$key] ?? 0;
                    if ($m <= 0) continue;
                ?>
                <tr style="background:#f8fff8">
                    <td class="text-muted pl-4" style="font-size:.9rem">
                        <i class="fas fa-angle-right mr-2 text-success" style="font-size:.75rem"></i><?= esc($label) ?>
                    </td>
                    <td class="text-right text-success" style="font-size:.9rem"><?= $fmt($m) ?></td>
                    <td></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty(array_filter($revByCatN))): ?>
                <tr style="background:#f8fff8">
                    <td class="text-muted pl-4" style="font-size:.9rem"><em>Aucune recette manuelle enregistrée</em></td>
                    <td></td><td></td>
                </tr>
                <?php endif; ?>

                <?php $pctC = $totalRevAllN > 0 ? round($totalCotisN / $totalRevAllN * 100) : 0; ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-users text-muted mr-2"></i>Cotisations
                        <small class="text-muted font-weight-normal">(RBCD <?= $fmt($cotisAmount) ?>/an · forfait <?= $fmt($forfaitAmount) ?>/sem.)</small>
                    </td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalCotisN) ?></td>
                    <td class="text-right text-muted"><?= $pctC ?> %</td>
                </tr>

                <?php $pctE = $totalRevAllN > 0 ? round($totalEnvN / $totalRevAllN * 100) : 0; ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-cash-register text-muted mr-2"></i>Bar / Enveloppes de caisse
                    </td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalEnvN) ?></td>
                    <td class="text-right text-muted"><?= $pctE ?> %</td>
                </tr>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td class="font-weight-bold">TOTAL RECETTES</td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalRevAllN) ?></td>
                    <td class="text-right text-muted">100 %</td>
                </tr>
            </tfoot>
        </table>
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
                    <th class="text-right">Rec. man.</th>
                    <th class="text-right">Cotisations</th>
                    <th class="text-right">Bar</th>
                    <th class="text-right text-success">Σ Recettes <?= $year ?></th>
                    <th class="text-right text-danger">Dépenses <?= $year ?></th>
                    <th class="text-right">Solde <?= $year ?></th>
                    <th class="text-right text-muted" style="border-left:2px solid #dee2e6">Σ Rec. <?= $prevYear ?></th>
                    <th class="text-right text-muted">Dép. <?= $prevYear ?></th>
                    <th class="text-right text-muted">Solde <?= $prevYear ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($m = 1; $m <= 12; $m++):
                    $rMan = $revManualByMonthN[$m]   ?? 0;
                    $rCot = $cotisMonthlyN[$m]        ?? 0;
                    $rEnv = $envMonthlyN[$m]          ?? 0;
                    $rAll = $rMan + $rCot + $rEnv;
                    $eN   = $expByMonthN[$m]          ?? 0;
                    $sN   = $rAll - $eN;

                    $rAllNm1 = ($revManualByMonthNm1[$m] ?? 0) + ($cotisMonthlyNm1[$m] ?? 0) + ($envMonthlyNm1[$m] ?? 0);
                    $eNm1    = $expByMonthNm1[$m] ?? 0;
                    $sNm1    = $rAllNm1 - $eNm1;
                    $emptyN  = ($rAll == 0 && $eN == 0);
                ?>
                <tr>
                    <td class="font-weight-bold"><?= $months[$m - 1] ?></td>
                    <td class="text-right text-muted"><?= $rMan > 0 ? $fmt($rMan) : '—' ?></td>
                    <td class="text-right text-muted"><?= $rCot > 0 ? $fmt($rCot) : '—' ?></td>
                    <td class="text-right text-muted"><?= $rEnv > 0 ? $fmt($rEnv) : '—' ?></td>
                    <td class="text-right <?= $rAll > 0 ? 'text-success font-weight-bold' : 'text-muted' ?>">
                        <?= $rAll > 0 ? $fmt($rAll) : '—' ?>
                    </td>
                    <td class="text-right <?= $eN > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                        <?= $eN > 0 ? $fmt($eN) : '—' ?>
                    </td>
                    <td class="text-right <?= !$emptyN ? ($sN >= 0 ? 'text-success' : 'text-danger') : 'text-muted' ?>">
                        <?= !$emptyN ? $fmt($sN) : '—' ?>
                    </td>
                    <td class="text-right text-muted" style="border-left:2px solid #dee2e6">
                        <?= $rAllNm1 > 0 ? $fmt($rAllNm1) : '—' ?>
                    </td>
                    <td class="text-right text-muted"><?= $eNm1 > 0 ? $fmt($eNm1) : '—' ?></td>
                    <td class="text-right text-muted">
                        <?= ($rAllNm1 > 0 || $eNm1 > 0) ? $fmt($sNm1) : '—' ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td class="font-weight-bold">TOTAL</td>
                    <td class="text-right"><?= $fmt($totalRevManualN) ?></td>
                    <td class="text-right"><?= $fmt($totalCotisN) ?></td>
                    <td class="text-right"><?= $fmt($totalEnvN) ?></td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalRevAllN) ?></td>
                    <td class="text-right font-weight-bold text-danger"><?= $fmt($totalExpN) ?></td>
                    <td class="text-right font-weight-bold <?= $soldeN >= 0 ? 'text-success' : 'text-danger' ?>"><?= $fmt($soldeN) ?></td>
                    <td class="text-right text-muted" style="border-left:2px solid #dee2e6"><?= $fmt($totalRevAllNm1) ?></td>
                    <td class="text-right text-muted"><?= $fmt($totalExpNm1) ?></td>
                    <td class="text-right text-muted"><?= $fmt($soldeNm1) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Dépenses par catégorie -->
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
                <tr><th>Catégorie</th><th class="text-right">Montant</th><th class="text-right" style="width:60px">%</th></tr>
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

<?= $this->endSection() ?>
