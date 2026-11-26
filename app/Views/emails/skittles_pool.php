<?php
use App\Models\ScoreboardSkittlesPoolModel as P;

$jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
$mois  = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$dt    = $pool->scheduled_at ? new DateTime($pool->scheduled_at) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feuille récapitulative — Tournoi régional 5 Quilles</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f4; font-family: Arial, sans-serif; color: #333; }
    .wrapper { max-width: 580px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #84252B; padding: 28px 32px; text-align: center; }
    .header h1 { margin: 0; color: #fff; font-size: 22px; }
    .body { padding: 32px; }
    .body p { line-height: 1.65; margin: 0 0 16px; }
    .detail-box { background: #f9f9f9; border-left: 4px solid #84252B; padding: 16px 20px; margin: 20px 0; border-radius: 0 4px 4px 0; }
    .detail-box p { margin: 4px 0; font-size: 15px; }
    table.recap { border-collapse: collapse; width: 100%; margin: 8px 0 0; font-size: 14px; }
    table.recap th, table.recap td { border: 1px solid #ddd; padding: 6px 8px; text-align: center; }
    table.recap th { background: #84252B; color: #fff; font-weight: normal; }
    table.recap td.name { text-align: left; }
    .footer { padding: 20px 32px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 12px; color: #999; text-align: center; }
  </style>
</head>
<body>
  <div class="wrapper">

    <div class="header">
      <h1>RBC Disonais</h1>
    </div>

    <div class="body">

      <p>Bonjour,</p>

      <p>Veuillez trouver en pièce jointe la <strong>feuille récapitulative</strong> de la poule suivante du tournoi régional 5 Quilles (open) :</p>

      <div class="detail-box">
        <p><strong>Poule :</strong> <?= esc($pool->name) ?></p>
        <?php if ($dt): ?>
          <p><strong>Date :</strong> <?= $jours[$dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int) $dt->format('n')] . ' ' . $dt->format('Y') ?></p>
        <?php endif; ?>
        <p><strong>Lieu :</strong> RBC Disonais — <?= esc(P::BILLIARDS[$pool->billiard]['label'] ?? '') ?> (<?= (int) $pool->set_points ?> points par set)</p>
        <table class="recap">
          <tr><th></th><th>Joueur</th><th>Pts de match</th><th>Pts de set</th><th>Total</th></tr>
          <?php foreach ($sheet['recap'] as $slot => $r): ?>
          <tr>
            <td><strong><?= P::letter($slot) ?></strong></td>
            <td class="name"><?= esc($r['name']) ?></td>
            <td><?= (int) $r['match_points'] ?></td>
            <td><?= (int) $r['set_points'] ?></td>
            <td><strong><?= (int) $r['total'] ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </table>
        <?php if (!$signed && $pool->signature_required): ?>
          <p style="margin-top:12px"><em>Feuille non signée par les joueurs.</em></p>
        <?php endif; ?>
        <?php if (!empty($pool->comment)): ?>
          <p style="margin-top:12px"><strong>Commentaire :</strong><br><?= nl2br(esc($pool->comment)) ?></p>
        <?php endif; ?>
      </div>

      <p>Pour toute question, contactez notre directeur sportif :<br>
        📧 <a href="mailto:ds@rbcd.be">ds@rbcd.be</a> &nbsp;|&nbsp; 📞 0499&nbsp;/&nbsp;26&nbsp;73&nbsp;82
      </p>

      <p>Sportivement,<br>RBC Disonais</p>

    </div>

    <div class="footer">
      &copy; <?= date('Y') ?> RBC Disonais — Royal Billard Club Disonais
    </div>

  </div>
</body>
</html>
