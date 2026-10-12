<?php use App\Models\ScoreboardMatchModel as M; ?>
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

    <table class="table table-sm table-bordered mb-4" style="max-width:900px">
      <thead class="thead-rbcd">
        <tr><th>Joueur</th><th>Club</th><th class="text-center">À réaliser</th><th class="text-center">Points</th><th class="text-center">Reprises</th><th class="text-center">Moyenne</th><th class="text-center">MS</th></tr>
      </thead>
      <tbody>
        <?php foreach ([1, 2] as $n): $p = 'p' . $n; ?>
        <tr class="<?= (int) $match->winner === $n ? 'font-weight-bold' : '' ?>">
          <td>
            <?= (int) $match->starting_player === $n ? '<i class="fas fa-circle mr-1" title="A cassé" style="color:' . ($n === 1 ? '#bbb' : '#f5c400') . '"></i>' : '' ?>
            <?= esc(M::playerLabel($match, $p)) ?>
            <?= (int) $match->winner === $n ? '<i class="fas fa-trophy ml-1 text-warning"></i>' : '' ?>
          </td>
          <td><?= esc($match->{$p . '_club'}) ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_distance'} ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_points'} ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_innings'} ?></td>
          <td class="text-center"><?= M::average((int) $match->{$p . '_points'}, (int) $match->{$p . '_innings'}) ?></td>
          <td class="text-center"><?= (int) $match->{$p . '_high_run'} ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h5 class="mb-2">Feuille de match</h5>
    <?php if (!$sheet): ?>
      <p class="text-muted">Aucune reprise enregistrée.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-striped text-center" style="max-width:600px">
      <thead class="thead-rbcd">
        <tr>
          <th rowspan="2" class="align-middle">Reprise</th>
          <th colspan="2"><?= esc($match->p1_last_name) ?></th>
          <th colspan="2"><?= esc($match->p2_last_name) ?></th>
        </tr>
        <tr><th>Série</th><th>Total</th><th>Série</th><th>Total</th></tr>
      </thead>
      <tbody>
        <?php foreach ($sheet as $no => $row): ?>
        <tr>
          <td><?= $no ?></td>
          <?php foreach ([1, 2] as $n): ?>
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
    <?php endif; ?>
  </div>
  <div class="card-footer d-flex">
    <a href="<?= base_url('admin/scoreboard') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
    <a href="<?= base_url('admin/scoreboard/' . $match->id . '/pdf') ?>" target="_blank" class="btn btn-primary ml-auto">
      <i class="fas fa-file-pdf mr-1"></i>Feuille de match PDF
    </a>
  </div>
</div>

<?= $this->endSection() ?>
