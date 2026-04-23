(function($) {
    'use strict';

    function filterForms(search) {
        var $rows = $('#form-list tbody tr').not('.header-row, .footer-row, .empty-row');

        if (!search) {
            $rows.show();
            return;
        }

        search = search.toLowerCase();

        $rows.each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(search) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $(document).on('ready', function() {
        var $input = $('#gf-live-search-input');

        if (!$input.length) return;

        $input.on('input', function() {
            filterForms($(this).val());
        });
    });

})(jQuery);
