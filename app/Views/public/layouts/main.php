<?= view('public/layouts/partials/header', [
    'extra_css'     => $this->renderSection('extra_css'),
    'styles'        => $this->renderSection('styles'),
    'extra_head_js' => $this->renderSection('extra_head_js'),
]) ?>

<div id="wrapper" class="clearfix">

  <?= view('public/layouts/partials/navigation') ?>

  <!-- Contenu principal -->
  <div class="main-content-area">

    <?php if (!empty($page_title)): ?>
    <section class="page-title layer-overlay overlay-dark-7 section-typo-light parallax"
             data-tm-bg-img="<?= base_url('assets/images/bg-banner-2.jpg') ?>"
             data-parallax-ratio="0.1">
      <div class="container pt-50 pb-50">
        <div class="section-content">
          <div class="row">
            <div class="col-md-12 text-center">
              <h2 class="title"><?= esc($page_title) ?></h2>
              <?php if (!empty($breadcrumbs)): ?>
              <nav class="breadcrumbs" role="navigation" aria-label="Fil d'Ariane">
                <div class="breadcrumbs">
                  <?php foreach ($breadcrumbs as $i => $crumb): ?>
                    <?php if ($i > 0): ?><span><i class="fa fa-angle-right"></i></span><?php endif; ?>
                    <?php if (!empty($crumb['url'])): ?>
                      <span><a href="<?= esc($crumb['url']) ?>"><?= esc($crumb['label']) ?></a></span>
                    <?php else: ?>
                      <span class="active"><?= esc($crumb['label']) ?></span>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </nav>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($flash = session()->getFlashdata('success')): ?>
      <div class="container mt-20">
        <div class="alert alert-success alert-dismissible fade show">
          <i class="fas fa-check-circle me-2"></i><?= esc($flash) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($flash = session()->getFlashdata('error')): ?>
      <div class="container mt-20">
        <div class="alert alert-danger alert-dismissible fade show">
          <i class="fas fa-exclamation-circle me-2"></i><?= esc($flash) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>

  </div>
  <!-- fin main-content-area -->

  <!-- Footer -->
  <?= view('public/layouts/partials/footer') ?>

  <a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
</div>
<!-- fin wrapper -->

<!-- Modal de connexion -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white border-0" style="background:#84252B">
        <h4 class="modal-title text-white fw-semibold" id="loginModalLabel">
          <i class="fas fa-user-lock fa-lg me-2"></i>Me connecter
        </h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div id="login-error" class="alert alert-danger d-none mb-3"></div>
        <form id="login-form" novalidate>
          <div class="mb-3">
            <label for="login-email" class="form-label fw-600">Adresse e-mail</label>
            <input type="email" id="login-email" name="email" class="form-control"
                   placeholder="votre@email.com" required autocomplete="email">
          </div>
          <div class="mb-4">
            <label for="login-password" class="form-label fw-600">Mot de passe</label>
            <input type="password" id="login-password" name="password" class="form-control"
                   placeholder="••••••••" required autocomplete="current-password">
          </div>
          <div class="d-grid">
            <button type="submit" id="login-submit" class="btn btn-lg text-white fw-600"
                    style="background:#84252B;border-color:#84252B">
              <i class="fa fa-sign-in-alt me-2"></i>Se connecter
            </button>
          </div>
        </form>
      </div>
      <?php if (session()->get('member_logged_in')): ?>
      <div class="modal-footer justify-content-center border-0 pt-0 pb-3 text-muted" style="font-size:.85rem">
        Connecté en tant que <strong><?= esc(session()->get('member_name')) ?></strong>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Scripts de bas de page -->
<script src="<?= base_url('studypress/js/custom.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<?= $this->renderSection('scripts') ?>

<script>
(function () {
  const csrfFieldName = '<?= csrf_token() ?>';
  const metaCsrf      = document.querySelector('meta[name="csrf-token"]');

  function getCsrf() {
    return metaCsrf ? metaCsrf.getAttribute('content') : '';
  }

  // ── Modal : remettre à zéro à l'ouverture ──
  const loginModal = document.getElementById('loginModal');
  if (loginModal) {
    loginModal.addEventListener('show.bs.modal', function () {
      document.getElementById('login-error').classList.add('d-none');
      document.getElementById('login-form').reset();
      setTimeout(() => document.getElementById('login-email').focus(), 300);
    });
  }

  // ── Soumission du formulaire de connexion ──
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn      = document.getElementById('login-submit');
      const errorDiv = document.getElementById('login-error');

      btn.disabled    = true;
      btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2"></span>Connexion…';
      errorDiv.classList.add('d-none');

      const body = new URLSearchParams({
        email   : document.getElementById('login-email').value,
        password: document.getElementById('login-password').value,
        [csrfFieldName]: getCsrf(),
      });

      fetch('<?= base_url('connexion') ?>', {
        method : 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body   : body.toString(),
      })
      .then(r => r.json())
      .then(data => {
        if (data.csrf) metaCsrf?.setAttribute('content', data.csrf);
        if (data.success) {
          window.location.reload();
        } else {
          errorDiv.textContent = data.message;
          errorDiv.classList.remove('d-none');
          btn.disabled  = false;
          btn.innerHTML = '<i class="fa fa-sign-in-alt me-2"></i>Se connecter';
        }
      })
      .catch(() => {
        errorDiv.textContent = 'Erreur réseau, veuillez réessayer.';
        errorDiv.classList.remove('d-none');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fa fa-sign-in-alt me-2"></i>Se connecter';
      });
    });
  }

  // ── Déconnexion AJAX ──
  document.querySelectorAll('.btn-logout-ajax').forEach(btn => {
    btn.addEventListener('click', function () {
      const body = new URLSearchParams({ [csrfFieldName]: getCsrf() });
      fetch('<?= base_url('deconnexion') ?>', {
        method : 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body   : body.toString(),
      })
      .finally(() => window.location.reload());
    });
  });
})();
</script>

</body>
</html>
