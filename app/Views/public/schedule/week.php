<?= $this->extend('public/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
:root {
    --clr-home: #198754;
    --clr-away: #c6000d;
}

.week-title { font-size:1.1rem; font-weight:700; }
.week-nav-next   { order:2; }
.week-nav-center { order:3; flex-basis:100%; margin-top:.5rem; }
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
    grid-template-columns: 70px 140px 1fr 240px 1fr;
    align-items:start;
    padding:.65rem 1rem;
    border-bottom:1px solid #f0f0f0;
    gap:.5rem;
}
.enc-block:last-child { border-bottom:none; }
.enc-block.home { border-left:4px solid var(--clr-home); background: #e8f5e9; }
.enc-block.away { border-left:4px solid var(--clr-away); background: #DC656D24; }

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

/* Players */
.players-col { line-height:1.6; }
.match-line {
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    gap:4px;
    font-size:.88rem;
    margin-bottom:2px;
}
.player-home { text-align:right; }
.player-rbcd { font-weight:600; }
.player-away { text-align:left; }
.vs-sep { color:#aaa; font-size:.78rem; text-align:center; }

/* Compétition */
.comp-col { border-left:2px solid rgba(0,0,0,.08); border-right:2px solid rgba(0,0,0,.08); padding:0 .8rem; }
.comp-label { font-size:.8rem; color:#555; font-style:italic; line-height:1.3; }

/* Badge Finale */
.badge-finale { display:inline-block; background:#ffc107; color:#000; border-radius:10px;
                padding:1px 8px; font-size:.73rem; font-weight:600; margin-bottom:3px; }

/* Arbitrage col */
.arb-col   { display:flex; flex-direction:column; gap:4px; font-size:.88rem; }
.arb-row   { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.arb-label { font-size:.75rem; color:#888; font-weight:600; white-space:nowrap; }
.arb-name  { font-weight:600; }
.arb-rounds { color:#888; cursor:help; font-size:.85rem; }

.badge-confirmed { background:#198754; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; cursor:help; }
.badge-pending   { background:#ffc107; color:#000; border-radius:10px; padding:1px 7px; font-size:.73rem; cursor:help; }
.badge-conv      { background:#fd7e14; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; cursor:help; }

/* Bar */
.bar-slots { display:flex; align-items:center; gap:.5rem; font-size:.83rem; }
.bar-slot-taken  { font-weight:600; color:#198754; }
.bar-slot-free   { color:#aaa; font-style:italic; }

/* Buttons */
.btn-signup, .btn-cancel { font-size:.78rem; padding:2px 8px; border-radius:10px; }
.btn-signup { background:#e8f4fd; color:#0d6efd; border:1px solid #bee3fd; }
.btn-signup:hover { background:#0d6efd; color:#fff; }
.btn-cancel { background:#fdecea; color:#dc3545; border:1px solid #f5c6cb; }
.btn-cancel:hover { background:#dc3545; color:#fff; }
.btn-confirm { font-size:.78rem; padding:2px 8px; border-radius:10px; background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.btn-confirm:hover { background:#ffc107; color:#000; }

/* Surlignage du membre connecté */
.me-highlight { background:#fff59d; border-radius:3px; padding:0 3px; font-weight:700; }

/* Tooltips couleur RBCD */
.tooltip-rbcd .tooltip-inner {
    background-color: #84252B;
    color: #fff;
    box-shadow: 0 3px 8px rgba(0,0,0,.35);
}
.tooltip-rbcd.bs-tooltip-top    .arrow::before { border-top-color:    #84252B; }
.tooltip-rbcd.bs-tooltip-bottom .arrow::before { border-bottom-color: #84252B; }
.tooltip-rbcd.bs-tooltip-left   .arrow::before { border-left-color:   #84252B; }
.tooltip-rbcd.bs-tooltip-right  .arrow::before { border-right-color:  #84252B; }

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
<div class="d-flex align-items-center justify-content-between mb-4 week-nav flex-wrap" style="gap:.5rem">
    <a href="<?= base_url("tableau/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary">
        <i class="fas fa-chevron-left mr-1"></i>Semaine précédente
    </a>
    <a href="<?= base_url("tableau/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary week-nav-next">
        Semaine suivante <i class="fas fa-chevron-right ml-1"></i>
    </a>
    <div class="text-center week-nav-center">
        <?php if (!$isCurrentWeek): ?>
            <div class="mb-1">
                <a href="<?= base_url('tableau') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-chevron-down mr-1"></i>Semaine en cours
                </a>
            </div>
        <?php endif; ?>
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
        <i class="fas fa-info-circle mr-2"></i>
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
                <?php $isMyBar = $isLogged && $barAm->admin_user_id == $currentUser; ?>
                <span class="bar-slot-taken <?= $isMyBar ? 'me-highlight' : '' ?>"><?= esc($barAm->last_name) ?> <?= esc(mb_substr($barAm->first_name,0,1)) ?>.</span>
            <?php elseif ($isLogged): ?>
                <button class="btn btn-info btn-sm btn-bar-signup" data-date="<?= $date ?>" data-period="am" data-toggle="tooltip" data-html="true" title="S'inscrire au bar<br><?= $isSunday ? 'le matin' : "l'après-midi" ?>"><i class="fas fa-user-plus"></i></button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>
            <span class="text-muted ml-2">Bar soirée :</span>
            <?php if ($barSoir): ?>
                <?php $isMyBar = $isLogged && $barSoir->admin_user_id == $currentUser; ?>
                <span class="bar-slot-taken <?= $isMyBar ? 'me-highlight' : '' ?>"><?= esc($barSoir->last_name) ?> <?= esc(mb_substr($barSoir->first_name,0,1)) ?>.</span>
            <?php elseif ($isLogged): ?>
                <button class="btn btn-info btn-sm btn-bar-signup" data-date="<?= $date ?>" data-period="soir" data-toggle="tooltip" data-html="true" title="S'inscrire au bar<br>en soirée"><i class="fas fa-user-plus"></i></button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>
        </div>
    </div>

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
                    <i class="fas fa-car-side loc-away mr-3" title="<?= esc($enc->venue ?? 'En déplacement') ?>"></i>
                    <?php if ($enc->venue): ?>
                        <span style="font-size:.72rem;color:var(--clr-away);line-height:1.3"><?= esc($enc->venue) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="players-col">
                <?php foreach ($enc->players as $p): ?>
                <?php
                $isMyPlayer = $currentMemberId && $p->member_id == $currentMemberId;
                $pName = $p->member_id
                    ? esc($p->last_name . ' ' . mb_substr($p->first_name, 0, 1) . '.')
                    : esc($p->player_home_name ?? '—');
                $pName = $isMyPlayer ? "<span class=\"me-highlight\">{$pName}</span>" : $pName;
                $oppName = esc($p->opponent_name);
                ?>
                    <div class="match-line">
                        <span class="player-home <?= (!$isFinale && $enc->is_home)  ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $pName : $oppName ?></span>
                        <span class="vs-sep"><i class="fas fa-arrows-alt-h mr-2 ml-2"></i></span>
                        <span class="player-away <?= (!$isFinale && !$enc->is_home) ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $oppName : $pName ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="comp-col">
                <?php if ($enc->competition): ?>
                    <span class="comp-label"><?= esc($enc->competition) ?></span>
                <?php else: ?>
                    <span class="text-muted" style="font-size:.8rem">—</span>
                <?php endif; ?>
            </div>

            <!-- Arbitrage -->
            <div class="arb-col" id="arb-<?= $enc->id ?>">
                <?php if ($enc->is_home): ?>

                    <?php if ($isFinale): ?>
                        <!-- Finale : liste libre d'arbitres inscrits -->
                        <div id="arb-list-<?= $enc->id ?>">
                            <?php foreach ($enc->arbitrageRows as $arb): ?>
                            <?php
                            $isMe   = $isLogged && (
                                ($arb->admin_user_id && $arb->admin_user_id == $currentUser) ||
                                ($arb->member_id     && $arb->member_id     == $currentMemberId)
                            );
                            $isConv = $arb->assignment_type === 'designated';
                            $roundTip = $arb->round ? decodeTours($arb->round) : '';
                            $arbName  = esc($arb->last_name) . ' ' . esc(mb_substr($arb->first_name,0,1)) . '.';
                            ?>
                            <div class="arb-row" data-arb-id="<?= $arb->id ?>">
                                <span class="arb-name <?= $isMe ? 'me-highlight' : '' ?>"><?= $arbName ?></span>
                                <?php if ($arb->round): ?>
                                    <i class="far fa-clock arb-rounds" data-toggle="tooltip" data-html="true" title="Disponible :<br><?= esc($roundTip) ?>"></i>
                                <?php endif; ?>
                                <?php if ($isMe && $isConv && !$arb->confirmed): ?>
                                    <span class="badge-conv">Convoqué</span>
                                    <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>">
                                        <i class="fas fa-check mr-1"></i>Confirmer
                                    </button>
                                <?php elseif ($arb->confirmed): ?>
                                    <span class="badge-confirmed" data-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                                <?php elseif (!$arb->confirmed && $isConv): ?>
                                    <span class="badge-pending" data-toggle="tooltip" data-html="true" title="En attente<br>de confirmation"><i class="fas fa-hourglass-start"></i></span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Bouton toujours visible si pas encore inscrit -->
                        <div class="arb-row">
                            <span class="arb-label">Arbitrage :</span>
                            <?php if ($isLogged): ?>
                            <button class="btn btn-info btn-sm btn-arb-signup <?= $myArb ? 'd-none' : '' ?>"
                                    data-encounter="<?= $enc->id ?>"
                                    data-type="finale"
                                    data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>">
                                <i class="fas fa-hand-paper mr-1"></i>Arbitrer
                            </button>
                            <?php elseif (empty($enc->arbitrageRows)): ?>
                                <span class="text-muted" style="font-size:.82rem">libre</span>
                            <?php endif; ?>
                        </div>

                    <?php else: ?>
                        <!-- Match normal : 1 seul arbitre -->
                        <div class="arb-row" id="arb-normal-<?= $enc->id ?>">
                            <span class="arb-label">Arbitrage :</span>
                            <?php if (!empty($enc->arbitrageRows)): ?>
                                <?php
                                $arb    = $enc->arbitrageRows[0];
                                $isMe   = $isLogged && (
                                    ($arb->admin_user_id && $arb->admin_user_id == $currentUser) ||
                                    ($arb->member_id     && $arb->member_id     == $currentMemberId)
                                );
                                $isConv  = $arb->assignment_type === 'designated';
                                $arbName = esc($arb->last_name) . ' ' . esc(mb_substr($arb->first_name,0,1)) . '.';
                                ?>
                                <span class="arb-name <?= $isMe ? 'me-highlight' : '' ?>"><?= $arbName ?></span>
                                <?php if ($isMe && $isConv && !$arb->confirmed): ?>
                                    <span class="badge-conv">Convoqué</span>
                                    <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>">
                                        <i class="fas fa-check mr-1"></i>Confirmer
                                    </button>
                                <?php elseif ($arb->confirmed): ?>
                                    <span class="badge-confirmed" data-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                                <?php else: ?>
                                    <span class="badge-pending" data-toggle="tooltip" data-html="true" title="En attente<br>de confirmation"><i class="fas fa-hourglass-start"></i></span>
                                <?php endif; ?>
                            <?php elseif ($isLogged): ?>
                                <button class="btn btn-info btn-sm btn-arb-signup"
                                        data-encounter="<?= $enc->id ?>"
                                        data-type="normal"
                                        data-date-label="<?= esc(frDay($enc->match_date, $frDays, $frMonths)) ?>">
                                    <i class="fas fa-hand-paper mr-1"></i>Arbitrer
                                </button>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:.82rem">libre</span>
                            <?php endif; ?>
                        </div>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName  = '<?= csrf_token() ?>';
let   csrfToken = document.querySelector('meta[name="csrf-token"]').content;

const rbcdTooltipTemplate = '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>';

// Initialiser les tooltips Bootstrap
$(function() { $('[data-toggle="tooltip"]').tooltip({ template: rbcdTooltipTemplate }); });

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
            // Générer les cases à cocher selon le nombre de tours de la compétition
            let chkHtml = '';
            for (let i = 0; i < rounds; i++) {
                const val = 1 << i;
                chkHtml += `<div class="${i < rounds - 1 ? 'mb-1' : ''}"><label style="cursor:pointer">` +
                    `<input type="checkbox" class="swal-round-chk" value="${val}" checked style="margin-right:6px">Tour ${i + 1}` +
                    `</label></div>`;
            }
            Swal.fire({
                title: '<i class="fas fa-trophy mr-2" style="color:#ffc107"></i>Inscription — Finale',
                html: `<p class="mb-3">Quels tours souhaitez-vous arbitrer ?</p>
                    <div class="text-left pl-4">${chkHtml}</div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-hand-paper mr-1"></i>S\'inscrire',
                cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-secondary ml-2' },
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
                const round = result.value;
                doSignup(encId, round, self);
            });
        } else {
            const dateLabel = self.dataset.dateLabel || '';
            Swal.fire({
                title: '<i class="fas fa-hand-paper mr-2" style="color:#0dcaf0"></i>Arbitrage',
                html: `Je me mets à l'arbitrage pour ce<br><strong>${dateLabel}</strong>`,
                showCancelButton: true,
                confirmButtonText: 'Je confirme',
                cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-info', cancelButton: 'btn btn-secondary ml-2' },
                buttonsStyling: false,
            }).then(result => {
                if (!result.isConfirmed) return;
                doSignup(encId, 0, self);
            });
        }
    });
}

// Bitmask → "Tour 1 · Tour N" (générique, jusqu'à 8 tours)
function decodeTours(mask) {
    const t = [];
    for (let i = 0; i < 8; i++) {
        if (mask & (1 << i)) t.push('Tour ' + (i + 1));
    }
    return t.join(' · ');
}

function doSignup(encId, round, btn) {
    postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/signup`, {round})
    .then(data => {
        if (!data.success) return Swal.fire('Erreur', data.message, 'error');

        if (btn.dataset.type === 'finale') {
            const roundTip = round > 0 ? `<i class="far fa-clock arb-rounds" data-toggle="tooltip" title="${decodeTours(round)}"></i>` : '';
            const newRow = `
                <div class="arb-row" data-arb-id="${data.arb_id}">
                    <span class="arb-name me-highlight">${data.name}</span>
                    ${roundTip}
                    <span class="badge-confirmed" data-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>
                </div>`;
            const list = document.getElementById(`arb-list-${encId}`);
            list.insertAdjacentHTML('beforeend', newRow);
            $(list.lastElementChild).find('[data-toggle="tooltip"]').tooltip({ template: rbcdTooltipTemplate });
            btn.classList.add('d-none');
        } else {
            const div = document.getElementById(`arb-normal-${encId}`);
            div.innerHTML = `
                <span class="arb-label">Arbitrage :</span>
                <span class="arb-name me-highlight">${data.name}</span>
                <span class="badge-confirmed" data-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>`;
            $(div).find('[data-toggle="tooltip"]').tooltip({ template: rbcdTooltipTemplate });
        }
    });
}


// ── Arbitrage : confirmer convocation ──
document.querySelectorAll('.btn-arb-confirm').forEach(btn => {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/confirm`, {})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            // Remplacer Convoqué + Confirmer par ✓
            const row = this.closest('.arb-row') || document.getElementById(`arb-normal-${encId}`);
            const convBadge = row.querySelector('.badge-conv');
            const confirmBtn = row.querySelector('.btn-arb-confirm');
            if (convBadge) {
                convBadge.outerHTML = '<span class="badge-confirmed" data-toggle="tooltip" title="Confirmé"><i class="fas fa-check"></i></span>';
                $(row).find('.badge-confirmed[data-toggle="tooltip"]').tooltip({ template: rbcdTooltipTemplate });
            }
            if (confirmBtn) confirmBtn.remove();
        });
    });
});

// ── Bar : s'inscrire ──
document.querySelectorAll('.btn-bar-signup').forEach(bindBarSignup);

function bindBarSignup(btn) {
    btn.addEventListener('click', function() {
        const date   = this.dataset.date;
        const period = this.dataset.period;
        postJson('<?= base_url('tableau/bar/signup') ?>', {date, period})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            const parentDiv = this.parentElement;
            this.outerHTML = `
                <span class="bar-slot-taken">${data.name}</span>
                <button class="btn-cancel btn-bar-cancel" data-id="${data.id}" data-date="${date}" data-period="${period}">Annuler</button>`;
            bindBarCancel(parentDiv.querySelector('.btn-bar-cancel'));
        });
    });
}

</script>
<?= $this->endSection() ?>
