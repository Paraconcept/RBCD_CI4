<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('content') ?>
<div class="sb-card text-center">
    <h1 class="h3 mb-2"><i class="fas fa-keyboard me-2 sb-accent"></i>Test du pavé numérique</h1>
    <p class="text-secondary mb-4">Appuyez sur la touche <strong>Verr. Num</strong>, puis sur quelques chiffres.</p>
    <div id="numlock" class="display-6 fw-bold mb-4 text-secondary">Verr. Num : pas encore détecté</div>
    <table class="table table-sm table-dark mb-0">
        <thead><tr><th>Heure</th><th>Touche (code)</th><th>Caractère (key)</th><th>Verr. Num actif ?</th></tr></thead>
        <tbody id="log"></tbody>
    </table>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(() => {
    const log = document.getElementById('log');
    const flag = document.getElementById('numlock');
    window.addEventListener('keydown', e => {
        e.preventDefault();
        const state = e.getModifierState ? (e.getModifierState('NumLock') ? 'oui' : 'non') : '?';
        const tr = document.createElement('tr');
        [new Date().toLocaleTimeString('fr-BE'), e.code || '(vide)', e.key || '(vide)', state]
            .forEach(v => { const td = document.createElement('td'); td.textContent = v; tr.appendChild(td); });
        log.prepend(tr);
        while (log.rows.length > 15) log.deleteRow(-1);
        if (e.code === 'NumLock' || e.key === 'NumLock') {
            flag.textContent = 'Verr. Num détecté ✔ (' + new Date().toLocaleTimeString('fr-BE') + ')';
            flag.className = 'display-6 fw-bold mb-4 text-success';
        }
    }, true);
})();
</script>
<?= $this->endSection() ?>
