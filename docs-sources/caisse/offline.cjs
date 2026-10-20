// Capture de l'écran rouge « Pas de connexion Internet » (coupure du réseau simulée dans Chrome).
// Usage (depuis docs-sources) : node caisse/offline.cjs caisse/img
const puppeteer = require('puppeteer-core');
const path = require('path');
const { CHROME, scoreboardToken, BASE } = require('../lib.cjs');
const wait = (ms) => new Promise(r => setTimeout(r, ms));
const out = path.resolve(process.argv[2] || 'caisse/img');

(async () => {
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  await p.setViewport({ width: 1194, height: 834, deviceScaleFactor: 2 });
  await p.evaluateOnNewDocument(() => {
    document.addEventListener('DOMContentLoaded', () => {
      const s = document.createElement('style'); s.textContent = '#debug-icon,#debug-bar,.debug-bar-ndisplay,#unlock{display:none!important}'; document.head.appendChild(s);
    });
  });
  await p.setCookie({ name: 'rbcd_scoreboard', value: scoreboardToken(), url: BASE });
  await p.goto(BASE + '/caisse', { waitUntil: 'networkidle2' });
  await wait(1500);
  const cdp = await p.target().createCDPSession();
  await cdp.send('Network.enable');
  await cdp.send('Network.emulateNetworkConditions', { offline: true, latency: 0, downloadThroughput: -1, uploadThroughput: -1 });
  await p.waitForFunction(() => !document.getElementById('offline').hidden, { timeout: 15000 });
  // Heure fixe et animation arrêtée, pour une capture identique d'une fois à l'autre
  await p.evaluate(() => {
    document.getElementById('offlineSince').textContent = 'Coupure depuis 21:42 · nouvel essai toutes les 2 secondes';
    document.querySelector('.offline-icon').style.animation = 'none';
  });
  await p.screenshot({ path: path.join(out, 'c10_hors_ligne.png') });
  console.log('c10_hors_ligne');
  await b.close();
})();
