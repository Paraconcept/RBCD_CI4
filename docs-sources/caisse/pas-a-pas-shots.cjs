// Captures du guide « La caisse pas à pas » (une action par page), avec la position des boutons à toucher.
// Écrit caisse/img-pas/*.png et caisse/img-pas/targets.js (zones en % de l'écran, lues par pas-a-pas.html).
// Les noms affichés sont fictifs. À lancer par pas-a-pas-shots.sh, qui travaille sur une copie de la base.
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const out = path.resolve(__dirname, 'img-pas');
fs.mkdirSync(out, { recursive: true });

const DEMO_CLIENTS = ['DUPONT JEAN', 'DUPONT MARIE', 'DUPUIS LUC', 'LEMAIRE LUC', 'LAMBERT SOPHIE', 'DUBOIS MARC', 'RENARD GUY', 'SIMON DANIEL', 'PEETERS JOS', 'GILLES ROBERT'];
const FAKE_BARMEN = ['MARTIN PAUL', 'MASSON ANNE', 'MATHIEU ANDRÉ'];
const W = 1194, H = 834;

(async () => {
  if (+sql("select count(*) from bar_services where status='open'")) {
    console.error('Un service est ouvert dans la base utilisée : lancez ce script par pas-a-pas-shots.sh.');
    process.exit(1);
  }
  const quoted = DEMO_CLIENTS.map(n => `'${n}'`).join(',');
  // Des clients de démonstration peuvent déjà exister (avec des tickets) : on les réutilise
  for (const n of DEMO_CLIENTS) sql(`insert into bar_clients (name, last_visit_at, created_at, updated_at) select '${n}', now() - interval ${1 + Math.floor(Math.random() * 30)} day, now(), now() from dual where not exists (select 1 from bar_clients where member_id is null and name = '${n}')`);
  const clientId = Object.fromEntries(sql(`select name, id from bar_clients where member_id is null and name in (${quoted})`).split('\n').map(l => l.split('\t')).map(([n, id]) => [n, +id]));
  const keyHolders = sql("select m.id, concat(upper(m.last_name), ' ', upper(m.first_name)) from members_keys mk join members m on m.id = mk.member_id where mk.returned_date is null and m.is_active = 1 order by m.last_name limit 3")
    .split('\n').map(l => l.split('\t'));
  const NAME_PAIRS = keyHolders.map(([, real], i) => [real, FAKE_BARMEN[i]]);
  const fakeNames = (s) => NAME_PAIRS.reduce((t, [real, f]) => t.split(real).join(f), s);
  sql("delete from bar_calls");
  sql("insert into bar_calls (table_id, called_at) select id, now() - interval 70 second from scoreboard_tables where name = 'Billard 4'");

  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  const errors = [];
  p.on('pageerror', e => errors.push(e.message));
  await p.evaluateOnNewDocument(() => {
    window.print = () => {};
    document.addEventListener('DOMContentLoaded', () => {
      const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s);
    });
  });
  await p.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: BASE });

  await p.setRequestInterception(true);
  p.on('request', async (req) => {
    const url = req.url();
    if (url.includes('/caisse/api/barmans')) {
      return req.respond({ contentType: 'application/json', body: JSON.stringify(keyHolders.map(([id], i) => ({ id: +id, name: FAKE_BARMEN[i], planned: i === 0 }))) });
    }
    if (url.includes('/caisse/api/clients/verifier')) {
      return req.respond({ contentType: 'application/json', body: JSON.stringify({ suspects: [{ id: clientId['DUPONT JEAN'], name: 'DUPONT JEAN', member: true, reason: 'proche' }], swapped: null, ambiguous: null, single: false }) });
    }
    if (url.includes('/caisse/api/clients?')) {
      const q = decodeURIComponent((url.split('q=')[1] || '')).toUpperCase();
      const seen = { 'DUPONT JEAN': [3, true], 'DUPONT MARIE': [12, false], 'DUPUIS LUC': [24, true] };
      const list = Object.keys(seen).filter(n => n.startsWith(q) || n.includes(' ' + q)).map(n => ({ id: clientId[n], name: n, member: seen[n][1], lastSeen: new Date(Date.now() - seen[n][0] * 864e5).toISOString().slice(0, 10) }));
      return req.respond({ contentType: 'application/json', body: JSON.stringify(list) });
    }
    if (url.includes('/caisse/api/') || url.includes('/marquoir/api/bar')) {
      try {
        const r = await fetch(url, { method: req.method(), headers: req.headers(), body: req.postData() });
        let text = fakeNames(await r.text());
        // Joueurs du billard qui appelle : noms fictifs
        if (url.includes('/marquoir/api/bar')) {
          const d = JSON.parse(text);
          (d.tables || []).forEach(t => { t.players = t.call ? ['LEMAIRE L.', 'DUBOIS M.'] : null; });
          text = JSON.stringify(d);
        }
        return req.respond({ status: r.status, contentType: 'application/json', body: text });
      } catch (e) { return req.continue(); }
    }
    req.continue();
  });

  await p.setViewport({ width: W, height: H, deviceScaleFactor: 2 });
  const TARGETS = {};
  // Zones à toucher (sélecteurs CSS, ou fonction qui renvoie un élément), en % de l'écran
  const shot = async (name, targets = []) => {
    await wait(600);
    await p.evaluate(() => {
      const t = document.getElementById('toast'); if (t) t.hidden = true;
      // Heures affichées ramenées à l'après-midi, quelle que soit l'heure des captures
      const w = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
      while (w.nextNode()) w.currentNode.nodeValue = w.currentNode.nodeValue.replace(/\b\d\d:(\d\d)\b/g, (m, mm) => '15:' + mm);
    });
    TARGETS[name] = [];
    for (const t of targets) {
      const r = await p.evaluate((sel, fnSrc) => {
        const el = fnSrc ? new Function('return (' + fnSrc + ')()')() : document.querySelector(sel);
        if (!el) return null;
        const b = el.getBoundingClientRect();
        return { x: b.left, y: b.top, w: b.width, h: b.height };
      }, typeof t === 'string' ? t : null, typeof t === 'function' ? t.toString() : null);
      if (!r) { console.warn(`  ${name} : cible introuvable ${t}`); continue; }
      TARGETS[name].push({ x: +(r.x / W * 100).toFixed(2), y: +(r.y / H * 100).toFixed(2), w: +(r.w / W * 100).toFixed(2), h: +(r.h / H * 100).toFixed(2) });
    }
    await p.screenshot({ path: `${out}/${name}.jpg`, type: 'jpeg', quality: 88 });
    console.log(name);
  };
  const tap = async (sel) => { await p.waitForSelector(sel, { visible: true }); await p.click(sel); await wait(200); };
  const keys = async (s) => { for (const k of s) await tap(`.key[data-k="${k}"]`); await wait(600); };
  const api = (pathname, body) => p.evaluate(async (pathname, body) => {
    const r = await fetch(window.CAISSE.api + pathname, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(body || {}) });
    return r.json();
  }, pathname, body);
  const product = (name) => +sql(`select id from bar_products where name = '${name}'`);
  const sell = async (client, items, method, reason) => {
    const t = (await api('tickets', { client_id: clientId[client] })).ticket;
    for (const [name, q] of items) for (let i = 0; i < q; i++) await api(`tickets/${t.id}/ligne`, { product_id: product(name), delta: 1 });
    if (method) await api(`tickets/${t.id}/valider`, { method });
    if (reason) await api(`tickets/${t.id}/annuler`, { reason });
    return t.id;
  };
  const pickCat = (name) => p.evaluate(n => [...document.querySelectorAll('.cat')].find(b => b.textContent === n).click(), name);
  const prod = async (name) => { await p.evaluate(n => [...document.querySelectorAll('.prod')].find(b => b.querySelector('.nm').textContent === n).click(), name); await wait(200); };

  try {
    // Démarrer
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await shot('p01_demarrer', ['#unlock .big']);
    await tap('#unlock');

    // Ouvrir le service : moment et nom
    await tap('#seg [data-p="am"]');
    await wait(500);
    await keys('MA');
    await shot('p02_ouvrir_nom', ['#seg [data-p="am"]', '#bmSugg .sg']);
    await tap('#bmSugg .sg');
    await keys('200');
    await shot('p03_ouvrir_fond', ['#fundBox', '#btnOpen']);
    await tap('#btnOpen');
    await wait(1000);
    await shot('p04_creer_ticket', ['#sideTicket [data-go="new"]']);

    // Service de démonstration
    await sell('RENARD GUY', [['Jupiler', 2], ['Chips', 1]], 'cash');
    await sell('SIMON DANIEL', [['Duvel', 1], ['Café', 1]], 'bancontact');
    await sell('GILLES ROBERT', [['Coca Cola', 2], ['Gaufre', 1]], 'cash');
    await sell('LEMAIRE LUC', [['Jupiler', 2], ['Coca Cola', 1]]);
    await sell('LAMBERT SOPHIE', [['Vin blanc', 1], ['Eau plate', 1], ['Boulette', 1]]);
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await tap('#unlock');

    // Chercher un client
    await tap('#sideTicket [data-go="new"]');
    await keys('DUP');
    await shot('p05_chercher_client', ['#sugg .sg']);

    // Client pas dans la liste (faute de frappe) : « + Créer », puis « existe peut-être déjà »
    await keys('ONT');
    await tap('.key[data-k=" "]');
    await keys('JAN');
    await shot('p06_nouveau_client', ['#sugg .sg.create']);
    await tap('#sugg .sg.create');
    await wait(700);
    await shot('p07_existe_deja', ['#kChoices .pick']);
    await tap('#kChoices .pick');
    await wait(800);

    // Consommations
    await pickCat('Bières');
    await prod('Jupiler'); await prod('Jupiler'); await prod('Duvel');
    await wait(600);
    await shot('p08_produits', [() => [...document.querySelectorAll('.cat')].find(b => b.textContent === 'Bières'), () => [...document.querySelectorAll('.prod')].find(b => b.querySelector('.nm').textContent === 'Jupiler')]);
    await pickCat('Snacks'); await prod('Chips');
    await pickCat('Café & thé'); await prod('Café');
    await pickCat('Bières');
    await wait(600);
    await shot('p09_corriger', ['#lines .pm[data-d="-1"]', '#lines .pm[data-d="1"]']);
    await shot('p10_plus_tard', ['#btnList']);

    // Tickets en cours
    await tap('#btnList');
    await wait(700);
    await shot('p11_tickets_en_cours', [() => [...document.querySelectorAll('#cards .card')].find(c => c.textContent.includes('DUPONT JEAN')).querySelector('[data-open]')]);

    // Payer
    await p.evaluate(() => [...document.querySelectorAll('#cards .card')].find(c => c.textContent.includes('DUPONT JEAN')).querySelector('[data-open]').click());
    await wait(500);
    await shot('p12_payer', ['#btnValidate']);
    await tap('#btnValidate');
    await shot('p13_mode_paiement', ['#payModal [data-m="cash"]', '#payModal [data-m="bancontact"]']);
    await tap('#payModal [data-m="cash"]');
    await wait(400);
    await shot('p14_confirmer', ['#mYes']);
    await tap('#mYes');
    await wait(800);

    // Ticket d'équipe payé en 3 parts (2 en espèces, 1 par Bancontact)
    const team = (await api('tickets', { name: 'EQUIPE DISON A' })).ticket;
    for (const [name, q] of [['Jupiler', 10], ['Carlsberg', 6], ['Eau plate', 5]]) for (let i = 0; i < q; i++) await api(`tickets/${team.id}/ligne`, { product_id: product(name), delta: 1 });
    await p.goto(`${BASE}/caisse?ticket=${team.id}`, { waitUntil: 'networkidle0' });
    await tap('#unlock');
    await wait(500);
    await tap('#btnValidate');
    await shot('p14a_partager', ['#payModal [data-m="split"]']);
    await tap('#payModal [data-m="split"]');
    await tap('#sN [data-n="3"]');
    await shot('p14b_nombre_parts', ['#sN [data-n="3"]', '#sYes']);
    await tap('#sYes');
    await tap('#sRows [data-i="0"][data-m="cash"]');
    await tap('#sRows [data-i="1"][data-m="cash"]');
    await tap('#sRows [data-i="2"][data-m="bancontact"]');
    await shot('p14c_modes', ['#sRows', '#sYes']);
    await tap('#sYes');
    await wait(400);
    await tap('#mYes');
    await wait(800);

    // Annuler un ticket (sans le faire)
    await p.evaluate(() => [...document.querySelectorAll('#cards .card')].find(c => c.textContent.includes('LAMBERT SOPHIE')).querySelector('[data-open]').click());
    await wait(500);
    await shot('p15_annuler_bouton', ['#btnCancel']);
    await tap('#btnCancel');
    await p.evaluate(() => document.querySelector('#mReasons .btn').click());
    await shot('p16_annuler_raison', ['#mReasons .btn', '#mYes']);
    await tap('#mNo');

    // Appel d'un billard (plus de 3 minutes : orange)
    sql("update bar_calls set called_at = now() - interval 4 minute");
    await wait(2600);
    await shot('p17_appel', ['#band .bt.called']);

    // Clôture : les tickets ouverts sont réglés d'abord
    for (const id of sql("select id from bar_tickets where status = 'open'").split('\n').filter(Boolean)) await api(`tickets/${id}/valider`, { method: 'cash' });
    sql("delete from bar_calls");
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await tap('#unlock');
    await tap('#sideTicket [data-go="list"]');
    await wait(700);
    await shot('p18_cloture_bouton', ['#btnGoClose']);
    await tap('#btnGoClose');
    await wait(800);
    await shot('p19_cloture_resume', ['#cExpected', '#btnClose']);
    await tap('#btnClose');
    await wait(700);
    await keys('200');
    await shot('p20_qui_cloture', ['#clFundBox', '#btnCloseGo']);
    await tap('#btnCloseGo');
    await wait(300);
    await shot('p21_cloture_confirmer', ['#mYes']);
    await tap('#mYes');
    await wait(900);
    await shot('p22_cloture_faite', ['#btnRecap', '#btnCloseBack']);
    const serviceId = +sql('select max(id) from bar_services');

    // Récapitulatif imprimé
    await p.setViewport({ width: 1100, height: 1400, deviceScaleFactor: 2 });
    await p.goto(`${BASE}/caisse/service/${serviceId}/recapitulatif`, { waitUntil: 'networkidle0' });
    await p.evaluate((pairs) => {
      pairs.forEach(([real, fake]) => { document.body.innerHTML = document.body.innerHTML.split(real).join(fake); });
      document.body.innerHTML = document.body.innerHTML.replace(/\b\d\d:(\d\d)\b/g, (m, mm) => '15:' + mm);
    }, NAME_PAIRS);
    await (await p.$('.sheet')).screenshot({ path: `${out}/p23_recapitulatif.jpg`, type: 'jpeg', quality: 88 });
    console.log('p23_recapitulatif');

    // Guide du comité : tickets du service et correction d'un paiement (fenêtre ouverte sur le ticket d'équipe)
    const sid = require('crypto').randomBytes(16).toString('hex');
    const sessFile = path.resolve(__dirname, '../../writable/session/ci_session' + sid);
    fs.writeFileSync(sessFile, `__ci_last_regenerate|i:${Math.floor(Date.now() / 1000)};admin_logged_in|b:1;member_id|i:1;`);
    try {
      await p.setCookie({ name: 'ci_session', value: sid, url: BASE });
      await p.setViewport({ width: 1440, height: 820, deviceScaleFactor: 1.5 });
      await p.goto(`${BASE}/admin/caisse/services/${serviceId}`, { waitUntil: 'networkidle0' });
      await p.evaluate((pairs) => {
        document.querySelectorAll('#debug-icon,#debug-bar').forEach(e => { e.style.display = 'none'; });
        const c = document.querySelector('.content-wrapper');
        // Texte seul (pas innerHTML) : la fenêtre de correction garde ses gestionnaires d'événements
        const w = document.createTreeWalker(c, NodeFilter.SHOW_TEXT);
        while (w.nextNode()) w.currentNode.nodeValue = pairs.reduce((t, [real, f]) => t.split(real).join(f), w.currentNode.nodeValue).replace(/\b\d\d:(\d\d)\b/g, (m, mm) => '15:' + mm);
      }, NAME_PAIRS);
      await p.evaluate(id => {
        // Fenêtre plus bas que d'habitude : l'en-tête de l'administration est découpé de la capture
        const st = document.createElement('style'); st.textContent = '#payFix .modal-dialog{margin-top:130px}'; document.head.appendChild(st);
        document.querySelector(`#t${id} [data-target="#payFix"]`).click();
      }, team.id);
      await wait(800);
      await p.evaluate(() => window.scrollTo(0, 0));
      await wait(200);
      // Écran visible, puis découpe de la barre latérale (250 px) et de l'en-tête (57 px), comme shots.cjs
      const file = path.resolve(__dirname, 'img/a04_corriger_paiement.png');
      await p.screenshot({ path: file });
      require('child_process').execSync(`sips -c ${Math.round((820 - 57) * 1.5)} ${Math.round((1440 - 250) * 1.5)} --cropOffset ${Math.round(57 * 1.5)} ${Math.round(250 * 1.5)} "${file}" >/dev/null`);
      console.log('a04_corriger_paiement');
    } finally {
      fs.unlinkSync(sessFile);
    }

    fs.writeFileSync(`${out}/targets.js`, '// Généré par pas-a-pas-shots.cjs : zones à toucher, en % de l\'écran\nwindow.TARGETS = ' + JSON.stringify(TARGETS, null, 1) + ';\n');
  } finally {
    await b.close();
    console.log(errors.length ? 'Erreurs JavaScript :\n' + errors.join('\n') : 'Captures terminées.');
  }
})();
