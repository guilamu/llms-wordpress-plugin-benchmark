<?php
/**
 * Plugin Name: GF Live Search
 * Description: Ajoute une fonctionnalité de recherche instantanée sur la page de gestion des formulaires Gravity Forms.
 * Version: 1.0
 * Author: Votre Nom
 * Text Domain: gf-live-search
 * Requires Plugins: gravityforms
 */

// Sécurité : Empêche l'accès direct au fichier via URL
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Sortie si le script est accédé directement
}

/**
 * Classe principale du plugin.
 */
class GF_LiveSearch {

    private $text_domain = 'gf-live-search';

    public function __construct() {
        // Hook principal pour l'ajout des styles et scripts dans l'administration
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // Le hook admin_init est utile si nous voulions modifier le HTML de la page elle-même, 
        // mais pour une simple injection JS/CSS conditionnelle, enqueue_assets suffit.
    }

    /**
     * Vérifie la page actuelle et encolère les assets uniquement si c'est la bonne page.
     * @param string $hook Le hook actuel (page admin).
     */
    public function enqueue_assets( $hook ) {
        // 1. Condition de détection : Nous ne chargeons que sur la page des formulaires Gravity Forms.
        $target_slug = 'gf_edit_forms';
        $current_screen = get_current_screen();

        if ( ! is_admin() || $current_screen === null ) {
            return;
        }

        // Vérifie si l'écran actuel correspond à celui des formulaires GF.
        if ( strpos( $hook, 'gf_edit_forms' ) !== false && get_current_screen()->id === 'gf_edit_forms' ) {
            
            // 2. Enqueueing CSS
            wp_enqueue_style(
                'gfls-style', 
                plugin_dir_url( __FILE__ ) . 'assets/gf-live-search.css', 
                array(), 
                '1.0'
            );

            // 3. Enqueueing JS
            wp_enqueue_script(
                'gfls-script', 
                plugin_dir_url( __FILE__ ) . 'assets/gf-live-search.js', 
                array('jquery'), // Dépend de jQuery (standard dans WP Admin)
                '1.0', 
                true // Charger le script au footer
            );

            // Passage des variables PHP vers JavaScript (bonne pratique si nécessaire, ici on passe juste le texte domaine pour i18n côté JS)
            wp_localize_script( 'gfls-script', 'GFLS', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ), // Utile pour les recherches serveur si besoin d'extension
                'nonce'   => wp_create_nonce( 'gfls_search_nonce' ) 
            ) );

        }
    }
}

// Initialisation du plugin
new GF_LiveSearch();


/**
 * Fonction de domaine de traduction (pour i18n).
 */
function gf_live_search_setup() {
    load_plugin_textdomain( 'gf-live-search', false, dirname( __FILE__ ) . '/languages/' );
}
add_action( 'plugins_loaded', 'gf_live_search_setup' );

// NOTE: N'oubliez pas de créer un fichier .pot dans le dossier languages/ 
// en utilisant les outils de WordPress ou Poedit.
