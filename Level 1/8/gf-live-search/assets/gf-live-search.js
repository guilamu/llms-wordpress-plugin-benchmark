/* global gfLiveSearch */
( function ( $ ) {
	'use strict';

	$( document ).ready( function () {

		// Gravity Forms renders the forms list inside a <table> that is either
		// the first .widefat on the page or lives inside #gf_form_list.
		var $table = $( '#gf_form_list table, table.widefat' ).first();

		if ( ! $table.length ) {
			return;
		}

		var $tbody   = $table.find( 'tbody' );
		var $rows    = $tbody.find( 'tr' );

		// ── Build the search widget ─────────────────────────────────────────
		var $wrap = $( '<div>', { id: 'gf-live-search-wrap' } );

		var $input = $( '<input>', {
			type:          'search',
			id:            'gf-live-search-input',
			placeholder:   gfLiveSearch.placeholder,
			'aria-label':  gfLiveSearch.ariaLabel,
			autocomplete:  'off',
			spellcheck:    false,
		} );

		var $notice = $( '<p>', {
			id:   'gf-live-search-no-results',
			text: gfLiveSearch.noResults,
		} ).hide();

		$wrap.append( $input ).append( $notice );

		// Insert the widget immediately before the table.
		$table.before( $wrap );

		// ── Live-filter logic ───────────────────────────────────────────────
		$input.on( 'input', function () {
			var term = $( this ).val().toLowerCase().trim();

			if ( ! term ) {
				$rows.show();
				$notice.hide();
				return;
			}

			var visibleCount = 0;

			$rows.each( function () {
				var $row = $( this );

				// Preserve special rows added by WP_List_Table (e.g. "no items").
				if ( $row.hasClass( 'no-items' ) ) {
					$row.hide();
					return;
				}

				// Match against the full text of the row (covers title, date,
				// entries count, etc.). For a stricter match, replace with:
				//   $row.find( '.column-title' ).text().toLowerCase()
				var matches = $row.text().toLowerCase().indexOf( term ) !== -1;
				$row.toggle( matches );

				if ( matches ) {
					visibleCount++;
				}
			} );

			$notice.toggle( visibleCount === 0 );
		} );

		// Clear filter when the native search form (if present) is submitted
		// so GF's own search and ours do not conflict.
		$( 'form#search-forms' ).on( 'submit', function () {
			$input.val( '' ).trigger( 'input' );
		} );

	} );

}( jQuery ) );
