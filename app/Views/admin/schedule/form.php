<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('styles') ?>
<style>
.player-row { background:#f8f9fa; border-radius:4px; padding:.5rem; margin-bottom:.5rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$isEdit  = $encounter !== null;
$formUrl = $isEdit
    ? base_url("admin/schedule/{$encounter->id}/update")
    : base_url('admin/schedule');
$isHome  = $isEdit ? (int)$encounter->is_home : 1;
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
                        <label class="btn btn-outline-warning btn-sm <?= !$isHome ? 'active' : '' ?>">
                            <input type="radio" name="is_home" value="0" <?= !$isHome ? 'checked' : '' ?>>
                            <i class="fas fa-bus mr-1"></i> Déplacement
                        </label>
                    </div>
                </div>

                <!-- Lieu si déplacement -->
                <div class="col-md-4 form-group" id="venueGroup" <?= $isHome ? 'style="display:none"' : '' ?>>
                    <label>Lieu de déplacement</label>
                    <input type="text" name="venue" class="form-control"
                           placeholder="ex: Salle de Verviers"
                           value="<?= esc(old('venue', $isEdit ? $encounter->venue ?? '' : '')) ?>">
                </div>
            </div>

            <div class="row">
                <!-- Compétition -->
                <div class="col-md-4 form-group">
                    <label>Compétition <span class="text-danger">*</span></label>
                    <?php
                    $competitions = ['3 Bandes', 'PLPF', '3ème Bande', 'Coupe FRBB', 'Coupe RBCD', 'Championnat'];
                    $currentComp = old('competition', $isEdit ? $encounter->competition : '');
                    $isCustom = $currentComp && !in_array($currentComp, $competitions);
                    ?>
                    <select name="competition" class="form-control" id="competitionSelect">
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($competitions as $c): ?>
                            <option value="<?= $c ?>" <?= $currentComp === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                        <option value="__other__" <?= $isCustom ? 'selected' : '' ?>>Autre…</option>
                    </select>
                    <input type="text" name="competition_custom" id="competitionCustom" class="form-control mt-1"
                           placeholder="Nom de la compétition"
                           value="<?= $isCustom ? esc($currentComp) : '' ?>"
                           style="<?= $isCustom ? '' : 'display:none' ?>">
                </div>

                <!-- Équipe RBCD -->
                <div class="col-md-3 form-group">
                    <label>Équipe RBCD</label>
                    <input type="text" name="team_label" class="form-control"
                           placeholder="ex: RBCD 1, RBCD 2"
                           value="<?= esc(old('team_label', $isEdit ? $encounter->team_label ?? '' : '')) ?>">
                </div>

                <!-- Notes -->
                <div class="col-md-5 form-group">
                    <label>Notes</label>
                    <input type="text" name="notes" class="form-control"
                           placeholder="Remarques éventuelles"
                           value="<?= esc(old('notes', $isEdit ? $encounter->notes ?? '' : '')) ?>">
                </div>
            </div>

            <hr>

            <!-- Joueurs -->
            <h6 class="font-weight-bold mb-3"><i class="fas fa-users mr-1"></i> Joueurs</h6>
            <div id="playersContainer">
                <?php if (!empty($existingPlayers)): ?>
                    <?php foreach ($existingPlayers as $p): ?>
                    <div class="player-row d-flex align-items-center" style="gap:.5rem">
                        <div style="flex:1">
                            <select name="member_id[]" class="form-control form-control-sm">
                                <option value="">— Joueur RBCD —</option>
                                <?php foreach ($members as $m): ?>
                                <option value="<?= $m->id ?>" <?= $p->member_id == $m->id ? 'selected' : '' ?>>
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
                    <?php endforeach; ?>
                <?php else: ?>
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
                            <input type="text" name="opponent_name[]" class="form-control form-control-sm"
                                   placeholder="Nom de l'adversaire">
                        </div>
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

<!-- Template ligne joueur (hidden) -->
<template id="playerRowTemplate">
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Toggle venue
document.querySelectorAll('input[name="is_home"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('venueGroup').style.display = this.value === '0' ? '' : 'none';
    });
});

// Custom competition
document.getElementById('competitionSelect').addEventListener('change', function() {
    const customInput = document.getElementById('competitionCustom');
    if (this.value === '__other__') {
        customInput.style.display = '';
        customInput.name = 'competition';
        this.name = '';
    } else {
        customInput.style.display = 'none';
        customInput.name = 'competition_custom';
        this.name = 'competition';
    }
});

// Add player row
document.getElementById('btnAddPlayer').addEventListener('click', function() {
    const tmpl    = document.getElementById('playerRowTemplate');
    const clone   = tmpl.content.cloneNode(true);
    document.getElementById('playersContainer').appendChild(clone);
});

// Remove player row (delegated)
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
