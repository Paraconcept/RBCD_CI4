<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
/* Wrapper scrollable */
.stats-scroll-wrap {
    overflow-x: auto;
    position: relative;
}

/* Table */
.stats-table {
    border-collapse: collapse;
    white-space: nowrap;
    font-size: .8rem;
}
.stats-table th,
.stats-table td {
    border: 1px solid #dee2e6;
    padding: 3px 6px;
    vertical-align: middle;
    text-align: center;
}

/* Sticky player name column */
.col-name {
    position: sticky;
    left: 0;
    z-index: 2;
    text-align: left !important;
    min-width: 130px;
    font-weight: 600;
    border-right: 2px solid #adb5bd !important;
}
.stats-table thead .col-name {
    background: #343a40;
    color: #fff;
    z-index: 3;
}

/* Sticky summary columns */
.col-summary {
    position: sticky;
    right: 0;
    z-index: 2;
    background: #f8f9fa;
    border-left: 2px solid #adb5bd !important;
}
.stats-table thead .col-summary {
    background: #343a40;
    color: #fff;
    z-index: 3;
}

/* Date header — vertical text */
.stats-table thead th.col-date {
    background: #343a40;
    color: #fff;
    padding: 4px 2px;
    min-width: 28px;
    max-width: 28px;
    width: 28px;
}
.th-date-inner {
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    font-size: .7rem;
    line-height: 1;
    letter-spacing: .02em;
}

