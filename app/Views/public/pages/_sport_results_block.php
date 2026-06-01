<?php
/**
 * Partial : bloc palmarès pour une équipe (CDR ou INTM).
 * Variables attendues : $sportResults (array), $teamName (string), $season (string)
 */
?>
<style>
.sr-list { list-style:none; margin:0; padding:0; }
.sr-item { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid #f0f0f0; transition:background .15s; }
.sr-item:last-child { border-bottom:none; }
.sr-item:hover { background:#fafafa; }
.sr-type-col { flex-shrink:0; width:30px; text-align:center; }
.sr-icon-coupe       { font-size:1.5rem; color:#e6a800; }
.sr-icon-championnat { font-size:1.5rem; color:#84252B; }
.sr-place-num { display:inline-block; font-size:.8rem; font-weight:700; color:#888; line-height:1; }
.sr-info  { flex:1; min-width:0; }
.sr-title { font-weight:700; color:#333; font-size:.95rem; text-decoration:underline; margin-bottom:4px; }
.sr-winner { font-size:.85rem; color:#555; }
.sr-date-col { flex-shrink:0; min-width:170px; font-size:.8rem; color:#555; }
.sr-date-label { display:block; font-weight:600; margin-bottom:2px; }
.sr-date-value { display:block; }
.sr-pdf { flex-shrink:0; text-align:center; }
.sr-pdf-link { color:#c0392b; text-decoration:none; transition:transform .15s,color .15s; display:block; }
.sr-pdf-link i { font-size:1.8rem; }
.sr-pdf-link:hover { color:#84252B; transform:scale(1.1); }
.sr-pdf-none { color:#ccc; cursor:default; display:block; }
.sr-pdf-none i { font-size:1.8rem; }
.sr-count { font-size:.8rem; font-weight:400; opacity:.75; }
.accordion-theme-colored1 .accordion-button:not(.collapsed) { background-color:#84252B; color:#fff; }
.accordion-theme-colored1 .accordion-button:not(.collapsed)::after { filter:brightness(10); }
.accordion-theme-colored1 .accordion-button:focus { box-shadow:0 0 0 .2rem rgba(132,37,43,.25); }
.accordion-item { border:1px solid rgba(0,0,0,.1); margin-bottom:6px; border-radius:4px !important; overflow:hidden; }
</style>

<div class="accordion tm-accordion accordion-classic accordion-theme-colored1 mb-4" id="accordionTeamPalmares">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingTeamPalmares">
      <button class="accordion-button" type="button"
              data-bs-toggle="collapse" data-bs-target="#collapseTeamPalmares"
              aria-expanded="true" aria-controls="collapseTeamPalmares">
        <i class="fas fa-trophy me-3" style="opacity:.7"></i>
        <strong>Palmarès — <?= esc($teamName) ?> — Saison <?= esc($season) ?></strong>
        <span class="sr-count ms-5">
          <?= count($sportResults) ?>&nbsp;résultat<?= count($sportResults) > 1 ? 's' : '' ?>
        </span>
      </button>
    </h2>
    <div id="collapseTeamPalmares" class="accordion-collapse collapse show"
         aria-labelledby="headingTeamPalmares" data-bs-parent="#accordionTeamPalmares">
      <div class="accordion-body py-0 px-0">
        <ul class="sr-list">
          <?php foreach ($sportResults as $r): ?>
          <?php
            $place = (int) ($r->place ?? 1);
            $dateStr = null;
            if ($r->final_date) {
                $dt     = new DateTime($r->final_date);
                $days   = ['','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
                $months = ['','janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
                $dateStr = $days[(int)$dt->format('N')] . ' ' . $dt->format('j') . ' ' . $months[(int)$dt->format('n')] . ' ' . $dt->format('Y');
            }
            $hasPdf = $r->pdf_file && file_exists(FCPATH . 'uploads/PDF/SportResults/' . $r->pdf_file);
            $placeLabel = match(true) {
                $place === 1 && $r->type === 'coupe'       => 'Vainqueur',
                $place === 1 && $r->type === 'championnat' => 'Champion',
                $place === 1                               => '1<sup>er</sup>',
                default                                    => $place . '<sup>ème</sup> place',
            };
          ?>
          <li class="sr-item">
            <div class="sr-type-col">
              <?php if ($place === 1): ?>
                <?php if ($r->type === 'coupe'): ?>
                  <i class="fas fa-trophy sr-icon-coupe" title="Coupe"></i>
                <?php elseif ($r->type === 'championnat'): ?>
                  <i class="fas fa-medal sr-icon-championnat" title="Championnat"></i>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <div class="sr-info">
              <div class="sr-title"><?= esc($r->title) ?></div>
              <div class="sr-winner"><?= $placeLabel ?></div>
            </div>
            <div class="sr-date-col">
              <?php if ($dateStr): ?>
                <span class="sr-date-label"><i class="far fa-calendar-alt me-1"></i>Finale le :</span>
                <span class="sr-date-value"><?= $dateStr ?></span>
              <?php endif; ?>
            </div>
            <div class="sr-pdf">
              <?php if ($hasPdf): ?>
                <a href="<?= base_url('uploads/PDF/SportResults/' . $r->pdf_file) ?>"
                   target="_blank" rel="noopener" class="sr-pdf-link" title="Télécharger (PDF)">
                  <i class="fas fa-file-pdf"></i>
                </a>
              <?php else: ?>
                <span class="sr-pdf-none" title="Pas de PDF"><i class="fas fa-file-pdf"></i></span>
              <?php endif; ?>
            </div>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
