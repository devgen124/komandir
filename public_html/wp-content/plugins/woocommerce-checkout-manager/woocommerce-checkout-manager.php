<?php

/**
 * Plugin Name: Checkout Fields Manager for WooCommerce
 * Plugin URI:  https://quadlayers.com/portfolio/woocommerce-checkout-manager/
 * Description: Manage and customize WooCommerce Checkout fields (Add, Edit, Delete or re-order fields).
 * Version:     6.3.5
 * Author:      QuadLayers
 * Author URI:  https://quadlayers.com
 * License: GPLv3
 * Text Domain: woocommerce-checkout-manager
 * WC requires at least: 3.1.0
 * WC tested up to: 7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'WOOCCM_PLUGIN_NAME', 'Checkout Fields Manager for WooCommerce' );
define( 'WOOCCM_PLUGIN_VERSION', '6.3.5' );
define( 'WOOCCM_PLUGIN_FILE', __FILE__ );
define( 'WOOCCM_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'WOOCCM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOOCCM_PREFIX', 'wooccm' );
define( 'WOOCCM_WORDPRESS_URL', 'https://wordpress.org/plugins/woocommerce-checkout-manager/' );
define( 'WOOCCM_REVIEW_URL', 'https://wordpress.org/support/plugin/woocommerce-checkout-manager/reviews/?filter=5#new-post' );
define( 'WOOCCM_DOCUMENTATION_URL', 'https://quadlayers.com/documentation/woocommerce-checkout-manager/?utm_source=wooccm_admin' );
define( 'WOOCCM_DEMO_URL', 'https://quadlayers.com/portfolio/woocommerce-checkout-manager/?utm_source=wooccm_admin' );
define( 'WOOCCM_PURCHASE_URL', WOOCCM_DEMO_URL );
define( 'WOOCCM_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=wooccm_admin' );
define( 'WOOCCM_GROUP_URL', 'https://www.facebook.com/groups/quadlayers' );
define( 'WOOCCM_DEVELOPER', false );

define( 'WOOCCM_PREMIUM_SELL_SLUG', 'woocommerce-checkout-manager-pro' );
define( 'WOOCCM_PREMIUM_SELL_NAME', 'WooCommerce Checkout Manager' );
define( 'WOOCCM_PREMIUM_SELL_URL', 'https://quadlayers.com/portfolio/woocommerce-checkout-manager/?utm_source=wooccm_admin' );

define( 'WOOCCM_CROSS_INSTALL_SLUG', 'woocommerce-direct-checkout' );
define( 'WOOCCM_CROSS_INSTALL_NAME', 'Direct Checkout' );
define( 'WOOCCM_CROSS_INSTALL_DESCRIPTION', esc_html__( 'Direct Checkout for WooCommerce allows you to reduce the steps in the checkout process by skipping the shopping cart page. This can encourage buyers to shop more and quickly. You will increase your sales reducing cart abandonment.', 'woocommerce-checkout-manager' ) );
define( 'WOOCCM_CROSS_INSTALL_URL', 'https://quadlayers.com/portfolio/woocommerce-checkout-manager/?utm_source=wooccm_admin' );

if ( ! class_exists( 'WOOCCM', false ) ) {
	include_once WOOCCM_PLUGIN_DIR . 'includes/class-wooccm.php';
}

require_once WOOCCM_PLUGIN_DIR . 'includes/quadlayers/widget.php';
require_once WOOCCM_PLUGIN_DIR . 'includes/quadlayers/notices.php';
require_once WOOCCM_PLUGIN_DIR . 'includes/quadlayers/links.php';

function WOOCCM() {     // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	return WOOCCM::instance();
}

// Global for backwards compatibility.
$GLOBALS['wooccm'] = WOOCCM();
