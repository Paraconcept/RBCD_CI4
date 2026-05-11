<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= session()->getFlashdata('success') ?>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-coins mr-2"></i>Paramètres financiers</h3>
  </div>
  <form method="post" action="<?= base_url('admin/treasury/settings/save') ?>">
    <?= csrf_field() ?>
    <div class="card-body">

      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="annual_cotisation">Cotisation annuelle RBCD</label>
            <div class="input-group">
              <input type="number" name="annual_cotisation" id="annual_cotisation"
                     class="form-control" step="0.01" min="0"
                     value="<?= number_format((float)($settings->annual_cotisation ?? 50), 2, '.', '') ?>">
              <div class="input-group-append">
                <span class="input-group-text">€</span>
              </div>
            </div>
            <small class="form-text text-muted">Utilisée sur la page École et dans l'état des paiements.</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="forfait_price">Forfait billard (par semestre)</label>
            <div class="input-group">
              <input type="number" name="forfait_price" id="forfait_price"
                     class="form-control" step="0.01" min="0"
                     value="<?= number_format((float)($settings->forfait_price ?? 75), 2, '.', '') ?>">
              <div class="input-group-append">
                <span class="input-group-text">€</span>
              </div>
            </div>
            <small class="form-text text-muted">Forfait H1 (jan–jun) et H2 (jul–déc).</small>
          </div>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-2"></i>Enregistrer
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
