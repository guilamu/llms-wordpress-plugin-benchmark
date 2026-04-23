( function () {
	'use strict';

	const config = window.gfLiveSearch || {};

	function normalize(value) {
		return String(value || '').toLocaleLowerCase();
	}

	function init() {
		const table = document.querySelector('.wp-list-table.forms') || document.querySelector('.wp-list-table');

		if (!table || table.dataset.gfLiveSearchInitialized === '1') {
			return;
		}

		const tbody = table.tBodies[0];

		if (!tbody || !tbody.rows.length) {
			return;
		}

		table.dataset.gfLiveSearchInitialized = '1';

		const rows = Array.from(tbody.rows);
		const searchWrap = document.createElement('div');
		searchWrap.className = 'gf-live-search';

		const label = document.createElement('label');
		label.className = 'screen-reader-text';
		label.htmlFor = 'gf-live-search-input';
		label.textContent = config.label || 'Live search forms';

		const input = document.createElement('input');
		input.type = 'search';
		input.id = 'gf-live-search-input';
		input.className = 'regular-text gf-live-search__input';
		input.placeholder = config.placeholder || 'Search forms...';
		input.setAttribute('autocomplete', 'off');
		input.setAttribute('spellcheck', 'false');

		const status = document.createElement('p');
		status.className = 'gf-live-search__status';
		status.setAttribute('aria-live', 'polite');
		status.hidden = true;

		searchWrap.appendChild(label);
		searchWrap.appendChild(input);
		searchWrap.appendChild(status);

		table.parentNode.insertBefore(searchWrap, table);

		function render() {
			const term = normalize(input.value.trim());
			let visibleRows = 0;

			rows.forEach(function (row) {
				const matches = !term || normalize(row.textContent).includes(term);
				row.classList.toggle('gf-live-search-hidden', !matches);
				row.hidden = !matches;

				if (matches) {
					visibleRows += 1;
				}
			});

			if (term && visibleRows === 0) {
				status.hidden = false;
				status.textContent = config.emptyMessage || 'No forms match your search.';
				return;
			}

			status.hidden = true;
			status.textContent = '';
		}

		input.addEventListener('input', render);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init, { once: true });
	} else {
		init();
	}
}() );