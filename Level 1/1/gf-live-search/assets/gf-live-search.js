/**
 * GF Live Search JavaScript
 */
(function($) {
    'use strict';

    $(function() {
        const $table = $('.wp-list-table');
        if (!$table.length) return;

        // 1. Create and inject search input
        const $container = $('<div id="gf-live-search-container"></div>');
        const $input = $('<input type="text" id="gf-live-search-input" autocomplete="off" />')
            .attr('placeholder', gfLiveSearchL10n.placeholder);
        
        $container.append($input);

        // Inject above the table, usually after the page title or in the tablenav
        const $header = $('.wrap h1.wp-heading-inline');
        if ($header.length) {
            $container.insertAfter($header);
        } else {
            $('.tablenav.top').prepend($container);
        }

        const $rows = $table.find('tbody tr:not(.no-items)');
        const $tbody = $table.find('tbody');
        let $noResultsMsg = $('<tr id="gf-live-search-no-results"><td colspan="100%">' + gfLiveSearchL10n.noResults + '</td></tr>').hide();
        
        $tbody.append($noResultsMsg);

        // 2. Filter logic
        $input.on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            let visibleCount = 0;

            $rows.each(function() {
                const $row = $(this);
                // Search in Title and ID columns
                const text = $row.find('.column-title, .column-id').text().toLowerCase();
                
                if (text.indexOf(searchTerm) > -1) {
                    $row.removeClass('gf-live-search-hidden');
                    visibleCount++;
                } else {
                    $row.addClass('gf-live-search-hidden');
                }
            });

            // Handle no results
            if (visibleCount === 0 && searchTerm !== '') {
                $noResultsMsg.show();
            } else {
                $noResultsMsg.hide();
            }

            // Update Zebra striping (optional but nice)
            $rows.filter(':visible').each(function(index) {
                $(this).removeClass('alternate');
                if (index % 2 !== 0) {
                    $(this).addClass('alternate');
                }
            });
        });
    });

})(jQuery);
