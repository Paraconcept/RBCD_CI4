<?= $this->extend('public/layouts/main') ?>

<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">

    <!-- Heading -->
    <div class="row">
      <div class="col-md-10 col-lg-8 mx-auto mb-40">
        <div class="tm-sc-heading">
          <h3 class="heading-title text-center">Résultats sportifs</h3>
          <div class="heading-border-line"></div>
          <p class="heading-description text-center mt-20">
            Palmarès des joueurs du RBC Disonais — coupes et championnats, saison après saison.
          </p>
        </div>
      </div>
    </div>

    <!-- Légende -->
    <div class="row mb-20">
      <div class="col-12 text-center">
        <span class="sr-legend-item"><i class="fas fa-trophy sr-icon-coupe me-1"></i>Coupe</span>
        <span class="sr-legend-sep">·</span>
        <span class="sr-legend-item"><i class="fas fa-medal sr-icon-championnat me-1"></i>Championnat</span>
      </div>
    </div>

    <?php if (empty($bySeasonData)): ?>
    <div class="row">
      <div class="col-md-8 offset-md-2 text-center text-muted py-40">
        <i class="fas fa-trophy fa-3x mb-20" style="color:#ccc"></i>
        <p>Aucun résultat enregistré pour le moment.</p>
      </div>
    </div>

    <?php else: ?>
    <div class="row">
      <div class="col-md-10 mx-auto">

        <div class="accordion tm-accordion accordion-classic accordion-theme-colored1" id="resultsAccordion">

          <?php foreach ($bySeasonData as $season => $results): ?>
          <?php $sid = 'season-' . str_replace('-', '', $season); ?>

          <div class="accordion-item">
            <h2 class="accordion-header" id="heading-<?= $sid ?>">
              <button class="accordion-button collapsed"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#<?= $sid ?>"
                      aria-expanded="false"
                      aria-controls="<?= $sid ?>">
                <i class="fas fa-trophy me-3" style="opacity:.7"></i>
                <strong>Saison <?= esc($season) ?></strong>
                <span class="sr-count ms-3"><?= count($results) ?> résultat<?= count($results) > 1 ? 's' : '' ?></span>
              </button>
            </h2>
            <div id="<?= $sid ?>"
                 class="accordion-collapse collapse"
                 aria-labelledby="heading-<?= $sid ?>"
                 data-bs-parent="#resultsAccordion">
              <div class="accordion-body py-0 px-0">
                <ul class="sr-list">
                  <?php foreach ($results as $r): ?>
                  <?php
                    $winnerName = $r->m_id
                        ? (mb_strtoupper($r->m_last) . ' ' . $r->m_first)
                        : ($r->winner_name ?? null);
                    $photo = ($r->winner_photo ?? null)
                        ? base_url('uploads/sport_results/' . $r->winner_photo)
                        : null;
                    $dateStr = null;
                    if ($r->final_date) {
                        $dt     = new DateTime($r->final_date);
                        $days   = ['','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                        $months = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
                        $dateStr = $days[(int)$dt->format('N')] . ' ' . $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
                    }
                    $pdfPath = $r->pdf_file ? FCPATH . 'uploads/PDF/SportResults/' . $r->pdf_file : null;
                    $hasPdf  = $pdfPath && file_exists($pdfPath);
                  ?>
                  <li class="sr-item">

                    <!-- Photo -->
                    <div class="sr-photo">
                      <?php if ($photo): ?>
                        <img src="<?= esc($photo) ?>" alt="<?= esc($winnerName ?? '') ?>">
                      <?php else: ?>
                        <i class="fas fa-user"></i>
                      <?php endif; ?>
                    </div>

                    <!-- Icône type -->
                    <div class="sr-type-col">
                      <?php if ($r->type === 'coupe'): ?>
                        <i class="fas fa-trophy sr-icon-coupe" title="Coupe"></i>
                      <?php elseif ($r->type === 'championnat'): ?>
                        <i class="fas fa-medal sr-icon-championnat" title="Championnat"></i>
                      <?php endif; ?>
                    </div>

                    <!-- Titre + vainqueur -->
                    <div class="sr-info">
                      <div class="sr-title"><?= esc($r->title) ?></div>
                      <?php if ($winnerName): ?>
                      <div class="sr-winner"><strong>Vainqueur :</strong> <?= esc($winnerName) ?></div>
                      <?php endif; ?>
                    </div>

                    <!-- Date -->
                    <div class="sr-date-col">
                      <?php if ($dateStr): ?>
                        <span class="sr-date-label"><i class="far fa-calendar-alt me-1"></i>Finale disputée le :</span>
                        <span class="sr-date-value"><?= $dateStr ?></span>
                      <?php endif; ?>
                    </div>

                    <!-- PDF -->
                    <div class="sr-pdf">
                      <?php if ($hasPdf): ?>
                        <a href="<?= base_url('uploads/PDF/SportResults/' . $r->pdf_file) ?>"
                           target="_blank" rel="noopener" class="sr-pdf-link" title="Télécharger les résultats (PDF)">
                          <i class="fas fa-file-pdf"></i>
                        </a>
                      <?php else: ?>
                        <span class="sr-pdf-none" title="Pas de fichier PDF disponible">
                          <i class="fas fa-file-pdf"></i>
                        </span>
                      <?php endif; ?>
                    </div>

                  </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </div>

          <?php endforeach; ?>
        </div>

      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
/* ── Accordéon RBCD (identique journal) ── */
.accordion-theme-colored1 .accordion-button:not(.collapsed) {
    background-color: #84252B; color: #fff;
}
.accordion-theme-colored1 .accordion-button:not(.collapsed)::after { filter: brightness(10); }
.accordion-theme-colored1 .accordion-button:focus { box-shadow: 0 0 0 .2rem rgba(132,37,43,.25); }
.accordion-item { border: 1px solid rgba(0,0,0,.1); margin-bottom: 6px; border-radius: 4px !important; overflow: hidden; }
.sr-count { font-size: .8rem; font-weight: 400; opacity: .75; }

/* ── Liste des résultats ── */
.sr-list { list-style: none; margin: 0; padding: 0; }

.sr-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid #f0f0f0;
    transition: background .15s;
}
.sr-item:last-child { border-bottom: none; }
.sr-item:hover { background: #fafafa; }

/* Photo */
.sr-photo {
    flex-shrink: 0;
    width: 56px; height: 56px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    overflow: hidden;
    background: #f0f0f0;
    display: flex; align-items: center; justify-content: center;
    color: #bbb; font-size: 1.4rem;
}
.sr-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }

