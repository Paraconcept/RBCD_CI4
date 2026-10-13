<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;
$isEdit = $encounter !== null;
$val    = static fn (string $f, $default = '') => old($f, $encounter->$f ?? $default);
$sched  = $encounter->scheduled_at ?? null;
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

<form method="post" autocomplete="off"
      action="<?= $isEdit ? base_url('admin/scoreboard/teams/' . $encounter->id . '/update') : base_url('admin/scoreboard/teams') ?>">
  <?= csrf_field() ?>
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-users mr-2"></i><?= $isEdit ? 'Modifier la rencontre ' . esc($encounter->code) : "Nouvelle rencontre d'équipe" ?></h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-3">
          <div class="form-group">
            <label for="code">N° de rencontre <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control" maxlength="20" required style="text-transform:uppercase"
                   placeholder="ex. LPF001" value="<?= esc($val('code')) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="competition">Compétition <span class="text-danger">*</span></label>
            <select name="competition" id="competition" class="form-control">
              <?php foreach (TE::COMPETITIONS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('competition', 'CDR') === $k ? 'selected' : '' ?>><?= esc($k . ' — ' . $v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="scheduled_date">Date et heure</label>
            <div class="d-flex">
              <input type="date" name="scheduled_date" id="scheduled_date" class="form-control mr-2"
                     value="<?= esc(old('scheduled_date', $sched ? date('Y-m-d', strtotime($sched)) : '')) ?>">
              <input type="time" name="scheduled_time" id="scheduled_time" class="form-control" style="max-width:120px"
                     value="<?= esc(old('scheduled_time', $sched ? date('H:i', strtotime($sched)) : '19:30')) ?>">
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label for="team_id">Notre équipe CDR <span class="text-danger">*</span></label>
            <select name="team_id" id="team_id" class="form-control" required>
              <option value="">— Choisir l'équipe —</option>
              <?php foreach ($teams as $t): ?>
              <option value="<?= $t->id ?>" <?= (int) $val('team_id') === (int) $t->id ? 'selected' : '' ?>
                      data-discipline="<?= esc(M::DISCIPLINES[$t->discipline] . ' ' . $t->format) ?>"
                      data-roster="<?= esc(implode(', ', array_map(static fn ($r) => mb_strtoupper($r->last_name, 'UTF-8') . ' ' . $r->first_name, $t->roster))) ?>">
                <?= esc($t->name . ' — ' . $t->game_mode . ' (' . $t->season . ')') ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted" id="teamInfo"></small>
          </div>
        </div>
        <div class="col-md-5">
          <div class="form-group">
            <label for="team_club">Club visité (notre club)</label>
            <input type="text" name="team_club" id="team_club" class="form-control" maxlength="100"
                   value="<?= esc($val('team_club', 'RBC Disonais')) ?>">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="team_number">Équipe n°</label>
            <input type="number" name="team_number" id="team_number" class="form-control" min="1" max="255"
                   value="<?= esc($val('team_number')) ?>">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5 offset-md-5">
          <div class="form-group">
            <label for="opponent_club">Club visiteur <span class="text-danger">*</span></label>
            <input type="text" name="opponent_club" id="opponent_club" class="form-control" maxlength="100" required
                   placeholder="ex. B.C. Grâce-Hollogne" value="<?= esc($val('opponent_club')) ?>">
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="opponent_number">Équipe n°</label>
            <input type="number" name="opponent_number" id="opponent_number" class="form-control" min="1" max="255"
                   placeholder="ex. 3" value="<?= esc($val('opponent_number')) ?>">
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="team_email">E-mail du club visité</label>
            <input type="email" name="team_email" id="team_email" class="form-control" maxlength="150"
                   value="<?= esc($val('team_email', $clubEmail)) ?>">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="opponent_email">E-mail du club visiteur</label>
            <input type="email" name="opponent_email" id="opponent_email" class="form-control" maxlength="150"
                   value="<?= esc($val('opponent_email')) ?>">
            <small class="text-muted">Retrouvé automatiquement si ce club a déjà été encodé.</small>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>E-mail du responsable CDR</label>
        <input type="text" class="form-control-plaintext" readonly
               value="<?= esc(trim($cdr->name . ' — ' . $cdr->email, ' —')) ?>">
        <small class="text-muted">Défini dans <a href="<?= base_url('admin/scoreboard/settings') ?>">Marquoir › Paramètres</a> ; commun à toutes les rencontres.</small>
      </div>
      <div class="form-group mb-0">
        <label for="notes">Remarques</label>
        <textarea name="notes" id="notes" class="form-control" rows="2"><?= esc($val('notes')) ?></textarea>
      </div>
    </div>
    <div class="card-footer d-flex">
      <a href="<?= $isEdit ? base_url('admin/scoreboard/teams/' . $encounter->id) : base_url('admin/scoreboard/teams') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
      <button class="btn btn-primary ml-auto"><i class="fas fa-save mr-1"></i>Enregistrer</button>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
    const sel = document.getElementById('team_id');
    const info = document.getElementById('teamInfo');
    const num = document.getElementById('team_number');
    const sync = () => {
        const o = sel.options[sel.selectedIndex];
        info.textContent = o && o.value ? 'Discipline : ' + o.dataset.discipline + ' · Joueurs : ' + (o.dataset.roster || '—') : '';
    };
    sel.addEventListener('change', function () {
        sync();
        // « R.B.C. Disonais 1 » → équipe n° 1
        const m = (sel.options[sel.selectedIndex].text.split(' — ')[0] || '').trim().match(/(\d+)$/);
        if (m) num.value = m[1];
    });
    sync();

    // E-mail du club visiteur déjà connu (rencontres précédentes contre ce club)
    const knownEmails = <?= json_encode($knownEmails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const norm = (s) => s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]/g, '');
    const opp = document.getElementById('opponent_club');
    const oppMail = document.getElementById('opponent_email');
    opp.addEventListener('change', function () {
        const mail = knownEmails[norm(opp.value)];
        if (mail && !oppMail.value) oppMail.value = mail;
    });
})();
</script>
<?= $this->endSection() ?>
