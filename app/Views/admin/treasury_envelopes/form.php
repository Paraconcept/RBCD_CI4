<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $envelope !== null;
$errors = session()->getFlashdata('errors') ?? [];
$v      = fn($f, $default = '') => old($f, $isEdit ? ($envelope->$f ?? $default) : $default);

if ($isEdit) {
    $ecartVal   = (float)$envelope->amount_found + (float)$envelope->amount_sumup - (float)$envelope->amount_calculated;
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
                                <span class="text-muted"><small>Nom généré</small> : <strong id="namePreview"><?= $todayPrefix ?>01</strong></span>
                                <?php if (isset($errors['name'])): ?>
                                    <div class="text-danger small mt-1"><?= $errors['name'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col">
                        <div class="form-group mb-0">
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
                    <div class="col-auto pb-1">
                        <strong class="text-black" style="font-size:1.4rem"><i class="fas fa-equals"></i></strong>
                    </div>
                    <div class="col">
                        <div class="form-group mb-0">
                            <label>Montant trouvé total (€) <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
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
                    <div class="col-auto pb-1">
                        <strong class="text-black" style="font-size:1.4rem"><i class="fas fa-plus"></i></strong>
                    </div>
                    <div class="col">
                        <div class="form-group mb-0">
                            <label>Montant SumUp (€) <?= !$isEdit ? '<span class="text-danger">*</span>' : '' ?></label>
                            <?php if ($isEdit): ?>
                                <input type="text" class="form-control text-right"
                                       value="<?= number_format((float)$envelope->amount_sumup, 2, ',', ' ') ?> €" disabled>
                            <?php else: ?>
                                <input type="number" name="amount_sumup" id="amount_sumup"
                                       class="form-control text-right <?= isset($errors['amount_sumup']) ? 'is-invalid' : '' ?>"
                                       step="0.01" min="0"
                                       value="<?= esc($v('amount_sumup', '0.00')) ?>" required>
                                <?php if (isset($errors['amount_sumup'])): ?>
                                    <div class="invalid-feedback"><?= $errors['amount_sumup'] ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-auto pb-1">
                        <strong class="text-black" style="font-size:1.4rem"><i class="fas fa-arrows-alt-h"></i></strong>
                    </div>
                    <div class="col">
                        <div class="form-group mb-0">
                            <label>Écart</label>
                            <div class="pt-0">
                                <?php if ($isEdit): ?>
                                    <span class="badge <?= $ecartClass ?>" style="font-size:1rem;padding:.7em;">
                                        <?= $ecartText ?>
                                    </span>
                                <?php else: ?>
                                    <span id="ecart_badge" class="badge badge-secondary" style="font-size:1rem;padding:.7em;">
                                        0,00 €
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détail TVA -->
                <div class="card card-outline card-secondary mt-3 mb-0">
                    <div class="card-header py-2">
                        <h6 class="card-title mb-0"><i class="fas fa-percentage mr-1"></i>Détail TVA du montant trouvé <small class="text-muted">(facultatif)</small></h6>
                    </div>
                    <div class="card-body py-2">
                        <div class="row align-items-end">
                            <div class="col">
                                <div class="form-group mb-0">
                                    <label class="small">Montant 6% — gougouilles (€)</label>
                                    <?php if ($isEdit): ?>
                                        <input type="text" class="form-control text-right form-control-sm"
                                               value="<?= $envelope->amount_6pct !== null ? number_format((float)$envelope->amount_6pct, 2, ',', ' ') . ' €' : '—' ?>" disabled>
                                    <?php else: ?>
                                        <input type="number" name="amount_6pct" id="amount_6pct"
                                               class="form-control text-right form-control-sm <?= isset($errors['amount_6pct']) ? 'is-invalid' : '' ?>"
                                               step="0.01" min="0" placeholder="0,00"
                                               value="<?= esc($v('amount_6pct', '')) ?>">
                                        <?php if (isset($errors['amount_6pct'])): ?>
                                            <div class="invalid-feedback"><?= $errors['amount_6pct'] ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group mb-0">
                                    <label class="small">Montant 12% — gougouilles (€)</label>
                                    <?php if ($isEdit): ?>
                                        <input type="text" class="form-control text-right form-control-sm"
                                               value="<?= $envelope->amount_12pct !== null ? number_format((float)$envelope->amount_12pct, 2, ',', ' ') . ' €' : '—' ?>" disabled>
                                    <?php else: ?>
                                        <input type="number" name="amount_12pct" id="amount_12pct"
                                               class="form-control text-right form-control-sm <?= isset($errors['amount_12pct']) ? 'is-invalid' : '' ?>"
                                               step="0.01" min="0" placeholder="0,00"
                                               value="<?= esc($v('amount_12pct', '')) ?>">
                                        <?php if (isset($errors['amount_12pct'])): ?>
                                            <div class="invalid-feedback"><?= $errors['amount_12pct'] ?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group mb-0">
                                    <label class="small">Montant 21% — boissons (€) <em class="text-muted">(calculé)</em></label>
                                    <?php if ($isEdit): ?>
                                        <?php
                                        $amt21 = (float)$envelope->amount_found - (float)($envelope->amount_6pct ?? 0) - (float)($envelope->amount_12pct ?? 0);
                                        ?>
                                        <input type="text" class="form-control text-right form-control-sm"
                                               value="<?= number_format($amt21, 2, ',', ' ') ?> €" disabled>
                                    <?php else: ?>
                                        <input type="text" id="amount_21pct_display"
                                               class="form-control text-right form-control-sm bg-light"
                                               value="0,00 €" readonly>
                                    <?php endif; ?>
                                </div>
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

    function updateNameOptions(forceFirst) {
        const dateVal = $('#envelopeDate').val();
        if (!dateVal) return;
        const parts  = dateVal.split('-');
        const prefix = 'E' + parts[2] + '.' + parts[1] + '.';
        $('#namePrefix').text(prefix);

        const current = forceFirst ? null : $('#nameSeq').val();

        let firstAvailable = null;
        $('#nameSeq option').each(function () {
            const name  = prefix + $(this).val();
            const taken = usedNames.includes(name);
            $(this).prop('disabled', taken).toggleClass('text-muted', taken);
            if (!taken && firstAvailable === null) firstAvailable = $(this).val();
        });

        const selected = (current === null || usedNames.includes(prefix + current))
            ? firstAvailable
            : current;
        $('#nameSeq').val(selected);
        $('#namePreview').text(prefix + (selected || ''));
    }

    $('#envelopeDate').on('change', () => updateNameOptions(true));
    $('#nameSeq').on('change', function () {
        $('#namePreview').text($('#namePrefix').text() + $(this).val());
    });
    updateNameOptions(false); // page load : respecte la valeur PHP old()

    function calcAll() {
        const calc   = parseFloat($('#amount_calculated').val()) || 0;
        const found  = parseFloat($('#amount_found').val()) || 0;
        const pct6   = parseFloat($('#amount_6pct').val())  || 0;
        const pct12  = parseFloat($('#amount_12pct').val()) || 0;
        const sumup  = parseFloat($('#amount_sumup').val()) || 0;

        const pct21 = found - pct6 - pct12;
        $('#amount_21pct_display').val(
            (pct21 < 0 ? '⚠ ' : '') + pct21.toFixed(2).replace('.', ',') + ' €'
        ).toggleClass('text-danger', pct21 < 0);

        const ecart = (found + sumup) - calc;
        const sign  = ecart >= 0 ? '+' : '';
        $('#ecart_badge')
            .text(sign + ecart.toFixed(2).replace('.', ',') + ' €')
            .removeClass('badge-secondary badge-success badge-danger')
            .addClass(ecart === 0 ? 'badge-success' : 'badge-danger');
    }

    $('#amount_calculated, #amount_found, #amount_6pct, #amount_12pct, #amount_sumup').on('input', calcAll);
    calcAll();
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
