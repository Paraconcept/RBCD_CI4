<?php use App\Models\ScoreboardSkittlesPoolModel as P; ?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-chess-pawn mr-2"></i><?= P::NAME ?></h3>
    <div class="ml-auto">
      <a href="<?= base_url('admin/scoreboard/skittles/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i>Nouvelle poule</a>
    </div>
  </div>
  <div class="card-body">
    <?php if (!$pools): ?>
      <p class="text-muted text-center mb-0">Aucune poule encodée.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="130">Date</th>
          <th>Poule</th>
          <th>Joueurs</th>
          <th width="110">Billard</th>
          <th width="130" class="text-center">Matchs</th>
          <th width="150" class="text-center">Feuille récap.</th>
          <th width="110" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($pools as $p): ?>
        <?php
          $total = (int) $p->matches_total;
          $done  = (int) $p->matches_finished;
          $badge = $total > 0 && $done === $total ? 'badge-success' : ((int) $p->matches_live > 0 ? 'badge-danger' : 'badge-secondary');
        ?>
        <tr>
          <td><?= $p->scheduled_at ? date('d/m/Y H:i', strtotime($p->scheduled_at)) : '<span class="text-muted">—</span>' ?></td>
          <td><a href="<?= base_url('admin/scoreboard/skittles/' . $p->id) ?>"><strong><?= esc($p->name) ?></strong></a></td>
          <td class="small">
            <?php foreach (P::SLOTS as $slot => $l): ?>
              <span class="text-muted"><?= $l ?></span> <?= esc(P::playerName($p, $slot)) ?><?= $slot < 3 ? '<br>' : '' ?>
            <?php endforeach; ?>
          </td>
          <td><?= esc($p->table_name ?? '—') ?><br><small class="text-muted"><?= esc(P::BILLIARDS[$p->billiard]['label'] ?? '') ?> · <?= (int) $p->set_points ?> pts</small></td>
          <td class="text-center"><span class="badge <?= $badge ?> p-2"><?= $done ?> / <?= $total ?> terminé<?= $done > 1 ? 's' : '' ?></span></td>
          <td class="text-center small">
            <?php if ($p->signed_at): ?><i class="fas fa-file-signature text-success mr-1" title="Signée"></i><?php endif; ?>
            <?= $p->sent_at ? '<span class="text-success">Envoyée le ' . date('d/m', strtotime($p->sent_at)) . '</span>' : ($p->send_error ? '<span class="text-danger">Échec de l\'envoi</span>' : '<span class="text-muted">Non envoyée</span>') ?>
          </td>
          <td class="text-right text-nowrap">
            <a href="<?= base_url('admin/scoreboard/skittles/' . $p->id) ?>" class="btn btn-xs btn-info" data-toggle="tooltip" title="Ouvrir"><i class="fas fa-folder-open"></i></a>
            <a href="<?= base_url('admin/scoreboard/skittles/' . $p->id . '/edit') ?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Modifier"><i class="fas fa-pen"></i></a>
            <?php if ($done === 0 && (int) $p->matches_live === 0): ?>
            <form method="post" action="<?= base_url('admin/scoreboard/skittles/' . $p->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer la poule « <?= esc($p->name, 'js') ?> » et ses matchs ?')">
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
