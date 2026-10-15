const puppeteer = require('puppeteer-core');
const fs = require('fs');
const { execSync } = require('child_process');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken } = require('../lib.cjs');
const hideDebug = (p) => p.evaluateOnNewDocument(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const s = document.createElement('style');
    s.textContent = '#debug-icon, #debug-bar, .debug-bar-ndisplay { display: none !important; }';
    document.head.appendChild(s);
  });
});
(async () => {
  // Usage : node shots.cjs <url de base> <id billard de démo> <id rencontre officielle de démo> <dossier de sortie>
  const [base, T, OFF, out] = process.argv.slice(2);
  const token = scoreboardToken();
  const browser = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await browser.newPage();
  await hideDebug(page);
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: token, url: base });
  const ui = () => page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const tv = () => page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  const key = async (k, n = 1) => { for (let i = 0; i < n; i++) { await page.keyboard.press(k); await wait(90); } };
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });

  // 1. Choix du billard (sans le billard de démonstration)
  sql(`update scoreboard_tables set is_active=0 where id=${T}`);
  await ui(); await page.goto(`${base}/marquoir`, { waitUntil: 'networkidle0' }); await shot('01_billards');
  sql(`update scoreboard_tables set is_active=1 where id=${T}`);

  // 2. Page d'un billard
  await page.goto(`${base}/marquoir/billard/${T}`, { waitUntil: 'networkidle0' }); await shot('02_billard');

  // 3. Partie amicale
  await page.goto(`${base}/marquoir/billard/${T}/amicale`, { waitUntil: 'networkidle0' });
  await page.evaluate(() => {
    const set = (n, v) => { const el = document.querySelector(`[name="${n}"]`); el.value = v; };
    set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean'); set('p1_distance', '30');
    set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul'); set('p2_distance', '30');
    const d = document.getElementById('discipline'); d.value = '3bandes'; d.dispatchEvent(new Event('change'));
    const c = document.getElementById('shot_clock_enabled'); c.checked = true; c.dispatchEvent(new Event('change'));
  });
  await shot('03_amicale', true);
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button.btn-sb.btn-lg')]);

  // 4. Qui commence + échauffement
  await shot('04_qui_commence');
  await key('NumpadMultiply'); await wait(1500); await shot('05_echauffement');
  await key('NumpadEnter'); await wait(300);
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), key('Numpad1')]);

  // 5. Partie en cours
  await tv(); await page.reload({ waitUntil: 'networkidle0' });
  await key('NumpadAdd', 3); await key('NumpadEnter');
  await key('NumpadAdd', 2); await key('NumpadEnter');
  await key('Numpad4'); await key('NumpadEnter');
  await key('NumpadAdd'); await key('NumpadEnter');
  await key('NumpadAdd', 2); await wait(3200);
  await shot('06_marquoir');
  await key('Numpad1'); await key('Numpad2'); await wait(300); await shot('07_saisie_serie');
  await key('NumpadDivide');
  await key('NumpadEnter');                                  // DUPONT : 3 + 4 + 2 = 9
  await key('Numpad9'); await key('NumpadEnter');           // MARTIN : 2 + 1 + 9 = 12
  await key('Numpad2'); await key('Numpad1'); await key('NumpadEnter');  // DUPONT atteint 30
  await wait(400); await shot('08_egalisation');
  await key('Numpad5'); await key('NumpadEnter');           // MARTIN : reprise d'égalisation
  await wait(800); await shot('09_resultat');

  // 6. Time-outs (chrono raccourci à 5 s pour la démonstration)
  await ui();
  await page.goto(`${base}/marquoir/billard/${T}/amicale`, { waitUntil: 'networkidle0' });
  await page.evaluate(() => {
    const set = (n, v) => { document.querySelector(`[name="${n}"]`).value = v; };
    set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean'); set('p1_distance', '30');
    set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul'); set('p2_distance', '30');
    const c = document.getElementById('shot_clock_enabled'); c.checked = true;
    set('shot_clock_seconds', '5'); set('shot_clock_extensions', '3');
  });
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button.btn-sb.btn-lg')]);
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), key('Numpad1')]);
  await tv(); await page.reload({ waitUntil: 'networkidle0' });
  await key('NumpadAdd', 2); await key('NumpadMultiply');
  await wait(5600); await shot('10_timeout');
  await wait(10400); await shot('11_temps_ecoule');

  // 7. En direct (rencontre officielle)
  sql(`update scoreboard_matches set status='live', started_at=now(), starting_player=1, table_id=${T} where id=${OFF}`);
  await page.evaluate(async (url) => {
    await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ rev: 9, innings: [12, 4, 7, 9, 3, 0, 15, 6, 8], current_run: 5, extensions: [0, 0], finished: false }) });
  }, `${base}/marquoir/api/match/${OFF}`);
  const fan = await browser.createBrowserContext();
  const phone = await fan.newPage();
  await hideDebug(phone);
  await phone.setViewport({ width: 844, height: 390, isMobile: true, deviceScaleFactor: 2 });
  await phone.goto(`${base}/en-direct/${OFF}`, { waitUntil: 'networkidle0' }); await wait(500);
  await phone.screenshot({ path: `${out}/12_direct_telephone.png` });
  await phone.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  await phone.goto(`${base}/en-direct`, { waitUntil: 'networkidle0' });
  await phone.screenshot({ path: `${out}/13_direct_liste.png` });

  await browser.close();
})();
