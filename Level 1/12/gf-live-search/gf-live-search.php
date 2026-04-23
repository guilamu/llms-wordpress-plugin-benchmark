<?php
/**
 * Plugin Name:       GF Live Search
 * Plugin URI:        https://example.com/gf-live-search
 * Description:       Adds a real-time search field to the Gravity Forms form list page for instant filtering without page reload.
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            GF Live Search
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * Uses a singleton pattern to ensure a single instance throughout the request lifecycle.
 *
 * @since 1.0.0
 */
final class GF_Live_Search {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Singleton instance.
	 *
	 * @var GF_Live_Search|null
	 */
	private static ?GF_Live_Search $instance = null;

	/**
	 * Return the singleton instance.
	 *
	 * @return GF_Live_Search
	 */
	public static function get_instance(): GF_Live_Search {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — registers hooks.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Load plugin translations.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'gf-live-search',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}

	/**
	 * Determine whether we are on the Gravity Forms form list page.
	 *
	 * The form list lives at admin.php?page=gf_edit_forms with NO "id" parameter
	 * (when an "id" is present the user is editing a single form).
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 *
	 * @return bool
	 */
	private function is_gf_form_list_page( string $hook_suffix ): bool {
		// Gravity Forms registers its pages under toplevel_page_gf_edit_forms
		// or forms_page_gf_edit_forms depending on menu position.
		if ( false === strpos( $hook_suffix, 'gf_edit_forms' ) ) {
			return false;
		}

		// When editing a single form, the "id" query-string param is set.
		// We only want the list view.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['id'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue CSS & JS assets — only on the GF form list page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		if ( ! $this->is_gf_form_list_page( $hook_suffix ) ) {
			return;
		}

		$plugin_url = plugin_dir_url( __FILE__ );

		wp_enqueue_style(
			'gf-live-search',
			$plugin_url . 'assets/gf-live-search.css',
			array(),
			self::VERSION
		);

		wp_enqueue_script(
			'gf-live-search',
			$plugin_url . 'assets/gf-live-search.js',
			array(), // No dependencies — vanilla JS.
			self::VERSION,
			true    // Load in the footer.
		);

		// Pass translatable strings to JS.
		wp_localize_script(
			'gf-live-search',
			'gfLiveSearchL10n',
			array(
				'placeholder' => esc_attr__( 'Search forms…', 'gf-live-search' ),
				'noResults'   => esc_html__( 'No forms match your search.', 'gf-live-search' ),
				'clearLabel'  => esc_attr__( 'Clear search', 'gf-live-search' ),
			)
		);
	}
}

// Boot the plugin.
GF_Live_Search::get_instance();
