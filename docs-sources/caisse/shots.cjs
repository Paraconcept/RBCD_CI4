// Captures de la caisse du bar pour les deux documents (barman et comité).
// Crée un service de démonstration (clients fictifs, appels de billards), photographie chaque écran puis efface tout.
// Aucun vrai nom n'apparaît : les réponses de l'API sont réécrites avec des noms fictifs.
// Usage (depuis docs-sources) : node caisse/shots.cjs caisse/img
const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { execSync } = require('child_process');
const { ROOT, CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const out = path.resolve(process.argv[2] || 'caisse/img');
fs.mkdirSync(out, { recursive: true });

const DEMO_CLIENTS = ['DUPONT JEAN', 'DUPONT MARIE', 'DUPUIS LUC', 'LEMAIRE LUC', 'LAMBERT SOPHIE', 'DUBOIS MARC', 'RENARD GUY', 'SIMON DANIEL', 'PEETERS JOS', 'GILLES ROBERT'];
const FAKE_BARMEN = ['MARTIN PAUL', 'MASSON ANNE', 'MATHIEU ANDRÉ'];

(async () => {
  if (+sql("select count(*) from bar_services where status='open'")) {
    console.error('Un service est ouvert dans la base locale : clôturez-le avant de lancer les captures.');
    process.exit(1);
  }
  const maxService = +sql('select coalesce(max(id),0) from bar_services');
  const maxCall = +sql('select coalesce(max(id),0) from bar_calls');
  const quoted = DEMO_CLIENTS.map(n => `'${n}'`).join(',');
  sql(`delete from bar_clients where member_id is null and name in (${quoted})`);
  for (const n of DEMO_CLIENTS) sql(`insert into bar_clients (name, last_visit_at, created_at, updated_at) values ('${n}', now() - interval ${1 + Math.floor(Math.random() * 30)} day, now(), now())`);
  const clientId = Object.fromEntries(sql(`select name, id from bar_clients where member_id is null and name in (${quoted})`).split('\n').map(l => l.split('\t')).map(([n, id]) => [n, +id]));
  const keyHolders = sql("select m.id, concat(upper(m.last_name), ' ', upper(m.first_name)) from members_keys mk join members m on m.id = mk.member_id where mk.returned_date is null and m.is_active = 1 order by m.last_name limit 3")
    .split('\n').map(l => l.split('\t'));
  const realBarman = keyHolders[0][1];
  sql("insert into bar_calls (table_id, called_at) select id, now() - interval 4 minute from scoreboard_tables where name = 'Billard 1'");
  sql("insert into bar_calls (table_id, called_at) select id, now() - interval 70 second from scoreboard_tables where name = 'Billard 4'");

  // Session « comité » locale pour les pages d'administration (fichier de session, supprimé à la fin)
  const sid = crypto.randomBytes(16).toString('hex');
  const sessFile = path.join(ROOT, 'writable/session/ci_session' + sid);
  fs.writeFileSync(sessFile, `__ci_last_regenerate|i:${Math.floor(Date.now() / 1000)};admin_logged_in|b:1;member_id|i:1;`);

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

  // Noms fictifs dans les réponses de la caisse
  await p.setRequestInterception(true);
  p.on('request', async (req) => {
    const url = req.url();
    if (url.includes('/caisse/api/barmans')) {
      return req.respond({ contentType: 'application/json', body: JSON.stringify(keyHolders.map(([id], i) => ({ id: +id, name: FAKE_BARMEN[i], planned: i === 0 }))) });
    }
    if (url.includes('/caisse/api/clients')) {
      const q = decodeURIComponent((url.split('q=')[1] || '')).toUpperCase();
      const seen = { 'DUPONT JEAN': [3, true], 'DUPONT MARIE': [12, false], 'DUPUIS LUC': [24, true] };
      const list = Object.keys(seen).filter(n => n.startsWith(q) || n.includes(' ' + q)).map(n => ({ id: clientId[n], name: n, member: seen[n][1], lastSeen: new Date(Date.now() - seen[n][0] * 864e5).toISOString().slice(0, 10) }));
      return req.respond({ contentType: 'application/json', body: JSON.stringify(list) });
    }
    if (url.includes('/caisse/api/')) {
      try {
        const r = await fetch(url, { method: req.method(), headers: req.headers(), body: req.postData() });
        const text = (await r.text()).split(realBarman).join(FAKE_BARMEN[0]);
        return req.respond({ status: r.status, contentType: 'application/json', body: text });
      } catch (e) { return req.continue(); }
    }
    req.continue();
  });

  await p.setViewport({ width: 1194, height: 834, deviceScaleFactor: 2 });
  const shot = async (name, keepToast) => { await wait(500); if (!keepToast) await p.evaluate(() => { document.getElementById('toast').hidden = true; }); await p.screenshot({ path: `${out}/${name}.png` }); console.log(name); };
  const tap = async (sel) => { await p.waitForSelector(sel, { visible: true }); await p.click(sel); await wait(150); };
  const keys = async (s) => { for (const k of s) { await tap(`.key[data-k="${k}"]`); } await wait(600); };
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
  const prod = async (name) => { await p.evaluate(n => [...document.querySelectorAll('.prod')].find(b => b.querySelector('.nm').textContent === n).click(), name); await wait(150); };

  try {
    // 1. Ouverture du service
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await tap('#unlock');
    await keys('MA');
    await tap('#bmSugg .sg');
    await shot('c01_ouverture');
    await tap('#btnOpen');
    await wait(800);

    // Service de démonstration : tickets réglés, un annulé, trois en cours (le premier ouvert depuis plus d'une heure)
    await sell('RENARD GUY', [['Jupiler', 2], ['Chips', 1]], 'cash');
    await sell('SIMON DANIEL', [['Duvel', 1], ['Café', 1]], 'bancontact');
    await sell('PEETERS JOS', [['Chimay Bleue', 1]], null, 'Erreur de saisie');
    await sell('GILLES ROBERT', [['Coca Cola', 2], ['Gaufre', 1]], 'cash');
    const late = await sell('LEMAIRE LUC', [['Jupiler', 2], ['Coca Cola', 1]]);
    await sell('LAMBERT SOPHIE', [['Vin blanc', 1], ['Eau plate', 1], ['Boulette', 1]]);
    await sell('DUBOIS MARC', [['Duvel', 2], ['Jupiler', 2], ['Café', 1]]);
    sql(`update bar_tickets set opened_at = now() - interval 75 minute where id = ${late}`);

    // 2. Nouveau ticket au clavier
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await tap('#unlock');
    await tap('#sideTicket [data-go="new"]');
    await keys('DUP');
    await shot('c02_nouveau_ticket');

    // 3. Consommations
    await tap('#sugg .sg');
    await wait(500);
    await pickCat('Bières');
    await prod('Jupiler'); await prod('Jupiler'); await prod('Duvel');
    await pickCat('Snacks'); await prod('Chips');
    await pickCat('Café & thé'); await prod('Café');
    await pickCat('Bières');
    await wait(700);
    await shot('c03_caisse');

    // 4. Annulation (fenêtre de confirmation)
    await tap('#btnCancel');
    await p.evaluate(() => document.querySelector('#mReasons .btn').click());
    await shot('c04_annuler');
    await tap('#mNo');

    // 5. Tickets en cours
    await tap('#sideTicket [data-go="list"]');
    await shot('c05_tickets_en_cours');

    // 6. Régler : retour au ticket de DUPONT, Payer, fenêtre du mode de paiement, Espèces
    await p.evaluate(() => [...document.querySelectorAll('.card')].find(c => c.textContent.includes('DUPONT JEAN')).querySelector('[data-open]').click());
    await wait(300);
    await tap('#btnValidate');
    await shot('c06_payer');
    await tap('#payModal [data-m="cash"]');
    await wait(500);
    await tap('#list [data-go="ticket"]');
    const dupontTicket = +sql(`select id from bar_tickets where client_id = ${clientId['DUPONT JEAN']} order by id desc limit 1`);

    // 7. Historique
    await tap('#left [data-go="history"]');
    await wait(500);
    await shot('c07_historique');

    // 8. Clôture refusée tant qu'il reste des tickets ouverts
    await tap('#history [data-go="ticket"]');
    await tap('#left [data-go="close"]');
    await wait(700);
    await shot('c08_cloture_bloquee');

    // Les tickets ouverts sont réglés, puis clôture
    for (const id of sql("select id from bar_tickets where status = 'open'").split('\n').filter(Boolean)) await api(`tickets/${id}/valider`, { method: id == late ? 'bancontact' : 'cash' });
    await tap('#btnCloseBack');
    await tap('#left [data-go="close"]');
    await wait(700);
    await shot('c09_cloture');
    await tap('#btnClose');
    await wait(300);
    await shot('c10_cloture_confirmer');
    await tap('#mYes');
    await wait(800);
    await shot('c11_cloture_faite');
    const serviceId = +sql('select max(id) from bar_services');

    // Impressions : récapitulatif A4 et ticket client
    await p.setViewport({ width: 1100, height: 1400, deviceScaleFactor: 2 });
    for (const [url, sel, name] of [[`caisse/service/${serviceId}/recapitulatif`, '.sheet', 'c12_recapitulatif'], [`caisse/ticket/${dupontTicket}/imprimer`, '.ticket', 'c13_ticket_client']]) {
      await p.goto(`${BASE}/${url}`, { waitUntil: 'networkidle0' });
      await p.evaluate((real, fake) => { document.body.innerHTML = document.body.innerHTML.split(real).join(fake); }, realBarman, FAKE_BARMEN[0]);
      const el = await p.$(sel);
      await el.screenshot({ path: `${out}/${name}.png` });
      console.log(name);
    }

    // Pages d'administration (contenu seul, sans la barre latérale), noms réels remplacés
    await p.setCookie({ name: 'ci_session', value: sid, url: BASE });
    const SCALE = 1.5, SIDEBAR = 250, HEADER = 57;
    const admin = async (route, name, opts = {}) => {
      await p.setViewport({ width: 1440, height: opts.h || 900, deviceScaleFactor: SCALE });
      await p.goto(`${BASE}/${route}`, { waitUntil: 'networkidle0' });
      await p.evaluate((o) => {
        document.querySelectorAll('.alert-success,#debug-icon,#debug-bar').forEach(e => { e.style.display = 'none'; });
        if (o.clients) {
          // Liste réduite à des clients fictifs, membres compris
          const fake = ['AUBRY MICHEL', 'BODSON RENÉ', 'CHARLIER YVES', 'DUBOIS MARC', 'DUPONT JEAN', 'DUPONT MARIE', 'DUPUIS LUC', 'GILLES ROBERT', 'JANSSENS ANNE', 'LAMBERT SOPHIE', 'LEMAIRE LUC', 'PEETERS JOS'];
          const rows = [...document.querySelectorAll('#clientsTable tbody tr')];
          const demo = rows.filter(r => /DUPONT|DUPUIS|LEMAIRE LUC|LAMBERT SOPHIE|DUBOIS MARC|PEETERS JOS|GILLES ROBERT/.test(r.textContent));
          const members = rows.filter(r => r.querySelector('.fa-id-card')).slice(0, 5);
          rows.forEach(r => { if (!demo.includes(r) && !members.includes(r)) r.remove(); });
          ['AUBRY MICHEL', 'BODSON RENÉ', 'CHARLIER YVES', 'JANSSENS ANNE', 'MOTTET CLAUDE'].forEach((n, i) => { if (members[i]) members[i].cells[0].textContent = n; });
          const tb = document.querySelector('#clientsTable tbody');
          const key = r => (r.cells[0].querySelector('input')?.value || r.cells[0].textContent).trim();
          [...tb.rows].sort((a, b) => key(a).localeCompare(key(b))).forEach(r => tb.appendChild(r));
          document.querySelectorAll('.dataTables_info, .dataTables_paginate').forEach(e => e.remove());
          document.querySelectorAll('#clientsTable select option').forEach(o => { if (o.value && !fake.some(f => o.textContent.startsWith(f))) o.remove(); });
        }
        if (o.replace) document.querySelector('.content-wrapper').innerHTML = document.querySelector('.content-wrapper').innerHTML.split(o.replace[0]).join(o.replace[1]);
        if (o.menu) {
          const a = [...document.querySelectorAll('.nav-sidebar a.nav-link')].find(x => /Caisse du bar/.test(x.textContent));
          a.parentElement.classList.add('menu-open');
          a.parentElement.querySelector('.nav-treeview').style.display = 'block';
          const sb = document.querySelector('.sidebar'); sb.scrollTop = a.getBoundingClientRect().top - 200;
        }
      }, { ...opts, replace: opts.replace });
      await wait(500);
      const file = `${out}/${name}.png`;
      const h = opts.h || Math.min(await p.evaluate(() => document.querySelector('.content-wrapper').getBoundingClientRect().bottom), 1400);
      await p.setViewport({ width: 1440, height: Math.ceil(h), deviceScaleFactor: SCALE });
      await wait(300);
      await p.screenshot({ path: file });
      if (opts.menu) execSync(`sips -c ${Math.round(h * SCALE)} ${SIDEBAR * SCALE} --cropOffset 1 1 "${file}" >/dev/null`);
      else execSync(`sips -c ${Math.round((h - HEADER) * SCALE)} ${(1440 - SIDEBAR) * SCALE} --cropOffset ${HEADER * SCALE} ${SIDEBAR * SCALE} "${file}" >/dev/null`);
      console.log(name);
    };
    await admin('admin/caisse/services', 'a00_menu', { menu: true, h: 900 });
    await admin('admin/caisse/catalogue', 'a01_catalogue', { h: 1100 });
    await admin('admin/caisse/clients', 'a02_clients', { clients: true });
    await admin('admin/caisse/services', 'a03_services', { replace: [realBarman, FAKE_BARMEN[0]] });
  } finally {
    await b.close();
    // Nettoyage : rien de la démonstration ne reste en base
    sql(`delete l from bar_ticket_lines l join bar_tickets t on t.id = l.ticket_id where t.service_id > ${maxService}`);
    sql(`delete from bar_tickets where service_id > ${maxService}`);
    sql(`delete from bar_services where id > ${maxService}`);
    sql(`delete from bar_clients where member_id is null and name in (${quoted})`);
    sql(`delete from bar_calls where id > ${maxCall}`);
    sql("delete from scoreboard_devices where name='Captures documentation'");
    fs.rmSync(sessFile, { force: true });
    console.log(errors.length ? 'Erreurs JavaScript :\n' + errors.join('\n') : 'Captures terminées, données de démonstration effacées.');
  }
})();
