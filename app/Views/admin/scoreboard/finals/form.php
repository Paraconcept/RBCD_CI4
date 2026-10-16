<?php
use App\Models\ScoreboardFinalModel as F;
use App\Models\ScoreboardMatchModel as M;

$isEdit  = $final !== null;
$val     = static fn (string $f, $default = '') => old($f, $final->$f ?? $default);
$sched   = $final->scheduled_at ?? null;
$chosen  = array_map('intval', (array) old('table_ids', $final ? F::tableIds($final) : []));
$locked  = $started ? 'disabled' : '';
$day2    = $final->day2_at ?? null;
$twoDays = (bool) old('two_days', $day2 ? '1' : '');
$placed  = (string) old('placement_matches', $final->placement_matches ?? '0') === '1';
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
    <i class="fas fa-lock mr-2"></i>La finale a commencé : les joueurs et les règles ne peuvent plus changer. Seuls le nom, les dates, le type de finale et les codes club restent modifiables.
  </div>
<?php elseif ($isEdit): ?>
  <div class="alert alert-info">
    <i class="fas fa-info-circle mr-2"></i>À l'enregistrement, les matchs sont régénérés (aucun n'a encore commencé).
  </div>
<?php endif; ?>

<form method="post" autocomplete="off" action="<?= $isEdit ? base_url('admin/scoreboard/finals/' . $final->id . '/update') : base_url('admin/scoreboard/finals') ?>">
  <?= csrf_field() ?>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Finale ou poule</h3></div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label for="name">Nom <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" maxlength="150" required
                   placeholder="ex. Finale du club 3 Bandes PF 2026" value="<?= esc($val('name')) ?>">
          </div>
        </div>
        <div class="col-md-3">
          <div class="form-group">
            <label for="scheduled_date">Date et heure <span data-sb-show-when="two_days">(jour 1)</span></label>
            <div class="d-flex">
              <input type="date" name="scheduled_date" id="scheduled_date" class="form-control mr-2"
                     value="<?= esc(old('scheduled_date', $sched ? date('Y-m-d', strtotime($sched)) : '')) ?>">
              <input type="time" name="scheduled_time" class="form-control" style="max-width:120px"
                     value="<?= esc(old('scheduled_time', $sched ? date('H:i', strtotime($sched)) : '09:00')) ?>">
            </div>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="discipline">Discipline</label>
            <select name="discipline" id="discipline" class="form-control" <?= $locked ?> data-default-limit-3c="<?= (int) $defaults['innings_limit_3c'] ?>">
              <?php foreach (M::DISCIPLINES as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('discipline', 'libre') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label for="format">Format</label>
            <select name="format" id="format" class="form-control" <?= $locked ?>>
              <?php foreach (M::FORMATS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('format', 'PB') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label for="kind">Type de finale</label>
            <select name="kind" id="kind" class="form-control">
              <?php foreach (F::KINDS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('kind', 'championship') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted">Choisit le modèle de la feuille de classement PDF.</small>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label>Modèle</label>
            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="placement_matches" id="placement_0" value="0" <?= $locked ?> <?= $placed ? '' : 'checked' ?>>
              <label class="custom-control-label" for="placement_0"><b data-sb-count="0"></b> Après éliminatoires : chacun contre chacun</label>
            </div>
            <div class="custom-control custom-radio">
              <input type="radio" class="custom-control-input" name="placement_matches" id="placement_1" value="1" <?= $locked ?> <?= $placed ? 'checked' : '' ?>>
              <label class="custom-control-label" for="placement_1"><b data-sb-count="1"></b> Sans éliminatoires : chacun contre chacun + matchs de classement 1er-2e, 3e-4e…</label>
            </div>
          </div>
        </div>
        <div class="col-md-7">
          <div class="form-group">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" name="two_days" id="two_days" value="1" <?= $twoDays ? 'checked' : '' ?>>
              <label class="custom-control-label" for="two_days">Finale sur deux jours</label>
            </div>
            <div class="d-flex flex-wrap align-items-center mt-2" data-sb-show-when="two_days">
              <span class="mr-2">Jour 2 :</span>
              <input type="date" name="day2_date" class="form-control mr-2" style="max-width:170px"
                     value="<?= esc(old('day2_date', $day2 ? date('Y-m-d', strtotime($day2)) : '')) ?>">
              <input type="time" name="day2_time" class="form-control mr-3" style="max-width:120px"
                     value="<?= esc(old('day2_time', $day2 ? date('H:i', strtotime($day2)) : '14:00')) ?>">
              <span class="mr-2">à partir du match n°</span>
              <input type="number" name="day2_from" class="form-control" style="max-width:80px" min="2" max="99"
                     placeholder="auto" value="<?= esc(old('day2_from', $final->day2_from ?? '')) ?>">
            </div>
          </div>
        </div>
      </div>
      <div class="form-group mb-0">
        <label>Billards utilisés</label>
        <small class="text-muted ml-2">Les matchs sont répartis à tour de rôle sur les billards cochés. Aucun : n'importe quel billard.</small>
        <div>
          <?php foreach ($tables as $t): ?>
          <div class="custom-control custom-checkbox custom-control-inline">
            <input type="checkbox" class="custom-control-input" name="table_ids[]" id="table_<?= $t->id ?>" value="<?= $t->id ?>" <?= $locked ?>
                   <?= in_array((int) $t->id, $chosen, true) ? 'checked' : '' ?>>
            <label class="custom-control-label" for="table_<?= $t->id ?>"><?= esc($t->name) ?> <small class="text-muted">(<?= esc($t->format ?? 'PB') ?>)</small></label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-users mr-2"></i>Joueurs (<?= F::MIN_PLAYERS ?> à <?= F::MAX_PLAYERS ?>)</h3>
    </div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead class="thead-rbcd">
          <tr><th width="40" class="text-center">N°</th><th>Nom</th><th>Prénom</th><th>Club</th><th width="95" title="Code FRBB du club, imprimé sur la feuille du championnat régional">Code club</th><th width="260">Catégorie</th><th width="110">Pts à jouer</th></tr>
        </thead>
        <tbody>
        <?php for ($k = 1; $k <= F::MAX_PLAYERS; $k++): $f = 'f' . $k; $pl = $players[$k - 1] ?? null; $pv = static fn (string $n) => old($f . '_' . $n, $pl->$n ?? ''); ?>
          <tr>
            <td class="text-center align-middle font-weight-bold"><?= $k ?></td>
            <td class="position-relative">
              <input type="hidden" name="<?= $f ?>_member_id" value="<?= esc($pv('member_id')) ?>" <?= $locked ?>>
              <input type="hidden" name="<?= $f ?>_license" value="<?= esc($pv('license')) ?>" <?= $locked ?>>
              <input type="text" name="<?= $f ?>_last_name" class="form-control form-control-sm" style="text-transform:uppercase" <?= $locked ?>
                     data-sb-ac="<?= $f ?>" data-sb-ac-url="<?= base_url('admin/scoreboard/players/search') ?>"
                     placeholder="<?= $k <= F::MIN_PLAYERS ? 'Tapez 2 lettres…' : '(facultatif)' ?>" value="<?= esc($pv('last_name')) ?>">
            </td>
            <td><input type="text" name="<?= $f ?>_first_name" class="form-control form-control-sm" <?= $locked ?> value="<?= esc($pv('first_name')) ?>"></td>
            <td><input type="text" name="<?= $f ?>_club" class="form-control form-control-sm" <?= $locked ?> value="<?= esc($pv('club')) ?>"></td>
            <td><input type="text" name="<?= $f ?>_club_code" class="form-control form-control-sm" maxlength="10" style="text-transform:uppercase" <?= $started && !$pl ? 'disabled' : '' ?>
                       placeholder="ex. LLL 30" value="<?= esc($pv('club_code')) ?>"></td>
            <td><select name="<?= $f ?>_category_id" class="form-control form-control-sm" data-sb-category="<?= $f ?>" <?= $locked ?> data-selected="<?= esc($pv('category_id')) ?>"></select></td>
            <td><input type="number" name="<?= $f ?>_distance" class="form-control form-control-sm" min="1" max="9999" <?= $locked ?> value="<?= esc($pv('distance')) ?>"></td>
          </tr>
        <?php endfor; ?>
        </tbody>
      </table>
    </div>
    <div class="card-footer text-muted small">
      <i class="fas fa-info-circle mr-1"></i>Les points à jouer sont repris du barème de la catégorie de chaque joueur (modifiables).
      Inscrivez les joueurs dans l'ordre des moyennes du tour précédent.
      Chacun rencontre chacun une fois ; l'ordre des matchs évite qu'un joueur enchaîne deux matchs.
      À 4 joueurs, calendrier fédéral : 1-2 et 3-4, puis les matchs 3 à 6 se placent d'après leurs résultats (gagnant 1 – perdant 2…).
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>Règles des matchs</h3></div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="innings_limit_enabled" id="innings_limit_enabled" value="1" <?= $locked ?>
                   <?= old('innings_limit_enabled', $isEdit && $final->innings_limit ? '1' : '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="innings_limit_enabled">Limite de reprises</label>
          </div>
          <div class="input-group mt-2" data-sb-show-when="innings_limit_enabled" style="max-width:220px">
            <input type="number" name="innings_limit" id="innings_limit" class="form-control" min="1" max="999" <?= $locked ?>
                   value="<?= esc($val('innings_limit', $defaults['innings_limit_3c'])) ?>">
            <div class="input-group-append"><span class="input-group-text">reprises</span></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="unequal_innings" id="unequal_innings" value="1" <?= $locked ?>
                   <?= old('unequal_innings', $final->unequal_innings ?? '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="unequal_innings">Reprises inégales</label>
          </div>
          <small class="text-muted">Non coché : reprise d'égalisation, le match nul est possible.</small>
        </div>
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="shot_clock_enabled" id="shot_clock_enabled" value="1" <?= $locked ?>
                   <?= old('shot_clock_enabled', $final->shot_clock_enabled ?? '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="shot_clock_enabled">Chrono par coup</label>
          </div>
          <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled" style="max-width:220px">
            <input type="number" name="shot_clock_seconds" class="form-control" min="5" max="300" <?= $locked ?>
                   value="<?= esc($val('shot_clock_seconds', $defaults['shot_clock_seconds'])) ?>">
            <div class="input-group-append"><span class="input-group-text">secondes</span></div>
          </div>
          <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled" style="max-width:220px">
            <input type="number" name="shot_clock_extensions" class="form-control" min="0" max="10" <?= $locked ?>
                   value="<?= esc($val('shot_clock_extensions', $defaults['shot_clock_extensions'])) ?>">
            <div class="input-group-append"><span class="input-group-text">time-outs / joueur</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <a href="<?= $isEdit ? base_url('admin/scoreboard/finals/' . $final->id) : base_url('admin/scoreboard/finals') ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
      <button class="btn btn-primary ml-auto"><i class="fas fa-save mr-1"></i><?= $isEdit ? 'Enregistrer' : 'Créer la finale et générer les matchs' ?></button>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>window.SB_CATEGORIES = <?= json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script>
(function () {
    // Nombre de matchs de chaque modèle d'après les joueurs saisis
    const counts = () => {
        const n = [...document.querySelectorAll('input[name$="_last_name"]')].filter((i) => i.value.trim() !== '').length;
        const rr = n * (n - 1) / 2;
        document.querySelector('[data-sb-count="0"]').textContent = n >= 2 ? rr + ' matchs.' : '';
        document.querySelector('[data-sb-count="1"]').textContent = n >= 2 ? (rr + Math.floor(n / 2)) + ' matchs.' : '';
    };
    // Code club repris d'après le nom du club (codes déjà saisis), tant qu'il n'a pas été tapé à la main
    const codes = <?= json_encode($clubCodes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const key = (club) => club.toLowerCase().replace(/[^a-z0-9]/g, '');
    const fillCodes = () => document.querySelectorAll('input[name$="_club_code"]').forEach((code) => {
        const club = document.querySelector('input[name="' + code.name.replace('_club_code', '_club') + '"]');
        if (!club || code.disabled || (code.value !== '' && !code.dataset.sbAuto)) return;
        const name = document.querySelector('input[name="' + code.name.replace('_club_code', '_last_name') + '"]');
        const found = name && name.value.trim() !== '' ? codes[key(club.value)] || '' : '';
        if (code.value !== found) { code.value = found; code.dataset.sbAuto = '1'; }
    });
    document.querySelectorAll('input[name$="_club_code"]').forEach((code) => code.addEventListener('input', () => delete code.dataset.sbAuto));
    document.addEventListener('focusout', fillCodes);
    // Choix dans l'auto-complétion : le club est rempli juste après le clic ou la touche Entrée
    ['click', 'keyup'].forEach((ev) => document.addEventListener(ev, () => setTimeout(fillCodes)));
    fillCodes();

    document.addEventListener('input', counts);
    document.addEventListener('change', counts);
    counts();
})();
</script>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<?= $this->endSection() ?>
