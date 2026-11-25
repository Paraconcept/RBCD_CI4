// Captures du mode d'emploi : page du billard (une tuile « Partie amicale »), fenêtre de choix, partie aux 5 quilles
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots7.cjs <dossier de sortie>  (billard 2, rencontres de démonstration effacées à la fin)
const base = BASE, T = 2, out = process.argv[2];
(async () => {
  const maxPlayer = sql('select coalesce(max(id),0) from scoreboard_players');
  const ins = (when, comp, disc, d, a, b, c2) => sql(`insert into scoreboard_matches(type,status,table_id,scheduled_at,competition,discipline,format,p1_last_name,p1_first_name,p1_club,p1_distance,p2_last_name,p2_first_name,p2_club,p2_distance,created_at,updated_at) values ('official','scheduled',${T},${when},'${comp}','${disc}','PB','${a[0]}','${a[1]}','RBC Disonais',${d},'${b[0]}','${b[1]}','${c2}',${d},now(),now())`);
  ins('DATE_ADD(NOW(), INTERVAL 30 MINUTE)', 'Championnat Libre PF', 'libre', 60, ['DUPONT', 'Jean'], ['LAMBERT', 'Marc'], 'B.C. Herve');
  ins("CONCAT(DATE_ADD(CURDATE(), INTERVAL 5 DAY), ' 19:30:00')", 'Championnat 3 Bandes PF', '3bandes', 25, ['RENARD', 'Louis'], ['SIMON', 'Éric'], 'B.C. Eupen');
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: base });
  const ui = () => page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const tv = () => page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  const key = async (k, n = 1) => { for (let i = 0; i < n; i++) { await page.keyboard.press(k); await wait(150); } };
  const type = async (n) => { for (const d of String(n)) await key('Numpad' + d); };
  const shotPts = async (n) => { await type(n); await key('NumpadEnter'); };   // points du coup
  const foul = async (n) => { if (n) await type(n); await key('NumpadAdd'); };  // faute (points à l'adversaire)
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });
  try {
    await ui();
    await page.goto(`${base}/marquoir/billard/${T}`, { waitUntil: 'networkidle0' });
    // Seules les rencontres de démonstration restent à l'écran (pas de vrais joueurs dans un document public)
    await page.evaluate(() => document.querySelectorAll('.sb-match').forEach((c) => { if (!/LAMBERT|SIMON/.test(c.textContent)) c.remove(); }));
    await page.screenshot({ path: `${out}/02_billard.png`, clip: { x: 0, y: 0, width: 1440, height: 560 } });
    await page.click('[data-sb-friendly-open]'); await wait(300);
    await (await page.$('.sb-modal-box')).screenshot({ path: `${out}/30_choix_amicale.png` });

    await page.goto(`${base}/marquoir/billard/${T}/amicale-5-quilles`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
      const set = (n, v) => { document.querySelector(`[name="${n}"]`).value = v; };
      set('p1_last_name', 'DUPONT'); set('p1_first_name', 'Jean');
      set('p2_last_name', 'MARTIN'); set('p2_first_name', 'Paul');
    });
    await page.addStyleTag({ content: '.sb-sticky-actions{position:static!important}' });
    await shot('31_amicale5q', true);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('button.btn-sb.btn-lg')]);
    await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), key('Numpad1')]);

    await tv(); await page.reload({ waitUntil: 'networkidle0' });
    await shotPts(6);    // DUPONT 6
    await shotPts(10);   // MARTIN 10 (rouge seule)
    await foul(4);       // DUPONT : faute, 4 pour MARTIN (14)
    await key('NumpadEnter'); // MARTIN : coup sans point
    await shotPts(8);    // DUPONT 14
    await foul();        // MARTIN : faute de 2, DUPONT 16
    await shotPts(12);   // DUPONT 28
    await shotPts(6);    // MARTIN 20
    await type(10); await wait(400);
    await shot('32_marquoir5q');
    await key('NumpadAdd'); await wait(600);   // DUPONT : faute, 10 pour MARTIN (30)
    await shot('33_faute5q');
    await wait(4200);
    await shotPts(16);   // MARTIN 46
    await shotPts(14);   // DUPONT 42
    await shotPts(12);   // MARTIN 58
    await foul(); await wait(1200);           // DUPONT : faute de 2 → MARTIN 60, gagne
    await shot('34_resultat5q');
  } finally {
    await b.close();
    sql(`delete from scoreboard_matches where table_id=${T} and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) and (type='friendly' or p2_last_name in ('LAMBERT','SIMON'))`);
    sql(`delete from scoreboard_players where id > ${maxPlayer}`);
    console.log('restant', sql(`select count(*) from scoreboard_matches where table_id=${T} and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`));
  }
})();
