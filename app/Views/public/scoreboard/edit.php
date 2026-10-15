<?php
use App\Models\ScoreboardMatchModel as M;

$sets = M::isSets($match);
$trio = M::isTrio($match);
// Amicales : ni club ni catégorie à l'encodage, on garde tels quels ceux d'une ancienne partie
$friendly = $match->type === 'friendly';
$val  = static fn (string $f, $default = '') => old($f, $match->$f ?? $default);
?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name ?? '') ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title mb-2"><i class="fas fa-user-pen me-2 sb-accent"></i>Modifier la partie en cours</h1>
<p class="text-secondary mb-4">
    <?= esc(M::disciplineLabel($match)) ?><?= $sets ? ' · ' . esc(M::distanceLabel($match)) : '' ?> —
    les reprises déjà jouées sont conservées.
</p>
<form method="post" action="<?= base_url('marquoir/match/' . $match->id . '/modifier') ?>" autocomplete="off">
    <input type="hidden" id="discipline" value="<?= esc($match->discipline) ?>">
    <input type="hidden" id="format" value="<?= esc($match->format) ?>">
    <input type="hidden" name="players" value="<?= M::playerCount($match) ?>">
    <div class="row g-4">
        <?php foreach (M::playerNumbers($match) as $n): $p = 'p' . $n; ?>
        <div class="<?= $trio ? 'col-lg-4' : 'col-md-6' ?>">
            <div class="sb-card h-100">
                <?php if ($trio): ?>
                <h2 class="h5 mb-3">Joueur <?= $n ?> <small class="text-secondary">(joue en <?= $n === 1 ? '1er' : $n . 'e' ?>)</small></h2>
                <?php else: ?>
                <h2 class="h5 mb-3" style="color:var(--sb-<?= $p ?>)">Joueur <?= $n ?> <small class="text-secondary">(bille <?= $n === 1 ? 'blanche' : 'jaune' ?>)</small></h2>
                <?php endif; ?>
                <input type="hidden" name="<?= $p ?>_member_id" value="<?= esc($val($p . '_member_id')) ?>">
                <input type="hidden" name="<?= $p ?>_license" value="<?= esc($val($p . '_license')) ?>">
                <div class="mb-3 position-relative">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="<?= $p ?>_last_name" class="form-control form-control-lg" required
                           style="text-transform:uppercase" value="<?= esc($val($p . '_last_name')) ?>"
                           data-sb-ac="<?= $p ?>" data-sb-ac-url="<?= base_url('marquoir/joueurs') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="<?= $p ?>_first_name" class="form-control form-control-lg" required value="<?= esc($val($p . '_first_name')) ?>">
                </div>
                <div class="row g-3">
                    <?php if ($friendly): ?>
                    <input type="hidden" name="<?= $p ?>_club" value="<?= esc($val($p . '_club')) ?>">
                    <input type="hidden" name="<?= $p ?>_category_id" value="<?= esc($val($p . '_category_id')) ?>">
                    <?php else: ?>
                    <div class="col-8">
                        <label class="form-label">Club</label>
                        <input type="text" name="<?= $p ?>_club" class="form-control form-control-lg" value="<?= esc($val($p . '_club')) ?>">
                    </div>
                    <?php endif; ?>
                    <div class="<?= $friendly ? 'col-12' : 'col-4' ?>">
                        <label class="form-label"><?= $sets ? 'Points par set' : 'Points à réaliser' ?> <span class="text-danger">*</span></label>
                        <input type="number" name="<?= $p ?>_distance" min="<?= max(1, $scored[$n]) ?>" max="<?= $sets ? 999 : 9999 ?>" required class="form-control form-control-lg" value="<?= esc($val($p . '_distance')) ?>">
                        <small class="text-secondary">Déjà marqués<?= $sets ? ' (set en cours)' : '' ?> : <?= $scored[$n] ?></small>
                    </div>
                    <?php if (!$friendly): ?>
                    <div class="col-12">
                        <label class="form-label">Catégorie</label>
                        <select name="<?= $p ?>_category_id" class="form-select form-select-lg" data-sb-category="<?= $p ?>"
                                data-selected="<?= esc($val($p . '_category_id')) ?>"></select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="col-12">
            <div class="sb-card">
                <?php if ($sets): ?>
                <p class="text-secondary mb-0"><?= esc(M::distanceLabel($match)) ?> · reprises inégales, sans limite de reprises.</p>
                <?php else: ?>
                <div class="form-check form-switch fs-5">
                    <input class="form-check-input" type="checkbox" role="switch" name="innings_limit_enabled" id="innings_limit_enabled" value="1" <?= old('innings_limit_enabled', $match->innings_limit ? '1' : '') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="innings_limit_enabled">Limite de reprises</label>
                </div>
                <div class="input-group mt-2" data-sb-show-when="innings_limit_enabled" style="max-width:320px">
                    <input type="number" name="innings_limit" id="innings_limit" min="1" max="999" class="form-control" value="<?= esc($val('innings_limit', 60)) ?>">
                    <span class="input-group-text">reprises</span>
                </div>
                <?php endif; ?>
                <?php if ($match->type === 'official' && !M::isTrio($match)): ?>
                <div class="form-check form-switch fs-5 mt-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="signature_required" id="signature_required" value="1" <?= old('signature_required', $match->signature_required ? '1' : '') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="signature_required">Feuille à signer</label>
                </div>
                <div class="form-text">Coché : les joueurs et l'arbitre peuvent signer la feuille en fin de partie.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4 sb-sticky-actions">
        <a href="<?= base_url('marquoir/match/' . $match->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Retour au marquoir</a>
        <button class="btn btn-sb btn-lg"><i class="fas fa-check me-2"></i>Enregistrer</button>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>window.SB_CATEGORIES = <?= json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<?= $this->endSection() ?>
