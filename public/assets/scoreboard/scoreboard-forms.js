/* Formulaires de rencontre (admin DS + partie amicale) : auto-complétion des joueurs et options. */
(function () {
    'use strict';

    const css = document.createElement('style');
    css.textContent =
        '.sb-ac-list{position:absolute;z-index:1060;left:0;right:0;top:100%;margin:2px 0 0;padding:4px 0;list-style:none;' +
        'background:#fff;color:#222;border:1px solid #ccc;border-radius:6px;box-shadow:0 6px 18px rgba(0,0,0,.25);max-height:300px;overflow:auto}' +
        '.sb-ac-list li{padding:6px 12px;cursor:pointer;display:flex;justify-content:space-between;gap:12px}' +
        '.sb-ac-list li.active,.sb-ac-list li:hover{background:#84252B;color:#fff}' +
        '.sb-ac-list small{opacity:.7}' +
        'input[data-sb-ac]::placeholder{text-transform:none}';
    document.head.appendChild(css);

    document.querySelectorAll('input[data-sb-ac]').forEach(function (input) {
        const prefix = input.dataset.sbAc;
        const form   = input.form;
        const field  = (n) => form.querySelector('[name="' + prefix + '_' + n + '"]');
        const wrap   = input.parentElement;
        wrap.style.position = 'relative';

        let list = null, items = [], active = -1, timer = null, seq = 0;

        const close = () => { if (list) { list.remove(); list = null; } items = []; active = -1; };

        const pick = (p) => {
            input.value = p.last_name;
            field('first_name').value = p.first_name || '';
            field('club').value       = p.club || '';
            field('member_id').value  = p.member_id || '';
            if (field('license')) field('license').value = p.license || '';
            close();
            field('first_name').dispatchEvent(new Event('change'));
        };

        const render = () => {
            const data = items;
            close();
            items = data;
            if (!items.length) return;
            list = document.createElement('ul');
            list.className = 'sb-ac-list';
            items.forEach(function (p, i) {
                const li = document.createElement('li');
                li.innerHTML = '<span></span><small></small>';
                li.firstChild.textContent = p.last_name + ' ' + p.first_name;
                li.lastChild.textContent  = p.club || '';
                li.addEventListener('mousedown', function (e) { e.preventDefault(); pick(p); });
                list.appendChild(li);
            });
            wrap.appendChild(list);
        };

        const highlight = () => {
            if (!list) return;
            [...list.children].forEach((li, i) => li.classList.toggle('active', i === active));
        };

        input.addEventListener('input', function () {
            field('member_id').value = '';
            clearTimeout(timer);
            const q = input.value.trim();
            if (q.length < 2) { close(); return; }
            timer = setTimeout(function () {
                const mine = ++seq;
                fetch(input.dataset.sbAcUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.ok ? r.json() : [])
                    .then(function (data) {
                        if (mine !== seq) return;
                        items = data; render();
                    })
                    .catch(close);
            }, 200);
        });

        input.addEventListener('keydown', function (e) {
            if (!list) return;
            if (e.key === 'ArrowDown') { active = Math.min(active + 1, items.length - 1); highlight(); e.preventDefault(); }
            else if (e.key === 'ArrowUp') { active = Math.max(active - 1, 0); highlight(); e.preventDefault(); }
            else if (e.key === 'Enter' && active >= 0) { pick(items[active]); e.preventDefault(); }
            else if (e.key === 'Escape') { close(); }
        });

        input.addEventListener('blur', function () { setTimeout(close, 150); });

        // Un joueur modifié à la main n'est plus le membre sélectionné
        ['first_name', 'club'].forEach(function (n) {
            field(n).addEventListener('input', function () { field('member_id').value = ''; });
        });
    });

    document.querySelectorAll('[data-sb-show-when]').forEach(function (el) {
        const cb = document.getElementById(el.dataset.sbShowWhen);
        if (!cb) return;
        const sync = () => { el.style.display = cb.checked ? '' : 'none'; };
        cb.addEventListener('change', sync);
        sync();
    });

    const d1 = document.querySelector('input[name="p1_distance"]');
    const d2 = document.querySelector('input[name="p2_distance"]');
    if (d1 && d2) {
        let d2Touched = d2.value !== '' && d2.value !== d1.value;
        d2.addEventListener('input', function () { d2Touched = true; });
        d1.addEventListener('input', function () { if (!d2Touched) d2.value = d1.value; });
    }

    const discipline = document.getElementById('discipline');
    const limit      = document.getElementById('innings_limit');
    if (discipline && limit) {
        discipline.addEventListener('change', function () {
            if (discipline.value === '3bandes' && discipline.dataset.defaultLimit3c) {
                limit.value = discipline.dataset.defaultLimit3c;
            }
        });
    }
})();
