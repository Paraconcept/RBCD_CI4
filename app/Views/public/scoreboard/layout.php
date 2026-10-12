<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Marquoirs') ?> | RBC Disonais</title>
    <link rel="shortcut icon" href="<?= base_url('assets/images/favicons/favicon.ico') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600;700&family=Barlow+Condensed:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/scoreboard/scoreboard.css') . '?v=' . @filemtime(FCPATH . 'assets/scoreboard/scoreboard.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body class="sb-body">
<header class="sb-topbar">
    <a href="<?= $brandUrl ?? base_url('marquoir') ?>" class="sb-brand">
        <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" alt="">
        <span>RBC Disonais <small><?= esc($brandSub ?? 'Marquoirs') ?></small></span>
    </a>
    <?= $this->renderSection('topbar') ?>
</header>
<main class="container py-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check me-2"></i><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><i class="fas fa-ban me-2"></i><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger"><ul class="mb-0">
            <?php foreach ((array) session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
<?= $this->renderSection('scripts') ?>
</body>
</html>
