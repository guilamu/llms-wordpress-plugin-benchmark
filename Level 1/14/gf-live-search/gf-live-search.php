<?php
/**
 * Plugin Name: GF Live Search
 * Description: Ajoute une recherche en temps réel sur la page liste des formulaires Gravity Forms.
 * Version: 1.0.0
 * Author: Votre Nom
 * Text Domain: gf-live-search
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sécurité : Empêche l'accès direct
}

class GF_Live_Search {

    private $plugin_slug = 'gf-live-search';

    public function __otin() {
        // Chargement des traductions
        add_action( 'init', [ $this, 'load_textdomain' ] );

        // Chargement des scripts et styles uniquement sur la page GF
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Injection de la barre de recherche dans le footer de l'admin
        add_action( 'admin_footer', [ $this, 'inject_search_bar' ] );
    }

    public function load_textdomain() {
        load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Vérifie si nous sommes sur la page de liste Gravity Forms
     */
    private function is_gf_forms_page() {
        return isset( $_GET['page'] ) && $_GET['page'] === 'gf_edit_forms';
    }

    /**
     * Enqueue des assets uniquement si nécessaire
     */
    public function enqueue_assets( $hook ) {
        if ( ! $this->is_gf_forms_page() ) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_slug . '-css',
            plugins_url( 'assets/gf-live-search.css', __FILE__ ),
            [],
            $this->plugin_slug // Versioning via slug pour le cache
        );

        wp_enqueue_script(
            $this->plugin_slug . '-js',
            plugins_url( 'assets/gf-live-search.js', __FILE__ ),
            [ 'jquery' ], // Dépendance jQuery pour injecter le HTML si besoin ou manipuler le DOM
            $this->plugin_slug,
            true // Footer
        );
    }

    /**
     * Injecte le champ HTML de recherche au-dessus du tableau
     */
    public function inject_search_bar() {
        if ( ! $this->is_gf_forms_page() ) {
            return;
        }

        echo '<div class="gf-live-search-container">';
        printf(
            '<input type="text" id="gf-live-search-input" placeholder="%s" autocomplete="off">',
            esc_html__( 'Rechercher un formulaire...', $this->plugin_slug )
        );
        echo '</div>';
    }
}

$gf_live_search = new GF_Live_Search();
$gf_live_search->otin();