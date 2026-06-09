<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<form action="<?= $formAction ?>" method="post" autocomplete="off">
<?= csrf_field() ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i>Modifier la clé</h3>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label>N° de badge</label>
                    <input type="text" name="badge_number" class="form-control"
                           placeholder="ex : 042"
                           value="<?= esc(old('badge_number', $key->badge_number ?? '')) ?>">
                </div>

                <div class="form-group mb-3">
                    <label>Titulaire</label>
                    <select name="member_id" id="member_id" class="form-control select2" style="width:100%">
                        <option value="">— Aucun (clé disponible) —</option>
                        <?php $currentMemberId = old('member_id', $key->member_id); ?>
                        <?php foreach ($allMembers as $m): ?>
                            <option value="<?= $m->id ?>" <?= $m->id == $currentMemberId ? 'selected' : '' ?>>
                                <?= esc($m->last_name . ' ' . $m->first_name) ?>
                                <?= $m->is_active ? '' : ' (inactif)' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small id="memberHint" class="text-muted">Laisser vide = clé disponible en stock</small>
                </div>

                <div class="form-group">
                    <label>&nbsp;</label>
                </div>

                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date d'attribution</label>
                            <input type="date" name="given_date" class="form-control"
                                   value="<?= esc(old('given_date', $key->given_date ?? '')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date de retour</label>
                            <input type="date" name="returned_date" class="form-control"
                                   value="<?= esc(old('returned_date', $key->returned_date ?? '')) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= esc(old('notes', $key->notes ?? '')) ?></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-save mr-1"></i> Enregistrer
        </button>
        <a href="<?= base_url('admin/club-keys') ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Annuler
        </a>
    </div>
</div>

</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    $('#member_id').select2({ theme: 'bootstrap4', placeholder: '— Aucun (clé disponible) —', allowClear: true });

    function toggleHint() {
        $('#memberHint').toggle($('#member_id').val() === '');
    }
    $('#member_id').on('change', toggleHint);
    toggleHint();
});
</script>
<?= $this->endSection() ?>
