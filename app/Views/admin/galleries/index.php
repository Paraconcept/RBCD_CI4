<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">
      <i class="fas fa-images mr-2"></i>Galeries photos
    </h3>
    <a href="<?= base_url('admin/galleries/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i>Nouvelle galerie
    </a>
  </div>
  <div class="card-body p-0">

    <?php if (empty($galleries)): ?>
    <div class="text-center text-muted py-4">
      <i class="fas fa-images fa-2x mb-2 d-block" style="color:#ccc"></i>
      Aucune galerie enregistrée.
    </div>

    <?php else: ?>
    <table class="table table-sm table-hover mb-0">
      <thead class="thead-rbcd">
        <tr>
          <th width="70">Couverture</th>
          <th>Titre</th>
          <th>Saison</th>
          <th>Date</th>
          <th class="text-center">Photos</th>
          <th class="text-center">Publié</th>
          <th width="120" class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($galleries as $g): ?>
        <tr>
          <td>
            <?php if ($g->cover_filename): ?>
              <img src="<?= base_url('uploads/galleries/' . $g->id . '/' . $g->cover_filename) ?>"
                   alt="" style="width:56px;height:42px;object-fit:cover;border-radius:3px;">
            <?php else: ?>
              <div style="width:56px;height:42px;background:#f0f0f0;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-image text-muted"></i>
              </div>
            <?php endif; ?>
          </td>
          <td class="align-middle font-weight-bold"><?= esc($g->title) ?></td>
          <td class="align-middle"><?= $g->season ? esc($g->season) : '—' ?></td>
          <td class="align-middle"><?= $g->event_date ? date('d/m/Y', strtotime($g->event_date)) : '—' ?></td>
          <td class="align-middle text-center">
            <span class="badge badge-secondary"><?= $g->photo_count ?></span>
          </td>
          <td class="align-middle text-center">
            <?php if ($g->is_published): ?>
              <i class="fas fa-check-circle text-success"></i>
            <?php else: ?>
              <i class="fas fa-times-circle text-muted"></i>
            <?php endif; ?>
          </td>
          <td class="align-middle text-right">
            <a href="<?= base_url('admin/galleries/' . $g->id . '/photos') ?>"
               class="btn btn-xs btn-info" title="Gérer les photos">
              <i class="fas fa-images"></i>
            </a>
            <a href="<?= base_url('admin/galleries/' . $g->id . '/edit') ?>"
               class="btn btn-xs btn-default" title="Modifier">
              <i class="fas fa-edit"></i>
            </a>
            <form method="post" action="<?= base_url('admin/galleries/' . $g->id . '/delete') ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer cette galerie et toutes ses photos ?')">
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
    <?php endif; ?>

  </div>
</div>

<?= $this->endSection() ?>
