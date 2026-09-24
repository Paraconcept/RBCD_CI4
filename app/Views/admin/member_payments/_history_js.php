<script>
$(function () {
    $(document).on('submit', '.js-open-year', function (e) {
        e.preventDefault();
        const year = parseInt($(this).find('input').val(), 10);
        if (year) window.location = $(this).data('base') + year + $(this).data('ref');
    });

    $(document).on('click', '.js-confirm-delete', function (e) {
        e.preventDefault();
        const form = $(this).data('form');
        Swal.fire({
            title: 'Supprimer ' + $(this).data('label') + ' ?',
            text: 'Cette action est irréversible.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            confirmButtonColor: '#84252B',
        }).then(result => {
            if (result.isConfirmed) $(form).submit();
        });
    });

    // ── Cartes sympathisant ──────────────────────────────────
    const $cardForm = $('#cardForm');
    const storeUrl  = $cardForm.attr('action');

    function addMonths(iso, months) {
        const [y, m, d] = iso.split('-').map(Number);
        const target    = new Date(Date.UTC(y, m - 1 + months, 1));
        const lastDay   = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate();
        target.setUTCDate(Math.min(d, lastDay));
        return target.toISOString().slice(0, 10);
    }
    const today = new Date().toISOString().slice(0, 10);

    $(document).on('click', '.js-card-new', function () {
        $cardForm[0].reset();
        $cardForm.attr('action', storeUrl);
        $('#cardModalTitle').text('Nouvelle carte sympathisant');
        $('#cm_purchase').val(today);
        $('#cm_expiry').val(addMonths(today, 6));
        $('#cardModal').modal('show');
    });

    $(document).on('click', '.js-card-edit', function () {
        const d = $(this).data();
        $cardForm[0].reset();
        $cardForm.attr('action', d.action);
        $('#cardModalTitle').text('Modifier la carte');
        $('#cm_number').val(d.number);
        $('#cm_purchase').val(d.purchase);
        $('#cm_expiry').val(d.expiry);
        $('#cm_notes').val(d.notes);
        $('#cardModal').modal('show');
    });

    $(document).on('change', '#cm_purchase', function () {
        if (this.value) $('#cm_expiry').val(addMonths(this.value, 6));
    });

    $(document).on('click', '.js-session-add', function () {
        const d = $(this).data();
        $('#sessionForm').attr('action', d.action);
        $('#sessionModalLabel').text(d.label + ' — valable jusqu\'au ' + d.max.split('-').reverse().join('/'));
        $('#sm_date').attr({ min: d.min, max: d.max })
                     .val(today < d.min ? d.min : (today > d.max ? d.max : today));
        $('#sessionModal').modal('show');
    });
});
</script>
