<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardSaladFigureModel as F;

$qp = $discipline === M::SALAD_QP;
// Après une erreur : les figures telles que saisies ; sinon la liste du club avec ses derniers réglages
$rows = [];
if (is_array(old('figures'))) {
    $byId = [];
    foreach ($figures as $f) $byId[$f->id] = $f;
    foreach (old('figures') as $r) {
        if (!empty($r['delete'])) continue;
        $f = $byId[(int) ($r['id'] ?? 0)] ?? null;
        $rows[] = ['id' => $f->id ?? '', 'label' => $f->label ?? ($r['label'] ?? ''), 'builtin' => (bool) ($f->is_builtin ?? false),
                   'active' => !empty($r['active']), 'p1' => $r['p1'] ?? '', 'p2' => $r['p2'] ?? ''];
    }
    $equal = (bool) old('equal_points');
} else {
    foreach ($figures as $f) {
        $rows[] = ['id' => $f->id, 'label' => $f->label, 'builtin' => (bool) $f->is_builtin, 'active' => (bool) $f->active,
                   'p1' => $f->p1_points, 'p2' => $f->p2_points];
    }
    $equal = !array_filter($rows, static fn ($r) => $r['active'] && (int) $r['p1'] !== (int) $r['p2']);
}
$figureRow = static function (int $i, array $r): string {
    $n = 'figures[' . $i . ']';
    ob_start(); ?>
    <div class="sal-row<?= $r['active'] ? '' : ' off' ?>" data-sal-row>
        <input type="hidden" name="<?= $n ?>[id]" value="<?= esc((string) $r['id']) ?>">
        <input type="hidden" name="<?= $n ?>[delete]" value="" data-sal-delete>
        <div class="form-check form-switch fs-4 mb-0">
            <input class="form-check-input" type="checkbox" role="switch" name="<?= $n ?>[active]" value="1" <?= $r['active'] ? 'checked' : '' ?> data-sal-active aria-label="Activer la figure">
        </div>
        <div class="input-group input-group-lg">
            <span class="input-group-text">Point «</span>
            <input type="text" name="<?= $n ?>[label]" class="form-control" maxlength="60" value="<?= esc($r['label']) ?>"
                   <?= $r['id'] !== '' ? 'readonly tabindex="-1"' : 'required placeholder="ex. De la mauvaise main"' ?> data-sal-label>
            <span class="input-group-text">»</span>
        </div>
        <input type="number" name="<?= $n ?>[p1]" class="form-control form-control-lg" min="1" max="<?= F::MAX_POINTS ?>" value="<?= esc((string) $r['p1']) ?>" data-sal-p1 aria-label="Points joueur 1">
        <input type="number" name="<?= $n ?>[p2]" class="form-control form-control-lg" min="1" max="<?= F::MAX_POINTS ?>" value="<?= esc((string) $r['p2']) ?>" data-sal-p2 aria-label="Points joueur 2">
        <input type="text" class="form-control form-control-lg text-center sal-key" disabled data-sal-key aria-label="N° au pavé numérique">
        <?php if (!$r['builtin']): ?>
        <button type="button" class="btn btn-outline-danger" data-sal-remove title="Retirer de la liste du club"><i class="fas fa-trash"></i></button>
        <?php else: ?>
        <span></span>
        <?php endif; ?>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('public/scoreboard/layout') ?>
<?= $this->section('topbar') ?>
<span class="sb-topbar-title"><?= esc($table->name) ?></span>
<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<style>
.sal-steps { display: flex; gap: .75rem; margin-bottom: 1.5rem; }
.sal-steps span { padding: .35rem 1rem; border-radius: 20px; background: var(--sb-panel); color: var(--sb-muted); font-weight: 600; }
.sal-steps span.on { background: var(--sb-red); color: #fff; }
.sal-grid-head, .sal-row { display: grid; grid-template-columns: 3.5rem minmax(0, 1fr) 7rem 7rem 7rem 3rem; gap: .75rem; align-items: center; }
.sal-grid-head { color: var(--sb-muted); font-size: .95rem; margin-bottom: .5rem; }
.sal-row { margin-bottom: .75rem; }
.sal-row.off input[data-sal-label], .sal-row.off input[type=number], .sal-row.off .input-group-text { opacity: .45; }
.sal-key { font-weight: 700; }
.sal-equal [data-sal-p2] { pointer-events: none; opacity: .6; }
@media (max-width: 767px) {
    .sal-grid-head { display: none; }
    .sal-row { grid-template-columns: 3rem 1fr 1fr; }
    .sal-row .input-group { grid-column: 2 / -1; }
    .sal-row .sal-key { display: none; }
}
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<h1 class="sb-title mb-3"><i class="<?= $qp ? 'fas fa-shuffle' : 'far fa-list-alt' ?> me-2 sb-accent"></i><?= esc($title) ?></h1>
<div class="sal-steps"><span data-sal-step-tab="1" class="on">1. Joueurs</span><span data-sal-step-tab="2">2. Figures</span></div>
<form method="post" action="<?= base_url('marquoir/billard/' . $table->id . ($qp ? '/amicale-salade-quick-pick' : '/amicale-salade')) ?>" autocomplete="off" novalidate id="salForm">
    <input type="hidden" name="discipline" value="<?= esc($discipline) ?>">
    <input type="hidden" name="players" value="2">

    <div data-sal-step="1">
        <div class="row g-4">
            <?php for ($n = 1; $n <= 2; $n++): $p = 'p' . $n; ?>
            <div class="col-md-6">
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
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="<?= $p ?>_first_name" class="form-control form-control-lg" required value="<?= esc(old($p . '_first_name')) ?>">
                </div>
            </div>
            <?php endfor; ?>
        </div>
        <div class="d-flex justify-content-between mt-4 sb-sticky-actions">
            <a href="<?= base_url('marquoir/billard/' . $table->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Retour</a>
            <button type="button" class="btn btn-sb btn-lg" id="salNext">Suivant<i class="fas fa-arrow-right ms-2"></i></button>
        </div>
    </div>

    <div data-sal-step="2" hidden>
        <div class="sb-card<?= $equal ? ' sal-equal' : '' ?>" id="salFigures">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <h2 class="h4 mb-0">Figures</h2>
                <div class="form-check form-switch fs-5 mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="equal_points" id="equal_points" value="1" <?= $equal ? 'checked' : '' ?>>
                    <label class="form-check-label" for="equal_points">Points égaux</label>
                </div>
            </div>
            <div class="sal-grid-head"><span></span><span>Type de figure</span><span>Points <b data-sal-name="1">joueur 1</b></span><span>Points <b data-sal-name="2">joueur 2</b></span><span>N° au pavé</span><span></span></div>
            <div id="salRows">
                <?php foreach ($rows as $i => $r): ?><?= $figureRow($i, $r) ?><?php endforeach; ?>
            </div>
            <template id="salRowTpl"><?= $figureRow(999, ['id' => '', 'label' => '', 'builtin' => false, 'active' => true, 'p1' => '', 'p2' => '']) ?></template>
            <button type="button" class="btn btn-outline-light mt-2" id="salAdd"><i class="fas fa-plus me-2"></i>Ajouter un type de point</button>
            <p class="text-secondary small mt-3 mb-0"><?= F::MAX_ACTIVE ?> figures actives au maximum, de 1 à <?= F::MAX_POINTS ?> points chacune. Les figures ajoutées sont gardées pour tous les billards.
                <?= $qp ? 'En « Quick Pick », le marquoir tire au hasard la figure de chaque coup.' : 'Au pavé, la touche de chaque figure est son N°.' ?></p>
            <p class="small mt-2 mb-0">À réaliser : <b data-sal-name="1">joueur 1</b> <b data-sal-total="1">0</b> points · <b data-sal-name="2">joueur 2</b> <b data-sal-total="2">0</b> points</p>
            <div class="alert alert-danger mt-3 mb-0" id="salError" hidden></div>
        </div>
        <div class="sb-card mt-4">
            <div class="form-check form-switch fs-5">
                <input class="form-check-input" type="checkbox" role="switch" name="unequal_innings" id="unequal_innings" value="1" <?= old('unequal_innings') ? 'checked' : '' ?>>
                <label class="form-check-label" for="unequal_innings">Reprises inégales</label>
            </div>
            <small class="text-secondary">Le premier qui termine toutes ses figures gagne. Sans cette option, son adversaire joue encore une reprise d'égalisation.</small>
        </div>
        <div class="d-flex justify-content-between mt-4 sb-sticky-actions">
            <button type="button" class="btn btn-outline-secondary btn-lg" id="salPrev"><i class="fas fa-arrow-left me-2"></i>Joueurs</button>
            <button class="btn btn-sb btn-lg"><i class="fas fa-check me-2"></i>Continuer</button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<script>
// Configuration de la Salade : joueurs puis figures (N° au pavé automatique, points égaux, ajout de figures)
(function () {
    const MAX_ACTIVE = <?= F::MAX_ACTIVE ?>, MAX_POINTS = <?= F::MAX_POINTS ?>;
    const form = document.getElementById('salForm');
    const box = document.getElementById('salFigures');
    const list = document.getElementById('salRows');
    const equal = document.getElementById('equal_points');
    const err = document.getElementById('salError');
    const rows = () => [...list.querySelectorAll('[data-sal-row]')].filter((r) => !r.hidden);
    let seq = 1000;

    function refresh() {
        let k = 0;
        rows().forEach(function (r) {
            const on = r.querySelector('[data-sal-active]').checked;
            r.classList.toggle('off', !on);
            r.querySelector('[data-sal-key]').value = on ? ++k : '';
            if (equal.checked) r.querySelector('[data-sal-p2]').value = r.querySelector('[data-sal-p1]').value;
        });
        box.classList.toggle('sal-equal', equal.checked);
        [1, 2].forEach(function (n) {
            const sum = rows().filter((r) => r.querySelector('[data-sal-active]').checked)
                .reduce((t, r) => t + (parseInt(r.querySelector('[data-sal-p' + n + ']').value, 10) || 0), 0);
            document.querySelector('[data-sal-total="' + n + '"]').textContent = sum;
        });
    }

    function showError(msg) { err.textContent = msg; err.hidden = !msg; }

    list.addEventListener('change', function (e) {
        if (e.target.matches('[data-sal-active]') && e.target.checked
            && rows().filter((r) => r.querySelector('[data-sal-active]').checked).length > MAX_ACTIVE) {
            e.target.checked = false;
            showError(MAX_ACTIVE + ' figures actives au maximum : désactivez-en une autre d\'abord.');
        }
        refresh();
    });
    list.addEventListener('input', refresh);
    equal.addEventListener('change', refresh);

    list.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-sal-remove]');
        if (!btn) return;
        const row = btn.closest('[data-sal-row]');
        const label = row.querySelector('[data-sal-label]').value.trim();
        if (row.querySelector('input[name$="[id]"]').value) {
            if (!confirm('Retirer « ' + label + ' » de la liste des figures du club ?')) return;
            row.querySelector('[data-sal-delete]').value = '1';
            row.hidden = true;
        } else {
            row.remove();
        }
        refresh();
    });

    document.getElementById('salAdd').addEventListener('click', function () {
        const html = document.getElementById('salRowTpl').innerHTML.replace(/figures\[999\]/g, 'figures[' + (seq++) + ']');
        list.insertAdjacentHTML('beforeend', html);
        const row = list.lastElementChild;
        const active = row.querySelector('[data-sal-active]');
        if (rows().filter((r) => r.querySelector('[data-sal-active]').checked).length > MAX_ACTIVE) active.checked = false;
        row.querySelector('[data-sal-label]').focus();
        refresh();
    });

    function checkFigures() {
        const on = rows().filter((r) => r.querySelector('[data-sal-active]').checked);
        if (!on.length) return 'Activez au moins une figure.';
        for (const r of rows()) {
            if (!r.querySelector('[data-sal-label]').value.trim()) return 'Donnez un nom à la figure ajoutée (ou retirez-la).';
        }
        for (const r of on) {
            for (const n of [1, 2]) {
                const v = parseInt(r.querySelector('[data-sal-p' + n + ']').value, 10);
                if (!(v >= 1 && v <= MAX_POINTS)) return 'Point « ' + r.querySelector('[data-sal-label]').value.trim() + ' » : points de 1 à ' + MAX_POINTS + '.';
            }
        }
        return '';
    }

    // Les colonnes de points portent le nom des joueurs saisis à l'étape 1
    function names() {
        [1, 2].forEach(function (n) {
            const v = form.querySelector('[name="p' + n + '_last_name"]').value.trim().toUpperCase();
            document.querySelectorAll('[data-sal-name="' + n + '"]').forEach((el) => { el.textContent = v || 'joueur ' + n; });
        });
    }
    const playerInputs = () => [...document.querySelectorAll('[data-sal-step="1"] input[required]')];

    function step(n) {
        names();
        document.querySelectorAll('[data-sal-step]').forEach((el) => { el.hidden = el.dataset.salStep !== String(n); });
        document.querySelectorAll('[data-sal-step-tab]').forEach((el) => el.classList.toggle('on', el.dataset.salStepTab === String(n)));
        window.scrollTo(0, 0);
        if (n === 1) form.querySelector('[name="p1_last_name"]').focus();
    }
    document.getElementById('salNext').addEventListener('click', function () {
        const bad = playerInputs().find((i) => !i.value.trim());
        if (bad) { bad.reportValidity(); bad.focus(); return; }
        step(2);
    });
    document.getElementById('salPrev').addEventListener('click', () => step(1));
    form.addEventListener('submit', function (e) {
        refresh();
        if (playerInputs().some((i) => !i.value.trim())) { e.preventDefault(); step(1); playerInputs().find((i) => !i.value.trim()).reportValidity(); return; }
        const msg = checkFigures();
        showError(msg);
        if (msg) e.preventDefault();
    });
    refresh();
    // Retour après une erreur du serveur : les joueurs sont déjà remplis, on reprend aux figures
    step(<?= old('p1_last_name') ? 2 : 1 ?>);
})();
</script>
<?= $this->endSection() ?>
