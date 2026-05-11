<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center"><?= esc($team->name) ?></h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-10">
            <span class="cup-badge-mode"><?= esc($team->game_mode) ?></span>
            <span class="cup-badge-season"><?= esc($team->season) ?></span>
          </p>
        </div>
      </div>
    </div>

    <div class="row justify-content-center">

      <!-- Photo d'équipe -->
      <?php if ($team->photo): ?>
      <div class="col-12 text-center mb-40">
        <img src="<?= base_url('uploads/cup_teams/' . $team->photo) ?>"
             alt="<?= esc($team->name) ?>"
             class="cup-team-photo">
      </div>
      <?php endif; ?>

    </div><!-- /heading row -->

    <!-- Joueurs -->
    <?php
      $players = [
          ['id' => $team->p1_id, 'last' => $team->p1_last, 'first' => $team->p1_first, 'photo' => $team->p1_photo],
          ['id' => $team->p2_id, 'last' => $team->p2_last, 'first' => $team->p2_first, 'photo' => $team->p2_photo],
          ['id' => $team->p3_id, 'last' => $team->p3_last, 'first' => $team->p3_first, 'photo' => $team->p3_photo],
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
            <p class="member-roles">Joueur <?= $i + 1 ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      </div>
    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
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

/* Photo d'équipe */
.cup-team-photo {
    max-width: 680px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 4px 18px rgba(0,0,0,.12);
}

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
</style>
<?= $this->endSection() ?>
