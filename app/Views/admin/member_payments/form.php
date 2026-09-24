<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php
$refParam   = $ref ? '?ref=' . $ref : '';
$formAction = base_url("admin/members/{$member->id}/payments/{$year}") . $refParam;
$backUrl    = match ($ref) {
    'treasury'    => base_url("admin/treasury?year={$year}"),
    'member_edit' => base_url("admin/members/{$member->id}/edit?tab=cotisations"),
    default       => base_url("admin/members/{$member->id}/payments"),
};

$v   = fn(?object $row, string $f) => old($f, $row->$f ?? '');
$chk = fn(?object $row, string $f) => (bool) (old($f) !== null ? old($f) : ($row->$f ?? 0));

$rbcdBlocks = [
    'h1' => ['label' => 'RBCD 1', 'period' => "janvier – juin {$year}"],
    'h2' => ['label' => 'RBCD 2', 'period' => "juillet – décembre {$year}"],
];
$forfaitBlocks = [
    'h1' => ['label' => 'Effectif 1', 'period' => "janvier – juin {$year}"],
    'h2' => ['label' => 'Effectif 2', 'period' => "juillet – décembre {$year}"],
];
?>

<form action="<?= $formAction ?>" method="post" autocomplete="off">
<?= csrf_field() ?>

<div class="row">

    <!-- ── Fédération (saison) ──────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="<?= base_url('assets/images/Ecusson_FRBB-LL.png') ?>" style="height:1.4em;width:auto;vertical-align:middle;" class="mr-2">Cotisation FRBB <small class="text-muted">(septembre <?= $season ?> – juin <?= $season + 1 ?>)</small>
                </h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="frbb_paid" value="0">
                    <input type="checkbox" class="custom-control-input paid-toggle" id="frbb_paid"
                           name="frbb_paid" value="1" data-target="#frbb_date_wrap"
                           <?= $chk($payment, 'frbb_paid') ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="frbb_paid">Cotisation payée</label>
                </div>
                <div class="form-group mb-0" id="frbb_date_wrap" <?= !$chk($payment, 'frbb_paid') ? 'style="display:none"' : '' ?>>
                    <label>Date de paiement</label>
                    <input type="date" name="frbb_paid_date" class="form-control" style="max-width:180px"
                           value="<?= esc($v($payment, 'frbb_paid_date')) ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Club RBCD (année civile) ─────────────────────────── -->
    <div class="col-lg-4">
        <?php foreach ($rbcdBlocks as $h => $b): ?>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" style="height:1.4em;width:auto;vertical-align:middle;" class="mr-2">Cotisation <?= $b['label'] ?> <small class="text-muted">(<?= $b['period'] ?>)</small>
                </h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="rbcd_<?= $h ?>_paid" value="0">
                    <input type="checkbox" class="custom-control-input paid-toggle" id="rbcd_<?= $h ?>_paid"
                           name="rbcd_<?= $h ?>_paid" value="1" data-target="#rbcd_<?= $h ?>_date_wrap"
                           <?= $chk($clubFee, "rbcd_{$h}_paid") ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="rbcd_<?= $h ?>_paid">Cotisation payée</label>
                </div>
                <div class="form-group mb-0" id="rbcd_<?= $h ?>_date_wrap" <?= !$chk($clubFee, "rbcd_{$h}_paid") ? 'style="display:none"' : '' ?>>
                    <label>Date de paiement</label>
                    <input type="date" name="rbcd_<?= $h ?>_paid_date" class="form-control" style="max-width:180px"
                           value="<?= esc($v($clubFee, "rbcd_{$h}_paid_date")) ?>">
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Forfaits billard (année civile) ──────────────────── -->
    <div class="col-lg-4">
        <?php foreach ($forfaitBlocks as $h => $b): ?>
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-id-card mr-2" style="color:#84252B;"></i>Forfait <?= $b['label'] ?> <small class="text-muted">(<?= $b['period'] ?>)</small>
                </h3>
            </div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-3">
                    <input type="hidden" name="forfait_<?= $h ?>_choice" value="0">
                    <input type="checkbox" class="custom-control-input choice-toggle"
                           id="forfait_<?= $h ?>_choice" name="forfait_<?= $h ?>_choice" value="1"
                           data-target="#forfait_<?= $h ?>_details"
                           <?= $chk($clubFee, "forfait_{$h}_choice") ? 'checked' : '' ?>>
                    <label class="custom-control-label" for="forfait_<?= $h ?>_choice">Le membre a souscrit au forfait</label>
                </div>
                <div id="forfait_<?= $h ?>_details" <?= !$chk($clubFee, "forfait_{$h}_choice") ? 'style="display:none"' : '' ?>>
                    <div class="custom-control custom-switch mb-3">
                        <input type="hidden" name="forfait_<?= $h ?>_paid" value="0">
                        <input type="checkbox" class="custom-control-input paid-toggle" id="forfait_<?= $h ?>_paid"
                               name="forfait_<?= $h ?>_paid" value="1" data-target="#forfait_<?= $h ?>_date_wrap"
                               <?= $chk($clubFee, "forfait_{$h}_paid") ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="forfait_<?= $h ?>_paid">Forfait payé</label>
                    </div>
                    <div class="form-group mb-0" id="forfait_<?= $h ?>_date_wrap" <?= !$chk($clubFee, "forfait_{$h}_paid") ? 'style="display:none"' : '' ?>>
                        <label>Date de paiement</label>
                        <input type="date" name="forfait_<?= $h ?>_paid_date" class="form-control" style="max-width:180px"
                               value="<?= esc($v($clubFee, "forfait_{$h}_paid_date")) ?>">
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<div class="row mb-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary mr-2">
            <i class="fas fa-save mr-1"></i> Enregistrer
        </button>
        <a href="<?= $backUrl ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-1"></i> Annuler
        </a>
    </div>
</div>

</form>

<?= $this->include('admin/member_payments/_supporter_cards') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    $('.paid-toggle').on('change', function () {
        $($(this).data('target')).toggle(this.checked);
    });

    $('.choice-toggle').on('change', function () {
        const $target = $($(this).data('target'));
        $target.toggle(this.checked);
        if (!this.checked) {
            $target.find('input[type=checkbox]').prop('checked', false).trigger('change');
        }
    });
});
</script>
<?= $this->include('admin/member_payments/_history_js') ?>
<?= $this->endSection() ?>
