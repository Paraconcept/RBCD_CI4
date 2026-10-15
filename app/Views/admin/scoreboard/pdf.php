<?php
use App\Models\ScoreboardMatchModel as M;

$perPage    = \App\Libraries\MatchSheetPdf::INNINGS_PER_PAGE;
$date       = $match->started_at ?? $match->scheduled_at;
// Provisoire pour le jeu par sets (modèle de feuille à venir) : une feuille par set
$sheets     = $sheets ?: [1 => []];

$cell = static function (array $sheet, int $n, int $p, string $key): string {
    return isset($sheet[$n][$p]) ? (string) $sheet[$n][$p][$key] : '';
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 9mm 10mm; }
    * { box-sizing: border-box; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #000; margin: 0; }
    .page { page-break-after: always; }
    .page:last-child { page-break-after: auto; }
    .layout { width: 100%; border-collapse: collapse; }
    .layout > tbody > tr > td { vertical-align: top; padding: 0; }

    /* Grille des reprises */
    .grid { border-collapse: collapse; width: 100%; border: 2px solid #000; }
    .grid td { border: 1px solid #000; height: 6.95mm; padding: 0 1mm; text-align: center; font-size: 9pt; }
    .grid td.no { text-align: left; width: 7mm; font-size: 9pt; border-right: 2px solid #000; }
    .grid td.sep { border-left: 2px solid #000; }
    .grid td.grp { border-right: 2px solid #000; }
    .grid tr.head td { border-bottom: 2px solid #000; font-size: 7pt; font-weight: bold; text-transform: uppercase; }
    .grid td.tot { font-weight: bold; }

    /* Colonne de droite */
    .side { padding-left: 7mm !important; }
    .federation { text-align: center; font-weight: bold; font-size: 10.5pt; margin: 1mm 0 6mm; }
    .club { border: 1.5px solid #555; background: #d4d4d4; padding: 1.5mm 2.5mm; position: relative; height: 41mm; }
    .club .name { font-weight: bold; font-size: 12.5pt; text-decoration: underline; margin-bottom: 2mm; }
    .club .line { font-size: 10pt; line-height: 6.6mm; }
    .club img { position: absolute; right: 9mm; top: 9mm; width: 21mm; }
    .field { border-bottom: 1px solid #000; height: 7mm; padding-top: 1.5mm; font-size: 10pt; }
    .field.thick { border-bottom-width: 2px; }
    .field b { font-weight: normal; }
    .field span { font-weight: bold; }

    .result { width: 100%; border-collapse: collapse; margin-top: 0; }
    .result td { border-bottom: 1px solid #000; height: 6.95mm; font-size: 10pt; padding: 0 1mm; }
    .result td.lbl { width: 17mm; }
    .result td.a { border-right: 2.5px solid #000; font-weight: bold; }
    .result td.b { font-weight: bold; }
    .result tr.title td { text-align: center; font-weight: normal; border-bottom: 3px solid #000; }
    .result tr.split td { border-bottom: 2.5px solid #000; }
    .result tr.sign td { border-right: none; font-weight: normal; }
    .result tr.sign.signed td { height: 9.5mm; vertical-align: middle; }
    .result tr.sign img { height: 9mm; vertical-align: middle; margin-left: 2mm; }
    .result tr.sign .signed-at { font-size: 6.5pt; color: #444; }
    .result tr.sign .referee { font-weight: bold; margin-left: 2mm; }
    .small { font-size: 8pt; }
<?php // Niveaux de compactage appliqués par MatchSheetPdf tant qu'une feuille déborde sur la page suivante ?>
<?php if (($compact ?? 0) >= 1): ?>
    .federation { margin: 0 0 3mm; }
    .club { height: 35mm; padding-top: 1mm; }
    .club .line { line-height: 5.6mm; }
    .club img { top: 7mm; width: 18mm; }
    .spacer { height: 1mm !important; }
    .remarks { font-size: 7.5pt !important; line-height: 1.15; }
<?php endif; ?>
<?php if (($compact ?? 0) >= 2): ?>
    .field { height: 6mm; padding-top: 1mm; }
    .result td { height: 6.2mm; }
    .result tr.sign.signed td { height: 8.5mm; }
    .result tr.sign img { height: 8mm; }
    .remarks { font-size: 6.8pt !important; }
<?php endif; ?>
<?php if (($compact ?? 0) >= 3): ?>
    .club { height: 32mm; }
    .club .line { line-height: 4.8mm; font-size: 9pt; }
    .club img { width: 15mm; }
    .remarks { font-size: 6pt !important; line-height: 1.05; }
<?php endif; ?>
</style>
</head>
<body>
<?php foreach ($sheets as $setNo => $sheet):
    $lastInning = $sheet ? max(array_keys($sheet)) : 0;
    $pages      = max(1, (int) ceil($lastInning / $perPage));
    for ($page = 0; $page < $pages; $page++): ?>
<div class="page">
<table class="layout">
<tr>
<td style="width:59%">
    <table class="grid">
        <tr class="head">
            <?php for ($col = 0; $col < 3; $col++): ?>
            <td class="no"></td>
            <td colspan="2" class="grp"><?= esc(mb_substr($match->p1_last_name, 0, 12)) ?></td>
            <td colspan="2" class="<?= $col < 2 ? 'grp' : '' ?>"><?= esc(mb_substr($match->p2_last_name, 0, 12)) ?></td>
            <?php endfor; ?>
        </tr>
        <?php for ($row = 1; $row <= 25; $row++): ?>
        <tr>
            <?php for ($col = 0; $col < 3; $col++): $n = $page * $perPage + $col * 25 + $row; ?>
            <td class="no"><?= $n ?></td>
            <td><?= $cell($sheet, $n, 1, 'points') ?></td>
            <td class="grp tot"><?= $cell($sheet, $n, 1, 'total') ?></td>
            <td><?= $cell($sheet, $n, 2, 'points') ?></td>
            <td class="tot <?= $col < 2 ? 'grp' : '' ?>"><?= $cell($sheet, $n, 2, 'total') ?></td>
            <?php endfor; ?>
        </tr>
        <?php endfor; ?>
    </table>
</td>
<td class="side">
    <div class="federation">FRBB Rég. Liège-Luxembourg</div>

    <div class="club">
        <div class="name">Royal Billard Club Disonais asbl</div>
        <div class="line">Chalet Bonvoisin</div>
        <div class="line">Av. Reine Elisabeth 46 B</div>
        <div class="line">4820 DISON</div>
        <div class="line">Tél. : 0494 / 797 353</div>
        <div class="line">URL : http://www.rbcd.be/</div>
        <?php if ($logo): ?><img src="<?= $logo ?>" alt=""><?php endif; ?>
    </div>

    <?php // Les cases de signature remplies sont plus hautes : on reprend la place sur les espacements ?>
    <div class="spacer" style="height:<?= $signature ? 2 : 6 ?>mm"></div>
    <div class="field"><b>Date :</b> <span><?= $date ? date('d/m/Y', strtotime($date)) : '' ?></span></div>
    <div class="field thick"><b>Epreuve :</b> <span><?= esc(M::competitionLabel($match) ?: ($match->type === 'friendly' ? 'Partie amicale' : '')) ?></span></div>
    <?php if (!empty($match->comment)): ?>
    <div class="field remarks" style="height:auto;min-height:7mm;font-size:8.5pt;padding-bottom:1mm"><b>Remarques :</b> <?= ($compact ?? 0) >= 3
        ? esc(implode(' / ', preg_split('/\s*\R\s*/', trim($match->comment))))
        : nl2br(esc(preg_replace('/(\R\s*){2,}/', "\n", trim($match->comment)))) ?></div>
    <?php else: ?>
    <div class="field"></div>
    <?php endif; ?>
    <div class="spacer" style="height:<?= $signature ? 1 : 7 ?>mm"></div>
    <div class="field thick"><b>Mode de jeu :</b> <span><?= esc(M::disciplineLabel($match)) ?><?= $match->innings_limit ? ' — ' . (int) $match->innings_limit . ' reprises max.' : '' ?><?= M::isSets($match) ? ' — set ' . $setNo . ' (' . esc(M::distanceLabel($match)) . ')' : '' ?></span><?= $pages > 1 ? ' <span class="small">(feuille ' . ($page + 1) . '/' . $pages . ')</span>' : '' ?></div>

    <table class="result">
        <tr class="title"><td colspan="3">RESULTAT</td></tr>
        <tr><td class="lbl">Nom</td><td class="a"><?= esc(M::playerLabel($match, 'p1')) ?></td><td class="b"><?= esc(M::playerLabel($match, 'p2')) ?></td></tr>
        <tr><td class="lbl">Club</td><td class="a"><?= esc($match->p1_club) ?></td><td class="b"><?= esc($match->p2_club) ?></td></tr>
        <tr><td class="lbl">Cat.</td><td class="a"><?= esc($match->p1_category ?? '') ?></td><td class="b"><?= esc($match->p2_category ?? '') ?></td></tr>
        <tr class="split"><td class="lbl">P à J</td><td class="a"><?= (int) $match->p1_distance ?></td><td class="b"><?= (int) $match->p2_distance ?></td></tr>
        <?php $done = $match->status !== 'scheduled'; ?>
        <tr><td class="lbl">Pts</td><td class="a"><?= $done ? (int) $match->p1_points : '' ?></td><td class="b"><?= $done ? (int) $match->p2_points : '' ?></td></tr>
        <tr><td class="lbl">Rep</td><td class="a"><?= $done ? (int) $match->p1_innings : '' ?></td><td class="b"><?= $done ? (int) $match->p2_innings : '' ?></td></tr>
        <tr><td class="lbl">Moy</td><td class="a"><?= $done ? M::average((int) $match->p1_points, (int) $match->p1_innings) : '' ?></td><td class="b"><?= $done ? M::average((int) $match->p2_points, (int) $match->p2_innings) : '' ?></td></tr>
        <tr><td class="lbl">Sér</td><td class="a"><?= $done ? (int) $match->p1_high_run : '' ?></td><td class="b"><?= $done ? (int) $match->p2_high_run : '' ?></td></tr>
        <?php if ($signature): ?>
        <tr class="sign signed">
            <td class="lbl" colspan="2">&nbsp;Jr <img src="<?= $signature->p1_signature ?>" alt=""></td>
            <td>Jr <img src="<?= $signature->p2_signature ?>" alt=""></td>
        </tr>
        <tr class="sign signed">
            <td class="lbl" colspan="2">Arb. <span class="referee"><?= esc($signature->referee_name) ?></span><br><span class="signed-at">signé le <?= date('d/m/Y à H:i', strtotime($signature->signed_at)) ?></span></td>
            <td><img src="<?= $signature->referee_signature ?>" alt=""></td>
        </tr>
        <?php else: ?>
        <tr class="sign"><td class="lbl" colspan="2">&nbsp;Jr</td><td>Jr</td></tr>
        <tr class="sign"><td class="lbl" colspan="3">Arb.</td></tr>
        <?php endif; ?>
    </table>
</td>
</tr>
</table>
</div>
<?php endfor; endforeach; ?>
</body>
</html>
