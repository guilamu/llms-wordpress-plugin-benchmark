<?php
/**
 * Plugin Name: GF Live Search
 * Plugin URI:  https://example.com/gf-live-search
 * Description: Ajoute une recherche en temps réel à la page de liste des formulaires Gravity Forms.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: gf-live-search
 * Domain Path: /languages
 *
 * @package GF_Live_Search
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 */
final class GF_Live_Search {

	/**
	 * Plugin instance.
	 *
	 * @var GF_Live_Search|null
	 */
	private static $instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	const SLUG = 'gf-live-search';

	/**
	 * Get the singleton instance.
	 *
	 * @return GF_Live_Search
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 *
	 * @return void
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Load plugin textdomain for internationalization.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			self::SLUG,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Enqueue scripts and styles only on the Gravity Forms edit forms page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Only load on the Gravity Forms forms list page.
		if ( 'toplevel_page_gf_edit_forms' !== $hook_suffix ) {
			return;
		}

		$plugin_url = plugin_dir_url( __FILE__ );

		// Enqueue CSS.
		wp_enqueue_style(
			self::SLUG,
			$plugin_url . 'assets/gf-live-search.css',
			array(),
			self::VERSION
		);

		// Enqueue JS.
		wp_enqueue_script(
			self::SLUG,
			$plugin_url . 'assets/gf-live-search.js',
			array(),
			self::VERSION,
			true
		);

		// Pass localized data to the script.
		wp_localize_script(
			self::SLUG,
			'gfLiveSearch',
			array(
				'searchPlaceholder' => __( 'Search forms…', 'gf-live-search' ),
				'noResults'         => __( 'No forms found matching your search.', 'gf-live-search' ),
			)
		);
	}
}

/**
 * Initialize the plugin.
 *
 * @return GF_Live_Search
 */
function gf_live_search() {
	return GF_Live_Search::get_instance();
}

gf_live_search();
