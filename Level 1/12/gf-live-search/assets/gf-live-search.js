/**
 * GF Live Search — Client-side filtering
 *
 * Injects a search input above the Gravity Forms form list table and
 * hides/shows rows in real time based on the query.
 *
 * Relies on `gfLiveSearchL10n` (wp_localize_script) for translatable strings.
 *
 * @since 1.0.0
 */
( function () {
	'use strict';

	/* ------------------------------------------------------------------ */
	/* Helpers                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Simple debounce utility.
	 *
	 * @param {Function} fn       Callback.
	 * @param {number}   delay    Milliseconds.
	 * @return {Function}
	 */
	function debounce( fn, delay ) {
		var timerId;
		return function () {
			var context = this;
			var args = arguments;
			clearTimeout( timerId );
			timerId = setTimeout( function () {
				fn.apply( context, args );
			}, delay );
		};
	}

	/**
	 * Normalise a string for comparison (lowercase, trimmed, collapsed spaces).
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function normalise( str ) {
		return str.toLowerCase().replace( /\s+/g, ' ' ).trim();
	}

	/* ------------------------------------------------------------------ */
	/* Boot                                                                */
	/* ------------------------------------------------------------------ */

	document.addEventListener( 'DOMContentLoaded', function () {
		var l10n = window.gfLiveSearchL10n || {};

		// Gravity Forms renders the list inside a table with various possible
		// selectors depending on the GF version.  We look for a common pattern.
		var table = document.querySelector( '#gf_form_list' )               // GF ≥ 2.5
		         || document.querySelector( '.gform_form_list_table' )       // older
		         || document.querySelector( '#the-list' )                    // fallback
		         || document.querySelector( '.wp-list-table' );              // generic WP

		if ( ! table ) {
			return; // Not on the expected page or table not found.
		}

		// If `table` is actually a <tbody>, walk up to the <table>.
		var tableEl = table.closest( 'table' ) || table;
		var tbody   = tableEl.querySelector( 'tbody' ) || tableEl;

		/* -------------------------------------------------------------- */
		/* Build the search UI                                             */
		/* -------------------------------------------------------------- */

		var wrap = document.createElement( 'div' );
		wrap.className = 'gf-live-search-wrap';

		var field = document.createElement( 'div' );
		field.className = 'gf-live-search-field';

		var input = document.createElement( 'input' );
		input.type        = 'search';
		input.className   = 'gf-live-search-input';
		input.placeholder = l10n.placeholder || 'Search forms…';
		input.setAttribute( 'aria-label', l10n.placeholder || 'Search forms…' );
		input.id = 'gf-live-search-input';

		var clearBtn = document.createElement( 'button' );
		clearBtn.type      = 'button';
		clearBtn.className = 'gf-live-search-clear';
		clearBtn.innerHTML = '&times;';
		clearBtn.setAttribute( 'aria-label', l10n.clearLabel || 'Clear search' );
		clearBtn.title = l10n.clearLabel || 'Clear search';

		field.appendChild( input );
		field.appendChild( clearBtn );
		wrap.appendChild( field );

		// Insert above the table (or its wrapping container).
		var insertBefore = tableEl.parentNode;
		if ( insertBefore && insertBefore.parentNode ) {
			insertBefore.parentNode.insertBefore( wrap, insertBefore );
		} else {
			tableEl.parentNode.insertBefore( wrap, tableEl );
		}

		/* -------------------------------------------------------------- */
		/* "No results" row                                                */
		/* -------------------------------------------------------------- */

		var noResultsRow = document.createElement( 'tr' );
		noResultsRow.className = 'gf-live-search-no-results gf-live-search-hidden';

		var noResultsTd = document.createElement( 'td' );
		// Span all columns.
		var colCount = tableEl.querySelector( 'thead tr' )
			? tableEl.querySelector( 'thead tr' ).children.length
			: 1;
		noResultsTd.setAttribute( 'colspan', colCount );
		noResultsTd.textContent = l10n.noResults || 'No forms match your search.';

		noResultsRow.appendChild( noResultsTd );
		tbody.appendChild( noResultsRow );

		/* -------------------------------------------------------------- */
		/* Filtering logic                                                 */
		/* -------------------------------------------------------------- */

		/**
		 * Filter visible rows.
		 */
		function filterRows() {
			var query = normalise( input.value );
			var rows  = tbody.querySelectorAll( 'tr:not(.gf-live-search-no-results)' );
			var visibleCount = 0;

			for ( var i = 0; i < rows.length; i++ ) {
				var row  = rows[ i ];
				var text = normalise( row.textContent || '' );

				if ( ! query || text.indexOf( query ) !== -1 ) {
					row.classList.remove( 'gf-live-search-hidden' );
					visibleCount++;
				} else {
					row.classList.add( 'gf-live-search-hidden' );
				}
			}

			// Toggle "no results" message.
			if ( query && visibleCount === 0 ) {
				noResultsRow.classList.remove( 'gf-live-search-hidden' );
			} else {
				noResultsRow.classList.add( 'gf-live-search-hidden' );
			}

			// Toggle clear button visibility.
			clearBtn.style.display = input.value ? 'flex' : 'none';
		}

		/* -------------------------------------------------------------- */
		/* Event listeners                                                 */
		/* -------------------------------------------------------------- */

		input.addEventListener( 'input', debounce( filterRows, 150 ) );

		// The "search" event fires when the user clears the native ×
		// button rendered by some browsers for type="search".
		input.addEventListener( 'search', filterRows );

		clearBtn.addEventListener( 'click', function () {
			input.value = '';
			filterRows();
			input.focus();
		} );

		// Allow Escape key to clear the search.
		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				input.value = '';
				filterRows();
			}
		} );
	} );
} )();
