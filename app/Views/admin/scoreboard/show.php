<?php use App\Models\ScoreboardMatchModel as M;
$trio  = M::isTrio($match);
$ranks = $trio && $match->status === 'finished' ? M::storedRanking($match) : [];
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title">
      <i class="fas fa-list-ol mr-2"></i>
      <?= esc(M::disciplineLabel($match)) ?> · <?= esc(M::distanceLabel($match)) ?>
      <?= $match->innings_limit ? ' · limite ' . (int) $match->innings_limit . ' reprises' : '' ?>
      <?= ($competition = M::competitionLabel($match)) ? ' — ' . esc($competition) : '' ?>
    </h3>
    <div class="ml-auto">
      <span class="badge badge-<?= $match->status === 'finished' ? 'success' : 'danger' ?> p-2"><?= esc(M::STATUSES[$match->status]) ?></span>
    </div>
  </div>
  <div class="card-body">
    <p class="text-muted mb-3">
      <?= $table ? esc($table->name) . ' · ' : '' ?>
      <?= $match->started_at ? 'Début ' . date('d/m/Y H:i', strtotime($match->started_at)) : '' ?>
      <?= $match->finished_at ? ' · Fin ' . date('H:i', strtotime($match->finished_at)) : '' ?>
      <?= $match->unequal_innings ? ' · reprises inégales' : '' ?>
    </p>

    <?php $prop = M::decidedByProportional($match); ?>
    <table class="table table-sm table-bordered <?= $prop ? 'mb-2' : 'mb-4' ?>" style="max-width:1000px">
      <thead class="thead-rbcd">
        <tr><?php if ($ranks): ?><th class="text-center">Rang</th><?php endif; ?><th>Joueur</th><th>Club</th><th class="text-center">Catégorie</th><th class="text-center">À réaliser</th><th class="text-center">Points</th><th class="text-center">Reprises</th><th class="text-center">Moyenne</th><th class="text-center">MS</th><?php if ($prop): ?><th class="text-center">Moy. prop.</th><?php endif; ?></tr>
      </thead>
      <tbody>
        <?php foreach (M::playerNumbers($match) as $n): $p = 'p' . $n; ?>
        <tr class="<?= (int) $match->winner === $n ? 'font-weight-bold' : '' ?>">
          <?php if ($ranks): ?><td class="text-center"><?= $ranks[$n] === 1 ? '1er' : $ranks[$n] . 'e' ?></td><?php endif; ?>
          <td>
            <?= (int) $match->starting_player === $n ? '<i class="fas fa-circle mr-1" title="A cassé" style="color:' . ($n === 1 ? '#bbb' : '#f5c400') . '"></i>' : '' ?>
            <?= esc(M::playerLabel($match, $p)) ?>
            <?= (int) $match->winner === $n ? '<i class="fas fa-trophy ml-1 text-warning"></i>' : '' ?>
          </td>
          <td><?= esc($match->{$p . '_club'}) ?></td>
          <td class="text-center"><?= esc(M::categoryLabel($match, $p)) ?: '<span class="text-muted">—</span>' ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_distance'} ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_points'} ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_innings'} ?></td>
          <td class="text-center"><?= M::average((int) $match->{$p . '_points'}, (int) $match->{$p . '_innings'}) ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_high_run'} ?></td>
          <?php if ($prop): ?><td class="text-center"><?= M::proportionalLabel($match, $p) ?></td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($prop): ?>
    <p class="text-muted small mb-4"><i class="fas fa-balance-scale mr-1"></i>Limite de reprises atteinte : vainqueur désigné à la moyenne proportionnelle (moyenne réalisée ÷ moyenne minimum de la catégorie).</p>
    <?php endif; ?>

    <?php if (M::isSets($match) && $sheets): ?>
    <h5 class="mb-2">Sets</h5>
    <table class="table table-sm table-bordered text-center mb-4" style="max-width:1000px">
      <thead class="thead-rbcd">
        <tr>
          <th class="text-left">Joueur</th>
          <?php foreach (array_keys($sheets) as $no): ?><th>Set <?= $no ?></th><?php endforeach; ?>
          <th>Sets gagnés</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ([1, 2] as $n): ?>
        <tr class="<?= (int) $match->winner === $n ? 'font-weight-bold' : '' ?>">
          <td class="text-left"><?= esc(M::playerLabel($match, 'p' . $n)) ?></td>
          <?php foreach ($sheets as $sheet): $mine = array_column($sheet, $n); ?>
          <td><?= $mine ? end($mine)['total'] : 0 ?> <small class="text-muted">(<?= count($mine) ?> rep.)</small></td>
          <?php endforeach; ?>
          <td><?= (int) $match->{'p' . $n . '_sets'} ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if (!$sheets): ?>
      <h5 class="mb-2">Feuille de match</h5>
      <p class="text-muted">Aucune reprise enregistrée.</p>
    <?php endif; ?>
    <?php foreach ($sheets as $setNo => $sheet): ?>
    <h5 class="mb-2"><?= M::isSets($match) ? 'Set ' . $setNo : 'Feuille de match' ?></h5>
    <table class="table table-sm table-bordered table-striped text-center mb-4" style="max-width:<?= $trio ? 850 : 600 ?>px">
      <thead class="thead-rbcd">
        <tr>
          <th rowspan="2" class="align-middle">Reprise</th>
          <?php foreach (M::playerKeys($match) as $p): ?>
          <th colspan="2"><?= esc($match->{$p . '_last_name'}) ?></th>
          <?php endforeach; ?>
        </tr>
        <tr><?= str_repeat('<th>Série</th><th>Total</th>', M::playerCount($match)) ?></tr>
      </thead>
      <tbody>
        <?php foreach ($sheet as $no => $row): ?>
        <tr>
          <td><?= $no ?></td>
          <?php foreach (M::playerNumbers($match) as $n): ?>
            <?php if (isset($row[$n])): ?>
              <td><?= $row[$n]['points'] ?: '<span class="text-muted">0</span>' ?></td>
              <td class="font-weight-bold"><?= $row[$n]['total'] ?></td>
            <?php else: ?>
              <td class="text-muted">—</td><td class="text-muted">—</td>
            <?php endif; ?>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endforeach; ?>
  </div>
  <form method="post" action="<?= base_url('admin/scoreboard/' . $match->id . '/comment') ?>" class="px-3 pb-3" id="commentaire">
    <?= csrf_field() ?>
    <h5 class="mb-2"><i class="fas fa-comment-dots mr-1"></i>Commentaire de la feuille de match</h5>
    <textarea name="comment" class="form-control mb-2" rows="2" maxlength="500" style="max-width:1000px"
              placeholder="Incident, réserve, remarque de l'arbitre… (imprimé sur la feuille de match PDF)"><?= esc($match->comment ?? '') ?></textarea>
    <button class="btn btn-primary btn-sm"><i class="fas fa-save mr-1"></i>Enregistrer le commentaire</button>
  </form>
  <div class="card-footer d-flex">
    <a href="<?= base_url('admin/scoreboard') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
    <?php if ($match->type === 'official'): ?>
    <span class="ml-auto mr-3 align-self-center">
      <?php if ($signature): ?>
      <span class="badge badge-success p-2"><i class="fas fa-file-signature mr-1"></i>Signée le <?= date('d/m/Y à H:i', strtotime($signature->signed_at)) ?> — arbitre : <?= esc($signature->referee_name) ?></span>
      <?php elseif ($match->status === 'finished'): ?>
      <span class="badge badge-secondary p-2"><i class="fas fa-signature mr-1"></i>Non signée</span>
      <?php endif; ?>
    </span>
    <a href="<?= base_url('admin/scoreboard/' . $match->id . '/pdf') ?>" target="_blank" class="btn btn-primary">
      <i class="fas fa-file-pdf mr-1"></i>Feuille de match PDF
    </a>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
