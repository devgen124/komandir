
        <tr>
            <th><?php _e('Show filters above products', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <?php $options = BeRocket_AAPF::get_aapf_option();
                $elements_above_products = br_get_value_from_array($options, 'elements_above_products');
                if( ! is_array($elements_above_products) ) {
                    $elements_above_products = array();
                }
                global $pagenow;
                $post_id = 0;
                if( ! in_array( $pagenow, array( 'post-new.php' ) ) ) {
                    $post_id = $post->ID;
                }
                 ?>
                <input type="checkbox" class="berocket_show_above_option" name="br_filter_group_show_above" value="1"<?php if(in_array($post_id, $elements_above_products)) echo ' checked'; ?>>
            </td>
        </tr>
        <tr>
            <th><?php _e('Display filters in line', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <input class="berocket_display_inline_option" type="checkbox" name="<?php echo $post_name; ?>[display_inline]" value="1"<?php if(! empty($filters['display_inline']) ) echo ' checked'; ?>>
            </td>
        </tr>
        <tr class="berocket_display_inline_count">
            <th><?php _e('Display filters in line max count', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <select name="<?php echo $post_name; ?>[display_inline_count]">
                    <option value=""><?php _e('Default', 'BeRocket_AJAX_domain'); ?></option>
                    <option value="1"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 1 ) echo ' selected'; ?>>1</option>
                    <option value="2"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 2 ) echo ' selected'; ?>>2</option>
                    <option value="3"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 3 ) echo ' selected'; ?>>3</option>
                    <option value="4"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 4 ) echo ' selected'; ?>>4</option>
                    <option value="5"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 5 ) echo ' selected'; ?>>5</option>
                    <option value="6"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 6 ) echo ' selected'; ?>>6</option>
                    <option value="7"<?php if( br_get_value_from_array($filters, 'display_inline_count') == 7 ) echo ' selected'; ?>>7</option>
                </select>
            </td>
        </tr>
        <tr class="berocket_min_filter_width_inline">
            <th><?php _e('Min Width for Filter', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <input type="number" min="25" name="<?php echo $post_name; ?>[min_filter_width_inline]" value="<?php echo br_get_value_from_array($filters, 'min_filter_width_inline', '200'); ?>">
            </td>
        </tr>
        <tr>
            <th><?php _e('Show title only', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <input type="checkbox" class="berocket_hidden_clickable_option" name="<?php echo $post_name; ?>[hidden_clickable]" value="1"<?php if(! empty($filters['hidden_clickable']) ) echo ' checked'; ?>>
                <span><?php _e('Only title will be visible. Filter will be displayed after click on title and hide after click everywhere else', 'BeRocket_AJAX_domain'); ?></span>
            </td>
        </tr>
        <tr class="berocket_hidden_clickable_option_data">
            <th><?php _e('Display filters on mouse over', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <input type="checkbox" name="<?php echo $post_name; ?>[hidden_clickable_hover]" value="1"<?php if(! empty($filters['hidden_clickable_hover']) ) echo ' checked'; ?>>
                <span><?php _e('Display on mouse over and hide on mouse leave', 'BeRocket_AJAX_domain'); ?></span>
            </td>
        </tr>
        <tr class="berocket_group_is_hide_option_data">
            <th><?php _e('Collapsed on page load', 'BeRocket_AJAX_domain'); ?></th>
            <td>
                <input type="checkbox" class="berocket_group_is_hide_option" name="<?php echo $post_name; ?>[group_is_hide]" value="1"<?php if(! empty($filters['group_is_hide']) ) echo ' checked'; ?>>
                <span><?php _e('Collapse group on page load and show icon instead. When icon is clicked filters will be shown', 'BeRocket_AJAX_domain'); ?></span>
            </td>
        </tr>
        <tr class="berocket_group_is_hide_theme_option_data">
            <th class="row"><?php _e('Collapse Button style', 'BeRocket_AJAX_domain') ?></th>
            <td>
                <div class="berocket_group_is_hide_theme_option_slider">
                    <div>
                        <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_theme]" style="display:none!important;" id="group_is_hide_theme_" value="" <?php echo ( empty( $filters['group_is_hide_theme'] ) ? ' checked' : '' ) ?> />
                        <label for="group_is_hide_theme_"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file)?>images/themes/sidebar-button/default.png" /></label>
                    </div>
                    <?php for ( $theme_key = 1; $theme_key <= 10; $theme_key++ ) { ?>
                    <div>
                        <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_theme]" style="display:none!important;" id="group_is_hide_theme_<?php echo $theme_key?>" value="<?php echo $theme_key?>" <?php echo  ( ( ! empty( $filters['group_is_hide_theme'] ) and $filters['group_is_hide_theme'] == $theme_key ) ? ' checked' : '' ) ?> />
                        <label for="group_is_hide_theme_<?php echo $theme_key?>"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/' . $theme_key ?>.png" /></label>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr class="berocket_group_is_hide_theme_option_data">
            <th class="row"><?php _e('Collapse Button Icon style', 'BeRocket_AJAX_domain') ?></th>
            <td>
                <div class="berocket_group_is_hide_theme_option_slider icon_size">
                    <div>
                        <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_icon_theme]" style="display:none!important;" id="group_is_hide_icon_theme_" value="" <?php echo ( empty( $filters['group_is_hide_icon_theme'] ) ? ' checked' : '' ) ?> />
                        <label for="group_is_hide_icon_theme_"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file)?>images/themes/sidebar-button-icon/default.png" /></label>
                    </div>
                    <?php for ( $theme_key = 1; $theme_key <= 6; $theme_key++ ) { ?>
                        <div>
                            <input type="radio" name="<?php echo $post_name; ?>[group_is_hide_icon_theme]" style="display:none!important;" id="group_is_hide_icon_theme_<?php echo $theme_key?>" value="<?php echo $theme_key?>" <?php echo ( ( ! empty( $filters['group_is_hide_icon_theme'] ) and $filters['group_is_hide_icon_theme'] == $theme_key ) ? ' checked' : '' ) ?> />
                            <label for="group_is_hide_icon_theme_<?php echo $theme_key?>"><img src="<?php echo plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/' . $theme_key ?>.png" /></label>
                        </div>
                    <?php } ?>
                </div>
            </td>
        </tr>

