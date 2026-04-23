<?php
/**
 * Plugin Name: GF Live Search
 * Plugin URI:  https://github.com/yourname/gf-live-search
 * Description: Adds real-time search to the Gravity Forms form list page.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://yourwebsite.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gf-live-search
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class GF_Live_Search {

    /**
     * Singleton instance.
     *
     * @var GF_Live_Search|null
     */
    private static ?GF_Live_Search $instance = null;

    /**
     * Returns the singleton instance.
     */
    public static function get_instance(): GF_Live_Search {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor – registers hooks.
     */
    private function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_footer',          [ $this, 'render_search_input' ] );
        add_action( 'init',                  [ $this, 'load_textdomain' ] );
    }

    /**
     * Loads plugin translations.
     */
    public function load_textdomain(): void {
        load_plugin_textdomain(
            'gf-live-search',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    /**
     * Enqueues CSS and JS only on the Gravity Forms list page.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public function enqueue_assets( string $hook_suffix ): void {
        if ( 'toplevel_page_gf_edit_forms' !== $hook_suffix ) {
            return;
        }

        $version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : '1.0.0';

        wp_enqueue_style(
            'gf-live-search',
            plugins_url( 'assets/gf-live-search.css', __FILE__ ),
            [],
            $version
        );

        wp_enqueue_script(
            'gf-live-search',
            plugins_url( 'assets/gf-live-search.js', __FILE__ ),
            [],
            $version,
            true
        );

        wp_localize_script( 'gf-live-search', 'gfLiveSearch', [
            'placeholder' => __( 'Search forms…', 'gf-live-search' ),
        ] );
    }

    /**
     * Injects the search input into the page via admin_footer.
     */
    public function render_search_input(): void {
        $screen = get_current_screen();

        if ( ! $screen || 'toplevel_page_gf_edit_forms' !== $screen->id ) {
            return;
        }

        $placeholder = esc_attr__( 'Search forms…', 'gf-live-search' );

        printf(
            '<div id="gf-live-search-wrap" style="margin-bottom:12px;">
                <input type="search" id="gf-live-search-input" class="gf-live-search-input" placeholder="%s" />
            </div>',
            $placeholder
        );
    }
}

GF_Live_Search::get_instance();
