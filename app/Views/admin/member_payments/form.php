<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit     = $payment !== null;
$errors     = session()->getFlashdata('errors') ?? [];
$formAction = $isEdit
    ? base_url('admin/members/' . $member->id . '/payments/' . $payment->id . '/update')
    : base_url('admin/members/' . $member->id . '/payments');

// Helpers de valeur
$v   = fn($f, $default = '')  => old($f, $isEdit ? ($payment->$f ?? $default) : $default);
$chk = fn($f, $default = 0)   => (bool)(old($f) !== null ? old($f) : ($isEdit ? ($payment->$f ?? $default) : $default));
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

    <!-- ── Colonne gauche ───────────────────────────────────── -->
    <div class="col-lg-6">

        <!-- Saison (création seulement) -->
        <?php if (!$isEdit): ?>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Saison</h3>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label>Année de début <span class="text-danger">*</span>
                        <small class="text-muted">(ex : <?= ANNEE_1 ?> pour la saison <?= SAISON_EN_COURS ?>)</small>
                    </label>
                    <input type="number" name="year" class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>"
                           value="<?= old('year', ANNEE_1) ?>" min="2000" max="2100" required style="max-width:120px">
                    <?php if (isset($errors['year'])): ?>
                        <div class="invalid-feedback"><?= $errors['year'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cotisation RBCD -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-badge mr-2"></i>Cotisation RBCD <small class="text-muted">(jan–déc <?= $isEdit ? $payment->year + 1 : '' ?>)</small></h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="rbcd_paid" value="0">
                    <input type="checkbox" class="custom-control-input" id="rbcd_paid"
                           name="rbcd_paid" value="1" <?= $chk('rbcd_paid') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="rbcd_paid">Cotisation payée</label>
                </div>
                <div class="form-group mb-0" id="rbcd_date_wrap" <?= !$chk('rbcd_paid') ? 'style="display:none"' : '' ?>>
                    <label>Date de paiement</label>
                    <input type="date" name="rbcd_paid_date" class="form-control" style="max-width:180px"
                           value="<?= esc($v('rbcd_paid_date')) ?>">
                </div>
            </div>
        </div>

        <!-- Cotisation FRBB -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-medal mr-2"></i>Cotisation FRBB <small class="text-muted">(saison sep–jun)</small></h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="frbb_paid" value="0">
                    <input type="checkbox" class="custom-control-input" id="frbb_paid"
                           name="frbb_paid" value="1" <?= $chk('frbb_paid') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="frbb_paid">Cotisation payée</label>
                </div>
                <div class="form-group mb-0" id="frbb_date_wrap" <?= !$chk('frbb_paid') ? 'style="display:none"' : '' ?>>
                    <label>Date de paiement</label>
                    <input type="date" name="frbb_paid_date" class="form-control" style="max-width:180px"
                           value="<?= esc($v('frbb_paid_date')) ?>">
                </div>
            </div>
        </div>

    </div>

    <!-- ── Colonne droite ────────────────────────────────────── -->
    <div class="col-lg-6">

        <!-- Forfait H1 -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-billiard-ball mr-2"></i>Forfait billard H1 <small class="text-muted">(jan–juin — 75 €)</small></h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="forfait_h1_choice" value="0">
                    <input type="checkbox" class="custom-control-input choice-toggle"
                           id="forfait_h1_choice" name="forfait_h1_choice" value="1"
                           data-target="#h1_details"
                           <?= $chk('forfait_h1_choice') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="forfait_h1_choice">Le membre a souscrit au forfait H1</label>
                </div>
                <div id="h1_details" <?= !$chk('forfait_h1_choice') ? 'style="display:none"' : '' ?>>
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="forfait_h1_paid" value="0">
                        <input type="checkbox" class="custom-control-input" id="forfait_h1_paid"
                               name="forfait_h1_paid" value="1" <?= $chk('forfait_h1_paid') ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="forfait_h1_paid">Forfait H1 payé</label>
                    </div>
                    <div class="form-group mb-0" id="h1_date_wrap" <?= !$chk('forfait_h1_paid') ? 'style="display:none"' : '' ?>>
                        <label>Date de paiement</label>
                        <input type="date" name="forfait_h1_paid_date" class="form-control" style="max-width:180px"
                               value="<?= esc($v('forfait_h1_paid_date')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Forfait H2 -->
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-billiard-ball mr-2"></i>Forfait billard H2 <small class="text-muted">(jul–déc — 75 €)</small></h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="forfait_h2_choice" value="0">
                    <input type="checkbox" class="custom-control-input choice-toggle"
                           id="forfait_h2_choice" name="forfait_h2_choice" value="1"
                           data-target="#h2_details"
                           <?= $chk('forfait_h2_choice') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="forfait_h2_choice">Le membre a souscrit au forfait H2</label>
                </div>
                <div id="h2_details" <?= !$chk('forfait_h2_choice') ? 'style="display:none"' : '' ?>>
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="forfait_h2_paid" value="0">
                        <input type="checkbox" class="custom-control-input" id="forfait_h2_paid"
                               name="forfait_h2_paid" value="1" <?= $chk('forfait_h2_paid') ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="forfait_h2_paid">Forfait H2 payé</label>
                    </div>
                    <div class="form-group mb-0" id="h2_date_wrap" <?= !$chk('forfait_h2_paid') ? 'style="display:none"' : '' ?>>
                        <label>Date de paiement</label>
                        <input type="date" name="forfait_h2_paid_date" class="form-control" style="max-width:180px"
                               value="<?= esc($v('forfait_h2_paid_date')) ?>">
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-save mr-1"></i> <?= $isEdit ? 'Mettre à jour' : 'Créer' ?>
        </button>
        <a href="<?= base_url('admin/members/' . $member->id . '/payments') ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Annuler
        </a>
    </div>
</div>

</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    // Affiche/cache la date quand on coche "payé"
    function toggleDate(switchId, wrapId) {
        $('#' + switchId).on('change', function () {
            $(wrapId).toggle(this.checked);
        });
    }
    toggleDate('rbcd_paid',        '#rbcd_date_wrap');
    toggleDate('frbb_paid',        '#frbb_date_wrap');
    toggleDate('forfait_h1_paid',  '#h1_date_wrap');
    toggleDate('forfait_h2_paid',  '#h2_date_wrap');

    // Affiche/cache le bloc détail forfait quand on coche "a souscrit"
    $('.choice-toggle').on('change', function () {
        $($(this).data('target')).toggle(this.checked);
        if (!this.checked) {
            // Décocher "payé" si on retire la souscription
            $($(this).data('target')).find('input[type=checkbox]').prop('checked', false).trigger('change');
        }
    });
});
</script>
<?= $this->endSection() ?>
