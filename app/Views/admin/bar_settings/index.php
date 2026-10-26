<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>
<?php use App\Models\BarTicketModel; ?>

<?php if (session()->getFlashdata('warning')): ?>
<div class="alert alert-warning" style="max-width:760px"><i class="icon fas fa-exclamation-triangle"></i> <?= esc(session()->getFlashdata('warning')) ?></div>
<?php endif; ?>

<div class="card card-outline card-primary" style="max-width:760px">
  <div class="card-header d-flex align-items-center">
    <h3 class="card-title"><i class="fas fa-hashtag mr-2"></i>Numérotation des tickets</h3>
    <div class="ml-auto"><a href="<?= base_url('caisse') ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-cash-register mr-1"></i>Ouvrir la caisse</a></div>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Pour continuer la numérotation des tickets papier, indiquez le numéro que doit porter le <b>premier ticket</b> de la caisse.
      Les numéros se suivent ensuite sans trou. Un numéro déjà utilisé n'est jamais réattribué : si vous indiquez un numéro
      plus petit que le dernier ticket, la caisse continue simplement après celui-ci.
    </p>

    <table class="table table-sm table-bordered mb-4" style="max-width:420px">
      <tbody>
        <tr><th class="bg-light" style="width:60%">Dernier ticket enregistré</th><td class="text-right"><?= $last ? 'n° ' . BarTicketModel::formatNumber($last) : '<span class="text-muted">aucun</span>' ?></td></tr>
        <tr><th class="bg-light">Prochain ticket</th><td class="text-right"><b class="text-primary" style="font-size:1.15rem">n° <?= BarTicketModel::formatNumber($next) ?></b></td></tr>
      </tbody>
    </table>

    <form method="post" action="<?= base_url('admin/caisse/parametres') ?>" class="form-inline">
      <?= csrf_field() ?>
      <label for="ticket_start" class="mr-2 font-weight-bold">Numéro du premier ticket</label>
      <input type="number" min="1" max="99999999" step="1" name="ticket_start" id="ticket_start" class="form-control mr-2" style="width:150px"
             value="<?= esc(old('ticket_start', (string) $start)) ?>" required>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
    </form>
    <small class="text-muted d-block mt-2">Le changement vaut pour le prochain ticket créé à la caisse, même pendant un service en cours.</small>
  </div>
</div>

<div class="card card-outline card-primary" style="max-width:760px">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-image mr-2"></i>Photos des produits</h3>
  </div>
  <div class="card-body">
    <p class="text-muted">
      Chaque touche de la caisse peut montrer la photo du produit à droite du nom et du prix. Les photos se gèrent dans
      <a href="<?= base_url('admin/caisse/catalogue') ?>">Caisse › Catalogue</a> ; les masquer ici ne les efface pas.
    </p>
    <form method="post" action="<?= base_url('admin/caisse/parametres/photos') ?>">
      <?= csrf_field() ?>
      <div class="custom-control custom-switch mb-3">
        <input type="checkbox" class="custom-control-input" id="show_images" name="show_images" value="1" <?= $showImages ? 'checked' : '' ?>>
        <label class="custom-control-label font-weight-bold" for="show_images">Afficher les photos sur les touches de la caisse</label>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Enregistrer</button>
    </form>
    <small class="text-muted d-block mt-2">La tablette applique le changement au prochain rechargement de la caisse (ou à la prochaine ouverture).</small>
  </div>
</div>

<?= $this->endSection() ?>
