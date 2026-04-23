(function (window, document, undefined) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var searchInput = document.getElementById('gf-live-search');
		var wrapper = document.getElementById('gf-live-search-wrapper');

		if (!wrapper) {
			injectSearchInput();
			searchInput = document.getElementById('gf-live-search');
		}

		if (!searchInput) {
			return;
		}

		searchInput.addEventListener('input', debounce(function () {
			filterForms(searchInput.value.trim().toLowerCase());
		}, 150));

		function filterForms(query) {
			var rows = document.querySelectorAll('#gf_forms_list_table tbody tr, #form_list_table tbody tr, .widefat tbody tr');
			var visibleCount = 0;

			rows.forEach(function (row) {
				var text = row.textContent.toLowerCase();
				var match = text.indexOf(query) !== -1;
				row.style.display = match ? '' : 'none';
				if (match) {
					visibleCount++;
				}
			});

			updateNoResultsMessage(visibleCount, query);
		}

		function updateNoResultsMessage(count, query) {
			var existing = document.getElementById('gf-live-search-no-results');

			if (count === 0 && query.length > 0) {
				if (!existing) {
					existing = document.createElement('p');
					existing.id = 'gf-live-search-no-results';
					existing.className = 'gf-live-search-no-results';
					existing.textContent = gfLiveSearch.noResults;
					var table = document.querySelector('#gf_forms_list_table, #form_list_table, .widefat');
					if (table) {
						table.parentNode.insertBefore(existing, table.nextSibling);
					}
				}
				existing.style.display = '';
			} else if (existing) {
				existing.style.display = 'none';
			}
		}

		function injectSearchInput() {
			var target = document.querySelector(
				'#gf_forms_list_table .tablenav.top, ' +
				'#form_list_table .tablenav.top, ' +
				'.wrap .tablenav.top, ' +
				'.wrap h2, ' +
				'.wrap h1'
			);

			if (!target) {
				return;
			}

			var wrapper = document.createElement('div');
			wrapper.id = 'gf-live-search-wrapper';
			wrapper.className = 'gf-live-search-wrapper';

			var input = document.createElement('input');
			input.type = 'search';
			input.id = 'gf-live-search';
			input.className = 'gf-live-search-input';
			input.placeholder = gfLiveSearch.placeholder;
			input.setAttribute('autocomplete', 'off');

			wrapper.appendChild(input);
			target.parentNode.insertBefore(wrapper, target.nextSibling);
		}

		function debounce(func, wait) {
			var timeout;
			return function () {
				var context = this;
				var args = arguments;
				clearTimeout(timeout);
				timeout = setTimeout(function () {
					func.apply(context, args);
				}, wait);
			};
		}
	});
})(window, document);
