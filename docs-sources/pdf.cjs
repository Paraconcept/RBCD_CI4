// HTML → PDF (format de page donné par le @page du document) + aperçus PNG page par page
// Usage : node pdf.cjs <fichier.html> <sortie.pdf> [dossier-aperçus]
const puppeteer = require('puppeteer-core');
const path = require('path');
const fs = require('fs');
const { CHROME } = require('./lib.cjs');
(async () => {
  const [html, pdf, prev] = process.argv.slice(2);
  const b = await puppeteer.launch({ executablePath: CHROME, headless: true });
  const p = await b.newPage();
  await p.goto('file://' + path.resolve(html), { waitUntil: 'networkidle0' });
  await p.evaluateHandle('document.fonts.ready');
  await p.pdf({ path: pdf, printBackground: true, preferCSSPageSize: true });
  if (prev) {
    fs.mkdirSync(prev, { recursive: true });
    await p.setViewport({ width: 1123, height: 794, deviceScaleFactor: 1 });
    const pages = await p.$$('.page');
    for (let i = 0; i < pages.length; i++) await pages[i].screenshot({ path: `${prev}/p${String(i + 1).padStart(2, '0')}.png` });
    if (!pages.length) await p.screenshot({ path: `${prev}/p01.png`, fullPage: true });
  }
  console.log(pdf);
  await b.close();
})();
