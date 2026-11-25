// Captures du mode d'emploi : partie amicale à la Salade (configuration, tableau, résultat) et Salade « Quick Pick »
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots8.cjs <dossier de sortie>  (billard 2 ; parties et figure de démonstration effacées, liste des figures du club rétablie)
const base = BASE, T = 2, out = process.argv[2];
(async () => {
  const maxPlayer = sql('select coalesce(max(id),0) from scoreboard_players');
  const maxFigure = sql('select coalesce(max(id),0) from scoreboard_salad_figures');
  const figures = sql('select id, active, p1_points, p2_points, sort_order from scoreboard_salad_figures').split('\n').filter(Boolean).map((l) => l.split('\t'));
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: base });
  const ui = () => page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const tv = () => page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  const key = async (k, n = 1) => { for (let i = 0; i < n; i++) { await page.keyboard.press(k); await wait(160); } };
  const keys = async (s) => { for (const d of s) await key(d === 'E' ? 'NumpadEnter' : d === '+' ? 'NumpadAdd' : 'Numpad' + d); };
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });
  const players = async () => {
    await page.type('[name="p1_last_name"]', 'DUPONT'); await page.type('[name="p1_first_name"]', 'Jean');
    await page.type('[name="p2_last_name"]', 'MARTIN'); await page.type('[name="p2_first_name"]', 'Paul');
  };
  const start = async () => {
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button.btn-sb.btn-lg:not([type=button])')]);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), key('Numpad1')]);
    await tv(); await page.reload({ waitUntil: 'networkidle0' });
  };
  try {
    // Salade : 2 points par figure, Bricole désactivée, « De la mauvaise main » ajoutée
    await ui();
    await page.goto(`${base}/marquoir/billard/${T}/amicale-salade`, { waitUntil: 'networkidle0' });
    await players();
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await shot('35_salade_joueurs', true);
    await page.click('#salNext'); await wait(300);
    await page.evaluate(() => {
      // Liste de base seulement : les figures ajoutées par le club sont retirées de la page (pas de la base)
      document.querySelectorAll('[data-sal-row]').forEach((r) => { if (r.querySelector('[data-sal-remove]')) r.remove(); });
      const eq = document.getElementById('equal_points'); if (!eq.checked) { eq.checked = true; eq.dispatchEvent(new Event('change', { bubbles: true })); }
      document.querySelectorAll('[data-sal-row]').forEach((r) => {
        const label = r.querySelector('[data-sal-label]').value;
        const a = r.querySelector('[data-sal-active]'); a.checked = label !== 'Bricole'; a.dispatchEvent(new Event('change', { bubbles: true }));
        r.querySelector('[data-sal-p1]').value = 2;
      });
      document.getElementById('salAdd').click();
      const n = [...document.querySelectorAll('[data-sal-row]')].pop();
      n.querySelector('[data-sal-label]').value = 'De la mauvaise main';
      n.querySelector('[data-sal-p1]').value = 2;
      n.querySelector('[data-sal-p1]').dispatchEvent(new Event('input', { bubbles: true }));
      document.activeElement.blur();
    });
    await shot('36_salade_figures', true);
    await start();
    await keys('1123E');   // DUPONT : Direct ×2, Rouge, 1 Bande, raté
    await keys('244E');    // MARTIN : Rouge, 2 Bandes ×2, raté
    await keys('5'); await wait(400);   // DUPONT : 3 Bandes
    await shot('37_salade_tableau');
    await keys('234456'); await key('Numpad6'); await wait(500);   // DUPONT termine : reprise d'égalisation pour MARTIN
    await keys('1E'); await wait(1200);
    await shot('39_salade_resultat');

    // Quick Pick : mêmes figures (derniers réglages retenus)
    await ui();
    await page.goto(`${base}/marquoir/billard/${T}/amicale-salade-quick-pick`, { waitUntil: 'networkidle0' });
    await players();
    await page.click('#salNext'); await wait(300);
    await start();
    await keys('++E+'); await wait(600);
    await shot('40_quickpick');
  } finally {
    await b.close();
    sql(`delete from scoreboard_matches where table_id=${T} and type='friendly' and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`);
    sql(`delete from scoreboard_players where id > ${maxPlayer}`);
    sql(`delete from scoreboard_salad_figures where id > ${maxFigure}`);
    for (const [id, active, p1, p2, order] of figures) {
      sql(`update scoreboard_salad_figures set active=${active}, p1_points=${p1}, p2_points=${p2}, sort_order=${order} where id=${id}`);
    }
    console.log('restant', sql(`select count(*) from scoreboard_matches where table_id=${T} and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`));
  }
})();
