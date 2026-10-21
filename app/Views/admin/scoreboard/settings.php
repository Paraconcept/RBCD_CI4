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
            <label for="shot_clock_extensions">Time-outs par joueur (par partie, ou par set en jeu par sets)</label>
            <input type="number" name="shot_clock_extensions" id="shot_clock_extensions" class="form-control" min="0" max="10" value="<?= $shotClockExtensions ?>" style="max-width:150px">
            <small class="text-muted">Valeur proposée par défaut dans les formulaires. Chaque fois que le chrono arrive à zéro, un time-out est consommé et le chrono repart ;
            le dernier time-out arrête le chrono sur « Temps écoulé » jusqu'à ce que l'arbitre passe la main (<kbd>Entrée</kbd>).</small>
          </div>
          <div class="form-group">
            <label for="innings_limit_3c">Limite de reprises au 3 Bandes</label>
            <input type="number" name="innings_limit_3c" id="innings_limit_3c" class="form-control" min="1" max="999" value="<?= $inningsLimit3c ?>" style="max-width:150px">
            <small class="text-muted">Pré-remplie quand on choisit la discipline 3 Bandes (la limite reste à activer).</small>
          </div>
          <div class="form-group mb-0">
            <label for="warmup_minutes">Durée de l'échauffement (minutes par joueur)</label>
            <input type="number" name="warmup_minutes" id="warmup_minutes" class="form-control" min="1" max="15" value="<?= $warmupMinutes ?>" style="max-width:150px">
            <small class="text-muted">Décompte affiché avant la partie, la dernière minute en rouge.</small>
          </div>
          <div class="form-group mt-3">
            <label for="club_email">Adresse e-mail du club (feuilles récapitulatives)</label>
            <input type="email" name="club_email" id="club_email" class="form-control" maxlength="150" value="<?= esc($clubEmail) ?>" style="max-width:320px">
            <small class="text-muted">Proposée par défaut comme e-mail du club visité dans chaque rencontre d'équipe.</small>
          </div>
          <div class="form-row mt-3">
            <div class="form-group col-sm-6">
              <label for="cdr_name">Responsable CDR</label>
              <input type="text" name="cdr_name" id="cdr_name" class="form-control" maxlength="100" value="<?= esc($cdrName) ?>">
            </div>
            <div class="form-group col-sm-6">
              <label for="cdr_email">E-mail du responsable CDR</label>
              <input type="email" name="cdr_email" id="cdr_email" class="form-control" maxlength="150" value="<?= esc($cdrEmail) ?>">
            </div>
            <small class="text-muted col-12 mt-n2">Destinataire des feuilles récapitulatives des rencontres d'équipe, avec les clubs visité et visiteur (imprimé au bas du PDF).</small>
          </div>
          <div class="form-row mt-3">
            <div class="form-group col-sm-5">
              <label for="dsr_name">Directeur Sportif Régional</label>
              <input type="text" name="dsr_name" id="dsr_name" class="form-control" maxlength="100" value="<?= esc($dsr['name']) ?>">
            </div>
            <div class="form-group col-sm-4">
              <label for="dsr_email">E-mail du DSR</label>
              <input type="email" name="dsr_email" id="dsr_email" class="form-control" maxlength="150" value="<?= esc($dsr['email']) ?>">
            </div>
            <div class="form-group col-sm-3">
              <label for="dsr_mobile">GSM du DSR</label>
              <input type="text" name="dsr_mobile" id="dsr_mobile" class="form-control" maxlength="30" value="<?= esc($dsr['mobile']) ?>">
            </div>
            <small class="text-muted col-12 mt-n2">Imprimé au bas de la feuille de match par sets (modèle Coupe de Belgique, destinataire des feuilles) et de la feuille de classement des finales de championnat régional.</small>
          </div>
          <div class="form-group mb-0 mt-3">
            <label for="start_window_minutes">Démarrage des rencontres programmées (minutes avant l'heure prévue)</label>
            <input type="number" name="start_window_minutes" id="start_window_minutes" class="form-control" min="0" max="720" value="<?= $startWindowMinutes ?>" style="max-width:150px">
            <small class="text-muted">Avant ce délai, le bouton « Démarrer » reste verrouillé sur le marquoir (60 = une heure avant).</small>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="card card-outline card-primary" id="appareils">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-shield-halved mr-2"></i>Appareils autorisés (Chromebooks, tablette du bar)</h3>
        </div>
        <div class="card-body">
          <p class="text-muted small">
            Le marquoir (<b>rbcd.be/marquoir</b>) et la page du bar (<b>rbcd.be/marquoir/bar</b>) ne s'ouvrent qu'aux membres du comité connectés et aux appareils ci-dessous.
            Pour en ajouter un : au club, connectez-vous sur l'appareil, ouvrez le marquoir (ou la page du bar) et cliquez sur « Autoriser cet appareil » ; vous pouvez ensuite vous déconnecter.
            <b>Retirer</b> un appareil lui coupe l'accès immédiatement.
          </p>
          <?php if (!$devices): ?>
            <div class="alert alert-warning mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Aucun appareil autorisé : seuls les membres du comité connectés peuvent utiliser le marquoir.</div>
          <?php else: ?>
          <table class="table table-sm table-bordered mb-0">
            <thead class="thead-rbcd">
              <tr><th>Appareil</th><th>Autorisé par</th><th>Le</th><th>Dernière utilisation</th><th width="90" class="text-right">Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($devices as $d): ?>
              <tr>
                <td><?= esc($d->name) ?></td>
                <td><?= esc($d->author ?? '—') ?></td>
                <td><?= $d->created_at ? date('d/m/Y', strtotime($d->created_at)) : '—' ?></td>
                <td><?= $d->last_seen_at ? date('d/m/Y H:i', strtotime($d->last_seen_at)) : '—' ?> <small class="text-muted"><?= esc($d->last_ip ?? '') ?></small></td>
                <td class="text-right">
                  <button form="dev<?= $d->id ?>" class="btn btn-xs btn-danger"
                          onclick="return confirm('Retirer « <?= esc($d->name, 'js') ?> » ? Il n\'aura plus accès au marquoir.')"><i class="fas fa-trash mr-1"></i>Retirer</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php endif; ?>
        </div>
      </div>
      <div class="card card-outline card-primary" id="pause">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-images mr-2"></i>Écran de pause</h3>
        </div>
        <div class="card-body">
          <p class="text-muted small">
            Pendant une pause (touche <kbd>.</kbd>), le marquoir affiche l'écusson du club, puis chaque image ci-dessous, <b>15 secondes</b> chacune, en boucle.
            Idéal pour les sponsors ou les annonces du club. Images au format paysage de préférence ; JPEG, PNG, WebP ou GIF, 5 Mo maximum.
          </p>
          <div class="d-flex flex-wrap mb-3" style="gap:.75rem">
            <div class="text-center">
              <div class="border rounded d-flex align-items-center justify-content-center bg-dark" style="width:160px;height:100px">
                <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="" style="max-width:100%;max-height:100%">
              </div>
              <small class="text-muted">1 · Écusson (fixe)</small>
            </div>
            <?php foreach ($pauseImages as $i => $img): ?>
            <div class="text-center">
              <div class="border rounded d-flex align-items-center justify-content-center bg-dark" style="width:160px;height:100px">
                <img src="<?= base_url(\App\Libraries\ScoreboardPauseImages::DIR . $img) ?>" alt="" style="max-width:100%;max-height:100%">
              </div>
              <small class="text-muted"><?= $i + 2 ?></small>
              <button form="pauseDel<?= $i ?>" class="btn btn-xs btn-danger ml-1"
                      onclick="return confirm('Retirer cette image de l\'écran de pause ?')"><i class="fas fa-trash mr-1"></i>Retirer</button>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="input-group">
            <div class="custom-file">
              <input type="file" name="images[]" id="pauseImages" class="custom-file-input" form="pauseUpload" multiple accept="image/jpeg,image/png,image/webp,image/gif">
              <label class="custom-file-label" for="pauseImages" data-browse="Parcourir">Choisir une ou plusieurs images…</label>
            </div>
            <div class="input-group-append">
              <button form="pauseUpload" class="btn btn-success"><i class="fas fa-upload mr-1"></i>Ajouter</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
</form>
<?php foreach ($devices as $d): ?>
<form method="post" action="<?= base_url('admin/scoreboard/devices/' . $d->id . '/delete') ?>" id="dev<?= $d->id ?>" class="d-none"><?= csrf_field() ?></form>
<?php endforeach; ?>
<form method="post" action="<?= base_url('admin/scoreboard/pause-images') ?>" id="pauseUpload" enctype="multipart/form-data" class="d-none"><?= csrf_field() ?></form>
<?php foreach ($pauseImages as $i => $img): ?>
<form method="post" action="<?= base_url('admin/scoreboard/pause-images/delete') ?>" id="pauseDel<?= $i ?>" class="d-none"><?= csrf_field() ?><input type="hidden" name="name" value="<?= esc($img) ?>"></form>
<?php endforeach; ?>
<script>
document.getElementById('pauseImages').addEventListener('change', function () {
  const n = this.files.length;
  this.nextElementSibling.textContent = n > 1 ? n + ' images choisies' : (n ? this.files[0].name : 'Choisir une ou plusieurs images…');
});
</script>

<?= $this->endSection() ?>
