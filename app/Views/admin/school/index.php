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
    <h3 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i>Paramètres de l'École de Billard</h3>
  </div>
  <form method="post" action="<?= base_url('admin/school/save') ?>">
    <?= csrf_field() ?>
    <div class="card-body">

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="teacher_member_id">Professeur</label>
            <select name="teacher_member_id" id="teacher_member_id" class="form-control">
              <option value="">— Aucun —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ($settings && $settings->teacher_member_id == $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="contact_member_id">Personne de contact (inscriptions)</label>
            <select name="contact_member_id" id="contact_member_id" class="form-control">
              <option value="">— Aucun —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= ($settings && $settings->contact_member_id == $m->id) ? 'selected' : '' ?>>
                <?= esc($m->last_name . ' ' . $m->first_name) ?>
                <?php if ($m->mobile): ?>
                  — <?= esc($m->mobile) ?>
                <?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Le GSM affiché sur la page publique est celui de la fiche membre.</small>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <div class="form-group">
            <label for="schedule">Horaire des cours</label>
            <input type="text" name="schedule" id="schedule" class="form-control"
                   value="<?= esc($settings->schedule ?? '') ?>"
                   placeholder="Ex : Samedi, 10h00 — 12h00">
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-group">
            <label for="frequency_per_month">Séances par mois</label>
            <input type="number" name="frequency_per_month" id="frequency_per_month"
                   class="form-control" min="1" max="20"
                   value="<?= esc($settings->frequency_per_month ?? 4) ?>">
          </div>
        </div>
      </div>

    </div>
    <div class="card-footer">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-3"></i>Enregistrer
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
