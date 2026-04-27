<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.player-row { background:#f8f9fa; border-radius:4px; padding:.5rem; margin-bottom:.5rem; }
.btn-outline-rbcd { color:#84252B; border-color:#84252B; background:transparent; }
.btn-outline-rbcd:hover,
.btn-outline-rbcd.active { background:#84252B; border-color:#84252B; color:#fff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$isEdit        = $encounter !== null;
$formUrl       = $isEdit
    ? base_url("admin/schedule/{$encounter->id}/update")
    : base_url('admin/schedule');
$isHome        = $isEdit ? (int)$encounter->is_home : 1;
$encType       = $isEdit ? ($encounter->encounter_type ?? 'normal') : 'normal';
$isFinale      = $encType === 'finale';
?>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas <?= $isEdit ? 'fa-pencil-alt' : 'fa-plus' ?> mr-2"></i>
            <?= $isEdit ? 'Modifier la rencontre' : 'Nouvelle rencontre' ?>
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
                <div class="btn-group btn-group-toggle" data-toggle="buttons" id="encounterTypeToggle">
                    <label class="btn btn-outline-rbcd btn-sm <?= !$isFinale ? 'active' : '' ?>">
                        <input type="radio" name="_enc_type" value="normal" <?= !$isFinale ? 'checked' : '' ?>>
                        <i class="fas fa-people-arrows mr-1"></i> Match normal
                    </label>
                    <label class="btn btn-outline-warning btn-sm <?= $isFinale ? 'active' : '' ?>">
                        <input type="radio" name="_enc_type" value="finale" <?= $isFinale ? 'checked' : '' ?>>
                        <i class="fas fa-trophy mr-1"></i> Finale / Tournoi
                    </label>
                </div>
                <small class="text-muted d-block mt-1" id="finaleNote" <?= !$isFinale ? 'style="display:none!important"' : '' ?>>
                    En mode Finale, les joueurs ne sont pas liés à la base membres — aucun impact sur les stats d'arbitrage.
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
                           value="<?= esc(old('match_time', $isEdit ? substr($encounter->match_time, 0, 5) : '19:30')) ?>" required>
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
                           value="<?= esc(old('venue', $isEdit ? $encounter->venue ?? '' : '')) ?>">
                </div>
            </div>

            <div class="row">
                <!-- Compétition -->
                <div class="col-md-5 form-group">
                    <label>Compétition</label>
                    <input type="text" name="competition" class="form-control"
                           placeholder="ex: Championnat Régional 3° 3B PF"
                           value="<?= esc(old('competition', $isEdit ? $encounter->competition ?? '' : '')) ?>">
                </div>

                <!-- Notes internes -->
                <div class="col-md-4 form-group">
                    <label>Notes internes</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Remarques diverses"><?= esc(old('notes', $isEdit ? $encounter->notes ?? '' : '')) ?></textarea>
                </div>
            </div>

            <hr>

            <!-- Joueurs -->
            <h6 class="font-weight-bold mb-3"><i class="fas fa-users mr-1"></i> Joueurs</h6>
            <div id="playersContainer">
                <?php if (!empty($existingPlayers)): ?>
                    <?php foreach ($existingPlayers as $p): ?>
                    <?php if ($isFinale): ?>
                    <div class="player-row d-flex align-items-center" style="gap:.5rem">
                        <div style="flex:1">
                            <input type="text" name="player_home_name[]" class="form-control form-control-sm"
                                   placeholder="Joueur domicile"
                                   value="<?= esc($p->player_home_name ?? '') ?>">
                        </div>
                        <span class="text-muted">vs</span>
                        <div style="flex:1">
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm"
                                   placeholder="Joueur adverse"
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
                                   placeholder="Nom de l'adversaire"
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
                            <input type="text" name="player_home_name[]" class="form-control form-control-sm" placeholder="Joueur domicile">
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
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Nom de l'adversaire">
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

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la rencontre' ?>
            </button>
            <a href="<?= base_url('admin/schedule') ?>" class="btn btn-secondary ml-2">Annuler</a>
        </div>
    </form>
</div>

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
            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Nom de l'adversaire">
        </div>
        <button type="button" class="btn btn-xs btn-outline-danger btn-remove-player">
            <i class="fas fa-times"></i>
        </button>
    </div>
</template>

<template id="playerRowFinale">
    <div class="player-row d-flex align-items-center" style="gap:.5rem">
        <div style="flex:1">
            <input type="text" name="player_home_name[]" class="form-control form-control-sm" placeholder="Joueur domicile">
        </div>
        <span class="text-muted">vs</span>
        <div style="flex:1">
            <input type="text" name="opponent_name[]" class="form-control form-control-sm" placeholder="Joueur adverse">
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
    });
});

// ── Toggle Normal / Finale ──
let currentType = '<?= $encType ?>';

document.querySelectorAll('input[name="_enc_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        currentType = this.value;
        document.getElementById('encounterTypeInput').value = currentType;

        const note = document.getElementById('finaleNote');
        note.style.display = currentType === 'finale' ? '' : 'none';

        // Reconstruire toutes les lignes existantes dans le bon mode
        const container = document.getElementById('playersContainer');
        const count = container.querySelectorAll('.player-row').length || 1;
        container.innerHTML = '';
        for (let i = 0; i < count; i++) {
            addPlayerRow();
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
