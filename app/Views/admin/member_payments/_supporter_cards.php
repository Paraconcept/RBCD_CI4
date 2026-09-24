<?php
/**
 * @var object $member
 * @var array  $supporterCards  SupporterCardModel::getForMember()
 * @var string $ref             'member_edit' | ''
 */
use App\Models\SupporterCardModel;

$back       = $ref === 'member_edit' ? 'member_edit' : 'payments';
$cardsUrl   = base_url("admin/members/{$member->id}/supporter-cards");
$hasActive  = (bool) array_filter($supporterCards, fn($c) => $c->status === 'active');
$maxSess    = SupporterCardModel::SESSIONS_PER_CARD;
$statusBadge = [
    'active'   => '<span class="badge badge-success">Active</span>',
    'complete' => '<span class="badge badge-secondary">Complète</span>',
    'expired'  => '<span class="badge badge-danger">Expirée</span>',
];
$d = fn(string $date) => date('d/m/Y', strtotime($date));
?>

<div class="card card-outline card-primary">
    <div class="card-header d-flex align-items-center">
        <h3 class="card-title"><i class="fas fa-id-card mr-2"></i>Cartes sympathisant
            <small class="text-muted">(<?= $maxSess ?> séances, valable <?= SupporterCardModel::VALIDITY_MONTHS ?> mois)</small>
        </h3>
        <div class="ml-auto">
            <?php if (!$member->is_supporter): ?>
                <span class="text-muted small">Membre non sympathisant</span>
            <?php elseif ($hasActive): ?>
                <span class="text-muted small">Carte active en cours</span>
            <?php else: ?>
                <button type="button" class="btn btn-primary btn-sm js-card-new">
                    <i class="fas fa-plus mr-1"></i> Nouvelle carte
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($supporterCards)): ?>
    <div class="card-body">
        <div class="alert alert-info mb-0">Aucune carte sympathisant pour ce membre.</div>
    </div>
    <?php else: ?>
    <div class="card-body p-0">
        <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped mb-0">
            <thead class="thead-rbcd">
                <tr>
                    <th>N° carte</th>
                    <th class="text-center">Achat</th>
                    <th class="text-center">Expiration</th>
                    <th>Séances</th>
                    <th class="text-center">Statut</th>
                    <th class="text-right">Montant</th>
                    <th class="text-center" style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supporterCards as $c): ?>
                <tr>
                    <td>
                        <strong><?= $c->card_number ? esc($c->card_number) : '<span class="text-muted">—</span>' ?></strong>
                        <?php if ($c->notes): ?><br><small class="text-muted"><?= esc($c->notes) ?></small><?php endif; ?>
                    </td>
                    <td class="text-center"><?= $d($c->purchase_date) ?></td>
                    <td class="text-center"><?= $d($c->expiry_date) ?></td>
                    <td>
                        <span class="text-nowrap mr-2" title="<?= $c->used ?>/<?= $maxSess ?>">
                            <?php for ($i = 1; $i <= $maxSess; $i++): ?>
                                <i class="<?= $i <= $c->used ? 'fas' : 'far' ?> fa-circle" style="color:#84252B;font-size:.8em"></i>
                            <?php endfor; ?>
                            <strong class="ml-1"><?= $c->used ?>/<?= $maxSess ?></strong>
                        </span>
                        <?php foreach ($c->sessions as $s): ?>
                            <span class="badge badge-light border mr-1 mb-1">
                                <?= $d($s->session_date) ?>
                                <a href="#" class="text-danger ml-1 js-confirm-delete" title="Supprimer la séance"
                                   data-form="#del-sess-<?= $s->id ?>" data-label="la séance du <?= $d($s->session_date) ?>">&times;</a>
                            </span>
                            <form id="del-sess-<?= $s->id ?>" method="post" class="d-none"
                                  action="<?= "{$cardsUrl}/{$c->id}/sessions/{$s->id}/delete" ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_back" value="<?= $back ?>">
                            </form>
                        <?php endforeach; ?>
                    </td>
                    <td class="text-center"><?= $statusBadge[$c->status] ?></td>
                    <td class="text-right text-nowrap"><?= number_format((float) $c->amount, 2, ',', '.') ?> €</td>
                    <td class="text-center text-nowrap">
                        <?php if ($c->status === 'active'): ?>
                        <button type="button" class="btn btn-xs btn-success js-session-add" title="Ajouter une séance"
                                data-action="<?= "{$cardsUrl}/{$c->id}/sessions" ?>"
                                data-min="<?= $c->purchase_date ?>" data-max="<?= $c->expiry_date ?>"
                                data-label="<?= esc($c->card_number ? 'carte n° ' . $c->card_number : 'carte du ' . $d($c->purchase_date)) ?>">
                            <i class="fas fa-plus"></i> séance
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-xs btn-info js-card-edit" title="Modifier"
                                data-action="<?= "{$cardsUrl}/{$c->id}/update" ?>"
                                data-number="<?= esc($c->card_number ?? '') ?>"
                                data-purchase="<?= $c->purchase_date ?>"
                                data-expiry="<?= $c->expiry_date ?>"
                                data-notes="<?= esc($c->notes ?? '') ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-danger js-confirm-delete" title="Supprimer"
                                data-form="#del-card-<?= $c->id ?>"
                                data-label="cette carte et ses <?= $c->used ?> séance(s)">
                            <i class="fas fa-trash"></i>
                        </button>
                        <form id="del-card-<?= $c->id ?>" method="post" class="d-none"
                              action="<?= "{$cardsUrl}/{$c->id}/delete" ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_back" value="<?= $back ?>">
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

<!-- Modal — carte (création / modification) -->
<div class="modal fade" id="cardModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="cardForm" method="post" action="<?= $cardsUrl ?>" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="_back" value="<?= $back ?>">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="cardModalTitle">Nouvelle carte sympathisant</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>N° de carte</label>
                        <input type="text" name="card_number" id="cm_number" class="form-control" maxlength="20" style="max-width:180px">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label>Date d'achat (paiement) <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" id="cm_purchase" class="form-control" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Date d'expiration</label>
                            <input type="date" name="expiry_date" id="cm_expiry" class="form-control">
                            <small class="form-text text-muted">Par défaut : achat + <?= SupporterCardModel::VALIDITY_MONTHS ?> mois.</small>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Remarque</label>
                        <input type="text" name="notes" id="cm_notes" class="form-control" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal — séance -->
<div class="modal fade" id="sessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <form id="sessionForm" method="post" autocomplete="off">
                <?= csrf_field() ?>
                <input type="hidden" name="_back" value="<?= $back ?>">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Nouvelle séance</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2" id="sessionModalLabel"></p>
                    <div class="form-group mb-0">
                        <label>Date de la séance <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" id="sm_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
