<?php
use App\Models\ScoreboardMatchModel as M;
$isEdit = $match !== null;
$val = static fn (string $f, $default = '') => old($f, $match->$f ?? $default);
?>
<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
      <?php foreach ((array) session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<?php if ($isEdit && $match->status !== 'scheduled'): ?>
  <div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle mr-2"></i>
    Cette rencontre est <strong><?= esc(M::STATUSES[$match->status]) ?></strong> : les modifications des règles
    (distance, limite…) ne s'appliqueront qu'au prochain rechargement du marquoir.
  </div>
<?php endif; ?>

<form method="post" autocomplete="off"
      action="<?= $isEdit ? base_url('admin/scoreboard/' . $match->id . '/update') : base_url('admin/scoreboard') ?>">
  <?= csrf_field() ?>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Rencontre</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="scheduled_at">Date et heure</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control"
                   value="<?= esc(($v = $val('scheduled_at')) ? date('Y-m-d\TH:i', strtotime($v)) : '') ?>">
          </div>
        </div>
        <div class="col-md-5">
          <div class="form-group">
            <label for="competition">Compétition</label>
            <input type="text" name="competition" id="competition" class="form-control" maxlength="150"
                   placeholder="ex. Championnat 3 Bandes PF — 2e tour" value="<?= esc($val('competition')) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="table_id">Billard</label>
            <select name="table_id" id="table_id" class="form-control">
              <option value="">— Au choix (n'importe quel billard) —</option>
              <?php foreach ($tables as $t): ?>
              <option value="<?= $t->id ?>" <?= (int) $val('table_id') === (int) $t->id ? 'selected' : '' ?>><?= esc($t->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <?php foreach (['p1' => 'Joueur 1', 'p2' => 'Joueur 2'] as $p => $label): ?>
    <div class="col-md-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-user mr-2"></i><?= $label ?></h3>
        </div>
        <div class="card-body">
          <input type="hidden" name="<?= $p ?>_member_id" value="<?= esc($val($p . '_member_id')) ?>">
          <div class="form-group">
            <label>Nom <span class="text-danger">*</span></label>
            <input type="text" name="<?= $p ?>_last_name" class="form-control" required style="text-transform:uppercase"
                   data-sb-ac="<?= $p ?>" data-sb-ac-url="<?= base_url('admin/scoreboard/players/search') ?>"
                   placeholder="Tapez 2 lettres : membres du club et joueurs déjà encodés" value="<?= esc($val($p . '_last_name')) ?>">
          </div>
          <div class="form-group">
            <label>Prénom <span class="text-danger">*</span></label>
            <input type="text" name="<?= $p ?>_first_name" class="form-control" required value="<?= esc($val($p . '_first_name')) ?>">
          </div>
          <div class="form-group mb-0">
            <label>Club</label>
            <input type="text" name="<?= $p ?>_club" class="form-control" value="<?= esc($val($p . '_club', 'RBC Disonais')) ?>">
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Règles de la partie</h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="discipline">Discipline <span class="text-danger">*</span></label>
            <select name="discipline" id="discipline" class="form-control" data-default-limit-3c="<?= (int) $defaults['innings_limit_3c'] ?>">
              <?php foreach (M::DISCIPLINES as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('discipline', 'libre') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="distance">Points à réaliser <span class="text-danger">*</span></label>
            <input type="number" name="distance" id="distance" class="form-control" min="1" max="9999" required value="<?= esc($val('distance')) ?>">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="innings_limit_enabled" id="innings_limit_enabled" value="1"
                   <?= old('innings_limit_enabled', $isEdit && $match->innings_limit ? '1' : '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="innings_limit_enabled">Limite de reprises</label>
          </div>
          <div class="input-group mt-2" data-sb-show-when="innings_limit_enabled" style="max-width:220px">
            <input type="number" name="innings_limit" id="innings_limit" class="form-control" min="1" max="999"
                   value="<?= esc($val('innings_limit', $defaults['innings_limit_3c'])) ?>">
            <div class="input-group-append"><span class="input-group-text">reprises</span></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="unequal_innings" id="unequal_innings" value="1"
                   <?= old('unequal_innings', $match->unequal_innings ?? '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="unequal_innings">Reprises inégales</label>
          </div>
          <small class="text-muted">Coché : le second joueur ne joue pas de reprise d'égalisation.</small>
        </div>
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="shot_clock_enabled" id="shot_clock_enabled" value="1"
                   <?= old('shot_clock_enabled', $match->shot_clock_enabled ?? '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="shot_clock_enabled">Chrono par coup</label>
          </div>
          <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled" style="max-width:220px">
            <input type="number" name="shot_clock_seconds" class="form-control" min="5" max="300"
                   value="<?= esc($val('shot_clock_seconds', $defaults['shot_clock_seconds'])) ?>">
            <div class="input-group-append"><span class="input-group-text">secondes</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <a href="<?= base_url('admin/scoreboard') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
      <button class="btn btn-primary ml-auto"><i class="fas fa-save mr-1"></i>Enregistrer</button>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') ?>"></script>
<?= $this->endSection() ?>
