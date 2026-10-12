<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<form method="post" action="<?= base_url('admin/scoreboard/settings') ?>">
  <?= csrf_field() ?>
  <div class="row">
    <div class="col-lg-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Valeurs par défaut</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="shot_clock_seconds">Chrono par coup (secondes)</label>
            <input type="number" name="shot_clock_seconds" id="shot_clock_seconds" class="form-control" min="5" max="300" value="<?= $shotClockSeconds ?>" style="max-width:150px">
            <small class="text-muted">Proposé par défaut quand on active le chrono sur une partie.</small>
          </div>
          <div class="form-group">
            <label for="shot_clock_extensions">Extensions de chrono par joueur et par partie</label>
            <input type="number" name="shot_clock_extensions" id="shot_clock_extensions" class="form-control" min="0" max="10" value="<?= $shotClockExtensions ?>" style="max-width:150px">
            <small class="text-muted">Chaque extension ajoute une durée de chrono complète (touche <kbd>,</kbd> du pavé).</small>
          </div>
          <div class="form-group mb-0">
            <label for="innings_limit_3c">Limite de reprises au 3 Bandes</label>
            <input type="number" name="innings_limit_3c" id="innings_limit_3c" class="form-control" min="1" max="999" value="<?= $inningsLimit3c ?>" style="max-width:150px">
            <small class="text-muted">Pré-remplie quand on choisit la discipline 3 Bandes (la limite reste à activer).</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Accès des Chromebooks</h3>
        </div>
        <div class="card-body">
          <div class="form-group">
            <label for="pin">Code PIN du marquoir</label>
            <input type="text" name="pin" id="pin" class="form-control" inputmode="numeric" pattern="\d{4,8}" maxlength="8"
                   value="<?= esc($pin) ?>" style="max-width:150px">
          </div>
          <p class="text-muted small mb-0">
            Demandé une seule fois sur chaque Chromebook, puis retenu.
            Il empêche quiconque de modifier une partie depuis Internet.
            <strong>Changer le code déconnecte tous les Chromebooks</strong>, qui devront le ressaisir.
            Un membre du comité connecté à l'administration a toujours accès au marquoir.
          </p>
          <?php if ($pin === ''): ?>
            <div class="alert alert-warning mt-3 mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Aucun code défini : seuls les membres du comité connectés peuvent utiliser le marquoir.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
</form>

<?= $this->endSection() ?>
