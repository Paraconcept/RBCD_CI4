<?php
use App\Models\ScoreboardFinalModel as F;
use App\Models\ScoreboardMatchModel as M;

$total    = count($matches);
$finished = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));
$complete = $total > 0 && $finished === $total;
$num      = static fn (float $v, int $d): string => number_format($v, $d, ',', '');
$ref      = current(array_filter($levels)) ?: null;
$formats  = ['PB' => 'P.F.', 'GB' => 'G.F.'];
$dists    = array_unique(array_map(static fn ($p) => (int) $p->distance, $players));
sort($dists);
$lastDay  = $final->day2_at ?? $final->scheduled_at;
$short    = static fn (?object $p): string => $p ? mb_convert_case(mb_strtolower($p->last_name), MB_CASE_TITLE) . ' ' . mb_substr($p->first_name, 0, 2) : '—';

$best = ['match' => 0.0, 'run' => 0, 'points' => 0, 'innings' => 0];
foreach ($standings as $r) {
    $best['points']  += $r['points'];
    $best['innings'] += $r['innings'];
    $best['run']      = max($best['run'], $r['high_run']);
    $best['match']    = max($best['match'], $r['match_averages'][0] ?? 0.0);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm 12mm; }
    body { font-family: "Times New Roman", Times, serif; font-size: 11pt; color: #000; margin: 0; }
    table { border-collapse: collapse; }
    .fed { width: 100%; margin-bottom: 4mm; }
    .fed td { vertical-align: middle; text-align: center; }
    .fed .t { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; line-height: 1.35; }
    .fed .t b { font-size: 10.5pt; }
    .sheet { width: 100%; border: 2px solid #000; }
    .info td { padding: 1.5mm 3mm; font-style: italic; }
    .info a { color: #1a4fb4; }
    .title { font-size: 15pt; font-weight: bold; text-decoration: underline; }
    .grid { width: 100%; border-top: 2px solid #000; }
    .grid th, .grid td { border: 1px solid #000; padding: .6mm 1mm; text-align: center; font-style: italic; }
    .grid th { font-weight: normal; font-size: 8pt; }
    .grid th.big { font-size: 13pt; text-align: left; }
    .grid td.who { font-size: 11.5pt; line-height: 1.25; width: 22%; }
    .grid td.opp { width: 17%; }
    .grid tr.sum td { color: #e00000; font-weight: bold; border-bottom: 2px solid #000; }
    .grid tr.sum td.who { color: #000; font-weight: normal; }
    .grid td.rank { font-size: 30pt; font-weight: bold; width: 10%; }
    .grid td.label { font-size: 10pt; }
    .foot { width: 100%; margin-top: 2mm; font-style: italic; }
    .foot td { padding: .4mm 1mm; }
    .note { font-size: 8.5pt; margin-top: 2mm; font-style: italic; }
</style>
</head>
<body>

<table class="fed">
    <tr>
        <td style="width:18%"><?php if ($logos['frbb']): ?><img src="<?= $logos['frbb'] ?>" style="height:20mm"><?php endif; ?></td>
        <td class="t">
            <b>FEDERATION ROYALE BELGE DE BILLARD A.S.B.L.</b><br>
            Union Mondiale de Billard<br>Confédération Européenne de Billard<br>
            Comité Olympique et Interfédéral Belge<br>Région Liège-Luxembourg
        </td>
        <td style="width:18%"><?php if ($logos['region']): ?><img src="<?= $logos['region'] ?>" style="height:20mm"><?php endif; ?></td>
    </tr>
</table>

<table class="sheet">
    <tr><td>
        <table class="info" style="width:100%">
            <tr>
                <td colspan="2"><a href="http://www.frbb-liège-lux.be">http://www.frbb-liège-lux.be</a></td>
                <td style="text-align:right">Saison <?= str_replace('-', ' - ', F::season($final->scheduled_at)) ?></td>
            </tr>
            <tr>
                <td style="font-weight:bold"><?= esc($ref['group'] ?? '') ?> &nbsp; <?= esc(M::DISCIPLINES[$final->discipline] ?? $final->discipline) ?> <?= $formats[$final->format] ?? '' ?></td>
                <td style="font-weight:bold"><?= $ref ? 'Moyenne: ' . $num($ref['group_min'], 3) . ($ref['group_max'] !== null ? ' à ' . $num($ref['group_max'], 3) : '') : '' ?></td>
                <td style="font-weight:bold;text-align:right">Pts à jouer: &nbsp; <?= count($dists) === 1 ? $dists[0] : ($dists ? $dists[0] . ' à ' . end($dists) : '') ?></td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top:3mm"><span class="title">Classement finale championnat régional</span><?= $complete ? '' : ' &nbsp; (provisoire : ' . $finished . ' / ' . $total . ' matchs)' ?></td>
                <td style="text-align:right;padding-top:3mm"><?= $ref && $ref['dp'] !== null ? 'DP si &gt; ' . $num($ref['dp'], 3) : '' ?></td>
            </tr>
        </table>

        <table class="grid">
            <tr>
                <th class="big">Nom &amp; Prénom<br>Licence &amp; Club</th>
                <th style="font-size:12pt">Adversaires</th>
                <th>Points de<br>Matchs</th><th>Caramb.<br>Réalisées</th><th>Reprises</th>
                <th>Moyenne<br>Général</th><th>Série</th><th>Moyenne<br>Prop</th>
                <th style="font-size:11pt">Classement</th>
            </tr>
            <?php foreach ($standings as $r):
                $p    = $r['player'];
                $min  = (float) ($p->average_min ?? 0);
                $rows = $lines[(int) $p->id] ?? [];
                $span = max(1, count($rows));
                $who  = [esc(mb_convert_case(mb_strtolower($p->last_name), MB_CASE_TITLE)), esc($p->first_name), esc($p->license ?? ''), esc($p->category ?? ''), esc($p->club ?? '')];
            ?>
                <?php foreach ($rows ?: [null] as $i => $l):
                    $m    = $l['match'] ?? null;
                    $done = $m && $m->status === 'finished';
                    $n    = $l['side'] ?? 1;
                    $pts  = $done ? (int) $m->{'p' . $n . '_points'} : 0;
                    $inn  = $done ? (int) $m->{'p' . $n . '_innings'} : 0;
                    $avg  = $inn > 0 ? $pts / $inn : 0.0;
                ?>
                <tr>
                    <?php if ($i === 0): ?><td class="who" rowspan="<?= $span + 1 ?>"><?= implode('<br>', array_filter($who, 'strlen')) ?></td><?php endif; ?>
                    <td class="opp"><?= $l ? esc($short($l['opponent'])) : '' ?></td>
                    <td><?= $done ? ((int) $m->winner === 0 ? 1 : ((int) $m->winner === $n ? 2 : 0)) : '' ?></td>
                    <td><?= $done ? $pts : '' ?></td>
                    <td><?= $done ? $inn : '' ?></td>
                    <td><?= $done ? $num($avg, 2) : '' ?></td>
                    <td><?= $done ? (int) $m->{'p' . $n . '_high_run'} : '' ?></td>
                    <td><?= $done && $min > 0 ? $num($avg / $min, 3) : '' ?></td>
                    <?php if ($i === 0): ?>
                    <td class="rank" rowspan="<?= $span ?>">
                        <?php if ($complete && $r['rank'] === 1 && $logos['region']): ?><img src="<?= $logos['region'] ?>" style="height:16mm"><?php else: ?><?= $r['played'] ? $r['rank'] : '' ?><?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <tr class="sum">
                    <td></td>
                    <td><?= $r['match_points'] ?></td>
                    <td><?= $r['points'] ?></td>
                    <td><?= $r['innings'] ?></td>
                    <td><?= $num($r['average'], 2) ?></td>
                    <td><?= $r['high_run'] ?></td>
                    <td><?= $r['proportional'] !== null ? $num($r['proportional'], 3) : '' ?></td>
                    <td class="label"><?= F::ratingLabel(F::rating($r, $levels[(int) $p->id] ?? null), 'championship') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <table class="foot">
            <tr><td style="width:28%">Meilleur match:</td><td style="width:22%"><?= $num($best['match'], 2) ?></td><td></td><td></td></tr>
            <tr><td>Meilleure série:</td><td><?= $best['run'] ?></td><td></td><td></td></tr>
            <tr><td>Total Points:</td><td><?= $best['points'] ?></td><td style="white-space:nowrap">Moyenne générale de l'épreuve:</td><td><?= $num($best['innings'] > 0 ? $best['points'] / $best['innings'] : 0, 2) ?></td></tr>
            <tr><td>Total reprises:</td><td><?= $best['innings'] ?></td><td></td><td></td></tr>
            <tr><td>Direction sportive:</td><td colspan="2" style="text-align:center">R.B.C. Disonais</td><td style="text-align:right"><?= $lastDay ? date('d-m-y', strtotime($lastDay)) : '' ?></td></tr>
        </table>
    </td></tr>
</table>

<table class="foot">
    <tr><td style="width:28%">Directeur sportif régional</td><td><?= esc($final->regional_contact ?? '') ?></td></tr>
</table>

<?php if (!empty($final->comment)): ?>
<div class="note"><b>Commentaire :</b> <?= nl2br(esc($final->comment)) ?></div>
<?php endif; ?>

</body>
</html>
