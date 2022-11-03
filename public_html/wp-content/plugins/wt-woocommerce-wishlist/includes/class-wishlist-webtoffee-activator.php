<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

class Wishlist_Webtoffee_Activator {

    public function __construct() {
        
    }

    public static function activate() {

        if ( class_exists( 'WooCommerce' ) ) {
            global $wpdb;
            $search_query = "SHOW TABLES LIKE %s";
            $charset_collate = $wpdb->get_charset_collate();
            $like = '%' . $wpdb->prefix . 'wt_wishlists%';
            if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
                $table_name = $wpdb->prefix . 'wt_wishlists';
                $sql_settings = "CREATE TABLE $table_name 
                    (
                        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `user_id` int(11) NOT NULL DEFAULT '0',
                        `product_id` int(11) NOT NULL DEFAULT '0',
                                            `variation_id` int(11) NOT NULL DEFAULT '0',
                        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `quantity` int(11) NOT NULL DEFAULT '1',
                        PRIMARY KEY (`id`)
                    )$charset_collate;";
                dbDelta($sql_settings);
            }

            //creates a table for guest users
            $like_2 = '%' . $wpdb->prefix . 'wt_guest_wishlists%';
            if (!$wpdb->get_results($wpdb->prepare($search_query, $like_2), ARRAY_N)) {
                $table_name_2 = $wpdb->prefix . 'wt_guest_wishlists';
                $sql_settings_2 = "CREATE TABLE $table_name_2 
                    (
                        `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `user_id` int(11) NOT NULL DEFAULT '0',
                        `session_id` VARCHAR( 255 ) DEFAULT NULL,
                        `product_id` int(11) NOT NULL DEFAULT '0',
                        `variation_id` int(11) NOT NULL DEFAULT '0',
                        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                        `quantity` int(11) NOT NULL DEFAULT '1',
                        PRIMARY KEY (`id`)
                    )$charset_collate;";
                dbDelta($sql_settings_2);
            }

            //creates a new wishlist page upon activation/update
            $wishlist_page_id = get_option( 'wt_webtoffee-wishlist_page_id' );
            if(empty($wishlist_page_id)){
                wc_create_page(
                    sanitize_title_with_dashes( _x( 'wt-webtoffee-wishlist', 'page_slug', 'wt-woocommerce-wishlist' ) ),
                    'wt_webtoffee-wishlist_page_id',
                    __( 'My Wishlist', 'wt-woocommerce-wishlist' ),
                    '<!-- wp:shortcode -->[wt_mywishlist]<!-- /wp:shortcode -->'
                );
            }

            $default_value_added = get_option( 'default_value_added' );

            if(! $default_value_added){
                global $wt_wishlist_general_settings_options,$wt_wishlist_table_settings_options;
                $wishlist_general_saved_option = get_option('wt_wishlist_general_settings');
                if(!empty($wishlist_general_saved_option)){

                    $wishlist_general_options = array(
                        'wt_enable_product_categories' => array('all'),
                        'wt_add_to_myaccount' => 1,
                    );
                    
                    $wishlist_table_settings_options = array(
                        'wt_enable_unit_price_column'            => 1,
                        'wt_enable_stock_status_column'          => 1,
                        'wt_enable_add_to_cart_option_column'    => 1,
                        'redirect_to_cart'                       => 1,
                    );
                    $wishlist_table_settings_saved_option = get_option('wt_wishlist_table_settings');
                    if (! isset($wishlist_general_saved_option['wt_enable_product_categories']) ) {
                        foreach ( $wishlist_table_settings_options as $key => $option ) {
                            $wishlist_table_settings_saved_option[$key] = $option;
                        }
                    }
                    if (! isset($wishlist_general_saved_option['wt_enable_product_categories']) ) {
                        foreach ( $wishlist_general_options as $key => $option ) {
                            $wishlist_general_saved_option[$key] = $option;
                        }
                    }
        
                    update_option( 'default_value_added', true );
                    
                    update_option("wt_wishlist_general_settings", $wishlist_general_saved_option);
                    update_option("wt_wishlist_table_settings", $wishlist_table_settings_saved_option);
                }
            
            }
        }

    }

}