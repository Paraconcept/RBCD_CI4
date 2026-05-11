<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ($team !== null); ?>

<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
      <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-users mr-2"></i>
      <?= $isEdit ? 'Modifier l\'équipe I.N.T.M.' : 'Nouvelle équipe I.N.T.M.' ?>
    </h3>
  </div>

  <form method="post" action="<?= $isEdit
    ? base_url('admin/intm/' . $team->id . '/update')
    : base_url('admin/intm') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card-body">

      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label for="name">Nom de l'équipe <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control"
                   value="<?= esc(old('name', $team->name ?? 'R.B.C. Disonais ')) ?>"
                   placeholder="Ex : R.B.C. Disonais 1" required>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="season">Saison <span class="text-danger">*</span></label>
            <select name="season" id="season" class="form-control" required>
              <option value="">— Choisir —</option>
              <?php foreach ($seasons as $s): ?>
              <option value="<?= esc($s) ?>"
                <?= (old('season', $team->season ?? SAISON_EN_COURS) === $s) ? 'selected' : '' ?>>
                <?= esc($s) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="player1_id">Joueur 1 <span class="text-danger">*</span></label>
            <select name="player1_id" id="player1_id" class="form-control" required>
              <option value="">— Choisir —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ((int) old('player1_id', $team->player1_id ?? 0) === (int) $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="player2_id">Joueur 2 <span class="text-danger">*</span></label>
            <select name="player2_id" id="player2_id" class="form-control" required>
              <option value="">— Choisir —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ((int) old('player2_id', $team->player2_id ?? 0) === (int) $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="player3_id">Joueur 3 <span class="text-danger">*</span></label>
            <select name="player3_id" id="player3_id" class="form-control" required>
              <option value="">— Choisir —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ((int) old('player3_id', $team->player3_id ?? 0) === (int) $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="player4_id">Joueur 4 <span class="text-danger">*</span></label>
            <select name="player4_id" id="player4_id" class="form-control" required>
              <option value="">— Choisir —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ((int) old('player4_id', $team->player4_id ?? 0) === (int) $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>Photo de l'équipe</label>
            <?php if ($isEdit && $team->photo): ?>
            <div class="mb-2">
              <img src="<?= base_url('uploads/intm_teams/' . $team->photo) ?>"
                   alt="Photo équipe" style="max-height:120px;border-radius:4px;border:1px solid #dee2e6;">
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" name="remove_photo" id="remove_photo" value="1" class="custom-control-input">
              <label class="custom-control-label text-danger" for="remove_photo">Supprimer la photo</label>
            </div>
            <?php endif; ?>
            <div class="input-group">
              <div class="custom-file">
                <input type="file" name="photo" id="photo" class="custom-file-input"
                       accept="image/jpeg,image/png,image/webp">
                <label class="custom-file-label" for="photo">
                  <?= ($isEdit && $team->photo) ? 'Remplacer la photo…' : 'Choisir une photo…' ?>
                </label>
              </div>
            </div>
            <small class="form-text text-muted">JPG, PNG ou WebP · max 3 Mo</small>
          </div>
        </div>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/intm') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i> Retour
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'équipe' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