/* Icône type */
.sr-type-col { flex-shrink: 0; width: 30px; text-align: center; }
.sr-icon-coupe       { font-size: 1.5rem; color: #e6a800; }
.sr-icon-championnat { font-size: 1.5rem; color: #84252B; }

/* Infos */
.sr-info { flex: 1; min-width: 0; }
.sr-title {
    font-weight: 700; color: #333; font-size: .95rem;
    text-decoration: underline;
    margin-bottom: 4px;
}
.sr-winner { font-size: .85rem; color: #555; }

/* Date */
.sr-date-col {
    flex-shrink: 0;
    min-width: 170px;
    font-size: .8rem;
    color: #555;
}
.sr-date-label { display: block; font-weight: 600; margin-bottom: 2px; }
.sr-date-value { display: block; }

/* PDF */
.sr-pdf { flex-shrink: 0; text-align: center; }
.sr-pdf-link {
    color: #c0392b; text-decoration: none;
    transition: transform .15s, color .15s;
    display: block;
}
.sr-pdf-link i { font-size: 1.8rem; }
.sr-pdf-link:hover { color: #84252B; transform: scale(1.1); }
.sr-pdf-none { color: #ccc; cursor: default; display: block; }
.sr-pdf-none i { font-size: 1.8rem; }

/* Légende */
.sr-legend-item { font-size: .82rem; color: #555; }
.sr-legend-sep  { color: #ccc; margin: 0 10px; }
</style>
<?= $this->endSection() ?>
