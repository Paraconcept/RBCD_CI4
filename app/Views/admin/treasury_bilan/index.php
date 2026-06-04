<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$months   = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$soldeN   = $totalRevAllN - $totalExpN;
$soldeNm1 = $totalRevAllNm1 - $totalExpNm1;
$deltaRev   = $totalRevAllN - $totalRevAllNm1;
$deltaExp   = $totalExpN    - $totalExpNm1;
$deltaSolde = $soldeN       - $soldeNm1;

$fmt = fn(float $v): string => number_format($v, 2, ',', '.') . ' €';

// $arrowPlus / $arrowMinus = icône FontAwesome selon le contexte (recettes, dépenses, solde)
$delta = function(float $d, float $nm1, string $arrowPlus, string $arrowMinus) use ($fmt, $prevYear): string {
    if ($d > 0)     $badge = '<span class="text-success"><i class="fas fa-' . $arrowPlus  . ' mr-1"></i>' . $fmt($d)      . '</span>';
    elseif ($d < 0) $badge = '<span class="text-danger"><i class="fas fa-'  . $arrowMinus . ' mr-1"></i>' . $fmt(abs($d)) . '</span>';
    else            $badge = '<span class="text-muted">—</span>';
    return $badge . ' vs ' . $prevYear . ' <span class="text-muted small">(' . $fmt($nm1) . ')</span>';
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
                <span class="progress-description"><?= $delta($deltaRev, $totalRevAllNm1, 'arrow-up', 'arrow-up') ?></span>
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
                <span class="progress-description"><?= $delta($deltaExp, $totalExpNm1, 'arrow-down', 'arrow-down') ?></span>
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
                <span class="progress-description"><?= $delta($deltaSolde, $soldeNm1, 'arrow-up', 'arrow-down') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Sources des recettes -->
<div class="card card-outline card-success mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-arrow-up mr-2 text-success"></i>Sources des recettes <?= $year ?></h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <tbody>
                <?php $pctM = $totalRevAllN > 0 ? round($totalRevManualN / $totalRevAllN * 100) : 0; ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-edit text-muted mr-2"></i>Recettes manuelles
                    </td>
                    <td class="text-right font-weight-bold text-success" style="width:160px"><?= $fmt($totalRevManualN) ?></td>
                    <td class="text-right text-muted text-nowrap" style="width:90px"><?= $pctM ?> %</td>
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
                    <td class="text-right text-muted text-nowrap"><?= $pctC ?> %</td>
                </tr>

                <?php $pctE = $totalRevAllN > 0 ? round($totalEnvN / $totalRevAllN * 100) : 0; ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-cash-register text-muted mr-2"></i>Bar / Enveloppes de caisse
                    </td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalEnvN) ?></td>
                    <td class="text-right text-muted text-nowrap"><?= $pctE ?> %</td>
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

<!-- Dépenses par catégorie -->
<div class="card card-outline card-danger mb-4">
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
            <tbody>
                <?php foreach ($expCategories as $key => $label):
                    $m = $expByCatN[$key] ?? 0;
                    if ($m <= 0) continue;
                    $pct = $totalExpN > 0 ? round($m / $totalExpN * 100) : 0;
                ?>
                <tr>
                    <td class="font-weight-bold">
                        <i class="fas fa-tag text-muted mr-2"></i><?= esc($label) ?>
                    </td>
                    <td class="text-right font-weight-bold text-danger" style="width:160px"><?= $fmt($m) ?></td>
                    <td class="text-right text-muted text-nowrap" style="width:90px"><?= $pct ?> %</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td class="font-weight-bold">TOTAL DÉPENSES</td>
                    <td class="text-right font-weight-bold text-danger"><?= $fmt($totalExpN) ?></td>
                    <td class="text-right text-muted text-nowrap">100 %</td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Évolution mensuelle -->
