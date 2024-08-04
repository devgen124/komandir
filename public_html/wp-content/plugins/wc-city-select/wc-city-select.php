<?php
/**
 * Plugin Name: WC City Select
 * Plugin URI:  https://wordpress.org/plugins/wc-city-select/
 * Description: City Select for WooCommerce. Show a dropdown select as the cities input.
 * Version:     1.0.8
 * Author:      8manos
 * Author URI:  http://8manos.com
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * WC requires at least: 2.2
 * WC tested up to:      8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// Check if WooCommerce is active
if ( ( is_multisite() && array_key_exists( 'woocommerce/woocommerce.php', get_site_option( 'active_sitewide_plugins', array() ) ) ) ||
     in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	class WC_City_Select {

		// plugin version
		const VERSION = '1.0.8';

		private $plugin_path;
		private $plugin_url;

		private $cities;
		private $dropdown_cities;

		public function __construct() {
//			add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields' ), 10, 2 );
//			add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields' ), 10, 2 );
//			add_filter( 'woocommerce_form_field_city', array( $this, 'form_field_city' ), 10, 4 );

			//js scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

			// Add HPOS compatibility
			add_action( 'before_woocommerce_init', array( $this, 'hposCompatibility' ) );
		}

		public function billing_fields( $fields, $country ) {
			$fields['billing_city']['type'] = 'city';

			return $fields;
		}

		public function shipping_fields( $fields, $country ) {
			$fields['shipping_city']['type'] = 'city';

			return $fields;
		}

		public function get_cities( $cc = null ) {
			if ( empty( $this->cities ) ) {
				$this->load_country_cities();
			}

			if ( ! is_null( $cc ) ) {
				return isset( $this->cities[ $cc ] ) ? $this->cities[ $cc ] : false;
			} else {
				return $this->cities;
			}
		}

		public function load_country_cities() {
			global $cities;

			// Load only the city files the shop owner wants/needs.
			$allowed = array_merge( WC()->countries->get_allowed_countries(), WC()->countries->get_shipping_countries() );

			if ( $allowed ) {
				foreach ( $allowed as $code => $country ) {
					if ( ! isset( $cities[ $code ] ) && file_exists( $this->get_plugin_path() . '/cities/' . $code . '.php' ) ) {
						include( $this->get_plugin_path() . '/cities/' . $code . '.php' );
					}
				}
			}

			$this->cities = apply_filters( 'wc_city_select_cities', $cities );
		}

		private function add_to_dropdown( $item ) {
			$this->dropdown_cities[] = $item;
		}

		public function load_scripts() {
			if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) ) {

				$city_select_path = $this->get_plugin_url() . 'assets/js/city-select.js';
				wp_enqueue_script( 'wc-city-select', $city_select_path, array(
					'jquery',
					'woocommerce'
				), self::VERSION, true );

				$cities = json_encode( $this->get_cities() );
				wp_localize_script( 'wc-city-select', 'wc_city_select_params', array(
					'cities'                => $cities,
					'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
				) );
			}
		}

		public function get_plugin_path() {

			if ( $this->plugin_path ) {
				return $this->plugin_path;
			}

			return $this->plugin_path = plugin_dir_path( __FILE__ );
		}

		public function get_plugin_url() {

			if ( $this->plugin_url ) {
				return $this->plugin_url;
			}

			return $this->plugin_url = plugin_dir_url( __FILE__ );
		}

		public function hposCompatibility() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	}

	$GLOBALS['wc_city_select'] = new WC_City_Select();
}
