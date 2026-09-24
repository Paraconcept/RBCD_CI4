<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="mb-3">
    <a href="<?= base_url('admin/members/' . $member->id . '/edit') ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Retour fiche membre
    </a>
</div>

<?= $this->include('admin/member_payments/_history') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('admin/member_payments/_history_js') ?>
<?= $this->endSection() ?>
