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
          <div class="form-group mt-3">
            <label for="sets_recipient">Destinataire des feuilles de match par sets</label>
            <input type="text" name="sets_recipient" id="sets_recipient" class="form-control" maxlength="200" value="<?= esc($setsRecipient) ?>">
            <small class="text-muted">Imprimé au bas de la feuille de match par sets (modèle Coupe de Belgique) : « Feuilles de match à envoyer au plus tard 24 heures après les matches à : … ».</small>
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
