/**
 * GF Live Search – real-time form filtering.
 *
 * @package GF_Live_Search
 */

(function () {
	'use strict';

	var DEBOUNCE_DELAY = 200;
	var timer          = null;

	function getRows() {
		return document.querySelectorAll( '#the-list tr' );
	}

	function normalise( str ) {
		return ( str || '' )
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[\u0300-\u036f]/g, '' );
	}

	function filter() {
		var input = document.getElementById( 'gf-live-search-input' );
		if ( ! input ) {
			return;
		}

		var query       = normalise( input.value.trim() );
		var rows        = getRows();
		var visibleCount = 0;

		for ( var i = 0; i < rows.length; i++ ) {
			var row  = rows[ i ];
			var text = normalise( row.textContent );

			if ( ! query || text.indexOf( query ) !== -1 ) {
				row.style.display = '';
				visibleCount++;
			} else {
				row.style.display = 'none';
			}
		}

		toggleNoResults( visibleCount === 0 && query.length > 0 );
	}

	function toggleNoResults( show ) {
		var table = document.querySelector( '.wp-list-table' );
		if ( ! table ) {
			return;
		}

		var id       = 'gf-live-search-no-results';
		var existing = document.getElementById( id );

		if ( show && ! existing ) {
			var colCount = table.querySelectorAll( 'thead th' ).length || 1;
			var tbody    = table.querySelector( 'tbody' ) || table;
			var tr       = document.createElement( 'tr' );
			tr.id        = id;
			var td       = document.createElement( 'td' );
			td.setAttribute( 'colspan', colCount );
			td.textContent    = 'No forms found.';
			td.style.textAlign = 'center';
			td.style.padding   = '20px';
			tr.appendChild( td );
			tbody.appendChild( tr );
		} else if ( ! show && existing ) {
			existing.parentNode.removeChild( existing );
		}
	}

	function init() {
		var input = document.getElementById( 'gf-live-search-input' );
		if ( ! input ) {
			return;
		}

		var wrap  = document.getElementById( 'gf-live-search-wrap' );
		var table = document.querySelector( '.wp-list-table' );
		if ( wrap && table && ! wrap.contains( table ) ) {
			table.parentNode.insertBefore( wrap, table );
		}

		input.addEventListener( 'input', function () {
			clearTimeout( timer );
			timer = setTimeout( filter, DEBOUNCE_DELAY );
		} );

		input.addEventListener( 'search', filter );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
})();
