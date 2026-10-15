<?php
ob_start();
chdir('/Applications/MAMP/htdocs/RBCD/CI4_2026');
define('HOMEPATH', getcwd() . '/');
// Le banc de test de CI4 utilise le groupe « tests » : on le fait pointer sur la base MySQL locale
$env = parse_ini_file(getcwd() . '/.env', false, INI_SCANNER_RAW);
$cfg = static fn (string $k, string $d) => trim((string) ($env['database.default.' . $k] ?? $d), "'\" ");
foreach (['hostname' => '127.0.0.1', 'database' => $cfg('database', 'rbcd_ci4_2026'), 'username' => $cfg('username', 'root'), 'password' => $cfg('password', ''), 'DBDriver' => 'MySQLi', 'DBPrefix' => '', 'port' => $cfg('port', '8889')] as $k => $v) {
    $_SERVER['database.tests.' . $k] = $_ENV['database.tests.' . $k] = $v; putenv("database.tests.$k=$v");
}
require 'vendor/codeigniter4/framework/system/Test/bootstrap.php';
use App\Models\ScoreboardFinalModel as F;
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;
$out = __DIR__ . '/pages/';
$log = fn (...$a) => fwrite(STDERR, implode('', $a) . "\n");
$db  = db_connect();
$maxPlayer = (int) $db->query('select coalesce(max(id),0) m from scoreboard_players')->getRow()->m;
$mm = new M(); $te = new TE(); $fm = new F();
$ctl = function (string $class) { $c = new $class(); $c->initController(service('request'), service('response'), service('logger')); return $c; };
$save = function (string $name, string $html) use ($out) { file_put_contents($out . $name . '.html', $html); service('renderer')->resetData(); };
// Partie crédible : séries tirées au hasard (graine fixe), reprise d'égalisation, limite de reprises
$sim = function (int $d1, int $d2, array $pool, int $seed, int $limit = 99): array {
    mt_srand($seed);
    $t = [0, 0]; $d = [$d1, $d2]; $inn = [];
    for ($n = 1; $n <= $limit; $n++) {
        foreach ([0, 1] as $p) {
            $v = min($pool[mt_rand(0, count($pool) - 1)], $d[$p] - $t[$p]);
            $t[$p] += $v; $inn[] = $v;
        }
        if ($t[0] >= $d1 || $t[1] >= $d2) break;
    }
    return $inn;
};
$play = function (int $id, array $innings) use ($mm) {
    $mm->update($id, ['status' => 'live', 'starting_player' => 1, 'started_at' => M::now()]);
    $mm->applyState($mm->find($id), ['rev' => 9, 'innings' => $innings, 'current_run' => 0, 'extensions' => [0, 0], 'finished' => true]);
};
$ids = ['matches' => [], 'te' => null, 'final' => null];
try {
    // Joueurs externes capturés avant la création des données de démonstration (qui y ajoutent leurs joueurs)
    $save('players', $ctl(\App\Controllers\Admin\ScoreboardSetupController::class)->players());
    $today = date('Y-m-d');
    // Rencontres individuelles officielles
    $base = ['type' => 'official', 'status' => 'scheduled', 'format' => 'PB', 'p1_club' => 'RBC Disonais'];
    $ids['matches'][] = $a = $mm->insert($base + ['scheduled_at' => "$today 20:00:00", 'competition' => 'Championnat Libre PF', 'discipline' => 'libre', 'table_id' => 3,
        'p1_last_name' => 'DUPONT', 'p1_first_name' => 'Jean', 'p1_distance' => 60, 'p2_last_name' => 'LAMBERT', 'p2_first_name' => 'Marc', 'p2_club' => 'B.C. Herve', 'p2_distance' => 60]);
    $ids['matches'][] = $b = $mm->insert($base + ['scheduled_at' => "$today 19:30:00", 'competition' => 'Championnat 3 Bandes PF', 'discipline' => '3bandes', 'table_id' => 4, 'innings_limit' => 50,
        'p1_last_name' => 'RENARD', 'p1_first_name' => 'Louis', 'p1_distance' => 25, 'p2_last_name' => 'SIMON', 'p2_first_name' => 'Éric', 'p2_club' => 'B.C. Eupen', 'p2_distance' => 25,
        'comment' => 'Partie interrompue 10 minutes (éclairage du billard 4).']);
    $play($b, $sim(25, 25, [0, 0, 0, 0, 1, 1, 1, 2, 3], 7, 50));

    // Rencontre d'équipe CDR
    $team = $db->table('cdr_teams')->orderBy('id', 'DESC')->get()->getRow();
    $ids['te'] = $teId = $te->insert(['code' => 'LPF099', 'competition' => 'CDR', 'team_id' => $team->id, 'team_name' => $team->name, 'team_club' => 'RBC Disonais',
        'team_number' => '1', 'opponent_club' => 'B.C. Herve', 'opponent_number' => '2', 'team_email' => 'club@rbcd.be', 'opponent_email' => 'secretariat@bc-herve.be',
        'scheduled_at' => "$today 19:30:00", 'discipline' => 'libre', 'format' => 'PB', 'recap_comment' => 'Rencontre jouée sans incident.']);
    $e = $te->find($teId);
    $roster = $te->getRoster((int) $team->id);
    foreach (array_slice($roster, 0, 3) as $i => $r) {
        $mid = $mm->insert(['type' => 'official', 'status' => 'scheduled', 'team_encounter_id' => $teId, 'scheduled_at' => "$today 19:30:00", 'discipline' => 'libre', 'format' => 'PB',
            'table_id' => $i + 1, 'p1_member_id' => $r->id, 'p1_license' => $r->frbb_license, 'p1_last_name' => mb_strtoupper($r->last_name), 'p1_first_name' => $r->first_name,
            'p1_club' => 'RBC Disonais 1', 'p1_distance' => [60, 50, 40][$i], 'p2_last_name' => ['MARTIN', 'LEGRAND', 'ABSENT'][$i], 'p2_first_name' => ['Paul', 'Luc', ''][$i],
            'p2_club' => 'B.C. Herve 2', 'p2_distance' => [55, 50, 0][$i], 'p2_license' => ['4521', '3987', null][$i]]);
        $ids['matches'][] = $mid;
        if ($i === 0) $play($mid, $sim(60, 55, [0, 1, 1, 2, 3, 4, 5, 7, 9], 11));
        if ($i === 1) $play($mid, $sim(50, 50, [0, 1, 2, 2, 3, 4, 6, 8], 23));
        if ($i === 2) $mm->update($mid, M::forfeitData(2, "$today 19:30:00"));
    }

    // Finale (4 joueurs, 2 billards), rendue avant puis pendant
    $cats = $db->query("select id, points from categories_new where game_mode='B3PF' order by average_min desc limit 4")->getResult();
    $post = ['name' => 'Finale du club 3 Bandes PF', 'scheduled_date' => $today, 'scheduled_time' => '09:00', 'discipline' => '3bandes', 'format' => 'PB', 'table_ids' => ['3', '4'],
             'innings_limit_enabled' => '1', 'innings_limit' => '50'];
    foreach ([['HUPPERTZ', 'Robert'], ['GILLET', 'Georges'], ['CAVELIER', 'Nathalie'], ['RANDAXHE', 'Patrick']] as $i => [$ln, $fn]) {
        $k = $i + 1;
        $post += ["f{$k}_last_name" => $ln, "f{$k}_first_name" => $fn, "f{$k}_club" => 'RBC Disonais', "f{$k}_category_id" => $cats[$i]->id, "f{$k}_distance" => $cats[$i]->points];
    }
    service('request')->setGlobal('post', $post);
    $ctl(\App\Controllers\Admin\ScoreboardFinalController::class)->store();
    $final = $fm->where('name', 'Finale du club 3 Bandes PF')->orderBy('id', 'DESC')->first();
    $ids['final'] = $final->id;
    service('request')->setGlobal('post', []);
    session()->remove('success'); session()->remove('__ci_vars');

    service('renderer')->resetData();
    $fc = $ctl(\App\Controllers\Admin\ScoreboardFinalController::class);
    $save('final_form', $fc->edit($final->id));
    $fms = $fm->getMatches($final->id);
    foreach (array_slice($fms, 0, 4) as $i => $m) {
        $play((int) $m->id, $sim((int) $m->p1_distance, (int) $m->p2_distance, [0, 0, 0, 0, 1, 1, 1, 2, 3], 100 + $i, 50));
    }
    $mm->update($fms[4]->id, ['status' => 'live', 'starting_player' => 1, 'started_at' => M::now(), 'p1_points' => 14, 'p2_points' => 11, 'p1_innings' => 19, 'p2_innings' => 18]);

    // Pages
    $sc = $ctl(\App\Controllers\Admin\ScoreboardController::class);
    $save('index', $sc->index());
    $save('match_create', $sc->create());
    $save('match_show', $sc->show($b));
    $save('team_match_create', $sc->createForTeam($teId));
    $tc = $ctl(\App\Controllers\Admin\ScoreboardTeamController::class);
    $save('teams_index', $tc->index());
    $save('team_form', $tc->edit($teId));
    $save('team_show', $tc->show($teId));
    file_put_contents($out . 'team_recap.pdf', $tc->pdf($teId)->getBody());
    file_put_contents($out . 'match.pdf', $sc->pdf($b)->getBody());
    $save('finals_index', $fc->index());
    $save('final_show', $fc->show($final->id));
    file_put_contents($out . 'final.pdf', $fc->pdf($final->id)->getBody());
    $st = $ctl(\App\Controllers\Admin\ScoreboardSetupController::class);
    $save('tables', $st->tables());
    $save('settings', $st->settings());
    $log('ok');
} finally {
    if ($ids['final']) $fm->delete($ids['final']);
    if ($ids['te']) { $db->table('scoreboard_matches')->where('team_encounter_id', $ids['te'])->delete(); $te->delete($ids['te']); }
    if ($ids['matches']) $db->table('scoreboard_matches')->whereIn('id', $ids['matches'])->delete();
    $db->table('scoreboard_players')->where('id >', $maxPlayer)->delete();
    $log('nettoyé');
}
