// Captures du mode d'emploi : signature de la feuille de match et clôture d'une rencontre d'équipe (26 à 29)
const puppeteer = require('puppeteer-core');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
// Usage : node shots4.cjs <dossier de sortie>  (billards 2 et 3, données de démonstration effacées à la fin)
const base = BASE, out = process.argv[2];
const CODE = 'DEMO-SIGN';

// Match officiel terminé (reprises comprises) ; renvoie son id
function finishedMatch({ table, a, b, da, db, runsA, runsB, sign = 1, encounter = 'NULL', club2 = 'B.C. Herve', comp = 'Championnat Libre PF' }) {
  const ptsA = runsA.reduce((x, y) => x + y, 0), ptsB = runsB.reduce((x, y) => x + y, 0);
  const winner = ptsA >= da && ptsB >= db ? 0 : (ptsA / da >= ptsB / db ? 1 : 2);
  sql(`insert into scoreboard_matches(type,status,table_id,team_encounter_id,scheduled_at,started_at,finished_at,starting_player,competition,discipline,format,signature_required,
       p1_last_name,p1_first_name,p1_club,p1_distance,p1_points,p1_innings,p1_high_run,p2_last_name,p2_first_name,p2_club,p2_distance,p2_points,p2_innings,p2_high_run,winner,rev,created_at,updated_at)
       values ('official','finished',${table},${encounter},NOW(),NOW(),NOW(),1,'${comp}','libre','PB',${sign},
       '${a[0]}','${a[1]}','RBC Disonais',${da},${ptsA},${runsA.length},${Math.max(...runsA)},'${b[0]}','${b[1]}','${club2}',${db},${ptsB},${runsB.length},${Math.max(...runsB)},${winner},5,now(),now())`);
  const id = sql('select max(id) from scoreboard_matches');
  runsA.forEach((v, i) => sql(`insert into scoreboard_innings(match_id,set_no,inning_no,player,points) values (${id},1,${i + 1},1,${v}),(${id},1,${i + 1},2,${runsB[i] ?? 0})`));
  return id;
}

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
  const ui = () => page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.5 });
  const tv = () => page.setViewport({ width: 1920, height: 1080, deviceScaleFactor: 1 });
  const shot = (name, full = false) => page.screenshot({ path: `${out}/${name}.png`, fullPage: full });
  try {
    // 26-27 : rencontre officielle individuelle terminée, puis signée
    const m = finishedMatch({ table: 2, a: ['DUPONT', 'Jean'], b: ['LAMBERT', 'Marc'], da: 60, db: 60, runsA: [12, 0, 21, 9, 18], runsB: [4, 11, 0, 7, 3] });
    await tv();
    await page.goto(`${base}/marquoir/match/${m}`, { waitUntil: 'networkidle0' }); await wait(800);
    await shot('26_resultat_signer');

    await ui();
    await page.goto(`${base}/marquoir/match/${m}/signer`, { waitUntil: 'networkidle0' });
    await scribble(page, 0.0);
    await page.click('[data-role="p2"]'); await scribble(page, 0.6);
    await page.click('[data-role="referee"]'); await scribble(page, 1.3);
    await page.type('#refereeName', 'Pierre GOFFIN');
    await page.click('[data-role="p2"]');            // cadre du joueur B réaffiché, comme pendant la signature
    await page.mouse.move(0, 0); await wait(300);
    await shot('27_signature', true);

    // 28-29 : rencontre d'équipe CDR du jour, 3 matchs terminés sur les billards 2 et 3
    sql(`insert into scoreboard_team_encounters(code,competition,team_id,team_name,team_club,team_number,opponent_club,opponent_number,scheduled_at,discipline,format,recap_signature_required,created_at,updated_at)
         values ('${CODE}','CDR',1,'R.B.C. Disonais 1','RBC Disonais',1,'B.C. Welkenraedt',2,CONCAT(CURDATE(),' 19:30:00'),'libre','PB',1,now(),now())`);
    const e = sql(`select id from scoreboard_team_encounters where code='${CODE}'`);
    const team = { encounter: e, sign: 0, club2: 'B.C. Welkenraedt', comp: 'CDR' };
    finishedMatch({ ...team, table: 2, a: ['HUPPERTZ', 'Robert'], b: ['KEVER', 'Luc'], da: 70, db: 60, runsA: [15, 22, 0, 18, 15], runsB: [9, 3, 14, 0, 6] });
    finishedMatch({ ...team, table: 3, a: ['GILLET', 'Georges'], b: ['SCHMITZ', 'Jos'], da: 95, db: 95, runsA: [30, 12, 25, 0, 28], runsB: [20, 31, 8, 36, 0] });
    finishedMatch({ ...team, table: 2, a: ['CAVELIER', 'Nathalie'], b: ['MEYER', 'Anne'], da: 40, db: 40, runsA: [8, 0, 17, 15], runsB: [10, 6, 2, 9] });

    await page.goto(`${base}/marquoir/billard/2`, { waitUntil: 'networkidle0' });
    await shot('28_billard_rencontre');

    await page.goto(`${base}/marquoir/rencontre/${e}?billard=2`, { waitUntil: 'networkidle0' });
    await scribble(page, 0.3);
    await page.click('[data-role="away"]'); await scribble(page, 0.9);
    await page.mouse.move(0, 0); await wait(300);
    await shot('29_cloture', true);
  } finally {
    await b.close();
    sql(`delete from scoreboard_matches where team_encounter_id in (select id from scoreboard_team_encounters where code='${CODE}')`);
    sql(`delete from scoreboard_team_encounters where code='${CODE}'`);
    sql(`delete from scoreboard_matches where table_id=2 and p2_last_name='LAMBERT' and created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`);
    console.log('restant', sql(`select count(*) from scoreboard_matches where created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)`));
  }
})();
