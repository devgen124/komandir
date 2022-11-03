<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wishlist-webtoffee-public.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wishlist-webtoffee-public.js', array('jquery'), $this->version, false);

        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_add', array('add_to_wishlist' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('add_to_wishlist'),'wishlist_loader' => WEBTOFFEE_WISHLIST_BASEURL .'public/images/wt-loader.svg','wishlist_favourite' => WEBTOFFEE_WISHLIST_BASEURL .'public/images/favourite.svg' ));
        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_myaccount_bulk_add_to_cart', array('myaccount_bulk_add_to_cart' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('bulk_add_to_cart')));
        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_myaccount_clear_list', array('myaccount_clear_list' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('clear_list')));
        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_single_add_to_cart', array('single_add_to_cart' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('single_add_to_cart')));
    }

    /**
	 * Sets session value, to store in database to identify guest user.
	 */
    public function set_session_value(){
		if(! is_user_logged_in()){
            if(WC()->session){
                if ( ! WC()->session->get('sessionid') ) {
                    WC()->session->set_customer_session_cookie( true );
        
                    WC()->session->set('sessionid', self::generateRandomString());
                    
                }
            }
			
		}
		
    }
    /**
	 * Generates a random value for session value.
	 */
	protected static function generateRandomString( $length = 24 ) {
		$characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTU';
		$charactersLength = strlen( $characters );
		$randomString     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$randomString .= $characters[ rand( 0, $charactersLength - 1 ) ];
		}

		return $randomString;
    }
    
}