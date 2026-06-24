<?= $this->extend('public/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
:root {
    --clr-home: #198754;
    --clr-away: #c6000d;
}

.week-title { font-size:1.1rem; font-weight:700; }
.week-nav-select { width:auto; display:inline-block; }
@media (max-width:575px) {
    .week-nav-select { width:100%; display:block; }
}

/* Day card */
.day-card {
    border:1px solid #dee2e6;
    border-top: 4px solid #84252B;
    border-radius:.5rem;
    margin-bottom:1.25rem;
    overflow:hidden;
}
.day-card-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:.6rem 1rem;
    background:#f8f9fa;
    border-bottom:1px solid #dee2e6;
    font-weight:700; font-size:.95rem;
}
.day-card-body { padding:0; }

/* Encounter row */
.enc-block {
    display:grid;
    grid-template-columns: 70px 130px 1fr 300px 0.55fr;
    align-items:start;
    padding:.65rem 1rem;
    border-bottom:1px solid #f0f0f0;
    gap:.5rem;
}
.enc-block:last-child { border-bottom:none; }
.enc-block.home { border-left:4px solid var(--clr-home); background: #e8f5e9; transition:background .15s; }
.enc-block.away { border-left:4px solid var(--clr-away); background: #DC656D24; transition:background .15s; }
.enc-block.home:hover { background: #d0f7d3; }
.enc-block.away:hover { background: #dc656d47; }

/* Time badge */
.time-pill {
    display:inline-block;
    background:#e9ecef; color:#343a40;
    border-radius:20px; padding:2px 10px;
    font-size:.82rem; font-weight:600; white-space:nowrap;
}

/* Location icon */
.loc-icon { font-size:1rem; display:flex; align-items:flex-start; gap:5px; }
.loc-icon i { margin-top:2px; flex-shrink:0; }
.loc-home { color:var(--clr-home); }
.loc-away { color:var(--clr-away); }
.loc-venue { font-size:.72rem; color:var(--clr-away); line-height:1.3; font-weight:700; }

/* Players */
.players-col { line-height:1.6; }
.match-line {
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    gap:4px;
    font-size:.88rem;
    margin-bottom:2px;
    color:#212529;
}
.player-home { text-align:right; }
.player-rbcd { font-weight:600; }
.player-away { text-align:left; }
.vs-sep { color:#666; font-size:.78rem; text-align:center; }
.match-line-solo { display:block; }

/* Compétition */
.comp-col { border-left:2px solid rgba(0,0,0,.08); border-right:2px solid rgba(0,0,0,.08); padding:0 .8rem; }
.comp-label { font-size:.8rem; color:#333; font-style:italic; line-height:1.3; }

/* Badge Finale */
.badge-finale { display:inline-block; background:#ffc107; color:#000; border-radius:10px;
                padding:1px 8px; font-size:.73rem; font-weight:600; margin-bottom:3px; }

/* Arbitrage col */
.arb-col   { display:flex; flex-direction:column; gap:4px; font-size:.88rem; }
.arb-row   { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.arb-label { font-size:.75rem; color:#555; font-weight:600; white-space:nowrap; }
.arb-name  { font-weight:600; }
.arb-rounds { color:#555; cursor:help; font-size:.85rem; }

.badge-confirmed { background:#198754; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; cursor:help; }
.badge-pending   { background:#ffc107; color:#000; border-radius:10px; padding:1px 7px; font-size:.73rem; cursor:help; }

/* Bar */
.bar-slots { display:flex; align-items:center; gap:.5rem; font-size:.83rem; }
.bar-slot-taken  { font-weight:600; color:#198754; }
.bar-slot-free   { color:#666; font-style:italic; }

/* Buttons */
.btn-signup { font-size:.78rem; padding:2px 8px; border-radius:10px; background:#e8f4fd; color:#0d6efd; border:1px solid #bee3fd; }
.btn-signup:hover { background:#0d6efd; color:#fff; }
.btn-confirm { font-size:.78rem; padding:2px 8px; border-radius:10px; background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.btn-confirm:hover { background:#ffc107; color:#000; }

/* Surlignage du membre connecté */
.me-highlight { background:#fff59d; border-radius:3px; padding:0 3px; font-weight:700; }

/* Fonce les text-muted dans le header jour (labels bar) */
.day-card-header .text-muted { color:#555 !important; }

/* Événements — même grille que enc-block */
.event-block { border-bottom: 1px solid rgba(0,0,0,.06); transition:filter .15s; }
.event-block:last-child { border-bottom: none; }
.event-block:hover { filter:brightness(.93); }
.event-block-title { font-weight: 700; font-size: .88rem; }
.event-block-desc  { font-size: .8rem; font-style: italic; }

/* Tooltips couleur RBCD — Bootstrap 5 */
.tooltip-rbcd .tooltip-inner {
    background-color: #84252B;
    color: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,.35);
}
.tooltip-rbcd[data-popper-placement^="top"]    .tooltip-arrow::before { border-top-color:    #84252B; }
.tooltip-rbcd[data-popper-placement^="bottom"] .tooltip-arrow::before { border-bottom-color: #84252B; }
.tooltip-rbcd[data-popper-placement^="left"]   .tooltip-arrow::before { border-left-color:   #84252B; }
.tooltip-rbcd[data-popper-placement^="right"]  .tooltip-arrow::before { border-right-color:  #84252B; }

@media (max-width:767px) {
    .enc-block { grid-template-columns: 1fr; }
    .week-nav .btn { min-width:auto; }
    .comp-col { border:none; padding:0; }
    .match-line { display:block; margin-bottom:1px; }
    .player-home { display:inline; text-align:left; font-weight:600; }
    .vs-sep      { display:inline; margin:0 3px; }
    .player-away { display:inline; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container pt-30 pb-40">

<?php
$frDays   = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$frMonths = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

function frDay(string $ymd, array $frDays, array $frMonths): string {
    $dt  = new \DateTime($ymd);
    $dow = (int)$dt->format('N') - 1;
    $d   = (int)$dt->format('j');
    $m   = (int)$dt->format('n') - 1;
    return $frDays[$dow] . ' ' . $d . ' ' . $frMonths[$m];
}

// Bitmask → "Tour 1 · Tour N" (bit i → Tour i+1, jusqu'à 8 tours)
function decodeTours(int $mask): string {
    $t = [];
    for ($i = 0; $i < 8; $i++) {
        if ($mask & (1 << $i)) $t[] = 'Tour ' . ($i + 1);
    }
    return $t ? implode(' · ', $t) : '';
}

$nowDt         = new \DateTime();
$nowWeek       = (int) $nowDt->format('W');
$nowYear       = (int) $nowDt->format('o');
$isCurrentWeek = ($week == $nowWeek && $year == $nowYear);

$monday = new \DateTime($weekDates[0]);
$sunday = new \DateTime($weekDates[6]);
$periodStr = $monday->format('j') . ' ' . $frMonths[(int)$monday->format('n')-1]
           . ' — ' . $sunday->format('j') . ' ' . $frMonths[(int)$sunday->format('n')-1]
           . ' ' . $sunday->format('Y');

// Saison août → juillet : générer toutes les semaines
$seasonStartYear = (int)$nowDt->format('n') >= 8 ? (int)$nowDt->format('Y') : (int)$nowDt->format('Y') - 1;
$aug1  = new \DateTime($seasonStartYear       . '-08-01');
$jul31 = new \DateTime(($seasonStartYear + 1) . '-07-31');
$seasonWeeks = [];
$swDt = (new \DateTime())->setISODate((int)$aug1->format('o'),  (int)$aug1->format('W'),  1);
$swEnd= (new \DateTime())->setISODate((int)$jul31->format('o'), (int)$jul31->format('W'), 1);
while ($swDt <= $swEnd) {
    $sw = (int)$swDt->format('W');
    $sy = (int)$swDt->format('o');
    $swSun = (clone $swDt)->modify('+6 days');
    $seasonWeeks[] = [
        'week'  => $sw,
        'year'  => $sy,
        'label' => "Semaine {$sw} — du " . $swDt->format('d-m-Y') . ' au ' . $swSun->format('d-m-Y'),
    ];
    $swDt->modify('+1 week');
}
?>

<!-- Navigation semaine -->
<div class="mb-4 week-nav">
    <div class="d-flex justify-content-center align-items-stretch gap-2 mb-2">
        <a href="<?= base_url("tableau/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-left d-block d-sm-inline me-sm-4"></i>Semaine précédente
        </a>
        <?php if (!$isCurrentWeek): ?>
            <a href="<?= base_url('tableau') ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
                <i class="fas fa-chevron-down d-block d-sm-inline me-sm-2"></i>Semaine en cours
            </a>
        <?php else: ?>
            <span class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;background:#6c757d;border-color:#6c757d;color:#fff;pointer-events:none;">
                <i class="fas fa-chevron-down d-block d-sm-inline me-sm-3"></i>Semaine en cours
            </span>
        <?php endif; ?>
        <a href="<?= base_url("tableau/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-right d-block d-sm-none"></i>Semaine suivante<i class="fas fa-chevron-right d-none d-sm-inline ms-4"></i>
        </a>
    </div>
    <div class="text-center">
        <div class="week-title">Semaine <?= $week ?></div>
        <div class="text-muted" style="font-size:.9rem"><?= esc($periodStr) ?></div>
        <div class="mt-2">
            <select class="form-control form-control-sm week-nav-select"
                    onchange="location.href=this.value">
                <?php foreach ($seasonWeeks as $sw): ?>
                <option value="<?= base_url("tableau/{$sw['week']}/{$sw['year']}") ?>"
                        <?= ($sw['week'] == $week && $sw['year'] == $year) ? 'selected' : '' ?>>
                    <?= esc($sw['label']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<?php if (!$isLogged): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <a href="<?= base_url('connexion') ?>">Connectez-vous</a> pour vous inscrire à l'arbitrage ou au service bar.
    </div>
<?php endif; ?>

<?php foreach ($weekDates as $date): ?>
<?php
$dayEncs      = $byDate[$date] ?? [];
$barAm        = $barByDate[$date]['am']   ?? null;
$barSoir      = $barByDate[$date]['soir'] ?? null;
$isActive     = !empty($activeDates[$date]);
$dayLabel     = frDay($date, $frDays, $frMonths);
$isSunday     = (int)(new \DateTime($date))->format('N') === 7;
$barAmLabel   = $isSunday ? 'Bar matin' : 'Bar après-midi';
?>

<div class="day-card">
    <div class="day-card-header">
        <span><?= esc($dayLabel) ?></span>
        <div class="bar-slots">
            <span class="text-muted"><?= $barAmLabel ?> :</span>
            <?php if ($barAm): ?>
                <?php $isMyBar = $isLogged && $barAm->member_id == $currentMemberId; ?>
                <span class="bar-slot-taken <?= $isMyBar ? 'me-highlight' : '' ?>"><?= esc($barAm->last_name) ?> <?= esc(member_initials($barAm->first_name)) ?>.</span>
            <?php elseif ($isLogged): ?>
                <button class="btn btn-info btn-sm btn-bar-signup" data-date="<?= $date ?>" data-period="am"
                        data-date-label="<?= esc($dayLabel) ?>"
                        data-period-label="<?= $isSunday ? 'le matin' : "l'après-midi" ?>"
                        data-bs-toggle="tooltip" data-bs-html="true"
                        title="S'inscrire au bar<br><?= $isSunday ? 'le matin' : "l'après-midi" ?>">
                    <i class="fas fa-user-plus"></i>
                </button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>
            <span class="text-muted ms-4">Bar soirée :</span>
            <?php if ($barSoir): ?>
                <?php $isMyBar = $isLogged && $barSoir->member_id == $currentMemberId; ?>
                <span class="bar-slot-taken <?= $isMyBar ? 'me-highlight' : '' ?>"><?= esc($barSoir->last_name) ?> <?= esc(member_initials($barSoir->first_name)) ?>.</span>
            <?php elseif ($isLogged): ?>
                <button class="btn btn-info btn-sm btn-bar-signup" data-date="<?= $date ?>" data-period="soir"
                        data-date-label="<?= esc($dayLabel) ?>"
                        data-period-label="en soirée"
                        data-bs-toggle="tooltip" data-bs-html="true"
                        title="S'inscrire au bar<br>en soirée">
                    <i class="fas fa-user-plus"></i>
                </button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($eventsByDate[$date])): ?>
    <div class="day-card-events">
        <?php foreach ($eventsByDate[$date] as $ev):
            $c = $eventColors[$ev->color] ?? $eventColors['blue'];
        ?>
        <div class="enc-block event-block" style="background:<?= $c['bg'] ?>;border-left:4px solid <?= $c['border'] ?>;color:<?= $c['text'] ?>;">
            <div>
                <?php if ($ev->start_time): ?>
                    <span class="time-pill"><?= substr($ev->start_time, 0, 5) ?></span>
                <?php endif; ?>
            </div>
            <div class="loc-icon">
                <i class="fas fa-calendar-day" style="color:<?= $c['border'] ?>;margin-top:2px;"></i>
            </div>
            <div class="event-block-title"><?= esc($ev->title) ?></div>
            <div class="comp-col">
                <?php if ($ev->description): ?>
                    <span class="event-block-desc" style="color:<?= $c['text'] ?>;"><?= esc($ev->description) ?></span>
                <?php endif; ?>
            </div>
            <div></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($dayEncs)): ?>
    <div class="day-card-body">
        <?php foreach ($dayEncs as $enc): ?>
        <?php
        $isFinale = $enc->encounter_type === 'finale';
        $myArb    = $enc->myArb;
        ?>
        <div class="enc-block <?= $enc->is_home ? 'home' : 'away' ?>" data-encounter="<?= $enc->id ?>">

            <div><span class="time-pill"><?= esc(substr($enc->match_time, 0, 5)) ?></span></div>

            <div class="loc-icon">
                <?php if ($enc->is_home): ?>
                    <i class="fas fa-home loc-home" title="À domicile"></i>
                <?php else: ?>
                    <i class="fas fa-car-side loc-away me-3" title="<?= esc($enc->venue ?? 'En déplacement') ?>"></i>
                    <?php if ($enc->venue): ?>
                        <span class="loc-venue"><?= esc($enc->venue) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="players-col">
                <?php foreach ($enc->players as $p): ?>
                <?php
                $isMyPlayer = $currentMemberId && $p->member_id == $currentMemberId;
                $pName = $p->member_id
                    ? esc($p->last_name . ' ' . member_initials($p->first_name))
                    : esc($p->player_home_name ?? '—');
                $pName = $isMyPlayer ? "<span class=\"me-highlight\">{$pName}</span>" : $pName;
                $oppName = esc($p->opponent_name);
                ?>
                    <?php if (empty($p->opponent_name)): ?>
                    <div class="match-line match-line-solo">
                        <span><?= $pName ?></span>
                    </div>
                    <?php else: ?>
                    <div class="match-line">
                        <span class="player-home <?= (!$isFinale && $enc->is_home)  ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $pName : $oppName ?></span>
                        <span class="vs-sep"><i class="fas fa-arrows-alt-h me-2 ms-2"></i></span>
                        <span class="player-away <?= (!$isFinale && !$enc->is_home) ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $oppName : $pName ?></span>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <div class="comp-col">
                <?php if ($enc->competition): ?>
                    <span class="comp-label"><?= esc($enc->competition) ?></span>
                <?php else: ?>
                    <span class="text-muted" style="font-size:.8rem">—</span>
                <?php endif; ?>
            </div>

            <!-- Arbitrage / Marqueurs -->
            <div class="arb-col" id="arb-<?= $enc->id ?>">
                <?php if ($enc->is_home): ?>

                    <?php if ($enc->requires_marquage ?? 0): ?>
                        <!-- Marquage requis -->
                        <div id="mrq-list-<?= $enc->id ?>">
                            <?php foreach ($enc->marqueurRows as $mrq): ?>
                            <?php
                            $isMe    = $isLogged && $mrq->member_id == $currentMemberId;
                            $mrqName = esc($mrq->last_name) . ' ' . esc(member_initials($mrq->first_name)) . '.';
                            ?>
                            <div class="arb-row" data-mrq-id="<?= $mrq->id ?>">
                                <span class="arb-label">Marqueur :</span>
                                <span class="arb-name <?= $isMe ? 'me-highlight' : '' ?>"><?= $mrqName ?></span>
                                <?php if ($mrq->round): ?>
                                    <i class="far fa-clock arb-rounds"
                                       data-bs-toggle="tooltip" data-bs-html="true"
                                       title="Disponible :<br><?= esc(decodeTours($mrq->round)) ?>"></i>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="arb-row">
                            <?php if ($isLogged && !$enc->myMarqueur): ?>
                                <button class="btn btn-warning btn-sm btn-mrq-signup"
                                        data-encounter="<?= $enc->id ?>"
                                        data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>"
                                        data-date-label="<?= esc(frDay($enc->match_date, $frDays, $frMonths)) ?>">
                                    <i class="fas fa-pen me-1"></i>Marquer
                                </button>
                            <?php elseif (empty($enc->marqueurRows) && !$isLogged): ?>
                                <span class="text-muted" style="font-size:.82rem">libre</span>
                            <?php endif; ?>
                        </div>

                    <?php elseif ($enc->requires_arbitrage ?? 1): ?>

                        <?php if ($isFinale): ?>
                            <!-- Finale avec arbitrage : liste libre d'arbitres -->
                            <div id="arb-list-<?= $enc->id ?>">
                                <?php foreach ($enc->arbitrageRows as $arb): ?>
                                <?php
                                $isMe   = $isLogged && $arb->member_id == $currentMemberId;
                                $isConv = $arb->assignment_type === 'designated';
                                $roundTip = $arb->round ? decodeTours($arb->round) : '';
                                $arbName  = esc($arb->last_name) . ' ' . esc(member_initials($arb->first_name)) . '.';
                                ?>
                                <div class="arb-row" data-arb-id="<?= $arb->id ?>">
                                    <span class="arb-name <?= $isMe ? 'me-highlight' : '' ?>"><?= $arbName ?></span>
                                    <?php if ($arb->round): ?>
                                        <i class="far fa-clock arb-rounds"
                                           data-bs-toggle="tooltip" data-bs-html="true"
                                           title="Disponible :<br><?= esc($roundTip) ?>"></i>
                                    <?php endif; ?>
                                    <?php if ($isMe && $isConv && !$arb->confirmed): ?>
                                        <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>">
                                            <i class="fas fa-check me-1"></i>Confirmer
                                        </button>
                                    <?php elseif ($arb->confirmed): ?>
                                        <span class="badge-confirmed" data-bs-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                                    <?php elseif (!$arb->confirmed && $isConv): ?>
                                        <span class="badge-pending" data-bs-toggle="tooltip" data-bs-html="true" title="En attente<br>de confirmation"><i class="fas fa-hourglass-start"></i></span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="arb-row">
                                <span class="arb-label">Arbitrage :</span>
                                <?php if ($isLogged): ?>
                                <button class="btn btn-info btn-sm btn-arb-signup <?= $myArb ? 'd-none' : '' ?>"
                                        data-encounter="<?= $enc->id ?>"
                                        data-type="finale"
                                        data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>">
                                    <i class="fas fa-hand-paper me-1"></i>Arbitrer
                                </button>
                                <?php elseif (empty($enc->arbitrageRows)): ?>
                                    <span class="text-muted" style="font-size:.82rem">libre</span>
                                <?php endif; ?>
                            </div>

                        <?php else: ?>
                            <!-- Match normal : arbitres multiples -->
                            <div id="arb-normal-<?= $enc->id ?>">
                                <div id="arb-list-normal-<?= $enc->id ?>">
                                    <?php foreach ($enc->arbitrageRows as $arb): ?>
                                    <?php
                                    $isMe   = $isLogged && $arb->member_id == $currentMemberId;
                                    $isConv = $arb->assignment_type === 'designated';
                                    $arbName = esc($arb->last_name) . ' ' . esc(member_initials($arb->first_name)) . '.';
                                    ?>
                                    <div class="arb-row" data-arb-id="<?= $arb->id ?>">
                                        <span class="arb-label">Arbitrage :</span>
                                        <span class="arb-name <?= $isMe ? 'me-highlight' : '' ?>"><?= $arbName ?></span>
                                        <?php if ($isMe && $isConv && !$arb->confirmed): ?>
                                            <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>">
                                                <i class="fas fa-check me-1"></i>Confirmer
                                            </button>
                                        <?php elseif ($arb->confirmed): ?>
                                            <span class="badge-confirmed" data-bs-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge-pending" data-bs-toggle="tooltip" data-bs-html="true" title="En attente<br>de confirmation"><i class="fas fa-hourglass-start"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="arb-row">
                                    <?php if ($isLogged && !$myArb): ?>
                                        <button class="btn btn-info btn-sm btn-arb-signup"
                                                data-encounter="<?= $enc->id ?>"
                                                data-type="normal"
                                                data-date-label="<?= esc(frDay($enc->match_date, $frDays, $frMonths)) ?>">
                                            <i class="fas fa-hand-paper me-1"></i>Arbitrer
                                        </button>
                                    <?php elseif (empty($enc->arbitrageRows) && !$isLogged): ?>
                                        <span class="text-muted" style="font-size:.82rem">libre</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endif; ?>

                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-3 text-muted" style="font-size:.85rem">Pas de rencontre ce jour.</div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<!-- Navigation semaine (bas de page) -->
<div class="mt-4 week-nav">
    <div class="d-flex justify-content-center align-items-stretch gap-2 mb-2">
        <a href="<?= base_url("tableau/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-left d-block d-sm-inline me-sm-4"></i>Semaine précédente
        </a>
        <?php if (!$isCurrentWeek): ?>
            <a href="<?= base_url('tableau') ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
                <i class="fas fa-chevron-up d-block d-sm-inline me-sm-2"></i>Semaine en cours
            </a>
        <?php else: ?>
            <span class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;background:#6c757d;border-color:#6c757d;color:#fff;pointer-events:none;">
                <i class="fas fa-chevron-up d-block d-sm-inline me-sm-3"></i>Semaine en cours
            </span>
        <?php endif; ?>
        <a href="<?= base_url("tableau/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-right d-block d-sm-none"></i>Semaine suivante<i class="fas fa-chevron-right d-none d-sm-inline ms-4"></i>
        </a>
    </div>
</div>

    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>



</div><!-- /container -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName  = '<?= csrf_token() ?>';
let   csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Template tooltip Bootstrap 5 (tooltip-arrow au lieu de arrow)
const rbcdTooltipTemplate = '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>';

// Initialise les tooltips Bootstrap 5 dans un conteneur parent (ou sur le document entier)
function initTooltips(parent) {
    (parent || document).querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        // Détruire toute instance BS5
        bootstrap.Tooltip.getInstance(el)?.dispose();
        // Détruire toute instance jQuery/BS4 (custom.js du thème)
        if (typeof $ !== 'undefined') {
            try { $(el).tooltip('dispose'); } catch(e) {}
            try { $(el).tooltip('destroy'); } catch(e) {}
        }
        new bootstrap.Tooltip(el, {
            template : rbcdTooltipTemplate,
            html     : true,
            sanitize : false,
            delay    : { show: 80, hide: 80 },
        });
        // Empêche custom.js de réinitialiser après nous
        el.dataset.bsToggle = 'tooltip-rbcd';
    });
}

// setTimeout : garantit qu'on tourne APRÈS tous les DOMContentLoaded de custom.js
document.addEventListener('DOMContentLoaded', () => setTimeout(() => initTooltips(), 100));

// ── Navigation semaine : bloquer les clics multiples ──
(function () {
    const navLinks = document.querySelectorAll('.week-nav a.btn');
    const navSel   = document.querySelector('.week-nav-select');

    function lockNav() {
        navLinks.forEach(b => {
            b.classList.add('disabled');
            b.style.pointerEvents = 'none';
            b.style.opacity = '.5';
        });
        if (navSel) navSel.disabled = true;
    }

    navLinks.forEach(a => a.addEventListener('click', lockNav));

    // Remplace l'onchange inline pour protéger le select aussi
    if (navSel) {
        navSel.onchange = function () {
            const url = this.value;
            lockNav();
            location.href = url;
        };
    }
})();

function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: Object.entries({...body, [csrfName]: csrfToken})
                    .map(([k,v]) => `${k}=${encodeURIComponent(v)}`).join('&'),
    }).then(r => r.json());
}

// ── Arbitrage : s'inscrire ──
document.querySelectorAll('.btn-arb-signup').forEach(bindArbSignup);

function bindArbSignup(btn) {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const type  = this.dataset.type || 'normal';
        const self  = this;

        if (type === 'finale') {
            const rounds = parseInt(this.dataset.rounds || 3);
            let chkHtml = '';
            for (let i = 0; i < rounds; i++) {
                const val = 1 << i;
                chkHtml += `<div class="${i < rounds - 1 ? 'mb-1' : ''}"><label style="cursor:pointer">` +
                    `<input type="checkbox" class="swal-round-chk" value="${val}" checked style="margin-right:6px">Tour ${i + 1}` +
                    `</label></div>`;
            }
            Swal.fire({
                title: '<i class="fas fa-trophy me-2" style="color:#ffc107"></i>Inscription — Finale',
                html: `<p class="mb-3">Quels tours souhaitez-vous arbitrer ?</p>
                    <div class="text-start ps-4">${chkHtml}</div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-hand-paper me-1"></i>S\'inscrire',
                cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-secondary ms-2' },
                buttonsStyling: false,
                preConfirm: () => {
                    let mask = 0;
                    document.querySelectorAll('.swal-round-chk').forEach(el => {
                        if (el.checked) mask |= parseInt(el.value);
                    });
                    if (!mask) {
                        Swal.showValidationMessage('Veuillez sélectionner au moins un tour');
                        return false;
                    }
                    return mask;
                }
            }).then(result => {
                if (!result.isConfirmed) return;
                doSignup(encId, result.value, self);
            });
        } else {
            const dateLabel = self.dataset.dateLabel || '';
            Swal.fire({
                title: '<i class="fas fa-hand-paper me-2" style="color:#0dcaf0"></i>Arbitrage',
                html: `Je me mets à l'arbitrage pour ce<br><strong>${dateLabel}</strong>`,
                showCancelButton: true,
                confirmButtonText: 'Je confirme',
                cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-info', cancelButton: 'btn btn-secondary ms-2' },
                buttonsStyling: false,
            }).then(result => {
                if (!result.isConfirmed) return;
                doSignup(encId, 0, self);
            });
        }
    });
}

// Bitmask → "Tour 1 · Tour N"
function decodeTours(mask) {
    const t = [];
    for (let i = 0; i < 8; i++) {
        if (mask & (1 << i)) t.push('Tour ' + (i + 1));
    }
    return t.join(' · ');
}

function formatDateFr(dateStr, timeStr) {
    const jours  = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
    const mois   = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    const [y, m, d] = dateStr.split('-').map(Number);
    const dow = new Date(y, m - 1, d).getDay();
    const hm  = timeStr ? timeStr.substring(0, 5).replace(':', 'h') : '';
    return jours[dow] + ' ' + d + ' ' + mois[m - 1] + ' ' + y + (hm ? ' à ' + hm : '');
}

function doSignup(encId, round, btn) {
    postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/signup`, {round})
    .then(data => {
        if (!data.success) return Swal.fire('Erreur', data.message, 'error');

        if (btn.dataset.type === 'finale') {
            const roundTip = round > 0 ? `<i class="far fa-clock arb-rounds" data-bs-toggle="tooltip" title="${decodeTours(round)}"></i>` : '';
            const newRow = `
                <div class="arb-row" data-arb-id="${data.arb_id}">
                    <span class="arb-name me-highlight">${data.name}</span>
                    ${roundTip}
                    <span class="badge-confirmed" data-bs-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                </div>`;
            const list = document.getElementById(`arb-list-${encId}`);
            list.insertAdjacentHTML('beforeend', newRow);
            initTooltips(list.lastElementChild);
            btn.classList.add('d-none');
        } else {
            const list = document.getElementById(`arb-list-normal-${encId}`);
            const newRow = `
                <div class="arb-row" data-arb-id="${data.arb_id}">
                    <span class="arb-label">Arbitrage :</span>
                    <span class="arb-name me-highlight">${data.name}</span>
                    <span class="badge-confirmed" data-bs-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                </div>`;
            list.insertAdjacentHTML('beforeend', newRow);
            initTooltips(list.lastElementChild);
            btn.classList.add('d-none');
        }

        const dateLabel = formatDateFr(data.match_date, data.match_time);
        const compLine  = data.competition ? `<br><small>${data.competition}</small>` : '';
        Swal.fire({
            icon: 'success',
            title: 'Inscription enregistrée !',
            html: `Tu viens bien de t'inscrire à l'<strong>arbitrage</strong> le <strong>${dateLabel}</strong>${compLine}.<br><br>
                   📧 Tu as reçu un email de confirmation.<br>
                   📅 N'oublie pas de noter cette date dans ton agenda !<br><br>
                   <em>On compte désormais sur toi !</em>`,
            confirmButtonText: 'OK',
            customClass: { confirmButton: 'btn btn-success' },
            buttonsStyling: false,
        });
    });
}

// ── Arbitrage : confirmer convocation ──
document.querySelectorAll('.btn-arb-confirm').forEach(btn => {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/confirm`, {})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            const row = this.closest('.arb-row') || document.getElementById(`arb-normal-${encId}`);
            const convBadge = row.querySelector('.badge-conv');
            const confirmBtn = row.querySelector('.btn-arb-confirm');
            if (convBadge) {
                convBadge.outerHTML = '<span class="badge-confirmed" data-bs-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>';
                initTooltips(row);
            }
            if (confirmBtn) confirmBtn.remove();
        });
    });
});

// ── Marqueur : s'inscrire ──
document.querySelectorAll('.btn-mrq-signup').forEach(bindMrqSignup);

function bindMrqSignup(btn) {
    btn.addEventListener('click', function() {
        const encId     = this.dataset.encounter;
        const dateLabel = this.dataset.dateLabel || '';
        const rounds    = parseInt(this.dataset.rounds || 3);
        const self      = this;

        let chkHtml = '';
        for (let i = 0; i < rounds; i++) {
            const val = 1 << i;
            chkHtml += `<div class="${i < rounds - 1 ? 'mb-1' : ''}"><label style="cursor:pointer">` +
                `<input type="checkbox" class="swal-mrq-chk" value="${val}" checked style="margin-right:6px">Tour ${i + 1}` +
                `</label></div>`;
        }

        Swal.fire({
            title: '<i class="fas fa-pen me-2" style="color:#ffc107"></i>Marqueur',
            html: `<p class="mb-3">Je me mets comme <strong>marqueur</strong> le <strong>${dateLabel}</strong>.<br>Quels tours ?</p>
                   <div class="text-start ps-4">${chkHtml}</div>`,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-pen me-1"></i>S\'inscrire',
            cancelButtonText: 'Annuler',
            customClass: { confirmButton: 'btn btn-warning', cancelButton: 'btn btn-secondary ms-2' },
            buttonsStyling: false,
            preConfirm: () => {
                let mask = 0;
                document.querySelectorAll('.swal-mrq-chk').forEach(el => {
                    if (el.checked) mask |= parseInt(el.value);
                });
                if (!mask) {
                    Swal.showValidationMessage('Veuillez sélectionner au moins un tour');
                    return false;
                }
                return mask;
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            postJson(`<?= base_url('tableau/marqueur/') ?>${encId}/signup`, {round: result.value})
            .then(data => {
                if (!data.success) return Swal.fire('Erreur', data.message, 'error');

                const roundTip = data.round > 0
                    ? `<i class="far fa-clock arb-rounds" data-bs-toggle="tooltip" title="${decodeTours(data.round)}"></i>`
                    : '';
                const list = document.getElementById(`mrq-list-${encId}`);
                list.insertAdjacentHTML('beforeend', `
                    <div class="arb-row" data-mrq-id="${data.mrq_id}">
                        <span class="arb-label">Marqueur :</span>
                        <span class="arb-name me-highlight">${data.name}</span>
                        ${roundTip}
                    </div>`);
                initTooltips(list.lastElementChild);
                self.classList.add('d-none');

                const dl = formatDateFr(data.match_date, data.match_time);
                const cp = data.competition ? `<br><small>${data.competition}</small>` : '';
                Swal.fire({
                    icon: 'success',
                    title: 'Inscription enregistrée !',
                    html: `Tu viens bien de t'inscrire comme <strong>marqueur</strong> le <strong>${dl}</strong>${cp}.<br><br>
                           📧 Tu as reçu un email de confirmation.<br>
                           📅 N'oublie pas de noter cette date dans ton agenda !<br><br>
                           <em>On compte désormais sur toi !</em>`,
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-warning' },
                    buttonsStyling: false,
                });
            });
        });
    });
}

// ── Bar : s'inscrire ──
document.querySelectorAll('.btn-bar-signup').forEach(bindBarSignup);

function bindBarSignup(btn) {
    btn.addEventListener('click', function() {
        const date        = this.dataset.date;
        const period      = this.dataset.period;
        const dateLabel   = this.dataset.dateLabel   || '';
        const periodLabel = this.dataset.periodLabel || '';
        const self        = this;

        Swal.fire({
            title: '<i class="fas fa-glass-cheers me-2" style="color:#0dcaf0"></i>Bar',
            html: `Je me mets au bar pour ce<br><strong>${dateLabel}</strong><br>${periodLabel}`,
            showCancelButton: true,
            confirmButtonText: 'Je confirme',
            cancelButtonText: 'Annuler',
            customClass: { confirmButton: 'btn btn-info', cancelButton: 'btn btn-secondary ms-2' },
            buttonsStyling: false,
        }).then(result => {
            if (!result.isConfirmed) return;
            postJson('<?= base_url('tableau/bar/signup') ?>', {date, period})
            .then(data => {
                if (!data.success) return Swal.fire('Erreur', data.message, 'error');
                self.outerHTML = `<span class="bar-slot-taken">${data.name}</span>`;

                const periodLabel2 = period === 'am' ? 'Après-midi' : 'Soirée';
                Swal.fire({
                    icon: 'success',
                    title: 'Inscription enregistrée !',
                    html: `Tu viens bien de t'inscrire au <strong>bar</strong> le <strong>${dateLabel}</strong> — ${periodLabel2}.<br><br>
                           📧 Tu as reçu un email de confirmation.<br>
                           📅 N'oublie pas de noter cette date dans ton agenda !<br><br>
                           <em>On compte désormais sur toi !</em>`,
                    confirmButtonText: 'OK',
                    customClass: { confirmButton: 'btn btn-info' },
                    buttonsStyling: false,
                });
            });
        });
    });
}
</script>
<?= $this->endSection() ?>
