( function () {
	'use strict';

	var __ = ( window.wp && window.wp.i18n && window.wp.i18n.__ )
		? window.wp.i18n.__
		: function ( s ) { return s; };
	var sprintf = ( window.wp && window.wp.i18n && window.wp.i18n.sprintf )
		? window.wp.i18n.sprintf
		: function ( s ) { return s; };
	var _n = ( window.wp && window.wp.i18n && window.wp.i18n._n )
		? window.wp.i18n._n
		: function ( single, plural, n ) { return n === 1 ? single : plural; };

	function normalize( value ) {
		return String( value || '' )
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' )
			.trim();
	}

	function debounce( fn, wait ) {
		var timer = null;
		return function () {
			var ctx = this;
			var args = arguments;
			clearTimeout( timer );
			timer = setTimeout( function () {
				fn.apply( ctx, args );
			}, wait );
		};
	}

	function init() {
		var table = document.querySelector( '#gform_form_list, table.wp-list-table' );
		if ( ! table ) {
			return;
		}

		var tbody = table.querySelector( 'tbody' );
		if ( ! tbody ) {
			return;
		}

		// Build the search UI.
		var wrapper = document.createElement( 'div' );
		wrapper.className = 'gf-live-search';

		var inputId = 'gf-live-search-input';

		var label = document.createElement( 'label' );
		label.className = 'screen-reader-text';
		label.setAttribute( 'for', inputId );
		label.textContent = __( 'Search forms', 'gf-live-search' );

		var input = document.createElement( 'input' );
		input.type = 'search';
		input.id = inputId;
		input.className = 'gf-live-search__input';
		input.placeholder = __( 'Search forms…', 'gf-live-search' );
		input.autocomplete = 'off';
		input.spellcheck = false;

		var count = document.createElement( 'span' );
		count.className = 'gf-live-search__count';
		count.setAttribute( 'aria-live', 'polite' );

		wrapper.appendChild( label );
		wrapper.appendChild( input );
		wrapper.appendChild( count );

		table.parentNode.insertBefore( wrapper, table );

		// Precompute searchable text for each row.
		var rows = Array.prototype.slice.call( tbody.querySelectorAll( 'tr' ) );
		rows.forEach( function ( row ) {
			row.setAttribute( 'data-gfls-text', normalize( row.textContent ) );
		} );

		// "No results" row.
		var colCount = ( table.querySelectorAll( 'thead th' ).length ) || 1;
		var noResults = document.createElement( 'tr' );
		noResults.className = 'gf-live-search__no-results';
		noResults.hidden = true;
		var noResultsCell = document.createElement( 'td' );
		noResultsCell.colSpan = colCount;
		noResultsCell.textContent = __( 'No forms match your search.', 'gf-live-search' );
		noResults.appendChild( noResultsCell );
		tbody.appendChild( noResults );

		function apply() {
			var query = normalize( input.value );
			var visible = 0;

			rows.forEach( function ( row ) {
				var text = row.getAttribute( 'data-gfls-text' ) || '';
				var match = '' === query || text.indexOf( query ) !== -1;
				row.hidden = ! match;
				if ( match ) {
					visible++;
				}
			} );

			noResults.hidden = ! ( '' !== query && 0 === visible );

			if ( '' === query ) {
				count.textContent = '';
			} else {
				count.textContent = sprintf(
					/* translators: %d: number of forms matching the search query. */
					_n( '%d form found', '%d forms found', visible, 'gf-live-search' ),
					visible
				);
			}
		}

		input.addEventListener( 'input', debounce( apply, 120 ) );
		input.addEventListener( 'search', apply );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