<div class="card card-outline card-primary mb-4">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Évolution mensuelle</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-sm table-hover table-bordered mb-0" style="white-space:nowrap">
            <thead class="thead-rbcd">
                <tr>
                    <th rowspan="2" style="width:100px;vertical-align:middle">Mois</th>
                    <th class="text-right" rowspan="2" style="vertical-align:middle">Rec. man.</th>
                    <th class="text-right" rowspan="2" style="vertical-align:middle">Cotisations</th>
                    <th class="text-center" colspan="3" style="border-left:2px solid #adb5bd;border-right:2px solid #adb5bd">Bar</th>
                    <th class="text-right text-success" rowspan="2" style="vertical-align:middle">Σ Recettes</th>
                    <th class="text-right text-danger" rowspan="2" style="vertical-align:middle">Dépenses</th>
                    <th class="text-right" rowspan="2" style="vertical-align:middle">Solde</th>
                </tr>
                <tr>
                    <th class="text-right" style="border-left:2px solid #adb5bd">6%</th>
                    <th class="text-right">12%</th>
                    <th class="text-right" style="border-right:2px solid #adb5bd">21%</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($m = 1; $m <= 12; $m++):
                    $rMan  = $revManualByMonthN[$m] ?? 0;
                    $rCot  = $cotisMonthlyN[$m]     ?? 0;
                    $rEnv  = $envMonthlyN[$m]        ?? 0;
                    $r6    = $env6ByMonthN[$m]       ?? 0;
                    $r12   = $env12ByMonthN[$m]      ?? 0;
                    $r21   = $env21ByMonthN[$m]      ?? 0;
                    $rAll  = $rMan + $rCot + $rEnv;
                    $eN    = $expByMonthN[$m]         ?? 0;
                    $sN    = $rAll - $eN;
                    $emptyN = ($rAll == 0 && $eN == 0);
                ?>
                <tr>
                    <td class="font-weight-bold"><?= $months[$m - 1] ?></td>
                    <td class="text-right text-muted"><?= $rMan > 0 ? $fmt($rMan) : '—' ?></td>
                    <td class="text-right text-muted"><?= $rCot > 0 ? $fmt($rCot) : '—' ?></td>
                    <td class="text-right text-muted" style="border-left:2px solid #adb5bd"><?= $r6  > 0 ? $fmt($r6)  : '—' ?></td>
                    <td class="text-right text-muted"><?= $r12 > 0 ? $fmt($r12) : '—' ?></td>
                    <td class="text-right text-muted" style="border-right:2px solid #adb5bd"><?= $r21 > 0 ? $fmt($r21) : '—' ?></td>
                    <td class="text-right <?= $rAll > 0 ? 'text-success font-weight-bold' : 'text-muted' ?>">
                        <?= $rAll > 0 ? $fmt($rAll) : '—' ?>
                    </td>
                    <td class="text-right <?= $eN > 0 ? 'text-danger font-weight-bold' : 'text-muted' ?>">
                        <?= $eN > 0 ? $fmt($eN) : '—' ?>
                    </td>
                    <td class="text-right <?= !$emptyN ? ($sN >= 0 ? 'text-success' : 'text-danger') : 'text-muted' ?>">
                        <?= !$emptyN ? $fmt($sN) : '—' ?>
                    </td>
                </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot class="tfoot-total">
                <tr>
                    <td class="font-weight-bold">TOTAL</td>
                    <td class="text-right"><?= $fmt($totalRevManualN) ?></td>
                    <td class="text-right"><?= $fmt($totalCotisN) ?></td>
                    <td class="text-right" style="border-left:2px solid #adb5bd"><?= array_sum($env6ByMonthN)  > 0 ? $fmt(array_sum($env6ByMonthN))  : '—' ?></td>
                    <td class="text-right"><?= array_sum($env12ByMonthN) > 0 ? $fmt(array_sum($env12ByMonthN)) : '—' ?></td>
                    <td class="text-right" style="border-right:2px solid #adb5bd"><?= $fmt(array_sum($env21ByMonthN)) ?></td>
                    <td class="text-right font-weight-bold text-success"><?= $fmt($totalRevAllN) ?></td>
                    <td class="text-right font-weight-bold text-danger"><?= $fmt($totalExpN) ?></td>
                    <td class="text-right font-weight-bold <?= $soldeN >= 0 ? 'text-success' : 'text-danger' ?>"><?= $fmt($soldeN) ?></td>
                </tr>
            </tfoot>
        </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
