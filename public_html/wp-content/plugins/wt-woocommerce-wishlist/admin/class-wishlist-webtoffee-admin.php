<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee_Admin {

    private $plugin_name;
    private $version;


    public function __construct($plugin_name, $version) {


        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        // add_filter( 'woocommerce_login_redirect',  [$this,'wt_wishlist_login_redirect'],10,2 );
        add_shortcode('wt_mywishlist', array('Wishlist_Account_View', 'endpoint_content'));
        add_action('admin_notices', array($this, 'show_banner'));
    }

    public function show_banner() {
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        if ('wishlist-webtoffee' != $page)
            return;
?>
        <div class="webtoffee_banner">
            <p class="wishlist_version">
                <?php echo "Version " . WEBTOFFEE_WISHLIST_VERSION ?>
            </p>

        </div>
<?php
    }

    public function wt_wishlist_login_redirect($redirect, $user) {


        global $wpdb;
        if (isset($_GET['product_id'])) {
            $product_id   = absint($_GET['product_id']);
            $variation_id = isset($_GET['variation_id']) ? absint($_GET['variation_id']) : '';
            $user_id = $user->ID;


            $table_name = $wpdb->prefix . 'wt_wishlists';
            $query_check_already_exists = "SELECT COUNT(*)  from  $table_name where `product_id` = '$product_id' and `user_id` = '$user_id'";
            //todo include variable id too

            if (!$wpdb->get_var($query_check_already_exists)) {
                $query_wp = "INSERT INTO $table_name 
                    (`user_id`, `product_id`, `variation_id`) 
                    VALUES 
                        ('$user_id', '$product_id', '$variation_id')
                       
                    ";

                $wpdb->query($query_wp);
            }

            $location = $_GET['redirect'];
            wp_redirect($location);
        }
    }

    public function enqueue_styles() {

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wishlist-webtoffee-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts() {

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wishlist-webtoffee-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'webtoffee_wishlist_ajax_add', array('add_to_wishlist' => admin_url('admin-ajax.php'), 'wt_nonce' => wp_create_nonce('add_to_wishlist')));
    }

    function add_to_wishlist() {


        check_ajax_referer('add_to_wishlist', 'wt_nonce');
        $product_id   = absint($_POST['product_id']);
        $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : '';
        $quantity     = absint($_POST['quantity']);
        $act          = sanitize_text_field($_POST['act']);
        $user         = get_current_user_id();
        $session_id   = WC()->session->get('sessionid');

        if ($act == 'remove') {
            $result = array(
                'img_change_url' => WEBTOFFEE_WISHLIST_BASEURL . 'public/images/unfavourite.svg',
                'img_change_url_icon' => WEBTOFFEE_WISHLIST_BASEURL . 'public/images/icon_unfavourite.png',
            );
        } else {
            $result = array(
                'img_change_url' => WEBTOFFEE_WISHLIST_BASEURL . 'public/images/favourite.svg',
                'img_change_url_icon' => WEBTOFFEE_WISHLIST_BASEURL . 'public/images/icon_favourite.png',
            );
        }
        global $wpdb;
        if ($user != 0) {
            $table_name = $wpdb->prefix . 'wt_wishlists';

            if ('add' == $act) {
                $query_check = "SELECT id FROM `$table_name` WHERE `user_id` = '$user' and `variation_id` = '$variation_id'and `product_id` = '$product_id'";
                $query_value = $wpdb->get_col($query_check);
                $query_wp = '';
                if (empty($query_value)) {
                    $query_wp = "INSERT INTO $table_name 
						(`user_id`, `product_id`, `variation_id`, `quantity`) 
						VALUES 
							('$user', '$product_id', '$variation_id', '$quantity')
						
						";
                }
            } else if ('remove' == $act) {
                $query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user' and `product_id` = '$product_id'";
            }
            if (!empty($query_wp)) {
                $wpdb->query($query_wp);
            }
        } else {
            $table_name = $wpdb->prefix . 'wt_guest_wishlists';

            if ('add' == $act) {
                $query_wp = "INSERT INTO $table_name 
						(`user_id`, `session_id`,`product_id`, `variation_id`, `quantity`) 
						VALUES 
							('$user', '$session_id', '$product_id', '$variation_id', '$quantity')
						
						";
            } else if ('remove' == $act) {
                $query_wp = "DELETE FROM `$table_name` WHERE `session_id` = '$session_id' and `product_id` = '$product_id'";
            }
            $wpdb->query($query_wp);
        }
        $button_style_settings = get_option('wt_wishlist_button_style_settings');
        $wt_wishlist_button_style_settings_options = get_option('wt_wishlist_button_style_settings');
        $result['icon_position'] = ('above_image' == $wt_wishlist_button_style_settings_options['wt_button_position']) ? 1 : 0;
        $browse_wishlist = isset($wt_wishlist_button_style_settings_options['wt_enable_browse_wishlist']) ? $wt_wishlist_button_style_settings_options['wt_enable_browse_wishlist'] : '';
        $wt_add_to_wishlist_text = $button_style_settings['wt_add_to_wishlist_text'] ? $button_style_settings['wt_add_to_wishlist_text'] : 'Add to wishlist';
        $wt_after_adding_product_text = $button_style_settings['wt_after_adding_product_text'] ? $button_style_settings['wt_after_adding_product_text'] : 'Added to wishlist';
        $result['wt_add_to_wishlist_text'] = $wt_add_to_wishlist_text;
        $result['wt_after_adding_product_text'] = $wt_after_adding_product_text;
        $result['browse_wishlist'] = $browse_wishlist;
        wp_send_json($result);
    }

    public function myaccount_clear_list() {
        $user         = get_current_user_id();
        $session_id   = WC()->session->get('sessionid');
        global $wpdb;

        if ($user != 0) {
            $table_name = $wpdb->prefix . 'wt_wishlists';
                $query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user'";
        } else {
            $table_name = $wpdb->prefix . 'wt_guest_wishlists';
                $query_wp = "DELETE FROM `$table_name` WHERE `session_id` = '$session_id'";
        }

        $wpdb->query($query_wp);
    }

    //add all product in wishlist to cart 
    public function myaccount_bulk_add_to_cart_action() {

        check_ajax_referer('bulk_add_to_cart', 'wt_nonce');

        $product_ids = isset($_POST['product_id']) ? array_filter(array_map('intval', $_POST['product_id'])) : array();

        foreach ($product_ids as $product_id) {

            $this->add_product_to_cart($product_id);
        }
        $result = array(
            'redirect' => wc_get_cart_url()
        );

        wp_send_json($result);
    }
    //add each product to cart from wishlist
    public function single_add_to_cart_action() {

        check_ajax_referer('single_add_to_cart', 'wt_nonce');

        $product_id   = absint($_POST['product_id']);
        $product = wc_get_product($product_id);

        if ($product->is_type('variable')) {
            $url = get_permalink($product_id);
            $result = array(
                'redirect' => $url,
            );
            unset($product);
        } else {

            $this->add_product_to_cart($product_id);

            $result = array(
                'redirect' => wc_get_cart_url()
            );
        }

        wp_send_json($result);
    }

    public function add_product_to_cart($product_id) {
        WC()->cart->add_to_cart($product_id, '1');

        $wt_wishlist_table_settings_options = get_option('wt_wishlist_table_settings');
        $remove_item = isset($wt_wishlist_table_settings_options['remove_item_from_wishlist']) ? $wt_wishlist_table_settings_options['remove_item_from_wishlist'] : '';

        if ($remove_item) {

            $user         = get_current_user_id();
            $session_id   = WC()->session->get('sessionid');

            global $wpdb;
            if ($user != 0) {
                $table_name = $wpdb->prefix . 'wt_wishlists';

                $product_data = wc_get_product($product_id);
                if ($product_data->is_type('variation')) {

                    $query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user' and `variation_id` = '$product_id'";
                } else {

                    $query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user' and `product_id` = '$product_id'";
                }
                $wpdb->query($query_wp);
            } else {
                $table_name = $wpdb->prefix . 'wt_guest_wishlists';

                if ($product_data->is_type('variation')) {

                    $query_wp = "DELETE FROM `$table_name` WHERE `session_id` = '$session_id' and `variation_id` = '$product_id'";
                } else {

                    $query_wp = "DELETE FROM `$table_name` WHERE `session_id` = '$session_id' and `product_id` = '$product_id'";
                }
                $wpdb->query($query_wp);
            }
        }
    }

    public function add_plugin_admin_menu() {

        add_menu_page('WooCommerce Wishlist', 'WooCommerce Wishlist', 'manage_options', $this->plugin_name, array(
            $this,
            'view_wishlist_settings_page'
        ), WEBTOFFEE_WISHLIST_BASEURL . 'public/images/wt-heart-icon.png', '56');
    }

    //function to register wishlist settings
    function wt_register_settings() {

        register_setting('wt_wishlist_general_settings_group', 'wt_wishlist_general_settings');
        register_setting('wt_wishlist_table_settings_group', 'wt_wishlist_table_settings');
        register_setting('wt_wishlist_button_style_settings_group', 'wt_wishlist_button_style_settings');

        //define default values for general settings options
        global $wt_wishlist_general_settings_options;
        $wishlist_general_options = apply_filters('wt_wishlist_default_general_options', array(

            'wt_enabled_pages' => array('shop', 'product'),
            'wt_enable_product_categories' => array('all'),
            'wt_add_to_myaccount' => 1,
        ));
        $wishlist_general_saved_option = get_option('wt_wishlist_general_settings');
        if (empty($wishlist_general_saved_option)) {
            foreach ($wishlist_general_options as $key => $option) {
                $wishlist_general_saved_option[$key] = $option;
            }
        }
        update_option("wt_wishlist_general_settings", $wishlist_general_saved_option);


        //define default values for table settings options
        global $wt_wishlist_table_settings_options;

        $wishlist_page_id = get_option('wt_webtoffee-wishlist_page_id');

        $wishlist_table_settings_options = apply_filters('wt_wishlist_default_table_settings_options', array(

            'wt_wishlist_page'                       => $wishlist_page_id,
            'wt_enable_product_variation_column'     => 1,
            'wt_enable_unit_price_column'            => 1,
            'wt_enable_wishlisted_date_column'       => 0,
            'wt_enable_stock_status_column'          => 1,
            'wt_enable_add_to_cart_option_column'    => 1,
            'add_all_to_cart'                        => 1,
            'redirect_to_cart'                       => 1,

        ));
        $wishlist_table_settings_saved_option = get_option('wt_wishlist_table_settings');
        if (empty($wishlist_table_settings_saved_option)) {
            foreach ($wishlist_table_settings_options as $key => $option) {
                $wishlist_table_settings_saved_option[$key] = $option;
            }
        }
        update_option("wt_wishlist_table_settings", $wishlist_table_settings_saved_option);


        global $wt_wishlist_button_style_settings_options;

        $wishlist_button_style_settings_options = apply_filters('wt_wishlist_default_button_style_settings_options', array(

            'wt_button_type'                       => 'text',
            'wt_button_position'                   => 'after_add_to_add',
        ));
        $wishlist_button_style_settings_saved_option = get_option('wt_wishlist_button_style_settings');
        if (empty($wishlist_button_style_settings_saved_option)) {
            foreach ($wishlist_button_style_settings_options as $key => $option) {
                $wishlist_button_style_settings_saved_option[$key] = $option;
            }
        }
        update_option("wt_wishlist_button_style_settings", $wishlist_button_style_settings_saved_option);
    }

    public function view_wishlist_settings_page() {

        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        } else {
            $tab = 'general_settings';
        }
        include('views/class-wishlist-settings.php');
    }

    public function admin_general_settings_page() {
        global $wt_wishlist_general_settings_options;
        include('views/wishlist-general-settings-page.php');
    }

    public function admin_wishlist_table_settings_page() {
        global $wt_wishlist_table_settings_options;

        include('views/wishlist-table-settings-page.php');

        if (isset($wt_wishlist_table_settings_options['wt_wishlist_page']) && ($wt_wishlist_table_settings_options['wt_wishlist_page'] != get_option('wt_webtoffee-wishlist_page_id'))) {

            //add wishlist table shortcode to the page admin selects from settings options
            $my_id = $wt_wishlist_table_settings_options['wt_wishlist_page'];
            $post_id = get_post($my_id);
            if (!empty($post_id)) {
                $content = $post_id->post_content;
                $title = $post_id->post_title;
                if (!preg_match("/wt_mywishlist/", $content)) {
                    $content = $content . '<!-- wp:shortcode -->[wt_mywishlist]<!-- /wp:shortcode -->';
                    $post_details = array(
                        'ID'            => $my_id,
                        'post_content'  => $content,
                        'post_title'    => $title,
                        'post_author'   => 1,
                        'post_type' => 'page',
                        'post_status' => 'publish'
                    );
                    wp_insert_post($post_details);
                }
            }
        }
    }

    public function admin_wishlist_button_style_settings_page() {
        global $wt_wishlist_button_style_settings_options;

        include('views/wishlist-button-style-settings-page.php');
    }

    public function admin_wishlist_popular_products_page() {

        include('views/wishlist-popular-products-page.php');
    }


    // public function wishlist_webtoffee_settings() {
    // 	//todo change
    // 	//include( 'class-wishlist_webtoffee_settings.php' );
    // 	wp_redirect( 'admin.php?page=wc-settings&tab=settings_tab_wt_wishlist' );
    // 	exit;

    // }

    // public function display_plugin_setup_page() {
    // 	global $wpdb;
    // 	$table_name   = $wpdb->prefix . 'wt_wishlists';
    // 	$wt_wishlists = $wpdb->get_results( "SELECT * FROM $table_name" );
    // 	include( 'wishlist-webtoffee-admin-table.php' );
    // }

    public function add_wt_screen_id($screen_ids) {
        $screen_ids[] = 'toplevel_page_wishlist-webtoffee';
        return $screen_ids;
    }
}
