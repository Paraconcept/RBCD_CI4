<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusBadge = static fn (string $s): string => match ($s) {
    'live'      => '<span class="badge badge-danger"><i class="fas fa-circle mr-1"></i>En cours</span>',
    'scheduled' => '<span class="badge badge-info">Prévue</span>',
    'finished'  => '<span class="badge badge-success">Terminée</span>',
    default     => '<span class="badge badge-secondary">' . esc(M::STATUSES[$s] ?? $s) . '</span>',
};
$rows = static function (array $list) use ($statusBadge): void { ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="110">Date</th>
          <th width="80">Type</th>
          <th>Discipline</th>
          <th>Joueur 1</th>
          <th width="90" class="text-center">Score</th>
          <th>Joueur 2</th>
          <th width="90" class="text-center">Reprises</th>
          <th width="100">Billard</th>
          <th width="90" class="text-center">Statut</th>
          <th width="160" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($list as $m): ?>
        <?php $date = $m->finished_at ?? $m->scheduled_at; ?>
        <tr>
          <td><?= $date ? date('d/m/Y H:i', strtotime($date)) : '<span class="text-muted">—</span>' ?></td>
          <td><?= $m->type === 'official' ? '<span class="badge" style="background:#84252B;color:#fff">Officielle</span>' : '<span class="badge badge-secondary">Amicale</span>' ?></td>
          <td>
            <?= esc(M::disciplineLabel($m)) ?> · <?= esc(M::distanceLabel($m)) ?>
            <?php if ($competition = M::competitionLabel($m)): ?><br><small class="text-muted"><?= esc($competition) ?></small><?php endif; ?>
          </td>
          <td class="<?= $m->winner == 1 ? 'font-weight-bold' : '' ?>">
            <?= esc(M::playerLabel($m, 'p1')) ?><br><small class="text-muted"><?= esc($m->p1_club) ?></small>
          </td>
          <td class="text-center align-middle"><?= $m->status === 'scheduled' ? '—' : M::score($m, 'p1') . ' – ' . M::score($m, 'p2') . (M::isSets($m) ? ' sets' : '') ?></td>
          <td class="<?= $m->winner == 2 ? 'font-weight-bold' : '' ?>">
            <?= esc(M::playerLabel($m, 'p2')) ?><br><small class="text-muted"><?= esc($m->p2_club) ?></small>
          </td>
          <td class="text-center align-middle"><?= $m->status === 'scheduled' ? '—' : max((int) $m->p1_innings, (int) $m->p2_innings) ?></td>
          <td><?= esc($m->table_name ?? '—') ?></td>
          <td class="text-center"><?= $statusBadge($m->status) ?></td>
          <td class="text-right text-nowrap">
            <?php if ($m->status !== 'scheduled'): ?>
              <a href="<?= base_url('admin/scoreboard/' . $m->id) ?>" class="btn btn-xs btn-info" data-toggle="tooltip" title="Feuille de match"><i class="fas fa-list-ol"></i></a>
              <a href="<?= base_url('admin/scoreboard/' . $m->id . '/pdf') ?>" target="_blank" class="btn btn-xs btn-primary" data-toggle="tooltip" title="Feuille de match PDF"><i class="fas fa-file-pdf"></i></a>
            <?php endif; ?>
            <a href="<?= base_url('admin/scoreboard/' . $m->id . '/edit') ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Modifier"><i class="fas fa-pen"></i></a>
            <?php if ($m->status !== 'scheduled' && $m->type === 'official'): ?>
              <form method="post" action="<?= base_url('admin/scoreboard/' . $m->id . '/reset') ?>" class="d-inline"
                    onsubmit="return confirm('Remettre cette rencontre à zéro ? Toutes les reprises seront effacées.')">
                <?= csrf_field() ?>
                <button class="btn btn-xs btn-secondary" data-toggle="tooltip" title="Remettre à zéro"><i class="fas fa-undo"></i></button>
              </form>
            <?php endif; ?>
            <form method="post" action="<?= base_url('admin/scoreboard/' . $m->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer définitivement cette rencontre ?')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-danger" data-toggle="tooltip" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
<?php }; ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-calendar-check mr-2"></i>Rencontres prévues et en cours</h3>
    <div class="ml-auto">
      <a href="<?= base_url('marquoir') ?>" target="_blank" class="btn btn-default btn-sm mr-2"><i class="fas fa-tv mr-1"></i>Ouvrir le marquoir</a>
      <a href="<?= base_url('admin/scoreboard/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Nouvelle rencontre</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$upcoming): ?>
      <p class="text-muted text-center mb-0">Aucune rencontre prévue.</p>
    <?php else: $rows($upcoming); endif; ?>
  </div>
</div>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-flag-checkered mr-2"></i>Parties terminées <small class="text-muted">(100 dernières)</small></h3>
  </div>
  <div class="card-body">
    <?php if (!$finished): ?>
      <p class="text-muted text-center mb-0">Aucune partie terminée.</p>
    <?php else: $rows($finished); endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () { $('[data-toggle="tooltip"]').tooltip({ trigger: 'hover' }); });
</script>
<?= $this->endSection() ?>
