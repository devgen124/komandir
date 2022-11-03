<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

class Wishlist_Account_View {

	public static $endpoint = 'webtoffee-wishlist';

	public function __construct() {

		$wt_wishlist_general_settings_options = get_option('wt_wishlist_general_settings');
		$add_to_myaccount = isset($wt_wishlist_general_settings_options['wt_add_to_myaccount']) ? $wt_wishlist_general_settings_options['wt_add_to_myaccount'] : '';

		if($add_to_myaccount){
			// Actions used to insert a new endpoint in the WordPress.
			add_action( 'init', array( $this, 'add_endpoints' ) );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

			// Change the My Accout page title.
			add_filter( 'the_title', array( $this, 'endpoint_title' ) );

			// Insering your new tab/page into the My Account page.
			add_filter( 'woocommerce_account_menu_items', array( $this, 'wt_wishlist_menu' ) );
		    add_action( 'woocommerce_account_' . self::$endpoint . '_endpoint',  __CLASS__ . '::endpoint_content' );

		}

	}

	public function add_endpoints() {
		add_rewrite_endpoint(self::$endpoint, EP_ROOT | EP_PAGES);
		flush_rewrite_rules();
	}

	public function add_query_vars($vars) {
		$vars[] = self::$endpoint;

		return $vars;
	}

	public function endpoint_title($title) {

		global $wp_query;
		$wishlist_title = 'Wishlists';

		$is_endpoint = isset($wp_query->query_vars[self::$endpoint]);
		if ($is_endpoint && !is_admin() && is_main_query() && in_the_loop() && is_account_page()) {
			$title = __($wishlist_title, 'wt-woocommerce-wishlist');
			remove_filter('the_title', array($this, 'endpoint_title'));
		}
		return $title;
	}

	public function wt_wishlist_menu($items) {

        $title = __('My Wishlist', 'wt-woocommerce-wishlist');

		$logout = $items['customer-logout'];
		unset($items['customer-logout']);
		$items[self::$endpoint] = $title;
		$items['customer-logout'] = $logout;
		return $items;
	}

	public static function endpoint_content() {
            
		global $wpdb;
		$table_name = $wpdb->prefix . 'wt_wishlists';
		$user = get_current_user_id();
		if(is_user_logged_in()){
			$products = $wpdb->get_results("SELECT * FROM `$table_name` where `user_id` = '$user'", ARRAY_A);
		}else{
			$table_name = $wpdb->prefix . 'wt_guest_wishlists';
			$session_id = WC()->session->get('sessionid');
			$products = $wpdb->get_results("SELECT * FROM `$table_name` where `session_id` = '$session_id'", ARRAY_A);
		}
		require_once ('wishlist-account-view-frontend.php');
	}

	public static function install() {
		flush_rewrite_rules();
	}

}

new Wishlist_Account_View();