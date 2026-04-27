<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.badge-3b      { background:#17a2b8; color:#fff; }
.badge-plpf    { background:#28a745; color:#fff; }
.badge-3eme    { background:#6f42c1; color:#fff; }
.badge-coupe   { background:#fd7e14; color:#fff; }
.badge-comp    { background:#6c757d; color:#fff; }
.enc-home      { border-left:4px solid #28a745; background: #e8f5e9; }
.enc-away      { border-left:4px solid #c6000d; background: #f8d7da; }

.text-away     { color: #c6000d; }

.enc-row       { transition:background .15s; }
.enc-row:hover { background:#f8f9fa; }
.arb-name      { font-weight:600; }
.day-card      { margin-bottom:1.25rem; }
.time-badge    { font-size:.82rem; background:#e9ecef; color:#343a40; border-radius:4px; padding:2px 7px; white-space:nowrap; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$frDays   = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
$frMonths = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

function frDate(string $ymd, array $frDays, array $frMonths): string {
    $dt    = new \DateTime($ymd);
    $dow   = (int)$dt->format('N') - 1;
    $day   = (int)$dt->format('j');
    $month = (int)$dt->format('n') - 1;
    $year  = $dt->format('Y');
    return $frDays[$dow] . ' ' . $day . ' ' . $frMonths[$month] . ' ' . $year;
}

function compBadge(string $comp): string {
    $lc = strtolower($comp);
    if (str_contains($lc,'3 bandes') || $lc === '3b')  return 'badge-3b';
    if (str_contains($lc,'plpf'))                       return 'badge-plpf';
    if (str_contains($lc,'3ème') || str_contains($lc,'3eme')) return 'badge-3eme';
    if (str_contains($lc,'coupe'))                      return 'badge-coupe';
    return 'badge-comp';
}

// Période de la semaine
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
        <!-- Bar AM / Soir -->
        <div class="ml-auto d-flex align-items-center" style="gap:.5rem">
            <span class="text-muted" style="font-size:.75rem">Bar AM :</span>
            <?php if ($barAm): ?>
                <span class="badge badge-success"><?= esc($barAm->last_name) ?> <?= esc(mb_substr($barAm->first_name,0,1)) ?>.</span>
                <button class="btn btn-xs btn-outline-danger btn-bar-cancel" data-id="<?= $barAm->id ?>">
                    <i class="fas fa-times"></i>
                </button>
            <?php else: ?>
                <span class="text-muted badge badge-light">libre</span>
            <?php endif; ?>
            <span class="text-muted ml-2" style="font-size:.75rem">Bar soir :</span>
            <?php if ($barSoir): ?>
                <span class="badge badge-success"><?= esc($barSoir->last_name) ?> <?= esc(mb_substr($barSoir->first_name,0,1)) ?>.</span>
                <button class="btn btn-xs btn-outline-danger btn-bar-cancel" data-id="<?= $barSoir->id ?>">
                    <i class="fas fa-times"></i>
                </button>
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
                    <th style="width:60px"></th>
                    <th>Rencontre</th>
                    <th style="width:110px">Compétition</th>
                    <th style="width:200px">Arbitrage</th>
                    <th style="width:80px" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($dayEncounters as $enc): ?>
            <tr class="enc-row <?= $enc->is_home ? 'enc-home' : 'enc-away' ?>">
                <td class="align-middle">
                    <span class="time-badge"><?= esc(substr($enc->match_time, 0, 5)) ?></span>
                </td>
                <td class="align-middle text-center">
                    <?php if ($enc->is_home): ?>
                        <span title="À domicile"><i class="fas fa-home text-success"></i></span>
                    <?php else: ?>
                        <span title="En déplacement <?= esc($enc->venue ?? '') ?>">
                            <i class="fas fa-car-side text-away"></i>
                        </span>
                    <?php endif; ?>
                </td>
                <td class="align-middle">
                    <?php if ($enc->team_label): ?>
                        <strong><?= esc($enc->team_label) ?></strong> —
                    <?php endif; ?>
                    <?php if (!empty($enc->players)): ?>
                        <?php foreach ($enc->players as $i => $p): ?>
                            <?= $i > 0 ? '<br>' : '' ?>
                            <span><?= esc($p->last_name) ?> <?= esc(mb_substr($p->first_name,0,1)) ?>.</span>
                            <span class="text-muted mx-1">vs</span>
                            <span><?= esc($p->opponent_name) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                    <?php if (!$enc->is_home && $enc->venue): ?>
                        <br><small class="text-away"><i class="fas fa-map-marker-alt mr-1"></i><?= esc($enc->venue) ?></small>
                    <?php endif; ?>
                </td>
                <td class="align-middle">
                    <?php if ($enc->notes): ?>
                        <span style="font-size:.82rem;color:#495057;font-style:italic"><?= esc($enc->notes) ?></span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="align-middle" id="arb-cell-<?= $enc->id ?>">
                    <?php if ($enc->arbitrage): ?>
                        <span class="arb-name"><?= esc($enc->arbitrage->last_name) ?> <?= esc(mb_substr($enc->arbitrage->first_name,0,1)) ?>.</span>
                        <?php if ($enc->arbitrage->assignment_type === 'designated'): ?>
                            <?php if ($enc->arbitrage->confirmed): ?>
                                <span class="badge badge-success ml-1"><i class="fas fa-check"></i> Confirmé</span>
                            <?php else: ?>
                                <span class="badge badge-warning ml-1">En attente</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge badge-info ml-1">Volontaire</span>
                        <?php endif; ?>
                        <button class="btn btn-xs btn-outline-danger ml-1 btn-remove-referee"
                                data-encounter="<?= $enc->id ?>"
                                title="Retirer l'arbitre">
                            <i class="fas fa-times"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn btn-xs btn-outline-primary btn-designate-referee"
                                data-encounter="<?= $enc->id ?>">
                            <i class="fas fa-user-plus mr-1"></i>Désigner
                        </button>
                    <?php endif; ?>
                </td>
                <td class="align-middle text-right text-nowrap">
                    <a href="<?= base_url("admin/schedule/{$enc->id}/edit") ?>"
                       class="btn btn-xs btn-outline-secondary" title="Modifier">
                        <i class="fas fa-pencil-alt"></i>
                    </a>
                    <button class="btn btn-xs btn-outline-danger btn-delete-encounter ml-1"
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
                <div class="form-group mb-0">
                    <label>Membre</label>
                    <select class="form-control" id="modalAdminUserId">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($adminUsers as $u): ?>
                        <option value="<?= $u->id ?>"><?= esc($u->last_name) ?> <?= esc($u->first_name) ?></option>
                        <?php endforeach; ?>
                    </select>
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

// Désigner arbitre
document.querySelectorAll('.btn-designate-referee').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modalEncounterId').value = this.dataset.encounter;
        document.getElementById('modalAdminUserId').value = '';
        $('#modalArbitrage').modal('show');
    });
});

document.getElementById('btnConfirmArbitrage').addEventListener('click', function() {
    const encounterId  = document.getElementById('modalEncounterId').value;
    const adminUserId  = document.getElementById('modalAdminUserId').value;
    if (!adminUserId) { return; }

    fetch(`<?= base_url('admin/schedule/') ?>${encounterId}/referee`, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: `<?= csrf_token() ?>=${csrfToken}&admin_user_id=${adminUserId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            $('#modalArbitrage').modal('hide');
            const cell = document.getElementById(`arb-cell-${encounterId}`);
            cell.innerHTML = `
                <span class="arb-name">${data.name}</span>
                <span class="badge badge-warning ml-1">En attente</span>
                <button class="btn btn-xs btn-outline-danger ml-1 btn-remove-referee"
                        data-encounter="${encounterId}" title="Retirer l'arbitre">
                    <i class="fas fa-times"></i>
                </button>`;
            bindRemoveReferee(cell.querySelector('.btn-remove-referee'));
        }
    });
});

// Retirer arbitre
function bindRemoveReferee(btn) {
    btn.addEventListener('click', function() {
        const encId = this.dataset.encounter;
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
                body: `<?= csrf_token() ?>=${csrfToken}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const cell = document.getElementById(`arb-cell-${encId}`);
                    cell.innerHTML = `
                        <button class="btn btn-xs btn-outline-primary btn-designate-referee" data-encounter="${encId}">
                            <i class="fas fa-user-plus mr-1"></i>Désigner
                        </button>`;
                    cell.querySelector('.btn-designate-referee').addEventListener('click', function() {
                        document.getElementById('modalEncounterId').value = this.dataset.encounter;
                        document.getElementById('modalAdminUserId').value = '';
                        $('#modalArbitrage').modal('show');
                    });
                }
            });
        });
    });
}
document.querySelectorAll('.btn-remove-referee').forEach(bindRemoveReferee);

// Supprimer rencontre
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
