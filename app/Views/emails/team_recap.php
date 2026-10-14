<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;

$jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
$mois  = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$dt    = $encounter->scheduled_at ? new DateTime($encounter->scheduled_at) : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feuille récapitulative</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f4; font-family: Arial, sans-serif; color: #333; }
    .wrapper { max-width: 580px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #84252B; padding: 28px 32px; text-align: center; }
    .header h1 { margin: 0; color: #fff; font-size: 22px; }
    .body { padding: 32px; }
    .body p { line-height: 1.65; margin: 0 0 16px; }
    .detail-box { background: #f9f9f9; border-left: 4px solid #84252B; padding: 16px 20px; margin: 20px 0; border-radius: 0 4px 4px 0; }
    .detail-box p { margin: 4px 0; font-size: 15px; }
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

      <p>Veuillez trouver en pièces jointes la <strong>feuille récapitulative</strong> et les <strong>feuilles de match individuelles</strong> de la rencontre suivante :</p>

      <div class="detail-box">
        <p><strong>Compétition :</strong> <?= esc(TE::COMPETITIONS[$encounter->competition] ?? $encounter->competition) ?> — <?= esc(M::disciplineLabel($encounter)) ?></p>
        <p><strong>Rencontre :</strong> <?= esc($encounter->code) ?></p>
        <?php if ($dt): ?>
          <p><strong>Date :</strong> <?= $jours[$dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int) $dt->format('n')] . ' ' . $dt->format('Y') ?></p>
        <?php endif; ?>
        <p><strong>Club visité :</strong> <?= esc(TE::homeLabel($encounter)) ?></p>
        <p><strong>Club visiteur :</strong> <?= esc(TE::awayLabel($encounter)) ?></p>
        <p><strong>Résultat :</strong> <?= $result['teams']['home']['match_points'] ?> / <?= $result['teams']['away']['match_points'] ?></p>
        <?php if (!empty($encounter->recap_comment)): ?>
          <p><strong>Commentaire :</strong><br><?= nl2br(esc($encounter->recap_comment)) ?></p>
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
