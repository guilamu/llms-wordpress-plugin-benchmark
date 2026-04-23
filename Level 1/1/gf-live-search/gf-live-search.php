<?php
/**
 * Plugin Name:       GF Live Search
 * Plugin URI:        https://example.com/gf-live-search
 * Description:       Adds real-time search functionality to the Gravity Forms list page.
 * Version:           1.0.0
 * Author:            Antigravity
 * Author URI:        https://example.com
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires at least: 5.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class GFLiveSearch
 * Handles the initialization and asset loading for the GF Live Search plugin.
 */
class GFLiveSearch {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'gf-live-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Enqueue assets only on the Gravity Forms list page.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		// Only load on Gravity Forms list page.
		// Standard GF list page hook is 'forms_page_gf_edit_forms'.
		if ( 'forms_page_gf_edit_forms' !== $hook ) {
			return;
		}

		$version = '1.0.0';

		wp_enqueue_style(
			'gf-live-search-css',
			plugins_url( 'assets/gf-live-search.css', __FILE__ ),
			array(),
			$version
		);

		wp_enqueue_script(
			'gf-live-search-js',
			plugins_url( 'assets/gf-live-search.js', __FILE__ ),
			array( 'jquery' ),
			$version,
			true
		);

		wp_localize_script(
			'gf-live-search-js',
			'gfLiveSearchL10n',
			array(
				'placeholder' => __( 'Search forms...', 'gf-live-search' ),
				'noResults'  => __( 'No forms found.', 'gf-live-search' ),
			)
		);
	}
}

new GFLiveSearch();
