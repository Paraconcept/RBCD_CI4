<?php
use App\Models\ScoreboardSkittlesPoolModel as P;

// Feuille récapitulative FRBB Liège-Luxembourg du tournoi régional 5 quilles (modèle fourni par le club)
$c      = (int) ($compact ?? 0);
$rowH   = [5.7, 5.3, 4.9][$c] ?? 4.9;
$letter = static fn (int $slot): string => P::letter($slot);
$val    = static fn ($v): string => $v === null ? '' : esc((string) $v);
$email  = $pool->regional_email ?: $regional['email'];
$date   = $pool->scheduled_at ? date('d/m/Y', strtotime($pool->scheduled_at)) : '';
// Dompdf (table-layout: fixed) prend les largeurs de la première ligne : une ligne invisible les impose
$sizer  = static fn (array $mm): string => '<tr class="sizer">' . implode('', array_map(static fn ($w) => '<td style="width:' . $w . 'mm"></td>', $mm)) . '</tr>';
$sigs   = $signature ? ['a' => $signature->a_signature, 'b' => $signature->b_signature, 'c' => $signature->c_signature] : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  @page { margin: 9mm 12mm 8mm 12mm; }
  body { font-family: Helvetica, Arial, sans-serif; font-size: 9.5pt; color: #000; }
  table { border-collapse: collapse; }
  .head td { vertical-align: middle; }
  .title { text-align: center; }
  .title .t1 { font-size: 13pt; font-weight: bold; font-style: italic; text-decoration: underline; }
  .title .t2 { font-size: 15pt; margin-top: 1mm; }
  .bar { width: 100%; margin-top: 2mm; }
  .bar .blue { background: #c6d9f0; font-weight: bold; text-align: center; padding: 1mm 0; width: 108mm; }
  .bar .date { text-align: right; font-weight: bold; padding-left: 8mm; }
  .bar .date span { display: inline-block; min-width: 42mm; border-bottom: 1px solid #000; text-align: center; font-weight: normal; }
  .billiards { margin-top: 2mm; }
  .billiards td { border: 1px solid #000; padding: .6mm 2mm; text-align: center; }
  .billiards td.note { border: none; text-align: left; padding-left: 6mm; }
  .billiards .struck { text-decoration: line-through; color: #555; }
  .grid { width: 100%; margin-top: 1.2mm; }
  .grid td { height: <?= $rowH ?>mm; padding: 0 1mm; vertical-align: middle; white-space: nowrap; }
  .grid tr.sizer td, .recap tr.sizer td { height: 0; padding: 0; border: none; }
  .match-label { color: #e00000; font-weight: bold; font-style: italic; text-decoration: underline; }
  .pair { background: #fbd4b4; font-weight: bold; text-align: center; }
  .set-label { font-weight: bold; padding-left: 4mm !important; }
  .letter { text-align: center; }
  .box { border: 1px solid #000; text-align: center; font-weight: bold; }
  .avg { border: 1px solid #000; text-align: center; }
  .avg-head { text-align: center; }
  .total td { border-top: 1.6px solid #000; }
  .total-2 td { border-bottom: 1.6px solid #000; }
  .total td.edge-l, .total-2 td.edge-l { border-left: 1.6px solid #000; }
  .total td.edge-r, .total-2 td.edge-r { border-right: 1.6px solid #000; }
  .total-label { background: #c6d9f0; font-weight: bold; font-style: italic; text-decoration: underline; text-align: center; }
  .total-name { background: #ebf1de; font-weight: bold; }
  .b { font-weight: bold; }
  .recap { width: 100%; border: 1.6px solid #000; margin-top: 2mm; }
  .recap td { padding: .8mm 1mm; vertical-align: middle; }
  .recap .rt { text-align: center; font-weight: bold; text-decoration: underline; }
  .recap .rh { text-align: center; }
  .recap .who { color: #e00000; font-weight: bold; white-space: nowrap; }
  .recap .rbox { border: 1px solid #000; height: <?= $rowH - .6 ?>mm; text-align: center; font-weight: bold; }
  .recap .sig-title { text-align: center; padding-top: 1.5mm; }
  .recap .sig-who { text-align: center; text-decoration: underline; }
  .recap .sig { text-align: center; height: <?= [17, 14, 12][$c] ?? 12 ?>mm; }
  .recap .sig img { height: <?= [15, 12.5, 11][$c] ?? 11 ?>mm; }
  .comment { margin-top: 1.5mm; font-size: 8.5pt; }
  .foot { text-align: center; margin-top: 2.5mm; font-size: 9pt; }
</style>
</head>
<body>

<table class="head" style="width:100%">
  <tr>
    <td style="width:34mm"><?php if ($logo): ?><img src="<?= $logo ?>" style="height:<?= $c ? 20 : 23 ?>mm"><?php endif; ?></td>
    <td class="title">
      <div class="t1">TOURNOI REGIONAL 5 QUILLES (OPEN)</div>
      <div class="t2">Saison <?= esc(P::season($pool->scheduled_at)) ?></div>
    </td>
    <td style="width:34mm;text-align:right"><?php if ($photo): ?><img src="<?= $photo ?>" style="height:<?= $c ? 19 : 22 ?>mm"><?php endif; ?></td>
  </tr>
</table>

<table class="bar">
  <tr>
    <td class="blue">Feuille récapitulative des matchs joués sur grand Billard</td>
    <td class="date">Date : <span><?= esc($date) ?></span></td>
  </tr>
</table>

<table class="billiards">
  <tr>
    <?php foreach (P::BILLIARDS as $code => $b): $struck = (string) $pool->billiard !== (string) $code; ?>
    <td class="<?= $struck ? 'struck' : 'b' ?>" style="width:27mm"><?= esc($b['label']) ?>*</td>
    <td class="<?= $struck ? 'struck' : 'b' ?>" style="width:24mm"><?= (int) $b['points'] ?> Points</td>
    <?php endforeach; ?>
    <td class="note">*Biffer la mention inutile</td>
  </tr>
</table>

<?php foreach ($sheet['matches'] as $no => $item): [$a, $b] = $item['players']; $tot = $item['totals']; ?>
<table class="grid">
  <?= $sizer([22, 25, 21, 10, 26, 10, 5, 21, 10, 4, 31]) ?>
  <tr>
    <td class="match-label">MATCH <?= $no ?> =</td>
    <td class="pair"><?= $letter($a) ?> / <?= $letter($b) ?></td>
    <td colspan="8"></td>
    <td class="avg-head"><?= $no === 1 ? 'Moyennes' : '' ?></td>
  </tr>
  <?php for ($s = 1; $s <= 3; $s++): $set = $item['sets'][$s]; ?>
  <?php foreach ([$a, $b] as $i => $slot): $r = $set[$slot] ?? null; ?>
  <tr>
    <td class="set-label"><?= $i === 0 ? 'SET ' . $s . ' =' : '' ?></td>
    <td class="letter"><?= $letter($slot) ?> =</td>
    <td>Pts réalisés</td>
    <td class="box"><?= $val($r['points'] ?? null) ?></td>
    <td colspan="3"></td>
    <td style="text-align:right">Pt de set =</td>
    <td class="box"><?= $val($r['set_point'] ?? null) ?></td>
    <td></td>
    <td class="avg"><?= $val($r['average'] ?? null) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php endfor; ?>
  <?php foreach ([$a, $b] as $i => $slot): $t = $tot[$slot] ?? null; ?>
  <tr class="<?= $i === 0 ? 'total' : 'total-2' ?>">
    <td class="edge-l <?= $i === 0 ? 'total-label' : '' ?>"><?= $i === 0 ? 'TOTAL =' : '' ?></td>
    <td class="total-name">Joueur <?= $letter($slot) ?> =</td>
    <td class="b">Pts réalisés</td>
    <td class="box"><?= $val($t['points'] ?? null) ?></td>
    <td class="b" style="text-align:right">Pts de match =</td>
    <td class="box"><?= $val($t['match_points'] ?? null) ?></td>
    <td></td>
    <td class="b" style="text-align:right">Pts de set =</td>
    <td class="box"><?= $val($t['sets'] ?? null) ?></td>
    <td></td>
    <td class="avg edge-r"><?= $val($t['average'] ?? null) ?></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endforeach; ?>

<table class="recap">
  <?= $sizer([22, 54, 8, 26, 8, 28, 8, 18, 12]) ?>
  <tr><td colspan="9" class="rt">RECAPITULATIF des POINTS de cette rencontre :</td></tr>
  <tr>
    <td></td><td class="rh">Nom Joueur</td><td></td><td class="rh">Pts de match =</td>
    <td></td><td class="rh">Pts de set =</td><td></td><td class="rh">Total</td><td></td>
  </tr>
  <?php foreach ($sheet['recap'] as $slot => $r): ?>
  <tr>
    <td class="who">Joueur <?= $letter($slot) ?> =</td>
    <td class="rbox" style="text-align:left;padding-left:2mm"><?= esc($r['name']) ?></td>
    <td></td>
    <td class="rbox"><?= $val($r['match_points']) ?></td>
    <td></td>
    <td class="rbox"><?= $val($r['set_points']) ?></td>
    <td></td>
    <td class="rbox"><?= $val($r['total']) ?></td>
    <td></td>
  </tr>
  <?php endforeach; ?>
  <tr><td colspan="9" class="sig-title">Signatures pour accord</td></tr>
  <tr>
    <td></td>
    <td class="sig-who">Joueur A</td>
    <td></td>
    <td colspan="3" class="sig-who">Joueur B</td>
    <td></td>
    <td colspan="2" class="sig-who">Joueur C</td>
  </tr>
  <tr>
    <td></td>
    <td class="sig"><?php if (!empty($sigs['a'])): ?><img src="<?= $sigs['a'] ?>"><?php endif; ?></td>
    <td></td>
    <td colspan="3" class="sig"><?php if (!empty($sigs['b'])): ?><img src="<?= $sigs['b'] ?>"><?php endif; ?></td>
    <td></td>
    <td colspan="2" class="sig"><?php if (!empty($sigs['c'])): ?><img src="<?= $sigs['c'] ?>"><?php endif; ?></td>
  </tr>
</table>

<?php if (!empty($pool->comment)): ?>
<div class="comment"><b>Commentaire :</b> <?= $c >= 2 ? esc(preg_replace('/\s*\R+\s*/', ' / ', $pool->comment)) : nl2br(esc($pool->comment)) ?></div>
<?php endif; ?>

<div class="foot">Seule la feuille récapitulative doit être envoyée à : <?= esc($email) ?></div>

</body>
</html>
