// Captures du mode d'emploi : Soirée Poule 5 Quilles (41 : page du billard, 42 : clôture et signatures des joueurs A, B, C)
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots9.cjs <dossier de sortie>  (billard 2, poule de démonstration sans destinataire, effacée à la fin)
const base = BASE, out = process.argv[2];
const NAME = 'Série 2 (DÉMO)';

// Trace une « signature » à la souris dans le cadre (événements pointeur, comme au doigt)
async function scribble(page, seed) {
  const r = await page.$eval('#signPad', (c) => { const b = c.getBoundingClientRect(); return { x: b.left, y: b.top, w: b.width, h: b.height }; });
  const strokes = [
    (t) => [0.12 + 0.30 * t, 0.62 - 0.30 * Math.sin(t * Math.PI * (2.2 + seed)) * (1 - t * 0.4)],
    (t) => [0.42 + 0.38 * t, 0.55 + 0.18 * Math.sin(t * Math.PI * (5 + seed)) - 0.12 * t],
    (t) => [0.30 + 0.45 * t, 0.78 - 0.04 * t],
  ];
  for (const f of strokes) {
    const [x0, y0] = f(0);
    await page.mouse.move(r.x + x0 * r.w, r.y + y0 * r.h);
    await page.mouse.down();
    for (let i = 1; i <= 40; i++) { const [x, y] = f(i / 40); await page.mouse.move(r.x + x * r.w, r.y + y * r.h); }
    await page.mouse.up();
  }
}

(async () => {
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const page = await b.newPage();
  await page.evaluateOnNewDocument(() => document.addEventListener('DOMContentLoaded', () => { const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay{display:none!important}'; document.head.appendChild(s); }));
  page.on('pageerror', e => console.log('pageerror:', e.message));
  await page.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: base });
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });
  try {
    // Poule du jour sur le billard 2, sans destinataire : rien ne peut être envoyé
    const players = [['DUPONT', 'Jean', 'RBC DISONAIS'], ['LAMBERT', 'Marc', 'B.C. HERVE'], ['SIMON', 'Éric', 'B.C. EUPEN']];
    sql(`insert into scoreboard_skittles_pools(name,scheduled_at,table_id,billiard,set_points,
         a_last_name,a_first_name,a_club,b_last_name,b_first_name,b_club,c_last_name,c_first_name,c_club,signature_required,send_to_regional,send_to_club_ds,created_at,updated_at)
         values ('${NAME}',DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:00'),2,'284',60,'${players[0].join("','")}','${players[1].join("','")}','${players[2].join("','")}',1,0,0,now(),now())`);
    const pool = sql(`select id from scoreboard_skittles_pools where name='${NAME}'`);
    // Calendrier officiel : 1 = B contre C, 2 = C contre A, 3 = A contre B
    const pairs = [[2, 3], [3, 1], [1, 2]];
    const ids = pairs.map(([x, y], i) => {
      const [p1, p2] = [players[x - 1], players[y - 1]];
      sql(`insert into scoreboard_matches(type,status,skittles_pool_id,final_order,table_id,scheduled_at,competition,discipline,format,sets_to_win,unequal_innings,signature_required,
           send_to_club_ds,send_to_p1,send_to_p2,p1_pool_slot,p1_last_name,p1_first_name,p1_club,p1_distance,p2_pool_slot,p2_last_name,p2_first_name,p2_club,p2_distance,created_at,updated_at)
           values ('official','scheduled',${pool},${i + 1},2,DATE_FORMAT(NOW(),'%Y-%m-%d %H:%i:00'),'Soirée Poule 5 Quilles — ${NAME}','5quilles','GB',2,1,0,
           0,0,0,${x},'${p1[0]}','${p1[1]}','${p1[2]}',60,${y},'${p2[0]}','${p2[1]}','${p2[2]}',60,now(),now())`);
      return sql('select max(id) from scoreboard_matches');
    });
    // Coups (positif = points, négatif = faute créditée à l'adversaire) enregistrés comme le ferait le marquoir
    const plays = [
      [20, 10, 20, 12, 20, 10, 20, 15, 21, -4, 0, 16, 15],
      [30, 16, 30, 20, 30, 40, 0, 16, 60],
      [60, 50, 10, -2, 0, 10, 60],
    ];
    const play = async (i) => {
      sql(`update scoreboard_matches set status='live', starting_player=1, started_at=NOW() where id=${ids[i]}`);
      const r = await page.evaluate((url, innings) => fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ rev: 1, innings, current_run: 0, extensions: [0, 0], finished: true }) }).then((x) => x.status), `${base}/marquoir/api/match/${ids[i]}`, plays[i]);
      if (r !== 200) throw new Error('saveState ' + r);
    };
    await page.goto(`${base}/marquoir/billard/2`, { waitUntil: 'networkidle2' });
    await play(0); await play(1);

    // 41 : page du billard, match 3 encore à jouer
    await page.goto(`${base}/marquoir/billard/2`, { waitUntil: 'networkidle2' }); await wait(500);
    // Seule la poule de démonstration reste : rencontres programmées, dernières parties et Chromebook masqués
    await page.evaluate(() => {
      document.querySelectorAll('.sb-match-upcoming,.sb-last,.sb-device').forEach((e) => e.remove());
      document.querySelectorAll('h2,h3').forEach((h) => { if (/programmées|Dernières parties/i.test(h.textContent)) h.remove(); });
    });
    const h = await page.evaluate(() => Math.ceil(Math.max(...[...document.querySelectorAll('.sb-card,.sb-tile')].map((e) => e.getBoundingClientRect().bottom)) + 30));
    await page.screenshot({ path: `${out}/41_billard_poule.png`, clip: { x: 0, y: 0, width: 1440, height: h } });

    // 42 : les trois matchs terminés, clôture avec les signatures des joueurs
    await play(2);
    await page.goto(`${base}/marquoir/poule-5-quilles/${pool}?billard=2`, { waitUntil: 'networkidle2' });
    await scribble(page, 0.3);
    await page.click('[data-role="b"]'); await scribble(page, 0.9);
    await page.click('[data-role="c"]'); await scribble(page, 1.5);
    await page.click('[data-role="b"]');
    await page.mouse.move(0, 0); await wait(300);
    await shot('42_cloture_poule', true);
  } finally {
    await b.close();
    sql(`delete from scoreboard_skittles_pools where name='${NAME}'`);
    console.log('restant', sql(`select count(*) from scoreboard_matches where competition like '%${NAME}%'`));
  }
})();
