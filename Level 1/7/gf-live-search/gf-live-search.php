<?php
/**
 * Plugin Name: GF Live Search
 * Description: Adds real-time search filtering to the Gravity Forms forms list page.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL-2.0-or-later
 * Text Domain: gf-live-search
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_LIVE_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'gf_live_search_load_textdomain' );

function gf_live_search_load_textdomain() {
	load_plugin_textdomain( 'gf-live-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_action( 'admin_enqueue_scripts', 'gf_live_search_enqueue_assets' );

function gf_live_search_enqueue_assets( $hook_suffix ) {
	if ( ! function_exists( 'rgget' ) && ! class_exists( 'GFForms' ) ) {
		return;
	}

	$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

	if ( 'gf_edit_forms' !== $current_page ) {
		return;
	}

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'gf-live-search',
		GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.css',
		array(),
		GF_LIVE_SEARCH_VERSION
	);

	wp_enqueue_script(
		'gf-live-search',
		GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.js',
		array(),
		GF_LIVE_SEARCH_VERSION,
		array( 'in_footer' => true )
	);

	wp_localize_script(
		'gf-live-search',
		'gfLiveSearch',
		array(
			'placeholder' => esc_html__( 'Search forms...', 'gf-live-search' ),
			'noResults'   => esc_html__( 'No forms found.', 'gf-live-search' ),
		)
	);
}

add_action( 'admin_footer', 'gf_live_search_output_search_input' );

function gf_live_search_output_search_input() {
	$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

	if ( 'gf_edit_forms' !== $current_page ) {
		return;
	}
	?>
	<script type="text/html" id="tmpl-gf-live-search-input">
		<div id="gf-live-search-wrapper" class="gf-live-search-wrapper">
			<label for="gf-live-search" class="screen-reader-text"><?php esc_html_e( 'Search forms', 'gf-live-search' ); ?></label>
			<input
				type="search"
				id="gf-live-search"
				class="gf-live-search-input"
				placeholder="<?php echo esc_attr( __( 'Search forms...', 'gf-live-search' ) ); ?>"
				autocomplete="off"
			/>
		</div>
	</script>
	<?php
}
