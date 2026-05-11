<?= $this->extend('admin/layouts/main') ?>
<?= $this->section('content') ?>

<form method="post" action="<?= base_url('admin/opening-hours/save') ?>">
    <?= csrf_field() ?>

    <div class="card card-outline card-primary">
        <div class="card-header d-flex align-items-center">
            <h3 class="card-title"><i class="fas fa-clock mr-2"></i>Heures d'ouverture</h3>
            <div class="ml-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-rbcd">
                        <tr>
                            <th style="width:110px">Jour</th>
                            <th class="text-center" style="width:80px">Fermé</th>
                            <th class="text-center">Matin</th>
                            <th class="text-center">Après-midi</th>
                            <th class="text-center">Soir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hours as $h): ?>
                        <tr class="day-row <?= $h->is_closed ? 'row-closed' : '' ?>" data-day="<?= $h->day_order ?>">
                            <td class="align-middle font-weight-bold"><?= esc($h->day_name) ?></td>
                            <td class="text-center align-middle">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox"
                                           class="custom-control-input closed-toggle"
                                           id="closed_<?= $h->day_order ?>"
                                           name="hours[<?= $h->day_order ?>][is_closed]"
                                           value="1"
                                           <?= $h->is_closed ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="closed_<?= $h->day_order ?>"></label>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center justify-content-center gap-1 time-inputs">
                                    <input type="time" name="hours[<?= $h->day_order ?>][morning_open]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->morning_open ? substr($h->morning_open, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                    <span class="text-muted mx-1">—</span>
                                    <input type="time" name="hours[<?= $h->day_order ?>][morning_close]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->morning_close ? substr($h->morning_close, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center justify-content-center gap-1 time-inputs">
                                    <input type="time" name="hours[<?= $h->day_order ?>][afternoon_open]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->afternoon_open ? substr($h->afternoon_open, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                    <span class="text-muted mx-1">—</span>
                                    <input type="time" name="hours[<?= $h->day_order ?>][afternoon_close]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->afternoon_close ? substr($h->afternoon_close, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                </div>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center justify-content-center gap-1 time-inputs">
                                    <input type="time" name="hours[<?= $h->day_order ?>][evening_open]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->evening_open ? substr($h->evening_open, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                    <span class="text-muted mx-1">—</span>
                                    <input type="time" name="hours[<?= $h->day_order ?>][evening_close]"
                                           class="form-control form-control-sm time-field" style="width:110px"
                                           value="<?= $h->evening_close ? substr($h->evening_close, 0, 5) : '' ?>"
                                           <?= $h->is_closed ? 'disabled' : '' ?>>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Enregistrer les horaires
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.row-closed td { background: #f8f9fa; }
.row-closed .time-inputs { opacity: .4; }
.gap-1 { gap: .25rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
    $('.closed-toggle').on('change', function () {
        const day     = $(this).closest('tr');
        const inputs  = day.find('.time-field');
        const closed  = this.checked;

        day.toggleClass('row-closed', closed);
        day.find('.time-inputs').css('opacity', closed ? '.4' : '1');
        inputs.prop('disabled', closed);
    });
});
</script>
<?= $this->endSection() ?>
