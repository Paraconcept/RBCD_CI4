<?php
use App\Filters\ScoreboardDeviceFilter as D;

// Visible uniquement d'un membre du comité connecté : c'est lui qui autorise le Chromebook ou la tablette
if (!D::isCommittee()) {
    return;
}
$device = D::device();
?>
<?php if ($device): ?>
<p class="text-secondary small mb-3"><i class="fas fa-shield-halved me-1 text-success"></i>Appareil autorisé : <b><?= esc($device->name) ?></b>.</p>
<?php else: ?>
<div class="sb-card mb-4" style="border-color:#f59e0b">
    <p class="mb-2"><i class="fas fa-shield-halved me-2 text-warning"></i><b>Cet appareil n'est pas autorisé.</b>
        Vous y avez accès parce que vous êtes connecté (comité). L'autoriser lui garde l'accès après votre déconnexion.<br>
        <b>À n'utiliser que pour les appareils du club (Chromebooks des billards, tablette du bar)</b>, jamais pour un ordinateur ou un téléphone personnel.</p>
    <form method="post" action="<?= base_url('marquoir/appareil/autoriser') ?>" class="d-flex flex-wrap gap-2">
        <?= csrf_field() ?>
        <input type="hidden" name="back" value="<?= esc(current_url()) ?>">
        <input type="text" name="name" class="form-control" style="max-width:260px" maxlength="60" required
               placeholder="Nom de l'appareil" value="<?= esc($suggestedName ?? '') ?>">
        <button class="btn btn-sb"><i class="fas fa-check me-2"></i>Autoriser cet appareil</button>
    </form>
</div>
<?php endif; ?>
