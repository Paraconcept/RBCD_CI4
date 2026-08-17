<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.player-row { background:#f8f9fa; border-radius:4px; padding:.5rem; margin-bottom:.5rem; }
.btn-outline-rbcd { color:#84252B; border-color:#84252B; background:transparent; }
.btn-outline-rbcd:hover,
.btn-outline-rbcd.active { background:#84252B; border-color:#84252B; color:#fff; }
.btn-outline-purple { color:#6f42c1; border-color:#6f42c1; background:transparent; }
.btn-outline-purple:hover,
.btn-outline-purple.active { background:#6f42c1; border-color:#6f42c1; color:#fff; }
.btn-outline-navy { color:#0D47A1; border-color:#0D47A1; background:transparent; }
.btn-outline-navy:hover,
.btn-outline-navy.active { background:#0D47A1; border-color:#0D47A1; color:#fff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$isEdit        = $encounter !== null;
$source        = $encounter ?? $prefill ?? null;
$formUrl       = $isEdit
    ? base_url("admin/schedule/{$encounter->id}/update")
    : base_url('admin/schedule');
$isHome        = $source ? (int)$source->is_home : 1;
$encType       = $source ? ($source->encounter_type ?? 'normal') : 'normal';
$isFinale      = $encType === 'finale';
$isTeamType    = in_array($encType, ['cdr', 'intm'], true);
$requiresArb   = $source ? (int)($source->requires_arbitrage ?? 1) : 1;
$requiresMrq   = $source ? (int)($source->requires_marquage  ?? 0) : 0;
$teamHome      = $isTeamType && !empty($existingPlayers) ? ($existingPlayers[0]->player_home_name ?? '') : '';
$teamAway      = $isTeamType && !empty($existingPlayers) ? ($existingPlayers[0]->opponent_name ?? '') : '';
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas <?= $isEdit ? 'fa-edit' : (isset($prefill) ? 'fa-copy' : 'fa-plus') ?> mr-2"></i>
            <?= esc($title) ?>
        </h3>
    </div>

    <form method="POST" action="<?= $formUrl ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="encounter_type" id="encounterTypeInput" value="<?= $encType ?>">

        <div class="card-body">

            <?php if ($errors = session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0 pl-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= esc($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Toggle Normal / Finale -->
            <div class="form-group">
                <label class="d-block">Type de rencontre</label>
                <div class="d-flex align-items-center flex-wrap" style="gap:1rem">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons" id="encounterTypeToggle">
                        <label class="btn btn-outline-rbcd btn-sm <?= $encType === 'normal' ? 'active' : '' ?>">
                            <input type="radio" name="_enc_type" value="normal" <?= $encType === 'normal' ? 'checked' : '' ?>>
                            <i class="fas fa-people-arrows mr-1"></i> Match normal
                        </label>
                        <label class="btn btn-outline-warning btn-sm <?= $isFinale ? 'active' : '' ?>">
                            <input type="radio" name="_enc_type" value="finale" <?= $isFinale ? 'checked' : '' ?>>
                            <i class="fas fa-trophy mr-1"></i> Finales / Tournoi / Open
                        </label>
                        <label class="btn btn-outline-purple btn-sm <?= $encType === 'cdr' ? 'active' : '' ?>">
                            <input type="radio" name="_enc_type" value="cdr" <?= $encType === 'cdr' ? 'checked' : '' ?>>
                            <i class="fas fa-users mr-1"></i> CDR
                        </label>
                        <label class="btn btn-outline-navy btn-sm <?= $encType === 'intm' ? 'active' : '' ?>">
                            <input type="radio" name="_enc_type" value="intm" <?= $encType === 'intm' ? 'checked' : '' ?>>
                            <i class="fas fa-users mr-1"></i> INTM
                        </label>
                    </div>
                </div>
                <small class="text-muted d-block mt-1" id="finaleNote" <?= !$isFinale ? 'style="display:none!important"' : '' ?>>
                    En mode Finales/Tournoi/Open, les joueurs ne sont pas liés à la base membres — aucun impact sur les stats d'arbitrage.
                </small>
                <small class="text-muted d-block mt-1" id="teamTypeNote" <?= !$isTeamType ? 'style="display:none!important"' : '' ?>>
                    En mode CDR/INTM, la rencontre est encodée par équipes (nom libre), sans liste de joueurs.
                </small>
            </div>

            <div class="row">
                <!-- Date + Heure -->
                <div class="col-md-3 form-group">
                    <label>Date <span class="text-danger">*</span></label>
                    <input type="date" name="match_date" class="form-control"
                           value="<?= esc(old('match_date', $isEdit ? $encounter->match_date : '')) ?>" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Heure <span class="text-danger">*</span></label>
                    <input type="time" name="match_time" class="form-control"
                           value="<?= esc(old('match_time', $source ? substr($source->match_time, 0, 5) : '19:30')) ?>" required>
                </div>

                <!-- Domicile / Déplacement -->
                <div class="col-md-3 form-group">
                    <label>Lieu</label>
                    <div class="btn-group btn-group-toggle d-block" data-toggle="buttons">
                        <label class="btn btn-outline-success btn-sm <?= $isHome ? 'active' : '' ?>">
                            <input type="radio" name="is_home" value="1" <?= $isHome ? 'checked' : '' ?>>
                            <i class="fas fa-home mr-1"></i> Domicile
                        </label>
                        <label class="btn btn-outline-danger btn-sm <?= !$isHome ? 'active' : '' ?>">
                            <input type="radio" name="is_home" value="0" <?= !$isHome ? 'checked' : '' ?>>
                            <i class="fas fa-car-side mr-1"></i> Déplacement
                        </label>
                    </div>
                </div>

                <!-- Lieu si déplacement -->
                <div class="col-md-4 form-group" id="venueGroup" <?= $isHome ? 'style="display:none"' : '' ?>>
                    <label>Lieu de déplacement</label>
                    <input type="text" name="venue" class="form-control"
                           placeholder="ex: BC Herstalien"
                           value="<?= esc(old('venue', $source ? $source->venue ?? '' : '')) ?>">
                </div>
            </div>

            <div class="row">
                <!-- Compétition -->
                <div class="col-md-5 form-group">
                    <label>Compétition</label>
                    <input type="text" name="competition" class="form-control"
                           placeholder="ex: Championnat Régional 3° 3B PF"
                           value="<?= esc(old('competition', $source ? $source->competition ?? '' : '')) ?>">

                    <!-- Nombre de tours — visible uniquement en mode Finale -->
                    <div class="col-md-12 form-group" id="roundsCountGroup" <?= !$isFinale ? 'style="display:none"' : '' ?>>
                        <label>Nombre de tours</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            </div>
                            <input type="number" name="rounds_count" class="form-control"
                                min="1" max="8" placeholder="Tours"
                                value="<?= esc(old('rounds_count', $source ? ($source->rounds_count ?? 3) : 3)) ?>"
                                title="Nombre de tours de la compétition">
                            <div class="input-group-append">
                                <span class="input-group-text">tour(s) de jeux</span>
                            </div>
                        </div>
                    </div>

                    <!-- Arbitrages / Marquage requis — visible uniquement en mode Finale -->
                    <div class="col-md-12 form-group mb-0" id="requiresArbGroup" <?= !$isFinale ? 'style="display:none"' : '' ?>>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="requires_arbitrage" id="requires_arbitrage"
                                   class="custom-control-input" value="1"
                                   <?= $requiresArb ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="requires_arbitrage">
                                Arbitrages requis
                            </label>
                        </div>
                        <small class="text-muted">Décocher si les arbitres sont fournis par la fédération ou auto-arbitrage.</small>
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" name="requires_marquage" id="requires_marquage"
                                   class="custom-control-input" value="1"
                                   <?= $requiresMrq ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="requires_marquage">
                                Marquage requis
                            </label>
                        </div>
                        <small class="text-muted">Décocher si des marqueurs ne sont pas nécessaires.</small>
                    </div>

                </div>



                <!-- Notes internes -->
                <div class="col-md-4 form-group">
                    <label>Notes internes</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Remarques diverses"><?= esc(old('notes', $source ? $source->notes ?? '' : '')) ?></textarea>
                </div>
            </div>

            <hr>

            <!-- Équipes (CDR / INTM) -->
            <div id="teamsSection" <?= !$isTeamType ? 'style="display:none"' : '' ?>>
                <h6 class="font-weight-bold mb-3"><i class="fas fa-users mr-1"></i> Équipes</h6>
                <div class="form-row align-items-center" id="teamsRow">
                    <div class="col-md-5 form-group" id="teamHomeCol">
                        <label>Équipe RBCD</label>
                        <input type="text" name="team_home" class="form-control"
                               placeholder="ex: DISON 3" value="<?= esc(old('team_home', $teamHome)) ?>">
                    </div>
                    <div class="col-md-2 text-center text-muted d-none d-md-block" style="margin-top:1.9rem;order:2">vs</div>
                    <div class="col-md-5 form-group" id="teamAwayCol">
                        <label>Équipe adverse</label>
                        <input type="text" name="team_away" class="form-control"
                               placeholder="ex: RWBC 1" value="<?= esc(old('team_away', $teamAway)) ?>">
                    </div>
                </div>
                <small class="text-muted d-block mt-1">
                    L'équipe RBCD est toujours affichée en gras dans le tableau. Sa position (gauche/droite)
                    suit le lieu choisi ci-dessus — Domicile ou Déplacement — comme dans le tableau des rencontres.
                </small>
            </div>

            <!-- Joueurs -->
            <div id="playersSection" <?= $isTeamType ? 'style="display:none"' : '' ?>>
            <h6 class="font-weight-bold mb-3"><i class="fas fa-users mr-1"></i> Joueurs</h6>
            <div id="playersContainer">
                <?php if (!empty($existingPlayers) && !$isTeamType): ?>
                    <?php foreach ($existingPlayers as $p): ?>
                    <?php if ($isFinale): ?>
                    <div class="player-row d-flex align-items-center" style="gap:.5rem">
                        <div style="flex:1">
                            <input type="text" name="player_home_name[]" class="form-control form-control-sm"
                                   placeholder="Joueur domicile" list="dlHomePlayers"
                                   value="<?= esc($p->player_home_name ?? '') ?>">
                        </div>
                        <span class="text-muted">vs</span>
                        <div style="flex:1">
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm"
                                   placeholder="Joueur adverse" list="dlOpponents"
                                   value="<?= esc($p->opponent_name) ?>">
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="player-row d-flex align-items-center" style="gap:.5rem">
                        <div style="flex:1">
                            <select name="member_id[]" class="form-control form-control-sm">
                                <option value="">— Joueur RBCD —</option>
                                <?php foreach ($members as $m): ?>
                                <option value="<?= $m->id ?>" <?= ($p->member_id ?? 0) == $m->id ? 'selected' : '' ?>>
                                    <?= esc($m->last_name) ?> <?= esc($m->first_name) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="text-muted">vs</span>
                        <div style="flex:1">
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm"
                                   placeholder="Nom de l'adversaire" list="dlOpponents"
                                   value="<?= esc($p->opponent_name) ?>">
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Ligne vide par défaut — le JS gère le bon template -->
                    <div class="player-row d-flex align-items-center" style="gap:.5rem" data-row-type="<?= $encType ?>">
                        <?php if ($isFinale): ?>
                        <div style="flex:1">
                            <input type="text" name="player_home_name[]" class="form-control form-control-sm" placeholder="Joueur domicile" list="dlHomePlayers">
                        </div>
                        <span class="text-muted">vs</span>
                        <div style="flex:1">
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Joueur adverse">
                        </div>
                        <?php else: ?>
                        <div style="flex:1">
                            <select name="member_id[]" class="form-control form-control-sm">
                                <option value="">— Joueur RBCD —</option>
                                <?php foreach ($members as $m): ?>
                                <option value="<?= $m->id ?>"><?= esc($m->last_name) ?> <?= esc($m->first_name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <span class="text-muted">vs</span>
                        <div style="flex:1">
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Nom de l'adversaire" list="dlOpponents">
                        </div>
                        <?php endif; ?>
                        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" id="btnAddPlayer" class="btn btn-sm btn-outline-secondary mt-2">
                <i class="fas fa-plus mr-1"></i> Ajouter un match
            </button>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la rencontre' ?>
            </button>
            <a href="<?= base_url('admin/schedule') ?>" class="btn btn-secondary ml-2">Annuler</a>
        </div>
    </form>
</div>

<!-- Datalists autocomplétion -->
<datalist id="dlOpponents">
    <?php foreach ($acOpponents as $v): ?>
    <option value="<?= esc($v) ?>">
    <?php endforeach; ?>
</datalist>
<datalist id="dlHomePlayers">
    <?php foreach ($acHomePlayers as $v): ?>
    <option value="<?= esc($v) ?>">
    <?php endforeach; ?>
</datalist>

<!-- Templates lignes joueurs -->
<template id="playerRowNormal">
    <div class="player-row d-flex align-items-center" style="gap:.5rem">
        <div style="flex:1">
            <select name="member_id[]" class="form-control form-control-sm">
                <option value="">— Joueur RBCD —</option>
                <?php foreach ($members as $m): ?>
                <option value="<?= $m->id ?>"><?= esc($m->last_name) ?> <?= esc($m->first_name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <span class="text-muted">vs</span>
        <div style="flex:1">
            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Nom de l'adversaire" list="dlOpponents">
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>

<template id="playerRowFinale">
    <div class="player-row d-flex align-items-center" style="gap:.5rem">
        <div style="flex:1">
            <input type="text" name="player_home_name[]" class="form-control form-control-sm" placeholder="Joueur domicile" list="dlHomePlayers">
        </div>
        <span class="text-muted">vs</span>
        <div style="flex:1">
            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Joueur adverse" list="dlOpponents">
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// ── Toggle venue ──
document.querySelectorAll('input[name="is_home"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('venueGroup').style.display = this.value === '0' ? '' : 'none';
        updateTeamOrder();
    });
});

// ── Équipes CDR/INTM : l'équipe RBCD suit le lieu (gauche = domicile, droite = déplacement) ──
function updateTeamOrder() {
    const isHomeRadio = document.querySelector('input[name="is_home"]:checked');
    const isHome = !isHomeRadio || isHomeRadio.value === '1';
    document.getElementById('teamHomeCol').style.order = isHome ? '1' : '3';
    document.getElementById('teamAwayCol').style.order = isHome ? '3' : '1';
}
updateTeamOrder();

// ── Toggle Normal / Finale ──
let currentType = '<?= $encType ?>';

document.querySelectorAll('input[name="_enc_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        currentType = this.value;
        document.getElementById('encounterTypeInput').value = currentType;

        const isFinale   = currentType === 'finale';
        const isTeamType = currentType === 'cdr' || currentType === 'intm';

        document.getElementById('finaleNote').style.display    = isFinale ? '' : 'none';
        document.getElementById('teamTypeNote').style.display   = isTeamType ? '' : 'none';
        document.getElementById('roundsCountGroup').style.display = isFinale ? '' : 'none';
        document.getElementById('requiresArbGroup').style.display = isFinale ? '' : 'none';
        if (!isFinale) {
            document.getElementById('requires_arbitrage').checked = true;
            document.getElementById('requires_marquage').checked  = false;
        }

        document.getElementById('teamsSection').style.display   = isTeamType ? '' : 'none';
        document.getElementById('playersSection').style.display = isTeamType ? 'none' : '';

        if (!isTeamType) {
            // Reconstruire toutes les lignes existantes dans le bon mode
            const container = document.getElementById('playersContainer');
            const count = container.querySelectorAll('.player-row').length || 1;
            container.innerHTML = '';
            for (let i = 0; i < count; i++) {
                addPlayerRow();
            }
        }
    });
});

// ── Ajouter une ligne joueur ──
function addPlayerRow() {
    const tmplId = currentType === 'finale' ? 'playerRowFinale' : 'playerRowNormal';
    const tmpl   = document.getElementById(tmplId);
    const clone  = tmpl.content.cloneNode(true);
    document.getElementById('playersContainer').appendChild(clone);
}

document.getElementById('btnAddPlayer').addEventListener('click', addPlayerRow);

// ── Supprimer une ligne joueur (délégué) ──
document.getElementById('playersContainer').addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-remove-player');
    if (btn) {
        const rows = this.querySelectorAll('.player-row');
        if (rows.length > 1) {
            btn.closest('.player-row').remove();
        }
    }
});
</script>
<?= $this->endSection() ?>
