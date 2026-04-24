/**
 * GF Live Search
 *
 * Provides instant client-side filtering of the Gravity Forms list table
 * on the "Forms" admin page.
 *
 * @package GF_Live_Search
 * @version 1.0.0
 */

(function () {
    'use strict';

    /**
     * Initialize the live search functionality once the DOM is ready.
     */
    function initLiveSearch() {
        var formsTable = document.querySelector('.wp-list-table tbody');

        // Abort if the forms table is not found.
        if (!formsTable) {
            return;
        }

        var rows = formsTable.querySelectorAll('tr');
        if (0 === rows.length) {
            return;
        }

        // Create the search container.
        var searchContainer = document.createElement('div');
        searchContainer.className = 'gf-live-search-container';

        // Create the search input field.
        var searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.placeholder = gfLiveSearch.searchPlaceholder || 'Search forms\u2026';
        searchInput.className = 'gf-live-search-input';
        searchInput.setAttribute('aria-label', gfLiveSearch.searchPlaceholder || 'Search forms\u2026');
        searchInput.setAttribute('autocomplete', 'off');
        searchInput.setAttribute('spellcheck', 'false');

        // Create the no-results message element (hidden by default).
        var noResultsMsg = document.createElement('div');
        noResultsMsg.className = 'gf-live-search-no-results';
        noResultsMsg.textContent = gfLiveSearch.noResults || 'No forms found.';
        noResultsMsg.style.display = 'none';

        // Append elements to the container.
        searchContainer.appendChild(searchInput);

        // Insert the search container and no-results message before the table.
        var wpListWrap = document.querySelector('.wp-list-wrap') || document.querySelector('.wp-list-table');
        if (wpListWrap && wpListWrap.parentNode) {
            wpListWrap.parentNode.insertBefore(searchContainer, wpListWrap);
            wpListWrap.parentNode.insertBefore(noResultsMsg, wpListWrap);
        } else {
            // Fallback: insert before the table itself.
            var listTable = document.querySelector('.wp-list-table');
            if (listTable && listTable.parentNode) {
                listTable.parentNode.insertBefore(searchContainer, listTable);
                listTable.parentNode.insertBefore(noResultsMsg, listTable);
            }
        }

        /**
         * Filter table rows based on the search query.
         */
        function filterRows() {
            var query = searchInput.value.trim().toLowerCase();
            var visibleCount = 0;

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];

                // Skip header rows and hidden rows.
                if (row.classList.contains('wp-list-table-header') || row.parentNode !== formsTable) {
                    continue;
                }

                var rowText = row.textContent.toLowerCase();

                if ('' === query || rowText.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }

            // Show or hide the no-results message.
            noResultsMsg.style.display = (0 === visibleCount) ? '' : 'none';
        }

        // Debounce helper to avoid excessive filtering on rapid input.
        var debounceTimer = null;

        /**
         * Debounced filter handler.
         */
        function onInput() {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(filterRows, 150);
        }

        // Bind the input event.
        searchInput.addEventListener('input', onInput);
        searchInput.addEventListener('search', filterRows); // Handle the "x" clear button on search inputs.

        // Focus the search input with Ctrl+Shift+F keyboard shortcut.
        document.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.shiftKey && 'f' === e.key.toLowerCase()) {
                e.preventDefault();
                searchInput.focus();
            }
        });
    }

    // Run on DOMContentLoaded.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLiveSearch);
    } else {
        initLiveSearch();
    }
})();