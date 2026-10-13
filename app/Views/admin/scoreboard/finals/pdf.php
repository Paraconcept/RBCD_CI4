<?php
use App\Models\ScoreboardMatchModel as M;

$total    = count($matches);
$finished = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));
$complete = $total > 0 && $finished === $total;
$fmt      = static fn (?float $v): string => $v === null ? '' : number_format($v, 3, ',', '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 9mm 11mm; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #000; margin: 0; }
    table { border-collapse: collapse; }
    .head { width: 100%; margin-bottom: 3mm; }
    .head td { vertical-align: middle; }
    .head .club { font-weight: bold; font-size: 12pt; }
    .head .title { text-align: center; font-weight: bold; font-size: 15pt; }
    .head .title small { display: block; font-size: 10pt; font-weight: normal; margin-top: 1mm; }
    .head .status { text-align: right; font-size: 9pt; }
    h2 { font-size: 10.5pt; margin: 4mm 0 1.5mm; text-transform: uppercase; letter-spacing: .5px; }
    .grid { width: 100%; border: 2px solid #000; }
    .grid th, .grid td { border: 1px solid #000; padding: 1mm 1.2mm; text-align: center; }
    .grid th { background: #e6e6e6; font-size: 8.5pt; }
    .grid td.name, .grid th.name { text-align: left; }
    .grid td.self { background: #bdbdbd; }
    .grid tr.first td { font-weight: bold; }
    .grid small { display: block; font-size: 7.5pt; color: #333; }
    .note { font-size: 8pt; color: #333; margin-top: 1mm; }
    .box { width: 100%; border: 2px solid #000; margin-top: 4mm; }
    .box td { padding: 2mm 3mm; vertical-align: top; }
</style>
</head>
<body>

<table class="head">
    <tr>
        <td style="width:28%"><?php if ($logo): ?><img src="<?= $logo ?>" style="height:18mm;vertical-align:middle;margin-right:3mm"><?php endif; ?><span class="club">RBC Disonais</span></td>
        <td class="title">
            <?= esc($final->name) ?>
            <small><?= $final->scheduled_at ? date('d/m/Y', strtotime($final->scheduled_at)) . ' · ' : '' ?><?= esc(M::disciplineLabel($final)) ?><?= $final->innings_limit ? ' · ' . (int) $final->innings_limit . ' reprises max.' : '' ?></small>
        </td>
        <td class="status" style="width:28%">FEUILLE RÉCAPITULATIVE<br><?= $complete ? 'Résultat final' : 'Provisoire : ' . $finished . ' / ' . $total . ' matchs terminés' ?></td>
    </tr>
</table>

<h2>Classement</h2>
<table class="grid">
    <tr>
        <th style="width:6%">Rang</th><th class="name">Joueur</th><th>Club</th><th>Cat.</th><th>Pts à jouer</th>
        <th>J</th><th>G</th><th>N</th><th>P</th><th>Pts match</th><th>Points</th><th>Reprises</th><th>Moyenne</th><th>Moy. prop.</th><th>MS</th>
    </tr>
    <?php foreach ($standings as $r): $p = $r['player']; ?>
    <tr class="<?= $complete && $r['rank'] === 1 ? 'first' : '' ?>">
        <td><?= $r['rank'] ?></td>
        <td class="name"><?= esc(trim($p->last_name . ' ' . $p->first_name)) ?></td>
        <td><?= esc($p->club ?? '') ?></td>
        <td><?= esc($p->category ?? '') ?></td>
        <td><?= (int) $p->distance ?></td>
        <td><?= $r['played'] ?></td><td><?= $r['won'] ?></td><td><?= $r['drawn'] ?></td><td><?= $r['lost'] ?></td>
        <td style="font-weight:bold"><?= $r['match_points'] ?></td>
        <td><?= $r['points'] ?></td><td><?= $r['innings'] ?></td>
        <td><?= number_format($r['average'], 3, ',', '') ?></td>
        <td><?= $fmt($r['proportional']) ?></td>
        <td><?= $r['high_run'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<div class="note">Victoire 2 pts · nul 1 pt · défaite 0 pt. Égalité : moyenne proportionnelle (moyenne ÷ moyenne minimum de la catégorie), puis meilleure série, puis match entre les ex æquo.</div>

<h2>Grille des résultats</h2>
<table class="grid">
    <tr>
        <th class="name" style="width:20%">Joueur</th>
        <?php foreach ($players as $i => $p): ?><th><?= $i + 1 ?>. <?= esc($p->last_name) ?></th><?php endforeach; ?>
    </tr>
    <?php foreach ($players as $i => $a): ?>
    <tr>
        <td class="name"><?= $i + 1 ?>. <?= esc(trim($a->last_name . ' ' . $a->first_name)) ?></td>
        <?php foreach ($players as $b): ?>
            <?php if ((int) $a->id === (int) $b->id): ?>
            <td class="self"></td>
            <?php else: $cell = $grid[(int) $a->id][(int) $b->id] ?? null; $m = $cell['match'] ?? null; ?>
            <td>
                <?php if ($m && $m->status === 'finished'): $n = $cell['side']; $o = 3 - $n; $w = (int) $m->winner; ?>
                <b><?= $w === 0 ? 1 : ($w === $n ? 2 : 0) ?></b>
                <small><?= (int) $m->{'p' . $n . '_points'} ?> – <?= (int) $m->{'p' . $o . '_points'} ?> en <?= max((int) $m->p1_innings, (int) $m->p2_innings) ?> rep.</small>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>
<div class="note">Points de match du joueur de la ligne contre celui de la colonne, puis son score.</div>

<?php if (!empty($final->comment)): ?>
<table class="box"><tr><td><b>Commentaire :</b> <?= nl2br(esc($final->comment)) ?></td></tr></table>
<?php endif; ?>

<table class="box" style="height:16mm"><tr><td><b>Signature du directeur sportif :</b></td><td style="width:50%"><b>Signature de l'arbitre :</b></td></tr></table>

</body>
</html>
