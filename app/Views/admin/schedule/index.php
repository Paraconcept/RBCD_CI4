<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.enc-home      { border-left:4px solid #28a745; background: #e8f5e9; }
.enc-away      { border-left:4px solid #c6000d; background: #DC656D24; }
.text-away     { color: #c6000d; }
.enc-row        { transition:background .15s; }
.enc-home:hover { background: #d0f7d3; }
.enc-away:hover { background: #dc656d47; }

.arb-name      { font-weight:600; }
.day-card      { margin-bottom:1.25rem; }
.time-badge    { font-size:.82rem; background:#e9ecef; color:#343a40; border-radius:4px; padding:2px 7px; white-space:nowrap; }
.loc-cell      { display:flex; align-items:center; gap:5px; flex-wrap:wrap; }
.loc-venue     { font-size:.72rem; color:#c6000d; margin-left:15px; }
.match-line    { display:grid; grid-template-columns:1fr auto 1fr; align-items:center; gap:4px; font-size:.88rem; margin-bottom:2px; }
.player-home   { text-align:right; font-weight:600; }
.player-away   { text-align:left; }
/* Liste d'arbitres pour les finales */
.arb-list      { display:flex; flex-direction:column; gap:3px; margin-bottom:3px; }
.arb-item      { display:flex; align-items:center; gap:.3rem; flex-wrap:wrap; font-size:.83rem; }
.arb-rounds    { color:#888; cursor:default; }

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
?>

<!-- Navigation semaine -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap" style="gap:.5rem">
    <a href="<?= base_url("admin/schedule/{$prev['week']}/{$prev['year']}") ?>" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-chevron-left mr-1"></i> Semaine précédente
    </a>
    <div class="text-center">
        <span class="badge badge-primary" style="font-size:1rem;padding:.5rem 1rem;">
            Semaine <?= $week ?> — <?= esc($periodStr) ?>
        </span>
    </div>
    <a href="<?= base_url("admin/schedule/{$next['week']}/{$next['year']}") ?>" class="btn btn-outline-secondary btn-sm">
        Semaine suivante <i class="fas fa-chevron-right ml-1"></i>
    </a>
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
                <span class="badge badge-success"><?= esc($barAm->last_name) ?> <?= esc(mb_substr($barAm->first_name,0,1)) ?>.</span>
                <button class="btn btn-xs btn-outline-danger btn-bar-cancel" data-id="<?= $barAm->id ?>"><i class="fas fa-times"></i></button>
            <?php else: ?>
                <span class="text-muted badge badge-light">libre</span>
            <?php endif; ?>
            <span class="text-muted ml-2" style="font-size:.75rem">Bar soir :</span>
            <?php if ($barSoir): ?>
                <span class="badge badge-success"><?= esc($barSoir->last_name) ?> <?= esc(mb_substr($barSoir->first_name,0,1)) ?>.</span>
                <button class="btn btn-xs btn-outline-danger btn-bar-cancel" data-id="<?= $barSoir->id ?>"><i class="fas fa-times"></i></button>
            <?php else: ?>
                <span class="text-muted badge badge-light">libre</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($dayEncounters)): ?>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th style="width:70px">Heure</th>
                    <th style="width:110px"></th>
                    <th>Rencontre</th>
                    <th style="width:110px">Compétition</th>
                    <th style="width:230px">Arbitrage</th>
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
                            <i class="fas fa-car-side text-away" title="En déplacement"></i>
                            <?php if ($enc->venue): ?>
                                <span class="loc-venue"><?= esc($enc->venue) ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="align-middle">
                    <?php if (!empty($enc->players)): ?>
                        <?php foreach ($enc->players as $p): ?>
                            <div class="match-line">
                                <span class="player-home">
                                    <?= $p->member_id
                                        ? esc($p->last_name . ' ' . mb_substr($p->first_name, 0, 1) . '.')
                                        : esc($p->player_home_name ?? '—') ?>
                                </span>
                                <span class="text-muted" style="font-size:.78rem;padding:0 2px">vs</span>
                                <span class="player-away"><?= esc($p->opponent_name) ?></span>
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
                    <?php if ($enc->is_home): ?>
                        <!-- Liste des arbitres déjà inscrits -->
                        <div class="arb-list" id="arb-list-<?= $enc->id ?>">
                            <?php foreach ($enc->arbitrageRows as $arb): ?>
                            <div class="arb-item" data-arb-id="<?= $arb->id ?>">
                                <span class="arb-name"><?= esc($arb->last_name) ?> <?= esc(mb_substr($arb->first_name,0,1)) ?>.</span>
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
                                    <span class="badge badge-info">Vol.</span>
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
                        <!-- Bouton Désigner — toujours visible pour les domiciles -->
                        <button class="btn btn-xs btn-info btn-designate-referee"
                                data-encounter="<?= $enc->id ?>"
                                data-type="<?= $enc->encounter_type ?>"
                                data-rounds="<?= (int)($enc->rounds_count ?? 3) ?>">
                            <i class="fas fa-user-plus mr-1"></i>Désigner
                        </button>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-right text-nowrap">
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
