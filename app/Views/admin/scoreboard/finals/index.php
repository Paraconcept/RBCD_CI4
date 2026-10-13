<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Finales et poules</h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/finals/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Nouvelle finale ou poule</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$finals): ?>
      <p class="text-muted text-center mb-0">Aucune finale encodée.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="130">Date</th>
          <th>Finale</th>
          <th width="120">Discipline</th>
          <th width="90" class="text-center">Joueurs</th>
          <th width="130" class="text-center">Matchs</th>
          <th width="110" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($finals as $f): ?>
        <?php
          $total = (int) $f->matches_total;
          $done  = (int) $f->matches_finished;
          $badge = $total > 0 && $done === $total ? 'badge-success' : ((int) $f->matches_live > 0 ? 'badge-danger' : 'badge-secondary');
        ?>
        <tr>
          <td><?= $f->scheduled_at ? date('d/m/Y H:i', strtotime($f->scheduled_at)) : '<span class="text-muted">—</span>' ?></td>
          <td><a href="<?= base_url('admin/scoreboard/finals/' . $f->id) ?>"><strong><?= esc($f->name) ?></strong></a></td>
          <td><?= esc(M::disciplineLabel($f)) ?></td>
          <td class="text-center"><?= (int) $f->players_count ?></td>
          <td class="text-center"><span class="badge <?= $badge ?> p-2"><?= $done ?> / <?= $total ?> terminé<?= $done > 1 ? 's' : '' ?></span></td>
          <td class="text-right text-nowrap">
            <a href="<?= base_url('admin/scoreboard/finals/' . $f->id) ?>" class="btn btn-xs btn-info" data-toggle="tooltip" title="Ouvrir"><i class="fas fa-folder-open"></i></a>
            <a href="<?= base_url('admin/scoreboard/finals/' . $f->id . '/edit') ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Modifier"><i class="fas fa-pen"></i></a>
            <?php if ($done === 0 && (int) $f->matches_live === 0): ?>
            <form method="post" action="<?= base_url('admin/scoreboard/finals/' . $f->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer la finale « <?= esc($f->name, 'js') ?> » et ses matchs ?')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-danger" data-toggle="tooltip" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
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
