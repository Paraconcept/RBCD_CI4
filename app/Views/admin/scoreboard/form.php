<?php
use App\Models\ScoreboardMatchModel as M;
use App\Models\ScoreboardTeamEncounterModel as TE;
$isEdit  = $match !== null;
$team    = $team ?? null;
$roster  = $roster ?? [];
// Valeurs du match existant, sinon pré-remplissage (match d'une rencontre d'équipe)
$src     = $match ?? (object) ($prefill ?? []);
$val     = static fn (string $f, $default = '') => old($f, $src->$f ?? $default);
$backUrl = $team ? base_url('admin/scoreboard/teams/' . $team->id) : base_url('admin/scoreboard');
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
    (points à réaliser, limite…) ne s'appliqueront qu'au prochain rechargement du marquoir.
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
            <?php $sched = $src->scheduled_at ?? null; ?>
            <label for="scheduled_date">Date et heure</label>
            <div class="d-flex">
              <input type="date" name="scheduled_date" id="scheduled_date" class="form-control mr-2"
                     value="<?= esc(old('scheduled_date', $sched ? date('Y-m-d', strtotime($sched)) : '')) ?>">
              <input type="time" name="scheduled_time" id="scheduled_time" class="form-control" style="max-width:120px"
                     value="<?= esc(old('scheduled_time', $sched ? date('H:i', strtotime($sched)) : '19:30')) ?>">
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <?php if ($team): ?>
          <input type="hidden" name="team_encounter_id" value="<?= (int) $team->id ?>">
          <input type="hidden" name="competition" value="">
          <label>Rencontre d'équipe</label>
          <div class="border rounded px-3 py-2 bg-light">
            <strong><?= esc(TE::label($team)) ?></strong><br>
            <span class="text-muted"><?= esc(TE::homeLabel($team)) ?><i class="fas fa-arrows-alt-h mx-2"></i><?= esc(TE::awayLabel($team)) ?></span>
          </div>
          <?php else: ?>
          <div class="form-row">
            <div class="form-group col-8">
              <label for="competition">Compétition</label>
              <input type="text" name="competition" id="competition" class="form-control" maxlength="150"
                     placeholder="ex. Championnat 3 Bandes PF — 2e tour" value="<?= esc($val('competition')) ?>">
            </div>
            <div class="form-group col-4">
              <label for="match_number">N° de match</label>
              <input type="text" name="match_number" id="match_number" class="form-control" maxlength="30"
                     placeholder="calendrier" value="<?= esc($val('match_number')) ?>">
            </div>
          </div>
          <?php endif; ?>
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
          <?php if ($p === 'p1' && $roster): ?>
          <div class="mb-3">
            <small class="text-muted d-block mb-1">Joueurs de l'équipe :</small>
            <?php foreach ($roster as $r): ?>
            <button type="button" class="btn btn-sm btn-outline-primary mr-1 mb-1" data-roster
                    data-member-id="<?= (int) $r->id ?>" data-license="<?= esc($r->frbb_license ?? '') ?>" data-categories="<?= esc(json_encode((object) ($rosterCategories[(int) $r->id] ?? []))) ?>" data-last-name="<?= esc(mb_strtoupper($r->last_name, 'UTF-8')) ?>" data-first-name="<?= esc($r->first_name) ?>">
              <i class="fas fa-user mr-1"></i><?= esc(mb_strtoupper($r->last_name, 'UTF-8') . ' ' . $r->first_name) ?>
            </button>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <?php if ($team): ?>
          <div class="custom-control custom-checkbox mb-3">
            <input type="checkbox" class="custom-control-input" name="<?= $p ?>_absent" id="<?= $p ?>_absent" value="1" data-sb-absent="<?= $p ?>"
                   <?= old($p . '_absent', ((int) ($src->absent ?? 0)) & ($p === 'p1' ? 1 : 2) ? '1' : '') ? 'checked' : '' ?>>
            <label class="custom-control-label text-danger" for="<?= $p ?>_absent"><i class="fas fa-user-slash mr-1"></i>Absent (forfait)</label>
          </div>
          <?php endif; ?>
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
          <div class="row">
            <div class="col-8">
              <div class="form-group">
                <label>Club</label>
                <input type="text" name="<?= $p ?>_club" class="form-control" style="text-transform:uppercase" value="<?= esc($val($p . '_club', \App\Models\ScoreboardPlayerModel::HOME_CLUB)) ?>">
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label>N° licence</label>
                <input type="text" name="<?= $p ?>_license" class="form-control" maxlength="25" value="<?= esc($val($p . '_license')) ?>">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-8">
              <div class="form-group mb-0">
                <label>Catégorie</label>
                <select name="<?= $p ?>_category_id" class="form-control" data-sb-category="<?= $p ?>"
                        data-selected="<?= esc($val($p . '_category_id')) ?>"></select>
                <small class="text-muted">Sert à départager à la moyenne proportionnelle si la limite de reprises est atteinte.</small>
              </div>
            </div>
            <div class="col-4" data-sb-distance>
              <div class="form-group mb-0">
                <label><span data-sb-distance-label>Points à réaliser</span> <span class="text-danger">*</span></label>
                <input type="number" name="<?= $p ?>_distance" class="form-control" min="1" max="9999" required value="<?= esc($val($p . '_distance')) ?>"
                       data-sb-set-default="<?= (int) $defaults['set_points'] ?>">
              </div>
            </div>
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
            <label for="format">Format <span class="text-danger">*</span></label>
            <select name="format" id="format" class="form-control">
              <?php foreach (M::FORMATS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('format', 'PB') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <?php $setsOn = old('sets_enabled', !empty($src->sets_to_win) ? '1' : ''); ?>
      <div class="row mb-3" data-sb-sets-block>
        <div class="col-md-4">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="sets_enabled" id="sets_enabled" value="1" <?= $setsOn ? 'checked' : '' ?>>
            <label class="custom-control-label" for="sets_enabled">Par sets</label>
          </div>
          <small class="text-muted">Toujours à reprises inégales. Le joueur qui commence le 1<sup>er</sup> set garde la bille blanche ; les joueurs commencent les sets à tour de rôle.
            Les <b>points par set</b> de chaque joueur se règlent dans sa fiche (ils peuvent être différents).</small>
        </div>
        <div class="col-md-4" data-sb-show-when="sets_enabled">
          <div class="input-group" style="max-width:220px">
            <input type="number" name="sets_to_win" class="form-control" min="1" max="9"
                   value="<?= esc(old('sets_to_win', $src->sets_to_win ?? $defaults['sets_to_win'])) ?>">
            <div class="input-group-append"><span class="input-group-text">sets gagnants</span></div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
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
        <div class="col-md-3">
          <div class="custom-control custom-switch">
            <?php // Par défaut, pas de signature pour les matchs d'une rencontre d'équipe (CDR) ?>
            <input type="checkbox" class="custom-control-input" name="signature_required" id="signature_required" value="1"
                   <?= old('signature_required', $isEdit ? $match->signature_required : ($team ? '' : '1')) ? 'checked' : '' ?>>
            <label class="custom-control-label" for="signature_required">Feuille à signer</label>
          </div>
          <small class="text-muted">Coché : les joueurs et l'arbitre peuvent signer la feuille au marquoir en fin de partie.</small>
        </div>
        <div class="col-md-3">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="unequal_innings" id="unequal_innings" value="1"
                   <?= old('unequal_innings', $match->unequal_innings ?? '') ? 'checked' : '' ?>>
            <label class="custom-control-label" for="unequal_innings">Reprises inégales</label>
          </div>
          <small class="text-muted">Coché : le second joueur ne joue pas de reprise d'égalisation.</small>
        </div>
        <div class="col-md-3">
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
          <div class="input-group mt-2" data-sb-show-when="shot_clock_enabled" style="max-width:220px">
            <input type="number" name="shot_clock_extensions" class="form-control" min="0" max="10"
                   value="<?= esc($val('shot_clock_extensions', $defaults['shot_clock_extensions'])) ?>">
            <div class="input-group-append"><span class="input-group-text" data-sb-timeouts-label>time-outs / joueur</span></div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <a href="<?= $backUrl ?>" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i>Retour</a>
      <button class="btn btn-primary ml-auto"><i class="fas fa-save mr-1"></i>Enregistrer</button>
    </div>
  </div>
</form>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>window.SB_CATEGORIES = <?= json_encode($categories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= base_url('assets/scoreboard/scoreboard-forms.js') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard-forms.js') ?>"></script>
<script>
// Joueur absent (forfait) : nom, prénom et points à réaliser ne sont plus obligatoires
document.querySelectorAll('[data-sb-absent]').forEach(function (cb) {
    const f = cb.form, p = cb.dataset.sbAbsent;
    const sync = function () {
        ['last_name', 'first_name', 'distance'].forEach(function (n) {
            const el = f.querySelector('[name="' + p + '_' + n + '"]');
            if (!el) return;
            el.required = !cb.checked;
            if (n === 'distance') el.min = cb.checked ? 0 : 1;
        });
    };
    cb.addEventListener('change', sync);
    sync();
});
document.querySelectorAll('[data-roster]').forEach(function (b) {
    b.addEventListener('click', function () {
        const f = b.closest('form');
        f.querySelector('[name="p1_member_id"]').value  = b.dataset.memberId;
        f.querySelector('[name="p1_last_name"]').value  = b.dataset.lastName;
        f.querySelector('[name="p1_first_name"]').value = b.dataset.firstName;
        f.querySelector('[name="p1_license"]').value    = b.dataset.license;
        f.querySelector('[name="p1_last_name"]').dispatchEvent(new CustomEvent('sb:player', { detail: JSON.parse(b.dataset.categories || '{}') }));
        document.querySelectorAll('[data-roster]').forEach(x => x.classList.toggle('active', x === b));
    });
});
</script>
<?= $this->endSection() ?>
