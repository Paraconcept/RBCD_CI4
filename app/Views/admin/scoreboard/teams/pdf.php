<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;

$blocks = [
    'home' => ['title' => 'CLUB VISITE',   'club' => $encounter->team_club,     'number' => $encounter->team_number,     'email' => $encounter->team_email],
    'away' => ['title' => 'CLUB VISITEUR', 'club' => $encounter->opponent_club, 'number' => $encounter->opponent_number, 'email' => $encounter->opponent_email],
];
$rowsCount = max(3, $result['total']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 10mm 12mm; }
    body { font-family: 'Times New Roman', Times, serif; font-size: 10.5pt; color: #000; margin: 0; }
    table { border-collapse: collapse; }

    .head { width: 100%; margin-bottom: 4mm; }
    .head td { vertical-align: middle; text-align: center; }
    .head .titles { font-weight: bold; font-size: 11.5pt; line-height: 1.25; }
    .head .titles small { font-size: 9.5pt; }

    .box { width: 100%; border: 2px solid #000; }
    .box td { border: 1px solid #000; padding: 1mm 2mm; }
    .box .b { font-weight: bold; }

    .team { width: 100%; border: 2px solid #000; margin-top: 5mm; }
    .team td { border: 1px solid #000; padding: .6mm 1mm; text-align: center; font-size: 10pt; height: 5.2mm; }
    .team td.club { width: 23%; border-right: 2px solid #000; padding: 0; vertical-align: top; text-align: left; height: auto; }
    .team th { border: 1px solid #000; font-weight: normal; font-size: 10pt; padding: .5mm 1mm; line-height: 1.1; }
    .team td.idx { width: 2.5%; text-align: left; }
    .team td.name { text-align: center; }
    .team tr.total td { font-weight: bold; font-size: 12pt; height: 9mm; border-top: 2px solid #000; }
    .team tr.total td.mail { font-weight: normal; font-size: 10pt; vertical-align: top; }

    .final { margin: 3mm 0 0 23%; width: 52%; border: 2px solid #000; }
    .final td { padding: 1mm 3mm; font-weight: bold; font-size: 11.5pt; }

    .sign { width: 100%; border: 2px solid #000; margin-top: 3mm; height: 17mm; }
    .sign td { vertical-align: top; padding: 1.5mm 2mm; font-size: 10pt; }
    .footer { font-family: Helvetica, Arial, sans-serif; font-size: 8.5pt; margin-top: 1mm; }
</style>
</head>
<body>

<table class="head">
    <tr>
        <td style="width:20%"><?php if ($logoFrbb): ?><img src="<?= $logoFrbb ?>" style="height:24mm"><?php endif; ?></td>
        <td class="titles">
            FEDERATION ROYALE BELGE DE BILLARD<br>
            REGION LIEGE - LUXEMBOURG<br>
            <?= esc(mb_strtoupper(TE::COMPETITIONS[$encounter->competition] ?? $encounter->competition, 'UTF-8')) ?><br>
            <small>SAISON SPORTIVE <?= esc(TE::season($encounter)) ?></small>
        </td>
        <td style="width:20%"><?php if ($logoLL): ?><img src="<?= $logoLL ?>" style="height:24mm"><?php endif; ?></td>
    </tr>
</table>

<table class="box">
    <tr>
        <td class="b" style="width:23%;text-align:center">Mode de jeu :</td>
        <td class="b" style="width:52%"><?= esc(TE::gameModeLabel($encounter)) ?></td>
        <td style="width:10%">N° Match :</td>
        <td><?= esc($encounter->code) ?></td>
    </tr>
    <tr><td colspan="4" class="b" style="text-align:center;font-size:12pt">FEUILLE RECAPITULATIVE</td></tr>
    <tr>
        <td style="text-align:center">Date :</td>
        <td colspan="3"><?= $encounter->scheduled_at ? date('d/m/Y', strtotime($encounter->scheduled_at)) : '' ?></td>
    </tr>
</table>

<?php foreach ($blocks as $side => $b): $t = $result['teams'][$side]; ?>
<table class="team">
    <tr>
        <td class="club" rowspan="<?= $rowsCount + 2 ?>">
            <div style="text-align:center;font-weight:bold;font-size:12pt;margin-top:3mm"><?= $b['title'] ?></div>
            <div style="text-align:center;margin-top:3mm"><?= esc($b['club']) ?></div>
            <div style="margin:<?= 6 + ($rowsCount - 3) * 5 ?>mm 0 0 3mm">Equipe n°<span style="margin-left:22mm"><?= esc((string) ($b['number'] ?? '')) ?></span></div>
        </td>
        <th colspan="2" style="width:22%">JOUEURS</th>
        <th style="width:7%">N° Lic</th>
        <th style="width:7.5%">Points à jouer</th>
        <th style="width:7.5%">Points Réaliser</th>
        <th style="width:7.5%">Reprises</th>
        <th style="width:9.5%">Moyennes</th>
        <th style="width:7%">Série</th>
        <th style="width:9%">Pts Match</th>
    </tr>
    <?php for ($i = 0; $i < $rowsCount; $i++): $r = $t['rows'][$i] ?? null; ?>
    <tr>
        <td class="idx"><?= $i + 1 ?></td>
        <td class="name"><?php if ($r): ?><?= $r['absent'] ? ($r['name'] === 'ABSENT' ? '<b>ABSENT</b>' : esc($r['name']) . ' <b>(absent)</b>') : esc($r['name']) . ($r['forfeit'] ? ' (forfait adverse)' : '') ?><?php endif; ?></td>
        <td><?= $r ? esc($r['license'] ?? '') : '' ?></td>
        <td><?= $r && $r['distance'] ? $r['distance'] : '' ?></td>
        <td><?= $r && $r['played'] ? $r['points'] : '' ?></td>
        <td><?= $r && $r['played'] ? $r['innings'] : '' ?></td>
        <td><?= $r && $r['played'] ? M::average($r['points'], $r['innings']) : '' ?></td>
        <td><?= $r && $r['played'] ? $r['high_run'] : '' ?></td>
        <td style="font-weight:bold"><?= $r ? ($r['match_points'] ?? '') : '' ?></td>
    </tr>
    <?php endfor; ?>
    <tr class="total">
        <td colspan="4" class="mail">E.Mail : <?= esc($b['email'] ?? '') ?></td>
        <td><?= $t['points'] ?></td>
        <td><?= $t['innings'] ?></td>
        <td><?= M::average($t['points'], $t['innings']) ?></td>
        <td><?= $t['high_run'] ?></td>
        <td><?= $t['match_points'] ?></td>
    </tr>
</table>
<?php endforeach; ?>

<table class="final">
    <tr>
        <td style="width:45%;text-align:center">Résultat final :</td>
        <td style="text-align:center;font-size:13pt">
            <?= $result['teams']['home']['match_points'] ?> / <?= $result['teams']['away']['match_points'] ?>
        </td>
    </tr>
</table>

<?php if (!empty($encounter->recap_comment)): ?>
<table class="sign" style="height:auto">
    <tr><td style="padding:2mm 3mm"><b>Commentaire :</b> <?= nl2br(esc($encounter->recap_comment)) ?></td></tr>
</table>
<?php endif; ?>
<table class="sign">
    <tr><td style="height:16mm"><b>Signatures :</b> (facultatif pour l'envoi par mail. Conservez les feuilles de match en cas de litige)</td></tr>
</table>
<div class="footer">
    Envoyer à <?= esc($cdr->name) ?><span style="margin-left:30mm"><?= esc($cdr->email) ?></span>
</div>

</body>
</html>
