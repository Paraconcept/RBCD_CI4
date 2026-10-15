/*
 * Signature au doigt ou au stylet sur les Chromebooks du marquoir (feuille de match, feuille récapitulative).
 *
 * Un seul grand cadre (#signPad) ; les boutons [data-role] choisissent qui signe. Les traits de chaque
 * signataire sont gardés à part, en coordonnées relatives (0-1), et exportés en PNG 900 × 300 dans les
 * champs cachés « <role>_signature » à l'envoi du formulaire.
 *
 * Options : backUrl (Retour arrière du pavé), extraComplete() (autre condition pour activer l'envoi).
 */
window.SbSignaturePad = function (opts) {
    'use strict';

    const OUT_W = 900, OUT_H = 300;
    const form = document.getElementById('signForm');
    const submit = document.getElementById('signSubmit');
    const canvas = document.getElementById('signPad');
    const ctx = canvas.getContext('2d');
    const roleButtons = [...form.querySelectorAll('[data-role]')];
    const extraComplete = opts.extraComplete || (() => true);

    const strokes = {};
    roleButtons.forEach(function (b) { strokes[b.dataset.role] = []; });
    let role = roleButtons[0].dataset.role;
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
        return extraComplete() && Object.values(strokes).every((s) => s.length > 0);
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

    let sent = false;
    form.addEventListener('submit', function (e) {
        if (sent || !complete()) { e.preventDefault(); return; }
        sent = true;
        Object.keys(strokes).forEach(function (k) { form.elements[k + '_signature'].value = toPng(strokes[k]); });
        submit.disabled = true;
        submit.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement…';
    });

    // Pavé : Entrée ne valide pas (trop facile par erreur), Retour arrière revient en arrière hors d'un champ texte
    window.addEventListener('keydown', function (e) {
        const field = document.activeElement && /^(INPUT|TEXTAREA)$/.test(document.activeElement.tagName);
        if (e.key === 'Enter' && field && document.activeElement.tagName === 'INPUT') { e.preventDefault(); document.activeElement.blur(); return; }
        if ((e.key === 'Backspace' || e.key === 'Escape') && !field && opts.backUrl) {
            e.preventDefault();
            window.location.href = opts.backUrl;
        }
    });

    select(role);
    refresh();
    return { refresh: refresh };
};
