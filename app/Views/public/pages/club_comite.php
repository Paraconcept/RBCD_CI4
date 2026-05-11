<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-10">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Notre Comité</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Les membres du comité dirigeant du Royal Billard Club Disonais pour la saison en cours.
          </p>
        </div>
      </div>
    </div>

    <?php
    $feminineRoles = [
        'Président'                 => 'Présidente',
        'Vice-Président'            => 'Vice-Présidente',
        'Secrétaire Adjoint'        => 'Secrétaire Adjointe',
        'Directeur Sportif'         => 'Directrice Sportive',
        'Directeur Sportif Adjoint' => 'Directrice Sportive Adjointe',
        'Trésorier'                 => 'Trésorière',
        'Trésorier Adjoint'         => 'Trésorière Adjointe',
    ];
    ?>

    <div class="comite-wrapper">
      <div class="row justify-content-center">

      <?php foreach ($members as $m):
        $isFemale = ($m->gender ?? '') === 'F';
        $roles = $rolesMap[$m->id] ?? [];
        if ($isFemale) {
            $roles = array_map(fn($r) => $feminineRoles[$r] ?? $r, $roles);
        }
        $photoUrl = $m->photo
            ? base_url('uploads/members/' . esc($m->photo))
            : null;
      ?>
      <div class="col-sm-6 col-md-4 text-center mb-30">
        <div class="team-members">
          <div class="team-thumb">
            <?php if ($photoUrl): ?>
              <img class="img-fullwidth" alt="<?= esc($m->first_name . ' ' . $m->last_name) ?>"
                   src="<?= $photoUrl ?>">
            <?php else: ?>
              <div class="team-thumb-placeholder"><i class="fas fa-user"></i></div>
            <?php endif; ?>
          </div>
          <div class="team-bottom-part text-center">
            <h4>
              <?php if ($m->member_id): ?>
                <a href="<?= base_url('club/membres/' . $m->member_id) ?>"><?= esc($m->first_name . ' ' . $m->last_name) ?></a>
              <?php else: ?>
                <?= esc($m->first_name . ' ' . $m->last_name) ?>
              <?php endif; ?>
            </h4>
            <p class="member-roles"><?= esc(implode(' · ', $roles)) ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      </div><!-- /.row -->
    </div><!-- /.comite-wrapper -->

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.team-members {
    border: 1px solid #e8e8e8;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
    transition: box-shadow .25s;
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
.team-bottom-part {
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
.team-bottom-part h4 a:hover {
    color: #84252B;
}
.team-bottom-part .member-roles {
    font-size: .9rem;
    font-weight: 600;
    color: #84252B;
    margin: 0;
}
/* Conteneur limité à 3/5 de la largeur → chaque carte col-4 = 20 % du container */
@media (min-width: 992px) {
    .comite-wrapper {
        max-width: 80%;
        margin: 0 auto;
    }
}
</style>
<?= $this->endSection() ?>
