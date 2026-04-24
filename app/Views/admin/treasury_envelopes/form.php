<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $envelope !== null;
$errors = session()->getFlashdata('errors') ?? [];
$v      = fn($f, $default = '') => old($f, $isEdit ? ($envelope->$f ?? $default) : $default);
?>

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
    <div class="col-lg-7">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-envelope-open-text mr-2"></i>Enveloppe de caisse</h3>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>"
                                   value="<?= esc($v('date', date('Y-m-d'))) ?>" required>
                            <?php if (isset($errors['date'])): ?>
                                <div class="invalid-feedback"><?= $errors['date'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label>Catégorie <span class="text-danger">*</span></label>
                            <select name="category" class="form-control">
                                <?php $cat = $v('category', 'bar'); ?>
                                <option value="bar"    <?= $cat === 'bar'    ? 'selected' : '' ?>>Bar / Buvette</option>
                                <option value="divers" <?= $cat === 'divers' ? 'selected' : '' ?>>Divers</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Montant calculé (€) <span class="text-danger">*</span></label>
                            <input type="number" name="amount_calculated" id="amount_calculated"
                                   class="form-control text-right <?= isset($errors['amount_calculated']) ? 'is-invalid' : '' ?>"
                                   step="0.01" min="0"
                                   value="<?= esc($v('amount_calculated', '0.00')) ?>" required>
                            <?php if (isset($errors['amount_calculated'])): ?>
                                <div class="invalid-feedback"><?= $errors['amount_calculated'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Montant trouvé (€) <span class="text-danger">*</span></label>
                            <input type="number" name="amount_found" id="amount_found"
                                   class="form-control text-right <?= isset($errors['amount_found']) ? 'is-invalid' : '' ?>"
                                   step="0.01" min="0"
                                   value="<?= esc($v('amount_found', '0.00')) ?>" required>
                            <?php if (isset($errors['amount_found'])): ?>
                                <div class="invalid-feedback"><?= $errors['amount_found'] ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Écart</label>
                            <div class="pt-2">
                                <span id="ecart_badge" class="badge badge-secondary" style="font-size:1rem;padding:.4em .7em">
                                    0,00 €
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Clôturé par <span class="text-danger">*</span></label>
                    <select name="closed_by_member_id" id="closed_by_member_id"
                            class="form-control select2 <?= isset($errors['closed_by_member_id']) ? 'is-invalid' : '' ?>"
                            style="width:100%">
                        <option value="">— Sélectionner —</option>
                        <?php $closedBy = (int) $v('closed_by_member_id', 0); ?>
                        <?php foreach ($keyHolders as $kh): ?>
                            <option value="<?= $kh->id ?>" <?= $kh->id == $closedBy ? 'selected' : '' ?>>
                                <?= esc($kh->last_name . ' ' . $kh->first_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['closed_by_member_id'])): ?>
                        <div class="invalid-feedback d-block"><?= $errors['closed_by_member_id'] ?></div>
                    <?php elseif (empty($keyHolders)): ?>
                        <small class="text-muted">
                            Aucun porteur de clé enregistré —
                            <a href="<?= base_url('admin/members') ?>">gérer les membres</a>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group mb-0">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= esc($v('notes')) ?></textarea>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Mettre à jour' : 'Enregistrer' ?>
        </button>
        <a href="<?= base_url('admin/treasury/envelopes') ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Annuler
        </a>
    </div>
</div>

</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    $('.select2').select2({ theme: 'bootstrap4', placeholder: '— Sélectionner —' });

    function calcEcart() {
        const calc  = parseFloat($('#amount_calculated').val()) || 0;
        const found = parseFloat($('#amount_found').val()) || 0;
        const ecart = found - calc;
        const sign  = ecart >= 0 ? '+' : '';
        $('#ecart_badge')
            .text(sign + ecart.toFixed(2).replace('.', ',') + ' €')
            .removeClass('badge-secondary badge-success badge-danger')
            .addClass(ecart === 0 ? 'badge-success' : 'badge-danger');
    }

    $('#amount_calculated, #amount_found').on('input', calcEcart);
    calcEcart();

    $('form').on('submit', function (e) {
        if (!$('#closed_by_member_id').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Champ obligatoire',
                text: 'Renseignez la personne qui a clôturé !',
                confirmButtonColor: '#84252B',
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
