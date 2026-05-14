<?= $this->extend('public/layouts/main') ?>
<?= $this->section('content') ?>

<section class="section-padding">
  <div class="container">
    <div class="row">

      <!-- Coordonnées -->
      <div class="col-lg-6 mb-40">
        <div class="tm-sc-heading text-left mb-30">
          <h3 class="heading-title">Nous contacter</h3>
          <div class="heading-border-line left-center-line"></div>
        </div>

        <div class="d-flex align-items-start mb-30">
          <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>"
               alt="Écusson RBC Disonais"
               style="width:160px;flex-shrink:0;margin-right:24px;margin-top:4px;">
          <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
            <ul>
              <li class="contact-address">
                <div class="icon"><i class="flaticon-contact-047-location"></i></div>
                <div class="text">
                  <strong>RBC Disonais</strong><br>
                  Chalet de Bonvoisin<br>
                  Av. Reine Élisabeth, 46B<br>
                  4820 DISON, Belgique
                </div>
              </li>
              <li class="contact-phone">
                <div class="icon"><i class="flaticon-contact-050-phone"></i></div>
                <div class="text">
                  <a href="tel:+32494797353">0494 / 797 353</a>
                  <pan class="ms-4 me-4">&nbsp;</pan>
                  <i class="fas fa-long-arrow-alt-left" style="color:#84252B;"></i>
                  <i class="fas fa-question-circle" style="color:#84252B;font-size:1.4rem;cursor:help;"
                     data-bs-toggle="tooltip" data-bs-html="true"
                     title="<i class='fas fa-mobile-alt fa-lg'></i><br>En raison des compétitions, le gsm est souvent sur silencieux.<br>N'hésitez pas à laisser un message, nous les écoutons tous !"></i>
                </div>
              </li>
              <li class="contact-email">
                <div class="icon"><i class="flaticon-contact-043-email-1"></i></div>
                <div class="text">
                  <a href="mailto:contact@rbcd.be">contact@rbcd.be</a>
                </div>
              </li>
              <li>
                <div class="icon"><i class="icomoon-banknote"></i></div>
                <div class="text"><u>N° de compte en banque</u> :<br>BE67 0012 1335 2687</div>
              </li>
              <li>
                <div class="icon"><i class="icomoon-facebook"></i></div>
                <div class="text">
                  <a href="https://www.facebook.com/rbcdisonais" target="_blank" rel="noopener">www.facebook.com/RBCDisonais</a>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Heures d'ouverture -->
      <div class="col-lg-6 mb-10">
        <div class="tm-sc-heading text-left mb-30">
          <h3 class="heading-title">Heures d'ouverture</h3>
          <div class="heading-border-line left-center-line"></div>
        </div>

        <table class="table-opening-hours">
          <thead>
            <tr>
              <th></th>
              <th>Matin</th>
              <th>Après-midi</th>
              <th>Soir<i class="fa fa-info-circle ms-2"></i></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hours as $h): ?>
            <tr class="<?= $h->is_closed ? 'row-closed' : '' ?>">
              <td class="day-name"><?= esc($h->day_name) ?></td>
              <?php if ($h->is_closed): ?>
                <td colspan="3" class="text-center closed-label">Fermé</td>
              <?php else: ?>
                <td>
                  <?php if ($h->morning_open && $h->morning_close): ?>
                    <?= substr($h->morning_open, 0, 5) ?> — <?= substr($h->morning_close, 0, 5) ?>
                  <?php else: ?>
                    <span class="slot-empty">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($h->afternoon_open && $h->afternoon_close): ?>
                    <?= substr($h->afternoon_open, 0, 5) ?> — <?= substr($h->afternoon_close, 0, 5) ?>
                  <?php else: ?>
                    <span class="slot-empty">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($h->evening_open && $h->evening_close): ?>
                    <?= substr($h->evening_open, 0, 5) ?> — <?= substr($h->evening_close, 0, 5) ?>
                  <?php else: ?>
                    <span class="slot-empty">—</span>
                  <?php endif; ?>
                </td>
              <?php endif; ?>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <p class="text-muted mt-20" style="font-size:.9rem;">
          <i class="fa fa-info-circle me-2"></i>
          Les horaires de soirées s'appliquent uniquement les soirs de compétitions.
        </p>
      </div>

    </div>
    <!-- fin row coordonnées + horaires -->

    <!-- Séparateur Craie de billard -->
    <div class="row">
      <div class="separator">
        <img  src="<?= base_url('assets/images/billiard-chalk.png') ?>" 
              alt="Séparateur Craie de billard" 
              style="width:20px;opacity:0.7;margin: 0 10px;">
      </div>
    </div>

    
    <!-- Carte Google Maps -->
    <div class="row mt-10">
      <div class="col-12">
        <div class="tm-sc-heading text-left mb-20">
          <h3 class="heading-title">Nous trouver</h3>
          <div class="heading-border-line left-center-line"></div>
        </div>
        <div class="map-wrapper" style="border-radius:4px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.1)">
          <iframe
            src="https://maps.google.com/maps?q=Av+Reine+%C3%89lisabeth+46B,+4820+Dison,+Belgique&hl=fr&z=17&output=embed"
            width="100%" height="380" style="border:0" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
          </iframe>
        </div>
      </div>
    </div>

  </div>
</section>

<?= $this->section('styles') ?>
<style>
.table-opening-hours {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    font-size: .95rem;
}
.table-opening-hours thead th {
    padding: 8px 14px;
    font-weight: 600;
    color: #fff;
    background: #84252B;
    text-align: center;
    border: 1px solid #6e1f24;
}
.table-opening-hours thead th:first-child {
    text-align: left;
    width: 25%;
}
.table-opening-hours tbody tr {
    border-bottom: 1px solid #e8e8e8;
}
.table-opening-hours tbody tr:last-child {
    border-bottom: none;
}
.table-opening-hours tbody td {
    padding: 10px 14px;
    text-align: center;
    vertical-align: middle;
    color: #555;
}
.table-opening-hours tbody td.day-name {
    text-align: left;
    font-weight: 600;
    color: #333;
}
.table-opening-hours tbody tr.row-closed td {
    background: #fafafa;
    color: #aaa;
}
.table-opening-hours .closed-label {
    font-style: italic;
    color: #aaa;
}
.table-opening-hours .slot-empty {
    color: #ccc;
}
</style>
<?= $this->endSection() ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const rbcdTpl = '<div class="tooltip tooltip-rbcd" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>';
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { template: rbcdTpl, html: true, sanitize: false, delay: { show: 80, hide: 100 } });
});
</script>
<?= $this->endSection() ?>
