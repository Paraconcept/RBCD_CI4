<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if ($msg = session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc($msg) ?></div>
<?php endif; ?>
<?php if ($msg = session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc($msg) ?></div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-file-pdf mr-2"></i>Documents PDF</h3>
    <a href="<?= base_url('admin/documents/create') ?>" class="btn btn-primary btn-sm">
      <i class="fas fa-plus mr-1"></i> Ajouter un document
    </a>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" id="docsTable">
      <thead class="thead-rbcd">
        <tr>
          <th>Titre</th>
          <th style="width:200px">Slug / URL</th>
          <th style="width:70px" class="text-center">PDF</th>
          <th style="width:100px">Date upload</th>
          <th style="width:110px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($documents as $doc): ?>
        <tr>
          <td><?= esc($doc->title) ?></td>
          <td>
            <?php if ($doc->slug): ?>
              <code>/documents/<?= esc($doc->slug) ?></code>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if ($doc->filename): ?>
              <a href="<?= base_url('uploads/PDF/Documents/' . $doc->filename) ?>" target="_blank" title="<?= esc($doc->filename) ?>">
                <i class="fas fa-file-pdf text-danger fa-lg"></i>
              </a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td><?= $doc->uploaded_at ? date('d/m/Y', strtotime($doc->uploaded_at)) : '—' ?></td>
          <td class="text-right">
            <a href="<?= base_url('admin/documents/' . $doc->id . '/edit') ?>"
               class="btn btn-xs btn-default" title="Modifier">
              <i class="fas fa-pencil-alt"></i>
            </a>
            <form method="post" action="<?= base_url('admin/documents/' . $doc->id . '/delete') ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer ce document ?');">
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
  $('#docsTable').DataTable({
    order: [[0, 'asc']],
    pageLength: 25,
    language: { emptyTable: 'Aucun document encore ajouté.' }
  });
});
</script>
<?= $this->endSection() ?>
