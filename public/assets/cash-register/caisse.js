/* Caisse du bar — une seule page : ouverture du service, caisse, nouveau ticket (clavier intégré),
   tickets en cours, historique, clôture, et le bandeau d'appel des billards toujours visible. */
(function () {
    'use strict';

    const C = window.CAISSE;
    const $ = id => document.getElementById(id);
    const eur = n => (Math.round(n * 100) / 100).toFixed(2).replace('.', ',') + ' €';
    const esc = s => String(s ?? '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const plain = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '');
    const cap = s => s.charAt(0).toUpperCase() + s.slice(1);
    // Les noms sont « NOM PRÉNOM » : la saisie peut être le début du nom de famille ou du prénom
    const matchAt = (name, q) => {
        const p = plain(name), k = plain(q);
        if (p.startsWith(k)) return 0;
        const i = p.indexOf(' ' + k);
        return i < 0 ? -1 : i + 1;
    };
    const highlight = (name, q) => {
        const i = q ? matchAt(name, q) : -1;
        if (i < 0) return esc(name);
        return esc(name.slice(0, i)) + '<mark>' + esc(name.slice(i, i + q.length)) + '</mark>' + esc(name.slice(i + q.length));
    };
    const METHODS = { cash: 'espèces', bancontact: 'Bancontact' };
    const PRINT_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="8" rx="2"/><path d="M6 14h12v7H6z"/><circle cx="17.5" cy="12" r=".6" fill="currentColor"/></svg>';

    const products = {};
    C.catalog.forEach(c => c.products.forEach(p => { products[p.id] = p; }));

    const S = {
        mode: null, service: null, tickets: [], current: null, nextNumber: '0001', lastClosed: null,
        cat: C.catalog.length ? C.catalog[0].id : null, clockSkew: 0,
        name: '', bm: '', bmPicked: null, period: null, bartenders: [], accents: false,
        kb: 'bm', fund: '', cl: '', clPicked: null, closers: [], clFund: '',
        closedService: null, nextEnvelope: null,
    };

    // ------------------------------------------------------------------ échanges avec le serveur

    function setNet(ok) {
        $('net').classList.toggle('off', !ok);
        $('net').lastElementChild.textContent = ok ? 'Connectée' : 'Hors ligne';
    }

    // Sans connexion, rien ne peut être enregistré : la caisse est bloquée et le barman passe au papier.
    // Au retour du réseau, l'écran est rechargé depuis le serveur, ce qui retire les touches non enregistrées.
    let offline = false;
    let netFails = 0;

    function goOffline() {
        setNet(false);
        if (offline) return;
        offline = true;
        const d = new Date();
        $('offlineSince').textContent = 'Coupure depuis ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') + ' · nouvel essai toutes les 2 secondes';
        $('toast').hidden = true;
        $('offline').hidden = false;
    }

    function goOnline() {
        netFails = 0;
        setNet(true);
        if (!offline) return;
        offline = false;
        $('offline').hidden = true;
        refresh(true)
            .then(() => toast('Connexion rétablie. Vérifiez le ticket affiché, puis encodez ce qui a été noté sur papier.'))
            .catch(() => {});
    }

    // Un Wi-Fi « muet » ne renvoie jamais d'erreur : sans délai maximal, la caisse attendrait indéfiniment
    async function netFetch(url, opt, ms) {
        const ctl = new AbortController();
        const timer = setTimeout(() => ctl.abort(), ms);
        try {
            return await fetch(url, { ...opt, signal: ctl.signal });
        } finally {
            clearTimeout(timer);
        }
    }

    async function api(path, body) {
        const opt = { cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } };
        if (body !== undefined) {
            opt.method = 'POST';
            opt.headers['Content-Type'] = 'application/json';
            opt.body = JSON.stringify(body);
        }
        let r;
        try {
            r = await netFetch(C.api + path, opt, 10000);
        } catch (e) {
            goOffline();
            throw e;
        }
        goOnline();
        const d = await r.json().catch(() => ({}));
        if (!r.ok) {
            const msg = d.error || ('Erreur ' + r.status + ' : réessayez.');
            toast(msg, true);
            throw new Error(msg);
        }
        return d;
    }

    async function refresh(keepMode) {
        const d = await api('etat');
        S.service = d.service;
        S.tickets = d.tickets;
        S.nextNumber = d.nextNumber;
        S.lastClosed = d.lastClosed || null;
        S.nextEnvelope = d.nextEnvelope || null;
        S.clockSkew = d.nowTs - Math.floor(Date.now() / 1000);
        if (S.current && !S.tickets.some(t => t.id === S.current)) S.current = null;
        renderHeader();
        renderCounts();
        if (!S.service) {
            if (S.mode !== 'close') setMode('open');
        } else if (!keepMode || !S.mode || S.mode === 'open') {
            setMode('ticket');
        } else {
            setMode(S.mode);
        }
    }

    // ------------------------------------------------------------------ navigation

    const SCREENS = {
        open:    ['kb', 'sideOpen'],
        ticket:  ['left', 'sideTicket'],
        new:     ['kb', 'sideNew'],
        closer:  ['kb', 'sideCloser'],
        list:    ['list'],
        history: ['history'],
        close:   ['close'],
    };

    function setMode(m) {
        if (m !== 'open' && m !== 'close' && !S.service) m = 'open';
        S.mode = m;
        ['kb', 'sideOpen', 'sideCloser', 'left', 'sideTicket', 'sideNew', 'list', 'history', 'close'].forEach(id => { $(id).hidden = !SCREENS[m].includes(id); });
        S.accents = false;
        if (m === 'open') enterOpen();
        if (m === 'ticket') { renderCats(); renderGrid(); renderTicket(); }
        if (m === 'new') { S.name = ''; renderKeys(); renderName(); }
        if (m === 'list') { renderList(); loadPaid(); }
        if (m === 'history') loadHistory();
        if (m === 'close') loadClose();
        if (m === 'closer') enterCloser();
    }

    document.addEventListener('click', e => {
        const b = e.target.closest('[data-go]');
        if (b) setMode(b.dataset.go);
    });

    function renderHeader() {
        $('hdSvc').textContent = S.service ? cap(S.service.label) + ' · Enveloppe ' + S.service.envelope : 'Aucun service ouvert';
    }

    function renderCounts() {
        document.querySelectorAll('[data-count]').forEach(el => {
            el.textContent = S.tickets.length;
            el.classList.toggle('zero', !S.tickets.length);
        });
        // Même règle que le serveur : pas de clôture tant qu'un ticket est ouvert
        const canClose = !S.tickets.length;
        $('btnGoClose').disabled = !canClose;
        $('listNote').classList.toggle('ok', canClose);
        $('listNote').lastElementChild.textContent = canClose
            ? 'Aucun ticket ouvert : tous les tickets sont réglés ou annulés.'
            : 'Chaque ticket ouvert doit être réglé ou annulé avant la clôture du service.';
    }

    function tickClock() {
        const d = new Date();
        const day = d.toLocaleDateString('fr-BE', { weekday: 'long', day: 'numeric', month: 'long' });
        $('clock').textContent = cap(day) + ' · ' + d.toLocaleTimeString('fr-BE', { hour: '2-digit', minute: '2-digit' });
    }

    // ------------------------------------------------------------------ messages et confirmations

    let toastTimer = null;
    function toast(text, isError, action) {
        $('toastText').textContent = text;
        $('toast').classList.toggle('err', !!isError);
        const a = $('toastAction');
        a.hidden = !action;
        if (action) { a.textContent = action.label; a.onclick = action.run; }
        $('toast').hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { $('toast').hidden = true; }, action ? 7000 : 4000);
    }

    function confirmBox(title, text, yesLabel, withReasons) {
        return new Promise(resolve => {
            $('mTitle').textContent = title;
            $('mText').textContent = text;
            $('mYes').textContent = yesLabel;
            const reasons = $('mReasons');
            reasons.hidden = !withReasons;
            let reason = null;
            reasons.querySelectorAll('.btn').forEach(b => {
                b.setAttribute('aria-pressed', 'false');
                b.onclick = () => {
                    reasons.querySelectorAll('.btn').forEach(x => x.setAttribute('aria-pressed', String(x === b)));
                    reason = b.textContent;
                };
            });
            $('modal').hidden = false;
            const done = ok => { $('modal').hidden = true; resolve(ok ? { reason } : null); };
            $('mYes').onclick = () => done(true);
            $('mNo').onclick = () => done(false);
        });
    }

    // ------------------------------------------------------------------ clavier intégré

    const ROWS = [['A','Z','E','R','T','Y','U','I','O','P'], ['Q','S','D','F','G','H','J','K','L','M'], ['W','X','C','V','B','N', "'", '-']];
    const ACCENTS = ['É','È','Ê','Ë','Ç','Ï','Î','Ô','Û','Ü'];

    const numpad = () => (S.mode === 'open' && S.kb === 'fund') || (S.mode === 'closer' && S.kb === 'clFund');

    function renderKeys() {
        const closing = S.mode === 'closer';
        $('kbTitle').textContent = numpad() ? (closing ? 'Fond de caisse laissé dans la caisse (en euros)' : 'Fond de caisse compté (en euros)')
            : S.mode === 'open' ? 'Qui ouvre ?' : closing ? 'Qui clôture ?' : 'Nom du client';
        $('kbTitle').nextElementSibling.textContent = !numpad() ? 'Majuscules uniquement · la recherche ignore les accents'
            : closing ? 'Retire les espèces attendues pour l’enveloppe, puis compte ce qui reste' : 'Compte les billets et les pièces de la caisse, puis encode le total';
        const k = (label, cls = '', act = label) => `<button class="key ${cls}" type="button" data-k="${esc(act)}">${esc(label)}</button>`;
        $('keys').classList.toggle('numpad', numpad());
        if (numpad()) {
            $('keys').innerHTML = [['7', '8', '9'], ['4', '5', '6'], ['1', '2', '3'], [',', '0', '⌫']]
                .map(r => `<div class="krow">${r.map(c => c === '⌫' ? k(c, 'fn', 'BACK') : k(c)).join('')}</div>`).join('');
            return;
        }
        $('keys').innerHTML =
            `<div class="krow">${(S.accents ? ACCENTS : ROWS[0]).map(c => k(c)).join('')}</div>` +
            `<div class="krow">${ROWS[1].map(c => k(c)).join('')}</div>` +
            `<div class="krow">${ROWS[2].map(c => k(c)).join('')}${k('⌫', 'fn span2', 'BACK')}</div>` +
            `<div class="krow">${k('É È Ç', 'fn span2' + (S.accents ? ' on' : ''), 'ACC')}${k('Espace', 'space span5', ' ')}${k(S.mode === 'new' ? 'Annuler' : 'Effacer', 'cancel span3', 'CANCEL')}</div>`;
    }

    $('keys').addEventListener('click', e => {
        const b = e.target.closest('.key');
        if (!b) return;
        const k = b.dataset.k;
        if (numpad()) { typeFund(k); return; }
        if (k === 'ACC') { S.accents = !S.accents; renderKeys(); return; }
        const field = S.mode === 'open' ? 'bm' : S.mode === 'closer' ? 'cl' : 'name';
        if (k === 'CANCEL') {
            if (S.mode === 'open') { S.bm = ''; S.bmPicked = null; renderBartenders(); }
            else if (S.mode === 'closer') { S.cl = ''; S.clPicked = null; renderClosers(); }
            else setMode('ticket');
            return;
        }
        let v = S[field];
        if (k === 'BACK') v = v.slice(0, -1);
        else if (k === ' ') { if (v && !v.endsWith(' ')) v += ' '; }
        else if (v.length < 40) { v += k; if (S.accents) { S.accents = false; renderKeys(); } }
        S[field] = v;
        if (S.mode === 'open') { S.bmPicked = null; renderBartenders(); }
        else if (S.mode === 'closer') { S.clPicked = null; renderClosers(); }
        else renderName();
    });

    // Un long nom rapetisse (et passe à la ligne au besoin) au lieu de déborder de la colonne
    function sizeName(box, text) {
        box.classList.toggle('long', text.length > 14 && text.length <= 22);
        box.classList.toggle('xlong', text.length > 22);
    }

    // ------------------------------------------------------------------ ouverture du service

    function enterOpen() {
        const now = new Date();
        const hm = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        if (!S.period) S.period = hm >= C.eveningFrom ? 'soir' : 'am';
        $('openDate').textContent = cap(now.toLocaleDateString('fr-BE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
        const lc = S.lastClosed;
        $('lastClosed').hidden = !lc;
        if (lc) {
            const when = lc.today ? 'à ' + lc.closedAt : 'le ' + lc.date.split('-').reverse().slice(0, 2).join('/');
            $('lastClosed').lastElementChild.textContent = cap(lc.label) + ' (' + lc.envelope + ') clôturé ' + when + (lc.closer ? ' par ' + lc.closer : '') + '.';
        }
        S.bm = ''; S.bmPicked = null; S.fund = ''; S.kb = 'bm';
        renderKeys();
        renderPeriod();
        loadBartenders();
    }

    function renderPeriod() {
        $('seg').querySelectorAll('.btn').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.p === S.period)));
        // Numéro = rang du service dans la journée (01 pour le premier ouvert, après-midi ou soir)
        $('envCode').textContent = S.nextEnvelope || '—';
    }

    $('seg').addEventListener('click', e => {
        const b = e.target.closest('.btn');
        if (!b) return;
        S.period = b.dataset.p;
        renderPeriod();
        loadBartenders();
    });

    async function loadBartenders() {
        renderBartenders();
        try {
            S.bartenders = await api('barmans?period=' + S.period);
        } catch (e) { S.bartenders = []; }
        renderBartenders();
    }

    // Saisie d'un détenteur de clé, commune à « Qui ouvre ? » et « Qui clôture ? »
    function renderPicker(o) {
        o.box.innerHTML = (o.text ? esc(o.text) : '<span class="ph">Ton nom ou ton prénom…</span>') + (o.active ? '<i class="caret"></i>' : '');
        o.box.classList.toggle('idle', !o.active);
        sizeName(o.box, o.text);
        const q = plain(o.text.trim());
        let hits, found;
        if (o.picked && o.text === o.picked.name) {
            hits = [o.picked];
            found = 'C’est bien toi ? Sinon, efface et tape ton nom.';
        } else if (q.length >= 2) {
            hits = o.list.map(b => [matchAt(b.name, q), b]).filter(([i]) => i >= 0)
                .sort((x, y) => (x[0] > 0) - (y[0] > 0)).map(([, b]) => b).slice(0, 4);
            found = hits.length ? 'Membres détenteurs d’une clé · touche ton nom' : 'Aucun détenteur de clé à ce nom.';
        } else {
            hits = o.list.filter(o.isDefault);
            found = hits.length ? o.defaultLabel : 'Tape les premières lettres de ton nom ou de ton prénom.';
        }
        o.found.textContent = found;
        o.sugg.hidden = !hits.length;
        const typed = q.length >= 2 && !(o.picked && o.text === o.picked.name) ? o.text.trim() : '';
        o.sugg.innerHTML = hits.map(b => `<button class="sg${o.picked && o.picked.id === b.id ? ' sel' : ''}" type="button" data-id="${b.id}">` +
            `<span>${highlight(b.name, typed)}</span>${o.tag(b) ? '<span class="mb">' + o.tag(b) + '</span>' : ''}</button>`).join('');
    }

    function renderBartenders() {
        renderPicker({
            box: $('bmBox'), found: $('bmFound'), sugg: $('bmSugg'), text: S.bm, picked: S.bmPicked, list: S.bartenders,
            active: S.kb === 'bm', isDefault: b => b.planned, defaultLabel: 'Prévu au planning des permanences :',
            tag: b => b.planned ? 'prévu au planning' : '',
        });
        renderFund();
    }

    // Fond de caisse : compté par celui qui ouvre (S.fund) et par celui qui clôture (S.clFund), même saisie
    const amount = s => (/^\d+(,\d{1,2})?$/.test(s) ? parseFloat(s.replace(',', '.')) : null);
    const fundValue = () => amount(S.fund);

    function renderFundBox(text, active, label, box, found, labelText) {
        const v = amount(text);
        label.textContent = labelText + ' · attendu ' + eur(C.openingFloat).replace(',00', '');
        box.innerHTML = (text ? esc(text) + ' €' : '<span class="ph">Touche ici, compte la caisse et encode le montant…</span>') + (active ? '<i class="caret"></i>' : '');
        box.classList.toggle('idle', !active);
        const ok = v !== null && Math.abs(v - C.openingFloat) < 0.005;
        found.className = 'found' + (v === null ? '' : ok ? ' good' : ' warn');
        found.textContent = v === null ? '' : ok ? 'Fond de caisse complet.'
            : 'Différent de ' + eur(C.openingFloat) + ' : recompte, et si c’est bien ce montant, préviens le trésorier.';
        return v;
    }

    function renderFund() {
        const v = renderFundBox(S.fund, S.kb === 'fund', $('fundLabel'), $('fundBox'), $('fundFound'), '2 · Fond de caisse compté');
        const ready = S.bmPicked && v !== null;
        $('btnOpen').disabled = !ready;
        $('btnOpen').innerHTML = 'Ouvrir le service' + (ready ? '<small>' + esc(S.bmPicked.name) + ' · fond de caisse ' + eur(v) + '</small>'
            : '<small>' + (S.bmPicked ? 'Encode le fond de caisse' : 'Choisis ton nom puis encode le fond de caisse') + '</small>');
    }

    function typeFund(k) {
        const field = S.mode === 'closer' ? 'clFund' : 'fund';
        let v = S[field];
        if (k === 'BACK') v = v.slice(0, -1);
        else if (k === ',') { if (v && !v.includes(',')) v += ','; }
        else if (/^\d$/.test(k)) {
            const dec = v.includes(',') ? v.split(',')[1].length : -1;
            if (dec < 2 && v.replace(',', '').length < 7) v = (v === '0' ? '' : v) + k;
        }
        S[field] = v;
        if (S.mode === 'closer') renderClosers(); else renderFund();
    }

    function setKb(target) {
        if (S.kb === target) return;
        S.kb = target;
        S.accents = false;
        renderKeys();
        if (S.mode === 'closer') renderClosers(); else renderBartenders();
    }

    $('bmBox').addEventListener('click', () => setKb('bm'));
    $('fundBox').addEventListener('click', () => setKb('fund'));

    $('bmSugg').addEventListener('click', e => {
        const b = e.target.closest('.sg');
        if (!b) return;
        S.bmPicked = S.bartenders.find(x => x.id === +b.dataset.id) || null;
        if (S.bmPicked) S.bm = S.bmPicked.name;
        if (S.bmPicked && !S.fund) { setKb('fund'); return; }
        renderBartenders();
    });

    $('btnOpen').addEventListener('click', async () => {
        const fund = fundValue();
        if (!S.bmPicked || fund === null) return;
        $('btnOpen').disabled = true;
        try {
            await api('service', { period: S.period, member_id: S.bmPicked.id, opening_float: fund });
            S.period = null;
            await refresh(false);
            toast('Service ouvert. Bon service !');
        } catch (e) { renderBartenders(); }
    });

    // ------------------------------------------------------------------ caisse

    const currentTicket = () => S.tickets.find(t => t.id === S.current) || null;

    function renderCats() {
        $('cats').innerHTML = C.catalog.map(c =>
            `<button class="cat" type="button" aria-pressed="${c.id === S.cat}" data-c="${c.id}">${esc(c.name)}</button>`).join('');
    }

    $('cats').addEventListener('click', e => {
        const b = e.target.closest('.cat');
        if (!b) return;
        S.cat = +b.dataset.c;
        renderCats();
        renderGrid();
    });

    function renderGrid() {
        const cat = C.catalog.find(c => c.id === S.cat);
        const t = currentTicket();
        const qty = {};
        if (t) t.lines.forEach(l => { qty[l.productId] = l.qty; });
        const bySlot = {};
        (cat ? cat.products : []).forEach(p => { bySlot[p.position] = p; });
        let html = '';
        for (let i = 1; i <= C.slots; i++) {
            const p = bySlot[i];
            html += p
                ? `<button class="prod" type="button" data-p="${p.id}"><span class="nm">${esc(p.name)}</span><span class="pr">${eur(p.price)}</span>${qty[p.id] ? `<span class="q">${qty[p.id]}</span>` : ''}</button>`
                : '<div class="slot"></div>';
        }
        $('grid').innerHTML = html;
        $('grid').classList.toggle('dim', !t);
    }

    $('grid').addEventListener('click', e => {
        const b = e.target.closest('.prod');
        if (!b) return;
        if (!currentTicket()) {
            toast('Ouvrez d’abord un ticket : « Créer un nouveau ticket » ou « Tickets en cours ».', true);
            return;
        }
        changeLine(+b.dataset.p, 1);
    });

    function renderTicket() {
        const t = currentTicket();
        if (t) {
            $('tk').className = 'tk';
            $('tk').innerHTML = `<small>Ticket n° ${t.number} · ouvert à ${t.openedAt}</small><strong>${esc(t.client)}</strong>`;
        } else {
            $('tk').className = 'tk empty';
            $('tk').innerHTML = '<small>Aucun ticket affiché</small><strong>Crée un nouveau ticket, ou choisis-en un dans « Tickets en cours ».</strong>';
        }
        const lines = t ? t.lines : [];
        $('lines').innerHTML = lines.map(l => `<div class="ln"><div><b>${esc(l.name)}</b><small>${eur(l.price)} l'unité</small></div>
            <button class="pm" type="button" data-p="${l.productId}" data-d="-1" aria-label="Retirer">−</button>
            <span class="qty">${l.qty}</span>
            <button class="pm" type="button" data-p="${l.productId}" data-d="1" aria-label="Ajouter">+</button>
            <span class="tot">${eur(l.price * l.qty)}</span></div>`).join('');
        const count = lines.reduce((s, l) => s + l.qty, 0);
        $('count').textContent = count + (count > 1 ? ' articles' : ' article');
        const total = t ? t.total : 0;
        $('total').textContent = eur(total);
        $('btnValidate').textContent = 'Payer · ' + eur(total);
        $('btnValidate').disabled = !t || !lines.length;
        $('btnCancel').disabled = !t;
        // Des barmans touchaient « Valider » après chaque boisson et clôturaient le ticket :
        // on les pousse vers « Tickets en cours » dès qu'il y a une consommation
        $('nextHint').classList.toggle('off', !lines.length);
        $('btnList').classList.toggle('nudge', !!lines.length);
    }

    $('lines').addEventListener('click', e => {
        const b = e.target.closest('.pm');
        if (b) changeLine(+b.dataset.p, +b.dataset.d);
    });

    // Les touches s'enchaînent vite : on affiche tout de suite, et les requêtes partent une à une
    let queue = Promise.resolve();
    let pending = 0;

    function recalc(t) {
        t.total = Math.round(t.lines.reduce((s, l) => s + l.price * l.qty, 0) * 100) / 100;
    }

    function replaceTicket(fresh) {
        const i = S.tickets.findIndex(t => t.id === fresh.id);
        if (i >= 0) S.tickets[i] = fresh;
        if (S.mode === 'ticket') { renderGrid(); renderTicket(); }
    }

    function changeLine(productId, delta) {
        const t = currentTicket();
        if (!t) return;
        const l = t.lines.find(x => x.productId === productId);
        if (l) {
            l.qty += delta;
            if (l.qty <= 0) t.lines.splice(t.lines.indexOf(l), 1);
        } else if (delta > 0) {
            const p = products[productId];
            if (!p) return;
            t.lines.push({ productId, name: p.name, price: p.price, qty: 1 });
        } else {
            return;
        }
        recalc(t);
        renderGrid();
        renderTicket();
        pending++;
        queue = queue
            .then(() => api('tickets/' + t.id + '/ligne', { product_id: productId, delta }))
            .then(d => { pending--; if (!pending) replaceTicket(d.ticket); })
            .catch(() => { pending--; return refresh(true).catch(() => {}); });
    }

    // Le ticket n'est réglé qu'au choix du mode de paiement dans la fenêtre, jamais d'un seul geste
    function payBox(t) {
        return new Promise(resolve => {
            $('pTitle').textContent = 'Payer le ticket n° ' + t.number;
            $('pText').textContent = t.client + ' · ' + eur(t.total) + '. Comment le client paie-t-il ?';
            $('payModal').hidden = false;
            const done = m => { $('payModal').hidden = true; resolve(m); };
            $('payModal').querySelectorAll('[data-m]').forEach(b => { b.onclick = () => done(b.dataset.m); });
            $('pNo').onclick = () => done(null);
        });
    }

    $('btnValidate').addEventListener('click', async () => {
        const t = currentTicket();
        if (!t) return;
        $('btnValidate').disabled = true;
        await queue;
        const method = await payBox(t);
        if (!method) { renderTicket(); return; }
        try {
            const d = await api('tickets/' + t.id + '/valider', { method });
            S.tickets = S.tickets.filter(x => x.id !== t.id);
            S.current = null;
            renderCounts();
            // Après un paiement, le barman sert en général un autre client déjà en cours
            setMode('list');
            toast('Ticket n° ' + d.ticket.number + ' réglé : ' + eur(d.ticket.total) + ' en ' + METHODS[d.ticket.method] + '.');
        } catch (e) { renderTicket(); }
    });

    $('btnCancel').addEventListener('click', () => {
        const t = currentTicket();
        if (t) cancelTicket(t);
    });

    async function cancelTicket(t) {
        const ok = await confirmBox('Annuler le ticket n° ' + t.number + ' ?',
            t.client + ' · ' + eur(t.total) + '. Le ticket n’est pas supprimé : il reste dans l’historique, marqué annulé.',
            'Oui, annuler le ticket', true);
        if (!ok) return;
        await queue;
        try {
            await api('tickets/' + t.id + '/annuler', { reason: ok.reason || '' });
            S.tickets = S.tickets.filter(x => x.id !== t.id);
            if (S.current === t.id) S.current = null;
            renderCounts();
            toast('Ticket n° ' + t.number + ' annulé.');
            setMode(S.mode);
        } catch (e) { /* message déjà affiché */ }
    }

    function printTicket(id) {
        window.location.href = C.print + 'ticket/' + id + '/imprimer';
    }

    // ------------------------------------------------------------------ nouveau ticket

    let searchTimer = null;
    let lastQuery = '';
    let hits = [];

    function renderName() {
        $('newLabel').textContent = 'Nouveau ticket n° ' + S.nextNumber + ' · nom du client';
        $('nameBox').innerHTML = (S.name ? esc(S.name) : '<span class="ph">Nom puis prénom du client…</span>') + '<i class="caret"></i>';
        sizeName($('nameBox'), S.name);
        const q = S.name.trim();
        if (q.length < 2) {
            hits = [];
            lastQuery = '';
            renderSuggestions();
            return;
        }
        clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            try {
                const res = await api('clients?q=' + encodeURIComponent(q));
                if (S.name.trim() !== q) return; // réponse d'une frappe plus ancienne
                hits = res;
                lastQuery = q;
                renderSuggestions();
            } catch (e) { /* message déjà affiché */ }
        }, 250);
    }

    function seen(date) {
        if (!date) return '';
        const d = new Date(date + 'T12:00:00');
        const today = new Date();
        if (d.toDateString() === today.toDateString()) return 'vu aujourd’hui';
        return 'vu le ' + d.toLocaleDateString('fr-BE', { day: 'numeric', month: 'short' });
    }

    function renderSuggestions() {
        const q = S.name.trim();
        if (q.length < 2) {
            $('found').textContent = 'Tape au moins 2 lettres du nom ou du prénom pour voir les clients connus.';
            $('sugg').hidden = true;
            return;
        }
        $('sugg').hidden = false;
        if (lastQuery !== q) {
            $('found').textContent = 'Recherche…';
        } else {
            $('found').textContent = hits.length
                ? hits.length + ' client' + (hits.length > 1 ? 's' : '') + ' trouvé' + (hits.length > 1 ? 's' : '') + ' · touche-en un ou crée-le'
                : 'Aucun client connu à ce nom.';
        }
        const exact = hits.some(h => plain(h.name) === plain(q.toUpperCase()));
        $('sugg').innerHTML = hits.slice(0, 5).map(h => {
            return `<button class="sg" type="button" data-id="${h.id}"><span>${highlight(h.name, q.toUpperCase())}${h.member ? '<span class="mb">membre</span>' : ''}</span><small>${seen(h.lastSeen)}</small></button>`;
        }).join('') + (exact ? '' : `<button class="sg create" type="button" data-create="1">+ Créer « ${esc(q.toUpperCase())} » comme nouveau client</button>`);
    }

    const REASONS = {
        inverse: 'mêmes mots, dans l’autre ordre',
        proche: 'une lettre de différence',
        abrege: 'prénom abrégé',
        identique: 'même nom, aux accents ou tirets près',
    };

    // Avant de créer un nouveau client : « c'est peut-être lui ? », nom remis dans l'ordre NOM PRÉNOM, nom d'un seul mot.
    // Retourne le corps de la requête de création, ou null si le barman revient à la saisie.
    async function confirmNewClient(name) {
        let d;
        try { d = await api('clients/verifier?name=' + encodeURIComponent(name)); }
        catch (e) { return { name }; } // la vérification est une aide : elle ne doit pas bloquer le service
        if (!d.suspects.length && !d.swapped && !d.single) return { name };

        const typed = name.toUpperCase();
        const choices = d.suspects.map(c => ({
            cls: 'pick', body: { client_id: c.id },
            html: `<span>C’est <b>${esc(c.name)}</b>${c.member ? ' <span class="mb">membre</span>' : ''}</span><small>${REASONS[c.reason] || ''}</small>`,
        }));
        if (d.swapped) choices.push({
            cls: 'pick', body: { name: d.swapped },
            html: `<span>Créer <b>${esc(d.swapped)}</b></span><small>nom de famille d’abord</small>`,
        });
        if (d.single) choices.push({ cls: '', body: null, html: '<span>Compléter le nom</span><small>ajouter le prénom</small>' });
        choices.push({ cls: '', body: { name }, html: `<span>Créer <b>${esc(typed)}</b> tel quel</span><small>nouveau client</small>` });
        choices.push({ cls: 'back', body: null, html: 'Revenir' });

        $('kTitle').textContent = d.suspects.length ? 'Ce client existe peut-être déjà'
            : d.swapped ? 'Le nom de famille d’abord ?' : 'Nom et prénom ?';
        $('kText').textContent = d.suspects.length ? '« ' + typed + ' » ressemble à un client connu. Touche le bon client pour ne pas créer de doublon.'
            : d.swapped ? '« ' + typed + ' » semble commencer par le prénom. Les clients s’écrivent NOM puis prénom.'
            : 'Un seul mot : ajoute le prénom si tu le connais, pour retrouver ce client facilement la prochaine fois.';
        $('kChoices').innerHTML = choices.map((c, i) => `<button class="btn ${c.cls}" type="button" data-i="${i}">${c.html}</button>`).join('');

        return new Promise(resolve => {
            $('clientModal').hidden = false;
            $('kChoices').onclick = e => {
                const b = e.target.closest('[data-i]');
                if (!b) return;
                $('clientModal').hidden = true;
                resolve(choices[+b.dataset.i].body);
            };
        });
    }

    $('sugg').addEventListener('click', async e => {
        const b = e.target.closest('.sg');
        if (!b || b.disabled) return;
        b.disabled = true;
        const body = b.dataset.create ? await confirmNewClient(S.name.trim()) : { client_id: +b.dataset.id };
        if (!body) { b.disabled = false; return; }
        try {
            const d = await api('tickets', body);
            S.tickets.push(d.ticket);
            S.current = d.ticket.id;
            S.nextNumber = String(parseInt(d.ticket.number, 10) + 1).padStart(4, '0');
            renderCounts();
            setMode('ticket');
        } catch (err) { b.disabled = false; }
    });

    // ------------------------------------------------------------------ tickets en cours

    const cardsEl = $('cards'); // retiré du DOM quand la liste est vide, on garde la référence

    // Tickets payés de ce service, affichés sous les tickets ouverts pour une vue de toute la journée
    let paidTickets = [];

    async function loadPaid() {
        try {
            const d = await api('historique');
            paidTickets = d.tickets.filter(t => t.status === 'paid');
        } catch (e) { return; }
        if (S.mode === 'list') renderList();
    }

    function renderList() {
        const n = S.tickets.length;
        const np = paidTickets.length;
        $('listSub').textContent = (n ? n + ' ticket' + (n > 1 ? 's' : '') + ' ouvert' + (n > 1 ? 's' : '') : 'Aucun ticket ouvert') +
            (np ? ' · ' + np + ' déjà payé' + (np > 1 ? 's' : '') : '') + ' · touche « Ouvrir » pour ajouter des consommations';
        const now = Math.floor(Date.now() / 1000) + S.clockSkew;
        const cards = cardsEl;
        cards.classList.toggle('few', n <= 4 && !np);
        if (!n && !np) {
            cards.innerHTML = '';
            $('cardsWrap').innerHTML = '<div class="empty-state" style="height:100%">Aucun ticket ouvert.<br>Touche « Créer un nouveau ticket » pour servir un client.</div>';
            return;
        }
        if (!$('cardsWrap').contains(cards)) { $('cardsWrap').innerHTML = ''; $('cardsWrap').appendChild(cards); }
        const open = S.tickets.map(t => {
            const count = t.lines.reduce((s, l) => s + l.qty, 0);
            const cur = t.id === S.current;
            const late = now - t.openedTs > C.lateAfter * 60;
            const flag = cur ? 'Affiché à la caisse' : late ? 'Ouvert depuis plus d’une heure' : '';
            return `<div class="card${cur ? ' current' : late ? ' late' : ''}">
                <div class="card-top"><div style="min-width:0"><small class="label">Ticket n° ${t.number} · ouvert à ${t.openedAt}</small><strong>${esc(t.client)}</strong><span class="flag">${flag}</span></div>
                <div class="amt"><b>${eur(t.total)}</b><span>${count} article${count > 1 ? 's' : ''}</span></div></div><div></div>
                <div class="card-btns"><button class="btn primary" type="button" data-open="${t.id}">Ouvrir</button><button class="btn" type="button" data-cancel="${t.id}">Annuler le ticket</button></div></div>`;
        });
        if (!n) open.push('<div class="cards-none">Aucun ticket ouvert : touche « Créer un nouveau ticket » pour servir un client.</div>');
        const paid = paidTickets.map(t => {
            const count = t.lines.reduce((s, l) => s + l.qty, 0);
            const items = t.lines.map(l => l.qty + ' × ' + l.name).join(', ');
            return `<div class="card paid">
                <div class="card-top"><div style="min-width:0"><small class="label">Ticket n° ${t.number} · ouvert à ${t.openedAt}</small><strong>${esc(t.client)}</strong><span class="flag"><span class="paid-tag">Payé</span>${t.method === 'cash' ? 'en espèces' : 'par Bancontact'} à ${t.paidAt}</span></div>
                <div class="amt"><b>${eur(t.total)}</b><span>${count} article${count > 1 ? 's' : ''}</span></div></div>
                <div class="paid-foot"><div class="items">${esc(items)}</div>
                <button class="btn print" type="button" data-print="${t.id}" aria-label="Imprimer le ticket" title="Imprimer le ticket${C.ticketPrinter ? '' : ' (pas encore d’imprimante à tickets)'}"${C.ticketPrinter ? '' : ' disabled'}>${PRINT_ICON}</button></div></div>`;
        });
        cards.innerHTML = open.join('') + (np ? `<div class="cards-sep">Déjà payés sur ce service <span>${np}</span></div>` + paid.join('') : '');
    }

    $('list').addEventListener('click', e => {
        const o = e.target.closest('[data-open]');
        if (o) { S.current = +o.dataset.open; setMode('ticket'); return; }
        const pr = e.target.closest('[data-print]');
        if (pr) { printTicket(+pr.dataset.print); return; }
        const c = e.target.closest('[data-cancel]');
        if (c) { const t = S.tickets.find(x => x.id === +c.dataset.cancel); if (t) cancelTicket(t); }
    });

    // ------------------------------------------------------------------ historique du service

    async function loadHistory() {
        $('histWrap').innerHTML = '<div class="empty-state" style="height:100%">Chargement…</div>';
        let d;
        try { d = await api('historique'); } catch (e) { return; }
        const paid = d.tickets.filter(t => t.status === 'paid').length;
        const cancelled = d.tickets.length - paid;
        $('histSub').textContent = paid + ' ticket' + (paid > 1 ? 's' : '') + ' réglé' + (paid > 1 ? 's' : '') + ' · ' + cancelled + ' annulé' + (cancelled > 1 ? 's' : '');
        if (!d.tickets.length) {
            $('histWrap').innerHTML = '<div class="empty-state" style="height:100%">Aucun ticket réglé ou annulé pour l’instant.</div>';
            return;
        }
        $('histWrap').innerHTML = '<table class="hist"><thead><tr><th>N°</th><th>Heure</th><th>Client</th><th>Consommations</th><th>Paiement</th><th class="r">Montant</th><th></th></tr></thead><tbody>' +
            d.tickets.map(t => {
                const x = t.status === 'cancelled';
                const items = t.lines.map(l => l.qty + ' × ' + l.name).join(', ');
                return `<tr class="${x ? 'x' : ''}"><td>${t.number}</td><td>${x ? t.cancelledAt : t.paidAt}</td><td class="cl">${esc(t.client)}</td>` +
                    `<td style="font-size:.82rem;color:var(--muted)">${esc(items) || '—'}</td>` +
                    `<td>${x ? 'Annulé' + (t.reason ? ' · ' + esc(t.reason) : '') : cap(METHODS[t.method])}</td><td class="r">${x ? '—' : eur(t.total)}</td>` +
                    `<td class="r">${x || !C.ticketPrinter ? '' : `<button class="btn" type="button" data-print="${t.id}">Imprimer</button>`}</td></tr>`;
            }).join('') + '</tbody></table>';
    }

    $('histWrap').addEventListener('click', e => {
        const b = e.target.closest('[data-print]');
        if (b) printTicket(+b.dataset.print);
    });

    // ------------------------------------------------------------------ clôture

    function fillClose(service, s) {
        $('cPaid').textContent = s.paid;
        $('cCancelled').textContent = s.cancelled;
        $('cTotal').textContent = eur(s.total);
        $('cCash').textContent = eur(s.cash);
        $('cCashN').textContent = s.cash_count + ' ticket' + (s.cash_count > 1 ? 's' : '') + ' · à glisser dans l’enveloppe';
        $('cBc').textContent = eur(s.bancontact);
        $('cBcN').textContent = s.bancontact_count + ' ticket' + (s.bancontact_count > 1 ? 's' : '') + ' · encaissé directement sur le terminal';
        $('cVat').textContent = 'B ' + eur(s.vat_b) + ' · G ' + eur(s.vat_g) + ' · D ' + eur(s.vat_d);
        $('cEnv').textContent = service.envelope;
        const d = new Date(service.date + 'T12:00:00');
        $('cWhen').textContent = cap(d.toLocaleDateString('fr-BE', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })) + ' · ' + service.label + (service.bartender ? ' · ' + service.bartender : '');
        $('cRange').textContent = s.first_number
            ? 'Tickets n° ' + String(s.first_number).padStart(4, '0') + ' à ' + String(s.last_number).padStart(4, '0') + ' · ' + s.first_at.slice(11, 16) + ' → ' + s.last_at.slice(11, 16)
            : 'Aucun ticket sur ce service.';
        $('cExpected').textContent = eur(s.cash);
        const note = $('cNote');
        note.classList.toggle('ok', !s.open);
        note.lastElementChild.textContent = s.open
            ? 'Il reste ' + s.open + ' ticket' + (s.open > 1 ? 's' : '') + ' ouvert' + (s.open > 1 ? 's' : '') + ' : règle-les ou annule-les avant de clôturer.'
            : 'Aucun ticket ouvert : tous les tickets sont réglés ou annulés.';
        return s;
    }

    async function loadClose() {
        if (S.closedService) return; // écran « service clôturé » : on ne recharge pas
        $('btnClose').hidden = false;
        $('btnRecap').hidden = true;
        $('btnCloseBack').textContent = 'Retour aux tickets en cours';
        $('cWarn').textContent = 'Après la clôture, plus aucun ticket ne peut être ajouté, modifié ou supprimé pour ce service.';
        $('cHint').hidden = false;
        $('cHint').textContent = 'Compte la caisse : le fond de caisse de ' + eur(C.openingFloat) + ' y reste. Glisse les espèces attendues et le récapitulatif dans l’enveloppe, puis note son numéro dessus.';
        try {
            const d = await api('service/resume');
            const s = fillClose(d.service, d.summary);
            $('btnClose').disabled = s.open > 0;
            S.closeSummary = s;
        } catch (e) { /* message déjà affiché */ }
    }

    $('btnClose').addEventListener('click', () => setMode('closer'));

    // « Qui clôture ? » : celui qui a ouvert est proposé d'office, un autre détenteur de clé peut prendre le relais
    async function enterCloser() {
        S.accents = false;
        S.clPicked = S.service.bartenderId ? { id: S.service.bartenderId, name: S.service.bartender } : null;
        S.cl = S.clPicked ? S.clPicked.name : '';
        S.clFund = '';
        // Le nom est déjà rempli : on commence par le comptage de la caisse
        S.kb = S.clPicked ? 'clFund' : 'cl';
        $('clSvc').textContent = cap(S.service.label) + ' · ouvert à ' + S.service.openedAt;
        $('clEnv').textContent = S.service.envelope;
        renderKeys();
        renderClosers();
        try {
            S.closers = await api('barmans?period=' + S.service.period);
        } catch (e) { S.closers = []; }
        if (S.clPicked) S.clPicked = S.closers.find(x => x.id === S.clPicked.id) || S.clPicked;
        renderClosers();
    }

    function renderClosers() {
        const opener = S.service ? S.service.bartenderId : null;
        renderPicker({
            box: $('clBox'), found: $('clFound'), sugg: $('clSugg'), text: S.cl, picked: S.clPicked, list: S.closers,
            active: S.kb === 'cl', isDefault: b => b.id === opener, defaultLabel: 'A ouvert le service :',
            tag: b => b.id === opener ? 'a ouvert le service' : '',
        });
        const v = renderFundBox(S.clFund, S.kb === 'clFund', $('clFundLabel'), $('clFundBox'), $('clFundFound'), '2 · Fond de caisse laissé dans la caisse');
        $('btnCloseGo').disabled = !S.clPicked || v === null;
    }

    $('clBox').addEventListener('click', () => setKb('cl'));
    $('clFundBox').addEventListener('click', () => setKb('clFund'));

    $('clSugg').addEventListener('click', e => {
        const b = e.target.closest('.sg');
        if (!b) return;
        S.clPicked = S.closers.find(x => x.id === +b.dataset.id) || S.clPicked;
        if (S.clPicked) S.cl = S.clPicked.name;
        if (S.clPicked && !S.clFund) { setKb('clFund'); return; }
        renderClosers();
    });

    $('btnCloseGo').addEventListener('click', async () => {
        const fund = amount(S.clFund);
        if (!S.clPicked || fund === null) return;
        const s = S.closeSummary;
        const ok = await confirmBox('Clôturer le service ?',
            'Clôturé par ' + S.clPicked.name + ', fond de caisse laissé : ' + eur(fund) + '. Espèces attendues dans l’enveloppe ' + S.service.envelope + ' : ' + eur(s ? s.cash : 0) + '. Après la clôture, plus aucun ticket ne pourra être ajouté ni modifié.',
            'Oui, clôturer', false);
        if (!ok) return;
        try {
            const d = await api('service/cloturer', { member_id: S.clPicked.id, closing_float: fund });
            S.closedService = d.service;
            S.service = null;
            S.tickets = [];
            S.current = null;
            renderHeader();
            renderCounts();
            setMode('close');
            $('btnClose').hidden = true;
            $('btnRecap').hidden = false;
            $('cHint').hidden = true;
            $('cWarn').textContent = 'Service clôturé à ' + d.service.closedAt + (d.service.closer ? ' par ' + d.service.closer : '') + '. Imprime le récapitulatif et glisse-le dans l’enveloppe avec l’argent.';
            $('btnCloseBack').textContent = 'Terminer';
        } catch (e) { /* message déjà affiché */ }
    });

    $('btnRecap').addEventListener('click', () => {
        if (S.closedService) window.location.href = C.print + 'service/' + S.closedService.id + '/recapitulatif';
    });

    $('btnCloseBack').addEventListener('click', () => {
        if (S.closedService) {
            S.closedService = null;
            refresh(false).catch(() => {});
        } else {
            setMode('list');
        }
    });

    // ------------------------------------------------------------------ bandeau des billards (appel du bar)

    const B = C.bar;
    let tables = [];
    let fetchedAt = 0;
    const beeped = {};
    const served = {};
    let audio = null;

    async function keepAwake() {
        try {
            if ('wakeLock' in navigator && document.visibilityState === 'visible') await navigator.wakeLock.request('screen');
        } catch (e) { /* refusé : la veille dépend alors des réglages de la tablette */ }
    }
    document.addEventListener('visibilitychange', keepAwake);

    // Son, plein écran et anti-veille ne s'obtiennent qu'après un premier toucher
    $('unlock').addEventListener('click', () => {
        try { audio = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) { audio = null; }
        $('unlock').hidden = true;
        if (document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(() => {});
        keepAwake();
    });

    function beep(times) {
        if (!audio) return;
        for (let i = 0; i < times; i++) {
            const t = audio.currentTime + i * 0.35;
            const osc = audio.createOscillator(), gain = audio.createGain();
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.0001, t);
            gain.gain.exponentialRampToValueAtTime(0.6, t + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.25);
            osc.connect(gain).connect(audio.destination);
            osc.start(t);
            osc.stop(t + 0.3);
        }
    }

    function waitFor(call) {
        return call.waiting + Math.floor((Date.now() - fetchedAt) / 1000);
    }

    function renderBand() {
        const tile = t => {
            let cls = 'bt', status = '';
            if (t.call) {
                const w = waitFor(t.call);
                cls += ' called' + (w >= B.redAfter ? ' very-late' : w >= B.orangeAfter ? ' late' : '');
                status = 'Appel · ' + (w < 60 ? 'à l’instant' : Math.floor(w / 60) + ' min');
            }
            return `<button class="${cls}" type="button" data-t="${t.id}"><b>${esc(t.name)}</b><small>${t.call && t.players ? esc(t.players.join(' – ')) : ''}</small><em>${status}</em></button>`;
        };
        // Billards 1-2 (grands) et 3-6 (petits, autre salle) séparés par l'espace d'une case
        $('band').innerHTML = tables.slice(0, 2).map(tile).join('') + '<div></div>' + tables.slice(2, 6).map(tile).join('');
    }

    $('band').addEventListener('click', e => {
        const b = e.target.closest('.bt.called');
        if (!b) return;
        const t = tables.find(x => x.id === +b.dataset.t);
        if (!t || !t.call) return;
        served[t.call.id] = true;
        const id = t.call.id;
        t.call = null;
        renderBand();
        fetch(B.serve + id, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } }).catch(() => {}).then(pollBar);
    });

    function ring() {
        const now = Date.now();
        let times = 0;
        tables.forEach(t => {
            if (!t.call) return;
            const last = beeped[t.call.id];
            if (last === undefined) { beeped[t.call.id] = now; times = 3; }
            else if (now - last >= B.beepEvery * 1000) { beeped[t.call.id] = now; times = Math.max(times, 1); }
        });
        if (times) beep(times);
    }

    function pollBar() {
        netFetch(B.state, { cache: 'no-store', headers: { 'X-Requested-With': 'XMLHttpRequest' } }, 6000)
            .catch(e => {
                // Deux échecs de suite avant de bloquer : un raté isolé du Wi-Fi ne doit pas faire clignoter l'écran rouge
                if (++netFails >= 2 || offline) goOffline();
                throw e;
            })
            .then(r => { goOnline(); if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(d => {
                tables = d.tables;
                tables.forEach(t => { if (t.call && served[t.call.id]) t.call = null; });
                fetchedAt = Date.now();
                renderBand();
                ring();
            })
            .catch(() => setNet(false));
    }

    // ------------------------------------------------------------------ démarrage

    tickClock();
    setInterval(tickClock, 10000);
    pollBar();
    setInterval(pollBar, 2000);
    window.addEventListener('offline', goOffline);
    window.addEventListener('online', pollBar);
    // Rafraîchit la liste des tickets si un autre appareil a travaillé sur le même service
    setInterval(() => {
        if (!pending && $('modal').hidden && $('payModal').hidden && $('clientModal').hidden && ['ticket', 'list'].includes(S.mode)) refresh(true).catch(() => {});
    }, 20000);
    // Retour d'une impression : on rouvre le ticket qui était affiché
    const params = new URLSearchParams(location.search);
    const back = params.get('ticket');
    const backToList = params.get('vue') === 'tickets';
    if (back || backToList) history.replaceState(null, '', location.pathname);
    refresh(false).then(() => {
        if (back && S.tickets.some(t => t.id === +back)) { S.current = +back; setMode('ticket'); }
        else if (backToList && S.service) setMode('list');
    }).catch(() => setMode('open'));
})();
