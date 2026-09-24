<?php
/**
 * @var object $member
 * @var array  $clubFees  member_club_fees (année civile)
 * @var array  $payments  member_payments  (saison FRBB)
 * @var string $ref       'member_edit' | '' — page de retour après édition
 */
$refParam = $ref ? '?ref=' . $ref : '';

$paidBadge = function (bool $paid, ?string $date): string {
    if (!$paid) {
        return '<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Non payé</span>';
    }
    return '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Payé</span>'
        . ($date ? '<br><small class="text-muted">' . date('d/m/Y', strtotime($date)) . '</small>' : '');
};
$forfaitBadge = function (bool $choice, bool $paid, ?string $date) use ($paidBadge): string {
    if (!$choice) {
        return '<span class="text-muted">—</span>';
    }
    return $paid
        ? $paidBadge(true, $date)
        : '<span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i>En attente</span>';
};
?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-euro-sign mr-2"></i>Club RBCD &amp; forfaits <small class="text-muted">(année civile)</small></h3>
        <form class="ml-auto form-inline js-open-year" data-base="<?= base_url("admin/members/{$member->id}/payments/") ?>" data-ref="<?= esc($refParam) ?>">
            <input type="number" class="form-control form-control-sm mr-2" style="width:90px"
                   value="<?= date('Y') ?>" min="2000" max="2100" required>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Ajouter / modifier une année
            </button>
        </form>
    </div>
    <?php if (empty($clubFees)): ?>
    <div class="card-body">
        <div class="alert alert-info mb-0">Aucune cotisation club enregistrée pour ce membre.</div>
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>Année</th>
                    <th class="text-center">RBCD 1<br><small>jan–juin</small></th>
                    <th class="text-center">RBCD 2<br><small>juil–déc</small></th>
                    <th class="text-center">Effectif 1<br><small>jan–juin</small></th>
                    <th class="text-center">Effectif 2<br><small>juil–déc</small></th>
                    <th class="text-center" style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clubFees as $f): ?>
                <tr>
                    <td><strong><?= $f->year ?></strong></td>
                    <td class="text-center"><?= $paidBadge((bool) $f->rbcd_h1_paid, $f->rbcd_h1_paid_date) ?></td>
                    <td class="text-center"><?= $paidBadge((bool) $f->rbcd_h2_paid, $f->rbcd_h2_paid_date) ?></td>
                    <td class="text-center"><?= $forfaitBadge((bool) $f->forfait_h1_choice, (bool) $f->forfait_h1_paid, $f->forfait_h1_paid_date) ?></td>
                    <td class="text-center"><?= $forfaitBadge((bool) $f->forfait_h2_choice, (bool) $f->forfait_h2_paid, $f->forfait_h2_paid_date) ?></td>
                    <td class="text-center">
                        <a href="<?= base_url("admin/members/{$member->id}/payments/{$f->year}") . $refParam ?>"
                           class="btn btn-xs btn-info" title="Modifier"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-xs btn-danger js-confirm-delete" title="Supprimer"
                                data-form="#del-club-<?= $f->id ?>" data-label="les cotisations club <?= $f->year ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="del-club-<?= $f->id ?>" method="post" class="d-none"
                              action="<?= base_url("admin/members/{$member->id}/payments/{$f->year}/delete-club") . $refParam ?>">
                            <?= csrf_field() ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <img src="<?= base_url('assets/images/Ecusson_FRBB-LL.png') ?>" style="height:1.2em;width:auto;vertical-align:middle;" class="mr-2">Cotisation FRBB <small class="text-muted">(saison sep–juin)</small>
        </h3>
    </div>
    <?php if (empty($payments)): ?>
    <div class="card-body">
        <div class="alert alert-info mb-0">Aucune cotisation FRBB enregistrée pour ce membre.</div>
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>Saison</th>
                    <th class="text-center">FRBB</th>
                    <th class="text-center" style="width:90px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><strong><?= $p->year . '-' . ($p->year + 1) ?></strong></td>
                    <td class="text-center"><?= $paidBadge((bool) $p->frbb_paid, $p->frbb_paid_date) ?></td>
                    <td class="text-center">
                        <a href="<?= base_url("admin/members/{$member->id}/payments/{$p->year}") . $refParam ?>"
                           class="btn btn-xs btn-info" title="Modifier"><i class="fas fa-edit"></i></a>
                        <button type="button" class="btn btn-xs btn-danger js-confirm-delete" title="Supprimer"
                                data-form="#del-season-<?= $p->id ?>" data-label="la saison FRBB <?= $p->year . '-' . ($p->year + 1) ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="del-season-<?= $p->id ?>" method="post" class="d-none"
                              action="<?= base_url("admin/members/{$member->id}/payments/season/{$p->year}/delete") . $refParam ?>">
                            <?= csrf_field() ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php endif; ?>
</div>
