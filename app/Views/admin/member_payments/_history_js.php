<script>
$(function () {
    $(document).on('submit', '.js-open-year', function (e) {
        e.preventDefault();
        const year = parseInt($(this).find('input').val(), 10);
        if (year) window.location = $(this).data('base') + year + $(this).data('ref');
    });

    $(document).on('click', '.js-confirm-delete', function () {
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
});
</script>
