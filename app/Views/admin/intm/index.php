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
    <h3 class="card-title mb-0"><i class="fas fa-users mr-2"></i>I.N.T.M. — Équipes</h3>
    <a href="<?= base_url('admin/intm/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Nouvelle équipe
    </a>
  </div>
  <div class="card-body p-0 mt-4">

    <?php if (empty($teams)): ?>
    <div class="text-center text-muted py-4">
      <i class="fas fa-users fa-2x mb-2 d-block" style="color:#ccc"></i>
      Aucune équipe enregistrée.
    </div>

    <?php else: ?>

    <?php
      $grouped = [];
      foreach ($teams as $team) {
          $grouped[$team->season][] = $team;
      }
      krsort($grouped);

      $currentYear = (int) date('Y');
      $accordionId = 'accordionINTM';
    ?>

    <div id="<?= $accordionId ?>">
    <?php foreach ($grouped as $season => $seasonTeams): ?>
    <?php
      $isOpen = str_contains((string) $season, (string) $currentYear);
      $slug   = 'intm-' . preg_replace('/[^a-z0-9]+/i', '-', (string) $season);
    ?>
    <div class="card card-secondary mb-0 rounded-0 border-left-0 border-right-0">

      <div class="card-header p-0" id="heading-<?= $slug ?>">
        <button class="btn btn-link w-100 text-left px-3 py-2 d-flex justify-content-between align-items-center<?= $isOpen ? '' : ' collapsed' ?>"
                type="button"
                data-toggle="collapse"
                data-target="#<?= $slug ?>"
                aria-expanded="<?= $isOpen ? 'true' : 'false' ?>"
                aria-controls="<?= $slug ?>">
          <span>
            <i class="fas fa-calendar-alt mr-2 text-muted"></i>
            <strong>Saison <?= esc($season) ?></strong>
          </span>
          <span class="d-flex align-items-center">
            <span class="badge badge-secondary mr-2"><?= count($seasonTeams) ?> équipe<?= count($seasonTeams) > 1 ? 's' : '' ?></span>
            <i class="fas fa-chevron-down accordion-chevron"></i>
          </span>
        </button>
      </div>

      <div id="<?= $slug ?>"
           style="padding:10px;"
           class="collapse<?= $isOpen ? ' show' : '' ?>"
           aria-labelledby="heading-<?= $slug ?>"
           data-parent="#<?= $accordionId ?>">
        <table class="table table-sm table-bordered table-hover mb-1">
          <thead class="thead-rbcd">
            <tr>
              <th>Équipe</th>
              <th style="width:90px">Division</th>
              <th>Joueur 1</th>
              <th>Joueur 2</th>
              <th>Joueur 3</th>
              <th>Joueur 4</th>
              <th style="width:90px" class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($seasonTeams as $team): ?>
            <tr>
              <td><strong><?= esc($team->name) ?></strong></td>
              <td><?= $team->division ? esc($team->division) : '<span class="text-muted">—</span>' ?></td>
              <td><?= esc($team->player1_name) ?></td>
              <td><?= esc($team->player2_name) ?></td>
              <td><?= esc($team->player3_name) ?></td>
              <td><?= esc($team->player4_name) ?></td>
              <td class="text-right">
                <a href="<?= base_url('admin/intm/' . $team->id . '/edit') ?>"
                   class="btn btn-xs btn-warning" title="Modifier">
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
    <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div>
</div>

<style>
.accordion-chevron {
  transition: transform .25s ease;
  font-size: .75rem;
}
.btn-link[aria-expanded="true"] .accordion-chevron {
  transform: rotate(180deg);
}
.btn-link:hover, .btn-link:focus {
  text-decoration: none;
  color: inherit;
}
.card-secondary .card-header .btn-link,
.card-secondary .card-header .btn-link:hover,
.card-secondary .card-header .btn-link:focus {
  color: #fff;
}
.card-secondary .card-header .badge {
  font-size: 1rem;
  font-weight: 600;
}
</style>

<?= $this->endSection() ?>
