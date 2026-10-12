<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1">Qui commence la partie ?</h1>
<p class="text-center text-secondary mb-4">
    <?= esc(M::DISCIPLINES[$match->discipline] ?? '') ?> · <?= esc(M::distanceLabel($match)) ?>
    <?= $match->innings_limit ? ' · limite ' . (int) $match->innings_limit . ' reprises' : '' ?>
    <br><kbd>1</kbd> ou <kbd>2</kbd> au pavé numérique, ou cliquez.<br>Le joueur qui commence se place à gauche et joue les chiffres blancs.
</p>
<form method="post" id="startForm" action="<?= base_url('marquoir/billard/' . $table->id . '/demarrer/' . $match->id) ?>">
    <input type="hidden" name="starting_player" id="startingPlayer">
    <div class="row g-4 justify-content-center sb-starters">
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
    const cols = document.querySelectorAll('.sb-starters > div');
    let chosen = false;
    const choose = (n) => {
        if (chosen) return;
        chosen = true;
        document.getElementById('startingPlayer').value = n;
        if (String(n) !== '2') { form.submit(); return; }
        // Le joueur 2 glisse à gauche (blanc), le joueur 1 à droite (jaune)
        const dx = cols[1].getBoundingClientRect().left - cols[0].getBoundingClientRect().left;
        cols[0].style.transform = 'translateX(' + dx + 'px)';
        cols[1].style.transform = 'translateX(' + (-dx) + 'px)';
        cols[0].querySelector('.sb-starter').classList.replace('sb-starter-1', 'sb-starter-2');
        cols[1].querySelector('.sb-starter').classList.replace('sb-starter-2', 'sb-starter-1');
        setTimeout(() => form.submit(), 650);
    };
    document.querySelectorAll('.sb-starter').forEach(b => b.addEventListener('click', () => choose(b.dataset.player)));
    document.addEventListener('keydown', (e) => {
        const k = e.code.replace('Numpad', '').replace('Digit', '');
        if (k === '1') choose(1);
        if (k === '2') choose(2);
    });
})();
</script>
<?= $this->endSection() ?>
