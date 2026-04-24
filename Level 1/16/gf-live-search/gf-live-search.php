<?php
/**
 * Plugin Name:     GF Live Search
 * Plugin URI:      https://example.com/gf-live-search
 * Description:     Adds real-time search functionality to the Gravity Forms list page, enabling instant filtering without page reload.
 * Version:         1.0.0
 * Author:          Your Name
 * Author URI:      https://example.com
 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     gf-live-search
 * Domain Path:     /languages
 *
 * @package         GF_Live_Search
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define plugin constants.
 */
define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_LIVE_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main plugin class.
 */
final class GF_Live_Search {

    /**
     * Instance of this class.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Returns an instance of this class.
     *
     * @return self
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Load plugin text domain for translations.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'gf-live-search',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Enqueue scripts and styles only on the Gravity Forms edit forms page.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_assets( $hook ) {
        // Only load on the Gravity Forms "edit forms" page.
        if ( 'forms_page_gf_edit_forms' !== $hook ) {
            return;
        }

        // Ensure Gravity Forms is active.
        if ( ! $this->is_gravity_forms_active() ) {
            return;
        }

        // Enqueue CSS.
        wp_enqueue_style(
            'gf-live-search',
            GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.css',
            array(),
            GF_LIVE_SEARCH_VERSION
        );

        // Enqueue JavaScript.
        wp_enqueue_script(
            'gf-live-search',
            GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.js',
            array(),
            GF_LIVE_SEARCH_VERSION,
            true // Load in footer.
        );

        // Localize script with translatable strings and data.
        wp_localize_script(
            'gf-live-search',
            'gfLiveSearch',
            array(
                'searchPlaceholder' => esc_html__( 'Search forms…', 'gf-live-search' ),
                'noResults'         => esc_html__( 'No forms found.', 'gf-live-search' ),
                'adminUrl'          => esc_url( admin_url() ),
            )
        );
    }

    /**
     * Check if Gravity Forms is active.
     *
     * @return bool
     */
    private function is_gravity_forms_active() {
        return class_exists( 'GFForms' );
    }
}

/**
 * Initialize the plugin.
 */
function gf_live_search_init() {
    return GF_Live_Search::get_instance();
}

// Load the plugin after all plugins are loaded.
add_action( 'plugins_loaded', 'gf_live_search_init', 20 );

/**
 * Display an admin notice if Gravity Forms is not active.
 */
function gf_live_search_admin_notice_missing_gf() {
    if ( ! class_exists( 'GFForms' ) && current_user_can( 'activate_plugins' ) ) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php esc_html_e( 'GF Live Search requires Gravity Forms to be installed and activated.', 'gf-live-search' ); ?>
            </p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'gf_live_search_admin_notice_missing_gf' );