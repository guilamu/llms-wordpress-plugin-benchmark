/**
 * GF Live Search — Frontend JavaScript
 *
 * Adds real-time filtering to the Gravity Forms "Edit Forms" table.
 *
 * @package GF_Live_Search
 * @since   1.0.0
 */

(function () {
	'use strict';

	/**
	 * Debounce helper to limit how often a callback fires.
	 *
	 * @param {Function} callback The function to debounce.
	 * @param {number}   delay    Delay in milliseconds.
	 * @return {Function} Debounced function.
	 */
	function debounce(callback, delay) {
		var timer = null;
		return function () {
			var context = this;
			var args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				callback.apply(context, args);
			}, delay);
		};
	}

	/**
	 * Initialize the live search on the GF edit-forms page.
	 */
	function initLiveSearch() {
		// Locate the table and its tbody rows.
		var table = document.querySelector('.wp-list-table');
		if (!table) {
			return;
		}

		var tbody = table.querySelector('tbody');
		if (!tbody) {
			return;
		}

		var rows = tbody.querySelectorAll('tr:not(.no-items)');
		if (!rows.length) {
			return;
		}

		// Create the search UI.
		var searchContainer = createSearchUI();
		var searchInput = searchContainer.querySelector('.gf-live-search-input');
		var countDisplay = searchContainer.querySelector('.gf-live-search-count');

		// Insert the search bar above the table.
		var tableParent = table.parentNode;
		tableParent.insertBefore(searchContainer, table);

		// Retrieve localized strings (fall back to English if not set).
		var placeholder = window.gfLiveSearch && window.gfLiveSearch.searchPlaceholder
			? window.gfLiveSearch.searchPlaceholder
			: 'Search forms…';
		var noResultsText = window.gfLiveSearch && window.gfLiveSearch.noResults
			? window.gfLiveSearch.noResults
			: 'No forms found matching your search.';

		searchInput.setAttribute('placeholder', placeholder);

		// Create or locate the no-results message row.
		var noResultsRow = createNoResultsRow(noResultsText);
		tbody.appendChild(noResultsRow);

		/**
		 * Perform the filtering.
		 */
		function filterRows() {
			var query = searchInput.value.trim().toLowerCase();
			var visibleCount = 0;

			rows.forEach(function (row) {
				var match = row.textContent.toLowerCase().indexOf(query) !== -1;
				if (match) {
					row.classList.remove('gf-live-search-hidden');
					visibleCount++;
				} else {
					row.classList.add('gf-live-search-hidden');
				}
			});

			// Show/hide the "no results" message.
			if (visibleCount === 0 && query.length > 0) {
				noResultsRow.classList.remove('gf-live-search-hidden');
			} else {
				noResultsRow.classList.add('gf-live-search-hidden');
			}

			// Update the result count.
			countDisplay.textContent = '(' + visibleCount + '/' + rows.length + ')';
		}

		// Attach the debounced input handler.
		searchInput.addEventListener('input', debounce(filterRows, 200));

		// Run an initial filter in case the browser auto-fills something.
		filterRows();
	}

	/**
	 * Build the search container DOM elements.
	 *
	 * @return {HTMLElement} The search wrapper div.
	 */
	function createSearchUI() {
		var wrap = document.createElement('div');
		wrap.className = 'gf-live-search-wrap';

		var label = document.createElement('label');
		label.setAttribute('for', 'gf-live-search-input');
		label.textContent = 'Search';

		var input = document.createElement('input');
		input.type = 'text';
		input.id = 'gf-live-search-input';
		input.className = 'gf-live-search-input';

		var count = document.createElement('span');
		count.className = 'gf-live-search-count';

		wrap.appendChild(label);
		wrap.appendChild(input);
		wrap.appendChild(count);

		return wrap;
	}

	/**
	 * Create a hidden "no results" table row.
	 *
	 * @param {string} message The message to display.
	 * @return {HTMLElement} The table row element.
	 */
	function createNoResultsRow(message) {
		// Check if the table already has a "no items" row.
		var existing = document.querySelector('.wp-list-table tbody .no-items');
		if (existing) {
			// Clone it so we keep the correct colspan.
			var clone = existing.cloneNode(true);
			clone.className = 'gf-live-search-no-results gf-live-search-hidden';
			var cell = clone.querySelector('td');
			if (cell) {
				cell.textContent = message;
			}
			// Hide the original no-items row – it will only appear when the table truly has no forms.
			existing.style.display = 'none';
			return clone;
		}

		// Fallback: create a fresh row.
		var row = document.createElement('tr');
		row.className = 'gf-live-search-no-results gf-live-search-hidden';

		var cell = document.createElement('td');
		cell.textContent = message;

		// Determine the colspan by counting the header columns.
		var headerCells = document.querySelectorAll('.wp-list-table thead tr:first-child th');
		cell.setAttribute('colspan', headerCells.length || 5);

		row.appendChild(cell);
		return row;
	}

	// Run once the DOM is fully loaded.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initLiveSearch);
	} else {
		initLiveSearch();
	}
})();
