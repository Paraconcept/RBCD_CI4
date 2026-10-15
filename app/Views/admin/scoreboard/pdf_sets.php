<?php
use App\Models\ScoreboardMatchModel as M;

/*
 * Feuille de match par sets, modèle FRBB « Coupe de Belgique — Trois bandes individuel par set » (A4 portrait).
 * Une seule page : la hauteur des lignes de la grille s'adapte au nombre de reprises du plus long set,
 * et MatchSheetPdf recompose plus serré ($compact) si la page déborde encore.
 */
$compact  = $compact ?? 0;
$setCount = max(3, 2 * (int) $match->sets_to_win - 1, $sheets ? max(array_keys($sheets)) : 0);
$maxInn   = 0;
foreach ($sheets as $sheet) {
    $maxInn = max($maxInn, $sheet ? max(array_keys($sheet)) : 0);
}
$rows   = max(40, $maxInn);
// Remarques imprimées sur toute la largeur, au-dessus du pied : la grille leur cède la place
$comment = trim((string) ($match->comment ?? ''));
$commentLines = $comment === '' ? 0 : array_sum(array_map(static fn ($l) => max(1, (int) ceil(mb_strlen($l) / 125)), preg_split('/\R/', $comment)));
$gridH  = [158, 150, 142, 132][min(3, $compact)] - ($comment === '' ? 0 : min(30, 3 + $commentLines * 3.6));
$rowH   = round(min(4.0, $gridH / $rows), 2);
$rowFs  = $rowH >= 3.6 ? 8 : max(5, round($rowH * 2, 1));
$date   = $match->started_at ?? $match->scheduled_at;
$done   = $match->status === 'finished';

