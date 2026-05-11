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

      <!-- Joueurs -->
      <?php
        $players = [
            ['id' => $team->p1_id, 'last' => $team->p1_last, 'first' => $team->p1_first, 'photo' => $team->p1_photo],
            ['id' => $team->p2_id, 'last' => $team->p2_last, 'first' => $team->p2_first, 'photo' => $team->p2_photo],
            ['id' => $team->p3_id, 'last' => $team->p3_last, 'first' => $team->p3_first, 'photo' => $team->p3_photo],
        ];
      ?>

      <?php foreach ($players as $i => $p): if (!$p['id']) continue; ?>
      <div class="col-sm-6 col-md-4 mb-30">
        <a href="<?= base_url('club/membres/' . $p['id']) ?>" class="cup-player-card">
          <div class="cup-player-thumb">
            <?php if ($p['photo']): ?>
              <img src="<?= base_url('uploads/members/' . $p['photo']) ?>"
                   alt="<?= esc($p['last'] . ' ' . $p['first']) ?>">
            <?php else: ?>
              <div class="cup-player-no-photo"><i class="fas fa-user"></i></div>
            <?php endif; ?>
          </div>
          <div class="cup-player-info">
            <div class="cup-player-num">Joueur <?= $i + 1 ?></div>
            <div class="cup-player-name">
              <?= esc($p['last']) ?><br>
              <span><?= esc($p['first']) ?></span>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>

    </div>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
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

.cup-team-photo {
    max-width: 680px;
    width: 100%;
    border-radius: 8px;
    box-shadow: 0 4px 18px rgba(0,0,0,.12);
}

.cup-player-card {
    display: block;
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: box-shadow .25s;
}
.cup-player-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
    color: inherit;
}
.cup-player-thumb {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f0f0f0;
}
.cup-player-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .35s;
}
.cup-player-card:hover .cup-player-thumb img { transform: scale(1.04); }
.cup-player-no-photo {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: #ccc;
}
.cup-player-info {
    padding: 14px 16px;
    text-align: center;
}
.cup-player-num {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #84252B;
    font-weight: 700;
    margin-bottom: 4px;
}
.cup-player-name {
    font-weight: 700;
    font-size: 1rem;
    color: #333;
    line-height: 1.35;
}
.cup-player-name span { font-weight: 400; }
</style>
<?= $this->endSection() ?>
