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

<form method="post" id="signForm" action="<?= base_url('marquoir/match/' . $match->id . '/signer') ?>">
    <div class="row g-3">
        <?php foreach (['p1' => 'Joueur A', 'p2' => 'Joueur B'] as $p => $label): ?>
        <div class="col-md-6">
            <div class="sb-sign-label <?= $p ?>">
                <span><?= $label ?> — <b><?= esc(M::playerLabel($match, $p)) ?></b></span>
                <button type="button" class="btn btn-sm btn-outline-light" data-clear="<?= $p ?>"><i class="fas fa-eraser me-1"></i>Effacer</button>
            </div>
            <canvas class="sb-sign-pad" data-pad="<?= $p ?>"></canvas>
            <input type="hidden" name="<?= $p ?>_signature">
        </div>
        <?php endforeach; ?>
        <div class="col-md-6">
            <div class="sb-sign-label">
                <span>Arbitre</span>
                <button type="button" class="btn btn-sm btn-outline-light" data-clear="referee"><i class="fas fa-eraser me-1"></i>Effacer</button>
            </div>
            <canvas class="sb-sign-pad" data-pad="referee"></canvas>
            <input type="hidden" name="referee_signature">
        </div>
        <div class="col-md-6 d-flex flex-column">
            <label for="refereeName" class="form-label sb-sign-label mb-2"><span>Nom de l'arbitre <span class="text-danger" title="Obligatoire">*</span></span></label>
            <input type="text" class="form-control form-control-lg" id="refereeName" name="referee_name" maxlength="100" autocomplete="off" required>
            <div class="mt-auto pt-3 d-flex gap-2 justify-content-end">
                <a href="<?= base_url('marquoir/match/' . $match->id) ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Retour</a>
                <button type="submit" class="btn btn-sb btn-lg" id="signSubmit" disabled><i class="fas fa-signature me-2"></i>Enregistrer les signatures</button>
            </div>
            <div class="text-secondary small text-end mt-2" id="signHint">Les trois signatures et le nom de l'arbitre sont nécessaires.</div>
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
    const pads = {};

    // Les traits sont gardés en coordonnées relatives (0-1) pour pouvoir redessiner après un redimensionnement
    function strokeAll(ctx, strokes, w, h, width) {
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.lineWidth = width;
        ctx.strokeStyle = '#000';
        ctx.fillStyle = '#000';
        strokes.forEach(function (s) {
            if (s.length === 1) {
                ctx.beginPath();
                ctx.arc(s[0][0] * w, s[0][1] * h, width / 2, 0, Math.PI * 2);
                ctx.fill();
                return;
            }
            ctx.beginPath();
            ctx.moveTo(s[0][0] * w, s[0][1] * h);
            for (let i = 1; i < s.length - 1; i++) {
                const mx = (s[i][0] + s[i + 1][0]) / 2, my = (s[i][1] + s[i + 1][1]) / 2;
                ctx.quadraticCurveTo(s[i][0] * w, s[i][1] * h, mx * w, my * h);
            }
            const last = s[s.length - 1];
            ctx.lineTo(last[0] * w, last[1] * h);
            ctx.stroke();
        });
    }

    function setupPad(canvas) {
        const pad = { canvas: canvas, strokes: [], current: null };
        const ctx = canvas.getContext('2d');

        pad.redraw = function () {
            const dpr = window.devicePixelRatio || 1;
            canvas.width = Math.round(canvas.clientWidth * dpr);
            canvas.height = Math.round(canvas.clientHeight * dpr);
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            strokeAll(ctx, pad.strokes, canvas.width, canvas.height, 3 * dpr);
        };

        pad.toPng = function () {
            const out = document.createElement('canvas');
            out.width = OUT_W;
            out.height = OUT_H;
            strokeAll(out.getContext('2d'), pad.strokes, OUT_W, OUT_H, 6);
            return out.toDataURL('image/png');
        };

        function point(e) {
            const r = canvas.getBoundingClientRect();
            return [Math.min(1, Math.max(0, (e.clientX - r.left) / r.width)), Math.min(1, Math.max(0, (e.clientY - r.top) / r.height))];
        }

        canvas.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            canvas.setPointerCapture(e.pointerId);
            pad.current = [point(e)];
            pad.strokes.push(pad.current);
            pad.redraw();
        });
        canvas.addEventListener('pointermove', function (e) {
            if (!pad.current) return;
            e.preventDefault();
            pad.current.push(point(e));
            pad.redraw();
        });
        const end = function () {
            if (!pad.current) return;
            pad.current = null;
            refresh();
        };
        canvas.addEventListener('pointerup', end);
        canvas.addEventListener('pointercancel', end);

        pad.redraw();
        return pad;
    }

    document.querySelectorAll('[data-pad]').forEach(function (c) { pads[c.dataset.pad] = setupPad(c); });
    document.querySelectorAll('[data-clear]').forEach(function (b) {
        b.addEventListener('click', function () {
            const pad = pads[b.dataset.clear];
            pad.strokes = [];
            pad.redraw();
            refresh();
        });
    });
    window.addEventListener('resize', function () { Object.values(pads).forEach((p) => p.redraw()); });

    function complete() {
        return referee.value.trim() !== '' && Object.values(pads).every((p) => p.strokes.length > 0);
    }

    function refresh() {
        submit.disabled = !complete();
        Object.keys(pads).forEach(function (k) {
            pads[k].canvas.classList.toggle('signed', pads[k].strokes.length > 0);
        });
    }
    referee.addEventListener('input', refresh);

    let sent = false;
    form.addEventListener('submit', function (e) {
        if (sent || !complete()) { e.preventDefault(); return; }
        sent = true;
        Object.keys(pads).forEach(function (k) { form.elements[k + '_signature'].value = pads[k].toPng(); });
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
})();
</script>
<?= $this->endSection() ?>
