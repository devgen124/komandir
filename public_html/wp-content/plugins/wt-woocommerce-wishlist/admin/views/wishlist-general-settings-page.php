<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

//settings page to change wishlist general settings for admin
ob_start(); 
        
?>
<div class="wt_mainform">
    <form action="options.php"  method="post">

        <?php settings_fields('wt_wishlist_general_settings_group');  ?>
        <h2 class="settings_title"><?php _e('General settings', 'wt-woocommerce-wishlist'); ?></h2>

        <table class="wt-form-table">
            <tbody>
               
                <tr valign="top">
                    <th scope="row"><?php _e('Show \'Add to wishlist\' only for logged in users','wt-woocommerce-wishlist'); ?>
                   </th>
                    <td> 
                        <label class="switch" >
                            <input name="wt_wishlist_general_settings[wt_enable_for_loggedin_users]" type="checkbox" class="switch-input" id="wt_wishlist_general_settings[wt_enable_for_loggedin_users]"  value="1"   <?php if(isset($wt_wishlist_general_settings_options['wt_enable_for_loggedin_users'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                        <span class="wt_form_help"><?php _e('Enabling will not allow guest users to move products to wishlist.','wt-woocommerce-wishlist'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e("Show 'Add to wishlist' in pages",'wt-woocommerce-wishlist'); ?>
                    <?php echo wc_help_tip( __('If not selected, ‘Add to wishlist’ will only appear on the below chosen product category pages.','wt-woocommerce-wishlist') ); ?>
                    </th>
                    <?php 
                        $shop_selected    = '';
                        $product_selected = '';
                        if(isset($wt_wishlist_general_settings_options['wt_enabled_pages'])){
                            $shop_selected    = in_array("shop",$wt_wishlist_general_settings_options['wt_enabled_pages']) ? '" selected="selected"' : "";
                            $product_selected = in_array("product",$wt_wishlist_general_settings_options['wt_enabled_pages']) ? '" selected="selected"' : "";
                        }
                    ?>
                    <td> 
                        <select name="wt_wishlist_general_settings[wt_enabled_pages][]" class="wc-enhanced-select selection_colour" id="wt_wishlist_general_settings[wt_enabled_pages]" multiple="multiple">
                        <?php
                        echo '<option value="shop' .$shop_selected .'">'. __('Shop', 'wt-woocommerce-wishlist') . '</option>';
                        echo '<option value="product' . $product_selected . '">' . __('Product', 'wt-woocommerce-wishlist') . '</option>';
                        ?>
                        </select><br>
                        <span class="wt_form_help"><?php _e("Show 'Add to wishlist' in chosen pages.",'wt-woocommerce-wishlist'); ?></span>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row"><?php _e("Show 'Add to wishlist' for product categories",'wt-woocommerce-wishlist'); ?>
                    </th>
                    <?php 
                        if(isset($wt_wishlist_general_settings_options['wt_enable_product_categories'])){
                            $selected_categories = $wt_wishlist_general_settings_options['wt_enable_product_categories'];
                        }
                        $args=array('orderby' => 'name', 'order' => 'ASC', 'taxonomy' => 'product_cat', 'hide_empty' => false);
                        $categories_all = get_categories($args);

                        
                    ?>
                    <td> 
                        <select name="wt_wishlist_general_settings[wt_enable_product_categories][]" class="wc-enhanced-select" id="wt_wishlist_general_settings[wt_enable_product_categories]" multiple="multiple" >
                        <?php

                            $all_category_selected    = '';
                            $categories_selected = '';
                            if(isset($wt_wishlist_general_settings_options['wt_enable_product_categories'])){
                                $all_category_selected = in_array("all",$wt_wishlist_general_settings_options['wt_enable_product_categories']) ? '" selected="selected"' : "";
                                
                            }

                            $all = 'all';
                            echo '<option value="' . $all . $all_category_selected .'">'. __('All categories','wt-woocommerce-wishlist') . '</option>';

                        foreach($categories_all as $category_info){

                            if(isset($wt_wishlist_general_settings_options['wt_enable_product_categories'])){
                                $categories_selected = in_array($category_info->slug ,$wt_wishlist_general_settings_options['wt_enable_product_categories']) ? '" selected="selected"' : "";
                            }

                            echo '<option value="' . $category_info->slug . $categories_selected. '">'. $category_info->cat_name . '</option>';
                        }
                        ?>
                        </select><br>
                        <span class="wt_form_help"><?php _e("Show 'Add to wishlist' in chosen product categories.",'wt-woocommerce-wishlist'); ?></span>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Show \'My Wishlist\' in My account','wt-woocommerce-wishlist'); ?>
                    </th>
                    <td> 
                        <label class="switch" >
                            <input name="wt_wishlist_general_settings[wt_add_to_myaccount]" type="checkbox" class="switch-input" id="wt_wishlist_general_settings[wt_add_to_myaccount]" value="1" <?php if(isset($wt_wishlist_general_settings_options['wt_add_to_myaccount'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                    </td>
                </tr>
                
            </tbody>
        </table>
</div>
        <input type="submit" name="submit" id="submit" class="button wt_wishlist_save_button" value="<?php _e('Save changes', 'wt-woocommerce-wishlist'); ?>" />
    </form>

<?php
echo ob_get_clean();