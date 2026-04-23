/**
 * GF Live Search
 *
 * Instantly filters the Gravity Forms list table based on user input,
 * without reloading the page.
 */
(function () {
	'use strict';

	var strings = window.gfLiveSearch || {};

	/**
	 * Normalize a string for case/diacritic-insensitive matching.
	 *
	 * @param {string} value
	 * @return {string}
	 */
	function normalize( value ) {
		if ( value === null || value === undefined ) {
			return '';
		}

		var str = String( value ).toLowerCase();

		if ( typeof str.normalize === 'function' ) {
			str = str.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' );
		}

		return str.trim();
	}

	/**
	 * Locate the forms list table.
	 *
	 * @return {HTMLElement|null}
	 */
	function findTable() {
		return (
			document.querySelector( '#gf_form_list' ) ||
			document.querySelector( '.wp-list-table.gforms_form_list' ) ||
			document.querySelector( '.wp-list-table' )
		);
	}

	/**
	 * Build and insert the search input above the list table.
	 *
	 * @param {HTMLElement} table
	 * @return {{ input: HTMLInputElement, status: HTMLElement }|null}
	 */
	function buildSearchUI( table ) {
		var wrap = document.createElement( 'div' );
		wrap.className = 'gf-live-search';

		var label = document.createElement( 'label' );
		label.className = 'screen-reader-text';
		label.setAttribute( 'for', 'gf-live-search-input' );
		label.textContent = strings.label || 'Live search';

		var input = document.createElement( 'input' );
		input.type = 'search';
		input.id = 'gf-live-search-input';
		input.className = 'gf-live-search__input';
		input.autocomplete = 'off';
		input.placeholder = strings.placeholder || 'Search forms…';
		input.setAttribute( 'aria-controls', table.id || 'the-list' );

		var status = document.createElement( 'span' );
		status.className = 'gf-live-search__status';
		status.setAttribute( 'role', 'status' );
		status.setAttribute( 'aria-live', 'polite' );

		wrap.appendChild( label );
		wrap.appendChild( input );
		wrap.appendChild( status );

		table.parentNode.insertBefore( wrap, table );

		return { input: input, status: status };
	}

	/**
	 * Apply the filter to table rows.
	 *
	 * @param {HTMLElement} table
	 * @param {HTMLElement} status
	 * @param {string} query
	 */
	function applyFilter( table, status, query ) {
		var tbody = table.querySelector( 'tbody' );

		if ( ! tbody ) {
			return;
		}

		var needle = normalize( query );
		var rows = tbody.querySelectorAll( 'tr' );
		var visible = 0;

		rows.forEach( function ( row ) {
			// Skip the "no items" placeholder row.
			if ( row.classList.contains( 'no-items' ) ) {
				return;
			}

			if ( needle === '' ) {
				row.hidden = false;
				visible++;
				return;
			}

			var haystack = normalize( row.textContent );

			if ( haystack.indexOf( needle ) !== -1 ) {
				row.hidden = false;
				visible++;
			} else {
				row.hidden = true;
			}
		} );

		// Update the aria-live status.
		if ( needle === '' ) {
			status.textContent = '';
		} else if ( visible === 0 ) {
			status.textContent = strings.noResults || 'No forms match your search.';
		} else if ( visible === 1 ) {
			status.textContent = strings.resultsOne || '1 form found';
		} else {
			var template = strings.resultsMany || '%d forms found';
			status.textContent = template.replace( '%d', visible );
		}

		// Toggle a class on the table to allow "empty" CSS styling.
		table.classList.toggle( 'gf-live-search--empty', needle !== '' && visible === 0 );
	}

	/**
	 * Initialize the plugin once the DOM is ready.
	 */
	function init() {
		var table = findTable();

		if ( ! table ) {
			return;
		}

		var ui = buildSearchUI( table );

		if ( ! ui ) {
			return;
		}

		var onInput = function () {
			applyFilter( table, ui.status, ui.input.value );
		};

		ui.input.addEventListener( 'input', onInput );
		ui.input.addEventListener( 'search', onInput );

		// Keep focus behavior sane: ESC clears the field.
		ui.input.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && ui.input.value !== '' ) {
				ui.input.value = '';
				onInput();
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
