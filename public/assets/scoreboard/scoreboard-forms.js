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
            if (field('club')) field('club').value = p.club || '';
            field('member_id').value  = p.member_id || '';
            if (field('license')) field('license').value = p.license || '';
            close();
            input.dispatchEvent(new CustomEvent('sb:player', { detail: p.categories || {} }));
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
            if (field(n)) field(n).addEventListener('input', function () { field('member_id').value = ''; });
        });
    });

    document.querySelectorAll('[data-sb-show-when]').forEach(function (el) {
        const cb = document.getElementById(el.dataset.sbShowWhen);
        if (!cb) return;
        const sync = () => { el.style.display = cb.checked ? '' : 'none'; };
        cb.addEventListener('change', sync);
        sync();
    });

    // Les points du joueur 1 sont recopiés chez les autres tant qu'on ne les a pas modifiés à la main
    const d1 = document.querySelector('input[name="p1_distance"]');
    const others = ['p2', 'p3'].map((p) => document.querySelector('input[name="' + p + '_distance"]')).filter(Boolean);
    if (d1) {
        others.forEach(function (d) {
            if (d.value !== '' && d.value !== d1.value) d.dataset.sbTouched = '1';
            d.addEventListener('input', function (e) { if (e.isTrusted) d.dataset.sbTouched = '1'; });
            d1.addEventListener('input', function () { if (!d.dataset.sbTouched) d.value = d1.value; });
        });
        [d1].concat(others).forEach((d) => d.addEventListener('input', function (e) { if (e.isTrusted) delete d.dataset.sbAuto; }));
    }

    const discipline = document.getElementById('discipline');
    const format     = document.getElementById('format');
    const limit      = document.getElementById('innings_limit');

    // Catégorie de chaque joueur : liste filtrée selon discipline + format, présélection d'après
    // les catégories connues du joueur choisi (membre du club ou joueur externe déjà encodé)
    const CAT = window.SB_CATEGORIES;
    const catSelects = document.querySelectorAll('select[data-sb-category]');
    if (CAT && discipline && format && catSelects.length) {
        const fmt = (n) => n.toFixed(3).replace('.', ',');
        catSelects.forEach(function (sel) {
            let known = {};
            const refresh = function (preferKnown) {
                const modes = (CAT.modes[discipline.value] || {})[format.value] || [];
                const current = preferKnown ? '' : (sel.value || sel.dataset.selected || '');
                delete sel.dataset.selected;
                sel.innerHTML = '<option value="">— Non renseignée —</option>';
                modes.forEach(function (mode) {
                    const rows = CAT.list.filter((c) => c.mode === mode);
                    if (!rows.length) return;
                    const parent = modes.length > 1 ? document.createElement('optgroup') : sel;
                    if (parent !== sel) { parent.label = rows[0].mode_text; sel.appendChild(parent); }
                    rows.forEach(function (c) {
                        const o = document.createElement('option');
                        o.value = c.id;
                        o.textContent = c.label + (c.points ? ' - ' + c.points : '') + ' - moy. min. ' + fmt(c.avg_min);
                        o.selected = String(c.id) === String(current) || (!current && known[mode] === c.code);
                        parent.appendChild(o);
                    });
                });
            };
            // Points à jouer repris du barème de la catégorie ; uniquement sur action de l'utilisateur,
            // pour ne pas écraser la distance enregistrée d'un match qu'on modifie.
            const distance = sel.form.querySelector('[name="' + sel.dataset.sbCategory + '_distance"]');
            const applyPoints = function () {
                // Par sets, le champ contient les points d'un set : le barème de la catégorie ne s'applique pas
                const setsOn = document.getElementById('sets_enabled');
                if (!distance || (setsOn && setsOn.checked)) return;
                const cat = CAT.list.find((c) => String(c.id) === sel.value);
                if (cat && cat.points > 0) {
                    distance.value = cat.points;
                    distance.dataset.sbAuto = '1';
                    if (sel.dataset.sbCategory !== 'p1') distance.dataset.sbTouched = '1';
                } else if (distance.dataset.sbAuto) {
                    // Plus de catégorie : on ne laisse pas une distance d'une autre discipline
                    const d1 = sel.form.querySelector('[name="p1_distance"]');
                    distance.value = sel.dataset.sbCategory !== 'p1' && d1 ? d1.value : '';
                    delete distance.dataset.sbAuto;
                    if (sel.dataset.sbCategory !== 'p1') delete distance.dataset.sbTouched;
                } else {
                    return;
                }
                distance.dispatchEvent(new Event('input'));
            };
            const refreshAndApply = () => { refresh(true); applyPoints(); };
            const nameInput = sel.form.querySelector('[name="' + sel.dataset.sbCategory + '_last_name"]');
            if (nameInput) {
                nameInput.addEventListener('sb:player', function (e) { known = e.detail || {}; refreshAndApply(); });
            }
            sel.addEventListener('change', applyPoints);
            discipline.addEventListener('change', refreshAndApply);
            format.addEventListener('change', refreshAndApply);
            refresh(false);
        });
    }

    // Jeu par sets (3 bandes uniquement) : reprises inégales imposées, pas de limite de reprises ;
    // le champ des points de chaque joueur devient « Points par set » (ils peuvent être différents)
    const setsCb = document.getElementById('sets_enabled');
    const setsBlock = document.querySelector('[data-sb-sets-block]');
    if (setsCb && setsBlock && discipline) {
        const unequal = document.getElementById('unequal_innings');
        const limitCb = document.getElementById('innings_limit_enabled');
        let setsWas = setsCb.checked;
        const applySets = function () {
            const allowed = discipline.value === '3bandes';
            setsBlock.style.display = allowed ? '' : 'none';
            if (!allowed && setsCb.checked) {
                setsCb.checked = false;
                setsCb.dispatchEvent(new Event('change'));
                return;
            }
            const on = setsCb.checked;
            if (unequal) {
                if (on) unequal.checked = true;
                unequal.disabled = on;
            }
            if (limitCb) {
                if (on && limitCb.checked) {
                    limitCb.checked = false;
                    limitCb.dispatchEvent(new Event('change'));
                }
                limitCb.disabled = on;
            }
            document.querySelectorAll('[data-sb-timeouts-label]').forEach(function (el) {
                el.textContent = on ? 'time-outs / joueur / set' : 'time-outs / joueur';
            });
            document.querySelectorAll('[data-sb-distance-label]').forEach(function (el) {
                if (!el.dataset.sbDefault) el.dataset.sbDefault = el.textContent;
                el.textContent = on ? 'Points par set' : el.dataset.sbDefault;
            });
            // Bascule faite par l'utilisateur : points d'un set par défaut, et retour aux points d'avant si on décoche
            if (on !== setsWas) {
                document.querySelectorAll('input[data-sb-set-default]').forEach(function (i) {
                    if (on) { i.dataset.sbBefore = i.value; i.value = i.dataset.sbSetDefault; }
                    else if (i.dataset.sbBefore !== undefined) { i.value = i.dataset.sbBefore; delete i.dataset.sbBefore; }
                });
                setsWas = on;
            }
        };
        setsCb.addEventListener('change', applySets);
        discipline.addEventListener('change', applySets);
        applySets();
    }

    if (discipline && limit) {
        discipline.addEventListener('change', function () {
            if (discipline.value === '3bandes' && discipline.dataset.defaultLimit3c) {
                limit.value = discipline.dataset.defaultLimit3c;
            }
        });
    }
})();
