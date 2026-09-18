<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<!-- Suggestion mode paysage (smartphone) -->
<div id="cdrLandscapeHint" class="cdr-landscape-hint" role="alert">
  <i class="fas fa-mobile-alt cdr-landscape-hint-icon"></i>
  <span>Pour un meilleur affichage, tournez votre écran en mode paysage.</span>
  <button type="button" class="cdr-landscape-hint-close" aria-label="Fermer">&times;</button>
</div>

<section class="section-padding">
  <div class="container">

    <!-- CDR, c'est quoi ? -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-10">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-left">La CDR, c'est quoi ?</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-left mt-20">
            Littéralement : <strong><span class="cdr-format-label">C</span>oupe <span class="cdr-format-label">D</span>es <span class="cdr-format-label">R</span>égions</strong>. 
            Il s'agit d'un tournoi interclub régional qui se joue par équipe de 3 joueurs.
          <br>
            Les différents modes de jeu proposés sont :<br>
            &bull; <strong><u>Petit format</u></strong> : libre, cadre 38/2, 3 bandes <br>
            &bull; <strong><u>Grand format</u></strong> : 3 bandes
          </p>
          <p class="heading-description text-left mt-10">
            Toutes les équipes se rencontrent en matchs aller et retour, totalisant des points tout au long de la saison sportive. 
            En fin de saison, l'équipe gagnante disputera la <strong>finale nationale de la Coupe des Régions</strong> avec les vainqueurs des autres régions participantes.
          </p>
        </div>
      </div>
    </div>

    <!-- Séparateur -->
    <div class="row mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center"><?= esc($team->name) ?></h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-10">
            <span class="cup-badge-mode"><?= esc($team->game_mode) ?></span>
            <span class="cup-badge-season">Saison <?= esc($team->season) ?></span>
          </p>
        </div>
      </div>
    </div>

    <!-- Joueurs -->
    <?php
      $players = [
          ['id' => $team->p1_id, 'last' => $team->p1_last, 'first' => $team->p1_first, 'photo' => $team->p1_photo, 'gender' => $team->p1_gender],
          ['id' => $team->p2_id, 'last' => $team->p2_last, 'first' => $team->p2_first, 'photo' => $team->p2_photo, 'gender' => $team->p2_gender],
          ['id' => $team->p3_id, 'last' => $team->p3_last, 'first' => $team->p3_first, 'photo' => $team->p3_photo, 'gender' => $team->p3_gender],
      ];
    ?>
    <div class="cup-players-wrapper">
      <div class="row justify-content-center">

      <?php foreach ($players as $i => $p): if (!$p['id']) continue; ?>
      <div class="col-sm-6 col-md-4 mb-30">
        <div class="team-members">
          <div class="team-thumb">
            <?php if ($p['photo']): ?>
              <img class="img-fullwidth"
                   src="<?= base_url('uploads/members/' . $p['photo']) ?>"
                   alt="<?= esc($p['last'] . ' ' . $p['first']) ?>">
            <?php else: ?>
              <div class="team-thumb-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
          </div>
          <div class="team-bottom-part text-center">
            <h4>
              <a href="<?= base_url('club/membres/' . $p['id']) ?>">
                <?= esc($p['first'] . ' ' . $p['last']) ?>
              </a>
            </h4>
            <p class="member-roles"><?= $p['gender'] === 'F' ? 'Joueuse' : 'Joueur' ?> <?= $i + 1 ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      </div>
    </div>

    <?php if ($team->frbb_team_id): ?>
    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

    <!-- Calendrier des rencontres (via l'API frbb-liege-lux.be) -->
    <div class="row">
      <div class="col-lg-10 mx-auto">
        <h4 class="font-weight-700 mb-20 text-center">Calendrier des rencontres / Résultats</h4>

        <?php if ($frbbCalendar === null): ?>
          <div class="alert alert-info text-center mb-0">
            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
            Calendrier temporairement indisponible.
          </div>
        <?php elseif (empty($frbbCalendar['calendar'])): ?>
          <div class="alert alert-info text-center mb-0">
            <i class="fas fa-info-circle fa-2x mb-2 d-block"></i>
            Aucune rencontre programmée pour cette saison.
          </div>
        <?php else: ?>
          <?php foreach (['aller' => 'Match aller', 'retour' => 'Match retour'] as $phaseKey => $phaseLabel): ?>
            <?php foreach (($frbbCalendar['calendar'][$phaseKey] ?? []) as $tourNum => $tourMatchs): ?>
            <ul class="comp-accordion">
              <?php foreach ($tourMatchs as $m): ?>
              <?php
                $accId = 'cdr-match-' . $m['id'];
                $homeOutcomeClass = $awayOutcomeClass = '';
                if ($m['has_result']) {
                    if ($m['home_score'] > $m['away_score'])      { $homeOutcomeClass = 'cdr-duel-win';  $awayOutcomeClass = 'cdr-duel-lose'; }
                    elseif ($m['home_score'] < $m['away_score'])  { $homeOutcomeClass = 'cdr-duel-lose'; $awayOutcomeClass = 'cdr-duel-win';  }
                    else                                          { $homeOutcomeClass = $awayOutcomeClass = 'cdr-duel-draw'; }
                }
              ?>
              <li class="accordion block">
                <button class="comp-accordion-btn" data-id="<?= $accId ?>">
                  <span class="cdr-match-tour">T<?= (int) $tourNum ?></span>
                  <span class="cdr-match-date">
                    <?= $m['match_date'] ? date('d/m/Y', strtotime($m['match_date'])) : 'Date à définir' ?>
                  </span>
                  <span class="cdr-match-composition">
                    <span class="cdr-match-home <?= $homeOutcomeClass ?> <?= $m['is_home'] ? 'cdr-match-us' : '' ?>"><?= esc($m['home_name'] ?? '?') ?></span>
                    <span class="cdr-match-score">
                      <?= $m['has_result'] ? ((int) $m['home_score'] . ' - ' . (int) $m['away_score']) : '—' ?>
                    </span>
                    <span class="cdr-match-away <?= $awayOutcomeClass ?> <?= !$m['is_home'] ? 'cdr-match-us' : '' ?>"><?= esc($m['away_name'] ?? '?') ?></span>
                  </span>
                  <?php if ($m['has_result']): ?>
                  <i class="fas fa-chart-bar" style="color:#84252B;"></i>
                  <?php endif; ?>
                  <i class="fas fa-chevron-down comp-chevron"></i>
                </button>
                <div class="comp-accordion-body" id="<?= $accId ?>">
                  <?php if (empty($m['duels'])): ?>
                    <p class="text-muted mb-0">Résultats pas encore encodés.</p>
                  <?php else: ?>
                  <div class="table-responsive">
                  <table class="cdr-duel-table">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Domicile</th>
                        <th>Score</th>
                        <th></th>
                        <th>Score</th>
                        <th>Extérieur</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $resClass = fn(?string $r) => $r === 'V' ? 'cdr-duel-win' : ($r === 'D' ? 'cdr-duel-lose' : ($r === 'N' ? 'cdr-duel-draw' : ($r === 'F' ? 'cdr-duel-forfait' : '')));
                      $realise  = fn(?int $car, ?int $rep, ?float $moy, ?string $res) => ($car === null && $rep === null)
                          ? ($res === 'F' ? 'FF' : '-')
                          : ($car ?? '-') . ' / ' . ($rep ?? '-') . ' = ' . ($moy !== null ? number_format($moy, 3, ',', ' ') : '-');
                      $ptsLabel = function (?int $pts, ?int $orig, bool $forced): string {
                          if ($pts === null) return '';
                          return $forced ? "($orig->$pts)" : "($pts)";
                      };
                      ?>
                      <?php foreach ($m['duels'] as $d): ?>
                      <tr>
                        <td class="text-center"><?= $d['position'] ?></td>
                        <td class="<?= $resClass($d['home_res']) ?>">
                          <div class="cdr-duel-home-name">
                            <span><?= esc($d['home_name']) ?></span>
                            <span class="cdr-duel-pts"><?= $ptsLabel($d['home_pts'], $d['home_pts_orig'], $d['home_forced']) ?></span>
                          </div>
                        </td>
                        <td class="text-center"><?= $realise($d['home_car'], $d['home_rep'], $d['home_moy'], $d['home_res']) ?></td>
                        <td class="text-center">vs</td>
                        <td class="text-center"><?= $realise($d['away_car'], $d['away_rep'], $d['away_moy'], $d['away_res']) ?></td>
                        <td class="<?= $resClass($d['away_res']) ?>">
                          <div class="cdr-duel-away-name">
                            <span class="cdr-duel-pts"><?= $ptsLabel($d['away_pts'], $d['away_pts_orig'], $d['away_forced']) ?></span>
                            <span><?= esc($d['away_name']) ?></span>
                          </div>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  </div>
                  <?php endif; ?>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($sportResults)): ?>
    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

    <!-- Palmarès de l'équipe -->
    <?= view('public/pages/_sport_results_block', ['sportResults' => $sportResults, 'teamName' => $team->name, 'season' => $team->season]) ?>
    <?php endif; ?>

    <!-- Séparateur -->
    <div class="row mt-20 mb-10">
      <div class="separator">
        <img src="<?= base_url('assets/images/billiard-chalk.png') ?>"
             alt="Séparateur Craie de billard"
             style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Labels format de jeu dans la description */
