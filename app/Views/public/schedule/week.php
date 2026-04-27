<?= $this->extend('public/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
/* ── Tableau public — styles du tableau hebdomadaire ── */
:root {
    --clr-home: #198754;
    --clr-away: #c6000d;
    --clr-3b:   #0dcaf0;

    --clr-plpf: #198754;
    --clr-3eme: #6f42c1;
    --clr-coupe:#fd7e14;
    --clr-comp: #6c757d;
}

.week-nav .btn { min-width:160px; }
.week-title { font-size:1.1rem; font-weight:700; }

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
.day-empty .day-card-header { opacity:.5; }
.day-card-body { padding:0; }

/* Encounter row */
.enc-block {
    display:grid;
    grid-template-columns: 72px 100px 1fr 240px 1fr;
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
.loc-icon { font-size:1rem; display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
.loc-home { color:var(--clr-home); }
.loc-away { color:var(--clr-away); }

/* Players — VS aligné en colonne */
.players-col { line-height:1.6; }
.match-line {
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    gap:4px;
    font-size:.88rem;
    margin-bottom:2px;
}
.player-home { text-align:right; font-weight:600; }
.player-away { text-align:left; }
.vs-sep { color:#aaa; font-size:.78rem; text-align:center; }

/* Compétition (texte libre) */
.comp-col { border-left:2px solid rgba(0,0,0,.08); border-right:2px solid rgba(0,0,0,.08); padding:0 .8rem; }
.comp-label { font-size:.8rem; color:#555; font-style:italic; line-height:1.3; }

/* Badge Finale */
.badge-finale { display:inline-block; background:#ffc107; color:#000; border-radius:10px;
                padding:1px 8px; font-size:.73rem; font-weight:600; margin-bottom:3px; }

/* Arbitrage col */
.arb-col  { display:flex; flex-direction:column; gap:4px; font-size:.88rem; }
.arb-row  { display:flex; align-items:center; gap:.4rem; flex-wrap:wrap; }
.arb-label { font-size:.75rem; color:#888; font-weight:600; white-space:nowrap; }
.arb-name { font-weight:600; }

.badge-confirmed { background:#198754; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; }
.badge-pending   { background:#ffc107; color:#000; border-radius:10px; padding:1px 7px; font-size:.73rem; }
.badge-mine      { background:#0d6efd; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; }
.badge-conv      { background:#fd7e14; color:#fff; border-radius:10px; padding:1px 7px; font-size:.73rem; }

/* Bar slot in day-header */
.bar-slots { display:flex; align-items:center; gap:.5rem; font-size:.83rem; }
.bar-slot-taken  { font-weight:600; color:#198754; }
.bar-slot-free   { color:#aaa; font-style:italic; }

/* Signup buttons */
.btn-signup, .btn-cancel { font-size:.78rem; padding:2px 8px; border-radius:10px; }
.btn-signup { background:#e8f4fd; color:#0d6efd; border:1px solid #bee3fd; }
.btn-signup:hover { background:#0d6efd; color:#fff; }
.btn-cancel { background:#fdecea; color:#dc3545; border:1px solid #f5c6cb; }
.btn-cancel:hover { background:#dc3545; color:#fff; }
.btn-confirm { font-size:.78rem; padding:2px 8px; border-radius:10px; background:#fff3cd; color:#856404; border:1px solid #ffc107; }
.btn-confirm:hover { background:#ffc107; color:#000; }

@media (max-width:767px) {
    .enc-block { grid-template-columns: 1fr; }
    .week-nav .btn { min-width:auto; }
    .comp-col { border:none; padding:0; }
    /* Matchs : affichage inline gauche sur mobile */
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

$monday = new \DateTime($weekDates[0]);
$sunday = new \DateTime($weekDates[6]);
$periodStr = $monday->format('j') . ' ' . $frMonths[(int)$monday->format('n')-1]
           . ' — ' . $sunday->format('j') . ' ' . $frMonths[(int)$sunday->format('n')-1]
           . ' ' . $sunday->format('Y');
?>

<!-- Navigation semaine -->
<div class="d-flex align-items-center justify-content-between mb-4 week-nav flex-wrap" style="gap:.5rem">
    <a href="<?= base_url("tableau/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary">
        <i class="fas fa-chevron-left mr-1"></i>Semaine précédente
    </a>
    <div class="text-center">
        <div class="week-title">Semaine <?= $week ?></div>
        <div class="text-muted" style="font-size:.9rem"><?= esc($periodStr) ?></div>
    </div>
    <a href="<?= base_url("tableau/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary">
        Semaine suivante <i class="fas fa-chevron-right ml-1"></i>
    </a>
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
$hasHomeMatch = !empty($homeDateFlags[$date]);
$dayLabel     = frDay($date, $frDays, $frMonths);
?>

<div class="day-card <?= $isActive ? '' : 'day-empty' ?>">
    <!-- En-tête du jour -->
    <div class="day-card-header">
        <span><?= esc($dayLabel) ?></span>

        <!-- Bar AM / Soir -->
        <div class="bar-slots">
            <span class="text-muted">Bar AM :</span>
            <?php if ($barAm): ?>
                <span class="bar-slot-taken">
                    <?= esc($barAm->last_name) ?> <?= esc(mb_substr($barAm->first_name,0,1)) ?>.
                </span>
                <?php if ($isLogged && $barAm->admin_user_id == $currentUser): ?>
                    <button class="btn-cancel btn-bar-cancel"
                            data-id="<?= $barAm->id ?>"
                            data-date="<?= $date ?>"
                            data-period="am">Annuler</button>
                <?php endif; ?>
            <?php elseif ($isLogged): ?>
                <button class="btn-signup btn-bar-signup"
                        data-date="<?= $date ?>"
                        data-period="am">S'inscrire</button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>

            <span class="text-muted ml-2">Bar soirée :</span>
            <?php if ($barSoir): ?>
                <span class="bar-slot-taken">
                    <?= esc($barSoir->last_name) ?> <?= esc(mb_substr($barSoir->first_name,0,1)) ?>.
                </span>
                <?php if ($isLogged && $barSoir->admin_user_id == $currentUser): ?>
                    <button class="btn-cancel btn-bar-cancel"
                            data-id="<?= $barSoir->id ?>"
                            data-date="<?= $date ?>"
                            data-period="soir">Annuler</button>
                <?php endif; ?>
            <?php elseif ($isLogged): ?>
                <button class="btn-signup btn-bar-signup"
                        data-date="<?= $date ?>"
                        data-period="soir">S'inscrire</button>
            <?php else: ?>
                <span class="bar-slot-free">libre</span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rencontres du jour -->
    <?php if (!empty($dayEncs)): ?>
    <div class="day-card-body">
        <?php foreach ($dayEncs as $enc): ?>
        <div class="enc-block <?= $enc->is_home ? 'home' : 'away' ?>"
             data-encounter="<?= $enc->id ?>">

            <!-- Heure -->
            <div><span class="time-pill"><?= esc(substr($enc->match_time, 0, 5)) ?></span></div>

            <!-- Domicile / Déplacement -->
            <div class="loc-icon">
                <?php if ($enc->is_home): ?>
                    <i class="fas fa-home loc-home" title="À domicile"></i>
                <?php else: ?>
                    <i class="fas fa-car-side loc-away" title="<?= esc($enc->venue ?? 'En déplacement') ?>"></i>
                    <?php if ($enc->venue): ?>
                        <span style="font-size:.72rem;margin-left:15px;color:var(--clr-away)"><?= esc($enc->venue) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Rencontre -->
            <div class="players-col">
                <?php if ($enc->encounter_type === 'finale'): ?>
                    <span class="badge-finale"><i class="fas fa-trophy mr-1"></i>Finale</span>
                <?php endif; ?>
                <?php foreach ($enc->players as $p): ?>
                    <div class="match-line">
                        <span class="player-home">
                            <?= $p->member_id
                                ? esc($p->last_name . ' ' . mb_substr($p->first_name, 0, 1) . '.')
                                : esc($p->player_home_name ?? '—') ?>
                        </span>
                        <span class="vs-sep">vs</span>
                        <span class="player-away"><?= esc($p->opponent_name) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Compétition -->
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
                    <?php if ($enc->encounter_type === 'finale'): ?>
                        <?php foreach ([1, 2, 3] as $r): ?>
                            <?php $arbSlot = $enc->arbitrageByRound[$r] ?? null; ?>
                            <div class="arb-row" id="arb-<?= $enc->id ?>-<?= $r ?>">
                                <span class="arb-label">Tour <?= $r ?> :</span>
                                <?php if ($arbSlot): ?>
                                    <?php
                                    $isMe   = $isLogged && $arbSlot->admin_user_id == $currentUser;
                                    $isConv = $arbSlot->assignment_type === 'designated';
                                    ?>
                                    <span class="arb-name"><?= esc($arbSlot->last_name) ?> <?= esc(mb_substr($arbSlot->first_name,0,1)) ?>.</span>
                                    <?php if ($isMe && $isConv && !$arbSlot->confirmed): ?>
                                        <span class="badge-conv">Convoqué</span>
                                        <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>" data-round="<?= $r ?>">
                                            <i class="fas fa-check mr-1"></i>Confirmer
                                        </button>
                                    <?php elseif ($arbSlot->confirmed): ?>
                                        <span class="badge-confirmed">✓ Confirmé</span>
                                        <?php if ($isMe && !$isConv): ?>
                                            <button class="btn-cancel btn-arb-cancel" data-encounter="<?= $enc->id ?>" data-round="<?= $r ?>">Annuler</button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge-pending">En attente</span>
                                    <?php endif; ?>
                                    <?php if ($isMe): ?>
                                        <span class="badge-mine">Vous</span>
                                    <?php endif; ?>
                                <?php elseif ($isLogged): ?>
                                    <button class="btn-signup btn-arb-signup" data-encounter="<?= $enc->id ?>" data-round="<?= $r ?>">
                                        <i class="fas fa-hand-paper mr-1"></i>Arbitrer
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:.82rem">libre</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="arb-row" id="arb-<?= $enc->id ?>-0">
                            <span class="arb-label">Arbitrage :</span>
                            <?php if ($enc->arbitrageByRound[0] ?? null): ?>
                                <?php
                                $arb    = $enc->arbitrageByRound[0];
                                $isMe   = $isLogged && $arb->admin_user_id == $currentUser;
                                $isConv = $arb->assignment_type === 'designated';
                                ?>
                                <span class="arb-name"><?= esc($arb->last_name) ?> <?= esc(mb_substr($arb->first_name,0,1)) ?>.</span>
                                <?php if ($isMe && $isConv && !$arb->confirmed): ?>
                                    <span class="badge-conv">Convoqué</span>
                                    <button class="btn-confirm btn-arb-confirm" data-encounter="<?= $enc->id ?>" data-round="0">
                                        <i class="fas fa-check mr-1"></i>Confirmer
                                    </button>
                                <?php elseif ($arb->confirmed): ?>
                                    <span class="badge-confirmed">✓ Confirmé</span>
                                    <?php if ($isMe && !$isConv): ?>
                                        <button class="btn-cancel btn-arb-cancel" data-encounter="<?= $enc->id ?>" data-round="0">Annuler</button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge-pending">En attente</span>
                                <?php endif; ?>
                                <?php if ($isMe): ?>
                                    <span class="badge-mine">Vous</span>
                                <?php endif; ?>
                            <?php elseif ($isLogged): ?>
                                <button class="btn-signup btn-arb-signup" data-encounter="<?= $enc->id ?>" data-round="0">
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
    <?php elseif (!$isActive): ?>
    <div class="p-3 text-muted" style="font-size:.85rem">Pas de rencontre ce jour.</div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName  = '<?= csrf_token() ?>';
let   csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: Object.entries({...body, [csrfName]: csrfToken})
                    .map(([k,v]) => `${k}=${encodeURIComponent(v)}`).join('&'),
    }).then(r => r.json());
}

// Retourne le div cible : toujours arb-{encId}-{round}
function getArbRow(encId, round) {
    return document.getElementById(`arb-${encId}-${round}`);
}

// Label arbitrage pour le innerHTML
function arbLabel(round) {
    return parseInt(round) > 0
        ? `<span class="arb-label">Tour ${round} :</span>`
        : `<span class="arb-label">Arbitrage :</span>`;
}

// ── Arbitrage : s'inscrire ──
document.querySelectorAll('.btn-arb-signup').forEach(btn => {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const round = this.dataset.round || '0';
        postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/signup`, {round})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            const row = getArbRow(encId, round);
            row.innerHTML = `
                ${arbLabel(round)}
                <span class="arb-name">${data.name}</span>
                <span class="badge-confirmed">✓ Confirmé</span>
                <span class="badge-mine">Vous</span>
                <button class="btn-cancel btn-arb-cancel" data-encounter="${encId}" data-round="${round}">Annuler</button>`;
            bindArbCancel(row.querySelector('.btn-arb-cancel'));
        });
    });
});

// ── Arbitrage : annuler ──
function bindArbCancel(btn) {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const round = this.dataset.round || '0';
        postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/cancel`, {round})
        .then(data => {
            if (!data.success) return Swal.fire('Info', data.message, 'info');
            const row = getArbRow(encId, round);
            row.innerHTML = `
                ${arbLabel(round)}
                <button class="btn-signup btn-arb-signup" data-encounter="${encId}" data-round="${round}">
                    <i class="fas fa-hand-paper mr-1"></i>Arbitrer
                </button>`;
            row.querySelector('.btn-arb-signup').addEventListener('click', function() {
                location.reload();
            });
        });
    });
}
document.querySelectorAll('.btn-arb-cancel').forEach(bindArbCancel);

// ── Arbitrage : confirmer convocation ──
document.querySelectorAll('.btn-arb-confirm').forEach(btn => {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const round = this.dataset.round || '0';
        postJson(`<?= base_url('tableau/arbitrage/') ?>${encId}/confirm`, {round})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            const row    = getArbRow(encId, round);
            const nameEl = row.querySelector('.arb-name');
            const name   = nameEl ? nameEl.textContent : '';
            row.innerHTML = `
                ${arbLabel(round)}
                <span class="arb-name">${name}</span>
                <span class="badge-confirmed">✓ Confirmé</span>
                <span class="badge-mine">Vous</span>`;
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

// ── Bar : annuler ──
document.querySelectorAll('.btn-bar-cancel').forEach(bindBarCancel);

function bindBarCancel(btn) {
    btn.addEventListener('click', function() {
        const id     = this.dataset.id;
        const period = this.dataset.period;
        const date   = this.dataset.date;
        postJson(`<?= base_url('tableau/bar/') ?>${id}/cancel`, {})
        .then(data => {
            if (!data.success) return Swal.fire('Erreur', data.message, 'error');
            const nameEl = this.previousElementSibling;
            if (nameEl) nameEl.remove();
            this.outerHTML = `
                <button class="btn-signup btn-bar-signup" data-date="${date}" data-period="${period}">S'inscrire</button>`;
            document.querySelectorAll('.btn-bar-signup').forEach(bindBarSignup);
        });
    });
}
</script>
<?= $this->endSection() ?>
