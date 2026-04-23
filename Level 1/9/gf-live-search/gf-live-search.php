<?php
/**
 * Plugin Name:       GF Live Search
 * Description:       Adds real-time filtering to the Gravity Forms forms list in the WordPress admin.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       gf-live-search
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GF_Live_Search' ) ) {
	final class GF_Live_Search {
		private const VERSION = '1.0.0';
		private const TEXT_DOMAIN = 'gf-live-search';
		private const PAGE_SLUG = 'gf_edit_forms';

		private static ?GF_Live_Search $instance = null;

		public static function get_instance(): GF_Live_Search {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		private function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		}

		public function load_textdomain(): void {
			load_plugin_textdomain(
				self::TEXT_DOMAIN,
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);
		}

		public function enqueue_assets( string $hook_suffix ): void {
			if ( ! $this->is_target_screen( $hook_suffix ) ) {
				return;
			}

			$asset_url = plugin_dir_url( __FILE__ ) . 'assets/';

			wp_enqueue_style(
				'gf-live-search-admin',
				$asset_url . 'gf-live-search.css',
				array(),
				self::VERSION
			);

			wp_enqueue_script(
				'gf-live-search-admin',
				$asset_url . 'gf-live-search.js',
				array(),
				self::VERSION,
				true
			);

			wp_localize_script(
				'gf-live-search-admin',
				'gfLiveSearch',
				array(
					'emptyMessage' => __( 'No forms match your search.', self::TEXT_DOMAIN ),
					'label'        => __( 'Live search forms', self::TEXT_DOMAIN ),
					'placeholder'  => __( 'Search forms...', self::TEXT_DOMAIN ),
				)
			);
		}

		private function is_target_screen( string $hook_suffix ): bool {
			$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

			if ( self::PAGE_SLUG !== $page ) {
				return false;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			if ( ! $screen ) {
				return true;
			}

			return in_array(
				$hook_suffix,
				array( 'toplevel_page_gf_edit_forms', 'forms_page_gf_edit_forms' ),
				true
			) || in_array(
				$screen->id,
				array( 'toplevel_page_gf_edit_forms', 'forms_page_gf_edit_forms', 'gf_edit_forms' ),
				true
			);
		}
	}
}

GF_Live_Search::get_instance();