<script>
    function berocket_hidden_clickable_option() {
        if( jQuery('.berocket_hidden_clickable_option').prop('checked') ) {
            jQuery('.berocket_hidden_clickable_option_data').show();
            jQuery('.berocket_filter_added_list').addClass('berocket_hidden_clickable_enabled');
        } else {
            jQuery('.berocket_hidden_clickable_option_data').hide();
            jQuery('.berocket_filter_added_list').removeClass('berocket_hidden_clickable_enabled');
        }
    }
    jQuery(document).on('change', '.berocket_hidden_clickable_option', berocket_hidden_clickable_option);
    berocket_hidden_clickable_option();

    function berocket_display_inline_count() {
        if( jQuery('.berocket_display_inline_option').prop('checked') ) {
            jQuery('.berocket_display_inline_count').show();
        } else {
            jQuery('.berocket_display_inline_count').hide();
        }
        if( jQuery('.berocket_display_inline_option').prop('checked') && jQuery('.berocket_display_inline_count select').val() ) {
            jQuery('.berocket_min_filter_width_inline').show();
        } else {
            jQuery('.berocket_min_filter_width_inline').hide();
        }
    }
    jQuery(document).on('change', '.berocket_display_inline_option, .berocket_hidden_clickable_option, .berocket_display_inline_count select', berocket_display_inline_count);
    berocket_display_inline_count();

    function berocket_group_is_hide_option() {
        if( jQuery('.berocket_group_is_hide_option').prop('checked') ) {
            jQuery('.berocket_group_is_hide_theme_option_data').show();
        } else {
            jQuery('.berocket_group_is_hide_theme_option_data').hide();
            jQuery('.berocket_group_is_hide_theme_option').removeAttr('checked');
        }
    }
    jQuery(document).on('change', '.berocket_group_is_hide_option', berocket_group_is_hide_option);
    berocket_group_is_hide_option();

    jQuery(document).ready(function() {
        berocket_hidden_clickable_option();
        berocket_display_inline_count();
    });
</script>
