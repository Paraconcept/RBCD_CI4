// Outils communs aux scripts de captures : accès MySQL (identifiants lus dans le .env du projet)
// et jeton d'accès au marquoir (calculé depuis le code PIN enregistré en base, jamais écrit ici).
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

const scoreboardToken = () => {
  const pin = sql("select value from site_settings where `key`='scoreboard_pin'");
  return crypto.createHash('sha256').update('rbcd-scoreboard|' + pin).digest('hex');
};

module.exports = { ROOT, CHROME, sql, scoreboardToken, BASE: 'http://localhost:8888/RBCD/CI4_2026/public' };
