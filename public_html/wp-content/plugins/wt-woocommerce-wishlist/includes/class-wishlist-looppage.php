<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class WT_Wishlist_Looppage {


    public function __construct() {
        
        global $wt_wishlist_general_settings_options, $wt_wishlist_button_style_settings_options;


        if(isset($wt_wishlist_button_style_settings_options['wt_button_position']) ){

            if( ('after_add_to_add' == $wt_wishlist_button_style_settings_options['wt_button_position'])){

                add_action( 'woocommerce_after_shop_loop_item', array( $this, 'render_webtoffee_wishlist_button' ), 15 );
            }
            else if('above_image' == $wt_wishlist_button_style_settings_options['wt_button_position']){

                add_action( 'woocommerce_before_shop_loop_item', array( $this, 'render_webtoffee_wishlist_button' ), 5 );
            }
        }
    }
    
    public function render_webtoffee_wishlist_button() {

        global $product, $wt_wishlist_button_style_settings_options, $wt_wishlist_table_settings_options, $wt_wishlist_general_settings_options;

        if(is_product_category()){

            if(!empty($wt_wishlist_general_settings_options['wt_enable_product_categories'])){
                $array = array();
                $array = $wt_wishlist_general_settings_options['wt_enable_product_categories'];
                
                if(! in_array('all', $array)){
                    if(! is_product_category($array)){
                        return;
                    }
                }
            }else{
                return;
            }
        }
        if(is_shop()){
            if(!empty($wt_wishlist_general_settings_options['wt_enabled_pages'])){
                if( ! in_array('shop',$wt_wishlist_general_settings_options['wt_enabled_pages'])){
                    return;
                }
            }else{
                return;
            }
        }
        
		$wt_enable_for_loggedin_users = isset($wt_wishlist_general_settings_options['wt_enable_for_loggedin_users']) ? $wt_wishlist_general_settings_options['wt_enable_for_loggedin_users'] : '';

        if(! is_user_logged_in() && ($wt_enable_for_loggedin_users == 1)){
            return;
        }
        $cat_array = $wt_wishlist_general_settings_options['wt_enable_product_categories'];
        if(! in_array('all', $cat_array)){
           
            $product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
            $cat_name = array();        
            foreach( $product_cats_ids as $cat_id ) {
                $term = get_term_by( 'id', $cat_id, 'product_cat' );
                $cat_name[]= strtolower($term->slug);
            }
            if(!array_intersect($cat_name,$cat_array)){
                return;
            }
        }

        if ($this->product_already_exists($product->get_id(), get_current_user_id())) {

            $class          = 'webtoffee_wishlist_remove';
            $action         = 'remove';
            $text_title     = empty($wt_wishlist_button_style_settings_options['wt_after_adding_product_text']) ? __( 'Added to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_after_adding_product_text'];
            $text_icon_src  = WEBTOFFEE_WISHLIST_BASEURL .'public/images/favourite.svg';
            $icon_src       = WEBTOFFEE_WISHLIST_BASEURL .'public/images/icon_favourite.png';
            $button_class   = ' button_product_added';
            $button_msg     = empty($wt_wishlist_button_style_settings_options['wt_after_adding_product_button']) ?  __('Added to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_after_adding_product_button'];
            $product_added  = 1;

            $my_id =  isset($wt_wishlist_table_settings_options['wt_wishlist_page']) ? $wt_wishlist_table_settings_options['wt_wishlist_page'] : get_option( 'wt_webtoffee-wishlist_page_id' );
				
            $browse_wishlist = isset($wt_wishlist_button_style_settings_options['wt_enable_browse_wishlist']) ? $wt_wishlist_button_style_settings_options['wt_enable_browse_wishlist'] : '';
            $visibility = "none";
            if($browse_wishlist){
                $visibility = "block";
                
            }
            $element =  "<br><sapn class='browse_wishlist' style=display:".$visibility."> <a href='".get_the_permalink($my_id)."'>".__('View wishlist','wt-woocommerce-wishlist')."</a></span>";

        } else {

            $class          = 'webtoffee_wishlist';
            $action         = 'add';
            $text_title     = empty($wt_wishlist_button_style_settings_options['wt_add_to_wishlist_text']) ? __('Add to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_add_to_wishlist_text'];
            $text_icon_src  = WEBTOFFEE_WISHLIST_BASEURL .'public/images/unfavourite.svg';
            $icon_src       = WEBTOFFEE_WISHLIST_BASEURL .'public/images/icon_unfavourite.png';
            $button_class   = ' button_product_to_add';
            $button_msg     = empty($wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button']) ?  __('Add to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button'];
            $product_added  = 0;
            $my_id =  isset($wt_wishlist_table_settings_options['wt_wishlist_page']) ? $wt_wishlist_table_settings_options['wt_wishlist_page'] : get_option( 'wt_webtoffee-wishlist_page_id' );

            $element = "<br><sapn class='browse_wishlist' style=display:none> <a href='".get_the_permalink($my_id)."'>".__('View wishlist','wt-woocommerce-wishlist')."</a></span>";

        }

        if(isset($wt_wishlist_button_style_settings_options['wt_button_position']) ){

            if( ('after_add_to_add' == $wt_wishlist_button_style_settings_options['wt_button_position'])){

                $icon_class = 'icon_after_add_to_cart';
                $icon_position = 1;
                
            }
            else if('above_image' == $wt_wishlist_button_style_settings_options['wt_button_position']){

                $icon_class = 'icon_above_image';
                $icon_position = 0;
            }
        }

        if(isset($wt_wishlist_button_style_settings_options['wt_button_type'])){
           
            if($wt_wishlist_button_style_settings_options['wt_button_type'] == 'text'){
            
                echo "<div class='".$icon_class."'> <a href='#' > <img class='wishlist_text_icon_image " . $class . " wt-wishlist-button' data-act='add' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "' data-action='" . $action . "' style='margin-bottom: -2px !important;' src='".$text_icon_src."'><span class='" . $class . " wt-wishlist-button' data-act='add' data-action='" . $action . "' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'>".$text_title."</span></a> ".$element." </div>";

            }else if($wt_wishlist_button_style_settings_options['wt_button_type'] == 'icon'){

                if($icon_position == 1){
                    echo "<div class='".$icon_class."'> <a href='#' > <i class='" . $class . " wt-wishlist-button' data-act='add' data-action='" . $action . "' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'><img style='width:auto; display:inline-flex; margin-bottom: -2px !important;'src='".$text_icon_src."'></i> </a> ".$element." </div>";
                }else{
                    echo "<div class='".$icon_class."'> <a href='#' > <i class='" . $class . " wt-wishlist-button' data-act='add' data-action='" . $action . "' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'><img style='width:auto; display:inline-flex; margin-bottom: -2px !important;'src='".$icon_src."'></i> </a> ".$element." </div>";
                }

            }else if($wt_wishlist_button_style_settings_options['wt_button_type'] == 'normal_button'){
                $text_icon_src_for_btn  = WEBTOFFEE_WISHLIST_BASEURL .'public/images/favourite.svg';
                $button_msg_old = empty($wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button']) ?  __('Add to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button'];
                $text_title_for_btn     = empty($wt_wishlist_button_style_settings_options['wt_after_adding_product_button']) ?  __('Added to wishlist','wt-woocommerce-wishlist') : $wt_wishlist_button_style_settings_options['wt_after_adding_product_button'];
                if($product_added == 1){
                    echo "<div class='".$icon_class."'> <a href='#' > <img class='wishlist_text_icon_image " . $class . " wt-wishlist-button' data-act='add' type-action='btn' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "' data-action='" . $action . "' style='margin-bottom: -2px !important;height:16px;' src='".$text_icon_src."'> <span class='" . $class . " wt-wishlist-button' data-act='add' type-action='btn' data-action='" . $action . "' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'>".$button_msg."</span></a> <button  style='margin-top:0px !important;display:none' class='button wt-wishlist-btn-removed" . $class .$button_class. " wt-wishlist-button' data-act='add' data-action='add' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'>".$button_msg_old."</button>".$element." </div>";
                }else{
                    echo "<div class='".$icon_class." '> <a href='#' class='wt-wishlist-btn-added' style='display:none'> <img class='wishlist_text_icon_image " . $class . " wt-wishlist-button' data-act='add' type-action='btn' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "' data-action='remove' style='margin-bottom: -2px !important;height:16px;' src='".$text_icon_src_for_btn."'> <span class='" . $class . " wt-wishlist-button' data-act='add' data-action='remove' type-action='btn' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'>".$text_title_for_btn."</span></a> <button  style='margin-top:0px !important' class='button wt-wishlist-btn-removed" . $class .$button_class. " wt-wishlist-button' data-act='add' data-action='" . $action . "' data-product_id='" . $product->get_id() . "' data-user_id='" . get_current_user_id() . "'>".$button_msg."</button>".$element." </div>";
                }
            }
        }
       
    }

    public function product_already_exists($product_id, $current_user) {
        
        global $wpdb;
        if (is_user_logged_in()) {
            $table_name = $wpdb->prefix . 'wt_wishlists';
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name where `product_id` = '$product_id' and `user_id` = '$current_user'");
        }else{
            $session_id = WC()->session->get('sessionid');
            $table_name = $wpdb->prefix . 'wt_guest_wishlists';
            $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name where `product_id` = '$product_id' and `session_id` = '$session_id'");
        }

        return $rowcount;
    }

}

new WT_Wishlist_Looppage();