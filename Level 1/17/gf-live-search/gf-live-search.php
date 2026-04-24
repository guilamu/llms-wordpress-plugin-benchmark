<?php
/**
 * Plugin Name: GF Live Search
 * Description: Adds live search filtering to the Gravity Forms forms list in WordPress admin.
 * Version: 1.0.0
 * Text Domain: gf-live-search
 * Domain Path: /languages
 *
 * @package GFLiveSearch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GF_LIVE_SEARCH_VERSION' ) ) {
	define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
}

/**
 * Loads the plugin text domain.
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
 * Checks whether the current admin screen is the Gravity Forms forms list.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return bool
 */
function gf_live_search_is_forms_list_screen( $hook_suffix ) {
	if ( ! is_admin() ) {
		return false;
	}

	$page = '';

	if ( isset( $_GET['page'] ) && is_scalar( $_GET['page'] ) ) {
		$page = sanitize_key( wp_unslash( $_GET['page'] ) );
	}

	if ( 'gf_edit_forms' !== $page ) {
		return false;
	}

	if ( ! class_exists( 'GFForms' ) && ! class_exists( 'GFAPI' ) ) {
		return false;
	}

	$hook_suffix = sanitize_key( (string) $hook_suffix );

	if ( false !== strpos( $hook_suffix, 'gf_edit_forms' ) ) {
		return true;
	}

	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();

		if ( $screen instanceof WP_Screen ) {
			$screen_id   = isset( $screen->id ) ? sanitize_key( $screen->id ) : '';
			$screen_base = isset( $screen->base ) ? sanitize_key( $screen->base ) : '';

			if ( false !== strpos( $screen_id, 'gf_edit_forms' ) || false !== strpos( $screen_base, 'gf_edit_forms' ) ) {
				return true;
			}
		}
	}

	global $pagenow;

	return isset( $pagenow ) && 'admin.php' === $pagenow;
}

/**
 * Enqueues admin assets on the Gravity Forms forms list only.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 */
function gf_live_search_enqueue_assets( $hook_suffix ) {
	if ( ! gf_live_search_is_forms_list_screen( $hook_suffix ) ) {
		return;
	}

	$assets_url = plugin_dir_url( __FILE__ ) . 'assets/';

	wp_enqueue_style(
		'gf-live-search',
		$assets_url . 'gf-live-search.css',
		array(),
		GF_LIVE_SEARCH_VERSION
	);

	wp_enqueue_script(
		'gf-live-search',
		$assets_url . 'gf-live-search.js',
		array( 'wp-i18n' ),
		GF_LIVE_SEARCH_VERSION,
		true
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations(
			'gf-live-search',
			'gf-live-search',
			plugin_dir_path( __FILE__ ) . 'languages'
		);
	}
}
add_action( 'admin_enqueue_scripts', 'gf_live_search_enqueue_assets' );
