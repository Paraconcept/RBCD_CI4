<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardSkittlesPoolModel as P;

$isEdit = $pool !== null;
$val    = static fn (string $f, $default = '') => old($f, $pool->$f ?? $default);
$sched  = $pool->scheduled_at ?? null;
$locked = $started ? 'disabled' : '';
$billiard = (string) $val('billiard', '284');
$check  = static fn (string $f, $default = 1): string => old($f, $pool->$f ?? ($isEdit ? 0 : $default)) ? 'checked' : '';
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

<?php if ($started): ?>
  <div class="alert alert-warning">
    <i class="fas fa-lock mr-2"></i>La poule a commencé : les joueurs, le billard et les points ne peuvent plus changer.
  </div>
<?php elseif ($isEdit): ?>
  <div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>À l'enregistrement, les trois matchs sont régénérés (aucun n'a encore commencé).
  </div>
<?php endif; ?>

<form method="post" autocomplete="off" action="<?= $isEdit ? base_url('admin/scoreboard/skittles/' . $pool->id . '/update') : base_url('admin/scoreboard/skittles') ?>">
  <?= csrf_field() ?>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-chess-pawn mr-2"></i><?= P::NAME ?></h3></div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="name">Nom de la poule <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" maxlength="150" required
                   placeholder="ex. Série 2" value="<?= esc($val('name')) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="scheduled_date">Date et heure <span class="text-danger">*</span></label>
            <div class="d-flex">
              <input type="date" name="scheduled_date" id="scheduled_date" class="form-control mr-2" required
                     value="<?= esc(old('scheduled_date', $sched ? date('Y-m-d', strtotime($sched)) : '')) ?>">
              <input type="time" name="scheduled_time" class="form-control" style="max-width:120px"
                     value="<?= esc(old('scheduled_time', $sched ? date('H:i', strtotime($sched)) : '19:30')) ?>">
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="table_id">Billard du marquoir</label>
            <select name="table_id" id="table_id" class="form-control">
              <option value="">— N'importe quel billard —</option>
              <?php foreach ($tables as $t): ?>
              <option value="<?= $t->id ?>" <?= (int) $val('table_id') === (int) $t->id ? 'selected' : '' ?>><?= esc($t->name) ?> (<?= esc($t->format ?? 'PB') ?>)</option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Les trois matchs se jouent sur ce billard. Plusieurs poules le même soir : une par billard.</small>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <div class="form-group mb-md-0">
            <label>Billard joué</label>
            <?php foreach (P::BILLIARDS as $code => $b): ?>
            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="billiard" id="billiard_<?= $code ?>" value="<?= $code ?>" <?= $locked ?>
                     data-points="<?= (int) $b['points'] ?>" <?= $billiard === (string) $code ? 'checked' : '' ?>>
              <label class="custom-control-label" for="billiard_<?= $code ?>"><?= esc($b['label']) ?> — <?= (int) $b['points'] ?> points par set</label>
            </div>
            <?php endforeach; ?>
            <small class="text-muted">L'autre mention est biffée sur la feuille récapitulative.</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group mb-md-0">
            <label for="set_points">Points par set</label>
            <input type="number" name="set_points" id="set_points" class="form-control" min="1" max="999" style="max-width:140px" <?= $locked ?>
                   value="<?= esc($val('set_points', P::BILLIARDS[$billiard]['points'] ?? 60)) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <p class="text-muted small mb-0 mt-md-4">
            <i class="fas fa-info-circle mr-1"></i>Chaque match se joue à reprises inégales, en <?= P::SETS_TO_WIN ?> sets gagnants, sans chrono.
            Points de match : victoire 2-0 = 3, victoire 2-1 = 2, défaite = 0.
          </p>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2"></i>Joueurs</h3></div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="thead-rbcd">
          <tr><th width="60" class="text-center">Joueur</th><th>Nom</th><th>Prénom</th><th>Club</th><th width="140">N° licence</th></tr>
        </thead>
        <tbody>
        <?php foreach (P::SLOTS as $slot => $letter): $k = P::key($slot); $pv = static fn (string $n) => $val($k . '_' . $n); ?>
          <tr>
            <td class="text-center align-middle font-weight-bold"><?= $letter ?></td>
            <td class="position-relative">
              <input type="hidden" name="<?= $k ?>_member_id" value="<?= esc($pv('member_id')) ?>" <?= $locked ?>>
              <input type="text" name="<?= $k ?>_last_name" class="form-control form-control-sm" style="text-transform:uppercase" required <?= $locked ?>
                     data-sb-ac="<?= $k ?>" data-sb-ac-url="<?= base_url('admin/scoreboard/players/search') ?>"
                     placeholder="Tapez 2 lettres…" value="<?= esc($pv('last_name')) ?>">
            </td>
            <td><input type="text" name="<?= $k ?>_first_name" class="form-control form-control-sm" required <?= $locked ?> value="<?= esc($pv('first_name')) ?>"></td>
            <td><input type="text" name="<?= $k ?>_club" class="form-control form-control-sm" style="text-transform:uppercase" <?= $locked ?> value="<?= esc($pv('club')) ?>"></td>
            <td><input type="text" name="<?= $k ?>_license" class="form-control form-control-sm" maxlength="25" <?= $locked ?> value="<?= esc($pv('license')) ?>"></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer text-muted small">
      <i class="fas fa-info-circle mr-1"></i>Ordre officiel des matchs : 1 = B contre C, 2 = C contre A, 3 = A contre B.
      Les noms sont repris dans le récapitulatif au bas de la feuille, toujours dans l'ordre A, B, C (pas de classement).
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Feuille récapitulative</h3></div>
    <div class="card-body">
      <div class="custom-control custom-switch mb-3">
        <input type="checkbox" class="custom-control-input" name="signature_required" id="signature_required" value="1" <?= $check('signature_required') ?>>
        <label class="custom-control-label" for="signature_required">Feuille à signer par les trois joueurs (au marquoir)</label>
      </div>
      <p class="text-muted small mb-2">Seule la feuille récapitulative est envoyée, automatiquement : après les signatures, ou
        <?= \App\Libraries\MatchResultMailer::AUTO_DELAY ?> minutes après la fin du dernier match sans signature.</p>
      <?php foreach ([
          'regional' => ['Responsable régional 5 quilles', $regional['email'], $regional['name']],
          'club_ds'  => ['DS du club', M::CLUB_DS_EMAIL, 'RBC Disonais'],
      ] as $key => [$label, $default, $who]): ?>
      <div class="form-row align-items-center mb-2">
        <div class="col-md-4">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" name="send_to_<?= $key ?>" id="send_to_<?= $key ?>" value="1" <?= $check('send_to_' . $key) ?>>
            <label class="custom-control-label" for="send_to_<?= $key ?>"><?= esc($label) ?></label>
          </div>
        </div>
        <div class="col-md-5">
          <input type="email" name="<?= $key ?>_email" class="form-control form-control-sm" maxlength="150"
                 placeholder="<?= esc($default) ?>" value="<?= esc($val($key . '_email')) ?>">
        </div>
        <div class="col-md-3 text-muted small"><?= esc($who) ?></div>
      </div>
      <?php endforeach; ?>
      <small class="text-muted">Adresse vide = adresse par défaut (Marquoir › Paramètres).</small>
    </div>
    <div class="card-footer d-flex">
      <a href="<?= $isEdit ? base_url('admin/scoreboard/skittles/' . $pool->id) : base_url('admin/scoreboard/skittles') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
      <button class="btn btn-primary ml-auto"><i class="fas fa-save mr-1"></i><?= $isEdit ? 'Enregistrer' : 'Créer la poule et générer les matchs' ?></button>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
// Points par set repris du billard choisi
document.querySelectorAll('input[name="billiard"]').forEach((r) => r.addEventListener('change', () => {
    document.getElementById('set_points').value = r.dataset.points;
}));
</script>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<?= $this->endSection() ?>
