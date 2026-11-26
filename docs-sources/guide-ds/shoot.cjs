// Captures des pages admin : rechargées à leur hauteur finale (AdminLTE se met en page au chargement),
// puis recadrées sur la zone de contenu (sans la barre latérale).
const puppeteer = require('puppeteer-core');
const { CHROME } = require('../lib.cjs');
const { execSync } = require('child_process');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
(async () => {
  const [dir, ...only] = process.argv.slice(2);
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  const SCALE = 1.5, SIDEBAR = 250, HEADER = 57;
  const prepare = (o) => p.evaluate((o) => {
    document.querySelectorAll('.nav-sidebar a.nav-link').forEach((a) => {
      if (/Marquoirs/.test(a.textContent)) {
        a.classList.add('active');
        const ul = a.parentElement.querySelector('.nav-treeview'); if (ul) ul.style.display = 'block';
        // Menus parents (Direction sportive) dépliés eux aussi
        for (let el = a.parentElement; el && !el.classList.contains('nav-sidebar'); el = el.parentElement) {
          if (el.matches('li.nav-item')) el.classList.add('menu-open');
          if (el.matches('ul.nav-treeview')) el.style.display = 'block';
        }
      }
    });
    document.querySelectorAll('.alert-success,#debug-icon,#debug-bar').forEach((e) => { e.style.display = 'none'; });
    const sb = document.querySelector('.sidebar'); if (sb) sb.scrollTop = 99999;
    if (o.maskPin) { const pin = document.getElementById('pin'); if (pin) pin.value = '••••'; }
    if (o.dropdown) { const d = document.querySelector(o.dropdown); if (d) { d.classList.add('show'); d.parentElement.classList.add('show'); } }
  }, o);
  const shot = async (name, opts = {}) => {
    if (only.length && !only.includes(opts.out || name)) return;
    const url = `file://${dir}/pages/${name}.html`;
    await p.setViewport({ width: 1440, height: 900, deviceScaleFactor: SCALE });
    await p.goto(url, { waitUntil: 'networkidle0' });
    await prepare(opts);
    const h = opts.full ? 900 : Math.min(await p.evaluate(() => document.querySelector('.content-wrapper').getBoundingClientRect().bottom), opts.maxH || 4000);
    await p.setViewport({ width: 1440, height: Math.ceil(h), deviceScaleFactor: SCALE });
    await p.goto(url, { waitUntil: 'networkidle0' });
    await prepare(opts);
    await wait(600);
    const file = `${dir}/img/${opts.out || name}.png`;
    await p.screenshot({ path: file });
    if (!opts.full) {
      const W = Math.round((1440 - SIDEBAR) * SCALE), H = Math.round((h - HEADER) * SCALE);
      execSync(`sips -c ${H} ${W} --cropOffset ${Math.round(HEADER * SCALE)} ${Math.round(SIDEBAR * SCALE)} "${file}" >/dev/null`);
    }
    console.log(name, Math.round(h));
  };
  // Une seule card d'une page (ex. « Envoi des résultats »), sans le reste du formulaire
  const shotCard = async (name, title, out) => {
    if (only.length && !only.includes(out)) return;
    await p.setViewport({ width: 1440, height: 2400, deviceScaleFactor: SCALE });
    await p.goto(`file://${dir}/pages/${name}.html`, { waitUntil: 'networkidle0' });
    await prepare({});
    // Barre latérale masquée : elle recouvrirait le début de la card
    await p.evaluate(() => { document.querySelectorAll('.main-sidebar,.main-header,#debug-icon,#debug-bar').forEach((e) => { e.style.display = 'none'; }); document.querySelectorAll('.content-wrapper,.main-footer').forEach((e) => { e.style.marginLeft = '0'; }); });
    await wait(600);
    const card = await p.evaluateHandle((t) => [...document.querySelectorAll('.card')].find((c) => c.querySelector('.card-title')?.textContent.includes(t)), title);
    const r = await card.asElement().boundingBox();
    await p.screenshot({ path: `${dir}/img/${out}.png`, clip: { x: r.x - 2, y: r.y - 2, width: r.width + 4, height: r.height + 4 } });
    console.log(out);
  };
  await shotCard('match_edit', 'Envoi des résultats', 'match_recipients');
  await shot('index', { full: true, out: 'a01_menu' });
  await shot('settings', { maskPin: true });
  await shot('tables');
  await shot('match_create');
  await shot('index', { out: 'a05_index', maxH: 1200 });
  await shot('match_show');
  await shot('teams_index');
  await shot('team_form');
  await shot('team_match_create');
  await shot('team_show', { dropdown: '.card-header .dropdown-menu' });
  await shot('finals_index');
  await shot('final_form');
  await shot('final_show');
  await shot('skittles_form');
  await shot('skittles_show', { dropdown: 'td .dropdown-menu' });
  await shot('players');
  await b.close();
})();
