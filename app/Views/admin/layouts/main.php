<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($title ?? 'Admin') ?> | RBC Disonais</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/custom.css') ?>">

    <?= $this->renderSection('styles') ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark navbar-rbcd">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('admin/dashboard') ?>" class="nav-link">Tableau de bord</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="fas fa-user-circle"></i>
                    <span class="d-none d-md-inline ml-1"><?= esc(session()->get('admin_name')) ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="<?= base_url() ?>" class="dropdown-item" target="_blank">
                        <i class="fas fa-external-link-alt mr-2"></i> Voir le site
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= base_url('admin/logout') ?>" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <?= $this->include('admin/partials/sidebar') ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= esc($title ?? 'Admin') ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">
                                <a href="<?= base_url('admin/dashboard') ?>">Tableau de bord</a>
                            </li>
                            <?php if (isset($breadcrumbs) && is_array($breadcrumbs)): ?>
                                <?php foreach ($breadcrumbs as $bc): ?>
                                    <?php if (isset($bc['url'])): ?>
                                        <li class="breadcrumb-item"><a href="<?= $bc['url'] ?>"><?= esc($bc['title']) ?></a></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item active"><?= esc($bc['title']) ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?= esc($title ?? '') ?></li>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible flash-msg">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-check"></i> <?= session()->getFlashdata('success') ?>
                </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible flash-msg">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <i class="icon fas fa-ban"></i> <?= session()->getFlashdata('error') ?>
                </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>

            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>RBC Disonais</strong> &mdash; Administration
        <div class="float-right d-none d-sm-inline-block">
            <b>CI4</b> v<?= \CodeIgniter\CodeIgniter::CI_VERSION ?>
        </div>
    </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Summernote -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>

<script>
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// DataTables defaults (français)
$.extend(true, $.fn.dataTable.defaults, {
    language: {
        paginate:     { first: '«', previous: '‹', next: '›', last: '»' },
        search:       'Rechercher :',
        lengthMenu:   'Afficher _MENU_ entrées',
        info:         'Entrées _START_ à _END_ sur _TOTAL_',
        infoEmpty:    'Aucune entrée',
        infoFiltered: '(filtré sur _MAX_ entrées)',
        zeroRecords:  'Aucun résultat',
        emptyTable:   'Aucune donnée disponible',
    }
});

// Auto-hide des alertes flash après 5s
$(function() {
    setTimeout(function() { $('.flash-msg').fadeOut(400); }, 5000);
});
</script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
