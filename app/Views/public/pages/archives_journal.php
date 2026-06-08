<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center">Partie Libre</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-20">
            Dans un souci de communication et de transparence vis-à-vis de nos membres, nous rédigeons un compte rendu de nos réunions de comité.
          </p>

          <?php if ($editor): ?>
          <?php
            $editorName  = esc($editor->first_name . ' ' . $editor->last_name);
            $editorPhoto = $editor->photo ? base_url('uploads/members/' . $editor->photo) : null;
            $editorUrl   = $editor->member_id ? base_url('club/membres/' . $editor->member_id) : null;
          ?>
          <div class="journal-editor-card mt-20">
            <span class="journal-editor-label">À la rédaction :</span>
            <?php $tag = $editorUrl ? 'a' : 'span'; ?>
            <<?= $tag ?> <?= $editorUrl ? 'href="' . $editorUrl . '"' : '' ?> class="journal-editor-member">
              <div class="member-photo-wrap">
                <?php if ($editorPhoto): ?>
                  <img src="<?= $editorPhoto ?>" alt="<?= $editorName ?>">
                <?php else: ?>
                  <i class="fas fa-user member-no-photo"></i>
                <?php endif; ?>
              </div>
              <div class="member-info">
                <div class="member-name">
                  <?= esc($editor->last_name) ?><br>
                  <span style="font-weight:400"><?= esc($editor->first_name) ?></span>
                </div>
                <?php // <div class="member-ranking">PR &amp; Communication</div> ?>
              </div>
            </<?= $tag ?>>
          </div>
          <?php endif; ?>

          <p class="heading-description text-center mt-20">
            Retrouvez ici tous les numéros disponibles au téléchargement.
          </p>
        </div>
      </div>
    </div>

    <?php if (!$isLoggedIn): ?>
    <!-- Invitation à se connecter -->
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="journal-login-box">
          <i class="fas fa-lock journal-login-icon"></i>
          <h5 class="journal-login-title">Accès réservé aux membres</h5>
          <p class="journal-login-text">
            Connectez-vous avec votre compte membre pour accéder aux numéros disponibles au téléchargement.
          </p>
          <a href="<?= base_url('connexion') ?>?redirect=<?= urlencode(current_url()) ?>"
             class="btn-journal-login">
            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
          </a>
        </div>
      </div>
    </div>

    <?php elseif (empty($byYear)): ?>
    <div class="row">
      <div class="col-md-8 offset-md-2 text-center text-muted py-40">
        <i class="fas fa-newspaper fa-3x mb-20" style="color:#ccc"></i>
        <p>Aucun numéro disponible pour le moment.</p>
      </div>
    </div>

    <?php else: ?>
    <div class="row">
      <div class="col-md-8 offset-md-2">

        <div class="accordion tm-accordion accordion-classic accordion-theme-colored1" id="journalAccordion">

          <?php $yearIndex = 0; foreach ($byYear as $year => $issues): ?>
          <?php $yearId = 'year-' . $year; $isFirst = ($yearIndex === 0); $yearIndex++; ?>

          <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?= $yearId ?>">
              <button class="accordion-button <?= $isFirst ? '' : 'collapsed' ?>"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#<?= $yearId ?>"
                      aria-expanded="<?= $isFirst ? 'true' : 'false' ?>"
                      aria-controls="<?= $yearId ?>">
                <i class="fas fa-calendar-alt me-3" style="opacity:.7"></i>
                <strong><?= esc($year) ?></strong>
                <span class="journal-count ms-3"><?= count($issues) ?> numéro<?= count($issues) > 1 ? 's' : '' ?></span>
              </button>
            </h2>
            <div id="<?= $yearId ?>"
                 class="accordion-collapse collapse <?= $isFirst ? 'show' : '' ?>"
                 aria-labelledby="heading-<?= $yearId ?>"
                 data-bs-parent="#journalAccordion">
              <div class="accordion-body py-0 px-0">
                <ul class="journal-list">
                  <?php foreach ($issues as $issue): ?>
                  <li class="journal-item">
                    <div class="journal-item-info">
                      <i class="fas fa-newspaper journal-icon"></i>
                      <span class="journal-item-title"><?= esc($issue->title) ?></span>
                      <?php if ($issue->description): ?>
                        <p class="journal-item-desc"><?= esc($issue->description) ?></p>
                      <?php endif; ?>
                    </div>
                    <?php if ($issue->file_path): ?>
                    <a href="<?= base_url('uploads/PDF/PartieLibre/' . $issue->file_path) ?>"
                       class="btn-journal-dl" target="_blank" rel="noopener">
                      <i class="fas fa-file-pdf me-1"></i>PDF
                    </a>
                    <?php else: ?>
                    <span class="journal-nodl">
                      <i class="fas fa-clock me-1"></i>Bientôt
                    </span>
                    <?php endif; ?>
                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>

          <?php endforeach; ?>
        </div><!-- /accordion -->
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* Bloc login invitation */
.journal-login-box {
    text-align: center;
    padding: 48px 32px;
    border: 1px dashed #ddd;
    border-radius: 8px;
    background: #fafafa;
    margin-bottom: 20px;
}
.journal-login-icon {
    font-size: 3.8rem;
    color: #edb818;
    margin-bottom: 16px;
    display: block;
}
.journal-login-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}
.journal-login-text {
    font-size: .92rem;
    color: #666;
    margin-bottom: 24px;
}
.btn-journal-login {
    display: inline-block;
    background: #84252B;
    color: #fff;
    border-radius: 4px;
    padding: 10px 28px;
    font-weight: 600;
    font-size: .9rem;
    text-decoration: none;
    transition: background .2s;
}
.btn-journal-login:hover { background: #6a1c21; color: #fff; }
/* Carte éditrice */
.journal-editor-card {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 14px;
    flex-wrap: wrap;
}
.journal-editor-label {
    font-size: .92rem;
    color: #555;
    white-space: nowrap;
}
.journal-editor-member {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: background .2s;
}
a.journal-editor-member:hover {
    background: #f8f8f8;
}
a.journal-editor-member:hover .member-photo-wrap {
    border-color: #84252B;
}
/* Réutilisation exacte du style club_membres */
.member-photo-wrap {
    flex-shrink: 0;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    transition: border-color .25s;
    overflow: hidden;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.member-photo-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.member-photo-wrap .member-no-photo {
    font-size: 1.8rem;
    color: #bbb;
    line-height: 1;
}
.member-info { min-width: 0; }
.member-name {
    font-weight: 700;
    font-size: .9rem;
    color: #333;
    line-height: 1.3;
}
.member-ranking { font-size: .8rem; color: #84252B; font-weight: 600; }
/* Accordéon journal — couleur RBCD */
.accordion-theme-colored1 .accordion-button:not(.collapsed) {
    background-color: #84252B;
    color: #fff;
}
.accordion-theme-colored1 .accordion-button:not(.collapsed)::after {
    filter: brightness(10);
}
.accordion-theme-colored1 .accordion-button:focus {
    box-shadow: 0 0 0 .2rem rgba(132,37,43,.25);
}
.accordion-item {
    border: 1px solid rgba(0,0,0,.1);
    margin-bottom: 6px;
    border-radius: 4px !important;
    overflow: hidden;
}
.journal-count {
    font-size: .8rem;
    font-weight: 400;
    opacity: .75;
}
/* Liste des numéros dans chaque section année */
.journal-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.journal-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 12px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background .18s;
}
.journal-item:hover { background: #f8f8f8; }
.journal-item:last-child { border-bottom: none; }
.journal-item-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.journal-icon {
    color: #84252B;
    font-size: .95rem;
    flex-shrink: 0;
}
.journal-item-title {
    font-weight: 600;
    color: #333;
    font-size: .95rem;
}
.journal-item-desc {
    font-size: .82rem;
    color: #777;
    margin: 0;
}
/* Bouton de téléchargement */
.btn-journal-dl {
    background: #84252B;
    color: #fff;
    border-radius: 4px;
    padding: 5px 14px;
    font-weight: 600;
    font-size: .82rem;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .2s;
}
.btn-journal-dl:hover { background: #6a1c21; color: #fff; }
.journal-nodl {
    font-size: .82rem;
    color: #999;
    white-space: nowrap;
    flex-shrink: 0;
}
</style>
<?= $this->endSection() ?>
