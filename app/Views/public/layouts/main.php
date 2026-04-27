<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($title ?? 'RBC Disonais') ?></title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <?= $this->renderSection('styles') ?>
</head>
<body>

<!-- Barre de navigation temporaire — à remplacer avec le template public définitif -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand font-weight-bold" href="<?= base_url('/') ?>">RBC Disonais</a>
    <div class="ml-auto d-flex align-items-center">
        <?php if (session()->get('admin_logged_in')): ?>
            <span class="text-white mr-3" style="font-size:.9rem">
                <i class="fas fa-user mr-1"></i><?= esc(session()->get('admin_name')) ?>
            </span>
            <a href="<?= base_url('deconnexion') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
            </a>
        <?php else: ?>
            <a href="<?= base_url('connexion') ?>" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-in-alt mr-1"></i>Connexion membres
            </a>
        <?php endif; ?>
    </div>
</nav>

<div class="container-fluid py-4">

    <?php if ($flash = session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= esc($flash) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= esc($flash) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<?= $this->renderSection('scripts') ?>
</body>
</html>
