<footer id="footer" class="footer">
  <div class="footer-widget-area">
    <div class="container pt-90 pb-60">
      <div class="row">
        <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
          <div class="tm-widget-contact-info contact-info-style1 contact-icon-theme-colored1">
            <div class="thumb mb-20">
              <img alt="RBC Disonais" src="<?= base_url('assets/images/logo_footer_rbcd.png') ?>" style="max-height:60px;">
            </div>
            <div class="description text-gray">Club de billard carambole fondé en 1951 à Dison, Belgique.</div>
          </div>
          <ul class="styled-icons icon-dark icon-theme-colored1 icon-rounded clearfix mt-20">
            <li><a class="social-link" href="https://www.facebook.com/rbcdisonais" target="_blank"><i class="fab fa-facebook"></i></a></li>
          </ul>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-3 mb-md-40">
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Liens utiles</h4>
            <div class="widget widget_nav_menu split-nav-menu clearfix">
              <ul>
                <li><?= anchor('https://www.kbbb-frbb.eu/', 'FRBB /KBBB', array('target' => '_blank')); ?></li>
              </ul>
            </div>
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
                  <div class="text">Dison, Belgique</div>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-6 col-xl-3">
          <div class="widget">
            <h4 class="widget-title widget-title-line-bottom line-bottom-theme-colored1">Heures d'ouverture</h4>
            <div class="opening-hours border-dark">
              <ul>
                <li class="clearfix"><span>Lundi — Vendredi :</span><div class="value">19h00 — 23h00</div></li>
                <li class="clearfix"><span>Samedi :</span><div class="value">14h00 — 23h00</div></li>
                <li class="clearfix"><span>Dimanche :</span><div class="value">Fermé</div></li>
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
          <div class="col-sm-6 text-end">
            <div class="footer-paragraph">
              <a href="<?= base_url('admin') ?>" class="text-gray">Administration</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>