// Résultat de chaque set : caramboles, reprises, vainqueur du set
$setResults = [];
for ($s = 1; $s <= $setCount; $s++) {
    $sheet = $sheets[$s] ?? [];
    foreach ([1, 2] as $p) {
        $innings = array_filter($sheet, static fn ($row) => isset($row[$p]));
        $last    = $innings ? end($innings)[$p]['total'] : null;
        $setResults[$s][$p] = ['points' => $last, 'innings' => $innings ? count($innings) : null,
                               'won' => $last !== null && $last >= (int) $match->{'p' . $p . '_distance'}];
    }
}
$setsWon = [1 => (int) $match->p1_sets, 2 => (int) $match->p2_sets];
$cell    = static fn (int $s, int $n, int $p, string $k): string => isset($sheets[$s][$n][$p]) ? (string) $sheets[$s][$n][$p][$k] : '';
$check   = static fn (bool $on): string => '<span class="box">' . ($on ? 'X' : '&nbsp;') . '</span>';
$colW    = round(150 / $setCount, 2);
// Au-delà de 3 sets, les colonnes sont trop étroites pour les noms : A et B, comme sur le modèle
$headA   = $setCount > 3 ? 'A' : mb_substr($match->p1_last_name, 0, 10);
$headB   = $setCount > 3 ? 'B' : mb_substr($match->p2_last_name, 0, 10);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: <?= $compact >= 2 ? '6mm 8mm' : '9mm 10mm' ?>; }
    * { box-sizing: border-box; }
    body { font-family: 'Times New Roman', Times, serif; font-size: 10pt; color: #000; margin: 0; }
    table { border-collapse: collapse; width: 100%; }
    td { padding: 0 1.2mm; vertical-align: middle; }
    .frame { border: 1.5px solid #000; }
    .frame + .frame { border-top: none; }
    .fill { background: #fff8c8; }
    .title td { font-size: 14pt; font-weight: bold; font-style: italic; padding-top: 1mm; }
    .sub td { font-size: 10.5pt; font-style: italic; padding-bottom: 1mm; }
    .lbl { font-style: italic; font-size: 9pt; white-space: nowrap; }
    .info td { height: <?= $compact >= 1 ? 5 : 5.6 ?>mm; }
    .box { display: inline-block; width: 3mm; height: 3mm; border: 1px solid #000; font-size: 7pt; line-height: 3mm; text-align: center; font-family: Helvetica, sans-serif; margin-right: 1mm; }
    .players td { height: <?= $compact >= 1 ? 5 : 6 ?>mm; font-size: 9.5pt; }
    .players .letter { font-size: 26pt; text-align: center; width: 12mm; }
    .players .small { font-size: 7.5pt; text-align: right; white-space: nowrap; }
    .players .val { border-bottom: 1px solid #000; font-weight: bold; }
    .res td { border: 1px solid #000; height: <?= $compact >= 1 ? 4 : 4.5 ?>mm; font-size: 9pt; text-align: center; }
    .res .won { font-size: 16pt; font-weight: bold; }
    .grid td { border: 1px solid #000; height: <?= $rowH ?>mm; font-size: <?= $rowFs ?>pt; text-align: center; padding: 0; line-height: 1; }
    .grid .head td { background: #d9d9d9; font-weight: bold; height: 4.5mm; font-size: 9pt; }
    .grid .head2 td { height: 4mm; font-size: 7.5pt; }
    .grid td.rep { text-align: left; padding-left: .8mm; background: #f2f2f2; }
    .grid td.sep { border-left: 2px solid #000; }
    .grid td.tot { font-weight: bold; }
    .sign td { text-align: center; font-size: 9.5pt; vertical-align: middle; padding: 1mm; }
    .sign .zone { background: #fff8c8; }
    .sign img { max-width: 34mm; max-height: <?= $compact >= 2 ? 16 : 22 ?>mm; }
    .note { color: #c00; font-weight: bold; font-size: 10.5pt; text-align: center; padding: 1mm 0; }
    .foot { text-align: center; font-size: 9.5pt; padding: 1.2mm 0; }
    .recipient { font-style: italic; font-weight: bold; font-size: 9.5pt; text-align: center; padding: 1.5mm 0; }
</style>
</head>
<body>

<table class="frame">
    <tr class="title"><td>F.R.B.B. <?= esc($titleFr) ?></td><td style="text-align:right">K.B.B.B. <?= esc($titleNl) ?></td></tr>
    <tr class="sub"><td>Trois bandes individuel par set</td><td style="text-align:right">Driebanden individueel per set</td></tr>
    <?php if (!empty($match->competition)): ?>
    <tr><td colspan="2" style="text-align:center;font-weight:bold;font-size:10.5pt;padding-bottom:1mm"><?= esc($match->competition) ?></td></tr>
    <?php endif; ?>
</table>
<table class="frame info">
    <tr><td class="lbl" style="width:22mm">Club :</td><td class="fill" colspan="3"><b>Royal Billard Club Disonais</b></td></tr>
    <tr>
        <td class="lbl">Bill :</td>
        <td style="width:52mm"><?= $check($match->format === 'PB') ?><b>Petit format 2M30</b></td>
        <td class="lbl" rowspan="2" style="width:16mm;text-align:right">Date :<br>Datum :</td>
        <td class="fill" rowspan="2" style="font-weight:bold"><?= $date ? date('d/m/Y', strtotime($date)) : '' ?></td>
    </tr>
    <tr>
        <td class="lbl">Bilj :</td>
        <td><?= $check($match->format === 'GB') ?><b>Grand format 2M84</b></td>
    </tr>
    <tr>
        <td class="lbl" colspan="2">N° Match / Wedstrijdnr : <span class="fill" style="padding:0 6mm"><?= esc($matchNumber) ?></span></td>
        <td colspan="2" style="text-align:right;font-size:9pt">(Selon calendrier / Zie wedstrijdkalender)</td>
    </tr>
</table>

<table class="frame players">
    <tr>
        <?php foreach (['A' => 'p1', 'B' => 'p2'] as $letter => $p): ?>
        <td class="letter" rowspan="3" <?= $letter === 'B' ? 'style="border-left:1.5px solid #000"' : '' ?>><?= $letter ?></td>
        <td class="small" style="width:18mm;line-height:1.1">Nom / Naam<br>Joueur / speler</td>
        <td class="val fill"><?= esc(M::playerLabel($match, $p)) ?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <?php foreach (['p1', 'p2'] as $p): ?>
        <td class="small">Club :</td><td class="val fill"><?= esc($match->{$p . '_club'} ?? '') ?></td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <?php foreach (['p1', 'p2'] as $p): ?>
        <td class="small">N° Lic / Nr Lic</td><td class="val fill"><?= esc($match->{$p . '_license'} ?? '') ?></td>
        <?php endforeach; ?>
    </tr>
</table>

<table class="frame res">
    <tr>
        <?php foreach (['p1' => 'Point à jouer', 'p2' => 'Te spelen punten'] as $p => $label): ?>
        <td colspan="2" style="text-align:left;border-left:<?= $p === 'p2' ? '1.5px' : '1px' ?> solid #000"><?= $label ?></td>
        <td colspan="2" style="font-weight:bold"><?= (int) $match->{$p . '_distance'} ?> pts / set</td>
        <?php endforeach; ?>
    </tr>
    <tr style="background:#eee">
        <td style="width:13%">Rés</td><td style="width:12%">Caramb</td><td style="width:12%">Repr / Beur</td><td style="width:13%;font-size:8pt">Nombre de sets gagnés</td>
        <td style="width:13%;border-left:1.5px solid #000">Rés</td><td style="width:12%">Caramb</td><td style="width:12%">Repr / Beur</td><td style="width:13%;font-size:8pt">Aantal gewonnen set</td>
    </tr>
    <?php for ($s = 1; $s <= $setCount; $s++): ?>
    <tr>
        <?php foreach ([1, 2] as $p): $r = $setResults[$s][$p]; ?>
        <td <?= $p === 2 ? 'style="border-left:1.5px solid #000"' : '' ?>>Set <?= $s ?></td>
        <td class="fill" style="<?= $r['won'] ? 'font-weight:bold' : '' ?>"><?= $r['points'] ?? '' ?></td>
        <td class="fill"><?= $r['innings'] ?? '' ?></td>
        <?php if ($s === 1): ?><td class="fill won" rowspan="<?= $setCount ?>"><?= $done || $setsWon[$p] ? $setsWon[$p] : '' ?></td><?php endif; ?>
        <?php endforeach; ?>
    </tr>
    <?php endfor; ?>
</table>

<table style="margin-top:<?= $compact >= 1 ? 1 : 2 ?>mm">
<tr>
<td style="padding:0;vertical-align:top;width:<?= 150 ?>mm">
    <table class="grid">
        <tr class="head">
            <?php for ($s = 1; $s <= $setCount; $s++): ?>
            <td colspan="5" class="<?= $s > 1 ? 'sep' : '' ?>">Set <?= $s ?></td>
            <?php endfor; ?>
        </tr>
        <tr class="head2">
            <?php for ($s = 1; $s <= $setCount; $s++): ?>
            <td class="rep <?= $s > 1 ? 'sep' : '' ?>" style="width:<?= round($colW * 0.18, 2) ?>mm">Rep</td>
            <td colspan="2"><?= esc($headA) ?></td>
            <td colspan="2"><?= esc($headB) ?></td>
            <?php endfor; ?>
        </tr>
        <?php for ($n = 1; $n <= $rows; $n++): ?>
        <tr>
            <?php for ($s = 1; $s <= $setCount; $s++): ?>
            <td class="rep <?= $s > 1 ? 'sep' : '' ?>"><?= $n ?></td>
            <td><?= $cell($s, $n, 1, 'points') ?></td><td class="tot"><?= $cell($s, $n, 1, 'total') ?></td>
            <td><?= $cell($s, $n, 2, 'points') ?></td><td class="tot"><?= $cell($s, $n, 2, 'total') ?></td>
            <?php endfor; ?>
        </tr>
        <?php endfor; ?>
    </table>
</td>
<td style="padding:0 0 0 1mm;vertical-align:top">
    <table class="sign frame">
        <tr><td>Signature joueur A<br><i>ondertekenende speler A</i></td></tr>
        <tr><td class="zone" style="height:<?= $compact >= 2 ? 18 : 26 ?>mm"><?php if ($signature): ?><img src="<?= $signature->p1_signature ?>" alt=""><?php endif; ?></td></tr>
        <tr><td>Signature joueur B<br><i>ondertekenende speler B</i></td></tr>
        <tr><td class="zone" style="height:<?= $compact >= 2 ? 18 : 26 ?>mm"><?php if ($signature): ?><img src="<?= $signature->p2_signature ?>" alt=""><?php endif; ?></td></tr>
        <tr><td>Nom Arbitre<br><i>Naam van scheidsrechter</i></td></tr>
        <tr><td class="zone" style="height:9mm;font-weight:bold"><?= $signature ? esc($signature->referee_name) : '' ?></td></tr>
        <tr><td>Signature Arbitre<br><i>handtekening scheidsrechter</i></td></tr>
        <tr><td class="zone" style="height:<?= $compact >= 2 ? 18 : 26 ?>mm"><?php if ($signature): ?><img src="<?= $signature->referee_signature ?>" alt=""><?php endif; ?></td></tr>
        <?php if ($signature): ?>
        <tr><td style="font-size:7.5pt">Signée au marquoir le <?= date('d/m/Y à H:i', strtotime($signature->signed_at)) ?></td></tr>
        <?php endif; ?>
    </table>
</td>
</tr>
</table>

<table class="frame" style="margin-top:<?= $compact >= 1 ? 1 : 1.5 ?>mm">
    <?php if ($comment !== ''): ?>
    <tr><td style="font-size:<?= $compact >= 2 ? 7.5 : 8.5 ?>pt;padding:1mm 2mm;border-bottom:1px solid #000"><b>Remarques :</b> <?= $compact >= 3
        ? esc(implode(' / ', preg_split('/\s*\R\s*/', $comment)))
        : nl2br(esc(preg_replace('/(\R\s*){2,}/', "\n", $comment))) ?></td></tr>
    <?php endif; ?>
    <tr><td class="note">* Les SET se jouent à reprises inégales<span style="margin-left:14mm">* SET's worden ongelijk gespeeld</span></td></tr>
    <tr><td class="foot">Feuilles de match à envoyer au plus tard 24 heures après les matches à :</td></tr>
    <tr><td class="recipient"><?= esc($recipient) ?></td></tr>
</table>

</body>
</html>
