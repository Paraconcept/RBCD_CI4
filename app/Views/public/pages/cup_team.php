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
          <div class="cup-player-photo-wrap">
            <?php if ($p['photo']): ?>
              <img src="<?= base_url('uploads/members/' . $p['photo']) ?>"
                   alt="<?= esc($p['last'] . ' ' . $p['first']) ?>">
            <?php else: ?>
              <i class="fas fa-user cup-player-no-photo"></i>
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
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 24px 16px;
    border: 1px solid #eee;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: box-shadow .2s, border-color .2s;
    height: 100%;
}
.cup-player-card:hover {
    box-shadow: 0 4px 18px rgba(0,0,0,.1);
    border-color: #84252B;
    color: inherit;
}
.cup-player-photo-wrap {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid #dee2e6;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: border-color .25s;
    flex-shrink: 0;
}
.cup-player-card:hover .cup-player-photo-wrap { border-color: #84252B; }
.cup-player-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.cup-player-no-photo { font-size: 3rem; color: #bbb; }
.cup-player-num {
    font-size: .75rem;
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
