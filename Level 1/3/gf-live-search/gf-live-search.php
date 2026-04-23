<?php
/**
 * Plugin Name:       GF Live Search
 * Plugin URI:        https://example.com/gf-live-search
 * Description:       Adds real-time filtering to the Gravity Forms forms list page.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Author Name
 * Author URI:        https://example.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 *
 * GF Live Search is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * GF Live Search is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GF_LS_VERSION', '1.0.0' );
define( 'GF_LS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GF_LS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

final class GF_Live_Search {

    private static ?self $instance = null;

    public static function get_instance(): self {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_hooks();
    }

    private function define_hooks(): void {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'gf-live-search',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages'
        );
    }

    public function enqueue_assets( string $hook_suffix ): void {
        if ( ! $this->is_gf_edit_forms_page() ) {
            return;
        }

        wp_enqueue_style(
            'gf-live-search',
            GF_LS_PLUGIN_URL . 'assets/gf-live-search.css',
            array(),
            GF_LS_VERSION
        );

        wp_enqueue_script(
            'gf-live-search',
            GF_LS_PLUGIN_URL . 'assets/gf-live-search.js',
            array(),
            GF_LS_VERSION,
            true
        );

        wp_localize_script( 'gf-live-search', 'gf_ls', array(
            'placeholder' => esc_html__( 'Search forms…', 'gf-live-search' ),
            'no_results'  => esc_html__( 'No forms found.', 'gf-live-search' ),
        ) );
    }

    private function is_gf_edit_forms_page(): bool {
        if ( ! is_admin() ) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

        if ( 'gf_edit_forms' !== $page ) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $id = isset( $_GET['id'] ) ? sanitize_text_field( wp_unslash( $_GET['id'] ) ) : '';

        // Only target the list page, not the form editor.
        return '' === $id;
    }
}

GF_Live_Search::get_instance();