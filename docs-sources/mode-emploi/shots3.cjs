// Captures du mode d'emploi : page du billard (2 boutons), formulaire amical simplifié, partie à 3
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots3.cjs <dossier de sortie>  (billard 2, rencontres de démonstration effacées à la fin)
const base = BASE, T = 2, out = process.argv[2];
(async () => {
  const maxPlayer = sql('select coalesce(max(id),0) from scoreboard_players');
  // Rencontres officielles de démonstration sur le billard 2 (effacées à la fin)
  const ins = (when, comp, disc, d, a, b, c2) => sql(`insert into scoreboard_matches(type,status,table_id,scheduled_at,competition,discipline,format,p1_last_name,p1_first_name,p1_club,p1_distance,p2_last_name,p2_first_name,p2_club,p2_distance,created_at,updated_at) values ('official','scheduled',${T},${when},'${comp}','${disc}','PB','${a[0]}','${a[1]}','RBC Disonais',${d},'${b[0]}','${b[1]}','${c2}',${d},now(),now())`);
  ins('DATE_ADD(NOW(), INTERVAL 30 MINUTE)', 'Championnat Libre PF', 'libre', 60, ['DUPONT', 'Jean'], ['LAMBERT', 'Marc'], 'B.C. Herve');
  ins("CONCAT(DATE_ADD(CURDATE(), INTERVAL 5 DAY), ' 19:30:00')", 'Championnat 3 Bandes PF', '3bandes', 25, ['RENARD', 'Louis'], ['SIMON', 'Éric'], 'B.C. Eupen');
  ins("CONCAT(DATE_ADD(CURDATE(), INTERVAL 12 DAY), ' 20:00:00')", 'Championnat Libre PF', 'libre', 40, ['LECOMTE', 'Anne'], ['GOFFIN', 'Paul'], 'B.C. Verviers');
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: base });
  const ui = () => page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const tv = () => page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  const key = async (k, n = 1) => { for (let i = 0; i < n; i++) { await page.keyboard.press(k); await wait(120); } };
  const series = async (n) => { for (const d of String(n)) await key('Numpad' + d); await key('NumpadEnter'); };
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });
  try {
    await ui();
    await page.goto(`${base}/marquoir/billard/${T}`, { waitUntil: 'networkidle0' }); await shot('02_billard');

    await page.goto(`${base}/marquoir/billard/${T}/amicale`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
      const set = (n, v) => { document.querySelector(`[name="${n}"]`).value = v; };
      set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean'); set('p1_distance', '30');
      set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul'); set('p2_distance', '30');
      const d = document.getElementById('discipline'); d.value = '3bandes'; d.dispatchEvent(new Event('change'));
      const c = document.getElementById('shot_clock_enabled'); c.checked = true; c.dispatchEvent(new Event('change'));
    });
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await shot('03_amicale', true);

    await page.goto(`${base}/marquoir/billard/${T}/amicale-a-3`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
      const set = (n, v) => { document.querySelector(`[name="${n}"]`).value = v; };
      set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean'); set('p1_distance', '15');
      set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul'); set('p2_distance', '15');
      set('p3_last_name', 'LECOMTE'); set('p3_first_name', 'Anne'); set('p3_distance', '15');
    });
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await shot('21_amicale3', true);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button.btn-sb.btn-lg')]);
    await key('Numpad2'); await wait(300); await shot('22_qui_commence3');
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), key('Numpad3')]);

    // MARTIN, LECOMTE, DUPONT
    await tv(); await page.reload({ waitUntil: 'networkidle0' });
    await series(4); await series(2); await series(6);
    await series(3); await key('NumpadAdd', 2); await wait(2600);
    await shot('23_marquoir3');
    await key('NumpadEnter');                      // LECOMTE 2 + 2 = 4
    await series(2);                               // DUPONT 8
    await series(8); await wait(2600);             // MARTIN 15 : fin du tour
    await shot('24_egalisation3');
    await series(6); await series(4); await wait(1200);  // LECOMTE 10, DUPONT 12
    await shot('25_resultat3');
  } finally {
    await b.close();
    sql(`delete from scoreboard_matches where table_id=${T} and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) and (p3_last_name is not null or type='friendly' or p2_last_name in ('LAMBERT','SIMON','GOFFIN'))`);
    sql(`delete from scoreboard_players where id > ${maxPlayer}`);
    console.log('restant', sql(`select count(*) from scoreboard_matches where table_id=${T} and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`));
  }
})();
