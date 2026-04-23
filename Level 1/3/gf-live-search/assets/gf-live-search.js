(function () {
    'use strict';

    if (typeof gf_ls === 'undefined') {
        return;
    }

    var DEBOUNCE_MS = 200;
    var noResultsRow = null;
    var searchInput = null;
    var tableBody = null;
    var rows = [];
    var debounceTimer = null;
    var prevQuery = '';

    function init() {
        var wrap = document.querySelector('#gf_edit_forms');
        if (!wrap) {
            return;
        }

        var table = wrap.querySelector('table');
        if (!table) {
            return;
        }

        tableBody = table.querySelector('tbody');
        if (!tableBody) {
            return;
        }

        rows = Array.prototype.slice.call(tableBody.querySelectorAll('tr'));

        searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'gf-ls-search';
        searchInput.placeholder = gf_ls.placeholder;
        searchInput.setAttribute('aria-label', gf_ls.placeholder);
        searchInput.autocomplete = 'off';

        var container = document.createElement('div');
        container.className = 'gf-ls-wrap';
        container.appendChild(searchInput);

        table.parentNode.insertBefore(container, table);

        noResultsRow = document.createElement('tr');
        noResultsRow.className = 'gf-ls-no-results';
        noResultsRow.innerHTML = '<td colspan="' + getColCount() + '">' + gf_ls.no_results + '</td>';
        noResultsRow.style.display = 'none';
        tableBody.appendChild(noResultsRow);

        searchInput.addEventListener('input', handleInput);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                filter('');
            }
        });
    }

    function handleInput() {
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(function () {
            var query = searchInput.value.trim();
            if (query !== prevQuery) {
                prevQuery = query;
                filter(query);
            }
        }, DEBOUNCE_MS);
    }

    function filter(query) {
        var lower = query.toLowerCase();
        var visibleCount = 0;

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var text = row.textContent.toLowerCase();
            var show = !query || text.indexOf(lower) !== -1;
            row.style.display = show ? '' : 'none';
            if (show) {
                visibleCount++;
            }
        }

        noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
    }

    function getColCount() {
        var headerRow = document.querySelector('#gf_edit_forms thead tr');
        if (!headerRow) {
            return 5;
        }
        var cells = headerRow.querySelectorAll('th, td');
        return cells.length || 5;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();