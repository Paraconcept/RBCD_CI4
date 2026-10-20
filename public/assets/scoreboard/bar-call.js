// Appel du bar depuis le Chromebook d'un billard : touche Verr. Num, puis Entrée pour confirmer.
// Chargé avant les autres scripts du marquoir : son écouteur passe en premier et retient les touches pendant la confirmation.
(function () {
    'use strict';

    const C = window.SB_BAR;
    if (!C) return;

    const style = document.createElement('style');
    style.textContent =
        '.sbbar{position:fixed;inset:0;z-index:5000;display:none;align-items:center;justify-content:center;background:rgba(5,8,18,.82);font-family:Barlow,sans-serif}' +
        '.sbbar.open{display:flex}' +
        '.sbbar-box{background:#16213d;color:#eef1f7;border:3px solid #ffcc00;border-radius:22px;padding:2.5rem 3.5rem;text-align:center;max-width:90vw;box-shadow:0 0 10vh rgba(255,204,0,.4)}' +
        '.sbbar-title{font-family:"Barlow Condensed",sans-serif;font-weight:800;font-size:3.2rem;line-height:1.1}' +
        '.sbbar-text{font-size:1.4rem;margin-top:1rem;color:#8b96ad}' +
        '.sbbar-text kbd{font-size:1.2rem}' +
        '.sbbar-btns{display:flex;gap:1.5rem;justify-content:center;margin-top:2rem}' +
        '.sbbar-btn{display:flex;align-items:center;gap:.9rem;min-width:13rem;justify-content:center;padding:1rem 1.8rem;border:0;border-radius:16px;cursor:pointer;font-family:"Barlow Condensed",sans-serif;font-weight:800;font-size:2.2rem;color:#fff;box-shadow:0 6px 0 rgba(0,0,0,.35)}' +
        '.sbbar-btn:active{transform:translateY(4px);box-shadow:0 2px 0 rgba(0,0,0,.35)}' +
        '.sbbar-btn.yes{background:#16a34a}.sbbar-btn.no{background:#dc2626}' +
        '.sbbar-btn kbd{font-family:Barlow,sans-serif;font-size:1.5rem;font-weight:700;background:#fff;color:#111;border-radius:10px;padding:.35rem .8rem;box-shadow:inset 0 -3px 0 rgba(0,0,0,.25)}' +
        '.sbbar.ok .sbbar-box{border-color:#22c55e;box-shadow:0 0 10vh rgba(34,197,94,.4)}.sbbar.err .sbbar-box{border-color:#ef4444;box-shadow:0 0 10vh rgba(239,68,68,.4)}';
    document.head.appendChild(style);

    const box = document.createElement('div');
    box.className = 'sbbar';
    box.innerHTML = '<div class="sbbar-box"><div class="sbbar-title"></div><div class="sbbar-text"></div></div>';
    document.body.appendChild(box);
    const title = box.querySelector('.sbbar-title'), text = box.querySelector('.sbbar-text');

    let mode = null; // 'ask' | 'sending' | 'result'

    let timer = null;

    box.addEventListener('click', function (e) {
        const b = e.target.closest('[data-bar]');
        if (!b || mode !== 'ask') return;
        if (b.dataset.bar === 'yes') send(); else hide();
    });

    function show(m, cls, t, html) {
        mode = m;
        clearTimeout(timer);
        box.className = 'sbbar open' + (cls ? ' ' + cls : '');
        title.innerHTML = t;
        text.innerHTML = html;
    }

    function hide() {
        mode = null;
        clearTimeout(timer);
        box.className = 'sbbar';
    }

    function result(cls, t, html) {
        show('result', cls, t, html);
        timer = setTimeout(hide, 4000);
    }

    function send() {
        show('sending', '', '<i class="fas fa-spinner fa-spin"></i>', 'Envoi de l\'appel…');
        fetch(C.url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (d) {
                if (d.created) result('ok', '<i class="fas fa-check me-2"></i>Le bar est appelé', 'Le barman arrive au ' + C.table + '.');
                else result('ok', '<i class="fas fa-bell me-2"></i>Déjà appelé', 'Le barman est prévenu depuis ' + Math.max(1, Math.round(d.waiting / 60)) + ' min.');
            })
            .catch(function () {
                result('err', '<i class="fas fa-triangle-exclamation me-2"></i>Appel non envoyé', 'Problème de connexion : réessayez avec <kbd>Verr. Num</kbd>.');
            });
    }

    window.addEventListener('keydown', function (e) {
        const isNumLock = e.code === 'NumLock' || e.key === 'NumLock';

        if (mode === 'result') { hide(); if (!isNumLock) return; }
        if (mode === null && !isNumLock) return;

        e.preventDefault();
        e.stopImmediatePropagation();
        if (e.repeat || mode === 'sending') return;

        if (mode === null) {
            show('ask', '', '<i class="fas fa-beer-mug-empty me-2"></i>Appeler le bar ?',
                '<div class="sbbar-btns">' +
                '<button type="button" class="sbbar-btn yes" data-bar="yes"><kbd>Entrée</kbd>Oui</button>' +
                '<button type="button" class="sbbar-btn no" data-bar="no"><kbd>⌫</kbd>Non</button>' +
                '</div>');
            timer = setTimeout(hide, 10000);
            return;
        }
        if (e.code === 'NumpadEnter' || e.key === 'Enter') send();
        else if (e.key === 'Backspace' || e.key === 'Escape' || isNumLock) hide();
    }, true);
})();
