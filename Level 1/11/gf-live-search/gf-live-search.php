<?php
/**
 * Plugin Name:       GF Live Search
 * Plugin URI:        https://example.com/gf-live-search
 * Description:       Adds an instant, client-side live search field to the Gravity Forms list screen (no page reload).
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.2
 * Author:            GF Live Search
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 *
 * @package GF_Live_Search
 */

defined( 'ABSPATH' ) || exit;

define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_PATH', plugin_dir_path( __FILE__ ) );
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
 * Determine whether the current admin screen is the Gravity Forms list screen.
 *
 * We target the list view only, not the single-form editor (which is reached
 * via `&view=...&id=...` on the same `page` slug).
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return bool
 */
function gf_live_search_is_forms_list_screen( $hook_suffix ) {
	if ( 'toplevel_page_gf_edit_forms' !== $hook_suffix ) {
		return false;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only check of GET params to scope asset loading.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'gf_edit_forms' !== $page ) {
		return false;
	}

	// Exclude the single-form editor and other sub-views.
	if ( ! empty( $_GET['view'] ) || ! empty( $_GET['id'] ) ) {
		return false;
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	return true;
}

/**
 * Enqueue assets only on the Gravity Forms list screen.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function gf_live_search_enqueue_assets( $hook_suffix ) {
	if ( ! gf_live_search_is_forms_list_screen( $hook_suffix ) ) {
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
		array( 'wp-i18n' ),
		GF_LIVE_SEARCH_VERSION,
		true
	);

	wp_set_script_translations(
		'gf-live-search',
		'gf-live-search',
		GF_LIVE_SEARCH_PATH . 'languages'
	);
}
add_action( 'admin_enqueue_scripts', 'gf_live_search_enqueue_assets' );
