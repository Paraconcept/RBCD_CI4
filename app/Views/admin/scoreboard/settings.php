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
    </div>
  </div>
  <button class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
</form>
<?php foreach ($devices as $d): ?>
<form method="post" action="<?= base_url('admin/scoreboard/devices/' . $d->id . '/delete') ?>" id="dev<?= $d->id ?>" class="d-none"><?= csrf_field() ?></form>
<?php endforeach; ?>

<?= $this->endSection() ?>
