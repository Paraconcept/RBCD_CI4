<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php
use App\Models\BarServiceModel;
use App\Models\BarTicketModel;
use App\Models\BarTicketPaymentModel;

$eur = static fn ($n): string => number_format((float) $n, 2, ',', ' ') . ' €';
$s   = $summary;
?>

<div class="card card-outline card-primary">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-receipt mr-2"></i>Service <?= esc($service->envelope_code) ?> · <?= date('d/m/Y', strtotime($service->service_date)) ?> · <?= BarServiceModel::PERIODS[$service->period] ?></h3>
    <div class="ml-auto">
      <a href="<?= base_url('caisse/service/' . $service->id . '/recapitulatif') ?>" target="_blank" class="btn btn-sm btn-info mr-2"><i class="fas fa-print mr-1"></i>Récapitulatif</a>
      <a href="<?= base_url('admin/caisse/services?year=' . substr($service->service_date, 0, 4)) ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i>Services</a>
    </div>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-3"><div class="small text-muted">Espèces attendues</div><div class="h4 mb-0 font-weight-bold"><?= $eur($s['cash']) ?></div><div class="small text-muted"><?= $s['cash_count'] ?> paiement(s)</div></div>
      <div class="col-md-3"><div class="small text-muted">Bancontact SumUp</div><div class="h4 mb-0"><?= $eur($s['bancontact']) ?></div><div class="small text-muted"><?= $s['bancontact_count'] ?> paiement(s)</div></div>
      <div class="col-md-3"><div class="small text-muted">Total encaissé</div><div class="h4 mb-0"><?= $eur($s['total']) ?></div><?= $s['surplus'] > 0 ? '<div class="small text-warning">dont ' . $eur($s['surplus']) . ' d\'arrondis / pourboires</div>' : '' ?><div class="small text-muted"><?= $s['paid'] ?> ticket(s) payé(s)<?= $s['split'] ? ', dont ' . $s['split'] . ' partagé(s)' : '' ?></div></div>
      <div class="col-md-3"><div class="small text-muted">État</div>
        <?= $service->status === 'open' ? '<span class="badge badge-warning">Ouvert</span>' : '<span class="badge badge-success">Clôturé</span>' ?>
        <div class="small text-muted">Barman : <?= esc(mb_strtoupper((string) $service->bartender_name, 'UTF-8')) ?></div>
      </div>
    </div>
    <p class="text-muted small">
      Un paiement mal encodé (mauvais bouton, ticket d'équipe payé en partie en espèces et en partie par Bancontact) se corrige avec <i class="fas fa-edit"></i>.
      Le contenu du ticket ne change pas ; les totaux du service sont recalculés<?= $service->status === 'closed' ? ', y compris les espèces attendues de l\'enveloppe' : '' ?>. La correction reste notée sur le ticket.
    </p>
    <table class="table table-sm table-bordered table-hover">
      <thead class="thead-rbcd">
        <tr><th width="70">N°</th><th width="70">Heure</th><th>Client</th><th>Consommations</th><th>Paiement</th><th width="100" class="text-right">Montant</th><th width="60" class="text-right"></th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t):
            $parts = $payments[(int) $t->id] ?? [];
            $items = implode(', ', array_map(static fn ($l) => (int) $l->quantity . ' × ' . $l->product_name, $lines[(int) $t->id] ?? []));
            $paid  = $t->status === 'paid';
        ?>
        <tr id="t<?= $t->id ?>" class="<?= $paid ? '' : 'text-muted' ?>">
          <td><?= BarTicketModel::formatNumber((int) $t->number) ?></td>
          <td><?= substr((string) ($t->paid_at ?? $t->cancelled_at ?? $t->opened_at), 11, 5) ?></td>
          <td><?= esc($t->client_name) ?></td>
          <td class="small"><?= esc($items) ?: '—' ?></td>
          <td>
            <?php if ($paid): ?>
              <?= count($parts) > 1 ? '<span class="badge badge-info mr-1">' . count($parts) . ' parts</span>' : '' ?><?= esc(BarTicketPaymentModel::describe($parts)) ?>
              <?php $over = array_sum(array_map(static fn ($p) => (float) $p->amount, $parts)) - (float) $t->total; if ($over > 0.004): ?>
              <span class="badge badge-warning">+<?= $eur($over) ?> d'arrondi</span>
              <?php endif; ?>
              <?php if ($t->payment_corrected_at): ?>
              <div class="small text-warning"><i class="fas fa-history mr-1"></i>Corrigé le <?= date('d/m/Y à H:i', strtotime($t->payment_corrected_at)) ?><?= isset($correctors[(int) $t->payment_corrected_by_member_id]) ? ' par ' . esc($correctors[(int) $t->payment_corrected_by_member_id]) : '' ?></div>
              <?php endif; ?>
            <?php elseif ($t->status === 'cancelled'): ?>
              Annulé<?= $t->cancel_reason ? ' · ' . esc($t->cancel_reason) : '' ?>
            <?php else: ?>
              <span class="badge badge-warning">Ouvert</span>
            <?php endif; ?>
          </td>
          <td class="text-right"><?= $paid ? '<b>' . $eur($t->total) . '</b>' : '—' ?></td>
          <td class="text-right">
            <?php if ($paid): ?>
            <button type="button" class="btn btn-xs btn-warning" title="Corriger le paiement" data-toggle="modal" data-target="#payFix"
                    data-id="<?= $t->id ?>" data-number="<?= BarTicketModel::formatNumber((int) $t->number) ?>" data-client="<?= esc($t->client_name) ?>"
                    data-total="<?= number_format((float) $t->total, 2, '.', '') ?>"
                    data-parts="<?= esc(json_encode(array_map(static fn ($p) => ['method' => $p->method, 'amount' => (float) $p->amount], $parts))) ?>">
              <i class="fas fa-edit"></i>
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$tickets): ?><tr><td colspan="7" class="text-center text-muted">Aucun ticket sur ce service.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="payFix" tabindex="-1">
  <div class="modal-dialog">
    <form method="post" class="modal-content" id="payFixForm">
      <?= csrf_field() ?>
      <div class="modal-header"><h5 class="modal-title">Corriger le paiement du ticket n° <span id="pfNumber"></span></h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
      <div class="modal-body">
        <p class="mb-2"><span id="pfClient"></span> · total <b id="pfTotal"></b></p>
        <div class="form-inline mb-3">
          <label class="mr-2">Partager en</label>
          <select id="pfN" class="form-control form-control-sm mr-2"><?php for ($n = 1; $n <= 8; $n++): ?><option value="<?= $n ?>"><?= $n ?> part<?= $n > 1 ? 's' : '' ?></option><?php endfor; ?></select>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="pfEqual">Parts égales</button>
        </div>
        <table class="table table-sm mb-2"><tbody id="pfRows"></tbody></table>
        <div id="pfMsg" class="small"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
        <button class="btn btn-warning" id="pfSave"><i class="fas fa-save mr-1"></i>Enregistrer la correction</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
  const fmt = c => (c / 100).toFixed(2).replace('.', ',');
  const cents = v => Math.round(parseFloat(String(v).replace(',', '.')) * 100) || 0;
  let total = 0;

  function row(i, p) {
    return '<tr><td class="align-middle font-weight-bold" width="70">Part ' + (i + 1) + '</td>' +
      '<td><select name="method[]" class="form-control form-control-sm">' +
      '<option value="cash"' + (p.method === 'cash' ? ' selected' : '') + '>Espèces</option>' +
      '<option value="bancontact"' + (p.method === 'bancontact' ? ' selected' : '') + '>Bancontact</option></select></td>' +
      '<td width="130"><div class="input-group input-group-sm"><input type="text" name="amount[]" class="form-control text-right" inputmode="decimal" value="' + fmt(p.c) + '" required>' +
      '<div class="input-group-append"><span class="input-group-text">€</span></div></div></td></tr>';
  }

  function current() {
    return Array.from(document.querySelectorAll('#pfRows tr')).map(tr => ({ method: tr.querySelector('select').value, c: cents(tr.querySelector('input').value) }));
  }

  function draw(parts) {
    document.getElementById('pfRows').innerHTML = parts.map((p, i) => row(i, p)).join('');
    document.getElementById('pfN').value = parts.length;
    check();
  }

  // Le centime qui ne se partage pas va aux premières parts, comme à la caisse
  function equal(n, methods) {
    const base = Math.floor(total / n), extra = total % n;
    return Array.from({ length: n }, (_, i) => ({ method: methods[i] || 'cash', c: base + (i < extra ? 1 : 0) }));
  }

  // Payé en plus du ticket (arrondi en faveur du club) : permis jusqu'à MAX, comme à la caisse
  const MAX = <?= \App\Libraries\BarRegister::MAX_SURPLUS * 100 ?>;
  function check() {
    const parts = current(), sum = parts.reduce((a, p) => a + p.c, 0), diff = sum - total;
    const zero = parts.some(p => p.c <= 0);
    const ok = !zero && diff >= 0 && diff <= MAX;
    const msg = document.getElementById('pfMsg');
    msg.className = 'small font-weight-bold ' + (!ok ? 'text-danger' : diff ? 'text-warning' : 'text-success');
    msg.textContent = zero ? 'Chaque part doit avoir un montant.'
      : diff < 0 ? 'Il manque ' + fmt(-diff) + ' € : les parts font ' + fmt(sum) + ' € pour un ticket de ' + fmt(total) + ' €.'
      : diff > MAX ? 'Plus de ' + fmt(MAX) + ' € de trop : vérifiez les montants.'
      : diff ? fmt(diff) + ' € de plus que le ticket : arrondi en faveur du club.'
      : 'Les parts font bien le total du ticket.';
    document.getElementById('pfSave').disabled = !ok;
  }

  $('#payFix').on('show.bs.modal', function (e) {
    const b = e.relatedTarget.dataset;
    total = cents(b.total);
    document.getElementById('payFixForm').action = '<?= base_url('admin/caisse/tickets') ?>/' + b.id + '/paiement';
    document.getElementById('pfNumber').textContent = b.number;
    document.getElementById('pfClient').textContent = b.client;
    document.getElementById('pfTotal').textContent = fmt(total) + ' €';
    const parts = JSON.parse(b.parts || '[]').map(p => ({ method: p.method, c: Math.round(p.amount * 100) }));
    draw(parts.length ? parts : [{ method: 'cash', c: total }]);
  });
  document.getElementById('pfN').addEventListener('change', function () { draw(equal(+this.value, current().map(p => p.method))); });
  document.getElementById('pfEqual').addEventListener('click', () => draw(equal(current().length, current().map(p => p.method))));
  document.getElementById('pfRows').addEventListener('input', check);
})();
</script>
<?= $this->endSection() ?>
