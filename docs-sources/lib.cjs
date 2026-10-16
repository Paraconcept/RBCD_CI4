// Outils communs aux scripts de captures : accès MySQL (identifiants lus dans le .env du projet)
// et clé d'accès au marquoir (appareil « Captures documentation » créé en base, jamais écrit ici).
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { execFileSync } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const CHROME = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
const MYSQL = '/Applications/MAMP/Library/bin/mysql';

const env = {};
for (const line of fs.readFileSync(path.join(ROOT, '.env'), 'utf8').split('\n')) {
  const m = line.match(/^\s*([\w.]+)\s*=\s*'?([^'#]*?)'?\s*$/);
  if (m) env[m[1]] = m[2];
}
const db = (k, d) => env['database.default.' + k] || d;

const sql = (q) => execFileSync(MYSQL, [
  '-u' + db('username', 'root'), '-p' + db('password', ''), '-h127.0.0.1', '-P' + db('port', '8889'), db('database', 'rbcd_ci4_2026'), '-N', '-e', q,
], { stdio: ['ignore', 'pipe', 'ignore'] }).toString().trim();

// Une nouvelle clé à chaque lancement ; l'appareil apparaît dans Marquoir › Paramètres (à retirer si besoin)
const scoreboardToken = () => {
  const token = crypto.randomBytes(32).toString('hex');
  const hash = crypto.createHash('sha256').update(token).digest('hex');
  sql("delete from scoreboard_devices where name='Captures documentation'");
  sql(`insert into scoreboard_devices (name, token_hash, created_at) values ('Captures documentation', '${hash}', now())`);
  return token;
};

module.exports = { ROOT, CHROME, sql, scoreboardToken, BASE: 'http://localhost:8888/RBCD/CI4_2026/public' };
