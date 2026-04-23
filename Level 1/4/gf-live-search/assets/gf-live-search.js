/* GF Live Search - Real-time filtering for Gravity Forms list table */
(function () {
    'use strict';

    const SEARCH_INPUT_ID = 'gf-live-search-input';
    const TABLE_SELECTOR = '#the-list';
    const ROW_SELECTOR = 'tr';

    /**
     * Initialize the live search functionality.
     */
    function init() {
        const tableBody = document.querySelector(TABLE_SELECTOR);
        if (!tableBody) {
            return;
        }

        const searchInput = createSearchInput();
        insertSearchInput(searchInput, tableBody);

        searchInput.addEventListener('keyup', function () {
            filterRows(tableBody, this.value.trim().toLowerCase());
        });
    }

    /**
     * Create the search input element.
     *
     * @returns {HTMLInputElement}
     */
    function createSearchInput() {
        const input = document.createElement('input');
        input.type = 'text';
        input.id = SEARCH_INPUT_ID;
        input.className = 'gf-live-search__input';
        input.placeholder = window.gfLiveSearch?.inputPlaceholder || 'Rechercher un formulaire...';
        input.autocomplete = 'off';
        input.setAttribute('aria-label', window.gfLiveSearch?.inputPlaceholder || 'Rechercher un formulaire...');
        return input;
    }

    /**
     * Insert the search input before the table wrapper.
     *
     * @param {HTMLInputElement} input
     * @param {HTMLElement} tableBody
     */
    function insertSearchInput(input, tableBody) {
        // Try to insert above the .tablenav or .wp-list-table.
        const listTable = document.querySelector('.wp-list-table, .gf-forms-table');
        if (listTable && listTable.parentNode) {
            const wrapper = document.createElement('div');
            wrapper.className = 'gf-live-search__wrapper';
            wrapper.appendChild(input);
            listTable.parentNode.insertBefore(wrapper, listTable);
        } else {
            tableBody.parentNode.insertBefore(input, tableBody);
        }
    }

    /**
     * Filter table rows based on search term.
     *
     * @param {HTMLElement} tableBody
     * @param {string} term
     */
    function filterRows(tableBody, term) {
        const rows = tableBody.querySelectorAll(ROW_SELECTOR);
        let visibleCount = 0;

        rows.forEach(function (row) {
            const text = row.textContent.toLowerCase();
            if (text.indexOf(term) > -1) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        toggleNoResultsMessage(tableBody.parentElement, visibleCount === 0 && term.length > 0);
    }

    /**
     * Toggle a "no results" message.
     *
     * @param {HTMLElement} container
     * @param {boolean} show
     */
    function toggleNoResultsMessage(container, show) {
        let msg = container.querySelector('.gf-live-search__no-results');

        if (show) {
            if (!msg) {
                msg = document.createElement('div');
                msg.className = 'gf-live-search__no-results';
                msg.textContent = window.gfLiveSearch?.noResultsText || 'Aucun résultat trouvé.';
                container.appendChild(msg);
            }
        } else if (msg) {
            msg.remove();
        }
    }

    // Run when DOM is ready.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
