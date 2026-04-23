jQuery(document).ready(function($) {
    // Wait for the DOM to be ready and check for the Gravity Forms list table
    var $table = $('table.wp-list-table.forms');
    
    // If we're not on a page with the forms table, do nothing
    if ($table.length === 0) {
        return;
    }

    // Prepare the search HTML
    var searchHTML = '<div class="gf-live-search-wrapper">' +
        '<input type="search" id="gf-live-search-input" placeholder="' + gfLiveSearchData.searchPlaceholder + '" />' +
        '</div>';
    
    // Insert the search input above the table navigation (top)
    $('.tablenav.top').before(searchHTML);

    var $searchInput = $('#gf-live-search-input');
    var $tbody = $table.find('tbody');
    var colspan = $table.find('thead th').length || 6; // Fallback to 6 columns if unable to find

    // Event listener for the input field
    $searchInput.on('input', function() {
        var searchTerm = $(this).val().toLowerCase().trim();
        var visibleCount = 0;
        var $rows = $table.find('tbody tr').not('.gf-live-search-no-results');

        // Remove any existing "no results" message
        $('.gf-live-search-no-results').remove();

        if (searchTerm === '') {
            // Show all rows if search is empty
            $rows.removeClass('gf-live-search-hidden');
        } else {
            // Loop through each row to match the search term
            $rows.each(function() {
                var $row = $(this);
                // We extract text from the row. This makes it search across title, ID, views, etc.
                var rowText = $row.text().toLowerCase();

                if (rowText.indexOf(searchTerm) > -1) {
                    $row.removeClass('gf-live-search-hidden');
                    visibleCount++;
                } else {
                    $row.addClass('gf-live-search-hidden');
                }
            });

            // If no rows match, display the no results message
            if (visibleCount === 0) {
                $tbody.append('<tr class="gf-live-search-no-results no-items"><td colspan="' + colspan + '">' + gfLiveSearchData.noResults + '</td></tr>');
            }
        }
    });
});
