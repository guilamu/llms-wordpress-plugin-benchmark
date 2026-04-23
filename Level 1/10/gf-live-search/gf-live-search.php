<?php
/**
 * Plugin Name:       GF Live Search
 * Plugin URI:        https://example.com/gf-live-search
 * Description:       Adds a real-time search field to the Gravity Forms list page to instantly filter forms without reloading.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Guigui
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 *
 * @package GF_Live_Search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_FILE', __FILE__ );
define( 'GF_LIVE_SEARCH_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_LIVE_SEARCH_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin translations.
 */
function gf_live_search_load_textdomain() {
	load_plugin_textdomain(
		'gf-live-search',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'gf_live_search_load_textdomain' );

/**
 * Determine whether we're on the Gravity Forms list page.
 *
 * @return bool
 */
function gf_live_search_is_forms_list_page() {
	if ( ! is_admin() ) {
		return false;
	}

	// The Gravity Forms list page is /wp-admin/admin.php?page=gf_edit_forms
	// with no "view" or "id" parameter (which indicate a sub-screen).
	if ( ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	$page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( 'gf_edit_forms' !== $page ) {
		return false;
	}

	// Exclude sub-screens (editor, entries, settings, etc.).
	if ( ! empty( $_GET['view'] ) || ! empty( $_GET['id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return false;
	}

	return true;
}

/**
 * Enqueue assets only on the Gravity Forms list page.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function gf_live_search_enqueue_assets( $hook_suffix ) {
	unset( $hook_suffix );

	if ( ! gf_live_search_is_forms_list_page() ) {
		return;
	}

	wp_enqueue_style(
		'gf-live-search',
		GF_LIVE_SEARCH_URL . 'assets/gf-live-search.css',
		array(),
		GF_LIVE_SEARCH_VERSION
	);

	wp_enqueue_script(
		'gf-live-search',
		GF_LIVE_SEARCH_URL . 'assets/gf-live-search.js',
		array(),
		GF_LIVE_SEARCH_VERSION,
		true
	);

	wp_localize_script(
		'gf-live-search',
		'gfLiveSearch',
		array(
			'placeholder' => __( 'Search forms…', 'gf-live-search' ),
			'label'       => __( 'Live search', 'gf-live-search' ),
			'noResults'   => __( 'No forms match your search.', 'gf-live-search' ),
			'resultsOne'  => __( '1 form found', 'gf-live-search' ),
			/* translators: %d: number of matching forms. */
			'resultsMany' => __( '%d forms found', 'gf-live-search' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'gf_live_search_enqueue_assets' );
