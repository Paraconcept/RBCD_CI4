<?= $this->extend('admin/layouts/main') ?>
<?php use App\Models\ScoreboardMatchModel as M; ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-table mr-2"></i>Billards</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Chaque Chromebook ouvre la page de son billard. Ajoutez-la aux favoris du Chromebook concerné.
      Le format est présélectionné dans les parties amicales jouées sur ce billard.
    </p>
    <table class="table table-sm table-bordered mb-4" style="max-width:1000px">
      <thead class="thead-rbcd">
        <tr><th>Nom</th><th width="160">Format</th><th width="90">Ordre</th><th width="80" class="text-center">Actif</th><th>Adresse pour le Chromebook</th><th width="130" class="text-right">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tables as $t): ?>
        <tr>
          <form method="post" action="<?= base_url('admin/scoreboard/tables/' . $t->id . '/update') ?>" id="tbl<?= $t->id ?>">
            <?= csrf_field() ?>
          </form>
          <td><input type="text" name="name" form="tbl<?= $t->id ?>" class="form-control form-control-sm" value="<?= esc($t->name) ?>" required></td>
          <td>
            <select name="format" form="tbl<?= $t->id ?>" class="form-control form-control-sm">
              <?php foreach (M::FORMATS as $k => $v): ?>
              <option value="<?= $k ?>" <?= ($t->format ?? 'PB') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td><input type="number" name="sort_order" form="tbl<?= $t->id ?>" class="form-control form-control-sm" value="<?= (int) $t->sort_order ?>"></td>
          <td class="text-center align-middle"><input type="checkbox" name="is_active" value="1" form="tbl<?= $t->id ?>" <?= $t->is_active ? 'checked' : '' ?>></td>
          <td class="align-middle"><a href="<?= base_url('marquoir/billard/' . $t->id) ?>" target="_blank"><?= base_url('marquoir/billard/' . $t->id) ?></a></td>
          <td class="text-right text-nowrap align-middle">
            <button form="tbl<?= $t->id ?>" class="btn btn-xs btn-warning" title="Enregistrer"><i class="fas fa-save"></i></button>
            <form method="post" action="<?= base_url('admin/scoreboard/tables/' . $t->id . '/delete') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer ce billard ? Les rencontres liées resteront, sans billard.')">
              <?= csrf_field() ?>
              <button class="btn btn-xs btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <form method="post" action="<?= base_url('admin/scoreboard/tables') ?>" class="form-inline">
      <?= csrf_field() ?>
      <input type="text" name="name" class="form-control mr-2" placeholder="ex. Billard 2" required>
      <select name="format" class="form-control mr-2">
        <?php foreach (M::FORMATS as $k => $v): ?>
        <option value="<?= $k ?>"><?= $v ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Ajouter un billard</button>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
