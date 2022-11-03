<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link                 https://www.webtoffee.com
 * @since                1.0.0
 * @package              Wishlist_Webtoffee
 *
 * @wordpress-plugin
 * Plugin Name:          Wishlist for WooCommerce
 * Plugin URI:           https://wordpress.org/plugins/wt-woocommerce-wishlist/
 * Description:          Manage WooCommerce Wishlist
 * Version:              2.0.5
 * Author:               WebToffee
 * Author URI:           https://www.webtoffee.com/
 * License:              GPLv3
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          wt-woocommerce-wishlist
 * Domain Path:          /languages
 * WC requires at least: 2.7
 * WC tested up to:      5.9.0
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}


define('WEBTOFFEE_WISHLIST_BASEPATH', plugin_dir_path(__FILE__));
define('WEBTOFFEE_WISHLIST_BASEURL', plugin_dir_url(__FILE__));
define('WEBTOFFEE_WISHLIST_FILENAME',__FILE__);



/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('WEBTOFFEE_WISHLIST_VERSION', '2.0.5');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wishlist-webtoffee-activator.php
 */
function activate_wishlist_webtoffee() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wishlist-webtoffee-activator.php';
    Wishlist_Webtoffee_Activator::activate();
}

register_activation_hook(__FILE__, 'Wishlist_Account_View::install');

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wishlist-webtoffee-deactivator.php
 */
function deactivate_wishlist_webtoffee() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wishlist-webtoffee-deactivator.php';
    @delete_option( 'wt_wishlist_table_settings' );
    Wishlist_Webtoffee_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wishlist_webtoffee');
register_deactivation_hook(__FILE__, 'deactivate_wishlist_webtoffee');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wishlist-webtoffee.php';

//global options variable
$wt_wishlist_general_settings_options = get_option('wt_wishlist_general_settings');
$wt_wishlist_table_settings_options = get_option('wt_wishlist_table_settings');
$wt_wishlist_button_style_settings_options = get_option('wt_wishlist_button_style_settings');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wishlist_webtoffee() {

    $plugin = new Wishlist_Webtoffee();
    $plugin->run();
}

require plugin_dir_path(__FILE__) . 'includes/class-wishlist-singlepage.php';
require plugin_dir_path(__FILE__) . 'includes/class-wishlist-looppage.php';
require plugin_dir_path(__FILE__) . 'public/partials/wishlist-account-view.php';

require_once plugin_dir_path(__FILE__) . 'includes/class-wt-wishlist-uninstall-feedback.php';



//TODO Move to inner page
function add_settings_link_wt_wishlist($links) {
    
    $plugin_links = array(
        '<a href="' . esc_url(admin_url('admin.php?page=wishlist-webtoffee')) . '">' . __('Settings', 'wt-woocommerce-wishlist') . '</a>',
        '<a target="_blank" href="https://wordpress.org/support/plugin/wt-woocommerce-wishlist">' . __('Support', 'wt-woocommerce-wishlist') . '</a>',
        '<a target="_blank" href="https://wordpress.org/support/plugin/wt-woocommerce-wishlist/reviews/#new-post">' . __('Review', 'wt-woocommerce-wishlist') . '</a>'
    );
    if (array_key_exists('deactivate', $links)) {

        $links['deactivate'] = str_replace('<a', '<a class="wtwishlist-deactivate-link"', $links['deactivate']);
    }
    return array_merge($plugin_links, $links);
    
}

// Woocommerce plugin active check
add_action('plugins_loaded', 'wt_wishlist_wc_active_check', 1);

function wt_wishlist_wc_active_check() {

	if ( class_exists( 'WooCommerce' ) ) {

		add_action('plugin_action_links_' . plugin_basename(__FILE__), 'add_settings_link_wt_wishlist');
        run_wishlist_webtoffee();

	}else{
        add_action( 'admin_notices', 'wt_wishlist_wc_missing_notice' );
		return;
    }
}

function wt_wishlist_wc_missing_notice() {

    deactivate_plugins( plugin_basename(__FILE__) );

    $install_url = wp_nonce_url( add_query_arg( array( 'action' => 'install-plugin', 'plugin' => 'woocommerce', ), admin_url( 'update.php' ) ), 'install-plugin_woocommerce' );
	$class		 = 'notice notice-error';
	$message	 = sprintf( __( 'The <b>WooCommerce</b> plugin must be active for <b>Wishlist for WooCommerce (WebToffee) </b> plugin to work.  Please <a href="%s" target="_blank">install & activate WooCommerce</a>.' ), esc_url( $install_url ) );
	printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), ( $message ) );

}

