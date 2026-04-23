<?php
/**
 * Plugin Name: GF Live Search
 * Plugin URI:  
 * Description: Ajoute une recherche en temps réel à la liste des formulaires Gravity Forms.
 * Version:     1.0.0
 * Author:      
 * Author URI:  
 * Text Domain: gf-live-search
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class GF_Live_Search {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    /**
     * Load plugin textdomain for internationalization.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'gf-live-search', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Enqueue scripts and styles only on the Gravity Forms edit page.
     *
     * @param string $hook The current admin page.
     */
    public function enqueue_scripts( $hook ) {
        // Retrieve 'page' and 'id' variables safely
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
        $id   = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        
        // Target specifically the form list page (page=gf_edit_forms without form ID)
        if ( $page !== 'gf_edit_forms' || $id > 0 ) {
            return;
        }

        wp_enqueue_style(
            'gf-live-search-style',
            plugin_dir_url( __FILE__ ) . 'assets/gf-live-search.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'gf-live-search-script',
            plugin_dir_url( __FILE__ ) . 'assets/gf-live-search.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );

        wp_localize_script(
            'gf-live-search-script',
            'gfLiveSearchData',
            array(
                'searchPlaceholder' => esc_html__( 'Live search forms...', 'gf-live-search' ),
                'noResults'         => esc_html__( 'No forms found.', 'gf-live-search' ),
            )
        );
    }
}

// Initialize the plugin
new GF_Live_Search();
