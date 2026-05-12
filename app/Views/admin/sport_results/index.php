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
  <div class="card-body p-0">

    <?php if (empty($grouped)): ?>
    <div class="text-center text-muted py-4">
      <i class="fas fa-trophy fa-2x mb-2 d-block" style="color:#ccc"></i>
      Aucun résultat enregistré.
    </div>

    <?php else: ?>

    <?php foreach ($grouped as $season => $results): ?>
    <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
      <strong><i class="fas fa-calendar-alt mr-2 text-muted"></i>Saison <?= esc($season) ?></strong>
      <span class="badge badge-secondary"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?></span>
    </div>
    <table class="table table-sm table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="80">Type</th>
          <th>Compétition</th>
          <th>Vainqueur</th>
          <th>Finale</th>
          <th width="60" class="text-center">PDF</th>
          <th width="100" class="text-right">Actions</th>
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
          $winnerName = $r->m_id
              ? (mb_strtoupper($r->m_last) . ' ' . $r->m_first)
              : ($r->winner_name ?? '—');
        ?>
        <tr>
          <td><?= $typeLabel ?></td>
          <td><?= esc($r->title) ?></td>
          <td><?= esc($winnerName) ?></td>
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
          <td class="text-right">
            <a href="<?= base_url('admin/sport-results/' . $r->id . '/edit') ?>"
               class="btn btn-xs btn-default" title="Modifier">
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
    <?php endforeach; ?>

    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
