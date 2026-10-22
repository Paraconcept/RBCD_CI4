<?php
use App\Models\BarServiceModel;
use App\Models\BarTicketModel;

$eur  = static fn ($n): string => number_format((float) $n, 2, ',', ' ') . ' €';
$fmt  = static fn (?string $dt): string => $dt ? substr($dt, 11, 5) : '—';
$s    = $summary;
$days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$months = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$ts   = strtotime($service->service_date);
$date = ucfirst($days[(int) date('w', $ts)]) . ' ' . date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
$closed = $service->status === 'closed';
$byCategory = [];
foreach ($sales as $row) {
    $byCategory[$row->category][] = $row;
}
$short = static function (string $name): string {
    $parts = explode(' ', $name, 2);
    return isset($parts[1]) ? $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '.' : $name;
};
$methods = ['cash' => 'Espèces', 'bancontact' => 'Bancontact'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Récapitulatif <?= esc($service->envelope_code) ?></title>
<style>
@page { size: A4 portrait; margin: 12mm 13mm 11mm; }
* { box-sizing: border-box; }
body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #121826; margin: 0; background: #ebeaf2; }
.sheet { background: #fff; width: 210mm; min-height: 297mm; margin: 0 auto; padding: 12mm 13mm 11mm; }
table { border-collapse: collapse; width: 100%; }
td, th { vertical-align: top; }
.r { text-align: right; white-space: nowrap; }
.b { font-weight: bold; }
.muted { color: #5d6478; font-size: 7.8pt; }
.label { font-size: 7pt; text-transform: uppercase; letter-spacing: .7pt; }
.head td { vertical-align: middle; }
.club { font-size: 17pt; font-weight: bold; color: #0b1020; }
.doc { margin-top: 1.5mm; font-size: 10.5pt; font-weight: bold; color: #1c447c; }
.env { width: 54mm; background: #0b1020; color: #fff; text-align: center; padding: 3mm 2mm; border-bottom: 3px solid #84252B; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
.env .code { font-size: 23pt; font-weight: bold; letter-spacing: .5pt; }
.cols { margin-top: 6mm; }
.cols > tbody > tr > td { padding: 0 3mm; }
.cols > tbody > tr > td:first-child { padding-left: 0; }
.cols > tbody > tr > td:last-child { padding-right: 0; }
.h { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: .7pt; color: #1c447c; border-bottom: 1.5px solid #1c447c; padding-bottom: 1mm; margin-bottom: 1.5mm; }
.kv td { padding: .9mm 0; border-bottom: .5px solid #d8d7e3; }
.kv td:first-child { white-space: nowrap; padding-right: 2mm; }
.sum td { font-weight: bold; border-top: 1.2px solid #121826; }
.cash { border: 2px solid #121826; text-align: center; padding: 5mm 2mm; margin-top: 5mm; }
.cash .big { font-size: 25pt; font-weight: bold; margin-top: 1.5mm; }
.sales th, .tk th { font-size: 6.8pt; text-transform: uppercase; color: #5d6478; text-align: left; padding: .6mm 1.2mm .6mm 0; border-bottom: .8px solid #121826; }
.sales th.r, .tk th.r { text-align: right; }
.sales td { padding: .55mm 0; border-bottom: .5px solid #e4e3ec; }
.sales .cat td { font-weight: bold; color: #1c447c; padding-top: 1.6mm; border-bottom: 0; font-size: 7.6pt; }
.tk { font-size: 7.2pt; }
.tk td { padding: .55mm .8mm .55mm 0; border-bottom: .5px solid #e4e3ec; white-space: nowrap; }
.tk tr.x td { color: #84252B; text-decoration: line-through; }
.tk tr.x td:last-child { text-decoration: none; }
/* Grosse soirée : lignes resserrées pour garder la liste et le cadre du trésorier sur une page aussi longtemps que possible */
.tk.dense td { padding-top: .2mm; padding-bottom: .2mm; }
.tk tr { break-inside: avoid; page-break-inside: avoid; }
.treas { margin-top: 7mm; border: 1.5px solid #121826; padding: 3mm 4mm 4mm; break-inside: avoid; page-break-inside: avoid; }
.fill td { padding-top: 5mm; white-space: nowrap; padding-right: 2mm; font-weight: bold; }
.fill td.line { border-bottom: 1px dotted #121826; width: 22%; }
.foot { margin-top: 4mm; text-align: center; font-size: 7pt; color: #5d6478; }
.open-warn { margin-top: 4mm; padding: 2mm 3mm; border: 1.5px solid #d9822b; color: #84252B; font-weight: bold; }
.toolbar { position: sticky; top: 0; display: flex; gap: 10px; justify-content: center; padding: 12px; background: #0b1020; }
.toolbar button, .toolbar a { font: bold 16px Arial, sans-serif; padding: 14px 26px; border-radius: 10px; border: 0; cursor: pointer; text-decoration: none; }
.toolbar .print { background: #1c447c; color: #fff; }
.toolbar .back { background: #fff; color: #121826; }
@media print {
    body { background: #fff; }
    .sheet { width: auto; min-height: 0; margin: 0; padding: 0; }
    .toolbar { display: none; }
}
</style>
</head>
<body>
<div class="toolbar">
    <button class="print" type="button" onclick="window.print()">Imprimer</button>
    <a class="back" href="<?= base_url('caisse') ?>">Retour à la caisse</a>
</div>
<div class="sheet">
<table class="head">
    <tr>
        <td style="width:20mm"><img src="<?= base_url('assets/images/Ecusson_RBCD.png') ?>" style="width:17mm" alt=""></td>
        <td>
            <div class="club">Royal Billard Club Disonais</div>
            <div class="muted">Chalet de Bonvoisin · Av. Reine Élisabeth 46B · 4820 Dison</div>
            <div class="doc">Récapitulatif du service · caisse du bar</div>
        </td>
        <td class="env">
            <div class="label">Enveloppe</div>
            <div class="code"><?= esc($service->envelope_code) ?></div>
            <div style="font-size:8.5pt"><?= date('d/m/Y', $ts) ?> · <?= $service->period === 'am' ? 'après-midi' : 'soir' ?></div>
        </td>
    </tr>
</table>

<table class="cols"><tbody>
    <tr>
        <td style="width:31%">
            <div class="h">Service</div>
            <table class="kv">
                <tr><td>Date</td><td class="r"><?= esc($date) ?></td></tr>
                <tr><td>Ouvert par</td><td class="r"><b><?= esc(mb_strtoupper((string) $service->bartender_name, 'UTF-8')) ?></b> · <?= $fmt($service->opened_at) ?></td></tr>
                <tr><td>Clôturé par</td><td class="r"><b><?= esc(mb_strtoupper((string) ($service->closer_name ?: '—'), 'UTF-8')) ?></b> · <?= $fmt($service->closed_at) ?></td></tr>
                <?php foreach (['opening_float' => 'Fond de caisse ouverture', 'closing_float' => 'Fond de caisse clôture'] as $col => $label):
                    if ($service->$col === null) continue;
                    $floatOk = abs((float) $service->$col - BarServiceModel::OPENING_FLOAT) < 0.005; ?>
                <tr><td><?= $label ?></td><td class="r b"<?= $floatOk ? '' : ' style="color:#b45309"' ?>><?= $eur((float) $service->$col) ?><?= $floatOk ? '' : ' (≠ ' . $eur(BarServiceModel::OPENING_FLOAT) . ')' ?></td></tr>
                <?php endforeach; ?>
                <tr><td>1<sup>er</sup> / dernier ticket</td><td class="r"><?= $fmt($s['first_at']) ?> / <?= $fmt($s['last_at']) ?></td></tr>
                <tr><td>Tickets n°</td><td class="r"><?= $s['first_number'] ? BarTicketModel::formatNumber($s['first_number']) . ' à ' . BarTicketModel::formatNumber($s['last_number']) : '—' ?></td></tr>
                <tr><td>Validés / annulés</td><td class="r b"><?= $s['paid'] ?> / <?= $s['cancelled'] ?></td></tr>
            </table>
        </td>
        <td style="width:38%">
            <div class="h">Par mode de paiement</div>
            <table class="kv">
                <tr><td>Espèces <span class="muted">· <?= $s['cash_count'] ?> ticket<?= $s['cash_count'] > 1 ? 's' : '' ?></span></td><td class="r"><?= $eur($s['cash']) ?></td></tr>
                <tr><td>Bancontact SumUp <span class="muted">· <?= $s['bancontact_count'] ?></span></td><td class="r"><?= $eur($s['bancontact']) ?></td></tr>
                <tr class="sum"><td>Total des ventes</td><td class="r"><?= $eur($s['total']) ?></td></tr>
                <tr><td>21 % B (bar)</td><td class="r"><?= $eur($s['vat_b']) ?></td></tr>
                <tr><td>21 % G (gougouilles)</td><td class="r"><?= $eur($s['vat_g']) ?></td></tr>
                <tr><td>21 % D (divers)</td><td class="r"><?= $eur($s['vat_d']) ?></td></tr>
            </table>
        </td>
        <td style="width:31%">
            <div class="cash">
                <div class="label">Espèces attendues<br>dans l'enveloppe</div>
                <div class="big"><?= $eur($s['cash']) ?></div>
            </div>
        </td>
    </tr>
</tbody></table>

<?php if (!$closed): ?>
<div class="open-warn">Service encore ouvert : ces chiffres peuvent encore changer. Imprimez le récapitulatif après la clôture.</div>
<?php endif; ?>

<table class="cols"><tbody>
    <tr>
        <td style="width:45%">
            <div class="h">Ventes payées</div>
            <table class="sales">
                <tr><th>Produit · prix</th><th class="r" style="width:9mm">Qté</th><th class="r" style="width:17mm">Total</th></tr>
                <?php foreach ($byCategory as $cat => $rows): ?>
                <tr class="cat"><td colspan="3"><?= esc($cat) ?></td></tr>
                <?php foreach ($rows as $r): ?>
                <tr><td><?= esc($r->product_name) ?> <span class="muted">· <?= $eur($r->unit_price) ?></span></td><td class="r"><?= (int) $r->quantity ?></td><td class="r"><?= $eur($r->amount) ?></td></tr>
                <?php endforeach; ?>
                <?php endforeach; ?>
                <tr class="sum"><td colspan="2">Total</td><td class="r"><?= $eur($s['total']) ?></td></tr>
            </table>
        </td>
        <td style="width:55%">
            <div class="h">Tickets du service</div>
            <table class="tk<?= count($tickets) > 30 ? ' dense' : '' ?>">
                <tr><th style="width:12%">N°</th><th style="width:12%">Heure</th><th style="width:40%">Client</th><th style="width:18%">Mode</th><th class="r" style="width:18%">Montant</th></tr>
                <?php foreach ($tickets as $t): $x = $t->status !== 'paid'; ?>
                <tr class="<?= $x ? 'x' : '' ?>">
                    <td><?= BarTicketModel::formatNumber((int) $t->number) ?></td>
                    <td><?= $fmt($t->paid_at ?? $t->cancelled_at ?? $t->opened_at) ?></td>
                    <td><?= esc($short($t->client_name)) ?></td>
                    <td><?= $t->status === 'paid' ? $methods[$t->payment_method] : ($t->status === 'cancelled' ? 'Annulé' : 'Ouvert') ?></td>
                    <td class="r"><?= $t->status === 'paid' ? $eur($t->total) : '—' ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </td>
    </tr>
</tbody></table>

<div class="treas">
    <div class="h" style="border:0;margin:0 0 2mm">À remplir par le trésorier</div>
    <table class="fill"><tr>
        <td>Montant trouvé</td><td class="line"></td>
        <td>Écart</td><td class="line"></td>
        <td>Date</td><td class="line"></td>
        <td>Signature</td><td class="line"></td>
    </tr></table>
</div>

<div class="foot">Imprimé le <?= date('d/m/Y à H:i') ?><?= $closed ? ' · service clôturé : plus aucun ticket ne peut y être ajouté, modifié ou supprimé' : '' ?>. Justificatif interne du club.</div>
</div>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
