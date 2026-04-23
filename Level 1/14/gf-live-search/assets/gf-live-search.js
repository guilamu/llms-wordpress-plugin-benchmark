(function($) {
    'use strict';

    $(document).ready(function() {
        const $searchInput = $('#gf-live-search-input');
        const $tableBody = $('.wp-list-table tbody');

        if (!$searchInput.length || !$tableBody.length) {
            return;
        }

        $searchInput.on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase().trim();

            $tableBody.find('tr').each(function() {
                const $row = $(this);
                // On récupère le texte de toute la ligne (ou d'une colonne spécifique si besoin)
                const rowText = $row.text().toLowerCase();

                if (rowText.indexOf(searchTerm) > -1) {
                    $row.show();
                } else {
                    $row.hide();
                }
            });
        });
    });

})(jQuery);