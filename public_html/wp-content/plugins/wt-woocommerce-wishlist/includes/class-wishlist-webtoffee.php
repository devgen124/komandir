<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee {

    protected $loader;
    protected $plugin_name;
    protected $version;

	public static $option_prefix = 'wishlist_webtoffee';


    public function __construct() {
        if (defined('WEBTOFFEE_WISHLIST_VERSION')) {
            $this->version = WEBTOFFEE_WISHLIST_VERSION;
        } else {
            $this->version = '2.0.5';
        }
        $this->plugin_name = 'wishlist-webtoffee';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }



    private function load_dependencies() {

        $plugin_dir_path = plugin_dir_path(dirname(__FILE__));
        require_once $plugin_dir_path . 'includes/class-wishlist-webtoffee-loader.php';
        require_once $plugin_dir_path . 'includes/class-wishlist-webtoffee-i18n.php';
        require_once $plugin_dir_path . 'admin/class-wishlist-webtoffee-admin.php';
        require_once $plugin_dir_path . 'public/class-wishlist-webtoffee-public.php';
        require_once $plugin_dir_path . 'includes/wishlist-wt-review-request.php';

        $this->loader = new Wishlist_Webtoffee_Loader();
    }

    private function set_locale() {

        $plugin_i18n = new Wishlist_Webtoffee_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {

	    $plugin_admin = new Wishlist_Webtoffee_Admin( $this->get_plugin_name(), $this->get_version() );


	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
	    $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_filter( 'woocommerce_screen_ids', $plugin_admin,'add_wt_screen_id' );
        
        //register settings
        $this->loader->add_action( 'admin_init', $plugin_admin, 'wt_register_settings' );
        $this->loader->add_action( 'wp_ajax_add_to_wishlist', $plugin_admin, 'add_to_wishlist' );
        $this->loader->add_action( 'wp_ajax_nopriv_add_to_wishlist', $plugin_admin, 'add_to_wishlist' );
        
        $this->loader->add_action( 'wp_ajax_myaccount_bulk_add_to_cart_action', $plugin_admin, 'myaccount_bulk_add_to_cart_action' );
        $this->loader->add_action( 'wp_ajax_nopriv_myaccount_bulk_add_to_cart_action', $plugin_admin, 'myaccount_bulk_add_to_cart_action' );

        $this->loader->add_action( 'wp_ajax_myaccount_clear_list', $plugin_admin, 'myaccount_clear_list' );
        $this->loader->add_action( 'wp_ajax_nopriv_myaccount_clear_list', $plugin_admin, 'myaccount_clear_list' );

        $this->loader->add_action( 'wp_ajax_single_add_to_cart_action', $plugin_admin, 'single_add_to_cart_action' );
        $this->loader->add_action( 'wp_ajax_nopriv_single_add_to_cart_action', $plugin_admin, 'single_add_to_cart_action' );

	    // settings page
    }

   

    private function define_public_hooks() {

        $plugin_public = new Wishlist_Webtoffee_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action( 'init', $plugin_public, 'set_session_value' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }

}