<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1">Qui casse ?</h1>
<p class="text-center text-secondary mb-4">
    <?= esc(M::DISCIPLINES[$match->discipline] ?? '') ?> · <?= esc(M::distanceLabel($match)) ?>
    <?= $match->innings_limit ? ' · limite ' . (int) $match->innings_limit . ' reprises' : '' ?>
    <br><kbd>1</kbd> ou <kbd>2</kbd> au pavé numérique, ou cliquez.
</p>
<form method="post" id="startForm" action="<?= base_url('marquoir/billard/' . $table->id . '/demarrer/' . $match->id) ?>">
    <input type="hidden" name="starting_player" id="startingPlayer">
    <div class="row g-4 justify-content-center">
        <?php foreach ([1 => 'p1', 2 => 'p2'] as $n => $p): ?>
        <div class="col-md-5">
            <button type="button" class="sb-starter sb-starter-<?= $n ?>" data-player="<?= $n ?>">
                <span class="sb-starter-key"><?= $n ?></span>
                <span class="sb-starter-name"><?= esc($match->{$p . '_last_name'}) ?></span>
                <span class="sb-starter-first"><?= esc($match->{$p . '_first_name'}) ?></span>
                <small><?= esc($match->{$p . '_club'}) ?></small>
                <span class="badge bg-sb mt-2 fs-6"><?= (int) $match->{$p . '_distance'} ?> points à réaliser</span>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</form>
<div class="text-center mt-4">
    <a href="<?= base_url('marquoir/billard/' . $table->id) ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    const form = document.getElementById('startForm');
    const choose = (n) => { document.getElementById('startingPlayer').value = n; form.submit(); };
    document.querySelectorAll('.sb-starter').forEach(b => b.addEventListener('click', () => choose(b.dataset.player)));
    document.addEventListener('keydown', (e) => {
        const k = e.code.replace('Numpad', '').replace('Digit', '');
        if (k === '1') choose(1);
        if (k === '2') choose(2);
    });
})();
</script>
<?= $this->endSection() ?>
