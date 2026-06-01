<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.enc-home      { background: #e8f5e9; }
.enc-away      { background: #DC656D24; }
.enc-home > td:first-child { border-left: 4px solid #28a745; }
.enc-away > td:first-child { border-left: 4px solid #c6000d; }
.text-away     { color: #c6000d; }
.enc-row        { transition:background .15s; }
.enc-home:hover { background: #d0f7d3; }
.enc-away:hover { background: #dc656d47; }

.arb-name      { font-weight:600; }
.day-card      { margin-bottom:1.25rem; overflow:hidden; }
.time-badge    { font-size:.82rem; background:#e9ecef; color:#343a40; border-radius:4px; padding:2px 7px; white-space:nowrap; }
.loc-cell      { display:flex; align-items:flex-start; gap:5px; }
.loc-cell i    { margin-top:2px; flex-shrink:0; }
.loc-venue     { font-size:.72rem; color:#c6000d; line-height:1.3; }
.match-line    { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:4px; font-size:.88rem; margin-bottom:2px; }
.player-home   { text-align:right; }
.player-away   { text-align:left; }
.player-rbcd   { font-weight:600; }
/* Liste d'arbitres pour les finales */
.arb-list      { display:flex; flex-direction:column; gap:3px; margin-bottom:3px; }
.arb-item      { display:flex; align-items:center; gap:.3rem; flex-wrap:wrap; font-size:.83rem; }
.arb-rounds    { color:#888; cursor:default; }

.col-actions { border-left:2px solid rgba(0,0,0,.08); border-right:2px solid rgba(0,0,0,.08); padding:0 .8rem; }

/* Événements */
.event-admin-row {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .45rem 1rem;
    font-size: .85rem;
    border-bottom: 1px solid rgba(0,0,0,.06);
    transition: filter .15s;
}
.event-admin-row:hover { filter: brightness(.93); }
.event-admin-row:last-of-type { border-bottom: none; }
.event-admin-title { font-weight: 700; flex: 1; }
.event-admin-time  { font-size: .78rem; opacity: .75; white-space: nowrap; }
.event-admin-desc  { font-size: .8rem; opacity: .8; }


@media (max-width:767px) {
    .day-card thead { display:none; }
    .day-card .enc-row { display:block; border-bottom:2px solid #dee2e6; }
    .day-card .enc-row td { display:block; border:none; padding:.2rem .75rem; width:100%; box-sizing:border-box; }
    .match-line  { display:block; margin-bottom:1px; }
    .player-home { display:inline; text-align:left; }
    .player-away { display:inline; }
    .day-card .enc-row td:nth-child(5),
    .day-card .enc-row td:nth-child(6) { text-align:right; }
    .loc-cell { flex-wrap:nowrap; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$frDays   = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$frMonths = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

// Bitmask → "Tour 1 · Tour 3" (bit i → Tour i+1, up to 8 tours)
function decodeTours(int $mask): string {
    $t = [];
    for ($i = 0; $i < 8; $i++) {
        if ($mask & (1 << $i)) $t[] = 'Tour ' . ($i + 1);
    }
    return $t ? implode(' · ', $t) : '';
}

function frDate(string $ymd, array $frDays, array $frMonths): string {
    $dt    = new \DateTime($ymd);
    $dow   = (int)$dt->format('N') - 1;
    $day   = (int)$dt->format('j');
    $month = (int)$dt->format('n') - 1;
    $year  = $dt->format('Y');
    return $frDays[$dow] . ' ' . $day . ' ' . $frMonths[$month] . ' ' . $year;
}

$monday = new \DateTime($weekDates[0]);
$sunday = new \DateTime($weekDates[6]);
$periodStr = $monday->format('j') . ' ' . $frMonths[(int)$monday->format('n')-1]
           . ' — ' . $sunday->format('j') . ' ' . $frMonths[(int)$sunday->format('n')-1]
           . ' ' . $sunday->format('Y');

$nowDt = new \DateTime();
$isCurrentWeek = ($week == (int)$nowDt->format('W') && $year == (int)$nowDt->format('o'));
?>

<!-- Navigation semaine -->
<div class="mb-3">
    <div class="d-flex justify-content-center align-items-stretch mb-2" style="gap:.5rem">
        <a href="<?= base_url("admin/schedule/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary btn-sm text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-left d-block d-sm-inline mr-sm-2"></i>Semaine précédente
        </a>
        <?php if (!$isCurrentWeek): ?>
            <a href="<?= base_url('admin/schedule') ?>" class="btn btn-outline-secondary btn-sm text-center px-2" style="flex:1;min-width:0;">
                <i class="fas fa-chevron-down d-block d-sm-inline mr-sm-2"></i>Semaine en cours
            </a>
        <?php else: ?>
            <span class="btn btn-outline-secondary btn-sm text-center px-2" style="flex:1;min-width:0;background:#6c757d;border-color:#6c757d;color:#fff;pointer-events:none;">
                <i class="fas fa-chevron-down d-block d-sm-inline mr-sm-2"></i>Semaine en cours
            </span>
        <?php endif; ?>
        <a href="<?= base_url("admin/schedule/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary btn-sm text-center px-2" style="flex:1;min-width:0;">
            <i class="fas fa-chevron-right d-block d-sm-none"></i>Semaine suivante<i class="fas fa-chevron-right d-none d-sm-inline ml-2"></i>
        </a>
    </div>
    <div class="text-center">
        <span class="badge badge-primary" style="font-size:1rem;padding:.5rem 1rem;">
            Semaine <?= $week ?> — <?= esc($periodStr) ?>
        </span>
    </div>
</div>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= base_url('admin/schedule/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Ajouter une rencontre
    </a>
</div>

<?php foreach ($weekDates as $date): ?>
<?php
$dayEncounters = $byDate[$date] ?? [];
$barAm   = $barByDate[$date]['am']   ?? null;
$barSoir = $barByDate[$date]['soir'] ?? null;
$hasContent = !empty($dayEncounters) || $barAm || $barSoir;
?>

<div class="card card-outline card-primary day-card <?= $hasContent ? '' : 'opacity-60' ?>">
    <div class="card-header py-2 d-flex align-items-center">
        <h3 class="card-title" style="font-size:.95rem;font-weight:700">
            <?= frDate($date, $frDays, $frMonths) ?>
        </h3>
        <?php if (!$hasContent): ?>
            <span class="ml-2 text-muted" style="font-size:.8rem">— pas de rencontre</span>
        <?php endif; ?>
        <div class="ml-auto d-flex align-items-center" style="gap:.5rem">
            <span class="text-muted" style="font-size:.75rem">Bar AM :</span>
            <?php if ($barAm): ?>
                <span class="badge badge-success"><?= esc($barAm->last_name) ?> <?= esc(member_initials($barAm->first_name)) ?>.</span>
                <button class="btn btn-xs btn-danger btn-bar-cancel" data-id="<?= $barAm->id ?>"><i class="fas fa-times"></i></button>
            <?php else: ?>
                <span class="badge badge-info btn-bar-assign" style="cursor:pointer"
                      data-date="<?= $date ?>" data-period="am">libre</span>
            <?php endif; ?>
            <span class="text-muted ml-2" style="font-size:.75rem">Bar soir :</span>
            <?php if ($barSoir): ?>
                <span class="badge badge-success"><?= esc($barSoir->last_name) ?> <?= esc(member_initials($barSoir->first_name)) ?>.</span>
                <button class="btn btn-xs btn-danger btn-bar-cancel" data-id="<?= $barSoir->id ?>"><i class="fas fa-times"></i></button>
            <?php else: ?>
                <span class="badge badge-info btn-bar-assign" style="cursor:pointer"
                      data-date="<?= $date ?>" data-period="soir">libre</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($eventsByDate[$date])): ?>
    <div class="day-card-events">
        <?php foreach ($eventsByDate[$date] as $ev):
            $c = $eventColors[$ev->color] ?? $eventColors['blue'];
        ?>
        <div class="event-admin-row" style="background:<?= $c['bg'] ?>;border-left:4px solid <?= $c['border'] ?>;color:<?= $c['text'] ?>;">
            <i class="fas fa-calendar-day" style="color:<?= $c['border'] ?>;flex-shrink:0;"></i>
            <div class="event-admin-title">
                <?= esc($ev->title) ?>
                <?php if ($ev->start_time): ?>
                    <span class="event-admin-time ml-2"><?= substr($ev->start_time, 0, 5) ?></span>
                <?php endif; ?>
                <?php if ($ev->description): ?>
                    <span class="event-admin-desc ml-2">— <?= esc($ev->description) ?></span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url("admin/schedule-events/{$ev->id}/duplicate") ?>"
               class="btn btn-xs btn-info ml-auto" title="Dupliquer">
                <i class="fas fa-copy"></i>
            </a>
            <a href="<?= base_url("admin/schedule-events/{$ev->id}/edit") ?>"
               class="btn btn-xs btn-warning" title="Modifier">
                <i class="fas fa-edit"></i>
            </a>
            <form method="post" action="<?= base_url("admin/schedule-events/{$ev->id}/delete") ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer cet événement ?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($dayEncounters)): ?>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th style="width:70px">Heure</th>
                    <th style="width:110px"></th>
                    <th class="text-center">Rencontre</th>
                    <th style="width:180px">Compétition</th>
                    <th style="width:230px">Arbitrage / Marqueurs</th>
                    <th style="width:80px" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dayEncounters as $enc): ?>
            <tr class="enc-row <?= $enc->is_home ? 'enc-home' : 'enc-away' ?>">
                <td class="align-middle">
                    <span class="time-badge"><?= esc(substr($enc->match_time, 0, 5)) ?></span>
                </td>
                <td class="align-middle">
                    <div class="loc-cell">
                        <?php if ($enc->is_home): ?>
                            <i class="fas fa-home text-success" title="À domicile"></i>
                        <?php else: ?>
                            <i class="fas fa-car-side text-away mr-3" title="En déplacement"></i>
                            <?php if ($enc->venue): ?>
                                <span class="loc-venue"><?= esc($enc->venue) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="align-middle">
                    <?php if (!empty($enc->players)): ?>
                        <?php
                        $isFinaleRow = $enc->encounter_type === 'finale';
                        ?>
                        <?php foreach ($enc->players as $p): ?>
                        <?php
                        $pName   = $p->member_id
                            ? esc($p->last_name . ' ' . member_initials($p->first_name))
                            : esc($p->player_home_name ?? '—');
                        $oppName = esc($p->opponent_name);
                        ?>
                            <div class="match-line">
                                <span class="player-home <?= (!$isFinaleRow && $enc->is_home)  ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $pName : $oppName ?></span>
                                <?php if (!empty($p->opponent_name)): ?><span class="text-muted" style="font-size:.78rem;padding:0 2px"><i class="fas fa-arrows-alt-h mr-2 ml-2"></i></span><?php endif; ?>
                                <span class="player-away <?= (!$isFinaleRow && !$enc->is_home) ? 'player-rbcd' : '' ?>"><?= $enc->is_home ? $oppName : $pName ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="align-middle">
                    <?php if ($enc->competition): ?>
                        <span style="font-size:.82rem;color:#495057;font-style:italic"><?= esc($enc->competition) ?></span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="align-middle" id="arb-cell-<?= $enc->id ?>">
                    <?php
                    $isFinaleEnc  = $enc->encounter_type === 'finale';
                    $needsMarqueur = $enc->is_home && $isFinaleEnc && !($enc->requires_arbitrage ?? 1);
                    $needsArb      = $enc->is_home && ($enc->requires_arbitrage ?? 1);
                    ?>
                    <?php if ($needsMarqueur): ?>
                        <!-- Finale fédérale : marqueurs -->
                        <div class="arb-list" id="mrq-list-<?= $enc->id ?>">
                            <?php foreach ($enc->marqueurRows as $mrq): ?>
                            <div class="arb-item" data-mrq-id="<?= $mrq->id ?>">
                                <span class="arb-name"><?= esc($mrq->last_name) ?> <?= esc(member_initials($mrq->first_name)) ?>.</span>
                                <?php if ($mrq->round): ?>
                                    <i class="far fa-clock arb-rounds" data-toggle="tooltip" title="<?= esc(decodeTours($mrq->round)) ?>"></i>
                                <?php endif; ?>
                                <button class="btn btn-xs btn-danger btn-remove-marqueur"
                                        data-encounter="<?= $enc->id ?>"
                                        data-mrq-id="<?= $mrq->id ?>"
                                        title="Retirer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-xs btn-warning btn-designate-marqueur"
                                data-encounter="<?= $enc->id ?>"
                                data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>">
                            <i class="fas fa-pen mr-1"></i>Désigner
                        </button>
                    <?php elseif ($needsArb): ?>
                        <!-- Arbitrage normal ou finale avec arbitres RBCD -->
                        <div class="arb-list" id="arb-list-<?= $enc->id ?>">
                            <?php foreach ($enc->arbitrageRows as $arb): ?>
                            <div class="arb-item" data-arb-id="<?= $arb->id ?>">
                                <span class="arb-name"><?= esc($arb->last_name) ?> <?= esc(member_initials($arb->first_name)) ?>.</span>
                                <?php if ($arb->round): ?>
                                    <i class="far fa-clock arb-rounds" data-toggle="tooltip" title="<?= esc(decodeTours($arb->round)) ?>"></i>
                                <?php endif; ?>
                                <?php if ($arb->assignment_type === 'designated'): ?>
                                    <?php if ($arb->confirmed): ?>
                                        <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">En attente</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-info">Volontaire</span>
                                <?php endif; ?>
                                <button class="btn btn-xs btn-danger btn-remove-referee"
                                        data-encounter="<?= $enc->id ?>"
                                        data-arb-id="<?= $arb->id ?>"
                                        title="Retirer">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-xs btn-info btn-designate-referee"
                                data-encounter="<?= $enc->id ?>"
                                data-type="<?= $enc->encounter_type ?>"
                                data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>">
                            <i class="fas fa-user-plus mr-1"></i>Désigner
                        </button>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-right text-nowrap col-actions">
                    <a href="<?= base_url("admin/schedule/{$enc->id}/duplicate") ?>"
                       class="btn btn-xs btn-info mr-1" title="Dupliquer">
                        <i class="fas fa-copy"></i>
                    </a>
                    <a href="<?= base_url("admin/schedule/{$enc->id}/edit") ?>"
                       class="btn btn-xs btn-warning" title="Modifier">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button class="btn btn-xs btn-danger btn-delete-encounter ml-1"
                            data-id="<?= $enc->id ?>"
                            data-url="<?= base_url("admin/schedule/{$enc->id}/delete") ?>"
                            title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

<!-- Modal assignation bar -->
<div class="modal fade" id="modalBar" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-glass-cheers mr-2"></i>Service bar</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalBarDate">
                <input type="hidden" id="modalBarPeriod">
                <div class="form-group mb-0">
                    <label id="modalBarLabel">Membre</label>
                    <select class="form-control" id="modalBarMemberId">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($allMembers as $m): ?>
                        <option value="<?= $m->id ?>"><?= esc($m->last_name) ?> <?= esc($m->first_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnConfirmBar">
                    <i class="fas fa-check mr-1"></i>Assigner
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal désignation marqueur -->
<div class="modal fade" id="modalMarqueur" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-pen mr-2"></i>Désigner un marqueur</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalMarqueurEncounterId">
                <div class="form-group">
                    <label>Membre</label>
                    <select class="form-control" id="modalMarqueurMemberId">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($allMembers as $m): ?>
                        <option value="<?= $m->id ?>"><?= esc($m->last_name) ?> <?= esc($m->first_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0" id="modalMarqueurRoundGroup">
                    <label>Tours à marquer</label>
                    <div id="modalMarqueurRoundCheckboxes" class="d-flex flex-wrap" style="gap:.8rem 1.2rem"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="btnConfirmMarqueur">
                    <i class="fas fa-check mr-1"></i>Confirmer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal désignation arbitre -->
<div class="modal fade" id="modalArbitrage" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-check mr-2"></i>Désigner un arbitre</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalEncounterId">
                <input type="hidden" id="modalEncounterType" value="normal">
                <div class="form-group">
                    <label>Membre fédéré</label>
                    <select class="form-control" id="modalMemberId">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m->id ?>"><?= esc($m->last_name) ?> <?= esc($m->first_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-0" id="modalRoundGroup" style="display:none">
                    <label>Tours à arbitrer</label>
                    <div id="modalRoundCheckboxes" class="d-flex flex-wrap" style="gap:.8rem 1.2rem"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnConfirmArbitrage">
                    <i class="fas fa-check mr-1"></i>Confirmer la désignation
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Initialiser les tooltips
$(function() { $('[data-toggle="tooltip"]').tooltip(); });

// ── Ouvrir le modal désignation ──
document.querySelectorAll('.btn-designate-referee').forEach(btn => {
    btn.addEventListener('click', function() { openDesignateModal(this); });
});

// Bitmask → "Tour 1 · Tour N" (générique, jusqu'à 8 tours)
function decodeTours(mask) {
    const t = [];
    for (let i = 0; i < 8; i++) {
        if (mask & (1 << i)) t.push('Tour ' + (i + 1));
    }
    return t.join(' · ');
}

let currentModalRounds = 3;

function openDesignateModal(btn) {
    const type = btn.dataset.type || 'normal';
    currentModalRounds = parseInt(btn.dataset.rounds || 3);
    document.getElementById('modalEncounterId').value   = btn.dataset.encounter;
    document.getElementById('modalEncounterType').value = type;
    document.getElementById('modalMemberId').value      = '';
    document.getElementById('modalRoundGroup').style.display = type === 'finale' ? '' : 'none';
    // Générer les cases à cocher selon le nombre de tours de la compétition
    const container = document.getElementById('modalRoundCheckboxes');
    container.innerHTML = '';
    for (let i = 0; i < currentModalRounds; i++) {
        const val = 1 << i;
        const id  = 'roundChk' + val;
        container.insertAdjacentHTML('beforeend',
            `<div class="custom-control custom-checkbox">` +
            `<input type="checkbox" class="custom-control-input" id="${id}" value="${val}" checked>` +
            `<label class="custom-control-label" for="${id}">Tour ${i + 1}</label>` +
            `</div>`
        );
    }
    $('#modalArbitrage').modal('show');
}

// ── Confirmer la désignation ──
document.getElementById('btnConfirmArbitrage').addEventListener('click', function() {
    const encounterId = document.getElementById('modalEncounterId').value;
    const memberId    = document.getElementById('modalMemberId').value;
    const type        = document.getElementById('modalEncounterType').value;
    if (!memberId) return;

    let round = 0;
    if (type === 'finale') {
        document.querySelectorAll('#modalRoundCheckboxes input[type="checkbox"]:checked').forEach(c => {
            round |= parseInt(c.value);
        });
        if (!round) round = (1 << currentModalRounds) - 1; // aucune case = tous les tours
    }

    fetch(`<?= base_url('admin/schedule/') ?>${encounterId}/referee`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: `<?= csrf_token() ?>=${csrfToken}&member_id=${memberId}&round=${round}`
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        $('#modalArbitrage').modal('hide');

        const roundLabel  = round > 0 ? `<i class="far fa-clock arb-rounds" data-toggle="tooltip" title="${decodeTours(round)}"></i>` : '';
        const newItem = `
            <div class="arb-item" data-arb-id="${data.arb_id}">
                <span class="arb-name">${data.name}</span>
                ${roundLabel}
                <span class="badge badge-warning">En attente</span>
                <button class="btn btn-xs btn-danger btn-remove-referee"
                        data-encounter="${encounterId}" data-arb-id="${data.arb_id}" title="Retirer">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        const list = document.getElementById(`arb-list-${encounterId}`);
        list.insertAdjacentHTML('beforeend', newItem);
        // Réinitialiser le tooltip sur le nouvel élément
        $(list.lastElementChild.querySelector('[data-toggle="tooltip"]')).tooltip();
        bindRemoveReferee(list.lastElementChild.querySelector('.btn-remove-referee'));
    });
});

// ── Retirer arbitre ──
function bindRemoveReferee(btn) {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const arbId = this.dataset.arbId;
        Swal.fire({
            title: 'Retirer l\'arbitre ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Oui, retirer',
            cancelButtonText: 'Annuler',
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`<?= base_url('admin/schedule/') ?>${encId}/referee/remove`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: `<?= csrf_token() ?>=${csrfToken}&arb_id=${arbId}`
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const item = document.querySelector(`#arb-list-${encId} [data-arb-id="${arbId}"]`);
                if (item) item.remove();
            });
        });
    });
}
document.querySelectorAll('.btn-remove-referee').forEach(bindRemoveReferee);

// ── Supprimer barman ──
function bindBarCancel(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Retirer ce barman ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Oui, retirer',
            cancelButtonText: 'Annuler',
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`<?= base_url('admin/schedule/bar/') ?>${id}/remove`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: `<?= csrf_token() ?>=${csrfToken}`
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const btn   = document.querySelector(`.btn-bar-cancel[data-id="${id}"]`);
                const badge = btn?.previousElementSibling;
                const period = btn?.dataset.period;
                const date   = btn?.dataset.date;
                if (badge) badge.remove();
                if (btn) btn.remove();
            });
        });
    });
}
document.querySelectorAll('.btn-bar-cancel').forEach(bindBarCancel);

// ── Assigner barman ──
document.querySelectorAll('.btn-bar-assign').forEach(badge => {
    badge.addEventListener('click', function() {
        const periodLabel = this.dataset.period === 'am' ? 'Bar après-midi' : 'Bar soirée';
        document.getElementById('modalBarDate').value   = this.dataset.date;
        document.getElementById('modalBarPeriod').value = this.dataset.period;
        document.getElementById('modalBarLabel').textContent = periodLabel;
        document.getElementById('modalBarMemberId').value = '';
        this._sourceBadge = this;
        $('#modalBar').modal('show');
    });
});

document.getElementById('btnConfirmBar').addEventListener('click', function() {
    const date     = document.getElementById('modalBarDate').value;
    const period   = document.getElementById('modalBarPeriod').value;
    const memberId = document.getElementById('modalBarMemberId').value;
    if (!memberId) return;

    fetch('<?= base_url('admin/schedule/bar/assign') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: `<?= csrf_token() ?>=${csrfToken}&duty_date=${date}&period=${period}&member_id=${memberId}`
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        $('#modalBar').modal('hide');
        // Remplacer le badge "libre" par le nom + bouton supprimer
        const badge = document.querySelector(`.btn-bar-assign[data-date="${date}"][data-period="${period}"]`);
        if (badge) {
            const successBadge = document.createElement('span');
            successBadge.className = 'badge badge-success';
            successBadge.textContent = data.name;
            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'btn btn-xs btn-danger btn-bar-cancel';
            cancelBtn.dataset.id = data.id;
            cancelBtn.innerHTML = '<i class="fas fa-times"></i>';
            bindBarCancel(cancelBtn);
            badge.replaceWith(successBadge);
            successBadge.insertAdjacentElement('afterend', cancelBtn);
        }
    });
});

// ── Marqueur : ouvrir modal ──
let currentMrqRounds = 3;

document.querySelectorAll('.btn-designate-marqueur').forEach(btn => {
    btn.addEventListener('click', function() {
        currentMrqRounds = parseInt(this.dataset.rounds || 3);
        document.getElementById('modalMarqueurEncounterId').value = this.dataset.encounter;
        document.getElementById('modalMarqueurMemberId').value = '';

        const container = document.getElementById('modalMarqueurRoundCheckboxes');
        container.innerHTML = '';
        for (let i = 0; i < currentMrqRounds; i++) {
            const val = 1 << i;
            const id  = 'mrqChk' + val;
            container.insertAdjacentHTML('beforeend',
                `<div class="custom-control custom-checkbox">` +
                `<input type="checkbox" class="custom-control-input" id="${id}" value="${val}" checked>` +
                `<label class="custom-control-label" for="${id}">Tour ${i + 1}</label>` +
                `</div>`
            );
        }
        $('#modalMarqueur').modal('show');
    });
});

// ── Marqueur : confirmer désignation ──
document.getElementById('btnConfirmMarqueur').addEventListener('click', function() {
    const encounterId = document.getElementById('modalMarqueurEncounterId').value;
    const memberId    = document.getElementById('modalMarqueurMemberId').value;
    if (!memberId) return;

    let round = 0;
    document.querySelectorAll('#modalMarqueurRoundCheckboxes input[type="checkbox"]:checked').forEach(c => {
        round |= parseInt(c.value);
    });
    if (!round) round = (1 << currentMrqRounds) - 1;

    fetch(`<?= base_url('admin/schedule/') ?>${encounterId}/marqueur`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: `<?= csrf_token() ?>=${csrfToken}&member_id=${memberId}&round=${round}`
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return Swal.fire('Erreur', data.message, 'warning');
        $('#modalMarqueur').modal('hide');

        const roundLabel = round > 0
            ? `<i class="far fa-clock arb-rounds" data-toggle="tooltip" title="${decodeTours(round)}"></i>`
            : '';
        const newItem = `
            <div class="arb-item" data-mrq-id="${data.mrq_id}">
                <span class="arb-name">${data.name}</span>
                ${roundLabel}
                <button class="btn btn-xs btn-danger btn-remove-marqueur"
                        data-encounter="${encounterId}" data-mrq-id="${data.mrq_id}" title="Retirer">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        const list = document.getElementById(`mrq-list-${encounterId}`);
        list.insertAdjacentHTML('beforeend', newItem);
        $(list.lastElementChild.querySelector('[data-toggle="tooltip"]')).tooltip();
        bindRemoveMarqueur(list.lastElementChild.querySelector('.btn-remove-marqueur'));
    });
});

// ── Marqueur : retirer ──
function bindRemoveMarqueur(btn) {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
        const mrqId = this.dataset.mrqId;
        Swal.fire({
            title: 'Retirer ce marqueur ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Oui, retirer',
            cancelButtonText: 'Annuler',
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`<?= base_url('admin/schedule/') ?>${encId}/marqueur/remove`, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
                body: `<?= csrf_token() ?>=${csrfToken}&mrq_id=${mrqId}`
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const item = document.querySelector(`#mrq-list-${encId} [data-mrq-id="${mrqId}"]`);
                if (item) item.remove();
            });
        });
    });
}
document.querySelectorAll('.btn-remove-marqueur').forEach(bindRemoveMarqueur);

// ── Supprimer rencontre ──
document.querySelectorAll('.btn-delete-encounter').forEach(btn => {
    btn.addEventListener('click', function() {
        const url = this.dataset.url;
        Swal.fire({
            title: 'Supprimer cette rencontre ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Supprimer',
            cancelButtonText: 'Annuler',
        }).then(result => {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `<input type="hidden" name="<?= csrf_token() ?>" value="${csrfToken}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
});
</script>
<?= $this->endSection() ?>
