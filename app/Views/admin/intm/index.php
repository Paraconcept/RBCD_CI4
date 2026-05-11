<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-users mr-2"></i>I.N.T.M. — Équipes</h3>
    <a href="<?= base_url('admin/intm/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Nouvelle équipe
    </a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" id="intmTable">
      <thead class="thead-rbcd">
        <tr>
          <th>Équipe</th>
          <th style="width:100px">Saison</th>
          <th>Joueur 1</th>
          <th>Joueur 2</th>
          <th>Joueur 3</th>
          <th>Joueur 4</th>
          <th style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($teams as $team): ?>
        <tr>
          <td><strong><?= esc($team->name) ?></strong></td>
          <td><?= esc($team->season) ?></td>
          <td><?= esc($team->player1_name) ?></td>
          <td><?= esc($team->player2_name) ?></td>
          <td><?= esc($team->player3_name) ?></td>
          <td><?= esc($team->player4_name) ?></td>
          <td class="text-right">
            <a href="<?= base_url('admin/intm/' . $team->id . '/edit') ?>"
               class="btn btn-xs btn-default" title="Modifier">
              <i class="fas fa-pencil-alt"></i>
            </a>
            <form method="post" action="<?= base_url('admin/intm/' . $team->id . '/delete') ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer cette équipe ?');">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
  $('#intmTable').DataTable({ order: [[0, 'asc']], pageLength: 25 });
});
</script>
<?= $this->endSection() ?>
