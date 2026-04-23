<?php
/**
 * Plugin Name: GF Live Search
 * Plugin URI:  https://example.com/gf-live-search
 * Description: Ajoute une recherche en temps réel sur la page liste des formulaires Gravity Forms.
 * Version:     1.0.0
 * Author:      Developer
 * Author URI:  https://example.com
 * License:     GPL v2 or later
 * Text Domain: gf-live-search
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GF_LIVE_SEARCH_VERSION', '1.0.0' );
define( 'GF_LIVE_SEARCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_LIVE_SEARCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action( 'admin_enqueue_scripts', 'gf_live_search_enqueue_assets' );
add_action( 'admin_menu', 'gf_live_search_add_search_field' );
add_action( 'wp_ajax_gf_live_search_filter', 'gf_live_search_ajax_filter' );
add_action( 'wp_ajax_nopriv_gf_live_search_filter', 'gf_live_search_ajax_filter' );

function gf_live_search_enqueue_assets( $hook ) {
    if ( 'toplevel_page_gf_edit_forms' !== $hook ) {
        return;
    }

    wp_enqueue_style(
        'gf-live-search',
        GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.css',
        array(),
        GF_LIVE_SEARCH_VERSION
    );

    wp_enqueue_script(
        'gf-live-search',
        GF_LIVE_SEARCH_PLUGIN_URL . 'assets/gf-live-search.js',
        array( 'jquery' ),
        GF_LIVE_SEARCH_VERSION,
        true
    );

    wp_localize_script(
        'gf-live-search',
        'gfLiveSearch',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'gf_live_search_nonce' ),
        )
    );
}

function gf_live_search_add_search_field() {
    add_action( 'admin_head', 'gf_live_search_render_search_field' );
}

function gf_live_search_render_search_field() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var header = document.querySelector('.wrap h1');
        if (!header) return;

        var container = document.createElement('div');
        container.className = 'gf-live-search-wrap';

        var input = document.createElement('input');
        input.type = 'search';
        input.id = 'gf-live-search-input';
        input.placeholder = <?php echo wp_json_encode( esc_html__( 'Rechercher un formulaire…', 'gf-live-search' ) ); ?>;
        input.className = 'gf-live-search-input';
        input.autocomplete = 'off';

        container.appendChild(input);
        header.parentNode.insertBefore(container, header.nextSibling);
    });
    </script>
    <?php
}

function gf_live_search_ajax_filter() {
    check_ajax_referer( 'gf_live_search_nonce', 'nonce' );

    if ( ! current_user_can( 'gravityforms_view_forms' ) ) {
        wp_send_json_error( array( 'message' => __( 'Permission refusée.', 'gf-live-search' ) ) );
    }

    $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

    if ( empty( $search ) ) {
        wp_send_json_success( '' );
    }

    global $wpdb;
    $table = $wpdb->prefix . 'rg_form';

    $like = '%' . $wpdb->esc_like( $search ) . '%';
    $forms = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table} WHERE title LIKE %s OR cast(id as char) LIKE %s ORDER BY id DESC LIMIT 100",
            $like,
            $like
        )
    );

    ob_start();
    if ( ! empty( $forms ) ) {
        foreach ( $forms as $form ) {
            printf(
                '<tr class="alternate" data-form-id="%d"><td class="check-column"><input type="checkbox" name="form_ids[]" value="%d"></td><td><strong><a href="?page=gf_edit_forms&id=%d">%s</a></strong></td><td>%s</td><td>%s</td></tr>',
                esc_attr( $form->id ),
                esc_attr( $form->id ),
                esc_attr( $form->id ),
                esc_html( $form->title ),
                esc_html( $form->date_created ),
                esc_html__( 'Active', 'gf-live-search' )
            );
        }
    } else {
        printf(
            '<tr><td colspan="4">%s</td></tr>',
            esc_html__( 'Aucun formulaire trouvé.', 'gf-live-search' )
        );
    }
    $html = ob_get_clean();

    wp_send_json_success( $html );
}
