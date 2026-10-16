<?php
use App\Models\ScoreboardFinalModel as F;

$total    = count($matches);
$finished = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));
$complete = $total > 0 && $finished === $total;
$num      = static fn (float $v): string => number_format($v, 3, ',', '');
$lastDay  = $final->day2_at ?? $final->scheduled_at;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm 12mm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; color: #000; margin: 0; }
    table { border-collapse: collapse; }
    .sheet { border: 2px solid #000; }
    .grid { table-layout: fixed; }
    .grid .w-who { width: 25%; } .grid .w-opp { width: 23%; } .grid .w-last { width: 11%; }
    .grid.next { border-top: 0; }
    /* Un joueur n'est jamais coupé entre deux pages */
    .player { page-break-inside: avoid; }
    /* Beaucoup de matchs (5 à 7 joueurs) : lignes resserrées pour tenir sur une page */
    body.compact .grid td { padding-top: .1mm; padding-bottom: .1mm; font-size: 9pt; }
    body.compact .grid td.rank { font-size: 22pt; }
    .head td { padding: 1.5mm 3mm; vertical-align: middle; }
    .head .club, .head .season { font-weight: bold; font-size: 10.5pt; white-space: nowrap; }
    .head .title { font-weight: bold; font-size: 16pt; text-align: center; }
    .grid { width: 100%; border-top: 2px solid #000; }
    .grid th, .grid td { border: 1px solid #000; padding: .5mm 1mm; text-align: center; }
    .grid th { font-weight: normal; font-size: 7.5pt; }
    .grid th.big { font-size: 10pt; }
    .grid td.who { width: 25%; line-height: 1.3; border-bottom: 2px solid #000; }
    .grid td.who b { font-size: 10.5pt; }
    .grid td.opp { width: 23%; }
    .grid tr.sum td { color: #e00000; font-weight: bold; border-bottom: 2px solid #000; }
    .grid tr.sum td.opp { text-align: right; }
    .grid td.rank { font-size: 34pt; color: #1a8f1a; width: 11%; }
    .foot { width: 100%; margin-top: 12mm; font-size: 8.5pt; }
    .note { font-size: 8.5pt; margin-top: 4mm; }
</style>
</head>
<?php $rowsTotal = array_sum(array_map(static fn ($l) => count($l) + 1, $lines)); ?>
<body class="<?= $rowsTotal > 25 ? 'compact' : '' ?>">

<div class="sheet">
        <table class="head" style="width:100%">
            <tr>
                <td class="club" colspan="2">RBC Disonais</td>
                <td class="season" style="text-align:right">Saison <?= F::season($final->scheduled_at) ?></td>
            </tr>
            <tr>
                <td style="width:16%"><?php if ($logos['club']): ?><img src="<?= $logos['club'] ?>" style="height:16mm"><?php endif; ?></td>
                <td class="title"><?= esc($final->name) ?><?= $complete ? '' : '<br><small style="font-size:9pt;font-weight:normal">Provisoire : ' . $finished . ' / ' . $total . ' matchs terminés</small>' ?></td>
                <td style="width:16%"></td>
            </tr>
        </table>

        <table class="grid">
            <thead><tr>
                <th class="big w-who">NOMS &nbsp; CLUBS</th><th class="big w-opp">ADVERSAIRES</th>
                <th>Points de<br>Matches</th><th>Points<br>Réalisés</th><th>Reprises</th><th>Moyenne</th><th>Série</th><th class="w-last">Moyenne<br>Proportion.</th>
            </tr></thead>
        </table>
            <?php foreach ($standings as $r):
                $p    = $r['player'];
                $min  = (float) ($p->average_min ?? 0);
                $max  = (float) ($p->average_max ?? 0);
                $rows = $lines[(int) $p->id] ?? [];
                $span = max(1, count($rows));
                $who  = ['<b>' . esc(trim(mb_strtoupper($p->last_name) . ' ' . $p->first_name)) . '</b>', esc($p->license ?? ''), esc($p->category ?? ''),
                         $min > 0 ? $num($min) : '', $max > 0 ? $num($max) : '', esc($p->club ?? '')];
            ?>
            <div class="player"><table class="grid next">
                <?php foreach ($rows ?: [null] as $i => $l):
                    $m    = $l['match'] ?? null;
                    $done = $m && $m->status === 'finished';
                    $n    = $l['side'] ?? 1;
                    $pts  = $done ? (int) $m->{'p' . $n . '_points'} : 0;
                    $inn  = $done ? (int) $m->{'p' . $n . '_innings'} : 0;
                ?>
                <tr>
                    <?php if ($i === 0): ?><td class="who" rowspan="<?= $span + 1 ?>"><?= implode('<br>', array_filter($who, 'strlen')) ?></td><?php endif; ?>
                    <td class="opp"><?= $l ? esc(mb_strtoupper($l['opponent']->last_name ?? '—')) : '' ?></td>
                    <td><?= $done ? ((int) $m->winner === 0 ? 1 : ((int) $m->winner === $n ? 2 : 0)) : '' ?></td>
                    <td><?= $done ? $pts : '' ?></td>
                    <td><?= $done ? $inn : '' ?></td>
                    <td><?= $done ? $num($inn > 0 ? $pts / $inn : 0) : '' ?></td>
                    <td><?= $done ? (int) $m->{'p' . $n . '_high_run'} : '' ?></td>
                    <?php if ($i === 0): ?><td class="rank" rowspan="<?= $span ?>"><?= $r['played'] ? $r['rank'] : '' ?></td><?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <tr class="sum">
                    <td class="opp"><?= F::ratingLabel(F::rating($r, $levels[(int) $p->id] ?? null), 'cup') ?></td>
                    <td><?= $r['match_points'] ?></td>
                    <td><?= $r['points'] ?></td>
                    <td><?= $r['innings'] ?></td>
                    <td><?= $num($r['average']) ?></td>
                    <td><?= $r['high_run'] ?></td>
                    <td><?= $r['proportional'] !== null ? $num($r['proportional']) : '' ?></td>
                </tr>
            </table></div>
            <?php endforeach; ?>
</div>

<div style="text-align:center;margin-top:10mm;font-size:9pt">Dison, le <?= date('d.m.Y', strtotime($lastDay ?? 'now')) ?></div>
<table class="foot">
    <tr>
        <td style="width:50%;padding-left:6mm"><?= esc($signatories[0] ?? '') ?></td>
        <td style="text-align:right;padding-right:6mm"><?= esc($signatories[1] ?? '') ?></td>
    </tr>
</table>

<?php if (!empty($final->comment)): ?>
<div class="note"><b>Commentaire :</b> <?= nl2br(esc($final->comment)) ?></div>
<?php endif; ?>

</body>
</html>
