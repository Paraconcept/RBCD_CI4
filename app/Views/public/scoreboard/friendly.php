<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?php $trio = $players === 3; ?>
<h1 class="sb-title mb-4"><i class="fas fa-<?= $trio ? 'users' : 'handshake' ?> me-2 sb-accent"></i><?= $trio ? 'Partie amicale à 3' : 'Partie amicale' ?></h1>
<form method="post" action="<?= base_url('marquoir/billard/' . $table->id . ($trio ? '/amicale-a-3' : '/amicale')) ?>" autocomplete="off">
    <input type="hidden" name="players" value="<?= $players ?>">
    <div class="row g-4">
        <?php for ($n = 1; $n <= $players; $n++): $p = 'p' . $n; ?>
        <div class="<?= $trio ? 'col-lg-4' : 'col-md-6' ?>">
            <div class="sb-card h-100">
                <h2 class="h5 mb-3">Joueur <?= $n ?></h2>
                <input type="hidden" name="<?= $p ?>_member_id" value="<?= esc(old($p . '_member_id')) ?>">
                <div class="mb-3 position-relative">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="<?= $p ?>_last_name" class="form-control form-control-lg" required
                           style="text-transform:uppercase" value="<?= esc(old($p . '_last_name')) ?>"
                           data-sb-ac="<?= $p ?>" data-sb-ac-url="<?= base_url('marquoir/joueurs') ?>"
                           placeholder="Tapez 2 lettres pour rechercher…">
                </div>
                <div class="row g-3">
                    <div class="col-8">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="<?= $p ?>_first_name" class="form-control form-control-lg" required value="<?= esc(old($p . '_first_name')) ?>">
                    </div>
                    <div class="col-4" data-sb-distance>
                        <label class="form-label"><span data-sb-distance-label>Points</span> <span class="text-danger">*</span></label>
                        <input type="number" name="<?= $p ?>_distance" min="1" max="9999" required class="form-control form-control-lg" value="<?= esc(old($p . '_distance')) ?>"
                               data-sb-set-default="<?= (int) $defaults['set_points'] ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php endfor; ?>

        <div class="col-12">
            <div class="sb-card">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="discipline">Discipline</label>
                        <select name="discipline" id="discipline" class="form-select form-select-lg" data-default-limit-3c="<?= (int) $defaults['innings_limit_3c'] ?>">
                            <?php foreach (M::DISCIPLINES as $k => $v): ?>
                            <option value="<?= $k ?>" <?= old('discipline', 'libre') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="format">Format</label>
                        <select name="format" id="format" class="form-select form-select-lg">
                            <?php foreach (M::FORMATS as $k => $v): ?>
                            <option value="<?= $k ?>" <?= old('format', $table->format ?? 'PB') === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php if (!$trio): ?>
                <div class="row g-3 mt-1" data-sb-sets-block>
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="sets_enabled" id="sets_enabled" value="1" <?= old('sets_enabled') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sets_enabled">Par sets</label>
                        </div>
                        <small class="text-secondary">Toujours à reprises inégales. Le joueur qui commence le 1<sup>er</sup> set garde la bille blanche ; les joueurs commencent les sets à tour de rôle.</small>
                    </div>
                    <div class="col-md-4" data-sb-show-when="sets_enabled">
                        <label class="form-label" for="sets_to_win">Sets gagnants</label>
                        <div class="input-group">
                            <input type="number" name="sets_to_win" id="sets_to_win" min="1" max="9" class="form-control form-control-lg" value="<?= esc(old('sets_to_win', $defaults['sets_to_win'])) ?>">
                            <span class="input-group-text">sets</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="innings_limit_enabled" id="innings_limit_enabled" value="1" <?= old('innings_limit_enabled') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="innings_limit_enabled">Limite de reprises</label>
                        </div>
                        <div class="input-group mt-2" data-sb-show-when="innings_limit_enabled">
                            <input type="number" name="innings_limit" id="innings_limit" min="1" max="999" class="form-control" value="<?= esc(old('innings_limit', $defaults['innings_limit_3c'])) ?>">
                            <span class="input-group-text">reprises</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="unequal_innings" id="unequal_innings" value="1" <?= old('unequal_innings') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="unequal_innings">Reprises inégales</label>
                        </div>
                        <small class="text-secondary"><?= $trio ? 'La partie s\'arrête dès qu\'un joueur atteint ses points, sans finir le tour.' : 'Pas de reprise d\'égalisation pour le second joueur.' ?></small>
                    </div>
                    <?php if (!$trio): ?>
                    <div class="col-md-4">
                        <div class="form-check form-switch fs-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="shot_clock_enabled" id="shot_clock_enabled" value="1" <?= old('shot_clock_enabled') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="shot_clock_enabled">Chrono par coup</label>
                        </div>
                        <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled">
                            <input type="number" name="shot_clock_seconds" min="5" max="300" class="form-control" value="<?= esc(old('shot_clock_seconds', $defaults['shot_clock_seconds'])) ?>">
                            <span class="input-group-text">secondes</span>
                        </div>
                        <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled">
                            <input type="number" name="shot_clock_extensions" min="0" max="10" class="form-control" value="<?= esc(old('shot_clock_extensions', $defaults['shot_clock_extensions'])) ?>">
                            <span class="input-group-text" data-sb-timeouts-label>time-outs / joueur</span>
                        </div>
                        <div class="mt-3" data-sb-show-when="shot_clock_enabled">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" name="shot_clock_auto_start" id="shot_clock_auto_start" value="1" <?= old('shot_clock_auto_start') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="shot_clock_auto_start">Démarrage automatique</label>
                            </div>
                            <small class="text-secondary">Le chrono repart dès l'appui sur <kbd class="mx-1">Entrée</kbd>.<br>Sinon, le lancer avec <kbd class="mx-1">*</kbd> à chaque reprise.</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between mt-4 sb-sticky-actions">
        <a href="<?= base_url('marquoir/billard/' . $table->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Retour</a>
        <button class="btn btn-sb btn-lg"><i class="fas fa-check me-2"></i>Continuer</button>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<?php
$tableFormat = $table->format ?? 'PB';
$tableLabel  = preg_match('/^billard\s+(\S+)$/iu', trim($table->name), $mm) ? 'Le billard numéro ' . $mm[1] : 'Le billard « ' . $table->name . ' »';
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
<script>
// Le format est celui du billard : on le rappelle gentiment si quelqu'un le change
(function () {
    const format = document.getElementById('format');
    const fixed  = <?= json_encode($tableFormat) ?>;
    const text   = <?= json_encode($tableLabel . ' est un ' . mb_strtolower(M::FORMATS[$tableFormat] ?? 'petit billard') . '.', JSON_HEX_TAG | JSON_UNESCAPED_UNICODE) ?>;
    if (!format || !window.Swal) return;
    format.addEventListener('change', function () {
        if (format.value === fixed) return;
        Swal.fire({
            title: text,
            icon: 'info',
            confirmButtonText: 'OK',
            background: '#16213d',
            color: '#eef1f7',
            iconColor: '#38bdf8',
            confirmButtonColor: '#84252B',
            customClass: { popup: 'sbd-swal' },
            showClass: { popup: 'sb-bounce' },
        }).then(function () {
            format.value = fixed;
            format.dispatchEvent(new Event('change'));
        });
    });
})();
</script>
<?= $this->endSection() ?>
