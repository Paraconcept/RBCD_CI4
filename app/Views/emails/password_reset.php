<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isFirst ? 'Créez votre mot de passe' : 'Réinitialisation de mot de passe' ?></title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f4; font-family: Arial, sans-serif; color: #333; }
    .wrapper { max-width: 580px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #84252B; padding: 28px 32px; text-align: center; }
    .header h1 { margin: 0; color: #fff; font-size: 22px; }
    .body { padding: 32px; }
    .body p { line-height: 1.65; margin: 0 0 16px; }
    .btn { display: inline-block; margin: 20px 0; padding: 14px 32px; background: #84252B; color: #fff !important; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold; }
    .url-fallback { font-size: 12px; color: #888; word-break: break-all; }
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

      <?php if ($isFirst): ?>
        <p>Votre profil membre sur le site du <strong>RBC Disonais</strong> est prêt. Pour accéder à votre espace personnel (planning, classements, journal…), vous devez d'abord créer votre mot de passe en cliquant sur le bouton ci-dessous.</p>
      <?php else: ?>
        <p>Vous avez demandé la réinitialisation de votre mot de passe sur le site du <strong>RBC Disonais</strong>. Cliquez sur le bouton ci-dessous pour en choisir un nouveau.</p>
      <?php endif; ?>

      <p style="text-align:center">
        <a href="<?= $resetUrl ?>" class="btn">
          <?= $isFirst ? 'Créer mon mot de passe' : 'Réinitialiser mon mot de passe' ?>
        </a>
      </p>

      <p>Ce lien est valable <strong>5 jours</strong>. Après ce délai, vous devrez faire une nouvelle demande.</p>

      <p>Si vous n'avez pas effectué cette demande, ignorez simplement cet e-mail.</p>

      <hr style="border:none;border-top:1px solid #eee;margin:24px 0">

      <p class="url-fallback">
        Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
        <?= $resetUrl ?>
      </p>

    </div>

    <div class="footer">
      &copy; <?= date('Y') ?> RBC Disonais — Cet e-mail est envoyé automatiquement, ne pas y répondre.
    </div>

  </div>
</body>
</html>
