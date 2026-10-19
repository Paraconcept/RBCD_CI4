<?php
use App\Models\BarServiceModel;
use App\Models\BarTicketModel;

$eur   = static fn ($n): string => number_format((float) $n, 2, ',', ' ') . ' €';
$count = array_sum(array_map(static fn ($l) => (int) $l->quantity, $lines));
$status = ['paid' => 'Payé en <b>' . ($ticket->payment_method === 'bancontact' ? 'Bancontact' : 'espèces') . '</b>', 'cancelled' => '<b>Ticket annulé</b>', 'open' => '<b>Non réglé</b>'][$ticket->status];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ticket <?= BarTicketModel::formatNumber((int) $ticket->number) ?></title>
<style>
@page { size: A4 portrait; margin: 12mm; }
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; color: #000; margin: 0; background: #ebeaf2; }
.ticket { background: #fff; width: 80mm; margin: 20px auto; padding: 5mm 5mm 7mm; }
.c { text-align: center; }
.club { font-size: 12pt; font-weight: bold; margin-top: 1mm; }
.small { font-size: 8pt; }
.rule { border-top: 1px dashed #000; margin: 2.5mm 0; }
.rule2 { border-top: 2px solid #000; margin: 2.5mm 0; }
table { width: 100%; border-collapse: collapse; }
td { padding: .6mm 0; vertical-align: top; }
.r { text-align: right; white-space: nowrap; }
.label { font-size: 7.5pt; text-transform: uppercase; letter-spacing: .6pt; }
.num { font-size: 17pt; font-weight: bold; }
.name { font-size: 14pt; font-weight: bold; text-transform: uppercase; }
.total { font-size: 17pt; font-weight: bold; }
.toolbar { display: flex; gap: 10px; justify-content: center; padding: 12px; background: #0b1020; }
.toolbar button, .toolbar a { font: bold 16px Arial, sans-serif; padding: 14px 26px; border-radius: 10px; border: 0; cursor: pointer; text-decoration: none; }
.toolbar .print { background: #1c447c; color: #fff; }
.toolbar .back { background: #fff; color: #121826; }
@media print {
    body { background: #fff; }
    .ticket { margin: 0; padding: 0; }
    .toolbar { display: none; }
}
</style>
</head>
<body>
<div class="toolbar">
    <button class="print" type="button" onclick="window.print()">Imprimer</button>
    <a class="back" href="<?= base_url('caisse') ?>">Retour à la caisse</a>
</div>
<div class="ticket">
    <div class="c">
        <img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" style="width:15mm" alt="">
        <div class="club">Royal Billard Club Disonais</div>
        <div class="small">Chalet de Bonvoisin · Av. Reine Élisabeth 46B<br>4820 Dison</div>
    </div>
    <div class="rule"></div>
    <table>
        <tr><td><span class="label">Ticket n°</span><br><span class="num"><?= BarTicketModel::formatNumber((int) $ticket->number) ?></span></td>
            <td class="r"><span class="label">Bar · <?= esc(BarServiceModel::periodLabel($service->period)) ?></span><br><?= date('d/m/Y', strtotime($service->service_date)) ?></td></tr>
    </table>
    <div class="c" style="margin-top:1.5mm"><span class="name"><?= esc($ticket->client_name) ?></span></div>
    <div class="rule"></div>
    <table>
        <?php foreach ($lines as $l): ?>
        <tr><td><?= (int) $l->quantity ?> × <?= esc($l->product_name) ?><br><span class="small"><?= $eur($l->unit_price) ?> l'unité</span></td><td class="r"><?= $eur($l->unit_price * $l->quantity) ?></td></tr>
        <?php endforeach; ?>
    </table>
    <div class="rule2"></div>
    <table>
        <tr><td style="font-size:12pt;font-weight:bold">TOTAL</td><td class="r total"><?= $eur($ticket->total) ?></td></tr>
        <tr><td><?= $count ?> article<?= $count > 1 ? 's' : '' ?></td><td class="r"><?= $status ?></td></tr>
    </table>
    <div class="rule"></div>
    <table class="small">
        <tr><td>Ouvert à <?= substr((string) $ticket->opened_at, 11, 5) ?></td><td class="r"><?= $ticket->paid_at ? 'Réglé à ' . substr((string) $ticket->paid_at, 11, 5) : '' ?></td></tr>
        <tr><td>Enveloppe <?= esc($service->envelope_code) ?></td><td class="r"><?= $service->bartender_name ? 'Barman : ' . esc(mb_strtoupper($service->bartender_name, 'UTF-8')) : '' ?></td></tr>
    </table>
    <div class="rule"></div>
    <div class="c small">Merci et bonne partie !<br>Justificatif interne du club, ne tient pas lieu de facture.</div>
</div>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
