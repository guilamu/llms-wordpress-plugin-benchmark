<?php
/**
 * Plugin Name:       GF Live Search
 * Description:       Ajoute une recherche en temps réel sur la page de liste des formulaires Gravity Forms.
 * Version:           1.0.0
 * Author:            OpenCode
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.4
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class GF_Live_Search
 *
 * Adds real-time filtering to the Gravity Forms list table.
 */
class GF_Live_Search {

    /**
     * Plugin version.
     *
     * @var string
     */
    const VERSION = '1.0.0';

    /**
     * Plugin slug (text domain).
     *
     * @var string
     */
    const SLUG = 'gf-live-search';

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Load plugin textdomain for translations.
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            self::SLUG,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );
    }

    /**
     * Enqueue scripts and styles only on the Gravity Forms list page.
     *
     * @param string $hook_suffix The current admin page hook.
     */
    public function enqueue_assets( string $hook_suffix ): void {
        // Only enqueue on the Gravity Forms "Forms" list page.
        if ( ! isset( $_GET['page'] ) || 'gf_edit_forms' !== $_GET['page'] ) {
            return;
        }

        $plugin_url = plugin_dir_url( __FILE__ );

        wp_enqueue_style(
            self::SLUG . '-css',
            $plugin_url . 'assets/gf-live-search.css',
            array(),
            self::VERSION
        );

        wp_enqueue_script(
            self::SLUG . '-js',
            $plugin_url . 'assets/gf-live-search.js',
            array(),
            self::VERSION,
            true
        );

        wp_localize_script(
            self::SLUG . '-js',
            'gfLiveSearch',
            array(
                'inputPlaceholder' => esc_attr__( 'Rechercher un formulaire...', 'gf-live-search' ),
                'noResultsText'    => esc_html__( 'Aucun résultat trouvé.', 'gf-live-search' ),
            )
        );
    }
}

new GF_Live_Search();
