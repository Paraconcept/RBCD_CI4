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
    : base_url('admin/intm') ?>">
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
        <div class="col-md-3">
          <div class="form-group">
            <label for="division">Division</label>
            <select name="division" id="division" class="form-control">
              <option value="">— Choisir —</option>
              <?php foreach ($divisions as $d): ?>
              <option value="<?= esc($d) ?>"
                <?= (old('division', $team->division ?? '') === $d) ? 'selected' : '' ?>>
                Division <?= esc($d) ?>
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


</div>

    <div class="card-footer d-flex justify-content-between align-items-center">
      <a href="<?= base_url('admin/intm') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i> Retour
      </a>
      <div class="custom-control custom-switch">
        <input type="hidden" name="is_published" value="0">
        <input type="checkbox" name="is_published" id="is_published" value="1"
               class="custom-control-input"
               <?= old('is_published', $team->is_published ?? 1) ? 'checked' : '' ?>>
        <label class="custom-control-label" for="is_published">Publié</label>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $isEdit ? 'Enregistrer les modifications' : 'Créer l\'équipe' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
