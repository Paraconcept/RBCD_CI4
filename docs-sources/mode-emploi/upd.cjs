const puppeteer = require('puppeteer-core');
const { CHROME, scoreboardToken } = require('../lib.cjs');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
(async () => {
  // Usage : node upd.cjs <url de base> <id billard> <id partie terminée à la moyenne proportionnelle> <dossier de sortie>
  const [base, T, MID, out] = process.argv.slice(2);
  const token = scoreboardToken();
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => { document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon, #debug-bar, .debug-bar-ndisplay { display: none !important; }'; document.head.appendChild(s); }); });
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: token, url: base });
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  await page.goto(`${base}/marquoir/billard/${T}/amicale`, { waitUntil: 'networkidle0' });
  await page.evaluate(() => {
    const set = (n, v) => { const el = document.querySelector(`[name="${n}"]`); el.value = v; };
    set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean'); set('p1_distance', '32');
    set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul'); set('p2_distance', '19');
    const d = document.getElementById('discipline'); d.value = '3bandes'; d.dispatchEvent(new Event('change'));
    set('p1_category_id', '51'); set('p2_category_id', '56');
    const l = document.getElementById('innings_limit_enabled'); l.checked = true; l.dispatchEvent(new Event('change'));
  });
  await page.screenshot({ path: `${out}/03_amicale.png`, fullPage: true });
  // Résultat départagé à la moyenne proportionnelle
  await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  await page.goto(`${base}/marquoir/match/${MID}`, { waitUntil: 'networkidle0' });
  await wait(800);
  await page.screenshot({ path: `${out}/14_moyenne_prop.png` });
  console.log('ok');
  await b.close();
})();
