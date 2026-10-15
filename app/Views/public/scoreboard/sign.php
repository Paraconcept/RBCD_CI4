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
<script>
(function () {
    'use strict';

    // Exportée à taille fixe (3:1), quelle que soit la taille du cadre à l'écran
    const OUT_W = 900, OUT_H = 300;
    const form = document.getElementById('signForm');
    const submit = document.getElementById('signSubmit');
    const referee = document.getElementById('refereeName');
    const canvas = document.getElementById('signPad');
    const ctx = canvas.getContext('2d');
    const roleButtons = [...document.querySelectorAll('[data-role]')];

    // Un seul cadre : les traits de chaque signataire sont gardés à part, en coordonnées relatives (0-1)
    const strokes = { p1: [], p2: [], referee: [] };
    let role = 'p1';
    let current = null;
    // Seul le premier contact dessine : la paume posée sur l'écran (stylet passif) est ignorée
    let pointerId = null;

    function strokeAll(c, list, w, h, width) {
        c.lineCap = 'round';
        c.lineJoin = 'round';
        c.lineWidth = width;
        c.strokeStyle = '#000';
        c.fillStyle = '#000';
        list.forEach(function (s) {
            if (s.length === 1) {
                c.beginPath();
                c.arc(s[0][0] * w, s[0][1] * h, width / 2, 0, Math.PI * 2);
                c.fill();
                return;
            }
            c.beginPath();
            c.moveTo(s[0][0] * w, s[0][1] * h);
            for (let i = 1; i < s.length - 1; i++) {
                const mx = (s[i][0] + s[i + 1][0]) / 2, my = (s[i][1] + s[i + 1][1]) / 2;
                c.quadraticCurveTo(s[i][0] * w, s[i][1] * h, mx * w, my * h);
            }
            const last = s[s.length - 1];
            c.lineTo(last[0] * w, last[1] * h);
            c.stroke();
        });
    }

    function redraw() {
        const dpr = window.devicePixelRatio || 1;
        canvas.width = Math.round(canvas.clientWidth * dpr);
        canvas.height = Math.round(canvas.clientHeight * dpr);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        strokeAll(ctx, strokes[role], canvas.width, canvas.height, 3 * dpr);
    }

    function toPng(list) {
        const out = document.createElement('canvas');
        out.width = OUT_W;
        out.height = OUT_H;
        strokeAll(out.getContext('2d'), list, OUT_W, OUT_H, 6);
        return out.toDataURL('image/png');
    }

    function point(e) {
        const r = canvas.getBoundingClientRect();
        return [Math.min(1, Math.max(0, (e.clientX - r.left) / r.width)), Math.min(1, Math.max(0, (e.clientY - r.top) / r.height))];
    }

    canvas.addEventListener('pointerdown', function (e) {
        e.preventDefault();
        if (pointerId !== null) return;
        pointerId = e.pointerId;
        canvas.setPointerCapture(e.pointerId);
        current = [point(e)];
        strokes[role].push(current);
        redraw();
    });
    canvas.addEventListener('pointermove', function (e) {
        if (e.pointerId !== pointerId) return;
        e.preventDefault();
        current.push(point(e));
        redraw();
    });
    function end(e) {
        if (e.pointerId !== pointerId) return;
        pointerId = null;
        current = null;
        refresh();
    }
    canvas.addEventListener('pointerup', end);
    canvas.addEventListener('pointercancel', end);

    function select(r) {
        if (pointerId !== null) return;
        role = r;
        roleButtons.forEach(function (b) { b.classList.toggle('active', b.dataset.role === role); });
        document.getElementById('signWho').textContent = roleButtons.find((b) => b.dataset.role === role).dataset.who;
        redraw();
    }
    roleButtons.forEach(function (b) { b.addEventListener('click', function () { select(b.dataset.role); }); });

    document.getElementById('signClear').addEventListener('click', function () {
        strokes[role] = [];
        redraw();
        refresh();
    });
    window.addEventListener('resize', redraw);

    function complete() {
        return referee.value.trim() !== '' && Object.values(strokes).every((s) => s.length > 0);
    }

    function refresh() {
        submit.disabled = !complete();
        roleButtons.forEach(function (b) {
            const done = strokes[b.dataset.role].length > 0;
            b.classList.toggle('signed', done);
            b.querySelector('.sb-sign-check').className = (done ? 'fas fa-circle-check' : 'far fa-circle') + ' sb-sign-check';
        });
        canvas.classList.toggle('signed', strokes[role].length > 0);
    }
    referee.addEventListener('input', refresh);

    let sent = false;
    form.addEventListener('submit', function (e) {
        if (sent || !complete()) { e.preventDefault(); return; }
        sent = true;
        Object.keys(strokes).forEach(function (k) { form.elements[k + '_signature'].value = toPng(strokes[k]); });
        submit.disabled = true;
        submit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement…';
    });

    // Pavé : Entrée ne valide pas (trop facile par erreur), Retour arrière revient au résultat hors du champ texte
    window.addEventListener('keydown', function (e) {
        const inField = document.activeElement === referee;
        if (e.key === 'Enter' && inField) { e.preventDefault(); referee.blur(); return; }
        if ((e.key === 'Backspace' || e.key === 'Escape') && !inField) {
            e.preventDefault();
            window.location.href = <?= json_encode(base_url('marquoir/match/' . $match->id)) ?>;
        }
    });

    select('p1');
    refresh();
})();
</script>
<?= $this->endSection() ?>
