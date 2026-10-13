<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-users mr-2"></i>Rencontres d'équipe</h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/teams/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Nouvelle rencontre d'équipe</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$encounters): ?>
      <p class="text-muted text-center mb-0">Aucune rencontre d'équipe encodée.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="100">N°</th>
          <th width="130">Date</th>
          <th width="110">Compétition</th>
          <th>Rencontre</th>
          <th width="110">Discipline</th>
          <th width="110" class="text-center">Matchs</th>
          <th width="110" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($encounters as $e): ?>
        <?php
          $total = (int) $e->matches_total;
          $done  = (int) $e->matches_finished;
          $badge = $total > 0 && $done === $total ? 'badge-success' : ((int) $e->matches_live > 0 ? 'badge-danger' : 'badge-secondary');
        ?>
        <tr>
          <td><a href="<?= base_url('admin/scoreboard/teams/' . $e->id) ?>"><strong><?= esc($e->code) ?></strong></a></td>
          <td><?= $e->scheduled_at ? date('d/m/Y H:i', strtotime($e->scheduled_at)) : '<span class="text-muted">—</span>' ?></td>
          <td><?= esc($e->competition) ?></td>
          <td><?= esc(TE::homeLabel($e)) ?> <span class="text-muted">contre</span> <?= esc(TE::awayLabel($e)) ?></td>
          <td><?= esc(M::disciplineLabel($e)) ?></td>
          <td class="text-center"><span class="badge <?= $badge ?> p-2"><?= $done ?> / <?= $total ?> terminé<?= $done > 1 ? 's' : '' ?></span></td>
          <td class="text-right text-nowrap">
            <a href="<?= base_url('admin/scoreboard/teams/' . $e->id) ?>" class="btn btn-xs btn-info" data-toggle="tooltip" title="Ouvrir la fiche"><i class="fas fa-folder-open"></i></a>
            <a href="<?= base_url('admin/scoreboard/teams/' . $e->id . '/edit') ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Modifier"><i class="fas fa-pen"></i></a>
            <form method="post" action="<?= base_url('admin/scoreboard/teams/' . $e->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer la rencontre <?= esc($e->code, 'js') ?> ? Ses matchs ne sont pas supprimés : ils redeviennent des matchs individuels.')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-danger" data-toggle="tooltip" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () { $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' }); });
</script>
<?= $this->endSection() ?>
