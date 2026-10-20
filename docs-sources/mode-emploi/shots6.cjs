// Capture du mode d'emploi : fin d'un set (17), avec le joueur qui commence le set suivant
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots6.cjs <dossier de sortie>  (partie amicale par sets sur le billard 2, effacée à la fin)
const out = process.argv[2];

(async () => {
  sql(`insert into scoreboard_matches(type,status,table_id,scheduled_at,started_at,starting_player,discipline,format,unequal_innings,sets_to_win,
       p1_last_name,p1_first_name,p1_club,p1_distance,p2_last_name,p2_first_name,p2_club,p2_distance,rev,created_at,updated_at)
       values ('friendly','live',2,NOW(),NOW(),1,'3bandes','PB',1,2,
       'DUPONT','Jean','RBC Disonais',15,'MARTIN','Paul','RBC Disonais',15,0,now(),now())`);
  const id = sql('select max(id) from scoreboard_matches');
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: BASE });
  await page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  try {
    await page.goto(`${BASE}/marquoir/match/${id}`, { waitUntil: 'networkidle0' });
    await wait(500);
    // Set 1 : DUPONT 7, MARTIN 4, DUPONT 3, MARTIN 2, DUPONT 5 → 15-6 en 3 reprises
    for (const run of ['7', '4', '3', '2', '5']) {
      await page.keyboard.press('Numpad' + run);
      await page.keyboard.press('NumpadEnter');
      await wait(250);
    }
    await wait(800);
    await page.screenshot({ path: `${out}/17_sets_pause.png` });
  } finally {
    await b.close();
    sql(`delete from scoreboard_innings where match_id=${id}`);
    sql(`delete from scoreboard_matches where id=${id}`);
  }
})();
