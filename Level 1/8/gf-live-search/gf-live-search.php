<?php
/**
 * Plugin Name: GF Live Search
 * Plugin URI:  https://example.com/gf-live-search
 * Description: Adds real-time search to the Gravity Forms forms list page.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: gf-live-search
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_FILE',    __FILE__ );
define( 'GF_LIVE_SEARCH_DIR',     plugin_dir_path( __FILE__ ) );
define( 'GF_LIVE_SEARCH_URL',     plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class — singleton.
 */
final class GF_Live_Search {

	/** @var GF_Live_Search|null */
	private static $instance = null;

	/**
	 * Returns the single instance of the class.
	 *
	 * @return GF_Live_Search
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/** Private constructor — use get_instance(). */
	private function __construct() {
		add_action( 'init',                  array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Loads the plugin text domain for i18n.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'gf-live-search',
			false,
			dirname( plugin_basename( GF_LIVE_SEARCH_FILE ) ) . '/languages'
		);
	}

	/**
	 * Enqueues CSS and JS only on the Gravity Forms forms list page.
	 *
	 * The forms list is identified by `page=gf_edit_forms` with no `id` or
	 * `view` query parameter (those indicate a single-form editing context).
	 */
	public function enqueue_assets() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';

		if ( 'gf_edit_forms' !== $page ) {
			return;
		}

		// Skip single-form views (edit, settings, entries, etc.).
		if ( ! empty( $_GET['id'] ) || ! empty( $_GET['view'] ) ) {
			return;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Bail gracefully when Gravity Forms is not active.
		if ( ! class_exists( 'GFForms' ) ) {
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
			array( 'jquery' ),
			GF_LIVE_SEARCH_VERSION,
			true
		);

		// Pass translatable strings to JavaScript.
		wp_localize_script(
			'gf-live-search',
			'gfLiveSearch',
			array(
				'placeholder' => esc_attr__( 'Search forms\u2026', 'gf-live-search' ),
				'ariaLabel'   => esc_attr__( 'Search forms', 'gf-live-search' ),
				'noResults'   => esc_html__( 'No forms match your search.', 'gf-live-search' ),
			)
		);
	}
}

GF_Live_Search::get_instance();
