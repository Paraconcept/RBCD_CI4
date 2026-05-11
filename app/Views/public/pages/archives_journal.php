<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto text-center mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title">Partie Libre</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description mt-20">
            Le journal interne du RBC Disonais — retrouvez ici tous les numéros disponibles au téléchargement.
          </p>
        </div>
      </div>
    </div>

    <?php if (empty($byYear)): ?>
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
                 aria-labelledby="heading-<?= $yearId ?>">
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
}
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
