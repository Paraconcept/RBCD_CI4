<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<?php if ($table): ?><span class="sb-topbar-title"><?= esc($table->name) ?></span><?php endif; ?>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1">Signer la feuille de match</h1>
<p class="text-center text-secondary mb-3">
    <?= esc(M::disciplineLabel($match)) ?><?php if ($competition = M::competitionLabel($match)): ?> · <?= esc($competition) ?><?php endif; ?>
    · une fois enregistrée, la feuille est définitive
</p>

<div class="sb-card mb-3 py-2">
    <table class="table table-sm table-borderless mb-0 sb-sign-summary">
        <thead><tr><th></th><th>Points</th><th>Reprises</th><th>Moyenne</th><th>Série max</th></tr></thead>
        <tbody>
            <?php foreach (['p1', 'p2'] as $p): ?>
            <tr class="<?= $p ?>">
                <td><?= esc(M::playerLabel($match, $p)) ?><?= (int) $match->winner === (int) substr($p, 1) ? ' <i class="fas fa-trophy ms-1"></i>' : '' ?></td>
                <td><?= (int) $match->{$p . '_points'} ?> / <?= (int) $match->{$p . '_distance'} ?></td>
                <td><?= (int) $match->{$p . '_innings'} ?></td>
                <td><?= M::average((int) $match->{$p . '_points'}, (int) $match->{$p . '_innings'}) ?></td>
                <td><?= (int) $match->{$p . '_high_run'} ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $roles = [
    'p1'      => ['label' => 'Joueur A', 'name' => M::playerLabel($match, 'p1')],
    'p2'      => ['label' => 'Joueur B', 'name' => M::playerLabel($match, 'p2')],
    'referee' => ['label' => 'Arbitre', 'name' => ''],
]; ?>
<form method="post" id="signForm" action="<?= base_url('marquoir/match/' . $match->id . '/signer') ?>">
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="sb-sign-label">
                <span>Signature : <b id="signWho"></b></span>
                <button type="button" class="btn btn-sm btn-outline-light" id="signClear"><i class="fas fa-eraser me-1"></i>Effacer</button>
            </div>
            <canvas class="sb-sign-pad" id="signPad"></canvas>
            <?php foreach ($roles as $role => $r): ?>
            <input type="hidden" name="<?= $role ?>_signature">
            <?php endforeach; ?>
        </div>
        <div class="col-lg-4 d-flex flex-column gap-2">
            <?php foreach ($roles as $role => $r): ?>
            <button type="button" class="btn sb-sign-role <?= $role ?>" data-role="<?= $role ?>" data-who="<?= esc($r['label'] . ($r['name'] !== '' ? ' — ' . $r['name'] : '')) ?>">
                <i class="far fa-circle sb-sign-check"></i>
                <span><?= $r['label'] ?><?php if ($r['name'] !== ''): ?><small><?= esc($r['name']) ?></small><?php endif; ?></span>
            </button>
            <?php endforeach; ?>
            <label for="refereeName" class="form-label mb-0 mt-1">Nom de l'arbitre <span class="text-danger" title="Obligatoire">*</span></label>
            <input type="text" class="form-control form-control-lg" id="refereeName" name="referee_name" maxlength="100" autocomplete="off" required>
            <div class="mt-auto pt-2 d-flex gap-2">
                <a href="<?= base_url('marquoir/match/' . $match->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left"></i></a>
                <button type="submit" class="btn btn-sb btn-lg flex-fill" id="signSubmit" disabled><i class="fas fa-signature me-2"></i>Enregistrer</button>
            </div>
            <div class="text-secondary small">Les trois signatures et le nom de l'arbitre sont nécessaires.</div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/scoreboard/signature-pad.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/signature-pad.js') ?>"></script>
<script>
(function () {
    const referee = document.getElementById('refereeName');
    const pad = window.SbSignaturePad({
        backUrl: <?= json_encode(base_url('marquoir/match/' . $match->id)) ?>,
        extraComplete: () => referee.value.trim() !== '',
    });
    referee.addEventListener('input', pad.refresh);
})();
</script>
<?= $this->endSection() ?>
