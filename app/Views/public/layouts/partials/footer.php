<footer id="footer" class="footer">
  <div class="footer-widget-area">
    <div class="container pt-90 pb-60">
      <div class="row">
        <div class="col-md-6 col-lg-6 col-xl-4 mb-md-40">
          <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
            <div class="thumb mb-20">
              <img alt="RBC Disonais" src="<?= base_url('assets/images/logo_footer_rbcd.png') ?>" style="max-height:60px;">
            </div>
            <div class="description">Club de billard carambole fondé en 1951 à Dison, Belgique.</div>
          </div>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Contact</h4>
            <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
              <ul>
                <li class="contact-phone">
                  <div class="icon"><i class="flaticon-contact-042-phone-1"></i></div>
                  <div class="text"><a href="tel:+32473453899">0473 / 45 38 99</a></div>
                </li>
                <li class="contact-email">
                  <div class="icon"><i class="flaticon-contact-043-email-1"></i></div>
                  <div class="text"><a href="mailto:contact@rbcd.be">contact@rbcd.be</a></div>
                </li>
                <li class="contact-address">
                  <div class="icon"><i class="flaticon-contact-047-location"></i></div>
                  <div class="text"><a href="https://maps.google.com/maps?q=Av+Reine+%C3%89lisabeth+46B,+4820+Dison,+Belgique&hl=fr&z=17" target="_blank" rel="noopener">Dison, Belgique</a></div>
                </li>
              </ul>

              <a href="https://www.facebook.com/rbcdisonais" target="_blank" rel="noopener" class="mt-20 d-inline-block">
                <i class="fab fa-facebook" style="color:#1877F2;font-size:1.6rem;"></i>
              </a>

            </div>
          </div>
        </div>
        <div class="col-md-12 col-lg-12 col-xl-5">
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Heures d'ouverture</h4>
            <?php
              // Grouper les jours consécutifs ayant les mêmes horaires
              $allHours = (new \App\Models\OpeningHourModel())->getAllOrdered();
              $groups = [];
              foreach ($allHours as $h) {
                  $key = implode('|', [
                      $h->is_closed,
                      $h->morning_open,   $h->morning_close,
                      $h->afternoon_open, $h->afternoon_close,
                      $h->evening_open,   $h->evening_close,
                  ]);
                  if ($groups && end($groups)['key'] === $key) {
                      $groups[count($groups) - 1]['end'] = $h->day_name;
                  } else {
                      $groups[] = ['key' => $key, 'start' => $h->day_name, 'end' => $h->day_name, 'h' => $h];
                  }
              }
            ?>
            <div class="opening-hours border-dark">
              <ul>
                <?php foreach ($groups as $g):
                  $label = $g['start'] === $g['end'] ? $g['start'] : $g['start'] . ' — ' . $g['end'];
                  $h = $g['h'];
                  $slots = [];
                  if (!$h->is_closed) {
                      if ($h->morning_open   && $h->morning_close)   $slots[] = substr($h->morning_open, 0, 5)   . ' — ' . substr($h->morning_close, 0, 5);
                      if ($h->afternoon_open && $h->afternoon_close) $slots[] = substr($h->afternoon_open, 0, 5) . ' — ' . substr($h->afternoon_close, 0, 5);
                      if ($h->evening_open   && $h->evening_close)   $slots[] = substr($h->evening_open, 0, 5)   . ' — ' . substr($h->evening_close, 0, 5);
                  }
                ?>
                <li class="clearfix">
                  <span><?= esc($label) ?> :</span>
                  <div class="value">
                    <?= $h->is_closed ? 'Fermé' : ($slots ? implode(' | ', $slots) : '—') ?>
                  </div>
                </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="footer-bottom" data-tm-bg-color="#84252B">
      <div class="container">
        <div class="row pt-20 pb-20">
          <div class="col-sm-6">
            <div class="footer-paragraph">
              &copy; <?= date('Y') ?> RBC Disonais. Tous droits réservés.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
