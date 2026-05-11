<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ($team !== null); ?>

<?php if (session()->getFlashdata('errors') || $errors = session()->getFlashdata('errors')): ?>
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
      <i class="fas fa-trophy mr-2"></i>
      <?= $isEdit ? 'Modifier l\'équipe' : 'Nouvelle équipe' ?>
    </h3>
  </div>

  <form method="post" action="<?= $isEdit
    ? base_url('admin/cup-regions/' . $team->id . '/update')
    : base_url('admin/cup-regions') ?>">
    <?= csrf_field() ?>

    <div class="card-body">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="name">Nom de l'équipe <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control"
                   value="<?= esc(old('name', $team->name ?? '')) ?>"
                   placeholder="Ex : Les Aigles, Équipe A …" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="game_mode">Mode de jeu <span class="text-danger">*</span></label>
            <select name="game_mode" id="game_mode" class="form-control" required>
              <?php foreach ($modes as $mode): ?>
              <option value="<?= esc($mode) ?>"
                <?= (old('game_mode', $team->game_mode ?? '') === $mode) ? 'selected' : '' ?>>
                <?= esc($mode) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
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
        <div class="col-md-4">
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
        <div class="col-md-4">
          <div class="form-group">
            <label for="player3_id">Joueur 3 <small class="text-muted">(optionnel)</small></label>
            <select name="player3_id" id="player3_id" class="form-control">
              <option value="">— Aucun —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ((int) old('player3_id', $team->player3_id ?? 0) === (int) $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

    </div>

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/cup-regions') ?>" class="btn btn-default">
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
