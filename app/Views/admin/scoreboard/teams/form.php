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
      <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i><?= $isEdit ? 'Rencontre ' . esc($encounter->code) : "Nouvelle rencontre d'équipe" ?></h3>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-5">
          <div class="form-group">
            <label for="team_id">Notre équipe CDR <span class="text-danger">*</span></label>
            <select name="team_id" id="team_id" class="form-control" required>
              <option value="">— Choisir l'équipe —</option>
              <?php foreach ($teams as $t): ?>
              <option value="<?= $t->id ?>" data-frbb="<?= (int) ($t->frbb_team_id ?? 0) ?>" <?= (int) $val('team_id') === (int) $t->id ? 'selected' : '' ?>
                      data-discipline="<?= esc(M::DISCIPLINES[$t->discipline] . ' ' . $t->format) ?>"
                      data-roster="<?= esc(implode(', ', array_map(static fn ($r) => mb_strtoupper($r->last_name, 'UTF-8') . ' ' . $r->first_name, $t->roster))) ?>">
                <?= esc($t->name . ' — ' . $t->game_mode . ' (' . $t->season . ')') ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="text-muted" id="teamInfo"></small>
          </div>
        </div>
        <div class="col-md-7">
          <div class="form-group">
            <label for="frbbEncounter">Rencontre du calendrier CDR</label>
            <select id="frbbEncounter" class="form-control" disabled>
              <option value="">— Choisissez d'abord notre équipe —</option>
            </select>
            <small class="text-muted" id="frbbInfo">Remplit le n°, la date, l'équipe visiteuse et propose la composition (site FRBB Liège-Lux).</small>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <div class="form-group mb-md-0">
            <label for="code">N° de rencontre <span class="text-danger">*</span></label>
            <input type="text" name="code" id="code" class="form-control" maxlength="20" required style="text-transform:uppercase"
                   placeholder="ex. LPF001" value="<?= esc($val('code')) ?>">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group mb-md-0">
            <label for="competition">Compétition <span class="text-danger">*</span></label>
            <select name="competition" id="competition" class="form-control">
              <?php foreach (TE::COMPETITIONS as $k => $v): ?>
              <option value="<?= $k ?>" <?= $val('competition', 'CDR') === $k ? 'selected' : '' ?>><?= esc($k . ' — ' . $v) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-5">
          <div class="form-group mb-0">
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
    </div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-home mr-2"></i>Équipe visitée (notre équipe)</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-8">
              <div class="form-group">
                <label for="team_club">Club</label>
                <input type="text" name="team_club" id="team_club" class="form-control" maxlength="100" style="text-transform:uppercase"
                       value="<?= esc($val('team_club', \App\Models\ScoreboardPlayerModel::HOME_CLUB)) ?>">
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label for="team_number">Équipe n°</label>
                <input type="number" name="team_number" id="team_number" class="form-control" min="1" max="255"
                       value="<?= esc($val('team_number')) ?>">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card card-outline card-primary">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-shuttle-van mr-2"></i>Équipe visiteuse</h3>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-8">
              <div class="form-group">
                <label for="opponent_club">Club <span class="text-danger">*</span></label>
                <input type="text" name="opponent_club" id="opponent_club" class="form-control" maxlength="100" required style="text-transform:uppercase"
                       placeholder="ex. B.C. GRÂCE-HOLLOGNE" value="<?= esc($val('opponent_club')) ?>">
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label for="opponent_number">Équipe n°</label>
                <input type="number" name="opponent_number" id="opponent_number" class="form-control" min="1" max="255"
                       placeholder="ex. 3" value="<?= esc($val('opponent_number')) ?>">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-user-friends mr-2"></i>Composition</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">Proposée d'après les joueurs inscrits des deux équipes, dans leur ordre : changez l'ordre, prenez une <b>réserve</b>
        ou marquez un joueur <b>absent</b> (forfait : une équipe peut jouer à deux). Les points à jouer suivent la position. Les matchs sont créés à l'enregistrement.</p>
      <?php
      $tablesById = [];
      foreach ($tables as $t) { $tablesById[(int) $t->id] = $t->name; }
      $sideInput = static function (string $side, int $i, array $row) {
          $v = static fn (string $f) => old($side . '.' . $i . '.' . $f, $row[$f] ?? '');
          ob_start(); ?>
          <div class="sb-side" data-side="<?= $side ?>" data-pos="<?= $i ?>">
            <label class="small text-muted mb-1"><?= $side === 'home' ? 'Notre joueur' : 'Joueur visiteur' ?></label>
            <div class="input-group input-group-sm">
              <select class="custom-select" data-sb-pick><option value="">— Choisir —</option></select>
              <input type="number" name="<?= $side ?>[<?= $i ?>][distance]" class="form-control" min="0" max="9999" style="max-width:80px" data-sb-points
                     value="<?= esc($v('distance') ?: '') ?>" title="Points à jouer">
              <div class="input-group-append"><span class="input-group-text">pts</span></div>
            </div>
            <div class="form-row mt-1" data-sb-manual>
              <div class="col-5"><input type="text" name="<?= $side ?>[<?= $i ?>][last_name]" class="form-control form-control-sm" placeholder="NOM" style="text-transform:uppercase" value="<?= esc($v('last_name')) ?>"></div>
              <div class="col-4"><input type="text" name="<?= $side ?>[<?= $i ?>][first_name]" class="form-control form-control-sm" placeholder="Prénom" value="<?= esc($v('first_name')) ?>"></div>
              <div class="col-3"><input type="text" name="<?= $side ?>[<?= $i ?>][license]" class="form-control form-control-sm" placeholder="Licence" value="<?= esc($v('license')) ?>"></div>
            </div>
            <input type="hidden" name="<?= $side ?>[<?= $i ?>][member_id]" value="<?= esc($v('member_id')) ?>">
            <input type="hidden" name="<?= $side ?>[<?= $i ?>][category_id]" value="<?= esc($v('category_id')) ?>">
            <input type="hidden" name="<?= $side ?>[<?= $i ?>][absent]" value="<?= $v('absent') ? '1' : '' ?>">
            <div class="text-danger small font-weight-bold mt-1" data-sb-absent-note hidden><i class="fas fa-user-slash mr-1"></i>Absent : match perdu par forfait</div>
          </div>
          <?php return (string) ob_get_clean();
      };
      ?>
      <?php for ($i = 1; $i <= 3; $i++): $row = $composition[$i] ?? ['home' => [], 'away' => []]; ?>
      <div class="sb-pos<?= $i > 1 ? ' border-top pt-3' : '' ?> mb-3" data-pos="<?= $i ?>">
        <div class="d-flex align-items-center mb-2">
          <strong class="mr-2">Match <?= $i ?></strong>
          <small class="text-muted" data-sb-range></small>
          <?php if (empty($row['locked'])): ?>
          <select name="table[<?= $i ?>]" class="custom-select custom-select-sm ml-auto" style="max-width:160px" title="Billard">
            <option value="">Billard : à choisir</option>
            <?php foreach ($tables as $t): ?>
            <option value="<?= $t->id ?>" <?= (int) old('table.' . $i, $row['table_id'] ?? 0) === (int) $t->id ? 'selected' : '' ?>><?= esc($t->name) ?></option>
            <?php endforeach; ?>
          </select>
          <?php endif; ?>
        </div>
        <?php if (!empty($row['locked'])): ?>
        <p class="mb-0 small"><i class="fas fa-lock text-muted mr-1"></i>
          <?= esc(trim($row['home']['last_name'] . ' ' . $row['home']['first_name'])) ?> – <?= esc(trim($row['away']['last_name'] . ' ' . $row['away']['first_name'])) ?> :
          match déjà commencé ou joué, il ne se modifie plus ici.</p>
        <?php else: ?>
        <div class="row">
          <div class="col-md-6 mb-2 mb-md-0"><?= $sideInput('home', $i, $row['home']) ?></div>
          <div class="col-md-6"><?= $sideInput('away', $i, $row['away']) ?></div>
        </div>
        <?php endif; ?>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <?php
  // Après une erreur de validation, une case décochée est absente de l'ancienne saisie : ne pas retomber sur la valeur par défaut
  $hasOld  = session('_ci_old_input') !== null;
  $checked = static fn (string $f, $current, $default = true) => ($hasOld ? old($f) : ($current ?? $default)) ? 'checked' : '';
  $box = static fn (string $name, string $id, string $state, string $title) => '<div class="custom-control custom-checkbox" title="' . $title . '"><input type="checkbox" class="custom-control-input" name="' . $name . '" id="' . $id . '" value="1" ' . $state . '><label class="custom-control-label" for="' . $id . '"><span class="d-md-none">' . $title . '</span></label></div>';
  ?>
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-paper-plane mr-2"></i>Envoi des résultats</h3>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-3">Envois automatiques : la <b>feuille de match</b> à la fin de chaque match, la <b>feuille récapitulative</b> (avec les feuilles de match)
        dès que la rencontre est complète (signatures comprises, ou <?= \App\Libraries\MatchResultMailer::AUTO_DELAY ?> minutes après le dernier match).
        L'adresse d'un joueur se remplit quand on le choisit ; vide, elle est recherchée au moment de l'envoi. Les joueurs sont en copie cachée.</p>
      <div class="sb-send">
        <div class="sb-send-head">Destinataire</div><div class="sb-send-head">Adresse e-mail</div>
        <div class="sb-send-head text-center">Feuille de match</div><div class="sb-send-head text-center">Feuille récapitulative</div>

        <div class="sb-send-player"><b>Club visité</b><br><span data-sb-club="home"></span></div>
        <div><input type="email" name="team_email" id="team_email" class="form-control form-control-sm" maxlength="150" value="<?= esc($val('team_email', $clubEmail)) ?>"></div>
        <div class="text-center text-muted">—</div>
        <div class="d-flex justify-content-center"><?= $box('recap_to_team', 'recap_to_team', $checked('recap_to_team', $encounter->recap_to_team ?? null), 'Feuille récapitulative') ?></div>

        <div class="sb-send-player"><b>Club visiteur</b><br><span data-sb-club="away"></span></div>
        <div><input type="email" name="opponent_email" id="opponent_email" class="form-control form-control-sm" maxlength="150" value="<?= esc($val('opponent_email')) ?>"
                    placeholder="retrouvé si le club est connu"></div>
        <div class="text-center text-muted">—</div>
        <div class="d-flex justify-content-center"><?= $box('recap_to_opponent', 'recap_to_opponent', $checked('recap_to_opponent', $encounter->recap_to_opponent ?? null), 'Feuille récapitulative') ?></div>

        <div class="sb-send-player"><b>Responsable CDR</b> <a href="<?= base_url('admin/scoreboard/settings') ?>" class="small ml-1" title="Adresse : Marquoir › Paramètres"><i class="fas fa-cog"></i></a><br><?= esc($cdr->name) ?></div>
        <div><input type="email" class="form-control form-control-sm" value="<?= esc($cdr->email) ?>" readonly title="Adresse définie dans Marquoir › Paramètres"></div>
        <div class="text-center text-muted small" title="Réglé dans chaque match (coché d'office en Coupe des Régions)">dans chaque match</div>
        <div class="d-flex justify-content-center"><?= $box('recap_to_cdr', 'recap_to_cdr', $checked('recap_to_cdr', $encounter->recap_to_cdr ?? null), 'Feuille récapitulative') ?></div>

        <?php for ($i = 1; $i <= 3; $i++): $row = $composition[$i] ?? ['home' => [], 'away' => []]; ?>
        <?php foreach (['home' => 'Notre joueur', 'away' => 'Joueur visiteur'] as $side => $sideLabel): $pl = $row[$side] ?? []; $f = $side . '[' . $i . ']'; $o = $side . '.' . $i . '.'; ?>
        <div class="sb-send-player" data-sb-send="<?= $side . $i ?>">Match <?= $i ?> · <?= $sideLabel ?><br><b data-sb-send-name><?= esc(trim(($pl['last_name'] ?? '') . ' ' . ($pl['first_name'] ?? ''))) ?></b></div>
        <?php if (!empty($row['locked'])): ?>
        <div class="small text-muted" data-sb-send="<?= $side . $i ?>"><?= esc($pl['email'] ?: 'adresse recherchée à l\'envoi') ?></div>
        <div class="text-center" data-sb-send="<?= $side . $i ?>"><i class="fas <?= $pl['send_sheet'] ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i></div>
        <div class="text-center" data-sb-send="<?= $side . $i ?>"><i class="fas <?= $pl['send_recap'] ? 'fa-check text-success' : 'fa-times text-muted' ?>"></i></div>
        <?php else: ?>
        <div data-sb-send="<?= $side . $i ?>">
          <div class="input-group input-group-sm">
            <input type="email" name="<?= $f ?>[email]" class="form-control" maxlength="150" placeholder="recherchée à l'envoi si vide" data-sb-email
                   value="<?= esc(old($o . 'email', $pl['email'] ?? '')) ?>">
            <div class="input-group-append"><button type="button" class="btn btn-outline-secondary" data-sb-email-lookup title="Rechercher l'adresse (membre du club, joueur déjà encodé, site de la FRBB)"><i class="fas fa-search"></i></button></div>
          </div>
          <small class="text-muted" data-sb-email-source></small>
        </div>
        <div class="d-flex justify-content-center" data-sb-send="<?= $side . $i ?>"><?= $box($f . '[send_sheet]', 'sheet_' . $side . $i, $checked($o . 'send_sheet', $pl['send_sheet'] ?? null), 'Feuille de match') ?></div>
        <div class="d-flex justify-content-center" data-sb-send="<?= $side . $i ?>"><?= $box($f . '[send_recap]', 'recap_' . $side . $i, $checked($o . 'send_recap', $pl['send_recap'] ?? null), 'Feuille récapitulative') ?></div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endfor; ?>
      </div>
      <style>
        .sb-send { display: grid; grid-template-columns: minmax(200px, 1.2fr) minmax(220px, 1.4fr) 120px 150px; gap: .45rem 1rem; align-items: center; }
        .sb-send-head { font-weight: 600; font-size: .8rem; color: #6c757d; text-transform: uppercase; letter-spacing: .03em; border-bottom: 1px solid #dee2e6; padding-bottom: .3rem; }
        .sb-send-player { font-size: .8rem; color: #6c757d; line-height: 1.25; }
        .sb-send-player b { color: #212529; font-size: .9rem; }
        .sb-send [hidden] { display: none !important; }
        @media (max-width: 767.98px) { .sb-send { grid-template-columns: 1fr 1fr; } .sb-send-head { display: none; } }
      </style>
    </div>
  </div>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title"><i class="fas fa-flag-checkered mr-2"></i>Clôture</h3>
    </div>
    <div class="card-body">
      <div class="form-group">
        <div class="custom-control custom-switch">
          <input type="checkbox" class="custom-control-input" name="recap_signature_required" id="recap_signature_required" value="1"
                 <?= old('recap_signature_required', $isEdit ? $encounter->recap_signature_required : '1') ? 'checked' : '' ?>>
          <label class="custom-control-label" for="recap_signature_required">Feuille récapitulative à signer</label>
        </div>
        <small class="text-muted">Coché : les deux capitaines signent la feuille récapitulative sur un Chromebook du marquoir, le jour de la rencontre, avant de pouvoir l'imprimer.</small>
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
<style>
  .sb-side [data-sb-manual] .form-control-sm { font-size: .8rem; }
  .sb-side.absent [data-sb-manual], .sb-side.absent [data-sb-points], .sb-side.absent .input-group-append { display: none; }
</style>
<script>
(function () {
    const sel = document.getElementById('team_id');
    const info = document.getElementById('teamInfo');
    const num = document.getElementById('team_number');
    const encSel = document.getElementById('frbbEncounter');
    const encInfo = document.getElementById('frbbInfo');
    const $ = (id) => document.getElementById(id);
    const url = <?= json_encode(base_url('admin/scoreboard/teams/frbb')) ?>;
    let data = null, awayId = null;

    const syncInfo = () => {
        const o = sel.options[sel.selectedIndex];
        info.textContent = o && o.value ? 'Discipline : ' + o.dataset.discipline + ' · Joueurs : ' + (o.dataset.roster || '—') : '';
    };

    // ---------------------------------------------------------------- composition
    const sides = () => document.querySelectorAll('.sb-side');
    const field = (box, f) => box.querySelector('[name$="[' + f + ']"]');
    const key = (l) => (l || '').toUpperCase().replace(/\s+/g, '');
    const plain = (v) => (v || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toUpperCase().replace(/[^A-Z]/g, '');
    const list = (box) => box.dataset.side === 'home' ? (data ? data.home : []) : ((data && awayId && data.teams[awayId]) ? data.teams[awayId].players : []);
    const rules = () => data && data.rules && data.rules.ranges ? data.rules : null;
    // Points à jouer selon la position (barème CDR : bornes par position, 2/3 en 3 bandes grand billard)
    const points = (raw, pos) => {
        if (raw === null || raw === undefined) return '';
        const r = rules();
        if (!r) return raw;
        const [lo, hi] = r.ranges[pos] || [raw, raw];
        const eff = Math.max(lo, Math.min(hi, raw));
        return r.two_thirds ? Math.ceil(eff * 2 / 3) : eff;
    };
    const rangeText = (pos) => {
        const r = rules();
        if (!r || !r.ranges[pos]) return '';
        const t = (v) => r.two_thirds ? Math.ceil(v * 2 / 3) : v;
        return '· points à jouer de ' + t(r.ranges[pos][0]) + ' à ' + t(r.ranges[pos][1]);
    };
    const setAbsent = (box, on) => {
        field(box, 'absent').value = on ? '1' : '';
        box.classList.toggle('absent', on);
        box.querySelector('[data-sb-absent-note]').hidden = !on;
        syncSend(box);
    };

    // Card « Envoi des résultats » : ligne du joueur à jour (nom, masquée s'il est absent), adresse recherchée
    const mailUrl = <?= json_encode(base_url('admin/scoreboard/players/email')) ?>;
    const sendRows = (box) => document.querySelectorAll('[data-sb-send="' + box.dataset.side + box.dataset.pos + '"]');
    const sendEmail = (box) => { const r = [...sendRows(box)].find((x) => x.querySelector('[data-sb-email]')); return r ? r.querySelector('[data-sb-email]') : null; };
    function syncSend(box) {
        const absent = !!field(box, 'absent').value, name = (field(box, 'last_name').value.toUpperCase() + ' ' + field(box, 'first_name').value).trim();
        sendRows(box).forEach((r) => {
            r.hidden = absent || !name;
            const b = r.querySelector('[data-sb-send-name]');
            if (b) b.textContent = name;
        });
    }
    const lookupEmail = (box, force) => {
        const input = sendEmail(box);
        if (!input || (!force && input.dataset.typed === '1')) return;
        const src = input.closest('[data-sb-send]').querySelector('[data-sb-email-source]');
        const q = new URLSearchParams({ license: field(box, 'license').value.trim(), member_id: field(box, 'member_id').value,
                                        last_name: field(box, 'last_name').value.trim(), first_name: field(box, 'first_name').value.trim() });
        if (!q.get('license') && !q.get('member_id')) { if (force) src.textContent = 'licence nécessaire pour la recherche'; return; }
        src.textContent = 'recherche…';
        fetch(mailUrl + '?' + q, { headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then((r) => r.json()).then((d) => {
            const other = d.fed_last && plain(d.fed_last) !== plain(field(box, 'last_name').value);
            if (d.email && !(other && d.source === 'frbb')) { input.value = d.email; input.dataset.typed = ''; src.textContent = 'trouvée : ' + d.label; }
            else { if (force || input.dataset.typed !== '1') input.value = ''; src.textContent = other ? 'licence d\'un autre joueur (' + d.fed_name + ') à la fédération' : 'aucune adresse trouvée'; }
        }).catch(() => { src.textContent = ''; });
    };
    document.querySelectorAll('[data-sb-email]').forEach((input) => {
        input.addEventListener('input', () => { input.dataset.typed = input.value.trim() ? '1' : ''; });
        if (input.value) input.dataset.typed = '1';
    });
    const label = (p) => p.last_name + ' ' + p.first_name + (p.category ? ' · ' + p.category : '') + (p.points !== null && p.points !== undefined ? ' (' + p.points + ')' : '');

    // Liste de la position : inscrits, réserves du club, autre joueur, absent ; sélection d'après ce qui est encodé
    const buildSelect = (box) => {
        const s = box.querySelector('[data-sb-pick]');
        const players = list(box);
        s.innerHTML = '<option value="">— Choisir —</option>';
        const groups = [['Joueurs inscrits', players.filter((p) => p.registered)], ['Réserves du club', players.filter((p) => !p.registered)]];
        groups.forEach(([title, items]) => {
            if (!items.length) return;
            const g = document.createElement('optgroup');
            g.label = title;
            items.forEach((p) => { const o = new Option(label(p), players.indexOf(p)); g.appendChild(o); });
            s.appendChild(g);
        });
        s.appendChild(new Option('Autre joueur (à taper)', '__other'));
        s.appendChild(new Option('Absent (forfait)', '__absent'));
        if (field(box, 'absent').value) { s.value = '__absent'; setAbsent(box, true); return; }
        const lic = key(field(box, 'license').value), last = plain(field(box, 'last_name').value);
        const found = players.findIndex((p) => (lic && key(p.license) === lic) || (!lic && last && plain(p.last_name) === last));
        s.value = found >= 0 ? String(found) : (last ? '__other' : '');
        setAbsent(box, false);
    };

    const pick = (box, value) => {
        const pos = +box.dataset.pos;
        setAbsent(box, value === '__absent');
        const clearMail = () => { const m = sendEmail(box); if (m) { m.value = ''; m.dataset.typed = ''; m.closest('[data-sb-send]').querySelector('[data-sb-email-source]').textContent = ''; } };
        if (value === '__absent' || value === '') {
            ['last_name', 'first_name', 'license', 'member_id', 'category_id', 'distance'].forEach((f) => { field(box, f).value = ''; });
            clearMail(); syncSend(box);
            return;
        }
        if (value === '__other') {
            ['last_name', 'first_name', 'license', 'member_id', 'category_id'].forEach((f) => { field(box, f).value = ''; });
            clearMail(); syncSend(box);
            field(box, 'last_name').focus();
            return;
        }
        const p = list(box)[+value];
        field(box, 'last_name').value = p.last_name;
        field(box, 'first_name').value = p.first_name;
        field(box, 'license').value = p.license || '';
        field(box, 'member_id').value = p.member_id || '';
        field(box, 'category_id').value = p.category_id || '';
        field(box, 'distance').value = points(p.points, pos);
        syncSend(box);
        const mail = sendEmail(box);
        if (mail) { mail.dataset.typed = ''; mail.value = ''; }
        lookupEmail(box, false);
    };

    // Proposition : le joueur inscrit n° X à la position X (si la position est vide, ou toujours si force)
    const propose = (side, force) => {
        document.querySelectorAll('.sb-side[data-side="' + side + '"]').forEach((box) => {
            const empty = !field(box, 'last_name').value && !field(box, 'absent').value;
            if (!force && !empty) return;
            const players = list(box);
            const idx = players.findIndex((p) => p.registered === +box.dataset.pos);
            const s = box.querySelector('[data-sb-pick]');
            s.value = idx >= 0 ? String(idx) : '';
            pick(box, s.value);
        });
    };

    const refreshRanges = () => document.querySelectorAll('.sb-pos').forEach((row) => {
        const r = row.querySelector('[data-sb-range]');
        if (r) r.textContent = rangeText(+row.dataset.pos);
    });

    sides().forEach((box) => {
        syncSend(box);
        ['last_name', 'first_name'].forEach((f) => field(box, f).addEventListener('input', () => syncSend(box)));
        field(box, 'license').addEventListener('change', () => lookupEmail(box, false));
        const look = [...sendRows(box)].map((r) => r.querySelector('[data-sb-email-lookup]')).find(Boolean);
        if (look) look.addEventListener('click', () => lookupEmail(box, true));
        box.querySelector('[data-sb-pick]').addEventListener('change', (ev) => pick(box, ev.target.value));
        field(box, 'last_name').addEventListener('input', () => {
            // Nom retouché à la main : ce n'est plus le joueur de la liste
            const s = box.querySelector('[data-sb-pick]');
            if (s.value !== '__other') { s.value = '__other'; field(box, 'member_id').value = ''; field(box, 'category_id').value = ''; }
        });
    });

    // ---------------------------------------------------------------- calendrier CDR
    const frDate = (d, t) => {
        const days = ['dim.', 'lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.'];
        const dt = new Date(d + 'T12:00:00');
        return days[dt.getDay()] + ' ' + d.slice(8, 10) + '/' + d.slice(5, 7) + '/' + d.slice(0, 4) + (t ? ' ' + t : '');
    };
    const chooseEncounter = (e, fill) => {
        awayId = e ? e.away : null;
        const t = e && data.teams[e.away];
        if (fill && e) {
            $('code').value = e.code;
            if (e.date) $('scheduled_date').value = e.date;
            if (e.time) $('scheduled_time').value = e.time;
            if (t) {
                $('opponent_club').value = t.club;
                $('opponent_number').value = t.number || '';
                if (t.email) $('opponent_email').value = t.email;
            }
        }
        document.querySelectorAll('.sb-side[data-side="away"]').forEach(buildSelect);
        if (fill && e) propose('away', true);
    };

    const load = (initial) => {
        const teamId = sel.value;
        encSel.innerHTML = '<option value="">— Choisissez d\'abord notre équipe —</option>';
        encSel.disabled = true;
        data = null; awayId = null;
        if (!teamId) { sides().forEach(buildSelect); refreshRanges(); return; }
        encInfo.textContent = 'Chargement du calendrier…';
        fetch(url + '?team_id=' + encodeURIComponent(teamId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((r) => r.json())
            .then((d) => {
                data = d;
                if (!d.available) {
                    encSel.innerHTML = '<option value="">Calendrier indisponible</option>';
                    encInfo.textContent = "Site FRBB Liège-Lux injoignable ou équipe non reliée : nos joueurs inscrits seulement, l'équipe visiteuse se tape à la main.";
                } else {
                    const todo = d.encounters.filter((e) => !e.played), done = d.encounters.filter((e) => e.played);
                    encSel.innerHTML = '<option value="">' + (d.encounters.length ? '— Choisir la rencontre —' : 'Aucune rencontre à domicile') + '</option>';
                    // À jouer d'abord ; celles dont le résultat est déjà encodé sur le site FRBB restent proposées
                    [['À jouer', todo], ['Résultat déjà encodé sur le site FRBB', done.reverse()]].forEach(([title, items]) => {
                        if (!items.length) return;
                        const g = document.createElement('optgroup');
                        g.label = title;
                        items.forEach((e) => {
                            const t = d.teams[e.away];
                            g.appendChild(new Option(e.code + ' · ' + frDate(e.date, e.time) + ' · ' + (t ? t.club + ' ' + (t.number || '') : '?'), d.encounters.indexOf(e)));
                        });
                        encSel.appendChild(g);
                    });
                    encSel.disabled = !d.encounters.length;
                    encInfo.textContent = d.encounters.length
                        ? "Remplit le n°, la date, l'équipe visiteuse et propose la composition (site FRBB Liège-Lux : " + d.frbb_team + ')' + (todo.length ? '.' : ' — aucune rencontre à jouer.')
                        : 'Aucune rencontre à domicile pour ' + d.frbb_team + ' sur le site FRBB Liège-Lux : vérifiez l\'« ID équipe sur frbb-liege-lux.be » de notre équipe (Direction sportive › Coupe des Régions › Équipes).';
                    // Rencontre déjà encodée (modification, ou retour après une erreur) : on la retrouve par son n°
                    const code = key($('code').value);
                    const cur = code ? d.encounters.findIndex((e) => key(e.code) === code) : -1;
                    if (cur >= 0) { encSel.value = String(cur); }
                    chooseEncounter(cur >= 0 ? d.encounters[cur] : null, false);
                }
                document.querySelectorAll('.sb-side[data-side="home"]').forEach(buildSelect);
                if (!d.available) document.querySelectorAll('.sb-side[data-side="away"]').forEach(buildSelect);
                propose('home', false);
                refreshRanges();
            })
            .catch(() => { encInfo.textContent = 'Calendrier indisponible.'; sides().forEach(buildSelect); });
    };

    encSel.addEventListener('change', () => chooseEncounter(encSel.value === '' ? null : data.encounters[+encSel.value], true));
    sel.addEventListener('change', function () {
        syncInfo();
        // « R.B.C. Disonais 1 » → équipe n° 1
        const m = (sel.options[sel.selectedIndex].text.split(' — ')[0] || '').trim().match(/(\d+)$/);
        if (m) num.value = m[1];
        // Autre équipe : nos joueurs changent
        document.querySelectorAll('.sb-side[data-side="home"]').forEach((box) => { box.querySelector('[data-sb-pick]').value = ''; pick(box, ''); });
        load(false);
    });
    const clubLabels = () => {
        document.querySelector('[data-sb-club="home"]').textContent = ($('team_club').value + ' ' + ($('team_number').value || '')).trim().toUpperCase();
        document.querySelector('[data-sb-club="away"]').textContent = ($('opponent_club').value + ' ' + ($('opponent_number').value || '')).trim().toUpperCase();
    };
    ['team_club', 'team_number', 'opponent_club', 'opponent_number'].forEach((id) => $(id).addEventListener('input', clubLabels));
    encSel.addEventListener('change', clubLabels);
    sel.addEventListener('change', clubLabels);
    clubLabels();
    syncInfo();
    load(true);

    // E-mail du club visiteur déjà connu (rencontres précédentes contre ce club)
    const knownEmails = <?= json_encode($knownEmails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    const norm = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]/g, '');
    const opp = document.getElementById('opponent_club');
    const oppMail = document.getElementById('opponent_email');
    opp.addEventListener('change', function () {
        const mail = knownEmails[norm(opp.value)];
        if (mail && !oppMail.value) oppMail.value = mail;
    });
})();
</script>
<?= $this->endSection() ?>
