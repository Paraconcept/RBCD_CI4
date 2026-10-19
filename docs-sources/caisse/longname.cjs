// Vérifie qu'un long nom (barman ou client) ne fait pas déborder la colonne de droite (écran 16:10 large)
// Usage (depuis docs-sources) : node caisse/longname.cjs <dossier de sortie>
const puppeteer = require('puppeteer-core');
const { CHROME, sql, scoreboardToken, BASE } = require('../lib.cjs');
const out = process.argv[2] || 'build';
const wait = (ms) => new Promise(r => setTimeout(r, ms));
(async () => {
  const id = +sql('select m.id from members_keys mk join members m on m.id = mk.member_id where mk.returned_date is null limit 1');
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  await p.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: BASE });
  await p.setRequestInterception(true);
  p.on('request', req => req.url().includes('/caisse/api/barmans')
    ? req.respond({ contentType: 'application/json', body: JSON.stringify([{ id, name: 'RANDAXHE PATRICK', planned: false }, { id: id + 100000, name: 'VAN DEN BERGHE-DE LA CROIX JEAN-PHILIPPE', planned: false }]) })
    : req.continue());
  let overflow = [];
  for (const [w, h] of [[1744, 1090], [1194, 834], [1280, 800]]) {
    await p.setViewport({ width: w, height: h, deviceScaleFactor: 1 });
    await p.goto(`${BASE}/caisse`, { waitUntil: 'networkidle0' });
    await p.click('#unlock');
    for (const k of 'RA') await p.click(`.key[data-k="${k}"]`);
    await wait(500);
    await p.click('#bmSugg .sg');
    await wait(300);
    const o1 = await p.evaluate(() => document.documentElement.scrollWidth > innerWidth || document.getElementById('sideOpen').scrollWidth > document.getElementById('sideOpen').clientWidth);
    await p.screenshot({ path: `${out}/long-${w}-a.png` });
    for (const k of 'VAN DEN BERGHE') await p.click(`.key[data-k="${k === ' ' ? ' ' : k}"]`);
    await wait(300);
    const o2 = await p.evaluate(() => document.documentElement.scrollWidth > innerWidth || document.getElementById('sideOpen').scrollWidth > document.getElementById('sideOpen').clientWidth);
    await p.screenshot({ path: `${out}/long-${w}-b.png` });
    overflow.push(`${w}×${h} : nom choisi ${o1 ? 'DÉBORDE' : 'ok'}, nom tapé long ${o2 ? 'DÉBORDE' : 'ok'}`);
  }
  console.log(overflow.join('\n'));
  sql("delete from scoreboard_devices where name='Captures documentation'");
  await b.close();
})();