.cdr-format-label {
    font-weight: 700;
    color: #84252B;
}
/* Badges heading */
.cup-badge-mode,
.cup-badge-season {
    display: inline-block;
    font-size: .82rem;
    font-weight: 600;
    border-radius: 20px;
    padding: 3px 14px;
    margin: 0 4px;
}
.cup-badge-mode   { background: #84252B; color: #fff; }
.cup-badge-season { background: #f0f0f0; color: #555; }

/* Cartes joueurs — style identique au comité */
.team-members {
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: box-shadow .25s;
    width: 100%;
    display: flex;
    flex-direction: column;
}
.team-members:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
}
.team-thumb {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f0f0f0;
}
.team-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .35s;
}
.team-members:hover .team-thumb img {
    transform: scale(1.04);
}
.team-thumb-placeholder {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5rem;
    color: #ccc;
}
.cup-players-wrapper .col-sm-6,
.cup-players-wrapper .col-md-4 {
    display: flex;
}
.team-bottom-part {
    flex: 1;
    border-top: 3px solid #84252B;
    background: #fafafa;
    padding: 18px 16px 20px;
}
.team-bottom-part h4 {
    font-size: 1rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #333;
    margin: 0 0 6px;
}
.team-bottom-part h4 a {
    color: #333;
    text-decoration: none;
}
.team-bottom-part h4 a:hover { color: #84252B; }
.team-bottom-part .member-roles {
    font-size: .9rem;
    font-weight: 600;
    color: #84252B;
    margin: 0;
}
@media (min-width: 992px) {
    .cup-players-wrapper {
        max-width: 80%;
        margin: 0 auto;
    }
}

/* ── Calendrier des rencontres — accordéon (repris de frbb-liege-lux.be, couleurs RBCD) ── */
.cdr-match-tour { flex-shrink: 0; font-weight: 700; color: #84252B; font-size: .9em; margin-right: 14px; }
.comp-accordion { list-style: none; padding: 0; margin: 0; }
.comp-accordion li {
    border: 1px solid #e5e5e5; border-radius: 4px;
    margin-bottom: 4px; overflow: hidden;
    transition: box-shadow .15s;
}
.comp-accordion li:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); }
.comp-accordion-btn {
    width: 100%; text-align: left; background: #fff;
    border: none; padding: 10px 14px; font-size: 1rem;
    font-weight: 600; color: #333; cursor: pointer;
    display: flex; align-items: center; gap: 8px;
    transition: background .15s;
}
.comp-accordion-btn:hover { background: #fdf3f4; }
.comp-accordion-btn .comp-chevron {
    margin-left: auto; font-size: .7rem; color: #84252B;
    transition: transform .2s; flex-shrink: 0;
}
.comp-accordion-btn.open { background: #fdf3f4; }
.comp-accordion-btn.open .comp-chevron { transform: rotate(180deg); }
.comp-accordion-body {
    display: none; padding: 14px 16px 16px;
    background: #fafafa; border-top: 1px solid #f0f0f0;
    font-size: .82rem; color: #555;
}
.cdr-match-date { flex-shrink: 0; font-weight: 400; font-size: .82em; color: #888; }
.cdr-match-composition {
    display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
    gap: 12px; flex: 1; margin: 0 24px; min-width: 0;
    font-weight: 600; color: #333;
}
.cdr-match-composition .cdr-match-home {
    text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cdr-match-composition .cdr-match-away {
    text-align: left; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.cdr-match-composition .cdr-match-us { color: #84252B; }
.cdr-match-score { font-weight: 700; color: #171717; min-width: 42px; text-align: center; }

.cdr-duel-table { width: 100%; font-size: .9rem; border-collapse: collapse; white-space: nowrap; }
.cdr-duel-table th {
    font-size: .78rem; text-transform: uppercase; letter-spacing: .4px;
    background: #84252B; color: #fff; padding: 6px 8px; text-align: center;
}
.cdr-duel-table td { padding: 6px 8px; border-top: 1px solid #f0f0f0; }
.cdr-duel-table td.text-center { color: #333; }
.cdr-duel-table .cdr-duel-home-name,
.cdr-duel-table .cdr-duel-away-name {
    display: flex; align-items: center; justify-content: space-between; gap: 6px;
}
.cdr-duel-pts { color: #888; font-weight: 400; font-size: .9em; }
.cdr-duel-win  { color: #198754; font-weight: 700; }
.cdr-duel-lose { color: #dc3545; font-weight: 700; }
.cdr-duel-draw { color: #0d6efd; font-weight: 700; }
.cdr-duel-forfait { color: #fd7e14; font-weight: 700; }

/* ── Suggestion mode paysage (smartphone en portrait) ── */
.cdr-landscape-hint {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1050;
    background: #84252B;
    color: #fff;
    padding: 10px 40px 10px 16px;
    font-size: .85rem;
    text-align: center;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
}
.cdr-landscape-hint-icon {
    font-size: 1.2rem;
    animation: cdr-rotate-hint 1.6s ease-in-out infinite;
}
@keyframes cdr-rotate-hint {
    0%, 100% { transform: rotate(0deg); }
    50% { transform: rotate(-90deg); }
}
.cdr-landscape-hint-close {
    position: absolute; top: 6px; right: 10px;
    background: none; border: none; color: #fff;
    font-size: 1.3rem; line-height: 1; cursor: pointer;
    padding: 4px 8px;
}
@media (max-width: 767px) and (orientation: portrait) {
    .cdr-landscape-hint.show { display: flex; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.comp-accordion-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var isOpen = this.classList.contains('open');
        document.querySelectorAll('.comp-accordion-btn').forEach(function(b) {
            b.classList.remove('open');
            document.getElementById(b.dataset.id).style.display = 'none';
        });
        if (!isOpen) {
            this.classList.add('open');
            document.getElementById(this.dataset.id).style.display = 'block';
        }
    });
});

(function() {
    var hint = document.getElementById('cdrLandscapeHint');
    if (!hint) return;
    var closeBtn = hint.querySelector('.cdr-landscape-hint-close');
    var dismissed = false;

    function update() {
        if (dismissed) { hint.classList.remove('show'); return; }
        var isPortraitSmartphone = window.matchMedia('(max-width: 767px) and (orientation: portrait)').matches;
        hint.classList.toggle('show', isPortraitSmartphone);
    }

    closeBtn.addEventListener('click', function() {
        dismissed = true;
        hint.classList.remove('show');
    });

    window.addEventListener('resize', update);
    window.addEventListener('orientationchange', update);
    update();
})();
</script>
<?= $this->endSection() ?>
