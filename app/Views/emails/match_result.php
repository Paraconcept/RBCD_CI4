<?php
use App\Models\ScoreboardMatchModel as M;

$jours = ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'];
$mois  = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
$when  = $match->started_at ?: $match->scheduled_at;
$dt    = $when ? new DateTime($when) : null;
$sets  = M::isSets($match);
$competition = M::competitionLabel($match);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Résultat du match</title>
  <style>
    body { margin: 0; padding: 0; background: #f4f4f4; font-family: Arial, sans-serif; color: #333; }
    .wrapper { max-width: 580px; margin: 30px auto; background: #fff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #84252B; padding: 28px 32px; text-align: center; }
    .header h1 { margin: 0; color: #fff; font-size: 22px; }
    .body { padding: 32px; }
    .body p { line-height: 1.65; margin: 0 0 16px; }
    .detail-box { background: #f9f9f9; border-left: 4px solid #84252B; padding: 16px 20px; margin: 20px 0; border-radius: 0 4px 4px 0; }
    .detail-box p { margin: 4px 0; font-size: 15px; }
    table.res { width: 100%; border-collapse: collapse; margin: 12px 0 4px; font-size: 14px; }
    table.res th { background: #84252B; color: #fff; padding: 6px; font-weight: normal; }
    table.res td { border-bottom: 1px solid #e5e5e5; padding: 6px; text-align: center; }
    table.res td.name { text-align: left; }
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

      <p>Veuillez trouver en pièce jointe la <strong>feuille de match</strong><?= !empty($signed) ? ", signée par les joueurs et l'arbitre," : '' ?> du match suivant :</p>

      <div class="detail-box">
        <p><strong>Discipline :</strong> <?= esc(M::disciplineLabel($match)) ?> — <?= esc(M::distanceLabel($match)) ?></p>
        <?php if ($competition !== ''): ?>
          <p><strong>Compétition :</strong> <?= esc($competition) ?><?= !empty($match->match_number) ? ' (n° ' . esc($match->match_number) . ')' : '' ?></p>
        <?php endif; ?>
        <?php if ($dt): ?>
          <p><strong>Date :</strong> <?= $jours[$dt->format('w')] . ' ' . $dt->format('j') . ' ' . $mois[(int) $dt->format('n')] . ' ' . $dt->format('Y') ?></p>
        <?php endif; ?>

        <table class="res">
          <tr>
            <th style="text-align:left">Joueur</th>
            <?php if ($sets): ?><th>Sets</th><?php endif; ?>
            <th>Points</th><th>Reprises</th><th>Moyenne</th><th>MS</th>
          </tr>
          <?php foreach (['p1', 'p2'] as $n => $p): ?>
          <tr>
            <td class="name">
              <?= (int) $match->winner === $n + 1 ? '<strong>' . esc(M::playerLabel($match, $p)) . '</strong> 🏆' : esc(M::playerLabel($match, $p)) ?>
              <?php if (!empty($match->{$p . '_club'})): ?><br><span style="color:#888;font-size:12px"><?= esc($match->{$p . '_club'}) ?></span><?php endif; ?>
            </td>
            <?php if ($sets): ?><td><?= (int) $match->{$p . '_sets'} ?></td><?php endif; ?>
            <td><?= (int) $match->{$p . '_points'} ?> / <?= (int) $match->{$p . '_distance'} ?></td>
            <td><?= (int) $match->{$p . '_innings'} ?></td>
            <td><?= M::average((int) $match->{$p . '_points'}, (int) $match->{$p . '_innings'}) ?></td>
            <td><?= (int) $match->{$p . '_high_run'} ?></td>
          </tr>
          <?php endforeach; ?>
        </table>

        <p><strong>Résultat :</strong>
          <?= (int) $match->winner === 0 ? 'match nul' : 'victoire de ' . esc(M::playerLabel($match, 'p' . (int) $match->winner)) ?>
        </p>
        <?php if (!empty($match->comment)): ?>
          <p><strong>Commentaire :</strong><br><?= nl2br(esc($match->comment)) ?></p>
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