/* Calendar cells */
.cell-home    { background: #28a745; }
.cell-arb     { background: #dc3545; }
.cell-bar     { background: #007bff; }
.cell-home-arb { background: linear-gradient(135deg, #28a745 50%, #dc3545 50%); }
.cell-home-bar { background: linear-gradient(135deg, #28a745 50%, #007bff 50%); }
.cell-empty   { background: #fff; }

/* Member name status */
.name-deficit { background: #dc3545 !important; color: #fff !important; }
.name-ok      { background: #28a745 !important; color: #fff !important; }
.name-none    { background: #fff; color: #212529; }

/* Legend */
.legend-box {
    display: inline-block;
    width: 18px; height: 18px;
    border-radius: 3px;
    vertical-align: middle;
    margin-right: 4px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">
            <i class="fas fa-chart-bar mr-2"></i>
            Statistiques d'arbitrage — Saison <?= $seasonYear ?>/<?= $seasonYear + 1 ?>
        </h3>
        <form method="get" class="form-inline mb-0">
            <label class="mr-2 mb-0 font-weight-normal">Saison :</label>
            <select name="dummy" class="form-control form-control-sm mr-2" onchange="location.href=this.value">
                <?php foreach ($availableSeasons as $sy): ?>
                    <option value="<?= base_url("admin/arbitrage-stats/{$sy}") ?>"
                        <?= $sy === $seasonYear ? 'selected' : '' ?>>
                        <?= $sy ?>/<?= $sy + 1 ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="card-body p-2">

        <!-- Légende -->
        <div class="mb-3 d-flex flex-wrap gap-3" style="gap:.8rem; font-size:.82rem;">
            <span><span class="legend-box" style="background:#28a745"></span> Joue à domicile</span>
            <span><span class="legend-box" style="background:#dc3545"></span> Arbitre</span>
            <span><span class="legend-box" style="background:#007bff"></span> Bar</span>
            <span class="ml-3">
                <span class="legend-box" style="background:#dc3545; border:2px solid #dc3545"></span>
                <strong style="color:#dc3545">NOM</strong> = redevable
            </span>
            <span>
                <span class="legend-box" style="background:#28a745; border:2px solid #28a745"></span>
                <strong style="color:#28a745">NOM</strong> = en ordre
            </span>
        </div>

        <!-- Règle -->
        <p class="text-muted mb-3" style="font-size:.8rem;">
            <i class="fas fa-info-circle mr-1"></i>
            Règle : 2 jours de jeu à domicile = 3 services requis (arbitrage ou bar).
        </p>

        <?php if (empty($members)): ?>
            <p class="text-muted">Aucun joueur fédéré actif trouvé.</p>
        <?php elseif (empty($dates)): ?>
            <div class="alert alert-info">
                Aucune activité enregistrée pour la saison <?= $seasonYear ?>/<?= $seasonYear + 1 ?>.
            </div>
        <?php else: ?>

        <div class="stats-scroll-wrap">
        <table class="stats-table">
            <thead>
                <tr>
                    <th class="col-name">Joueur</th>
                    <?php foreach ($dates as $d): ?>
                        <th class="col-date">
                            <div class="th-date-inner"><?= date('d/m', strtotime($d)) ?></div>
                        </th>
                    <?php endforeach; ?>
                    <!-- Summary columns (sticky right) -->
                    <th class="col-summary" title="Jours joués à domicile">Dom.</th>
                    <th class="col-summary" title="Services requis (règle 2/3)">Req.</th>
                    <th class="col-summary" title="Arbitrages">Arb.</th>
                    <th class="col-summary" title="Services bar">Bar</th>
                    <th class="col-summary" title="Total services accomplis">Fait</th>
                    <th class="col-summary" title="Solde (négatif = redevable)">Solde</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $m):
                    $s        = $stats[$m->id];
                    $solde    = $s['done'] - $s['required'];
                    $nameClass = match($s['status']) {
                        'deficit' => 'name-deficit',
                        'ok'      => 'name-ok',
                        default   => 'name-none',
                    };
                ?>
                <tr>
                    <td class="col-name <?= $nameClass ?>">
                        <?= esc(mb_strtoupper($m->last_name)) ?> <?= member_initials($m->first_name) ?>
                    </td>
                    <?php foreach ($dates as $d):
                        $isHome = isset($s['home_dates'][$d]);
                        $hasArb = isset($s['arb_dates'][$d]);
                        $hasBar = isset($s['bar_dates'][$d]);

                        if ($isHome && $hasArb)      { $cellClass = 'cell-home-arb'; $title = 'Domicile + Arbitrage'; }
                        elseif ($isHome && $hasBar)  { $cellClass = 'cell-home-bar'; $title = 'Domicile + Bar'; }
                        elseif ($isHome)             { $cellClass = 'cell-home';     $title = 'Joue à domicile'; }
                        elseif ($hasArb)             { $cellClass = 'cell-arb';      $title = 'Arbitre'; }
                        elseif ($hasBar)             { $cellClass = 'cell-bar';      $title = 'Bar'; }
                        else                         { $cellClass = 'cell-empty';    $title = ''; }
                    ?>
                        <td class="<?= $cellClass ?>"
                            <?= $title ? "title=\"{$title}\"" : '' ?>>&nbsp;</td>
                    <?php endforeach; ?>
                    <!-- Summary -->
                    <td class="col-summary"><?= $s['home_count'] ?></td>
                    <td class="col-summary"><?= $s['required'] == floor($s['required'])
                        ? (int)$s['required']
                        : number_format($s['required'], 1, '.', '') ?></td>
                    <td class="col-summary"><?= $s['arb_count'] ?></td>
                    <td class="col-summary"><?= $s['bar_count'] ?></td>
                    <td class="col-summary"><?= $s['done'] ?></td>
                    <td class="col-summary <?= $solde < 0 ? 'text-danger font-weight-bold' : ($solde > 0 ? 'text-success' : '') ?>">
                        <?= $solde == 0 ? '0' : ($solde > 0 ? '+' : '') . (
                            $solde == floor($solde)
                                ? (int)$solde
                                : number_format($solde, 1, '.', '')
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div><!-- /stats-scroll-wrap -->

        <?php endif; ?>
    </div><!-- /card-body -->
</div><!-- /card -->
<?= $this->endSection() ?>
