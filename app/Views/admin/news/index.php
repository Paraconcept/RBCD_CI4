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
    <h3 class="card-title"><i class="fas fa-newspaper mr-2"></i>Actualités</h3>
    <a href="<?= base_url('admin/news/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Nouvelle actualité
    </a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" id="newsTable">
      <thead class="thead-rbcd">
        <tr>
          <th style="width:70px">Image</th>
          <th>Titre</th>
          <th>Extrait</th>
          <th style="width:110px">Date</th>
          <th style="width:90px">Statut</th>
          <th style="width:90px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($news as $n): ?>
        <tr>
          <td>
            <?php if ($n->image): ?>
              <img src="<?= base_url('uploads/news/' . $n->image) ?>"
                   style="width:56px;height:40px;object-fit:cover;border-radius:3px;">
            <?php else: ?>
              <span class="text-muted"><i class="fas fa-image fa-lg"></i></span>
            <?php endif; ?>
          </td>
          <td><strong><?= esc($n->title) ?></strong><br>
            <small class="text-muted">/actualites/<?= esc($n->slug) ?></small>
          </td>
          <td><small><?= esc(mb_strimwidth($n->excerpt ?? '', 0, 90, '…')) ?></small></td>
          <td data-order="<?= $n->published_at ? strtotime($n->published_at) : 0 ?>">
            <?= $n->published_at ? esc(date('d/m/Y', strtotime($n->published_at))) : '<span class="text-muted">—</span>' ?>
          </td>
          <td>
            <form method="post" action="<?= base_url('admin/news/' . $n->id . '/toggle') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-xs <?= $n->is_published ? 'btn-success' : 'btn-secondary' ?>">
                <?= $n->is_published ? 'Publié' : 'Brouillon' ?>
              </button>
            </form>
          </td>
          <td class="text-right">
            <a href="<?= base_url('admin/news/' . $n->id . '/edit') ?>"
               class="btn btn-xs btn-warning" title="Modifier">
              <i class="fas fa-edit"></i>
            </a>
            <form method="post" action="<?= base_url('admin/news/' . $n->id . '/delete') ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer cette actualité ?');">
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
  $('#newsTable').DataTable({
    order: [[3, 'desc']],
    pageLength: 25,
    columnDefs: [{ orderable: false, targets: [0, 4, 5] }],
  });
});
</script>
<?= $this->endSection() ?>
