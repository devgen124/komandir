<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             2.0.0
 * @package           YooKassa
 *
 * @wordpress-plugin
 * Plugin Name:       ЮKassa для WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/yookassa/
 * Description:       Платежный модуль для работы с сервисом ЮKassa через плагин WooCommerce
 * Version:           2.11.0
 * Author:            YooMoney
 * Author URI:        http://yookassa.ru
 * License URI:       https://yoomoney.ru/doc.xml?id=527132
 * Text Domain:       yookassa
 * Domain Path:       /languages
 *
 * Requires Plugins: woocommerce
 * Requires at least: 5.2
 * Tested up to: 6.7
 * WC requires at least: 3.7
 * WC tested up to: 9.6
 */
// If this file is called directly, abort.

if (!defined('WPINC')) {
    die;
}

function yookassa_plugin_activate()
{
    if (!yookassa_check_woocommerce_plugin_status()) {
        deactivate_plugins(__FILE__);
        $error_message = __("Плагин ЮKassa для WooCommerce требует, чтобы плагин <a href=\"https://wordpress.org/extend/plugins/woocommerce/\" target=\"_blank\">WooCommerce</a> был активен!", 'yookassa');
        wp_die($error_message);
    }
    require_once plugin_dir_path(__FILE__) . 'includes/YooKassaActivator.php';
    YooKassaActivator::activate();
}

function yookassa_plugin_deactivate()
{
    require_once plugin_dir_path(__FILE__) . 'includes/YooKassaDeactivator.php';
    YooKassaDeactivator::deactivate();
}

/**
 * @return bool
 */
function yookassa_check_woocommerce_plugin_status()
{
    if (defined("RUNNING_CUSTOM_WOOCOMMERCE") && RUNNING_CUSTOM_WOOCOMMERCE === true) {
        return true;
    }
    if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        return true;
    }
    if (!is_multisite()) return false;
    $plugins = get_site_option('active_sitewide_plugins');
    return isset($plugins['woocommerce/woocommerce.php']);
}

register_activation_hook(__FILE__, 'yookassa_plugin_activate');
register_deactivation_hook(__FILE__, 'yookassa_plugin_deactivate');

if (yookassa_check_woocommerce_plugin_status()) {
    require_once plugin_dir_path(__FILE__) . 'includes/YooKassa.php';

    add_action( 'before_woocommerce_init', function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
        }
    } );

    $plugin = new YooKassa();

    define('YOOKASSA_VERSION', $plugin->getVersion());

    $plugin->run();
}

add_action( 'woocommerce_blocks_loaded', 'yookassa_gateway_block_support' );
function yookassa_gateway_block_support() {

    if( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-yookassa-blocks-support.php';

    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
            foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) {
                /** @var YooKassaGateway $gateway */
                if ($gateway->enabled === 'yes' && property_exists($gateway, 'pluginKey') && $gateway->pluginKey === 'yookassa') {
                    $payment_method_registry->register(new WC_YooKassa_Blocks_Support($gateway->id));
                }
            }
            if (version_compare(WC()->version, '8.0.3', '<') ) { // For old versions of WooCommerce
                $script_data = [];
                foreach ($payment_method_registry->get_all_registered() as $payment_method) {
                    $script_data[$payment_method->get_name()] = $payment_method->get_payment_method_data();
                }
                $asset_registry = Automattic\WooCommerce\Blocks\Package::container()->get( Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class );
                if (!empty( $script_data ) && ! $asset_registry->exists( 'paymentMethodData')) {
                    $asset_registry->add('paymentMethodData', $script_data);
                }
            }
        }
    );

}
