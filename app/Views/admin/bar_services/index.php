<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php
use App\Models\BarServiceModel;

$eur = static fn ($n): string => number_format((float) $n, 2, ',', ' ') . ' €';
$totals = ['cash' => 0.0, 'bc' => 0.0, 'paid' => 0];
?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-cash-register mr-2"></i>Services de la caisse <?= $year ?></h3>
    <div class="ml-auto d-flex align-items-center">
      <form method="get" class="form-inline mr-2">
        <select name="year" class="form-control form-control-sm" onchange="this.form.submit()">
          <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
        </select>
      </form>
      <a href="<?= base_url('caisse') ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt mr-1"></i>Ouvrir la caisse</a>
    </div>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Un service (après-midi ou soir) correspond à une enveloppe. Les espèces attendues sont à comparer au montant trouvé dans l'enveloppe
      (Trésorerie › Enveloppes de caisse). Le récapitulatif est le document glissé dans l'enveloppe.
      Un service d'essai (démonstration, test d'une tablette) s'efface avec <i class="fas fa-trash text-danger"></i> : seul le dernier service peut l'être, à répéter pour en effacer plusieurs.
    </p>
    <?php if (!$services): ?>
      <p class="text-muted text-center mb-0">Aucun service en <?= $year ?>.</p>
    <?php else: ?>
    <table class="table table-sm table-bordered table-hover" id="servicesTable">
      <thead class="thead-rbcd">
        <tr>
          <th>Date</th><th>Service</th><th>Enveloppe</th><th>Ouvert par</th><th>Clôturé par</th><th class="text-right">Fond de caisse</th><th class="text-center">Tickets</th>
          <th class="text-right">Espèces attendues</th><th class="text-right">Bancontact</th><th class="text-right">Total</th><th>État</th><th class="text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s):
            $totals['cash'] += (float) $s->cash_total;
            $totals['bc']   += (float) $s->bancontact_total;
            $totals['paid'] += (int) $s->paid_count;
            $open = $s->status === 'open';
        ?>
        <tr>
          <td data-order="<?= esc($s->service_date) ?>"><?= date('d/m/Y', strtotime($s->service_date)) ?></td>
          <td><?= BarServiceModel::PERIODS[$s->period] ?></td>
          <td><b><?= esc($s->envelope_code) ?></b></td>
          <td><?= esc(mb_strtoupper((string) $s->bartender_name, 'UTF-8')) ?></td>
          <td><?= $open ? '<span class="text-muted">—</span>' : esc(mb_strtoupper((string) ($s->closer_name ?: '—'), 'UTF-8')) ?></td>
          <td class="text-right"><?php if ($s->opening_float === null): ?><span class="text-muted">—</span><?php else: $floatOk = abs((float) $s->opening_float - BarServiceModel::OPENING_FLOAT) < 0.005; ?><span class="<?= $floatOk ? '' : 'text-warning font-weight-bold' ?>" title="<?= $floatOk ? '' : 'Différent de ' . esc($eur(BarServiceModel::OPENING_FLOAT)) ?>"><?= $eur($s->opening_float) ?></span><?php endif; ?></td>
          <td class="text-center"><?= (int) $s->paid_count ?><?= (int) $s->cancelled_count ? ' <span class="text-muted small">(+' . (int) $s->cancelled_count . ' annulé' . ((int) $s->cancelled_count > 1 ? 's' : '') . ')</span>' : '' ?></td>
          <td class="text-right"><b><?= $eur($s->cash_total) ?></b></td>
          <td class="text-right"><?= $eur($s->bancontact_total) ?></td>
          <td class="text-right"><?= $eur((float) $s->cash_total + (float) $s->bancontact_total) ?></td>
          <td>
            <?php if ($open): ?>
              <span class="badge badge-warning">Ouvert</span><?= !empty($s->open_tickets) ? ' <span class="small text-muted">' . (int) $s->open_tickets . ' ticket(s) en cours</span>' : '' ?>
            <?php else: ?>
              <span class="badge badge-success">Clôturé</span> <span class="small text-muted"><?= date('H:i', strtotime($s->closed_at)) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-right text-nowrap">
            <a href="<?= base_url('caisse/service/' . $s->id . '/recapitulatif') ?>" target="_blank" class="btn btn-xs btn-info" title="Récapitulatif imprimable"><i class="fas fa-print"></i></a>
            <?php if ($open): ?>
            <form method="post" action="<?= base_url('admin/caisse/services/' . $s->id . '/cloturer') ?>" class="d-inline"
                  onsubmit="return confirm('Clôturer le service <?= esc($s->envelope_code, 'js') ?> ? Plus aucun ticket ne pourra y être ajouté ni modifié.')">
              <?= csrf_field() ?><button class="btn btn-xs btn-warning" title="Clôturer ce service oublié"><i class="fas fa-lock"></i></button>
            </form>
            <?php endif; ?>
            <?php if ((int) $s->id === $lastId): ?>
            <form method="post" action="<?= base_url('admin/caisse/services/' . $s->id . '/supprimer') ?>" class="d-inline"
                  onsubmit="return confirm('Supprimer le service <?= esc($s->envelope_code, 'js') ?> du <?= date('d/m/Y', strtotime($s->service_date)) ?> ?\n\nSes <?= (int) $s->paid_count + (int) $s->cancelled_count + (int) ($s->open_tickets ?? 0) ?> ticket(s) seront effacés définitivement, ainsi que les clients créés pendant ce service.\n\nÀ réserver aux services d\'essai : un vrai service doit rester pour la trésorerie.')">
              <?= csrf_field() ?><button class="btn btn-xs btn-danger" title="Supprimer ce service d'essai (le dernier seulement)"><i class="fas fa-trash"></i></button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="font-weight-bold">
          <td colspan="6">Total <?= $year ?></td>
          <td class="text-center"><?= $totals['paid'] ?></td>
          <td class="text-right"><?= $eur($totals['cash']) ?></td>
          <td class="text-right"><?= $eur($totals['bc']) ?></td>
          <td class="text-right"><?= $eur($totals['cash'] + $totals['bc']) ?></td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
