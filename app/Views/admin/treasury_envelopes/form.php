<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $envelope !== null;
$errors = session()->getFlashdata('errors') ?? [];
$v      = fn($f, $default = '') => old($f, $isEdit ? ($envelope->$f ?? $default) : $default);

if ($isEdit) {
    $ecartVal   = (float)$envelope->amount_found - (float)$envelope->amount_calculated;
    $ecartSign  = $ecartVal >= 0 ? '+' : '';
    $ecartClass = $ecartVal == 0 ? 'badge-success' : 'badge-danger';
    $ecartText  = $ecartSign . number_format($ecartVal, 2, ',', ' ') . ' €';
}
$todayPrefix = 'E' . date('d.m.');
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
                <?php if ($isEdit): ?>
                <div class="card-tools">
                    <span class="badge badge-secondary"><i class="fas fa-lock mr-1"></i>Montants verrouillés</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Date <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control" value="<?= date('d/m/Y', strtotime($envelope->date)) ?>" disabled>
                            <?php else: ?>
                                <input type="date" name="date" id="envelopeDate"
                                       class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>"
                                       value="<?= esc($v('date', date('Y-m-d'))) ?>" required>
                                <?php if (isset($errors['date'])): ?>
                                    <div class="invalid-feedback"><?= $errors['date'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="form-group">
                            <label>Nom de l'enveloppe <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control font-weight-bold" value="<?= esc($envelope->name ?? '—') ?>" disabled>
                            <?php else: ?>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold" id="namePrefix"><?= $todayPrefix ?></span>
                                    </div>
                                    <select name="name_seq" id="nameSeq" class="form-control" style="max-width:80px" required>
                                        <?php $seq = old('name_seq', '01'); ?>
                                        <?php foreach (['01','02','03','04','05'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $seq === $s ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <small class="text-muted">Nom généré : <strong id="namePreview"><?= $todayPrefix ?>01</strong></small>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="text-danger small mt-1"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Montant calculé (€) <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control text-right"
                                       value="<?= number_format((float)$envelope->amount_calculated, 2, ',', ' ') ?> €" disabled>
                            <?php else: ?>
                                <input type="number" name="amount_calculated" id="amount_calculated"
                                       class="form-control text-right <?= isset($errors['amount_calculated']) ? 'is-invalid' : '' ?>"
                                       step="0.01" min="0"
                                       value="<?= esc($v('amount_calculated', '0.00')) ?>" required>
                                <?php if (isset($errors['amount_calculated'])): ?>
                                    <div class="invalid-feedback"><?= $errors['amount_calculated'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Montant trouvé (€) <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control text-right"
                                       value="<?= number_format((float)$envelope->amount_found, 2, ',', ' ') ?> €" disabled>
                            <?php else: ?>
                                <input type="number" name="amount_found" id="amount_found"
                                       class="form-control text-right <?= isset($errors['amount_found']) ? 'is-invalid' : '' ?>"
                                       step="0.01" min="0"
                                       value="<?= esc($v('amount_found', '0.00')) ?>" required>
                                <?php if (isset($errors['amount_found'])): ?>
                                    <div class="invalid-feedback"><?= $errors['amount_found'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Écart</label>
                            <div class="pt-2">
                                <?php if ($isEdit): ?>
                                    <span class="badge <?= $ecartClass ?>" style="font-size:1rem;padding:.4em .7em">
                                        <?= $ecartText ?>
                                    </span>
                                <?php else: ?>
                                    <span id="ecart_badge" class="badge badge-secondary" style="font-size:1rem;padding:.4em .7em">
                                        0,00 €
                                    </span>
                                <?php endif; ?>
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

    <?php if (!$isEdit): ?>
    const usedNames = <?= json_encode($usedNames ?? []) ?>;

    function updateNameOptions() {
        const dateVal = $('#envelopeDate').val();
        if (!dateVal) return;
        const parts  = dateVal.split('-');
        const prefix = 'E' + parts[2] + '.' + parts[1] + '.';
        $('#namePrefix').text(prefix);

        const current = $('#nameSeq').val(); // lire avant de modifier les options

        let firstAvailable = null;
        $('#nameSeq option').each(function () {
            const name  = prefix + $(this).val();
            const taken = usedNames.includes(name);
            $(this).prop('disabled', taken).toggleClass('text-muted', taken);
            if (!taken && firstAvailable === null) firstAvailable = $(this).val();
        });

        const selected = (firstAvailable !== null && usedNames.includes(prefix + current))
            ? firstAvailable
            : current;
        $('#nameSeq').val(selected);
        $('#namePreview').text(prefix + selected);
    }

    $('#envelopeDate').on('change', updateNameOptions);
    $('#nameSeq').on('change', function () {
        $('#namePreview').text($('#namePrefix').text() + $(this).val());
    });
    updateNameOptions();

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
    <?php endif; ?>

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
