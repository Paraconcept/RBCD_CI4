<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Bienvenue</h3>
            </div>
            <div class="card-body">
                <p>Bonjour <strong><?= esc(session()->get('admin_name')) ?></strong>, bienvenue dans l'administration du <strong>RBC Disonais</strong>.</p>
                <p class="text-muted mb-0">Utilisez le menu latéral pour naviguer dans les sections.</p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
