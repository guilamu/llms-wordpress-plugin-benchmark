( function ( window, document, wp ) {
	'use strict';

	var i18n = wp && wp.i18n ? wp.i18n : {};
	var __ = i18n.__ || function ( text ) {
		return text;
	};

	function onReady( callback ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', callback );
			return;
		}

		callback();
	}

	function normalizeText( value ) {
		var text = String( value || '' ).toLocaleLowerCase();

		if ( typeof text.normalize === 'function' ) {
			text = text.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' );
		}

		return text.trim();
	}

	function getRows( table ) {
		var body = table.tBodies && table.tBodies.length ? table.tBodies[0] : table.querySelector( 'tbody' );

		if ( ! body ) {
			return [];
		}

		return Array.prototype.filter.call( body.querySelectorAll( 'tr' ), function ( row ) {
			return ! row.classList.contains( 'no-items' ) &&
				! row.classList.contains( 'inline-edit-row' ) &&
				!! row.querySelector( 'td, th' );
		} );
	}

	function findFormsTable() {
		var theList = document.querySelector( '#the-list' );
		var table;
		var tables;
		var index;

		if ( theList ) {
			table = theList.closest( 'table' );

			if ( table && getRows( table ).length ) {
				return table;
			}
		}

		tables = Array.prototype.slice.call( document.querySelectorAll( 'table.wp-list-table' ) );

		for ( index = 0; index < tables.length; index += 1 ) {
			if ( getRows( tables[index] ).length ) {
				return tables[index];
			}
		}

		return null;
	}

	function getRowText( row ) {
		return row.innerText || row.textContent || '';
	}

	function insertControl( table ) {
		var control = document.createElement( 'div' );
		var label = document.createElement( 'label' );
		var field = document.createElement( 'div' );
		var input = document.createElement( 'input' );
		var clearButton = document.createElement( 'button' );
		var emptyMessage = document.createElement( 'p' );
		var inputId = 'gf-live-search-input';
		var emptyId = 'gf-live-search-empty';
		var tableId = table.id || 'gf-live-search-table';

		if ( document.getElementById( 'gf-live-search' ) ) {
			return null;
		}

		if ( ! table.id ) {
			table.id = tableId;
		}

		control.id = 'gf-live-search';
		control.className = 'gf-live-search';
		control.setAttribute( 'role', 'search' );

		label.className = 'gf-live-search__label';
		label.htmlFor = inputId;
		label.textContent = __( 'Search forms', 'gf-live-search' );

		field.className = 'gf-live-search__field';

		input.id = inputId;
		input.className = 'gf-live-search__input regular-text';
		input.type = 'search';
		input.autocomplete = 'off';
		input.placeholder = __( 'Filter forms...', 'gf-live-search' );
		input.setAttribute( 'aria-controls', tableId );
		input.setAttribute( 'aria-describedby', emptyId );

		clearButton.className = 'button gf-live-search__clear';
		clearButton.type = 'button';
		clearButton.textContent = __( 'Clear search', 'gf-live-search' );
		clearButton.hidden = true;

		emptyMessage.id = emptyId;
		emptyMessage.className = 'gf-live-search__empty';
		emptyMessage.setAttribute( 'aria-live', 'polite' );
		emptyMessage.textContent = __( 'No forms found.', 'gf-live-search' );
		emptyMessage.hidden = true;

		field.appendChild( input );
		field.appendChild( clearButton );
		control.appendChild( label );
		control.appendChild( field );
		control.appendChild( emptyMessage );

		table.parentNode.insertBefore( control, table );

		return {
			input: input,
			clearButton: clearButton,
			emptyMessage: emptyMessage
		};
	}

	function init() {
		var table = findFormsTable();
		var rows;
		var control;

		if ( ! table ) {
			return;
		}

		rows = getRows( table );

		if ( ! rows.length ) {
			return;
		}

		control = insertControl( table );

		if ( ! control ) {
			return;
		}

		function applyFilter() {
			var query = normalizeText( control.input.value );
			var hasQuery = query.length > 0;
			var visibleCount = 0;

			rows.forEach( function ( row ) {
				var matches = ! hasQuery || normalizeText( getRowText( row ) ).indexOf( query ) !== -1;

				row.classList.toggle( 'gf-live-search-is-hidden', ! matches );

				if ( matches ) {
					visibleCount += 1;
				}
			} );

			control.clearButton.hidden = ! hasQuery;
			control.emptyMessage.hidden = ! hasQuery || visibleCount > 0;
		}

		control.input.addEventListener( 'input', applyFilter );

		control.input.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && control.input.value ) {
				control.input.value = '';
				applyFilter();
			}
		} );

		control.clearButton.addEventListener( 'click', function () {
			control.input.value = '';
			applyFilter();
			control.input.focus();
		} );
	}

	onReady( init );
}( window, document, window.wp || {} ) );
