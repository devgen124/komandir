
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

        <?php settings_fields('wt_wishlist_button_style_settings_group');  ?>
        <h2 class="settings_title"><?php _e("'Add to wishlist' style settings", 'wt-woocommerce-wishlist'); ?></h2>

        <table class="wt-form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e('Show \'Add to wishlist\' as','wt-woocommerce-wishlist'); ?>
                    </th>
                    <td  style="padding-bottom:5px;"> 
                        <select name="wt_wishlist_button_style_settings[wt_button_type]" id="wt_wishlist_button_style_settings[wt_button_type]"  class="wc-enhanced-select wt_button_type" >
                            <option value="text" <?php selected("text", $wt_wishlist_button_style_settings_options['wt_button_type']); ?>> <?php _e('Text','wt-woocommerce-wishlist'); ?> </option> 
                            <option value="icon" <?php selected("icon", $wt_wishlist_button_style_settings_options['wt_button_type']); ?>> <?php _e('Icon','wt-woocommerce-wishlist'); ?>  </option> 
                            <option value="normal_button" <?php selected("normal_button", $wt_wishlist_button_style_settings_options['wt_button_type']); ?>> <?php _e('Normal button(theme)','wt-woocommerce-wishlist'); ?> </option> 
                        </select><br>
                    </td>
                </tr>
                <tr valign="top" class="hide_text_element">
                    <th scope="row"></th>
                    <td  class="checkbox_options"> 
                        <div class="wt_sub_option_div" style="padding-bottom:7px;">
                           <div class="wt_sub_option_text_div"> <label for="wt_wishlist_button_style_settings[wt_add_to_wishlist_text]" ><?php _e('Text (to add product to wishlist)','wt-woocommerce-wishlist'); ?></label></div>
                            <div> <input name="wt_wishlist_button_style_settings[wt_add_to_wishlist_text]" type="text" class="wt_add_to_wishlist_text" id="wt_wishlist_button_style_settings[wt_add_to_wishlist_text]" placeholder="<?php _e('Add to wishlist','wt-woocommerce-wishlist'); ?>" value="<?php echo isset($wt_wishlist_button_style_settings_options['wt_add_to_wishlist_text']) ? $wt_wishlist_button_style_settings_options['wt_add_to_wishlist_text'] : '';  ?>" class="regular-text"></div>

                        </div>
                        <div class="wt_sub_option_div" style="padding-top:8px;">
                            <div class="wt_sub_option_text_div"><label for="wt_wishlist_button_style_settings[wt_after_adding_product_text]" ><?php _e('Text (after adding product)','wt-woocommerce-wishlist'); ?></label></div>
                            <div><input name="wt_wishlist_button_style_settings[wt_after_adding_product_text]" type="text" class="wt_after_adding_product_text" id="wt_wishlist_button_style_settings[wt_after_adding_product_text]" placeholder="<?php _e('Added to wishlist','wt-woocommerce-wishlist'); ?>" value="<?php echo isset($wt_wishlist_button_style_settings_options['wt_after_adding_product_text']) ? $wt_wishlist_button_style_settings_options['wt_after_adding_product_text'] : '';  ?>" class="regular-text"></div>
                        </div>
                        <span class="wt_form_help"><?php _e('Choose ‘Add to wishlist’ style (text, icon, or button).','wt-woocommerce-wishlist'); ?></span>  
                    </td>

                </tr>

                <tr valign="top" class="hide_icon_element">
                    <th scope="row"></th>
                    <td  class="checkbox_options"> 
                        <span class="wt_form_help" style="margin-top:0px;"><?php _e('Choose ‘Add to wishlist’ style (text, icon, or button).','wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>

                <tr valign="top" class="hide_button_element">
                    <th scope="row"></th>
                    <td  class="checkbox_options"> 
                        <div class="wt_sub_option_div" style="padding-bottom:7px;">
                           <div class="wt_sub_option_text_div"> <label for="wt_wishlist_button_style_settings[wt_add_to_wishlist_button]" ><?php _e('Text (to add to wishlist)','wt-woocommerce-wishlist'); ?></label></div>
                            <div> <input name="wt_wishlist_button_style_settings[wt_add_to_wishlist_button]" type="text" class="wt_add_to_wishlist_button" id="wt_wishlist_button_style_settings[wt_add_to_wishlist_button]" placeholder="<?php _e('Add to Wishlist','wt-woocommerce-wishlist'); ?>" value="<?php echo isset($wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button']) ? $wt_wishlist_button_style_settings_options['wt_add_to_wishlist_button'] : '';  ?>" class="regular-text"></div>

                        </div>
                        <div class="wt_sub_option_div" style="padding-top:8px;">
                            <div class="wt_sub_option_text_div"><label for="wt_wishlist_button_style_settings[wt_after_adding_product_button]" ><?php _e('Text (after adding product)','wt-woocommerce-wishlist'); ?></label></div>
                            <div><input name="wt_wishlist_button_style_settings[wt_after_adding_product_button]" type="text" class="wt_after_adding_product_button" id="wt_wishlist_button_style_settings[wt_after_adding_product_button]" placeholder="<?php _e('Added to wishlist','wt-woocommerce-wishlist'); ?>" value="<?php echo isset($wt_wishlist_button_style_settings_options['wt_after_adding_product_button']) ? $wt_wishlist_button_style_settings_options['wt_after_adding_product_button'] : '';  ?>" class="regular-text"></div>
                        </div>
                        <span class="wt_form_help"><?php _e('Choose ‘Add to wishlist’ style (text, icon, or button).','wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php _e('\'Add to wishlist\' position in loop','wt-woocommerce-wishlist'); ?>
                    <?php echo wc_help_tip( __('Loop refers to WooCommerce shop and product category page.','wt-woocommerce-wishlist') ); ?>
                    <span class="wt_form_help"><?php _e("",'wt-woocommerce-wishlist'); ?></span>  
                    </th>
                    <td> 
                        <select name="wt_wishlist_button_style_settings[wt_button_position]" id="wt_wishlist_button_style_settings[wt_button_position]"  class="wc-enhanced-select">
                            <option value="after_add_to_add" <?php selected("after_add_to_add", $wt_wishlist_button_style_settings_options['wt_button_position']); ?>> <?php _e("After 'Add to cart' button",'wt-woocommerce-wishlist'); ?> </option> 
                            <option value="above_image" <?php selected("above_image", $wt_wishlist_button_style_settings_options['wt_button_position']); ?>> <?php _e('On top right corner of the product thumbnail','wt-woocommerce-wishlist'); ?> </option> 
                        </select><br>
                        <span class="wt_form_help"><?php _e("Choose ‘Add to wishlist’ position in shop and product category page",'wt-woocommerce-wishlist'); ?></span>  
                    </td>
                </tr>

                <tr valign="top" >
                    <th scope="row" style="padding-bottom:7px;"><?php _e('Enable \'View wishlist\' link','wt-woocommerce-wishlist'); ?></th>
                    <td class="checkbox_options"> 
                        <label class="switch" >
                            <input name="wt_wishlist_button_style_settings[wt_enable_browse_wishlist]" type="checkbox" class="switch-input wt_enable_browser_wishlist_option" id="wt_wishlist_button_style_settings[wt_enable_browse_wishlist]" value="1" <?php if(isset($wt_wishlist_button_style_settings_options['wt_enable_browse_wishlist'])==1){ echo 'checked' ;}  ?>>
                            <span class="switch-label" data-on="Yes" data-off="No"></span> <span class="switch-handle"></span>
                        </label>
                        <span class="wt_form_help" style="padding: 0px 0px 25px;"><?php _e('If product is already wishlisted, show `View wishlist` link','wt-woocommerce-wishlist'); ?></span>
                    </td>
                </tr>
                
            </tbody>
        </table>
</div>
       <input type="submit" name="submit" id="submit" class="button wt_wishlist_save_button" value="<?php _e('Save changes', 'wt-woocommerce-wishlist'); ?>" />
    </form>

<?php
echo ob_get_clean();