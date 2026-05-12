<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = ($result !== null); ?>

<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <ul class="mb-0">
      <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
        <li><?= esc($e) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
  </div>
<?php endif; ?>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-trophy mr-2"></i>
      <?= $isEdit ? 'Modifier le résultat' : 'Nouveau résultat sportif' ?>
    </h3>
  </div>

  <form method="post" enctype="multipart/form-data"
        action="<?= $isEdit ? base_url('admin/sport-results/' . $result->id . '/update') : base_url('admin/sport-results') ?>">
    <?= csrf_field() ?>

    <div class="card-body">

      <div class="row">

        <!-- Saison + Type -->
        <div class="col-md-4">
          <div class="form-group">
            <label for="season">Saison <span class="text-danger">*</span></label>
            <select name="season" id="season" class="form-control" required>
              <option value="">— Choisir une saison —</option>
              <?php
                $currentYear = (int) ANNEE_1;
                $selected    = old('season', $result->season ?? (ANNEE_1 . '-' . ANNEE_2));
                for ($y = $currentYear; $y >= 2020; $y--):
                  $s = $y . '-' . ($y + 1);
              ?>
              <option value="<?= $s ?>" <?= $selected === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="type">Type <span class="text-danger">*</span></label>
            <select name="type" id="type" class="form-control" required>
              <?php foreach (\App\Models\SportResultModel::TYPES as $val => $label): ?>
              <option value="<?= $val ?>"
                <?= old('type', $result->type ?? 'championnat') === $val ? 'selected' : '' ?>>
                <?= esc($label) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="final_date">Date de la finale</label>
            <input type="date" name="final_date" id="final_date" class="form-control"
                   value="<?= esc(old('final_date', $result->final_date ?? '')) ?>">
          </div>
        </div>

      </div>

      <!-- Titre -->
      <div class="form-group">
        <label for="title">Titre de la compétition <span class="text-danger">*</span></label>
        <input type="text" name="title" id="title" class="form-control" required
               value="<?= esc(old('title', $result->title ?? '')) ?>"
               placeholder="ex : Finale Régionale 3° Libre PF">
      </div>

      <!-- Vainqueur -->
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="winner_member_id">Vainqueur — Membre RBCD</label>
            <select name="winner_member_id" id="winner_member_id" class="form-control">
              <option value="">— Aucun membre lié —</option>
              <?php foreach ($members as $m): ?>
              <option value="<?= $m->id ?>"
                <?= (int) old('winner_member_id', $result->winner_member_id ?? 0) === $m->id ? 'selected' : '' ?>>
                <?= esc(mb_strtoupper($m->last_name) . ' ' . $m->first_name) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <small class="form-text text-muted">Si sélectionné, la photo du membre s'affichera.</small>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="winner_name">Vainqueur — Nom libre <small class="text-muted">(si pas de membre lié)</small></label>
            <input type="text" name="winner_name" id="winner_name" class="form-control"
                   value="<?= esc(old('winner_name', $result->winner_name ?? '')) ?>"
                   placeholder="ex : FANIELLE Henri">
          </div>

          <!-- Photo vainqueur (visible uniquement si aucun membre sélectionné) -->
          <div id="winner-photo-block" <?= ($result->winner_member_id ?? 0) ? 'style="display:none"' : '' ?>>
            <div class="form-group">
              <label>Photo du vainqueur <small class="text-muted">(si pas de membre lié)</small></label>
              <?php if ($isEdit && ($result->winner_photo ?? null) && !($result->winner_member_id ?? 0)): ?>
              <div class="mb-2">
                <img id="winner-photo-preview"
                     src="<?= base_url('uploads/sport_results/' . $result->winner_photo) ?>"
                     alt="Aperçu" style="max-height:80px;border-radius:50%;border:1px solid #dee2e6;">
              </div>
              <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" name="remove_winner_photo" id="remove_winner_photo" value="1" class="custom-control-input">
                <label class="custom-control-label text-danger" for="remove_winner_photo">Supprimer la photo</label>
              </div>
              <?php else: ?>
              <div class="mb-2">
                <img id="winner-photo-preview" src="" alt=""
                     style="max-height:80px;border-radius:50%;border:1px solid #dee2e6;display:none;">
              </div>
              <?php endif; ?>
              <div class="input-group">
                <div class="custom-file">
                  <input type="file" name="winner_photo" id="winner_photo" class="custom-file-input"
                         accept="image/jpeg,image/png,image/webp">
                  <label class="custom-file-label" for="winner_photo">
                    <?= ($isEdit && ($result->winner_photo ?? null)) ? 'Remplacer la photo…' : 'Choisir une photo…' ?>
                  </label>
                </div>
              </div>
              <small class="form-text text-muted">JPG, PNG ou WebP · max 3 Mo</small>
            </div>
          </div>
        </div>
      </div>

      <!-- PDF -->
      <div class="row">
        <div class="col-md-7">
          <div class="form-group">
            <label>Fichier PDF des résultats</label>
            <?php if ($isEdit && $result->pdf_file): ?>
            <div class="mb-2">
              <a href="<?= base_url('uploads/PDF/SportResults/' . $result->pdf_file) ?>"
                 target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf mr-1"></i>Voir le PDF actuel
              </a>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" name="remove_pdf" id="remove_pdf" value="1" class="custom-control-input">
              <label class="custom-control-label text-danger" for="remove_pdf">Supprimer le PDF</label>
            </div>
            <?php endif; ?>
            <div class="input-group">
              <div class="custom-file">
                <input type="file" name="pdf_file" id="pdf_file" class="custom-file-input" accept="application/pdf">
                <label class="custom-file-label" for="pdf_file">
                  <?= ($isEdit && $result->pdf_file) ? 'Remplacer le PDF…' : 'Choisir un fichier PDF…' ?>
                </label>
              </div>
            </div>
            <small class="form-text text-muted">PDF uniquement · max 10 Mo</small>
          </div>
        </div>
        <div class="col-md-5"></div>
      </div>

    </div><!-- /card-body -->

    <div class="card-footer d-flex justify-content-between">
      <a href="<?= base_url('admin/sport-results') ?>" class="btn btn-default">
        <i class="fas fa-arrow-left mr-1"></i>Retour
      </a>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i>
        <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le résultat' ?>
      </button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
  // Mise à jour libellé file input
  $('#pdf_file').on('change', function () {
    var name = this.files[0] ? this.files[0].name : '<?= ($isEdit && $result->pdf_file) ? 'Remplacer le PDF…' : 'Choisir un fichier PDF…' ?>';
    $(this).closest('.custom-file').find('.custom-file-label').text(name);
  });

  // Si membre sélectionné → masquer le bloc photo + vider le nom libre
  $('#winner_member_id').on('change', function () {
    if ($(this).val()) {
      $('#winner_name').val('').prop('placeholder', 'Nom libre inutile si membre sélectionné');
      $('#winner-photo-block').hide();
    } else {
      $('#winner_name').prop('placeholder', 'ex : Jean-Paul Wilmet');
      $('#winner-photo-block').show();
    }
  });

  // Aperçu photo vainqueur
  $('#winner_photo').on('change', function () {
    var file = this.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function (e) {
      $('#winner-photo-preview').attr('src', e.target.result).show();
    };
    reader.readAsDataURL(file);
    $(this).closest('.custom-file').find('.custom-file-label').text(file.name);
  });
});
</script>
<?= $this->endSection() ?>
