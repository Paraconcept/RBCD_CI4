<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title text-center mb-1">Qui commence la partie ?</h1>
<p class="text-center text-secondary mb-4">
    <?= esc(M::disciplineLabel($match)) ?> · <?= esc(M::distanceLabel($match)) ?>
    <?= $match->innings_limit ? ' · limite ' . (int) $match->innings_limit . ' reprises' : '' ?>
    <br><kbd>1</kbd> ou <kbd>2</kbd> au pavé numérique, ou cliquez.<br>Le joueur qui commence se place à gauche et joue les chiffres blancs.
</p>
<div class="text-center mb-4">
    <button type="button" class="btn btn-lg sb-warmup-btn" id="warmupBtn">
        <i class="fas fa-stopwatch me-2"></i><span id="warmupLabel">Démarrer l'échauffement</span>
        <kbd class="ms-2">*</kbd>
    </button>
    <div class="text-secondary small mt-2" id="warmupDone"></div>
</div>

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

<div class="sb-warmup" id="warmup" hidden>
    <div class="sb-warmup-title" id="warmupTitle">Échauffement</div>
    <div class="sb-warmup-players"><?= esc(M::playerLabel($match, 'p1')) ?> · <?= esc(M::playerLabel($match, 'p2')) ?></div>
    <div class="sb-warmup-time" id="warmupTime"></div>
    <div class="sb-warmup-hint" id="warmupHint"><kbd>Entrée</kbd> terminer l'échauffement</div>
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

    // Échauffement : un décompte par joueur, le nombre déjà fait survit à un rechargement de la page
    const WARMUP_MS = <?= (int) $warmupMinutes ?> * 60000;
    const storeKey = 'sb_warmup_<?= (int) $match->id ?>';
    const overlay = document.getElementById('warmup');
    let done = 0, endAt = 0, timer = null;
    try { done = parseInt(sessionStorage.getItem(storeKey) || '0', 10) || 0; } catch (e) { /* ignoré */ }

    const fmt = (ms) => {
        const s = Math.max(0, Math.ceil(ms / 1000));
        return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
    };

    function renderButton() {
        const btn = document.getElementById('warmupBtn');
        document.getElementById('warmupLabel').textContent = done === 0 ? "Démarrer l'échauffement" : 'Démarrer le 2e échauffement';
        btn.hidden = done >= 2;
        document.getElementById('warmupDone').textContent = done === 1 ? '1er échauffement terminé'
            : done >= 2 ? 'Échauffements terminés — choisissez qui commence' : '';
    }

    function tick() {
        const left = endAt - Date.now();
        const time = document.getElementById('warmupTime');
        time.textContent = fmt(left);
        time.classList.toggle('last', left <= 60000);
        if (left <= 0) {
            clearInterval(timer);
            time.textContent = '0:00';
            document.getElementById('warmupTitle').textContent = 'Échauffement terminé';
            document.getElementById('warmupHint').innerHTML = '<kbd>Entrée</kbd> pour continuer';
        }
    }

    function startWarmup() {
        if (done >= 2 || !overlay.hidden) return;
        document.getElementById('warmupTitle').textContent = 'Échauffement ' + (done + 1) + ' / 2';
        document.getElementById('warmupHint').innerHTML = "<kbd>Entrée</kbd> terminer l'échauffement";
        overlay.hidden = false;
        if (!document.fullscreenElement && document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen().catch(() => {});
        }
        endAt = Date.now() + WARMUP_MS;
        tick();
        timer = setInterval(tick, 250);
    }

    function stopWarmup() {
        clearInterval(timer);
        overlay.hidden = true;
        done++;
        try { sessionStorage.setItem(storeKey, String(done)); } catch (e) { /* ignoré */ }
        renderButton();
    }

    document.getElementById('warmupBtn').addEventListener('click', startWarmup);
    overlay.addEventListener('click', stopWarmup);
    renderButton();

    document.addEventListener('keydown', (e) => {
        if (!overlay.hidden) {
            if (e.key === 'Enter') { e.preventDefault(); stopWarmup(); }
            return;
        }
        if (e.code === 'NumpadMultiply' || e.key === '*') { e.preventDefault(); startWarmup(); return; }
        const k = e.code.replace('Numpad', '').replace('Digit', '');
        if (k === '1') choose(1);
        if (k === '2') choose(2);
    });
})();
</script>
<?= $this->endSection() ?>
