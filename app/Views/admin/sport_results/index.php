<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="fas fa-trophy mr-2"></i>Résultats sportifs
    </h3>
    <a href="<?= base_url('admin/sport-results/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i>Ajouter un résultat
    </a>
  </div>
  <div class="card-body p-0 mt-4">

    <?php if (empty($grouped)): ?>
    <div class="text-center text-muted py-4">
      <i class="fas fa-trophy fa-2x mb-2 d-block" style="color:#ccc"></i>
      Aucun résultat enregistré.
    </div>

    <?php else: ?>

    <?php
      $currentYear = (int) date('Y');
      $accordionId = 'accordionSeasons';
    ?>

    <div id="<?= $accordionId ?>">
    <?php foreach ($grouped as $season => $results): ?>
    <?php
      // Saison ouverte si elle contient l'année courante (ex: "2025-2026" ou "2026")
      $isOpen   = str_contains((string) $season, (string) $currentYear);
      $slug     = 'season-' . preg_replace('/[^a-z0-9]+/i', '-', (string) $season);
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
            <span class="badge badge-secondary mr-2"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?></span>
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
              <th width="80">Type</th>
              <th>Compétition</th>
              <th width="90" class="text-center">Place</th>
              <th>Joueur</th>
              <th>Finale</th>
              <th width="60" class="text-center">PDF</th>
              <th width="90" class="text-center">Statut</th>
              <th width="80" class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r): ?>
            <?php
              $typeLabel = match($r->type) {
                  'coupe'       => '<span class="badge" style="background:#ffc107;color:#333"><i class="fas fa-trophy mr-1"></i>Coupe</span>',
                  'championnat' => '<span class="badge" style="background:#84252B;color:#fff"><i class="fas fa-medal mr-1"></i>Champ.</span>',
                  default       => '<span class="badge badge-secondary">Autre</span>',
              };
              $place     = (int) $r->place;
              $placeLabel = match(true) {
                  $place === 1 && $r->type === 'coupe'       => '<span class="badge" style="background:#ffc107;color:#333">Vainqueur</span>',
                  $place === 1 && $r->type === 'championnat' => '<span class="badge" style="background:#84252B;color:#fff">Champion</span>',
                  $place === 1                               => '<span class="badge badge-secondary">1er</span>',
                  default                                    => '<span class="badge badge-info">&nbsp;' . $place . '&deg;&nbsp;</span>',
              };
              $playerName = $r->m_id
                  ? (mb_strtoupper($r->m_last) . ' ' . $r->m_first)
                  : ($r->winner_name ?? '—');
            ?>
            <tr>
              <td><?= $typeLabel ?></td>
              <td><?= esc($r->title) ?></td>
              <td class="text-center"><?= $placeLabel ?></td>
              <td><?= esc($playerName) ?></td>
              <td><?= $r->final_date ? date('d/m/Y', strtotime($r->final_date)) : '—' ?></td>
              <td class="text-center">
                <?php if ($r->pdf_file): ?>
                  <a href="<?= base_url('uploads/PDF/SportResults/' . $r->pdf_file) ?>" target="_blank" title="Voir le PDF">
                    <i class="fas fa-file-pdf text-danger"></i>
                  </a>
                <?php else: ?>
                  <i class="fas fa-file-pdf text-muted" title="Pas de PDF"></i>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <form method="post" action="<?= base_url('admin/sport-results/' . $r->id . '/toggle') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-xs <?= $r->is_published ? 'btn-success' : 'btn-secondary' ?>">
                    <?= $r->is_published ? 'Publié' : 'Brouillon' ?>
                  </button>
                </form>
              </td>
              <td class="text-right">
                <a href="<?= base_url('admin/sport-results/' . $r->id . '/edit') ?>"
                   class="btn btn-xs btn-warning" title="Modifier">
                  <i class="fas fa-edit"></i>
                </a>
                <form method="post" action="<?= base_url('admin/sport-results/' . $r->id . '/delete') ?>"
                      class="d-inline" onsubmit="return confirm('Supprimer ce résultat ?')">
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
