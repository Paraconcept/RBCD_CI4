<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmation d'inscription comme marqueur</title>
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

      <p>Bonjour <strong><?= esc($member->first_name . ' ' . $member->last_name) ?></strong>,</p>

      <p>Ton inscription comme <strong>marqueur</strong> a bien été enregistrée pour la rencontre suivante :</p>

      <?php
        $jours   = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
        $mois    = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
        $dt      = new DateTime($encounter->match_date . ' ' . $encounter->match_time);
        $dateStr = $jours[$dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int)$dt->format('n')] . ' ' . $dt->format('Y');
        $heureStr = $dt->format('H\hi');
      ?>

      <div class="detail-box">
        <?php if ($encounter->competition): ?>
          <p><strong>Compétition :</strong> <?= esc($encounter->competition) ?></p>
        <?php endif; ?>
        <p><strong>Date :</strong> <?= $dateStr ?></p>
        <p><strong>Heure :</strong> <?= $heureStr ?></p>
        <p><strong>Lieu :</strong> À domicile (Dison)</p>
      </div>

      <p>Pour annuler ton inscription, merci de prendre contact avec ton DS :<br>
        📧 <a href="mailto:ds@rbcd.be">ds@rbcd.be</a> &nbsp;|&nbsp; 📞 0499&nbsp;/&nbsp;26&nbsp;73&nbsp;82
      </p>

      <p>Merci pour ta disponibilité !</p>

    </div>

    <div class="footer">
      &copy; <?= date('Y') ?> RBC Disonais — Cet e-mail est envoyé automatiquement, ne pas y répondre.
    </div>

  </div>
</body>
</html>
