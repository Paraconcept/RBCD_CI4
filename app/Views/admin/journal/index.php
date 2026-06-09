<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title"><i class="fas fa-newspaper mr-2"></i>Journal "Partie Libre"</h3>
    <div class="d-flex align-items-center" style="gap:.5rem">
      <form method="get" class="d-flex align-items-center mr-2">
        <label class="mr-2 mb-0 font-weight-bold">Année :</label>
        <select name="year" class="form-control form-control-sm" style="width:100px"
                onchange="this.form.submit()">
          <?php foreach ($years as $y): ?>
            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <a href="<?= base_url('admin/journal/create') ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Ajouter un nouveau numéro
      </a>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-hover mb-0" id="journalTable">
      <thead class="thead-rbcd">
        <tr>
          <th>Titre</th>
          <th style="width:110px">Date</th>
          <th style="width:70px" class="text-center">PDF</th>
          <th style="width:90px" class="text-center">Publié</th>
          <th style="width:110px"></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($issues)): ?>
        <tr>
          <td colspan="5" class="text-center text-muted py-4">Aucun numéro pour <?= $year ?>.</td>
        </tr>
        <?php else: ?>
        <?php foreach ($issues as $issue): ?>
        <tr>
          <td><?= esc($issue->title) ?></td>
          <td data-order="<?= $issue->published_date ?? '0000-00' ?>">
            <?= $issue->published_date ? date('m/Y', strtotime($issue->published_date)) : '—' ?>
          </td>
          <td class="text-center">
            <?php if ($issue->file_path): ?>
              <a href="<?= base_url('uploads/PDF/PartieLibre/' . $issue->file_path) ?>" target="_blank" title="Voir le PDF">
                <i class="fas fa-file-pdf text-danger fa-lg"></i>
              </a>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?= $issue->is_published
                ? '<span class="badge badge-success">Oui</span>'
                : '<span class="badge badge-secondary">Non</span>' ?>
          </td>
          <td class="text-right">
            <a href="<?= base_url('admin/journal/' . $issue->id . '/edit') ?>"
               class="btn btn-xs btn-warning" title="Modifier">
              <i class="fas fa-edit"></i>
            </a>
            <form method="post" action="<?= base_url('admin/journal/' . $issue->id . '/delete') ?>"
                  class="d-inline" onsubmit="return confirm('Supprimer ce numéro ?');">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-xs btn-danger" title="Supprimer">
                <i class="fas fa-trash"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
  $('#journalTable').DataTable({ order: [[1, 'desc']], pageLength: 25 });
});
</script>
<?= $this->endSection() ?>
