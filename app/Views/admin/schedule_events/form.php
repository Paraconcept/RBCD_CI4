<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<div class="card card-outline card-primary" style="max-width:640px;">
    <div class="card-header">
        <h3 class="card-title mb-0">
            <i class="fas fa-calendar-plus mr-2"></i><?= esc($title) ?>
        </h3>
    </div>
    <div class="card-body">

        <?php
        $isEdit  = $event !== null;
        $action  = $isEdit
            ? base_url("admin/schedule-events/{$event->id}/update")
            : base_url('admin/schedule-events');
        ?>

        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>Date <span class="text-danger">*</span></label>
                <input type="date" name="event_date" class="form-control"
                       value="<?= esc($event->event_date ?? date('Y-m-d')) ?>" required>
            </div>

            <div class="form-group">
                <label>Heure de début <small class="text-muted">(optionnel)</small></label>
                <input type="time" name="start_time" class="form-control"
                       value="<?= esc($event?->start_time ? substr($event->start_time, 0, 5) : '') ?>">
            </div>

            <div class="form-group">
                <label>Titre <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control"
                       value="<?= esc($event?->title ?? '') ?>"
                       placeholder="Ex. Réunion de comité" maxlength="150" required>
            </div>

            <div class="form-group">
                <label>Description / Note <small class="text-muted">(optionnel)</small></label>
                <input type="text" name="description" class="form-control"
                       value="<?= esc($event?->description ?? '') ?>"
                       placeholder="Ex. Local fermé dès 19h30">
            </div>

            <div class="form-group">
                <label>Couleur / Catégorie</label>
                <div class="d-flex flex-wrap" style="gap:.6rem;">
                    <?php foreach ($colors as $key => $c): ?>
                    <label class="mb-0" style="cursor:pointer;">
                        <input type="radio" name="color" value="<?= $key ?>"
                               <?= ($event?->color ?? 'blue') === $key ? 'checked' : '' ?>
                               style="display:none;" class="color-radio">
                        <span class="color-chip d-flex align-items-center gap-1 px-3 py-2"
                              style="border-radius:6px;border:2px solid <?= $c['border'] ?>;
                                     background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>;
                                     font-size:.83rem;font-weight:600;white-space:nowrap;
                                     transition:box-shadow .15s;">
                            <span style="display:inline-block;width:10px;height:10px;
                                         border-radius:50%;background:<?= $c['border'] ?>;"></span>
                            <?= esc($c['label']) ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Enregistrer
                </button>
                <a href="<?= base_url('admin/schedule-events') ?>" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.color-radio').forEach(function(radio) {
    function update() {
        document.querySelectorAll('.color-chip').forEach(function(chip) {
            chip.style.boxShadow = '';
            chip.style.opacity   = '.65';
        });
        if (radio.checked) {
            radio.nextElementSibling.style.boxShadow = '0 0 0 3px rgba(0,0,0,.25)';
            radio.nextElementSibling.style.opacity   = '1';
        }
    }
    radio.addEventListener('change', function() {
        document.querySelectorAll('.color-radio').forEach(function(r) {
            r.nextElementSibling.style.boxShadow = '';
            r.nextElementSibling.style.opacity   = '.65';
        });
        radio.nextElementSibling.style.boxShadow = '0 0 0 3px rgba(0,0,0,.25)';
        radio.nextElementSibling.style.opacity   = '1';
    });
    if (radio.checked) {
        radio.nextElementSibling.style.opacity = '1';
    } else {
        radio.nextElementSibling.style.opacity = '.65';
    }
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
