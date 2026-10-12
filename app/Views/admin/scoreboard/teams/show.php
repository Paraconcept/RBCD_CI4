<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;

$total = count($matches);
$done  = count(array_filter($matches, static fn ($m) => $m->status === 'finished'));
$statusBadge = static fn (string $s): string => match ($s) {
    'live'      => '<span class="badge badge-danger"><i class="fas fa-circle mr-1"></i>En cours</span>',
    'scheduled' => '<span class="badge badge-info">Prévu</span>',
    'finished'  => '<span class="badge badge-success">Terminé</span>',
    default     => '<span class="badge badge-secondary">' . esc(M::STATUSES[$s] ?? $s) . '</span>',
};
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title">
      <i class="fas fa-users mr-2"></i><strong><?= esc($encounter->code) ?></strong>
      · <?= esc(TE::COMPETITIONS[$encounter->competition] ?? $encounter->competition) ?>
      <?= $encounter->scheduled_at ? ' · ' . date('d/m/Y H:i', strtotime($encounter->scheduled_at)) : '' ?>
      · <?= esc(M::disciplineLabel($encounter)) ?>
    </h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/edit') ?>" class="btn btn-warning btn-sm"><i class="fas fa-pen mr-1"></i>Modifier</a>
    </div>
  </div>
  <div class="card-body">
    <h4 class="mb-0">
      <?= esc($encounter->team_name) ?> <small class="text-muted">contre</small> <?= esc($encounter->opponent_club) ?>
    </h4>
    <?php if ($encounter->notes): ?><p class="text-muted mt-2 mb-0"><?= nl2br(esc($encounter->notes)) ?></p><?php endif; ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-list-ol mr-2"></i>Matchs de la rencontre</h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/matches/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Ajouter un match</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$matches): ?>
      <p class="text-muted text-center mb-0">Aucun match pour l'instant. Ajoutez-en un, ou rattachez un match déjà encodé ci-dessous.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="40">#</th>
          <th width="70">Heure</th>
          <th width="100">Billard</th>
          <th>Notre joueur</th>
          <th>Adversaire</th>
          <th width="90" class="text-center">Pts à jouer</th>
          <th width="90" class="text-center">Score</th>
          <th width="90" class="text-center">Reprises</th>
          <th width="90" class="text-center">Statut</th>
          <th width="150" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($matches as $i => $m): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= $m->scheduled_at ? date('H:i', strtotime($m->scheduled_at)) : '—' ?></td>
          <td><?= esc($m->table_name ?? '—') ?></td>
          <td class="<?= (int) $m->winner === 1 ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p1')) ?></td>
          <td class="<?= (int) $m->winner === 2 ? 'font-weight-bold' : '' ?>"><?= esc(M::playerLabel($m, 'p2')) ?></td>
          <td class="text-center"><?= (int) $m->p1_distance ?> / <?= (int) $m->p2_distance ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : (int) $m->p1_points . ' – ' . (int) $m->p2_points ?></td>
          <td class="text-center"><?= $m->status === 'scheduled' ? '—' : max((int) $m->p1_innings, (int) $m->p2_innings) ?></td>
          <td class="text-center"><?= $statusBadge($m->status) ?></td>
          <td class="text-right text-nowrap">
            <?php if ($m->status !== 'scheduled'): ?>
              <a href="<?= base_url('admin/scoreboard/' . $m->id) ?>" class="btn btn-xs btn-info" title="Feuille de match"><i class="fas fa-list-ol"></i></a>
              <a href="<?= base_url('admin/scoreboard/' . $m->id . '/pdf') ?>" target="_blank" class="btn btn-xs btn-primary" title="Feuille de match PDF"><i class="fas fa-file-pdf"></i></a>
            <?php endif; ?>
            <a href="<?= base_url('admin/scoreboard/' . $m->id . '/edit') ?>" class="btn btn-xs btn-warning" title="Modifier"><i class="fas fa-pen"></i></a>
            <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/detach/' . $m->id) ?>" class="d-inline"
                  onsubmit="return confirm('Détacher ce match de la rencontre ? Il reste disponible comme match individuel.')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-secondary" title="Détacher de la rencontre"><i class="fas fa-unlink"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

    <?php if ($attachable): ?>
    <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $encounter->id . '/attach') ?>" class="form-inline mt-3">
      <?= csrf_field() ?>
      <label class="mr-2 text-muted" for="match_id"><i class="fas fa-link mr-1"></i>Rattacher un match déjà encodé :</label>
      <select name="match_id" id="match_id" class="form-control form-control-sm mr-2" style="max-width:560px" required>
        <option value="">— Choisir un match —</option>
        <?php foreach ($attachable as $a): ?>
        <option value="<?= $a->id ?>">
          <?= esc(($a->scheduled_at ? date('d/m H:i', strtotime($a->scheduled_at)) . ' · ' : '') . M::playerLabel($a, 'p1') . ' – ' . M::playerLabel($a, 'p2') . ($a->competition ? ' · ' . $a->competition : '') . ' (' . (M::STATUSES[$a->status] ?? $a->status) . ')') ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-primary"><i class="fas fa-link mr-1"></i>Rattacher</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-flag-checkered mr-2"></i>Résultat de la rencontre</h3>
    <div class="ml-auto">
      <button class="btn btn-sm btn-primary" disabled title="Disponible quand le modèle de feuille récapitulative sera intégré">
        <i class="fas fa-file-pdf mr-1"></i>Feuille récapitulative
      </button>
    </div>
  </div>
  <div class="card-body">
    <?php if ($total === 0): ?>
      <p class="text-muted mb-0">En attente des matchs.</p>
    <?php elseif ($done < $total): ?>
      <p class="mb-0"><i class="fas fa-hourglass-half mr-2 text-muted"></i>En attente : <strong><?= $done ?> / <?= $total ?></strong> match<?= $total > 1 ? 's' : '' ?> terminé<?= $done > 1 ? 's' : '' ?>.</p>
    <?php else: ?>
      <p class="mb-0 text-success"><i class="fas fa-check mr-2"></i>Les <?= $total ?> matchs sont terminés. Le calcul du résultat global et la feuille récapitulative arrivent avec votre modèle PDF.</p>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
