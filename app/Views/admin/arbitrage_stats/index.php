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
    background: #721C24;
    color: #fff;
    z-index: 3;

    position: sticky;
    
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
    background: #721C24;
    color: #fff;
    z-index: 3;
    width: 50px;
    min-width: 50px;
    max-width: 50px;
}

/* Date header — vertical text */
.stats-table thead th.col-date {
    background: #721C24;
    color: #fff;
    padding: 4px 2px;
    min-width: 28px;
    max-width: 28px;
    width: 28px;
}
.th-date-inner {
    font-size: .8rem;
    line-height: 1.3;
}

/* Calendar cells */
.cell-home    { background: #93C37D; }
.cell-arb     { background: #D9534F; }
.cell-bar     { background: #117DC4; }
.cell-mrq     { background: #FFC109; }
.cell-home-arb { background: linear-gradient(135deg, #93C37D 50%, #D9534F 50%); }
.cell-home-bar { background: linear-gradient(135deg, #93C37D 50%, #117DC4 50%); }
.cell-home-mrq { background: linear-gradient(135deg, #93C37D 50%, #FFC109 50%); }
.cell-empty   { background: #fff; }

/* Member name status */
.name-deficit { background: #f0b0b7 !important; color: #212529 !important; }
.name-ok      { background: #bbecb1 !important; color: #212529 !important; }
.name-none    { background: #fff; color: #212529; }

/* Rollover lignes */
.stats-table tbody tr { transition: filter .12s; }
.stats-table tbody tr:hover td               { filter: brightness(.88); }
.stats-table tbody tr:hover td.col-name,
.stats-table tbody tr:hover td.col-summary   { filter: brightness(.88); }

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
        <div class="mb-2 d-flex flex-wrap" style="gap:.8rem; font-size:.82rem;">
            <span><span class="legend-box" style="background: #93C37D"></span> Joue à domicile (individuel)</span>
            <span><span class="legend-box" style="background: #D9534F"></span> Arbitre</span>
            <span><span class="legend-box" style="background: #117DC4"></span> Bar</span>
            <span><span class="legend-box" style="background: #FFC109"></span> Marqueur</span>
        </div>
        <div class="mb-3 d-flex flex-wrap" style="gap:.8rem; font-size:.82rem;">
            <span>
                <span class="legend-box" style="background: #f0b0b7"></span>
                <strong>NOM</strong> = redevable
            </span>
            <span>
                <span class="legend-box" style="background: #bbecb1"></span>
                <strong>NOM</strong> = en ordre
            </span>
        </div>

        <!-- Règle -->
        <p class="text-muted mb-3" style="font-size:1rem;">
            <i class="fas fa-info-circle fa-lg mr-2"></i>
            Règle de 2 pour 3 <i class="fas fa-arrow-right"></i> 2 jours de jeu à domicile = 3 services requis (arbitrage, bar ou marquage).
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
                            <div class="th-date-inner">
                                <?= date('d', strtotime($d)) ?><br>
                                <?= date('m', strtotime($d)) ?><br>
                                <?= date('y', strtotime($d)) ?>
                            </div>
                        </th>
                    <?php endforeach; ?>
                    <!-- Summary columns (sticky right) -->
                    <th class="col-summary" title="Jours joués à domicile">Joué</th>
                    <th class="col-summary" title="Services requis (règle 2/3)">Requis</th>
                    <th class="col-summary" title="Arbitrages">Arb.</th>
                    <th class="col-summary" title="Services bar">Bar</th>
                    <th class="col-summary" title="Marquages" style="background:#FFC109;color:#000">Marq.</th>
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
                        $hasMrq = isset($s['mrq_dates'][$d]);

                        if ($isHome && $hasArb)      { $cellClass = 'cell-home-arb'; $title = 'Domicile + Arbitrage'; }
                        elseif ($isHome && $hasBar)  { $cellClass = 'cell-home-bar'; $title = 'Domicile + Bar'; }
                        elseif ($isHome && $hasMrq)  { $cellClass = 'cell-home-mrq'; $title = 'Domicile + Marqueur'; }
                        elseif ($isHome)             { $cellClass = 'cell-home';     $title = 'Joue à domicile'; }
                        elseif ($hasArb)             { $cellClass = 'cell-arb';      $title = 'Arbitre'; }
                        elseif ($hasBar)             { $cellClass = 'cell-bar';      $title = 'Bar'; }
                        elseif ($hasMrq)             { $cellClass = 'cell-mrq';      $title = 'Marqueur'; }
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
                    <td class="col-summary" style="background:#FFC10922"><?= $s['mrq_count'] ?: '—' ?></td>
                    <td class="col-summary"><?= $s['done'] ?></td>
                    <td class="col-summary <?= $solde < 0 ? 'text-danger font-weight-bold' : ($solde > 0 ? 'text-success font-weight-bold' : '') ?>">
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
