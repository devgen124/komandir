
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

        <?php settings_fields('wt_wishlist_table_settings_group');  ?>
        <h2 class="settings_title"><?php _e('Wishlist page settings', 'wt-woocommerce-wishlist'); ?></h2>

        <table class="wt-form-table">
            <tbody>

                <tr valign="top">
                    <th   scope="row"><?php _e('Wishlist page','wt-woocommerce-wishlist'); ?>
                    </th>
                    <td > 
                        <select name="wt_wishlist_table_settings[wt_wishlist_page]" class="wc-enhanced-select" id="wt_wishlist_table_settings[wt_wishlist_page]">
                            <?php

                            $args = array('sort_order' => 'asc','sort_column' => 'post_title','hierarchical' => 1,'exclude' => '','include' => '','meta_key' => '','meta_value' => '','authors' => '','child_of' => 0, 'parent' => -1,'exclude_tree' => '','number' => '','offset' => 0,'post_type' => 'page','post_status' => 'publish'); 
                            $pages = get_pages($args);                        

                            $selected_page = isset($wt_wishlist_table_settings_options['wt_wishlist_page']) ? $wt_wishlist_table_settings_options['wt_wishlist_page'] : '';
                            foreach ($pages as $page) {
                                echo '<option value="' . $page->ID .(($page->ID == $selected_page) ? '" selected="selected"' : '').'">' . $page->post_title . '</option>';
                            }
                            ?>
                        </select><br>
                        <span class="wt_form_help"><?php _e("Defaulted to 'My Wishlist' page. Select an alternative page from the dropdown to change it.",'wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row" style="padding-bottom:7px;"><?php _e('Enable wishlist table contents','wt-woocommerce-wishlist'); ?></th>
                    <td style="padding-bottom:7px;"> 
                        <input name="wt_wishlist_table_settings[wt_enable_product_variation_column]" type="checkbox" class="" id="wt_wishlist_table_settings[wt_enable_product_variation_column]" value="1" <?php if(isset($wt_wishlist_table_settings_options['wt_enable_product_variation_column'])==1){ echo 'checked' ;}  ?>>
                        <label for="wt_wishlist_table_settings[wt_enable_product_variation_column]"><?php _e('Product variations(example: size, color)','wt-woocommerce-wishlist'); ?></label>
                    </td>
                </tr>
                <tr valign="top" >
                    <td scope="row" class="checkbox_options"></td>
                    <td class="checkbox_options"> 
                        <input name="wt_wishlist_table_settings[wt_enable_unit_price_column]" type="checkbox" class="" id="wt_wishlist_table_settings[wt_enable_unit_price_column]" value="1" <?php if(isset($wt_wishlist_table_settings_options['wt_enable_unit_price_column'])==1){ echo 'checked' ;}  ?>>
                        <label for="wt_wishlist_table_settings[wt_enable_unit_price_column]"><?php _e('Unit price','wt-woocommerce-wishlist'); ?></label>
                    </td>
                </tr>
                
                <tr valign="top" >
                    <td scope="row" class="checkbox_options"></td>
                    <td class="checkbox_options"> 
                        <input name="wt_wishlist_table_settings[wt_enable_wishlisted_date_column]" type="checkbox" class="" id="wt_wishlist_table_settings[wt_enable_wishlisted_date_column]" value="1" <?php if(isset($wt_wishlist_table_settings_options['wt_enable_wishlisted_date_column']) && $wt_wishlist_table_settings_options['wt_enable_wishlisted_date_column']==1){ echo 'checked' ;}  ?>>
                        <label for="wt_wishlist_table_settings[wt_enable_wishlisted_date_column]"><?php _e('Wishlisted date','wt-woocommerce-wishlist'); ?></label>
                    </td>
                </tr>
                <tr valign="top" >
                    <td scope="row" class="checkbox_options" ></td>
                    <td class="checkbox_options"> 
                        <input name="wt_wishlist_table_settings[wt_enable_stock_status_column]" type="checkbox" class="" id="wt_wishlist_table_settings[wt_enable_stock_status_column]" value="1" <?php if(isset($wt_wishlist_table_settings_options['wt_enable_stock_status_column'])==1){ echo 'checked' ;}  ?>>
                        <label for="wt_wishlist_table_settings[wt_enable_stock_status_column]"><?php _e('Stock status','wt-woocommerce-wishlist'); ?></label>
                    </td>
                </tr>
                <tr valign="top" >
                    <td scope="row" class="checkbox_options"></td>
                    <td class="checkbox_options"> 
                        <input name="wt_wishlist_table_settings[wt_enable_add_to_cart_option_column]" type="checkbox" class="wt_enable_add_to_cart_option" id="wt_wishlist_table_settings[wt_enable_add_to_cart_option_column]" value="1" <?php  if(isset($wt_wishlist_table_settings_options['wt_enable_add_to_cart_option_column'])==1){ echo 'checked' ;}  ?>>
                        <label for="wt_wishlist_table_settings[wt_enable_add_to_cart_option_column]"><?php _e('Add to cart option','wt-woocommerce-wishlist'); ?></label>
                    </td>
                </tr>
                <tr valign="top" class="wt_enable_add_to_cart_option_element">
                    <th scope="row" class="checkbox_options"></th>
                    <td class="checkbox_options" > 
                        <div class="wt_sub_option_div" style="grid-template-columns: 1fr 6fr;">
                        <div class="wt_sub_option_text_div"><label for="wt_wishlist_table_settings[wt_add_to_cart_text]"><?php _e('Add to cart text','wt-woocommerce-wishlist'); ?></label></div>
                        <div><input name="wt_wishlist_table_settings[wt_add_to_cart_text]" type="text" class="" id="wt_wishlist_table_settings[wt_add_to_cart_text]" placeholder="<?php _e('Add to cart','wt-woocommerce-wishlist'); ?>" value="<?php echo isset($wt_wishlist_table_settings_options['wt_add_to_cart_text']) ? $wt_wishlist_table_settings_options['wt_add_to_cart_text'] : '';  ?>" class="regular-text"></div>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Remove item from wishlist after adding to cart','wt-woocommerce-wishlist'); ?>
                    </th>
                    <td> 
                        <label class="switch" >
                            <input name="wt_wishlist_table_settings[remove_item_from_wishlist]" type="checkbox" class="switch-input" id="wt_wishlist_table_settings[remove_item_from_wishlist]" value="1" <?php if(isset($wt_wishlist_table_settings_options['remove_item_from_wishlist'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e("Enable 'Add all to cart' button",'wt-woocommerce-wishlist'); ?>
                    </th>
                    <td> 
                        <label class="switch" >
                            <input name="wt_wishlist_table_settings[add_all_to_cart]" type="checkbox" class="switch-input" id="wt_wishlist_table_settings[add_all_to_cart]" value="1" <?php if(isset($wt_wishlist_table_settings_options['add_all_to_cart'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                        <span class="wt_form_help"><?php _e('\'Add all to cart\' button will move all wishlist products to cart.','wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('Redirect to cart','wt-woocommerce-wishlist'); ?>
                    </th>
                    <td> 
                        <label class="switch" >
                            <input name="wt_wishlist_table_settings[redirect_to_cart]" type="checkbox" class="switch-input" id="wt_wishlist_table_settings[redirect_to_cart]" value="1" <?php if(isset($wt_wishlist_table_settings_options['redirect_to_cart'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                        <span class="wt_form_help"><?php _e('Redirect users to cart page when they add product to cart from wishlist.','wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>
               

            </tbody>
        </table>
</div>
        <input type="submit" name="submit" id="submit" class="button wt_wishlist_save_button" value="<?php _e('Save changes', 'wt-woocommerce-wishlist'); ?>" />
    </form>

<?php
echo ob_get_clean();