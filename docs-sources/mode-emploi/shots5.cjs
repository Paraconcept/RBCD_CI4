// Capture du mode d'emploi : formulaire amical par sets, points par set différents pour chaque joueur (15)
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots5.cjs <dossier de sortie>  (rien n'est enregistré : le formulaire n'est pas envoyé)
const out = process.argv[2];
(async () => {
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: BASE });
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  try {
    await page.goto(`${BASE}/marquoir/billard/2/amicale`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
      const set = (n, v) => { document.querySelector(`[name="${n}"]`).value = v; };
      set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean');
      set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul');
      const d = document.getElementById('discipline'); d.value = '3bandes'; d.dispatchEvent(new Event('change'));
      const s = document.getElementById('sets_enabled'); s.checked = true; s.dispatchEvent(new Event('change'));
      set('p2_distance', '12');
    });
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await wait(300);
    // Des fiches des joueurs jusqu'au bloc « Par sets »
    const clip = await page.evaluate(() => {
      const top = document.querySelector('[name="p1_last_name"]').closest('.sb-card').getBoundingClientRect();
      const bottom = document.querySelector('[data-sb-sets-block]').getBoundingClientRect();
      const card = bottom.top ? document.querySelector('[data-sb-sets-block]').closest('.sb-card').getBoundingClientRect() : bottom;
      return { x: card.left - 8, y: top.top + window.scrollY - 8, width: card.width + 16, height: bottom.bottom - top.top + 24 };
    });
    await page.screenshot({ path: `${out}/15_sets_form.png`, clip });
  } finally {
    await b.close();
  }
})();
