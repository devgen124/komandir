<?php
class BeRocket_AAPF_paid extends BeRocket_plugin_variations {
    public static $instance = null;
    public $plugin_name = 'ajax_filters';
    public $version_number = 20;
    public $default_permalink = array (
        'variable' => 'filters',
        'value'    => '/values',
        'split'    => '/',
    );
    public $default_nn_permalink = array (
        'variable' => 'filters',
        'value'    => '[values]',
        'split'    => '|',
    );
    public static function getInstance()
    {
        if (null === static::$instance)
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    function __construct() {
        static::$instance = $this;
        if( $this->init_validation() ) {
            parent::__construct();
            $this->defaults = array(
                'use_links_filters'         => '',
                'use_noindex'               => '',
                'use_nofollow'              => '',
                'nice_urls'                 => '',
                'canonicalization'          => '',
                'ub_product_count'          => '',
                'ub_product_text'           => 'products',
                'ub_product_button_text'    => 'Show',
                'object_cache'              => '',
                'search_variation_image'    => '',
                'search_variation_price'    => '',
                'slider_250_fix'            => '',
            );
            add_filter( 'berocket_filter_filter_type_array', array( $this, 'filter_type_array' ) );
            add_filter( 'berocket_aapf_single_filter_conditions_list', array( $this, 'aapf_conditions' ) );
            add_filter( 'berocket_aapf_group_filters_conditions_list', array( $this, 'aapf_conditions' ) );
            add_filter( 'aapf_localize_widget_script', array($this, 'aapf_localize_widget_script') );
            add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
            add_action( 'admin_head', array($this, 'admin_init'), 11 );
            if ( ! is_admin() ) {
                add_action( 'wp_head', array( $this, 'wp_head_enqueue' ) );
            }

            //AJAX
            add_action( 'wp_ajax_nopriv_berocket_aapf_listener_pc', array( $this, 'listener_product_count' ) );
            add_action( 'wp_ajax_berocket_aapf_listener_pc', array( $this, 'listener_product_count' ) );
            add_filter( 'berocket_aapf_filter_variable_name_nn', array($this, 'permalink_variable_nn_name') );

            //SECTIONS
            add_filter('brfr_ajax_filters_elements_above', array($this, 'section_elements_above'), $this->version_number, 3);

            //CACHE
            add_filter( 'br_get_cache', array($this, 'br_get_cache'), 10, 3 );
            add_filter( 'br_set_cache', array($this, 'br_set_cache'), 10, 5 );
            
            //SEO TITLE META
            add_filter('berocket_aapf_seo_meta_filtered_terms', array($this, 'seo_meta_filtered_terms'));
            add_filter('berocket_aapf_seo_meta_filtered_term_continue', array($this, 'seo_meta_filtered_term_continue'), 5, 2);
            add_filter('berocket_aapf_query_var_title_filter', array($this, 'query_var_title'), 10, 3);
            
            //SLIDER ATTRIBUTES
            add_filter('berocket_query_var_title_before_widget', array($this, 'attribute_price_var_title'), 10, 5);
            add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'attribute_price_var_title'), 10, 5);
            add_filter('berocket_aapf_get_terms_additional', array($this, 'add_slider_numeric_sorting'), 10, 2);
            //NEW SLIDER ATTRIBUTES
            add_filter('berocket_query_var_title_before_widget', array($this, 'new_slider_vars'), 10, 5);
            add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'new_slider_vars'), 10, 5);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'new_attribute_slider'), 1, 3);
            add_filter('BeRocket_AAPF_template_full_content', array($this, 'datepicker_selected'), 10, 4);
            include "paid/search_field.php";
        }
    }

    function init_validation() {
        return ( ( is_plugin_active( 'woocommerce/woocommerce.php' ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) && 
            br_get_woocommerce_version() >= 2.1 );
    }

    function settings_page($data) {
        $data['General']['hide_values']['items']['hide_value_button'] = array(
            "type"      => "checkbox",
            "name"      => array("hide_value", 'button'),
            "value"     => '1',
            'label_for'  => __('Hide the Show/Hide value(s) button in filters', 'BeRocket_AJAX_domain'),
        );
        $data['SEO'] = berocket_insert_to_array(
            $data['SEO'],
            'seo_friendly_urls',
            array(
                'use_links_filters' => array(
                    "label"     => __( 'Use links for checkboxes and radio filter', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "use_links_filters",
                    "value"     => '1',
                    "class"     => "berocket_use_links_filters",
                ),
                'use_noindex' => array(
                    "label"     => __( 'Use noindex for links', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "use_noindex",
                    "tr_class"  => "berocket_use_noindex",
                    "options"   => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Second+ levels', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('All levels', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                ),
                'use_nofollow' => array(
                    "label"     => __( 'Use nofollow for links', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "use_nofollow",
                    "tr_class"  => "berocket_use_nofollow",
                    "options"   => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Second+ levels', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('All levels', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                ),
            )
        );
        $data['SEO'] = berocket_insert_to_array(
            $data['SEO'],
            'slug_urls',
            array(
                'nice_urls' => array(
                    "label"     => __( 'Nice URLs', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "nice_urls",
                    "value"     => '1',
                    'class'     => 'berocket_nice_url',
                    'label_for' => __("Works only with SEO friendly urls. WordPress permalinks must be set to Post name(Custom structure: /%postname%/ )", 'BeRocket_AJAX_domain'),
                ),
                'canonicalization' => array(
                    "label"     => __( 'Base Canonical URL', "BeRocket_AJAX_domain" ),
                    "type"      => "selectbox",
                    "name"      => "canonicalization",
                    "options"   => array(
                        array('value' => '', 'text' => __('With filters', 'BeRocket_AJAX_domain')),
                        array('value' => '1', 'text' => __('Without filters', 'BeRocket_AJAX_domain')),
                        array('value' => '2', 'text' => __('Disable', 'BeRocket_AJAX_domain')),
                    ),
                    "value"     => '',
                    'label_for' => __("Use canonical tag without filters on WooCommerce pages", 'BeRocket_AJAX_domain'),
                ),
            )
        );
        $data['Elements']['elements_position_hook']['label'] = __( 'Elements position', "BeRocket_AJAX_domain" );
        $data['Elements']['ub_product_count'] = array(
            "label"     => __( 'Show the number of products before filtering', "BeRocket_AJAX_domain" ) . '<span id="braapf_ub_product_count_info" class="dashicons dashicons-editor-help"></span>',
            'items' => array(
                'ub_product_count' => array(
                    "type"      => "checkbox",
                    "name"      => "ub_product_count",
                    "value"     => '1',
                    'label_for'  => __("Show products count before filtering, when using update button", 'BeRocket_AJAX_domain') . '<br>',
                ),
                'ub_product_text' => array(
                    "type"      => "text",
                    "name"      => "ub_product_text",
                    "value"     => $this->defaults["ub_product_text"],
                    'label_for'  => __("Text that means products", 'BeRocket_AJAX_domain') . '<br>',
                ),
                'ub_product_button_text' => array(
                    "type"      => "text",
                    "name"      => "ub_product_button_text",
                    "value"     => $this->defaults["ub_product_button_text"],
                    'label_for'  => __("Text for show button", 'BeRocket_AJAX_domain') . '<br>',
                ),
            )
        );
        $data['Elements']['elements_above'] = array(
            "section"   => "elements_above",
            "value"     => "",
        );
        $data['Advanced'] = berocket_insert_to_array(
            $data['Advanced'],
            'page_same_as_filter',
            array(
                'object_cache' => array(
                    "label"     => __( 'Data cache', "BeRocket_AJAX_domain" ),
                    "name"     => "object_cache",   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => 'wordpress', 'text' => __('WordPress Cache', 'BeRocket_AJAX_domain')),
                        array('value' => 'persistent', 'text' => __('Persistent Cache Plugins', 'BeRocket_AJAX_domain')),
                    ),
                    "value"    => '',
                ),
            )
        );
        $data['General'] = berocket_insert_to_array(
            $data['General'],
            'out_of_stock_variable',
            array(
                'display_variation_data' => array(
                    "label"     => __( 'Display variation data', "BeRocket_AJAX_domain" ) . '<span id="braapf_display_variation_data_info" class="dashicons dashicons-editor-help"></span>',
                    "items"     => array(
                        'search_variation_image' => array(
                            "label"     => __( 'Display variation image', "BeRocket_AJAX_domain" ),
                            "type"      => "checkbox",
                            "name"      => "search_variation_image",
                            "value"     => '1',
                            'label_for' => __('Image', 'BeRocket_AJAX_domain'),
                        ),
                        'search_variation_price' => array(
                            "label"     => __( 'Display variation price', "BeRocket_AJAX_domain" ),
                            "type"      => "checkbox",
                            "name"      => "search_variation_price",
                            "value"     => '1',
                            'label_for' => __('Price', 'BeRocket_AJAX_domain'),
                        ),
                    ),
                ),
                'use_filter_for_variation' => array(
                    "label"     => __( 'Remember variation<br>options', "BeRocket_AJAX_domain" ) . '<span id="braapf_use_filter_for_variation_show_info" class="dashicons dashicons-editor-help"></span>',
                    "name"     => "select_filter_variation",   
                    "type"     => "selectbox",
                    "options"  => array(
                        array('value' => '', 'text' => __('Disabled', 'BeRocket_AJAX_domain')),
                        array('value' => 'url', 'text' => __('Replace Product URL', 'BeRocket_AJAX_domain')),
                        array('value' => 'session', 'text' => __('Set in PHP Session', 'BeRocket_AJAX_domain')),
                        array('value' => 'url_session', 'text' => __('Both URL and Session', 'BeRocket_AJAX_domain')),
                    ),
                    "value"    => '',
                ),
            )
        );
        $data['Advanced'] = berocket_insert_to_array(
            $data['Advanced'],
            'search_fix',
            array(
                'slider_250_fix' => array(
                    "tr_class"  => "bapf_incompatibility_fixes bapf_incompatibility_fixes_hide",
                    "label"     => __( 'Slider has many values', "BeRocket_AJAX_domain" ),
                    "type"      => "checkbox",
                    "name"      => "slider_250_fix",
                    "value"     => '1',
                    'label_for' => __('Enable the setting if the slider has more than 250 values. Hierarchical taxonomy may not work correctly with sliders.', 'BeRocket_AJAX_domain'),
                ),
            )
        );
        $data['Design'] = berocket_insert_to_array(
            $data['Design'],
            'color_img_tooltip_design',
            array(
                'product_count_tooltip_design' => array(
                    "label"     => __( 'Products Count Before Update', "BeRocket_AJAX_domain" ),
                    "items" => array(
                        "tippy_theme" => array(
                            "type"      => "selectbox",
                            "name"      => 'tippy_product_count_theme',
                            "options"  => array(
                                array('value' => 'light', 'text' => __('Light', 'BeRocket_AJAX_domain')),
                                array('value' => 'dark', 'text' => __('Dark', 'BeRocket_AJAX_domain')),
                                array('value' => 'translucent', 'text' => __('Translucent', 'BeRocket_AJAX_domain')),
                            ),
                            "value"     => '',
                            'label_be_for' => __('Tooltip Theme', 'BeRocket_AJAX_domain'),
                        ),
                        'tippy_fontsize' => array(
                            "type"         => "number",
                            "name"         => "tippy_product_count_fontsize",
                            "value"        => '',
                            'extra'        => 'placeholder="' . __('From Theme', 'BeRocket_AJAX_domain') . '"',
                            'label_be_for' => __('Tooltip Font Size', 'BeRocket_AJAX_domain'),
                        ),
                    ),
                ),
            )
        );
        //Show the number of products before filtering
        $tooltip_text = '<strong>' . __('Display tooltip with products count for a page, that will be displayed after applying filters.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Works only if filters are displayed with "Update Products button".', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('Option uses Tippy script. It may not be compatible with some other plugins that also use Tippy script.', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_ub_product_count_info', $tooltip_text);
        //Display variation data
        $tooltip_text = '<strong>' . __('Change image/price on variable products to image/price from variation that has attribute value of selected filters.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Image replace can do not work on some theme. Our plugin uses default WooCommerce functionality to replace image, but some theme do not use it.', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('If you have this issue, then please contact theme developer with this issue', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_display_variation_data_info', $tooltip_text);
        //Remember variation options
        $tooltip_text = '<h3 style="color:white;">' . __('Select variation on variable product page is based on selected filters', 'BeRocket_AJAX_domain') . '</h3>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Replace Product URL', 'BeRocket_AJAX_domain') . '</strong> - ' . __('selected filters will be added to product URL on products archive page (may not work with some themes)', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Set in PHP Session', 'BeRocket_AJAX_domain') . '</strong> - ' . __('selected filters will be stored in PHP session. PHP session stores the filters for all products that will be opened after filtering', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . '<strong style="color:#0085ba;">' . __('Both URL and Session', 'BeRocket_AJAX_domain') . '</strong> - ' . __('enable two previous methods and use them at the same time', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_use_filter_for_variation_show_info', $tooltip_text);
        
        return $data;
    }
    function section_elements_above ( $item, $options ) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $html = '<tr>
            <th scope="row">' . __('Elements over products', 'BeRocket_AJAX_domain') . '<span id="braapf_elements_over_products_info" class="dashicons dashicons-editor-help"></span>' . '</th>
            <td>';
                $posts_args = array(
                    'posts_per_page'   => -1,
                    'offset'           => 0,
                    'category'         => '',
                    'category_name'    => '',
                    'include'          => '',
                    'exclude'          => '',
                    'meta_key'         => '',
                    'meta_value'       => '',
                    'post_type'        => 'br_filters_group',
                    'post_mime_type'   => '',
                    'post_parent'      => '',
                    'author'           => '',
                    'post_status'      => 'publish',
                    'fields'           => 'ids',
                    'suppress_filters' => false 
                );
                $posts_array = new WP_Query($posts_args);
                $br_filters_group = $posts_array->posts;
                $html .= '<div>' . __('Group', 'BeRocket_AJAX_domain') . '<select>';
                foreach($br_filters_group as $post_id) {
                    $html .= '<option data-name="' . get_the_title($post_id) . '" value="' . $post_id . '">' . get_the_title($post_id) . ' (ID:' . $post_id . ')</option>';
                }
                $html .= '</select><button class="button berocket_elements_above_group" type="button">'.__('Add group', 'BeRocket_AJAX_domain').'</button></div>';
                $html .= '<ul class="berocket_elements_above_products">';
                if( is_array(br_get_value_from_array($options, 'elements_above_products')) ) {
                    foreach($options['elements_above_products'] as $post_id) {
                        $post_type = get_post_type($post_id);
                        $html .= '<li class="berocket_elements_added_' . $post_id . '"><fa class="fa fa-bars"></fa>
                            <input type="hidden" name="br_filters_options[elements_above_products][]" value="' . $post_id . '">
                            ' . get_the_title($post_id) . ' (ID:' . $post_id . ')
                            <i class="fa fa-times"></i>
                        </li>';
                    }
                }
                $html .= '</ul>';
                wp_enqueue_script('jquery-color');
                wp_enqueue_script('jquery-ui-sortable');
                $html .= "<script>
                    jQuery(document).on('click', '.berocket_elements_above_group', function(event) {
                        event.preventDefault();
                        var selected = jQuery(this).prev().find(':selected');
                        post_id = selected.val();
                        post_title = selected.text();
                        if( ! jQuery('.berocket_elements_added_'+post_id).length ) {
                            var html = '<li class=\"berocket_elements_added_'+post_id+'\"><fa class=\"fa fa-bars\"></fa>';
                            html += '<input type=\"hidden\" name=\"br_filters_options[elements_above_products][]\" value=\"'+post_id+'\">';
                            html += post_title;
                            html += '<i class=\"fa fa-times\"></i></li>';
                            jQuery('.berocket_elements_above_products').append(jQuery(html));
                        } else {
                            jQuery('.berocket_elements_added_'+post_id).css('background-color', '#ee3333').clearQueue().animate({backgroundColor:'#eeeeee'}, 1000);
                        }
                    });
                    jQuery(document).on('click', '.berocket_elements_above_products .fa-times', function(event) {
                        jQuery(this).parents('li').first().remove();
                    });
                    jQuery(document).ready(function() {
                        if(typeof(jQuery( \".berocket_elements_above_products\" ).sortable) == 'function') {
                            jQuery( \".berocket_elements_above_products\" ).sortable({axis:\"y\", handle:\".fa-bars\", placeholder: \"berocket_sortable_space\"});
                        }
                    });
                </script>
                <style>
                .berocket_elements_above_products li {
                    font-size: 2em;
                    border: 2px solid rgb(153, 153, 153);
                    background-color: rgb(238, 238, 238);
                    padding: 5px;
                    line-height: 1.1em;
                }
                .berocket_elements_above_products li .fa-bars {
                    margin-right: 0.5em;
                    cursor: move;
                }
                .berocket_elements_above_products small {
                    font-size: 0.5em;
                    line-height: 2em;
                    vertical-align: middle;
                }
                .berocket_elements_above_products li .fa-times {
                    margin-left: 0.5em;
                    cursor: pointer;
                    float: right;
                }
                .berocket_elements_above_products li .fa-times:hover {
                    color: black;
                }
                .berocket_elements_above_products .berocket_edit_filter {
                    vertical-align: middle;
                    font-size: 0.5em;
                    float: right;
                    line-height: 2em;
                    height: 2em;
                    display: inline-block;
                }
                .berocket_elements_above_products .berocket_sortable_space {
                    border: 2px dashed #aaa;
                    background: white;
                    font-size: 2em;
                    height: 1.1em;
                    box-sizing: content-box;
                    padding: 5px;
                }
                .br_framework_settings .button.berocket_elements_above_group {
                    margin: 0;
                    margin-left: 10px;
                    padding: 2px;
                }
                </style>
            </td>
        </tr>";
        $tooltip_text = '<strong>' . __('Will be displayed only on default WooCommerce page.', 'BeRocket_AJAX_domain') . '</strong>'
        . '<p>' . __('Default WooCommerce page are: shop page, category page, tag page, attribute page etc.', 'BeRocket_AJAX_domain') . '</p>'
        . '<p>' . __('Also it can does not work on WooCommerce pages edited with help of any page builders (Divi Builder, Elementor Builder etc.)', 'BeRocket_AJAX_domain') . '</p>';
        BeRocket_AAPF::add_tooltip('#braapf_elements_over_products_info', $tooltip_text);
        return $html;
    }

    function wp_head_enqueue() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( empty($options['styles_in_footer']) ) {
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
    }
    function filter_type_array($filter_type_array) {
        $filter_type_array = berocket_insert_to_array($filter_type_array, 'product_cat', array('custom_product_cat' => array(
            'name' => __('Product Category', 'BeRocket_AJAX_domain'),
            'sameas' => 'custom_taxonomy',
            'attribute' => 'product_cat',
        )), true);
        return $filter_type_array;
    }
    function aapf_conditions($conditions) {
        $conditions[] = 'condition_page_woo_attribute';
        $conditions[] = 'condition_page_woo_search';
        $conditions[] = 'condition_user_role';
        $conditions[] = 'condition_user_status';
        return $conditions;
    }
    function aapf_localize_widget_script($localize) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $option_permalink = $this->get_permalinks_oprions();
        $option_nn_permalink = $this->get_nn_permalinks_oprions();
        $permalink_values = explode( 'values', $option_permalink['value'] );
        $nn_permalink_values = explode( 'values', $option_nn_permalink['value'] );
        $localize['ub_product_count']           = ( empty($options['ub_product_count']) ? '' : $options['ub_product_count'] );
        $localize['ub_product_text']            = ( empty($options['ub_product_text']) ? '' : $options['ub_product_text'] );
        $localize['ub_product_button_text']     = ( empty($options['ub_product_button_text']) ? '' : $options['ub_product_button_text'] );
        $localize['number_style']               = array(
            wc_get_price_thousand_separator(), 
            wc_get_price_decimal_separator(), 
            wc_get_price_decimals()
        );
        $localize['hide_button_value']          = ( empty($options['hide_value']['button']) ? '' : $options['hide_value']['button'] );
        $localize['nice_urls']                  = ( empty($options['nice_urls']) ? '' : $options['nice_urls'] );
        $localize['nice_url_variable']          = $option_permalink['variable'];
        $localize['nice_url_mask']              = '%t%' . $permalink_values[0] . '%v%' . $permalink_values[1];
        $localize['nice_url_value_1']           = $permalink_values[0];
        $localize['nice_url_value_2']           = $permalink_values[1];
        $localize['nice_url_split']             = $option_permalink['split'];
        $localize['nn_url_variable']            = $option_nn_permalink['variable'];
        $localize['nn_url_mask']                = '%t%' . $nn_permalink_values[0] . '%v%' . $nn_permalink_values[1];
        $localize['nn_url_value_1']             = $nn_permalink_values[0];
        $localize['nn_url_value_2']             = $nn_permalink_values[1];
        $localize['nn_url_split']               = ($option_nn_permalink['split'] == '&' ? '/' : $option_nn_permalink['split']);
        
        if( empty($options['nice_urls']) ) {
            $localize['url_variable']   = $option_nn_permalink['variable'];
            $localize['url_mask']       = '%t%' . $nn_permalink_values[0] . '%v%' . $nn_permalink_values[1];
            $localize['url_split']      = ($option_nn_permalink['split'] == '&' ? '/' : $option_nn_permalink['split']);
        } else {
            $localize['url_variable']   = $option_permalink['variable'];
            $localize['url_mask']       = '%t%' . $permalink_values[0] . '%v%' . $permalink_values[1];
            $localize['url_split']      = $option_permalink['split'];
        }
        return $localize;
    }
    function permalink_variable_nn_name($name) {
        $option_nn_permalink = $this->get_nn_permalinks_oprions();
        return $option_nn_permalink['variable'];
    }
    function plugins_loaded() {
        $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
        $BeRocket_AAPF_group_filters->add_meta_box('search_box', __( 'Search Box', 'BeRocket_AJAX_domain' ), array($this, 'search_box'));
        $this->global_settings();
        $this->group_add();
        $this->filter_add();
        $this->multiple_color();
        $this->ranges();
        $this->count_before_update();
        add_filter('berocket_aapf_group_before_all', array($this, 'group_is_hide_before_group'), 10, 2);
        add_filter('berocket_aapf_group_after_all', array($this, 'group_is_hide_after_group'), 10, 2);
    }
    function admin_init() {
        $screen = get_current_screen();
        $admin_js = '';
        if( berocket_isset($screen, 'id') != 'widgets' ) {
            $admin_js .= 'berocket_admin_filter_types_by_attr.ranges = "<option value=\'ranges\'>'.__('Ranges', 'BeRocket_AJAX_domain').'</option>";
            berocket_admin_filter_types.price.push("ranges");';
        }
        $admin_js .= 'berocket_admin_filter_types.custom_taxonomy.push("slider");
        berocket_admin_filter_types.attribute.push("slider");
        berocket_admin_filter_types.filter_by.push("slider");';
        wp_add_inline_script('berocket_aapf_widget-admin', $admin_js);
    }
    function search_box($post) {
        $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
        wp_enqueue_script('jquery-ui-sortable');
        $filters = $BeRocket_AAPF_group_filters->get_option($post->ID);
        $post_name = $BeRocket_AAPF_group_filters->post_name;
        $categories = BeRocket_AAPF_Widget_functions::get_product_categories();
        $categories = BeRocket_AAPF_Widget_functions::set_terms_on_same_level( $categories );
        include AAPF_TEMPLATE_PATH . "paid/filters_search_box.php";
    }
    //GLOBAL SETTINGS
    function global_settings() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();

        if( is_array(br_get_value_from_array($option, 'elements_above_products')) && count($option['elements_above_products']) ) {
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'elements_above_products'), 1 );
        }
        add_filter( 'berocket_aapf_is_filtered_page_check', array($this, 'is_filtered_word_changed'), 10, 3 );
        //add_action( 'br_aapf_args_converter_before', array($this, 'br_aapf_args_converter'), 10, 1 );
        add_filter( 'brapf_args_converter_get_string', array($this, 'args_converter'), 10, 3 );
        if ( ! empty( $option['nice_urls'] ) ) {
            add_action( 'init', array( $this, 'nice_url_init' ) );

            if ( defined( 'POLYLANG_BASENAME' ) ) {
                add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ), 9 );
                add_filter('pll_filtered_taxonomies', array($this, 'pll_filtered_taxonomies'), 10, 2);
                add_filter('pll_rewrite_rules', array($this, 'pll_rewrite_rules'));
            }
            add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ), 999999999 );
            add_filter( 'query_vars', array( $this, 'add_queryvars' ) );
            add_filter( 'berocket_aapf_current_page_url', array($this, 'current_page_url'), 10, 2 );
            add_filter( 'berocket_aapf_is_filtered_page_check', array($this, 'is_filtered_with_nice_url'), 10, 3 );
            add_filter( 'berocket_add_filter_to_link_explode', array($this, 'add_filter_to_link_explode_nice_url') );
            add_filter( 'berocket_add_filter_to_link_filters_str', array($this, 'add_filter_to_link_filters_str_nice_url') );
            add_filter( 'berocket_add_filter_to_link_implode', array($this, 'add_filter_to_link_implode_nice_url') );
        } else {
            add_filter( 'berocket_add_filter_to_link_explode', array($this, 'add_filter_to_link_explode'), 10, 2 );
            add_filter( 'brapf_TEMP_generate_url_strip_symbols', array($this, 'TEMP_generate_url_strip_symbols') );
            add_filter( 'berocket_add_filter_to_link_implode', array($this, 'add_filter_to_link_implode') );
        }
        if( ! empty( $option['use_links_filters'] ) ) {
            add_filter('berocket_check_radio_color_filter_term_text', array($this, 'check_radio_color_filter_term_text'), 10, 4);
        }
        add_action('wp_head', array($this, 'wp_head_canonical'), 99999);
        add_action('wpseo_canonical', array($this, 'yoast_canonical'), 99999);
        if( ! empty($option['search_variation_image']) ) {
            include_once( dirname( __FILE__ ) . '/paid/woocommerce-variation-image.php' );
        }
        if( ! empty($option['search_variation_price']) ) {
            include_once( dirname( __FILE__ ) . '/paid/woocommerce-variation-price.php' );
        }
        if( ! empty($option['select_filter_variation'])  ) {
            add_filter( 'woocommerce_loop_product_link', array( $this, 'woocommerce_loop_product_link' ), 1, 2 );
            if( strpos($option['select_filter_variation'], 'session') !== FALSE && ! is_admin() ) {
                if(!session_id()) {
                    session_start();
                }
                add_action( 'wp_head', array( $this, 'wp_head' ) );
            }
        }
        add_filter('berocket_aapf_convert_limits_to_tax_query', array($this, 'convert_limits_to_tax_query'));
        add_filter( 'berocket_aapf_filters_on_page_load', array($this, 'convert_limits_to_tax_query') );
        add_filter( 'bapf_loop_shop_post_in', array( $this, 'add_terms' ), 900 );
        add_filter( 'loop_shop_post_in', array( $this, 'add_terms' ), 900 );
        add_filter( 'berocket_recount_taxonomy_data', array( $this, 'add_terms_recount' ), 900, 1 );
        add_action( 'berocket_aapf_wizard_attribute_count_hide_values', array( $this, 'wizard_attribute_count_hide_values' ), 10, 1 );
        add_action( 'current_screen', array( $this, 'register_permalink_option' ) );
    }
    function wizard_attribute_count_hide_values($option) {
        ?>
        <div><label><input name="berocket_aapf_wizard_settings[hide_value][button]" class="attribute_count_preset_16" type="checkbox" value="1"
        <?php if( ! empty($option['hide_value']['button']) ) echo " checked"; ?>>
        <?php _e('Hide "Show/Hide value(s)" button', 'BeRocket_AJAX_domain') ?>
        </label></div>
        <?php
    }
    function elements_above_products() {
        $BeRocket_AAPF           = BeRocket_AAPF::getInstance();
        $options                 = $BeRocket_AAPF->get_option();
        $elements_above_products = br_get_value_from_array($options, 'elements_above_products');
        if ( ! is_array( $elements_above_products ) ) {
            $elements_above_products = array();
        }

        if ( $elements_above_products ) {
            $current_language = apply_filters( 'wpml_current_language', NULL );
            $BeRocket_AAPF_group_filters = BeRocket_AAPF_group_filters::getInstance();
            foreach($elements_above_products as $element_above_products) {
                $group_id      = apply_filters( 'wpml_object_id', $element_above_products, 'page', true, $current_language );
                $group_options = $BeRocket_AAPF_group_filters->get_option( $group_id );
                $extra_class   = '';
                if ( ! empty( $group_options['hide_group'] ) and is_array( $group_options['hide_group'] ) ) {
                    foreach ( $group_options['hide_group'] as $device => $active ) {
                        if ( $active and $device ) {
                            $extra_class .= ' bapf_sngl_hd_' . $device;
                        }
                    }

                }
                echo '<div class="berocket_element_above_products' . $extra_class . '">';
                the_widget( 'BeRocket_new_AAPF_Widget', array('group_id' => $element_above_products));
                echo '</div><div class="berocket_element_above_products_after"></div>';
            }
        }
    }
    public function group_is_hide_before_group($custom_vars, $filters) {
        if ( ! empty( $filters[ 'group_is_hide' ] ) ) {
            $extra_class   = '';
            if ( ! empty( $filters['hide_group'] ) and is_array( $filters['hide_group'] ) ) {
                foreach ( $filters['hide_group'] as $device => $active ) {
                    if ( $active and $device ) {
                        $extra_class .= ' bapf_sngl_hd_' . $device;
                    }
                }

            }
            echo '<a href="#toggle-filters" class="berocket_element_above_products_is_hide_toggle berocket_ajax_filters_toggle' . ( ( ! empty( $filters[ 'group_is_hide_theme' ] ) ) ? ' theme-' . $filters[ 'group_is_hide_theme' ] : '' ) . ( ( ! empty( $filters['group_is_hide_icon_theme'] ) ) ? ' icon-theme-' . $filters['group_is_hide_icon_theme'] : '' ) . $extra_class . '"><span><i></i><b></b><s></s></span>' . __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) . '</a>';
            echo '<div class="berocket_element_above_products_is_hide br_is_hidden">';
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
        return $custom_vars;
    }
    public function group_is_hide_after_group($custom_vars, $filters) {
        if ( ! empty( $filters[ 'group_is_hide' ] ) ) {
            echo '</div>';
        }
        return $custom_vars;
    }
    public function wp_head_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'custom_sidebar_toggle'), 1 );
        }
    }
    public function wp_init_sidebar() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        add_action( 'wp_enqueue_scripts', array( $BeRocket_AAPF, 'include_all_scripts' ) );
    }
    public function shortcode_sidebar_button($args = array()) {
        ob_start();
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $this->custom_sidebar_toggle($args);
        }
        return ob_get_clean();
    }
    public function custom_sidebar_toggle($args = array()) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $theme_class      = (empty($args['theme']) ? ( ( ! empty( $option['sidebar_collapse_theme'] ) ) ? ' theme-' . $option['sidebar_collapse_theme'] : '' ) : ' theme-'.$args['theme'] );
        $icon_theme_class = (empty($args['icon_theme']) ? ( ( ! empty( $option['sidebar_collapse_icon_theme'] ) ) ? ' icon-theme-' . $option['sidebar_collapse_icon_theme'] : '' ) : ' icon-theme-'.$args['icon_theme'] );
        $button_text      = (empty($args['title']) ? __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) : $args['title']);
        echo '<a href="#toggle-sidebar" class="berocket_ajax_filters_sidebar_toggle berocket_ajax_filters_toggle' . $theme_class . '' . $icon_theme_class . '"><span><i></i><b></b><s></s></span>' . $button_text . '</a>';
        BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
    }
    function convert_limits_to_tax_query($args) {
        if ( ! empty($_POST['price_ranges']) ) {
            if ( ! isset( $args['meta_query'] ) ) {
                $args['meta_query'] = array();
            }
            $price_range_query = array( 'relation' => 'OR' );
            foreach ( $_POST['price_ranges'] as $range ) {
                $range = apply_filters('berocket_min_max_filter_range', explode( '*', $range ));
                $price_range_query[] = array( 'key' => apply_filters('berocket_price_filter_meta_key', '_price', 'paid_478'), 'compare' => 'BETWEEN', 'type' => 'DECIMAL', 'value' => array( intval($range[0] - 1), intval($range[1]) ) );
            }
            $args['meta_query'][] = $price_range_query;
        }
        if( empty($_POST['limits']) ) {
            return $args;
        }
        $limits = $_POST['limits'];
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $tax_query = (! isset($args['tax_query']) || ! is_array($args['tax_query']) ? array() : $args['tax_query']);
        if ( ! empty($limits) ) {
            $wc_attributes = wc_get_attribute_taxonomy_names();
            foreach ( $limits as $v ) {
                if( $v[0] == 'pa__date' || $v[0] == '_date' ) {
                    $from_date = substr($v[1], 4, 2).'/'.substr($v[1], 6, 2).'/'.substr($v[1], 0, 4);
                    $to_date = substr($v[2], 4, 2).'/'.substr($v[2], 6, 2).'/'.substr($v[2], 0, 4);
                    $from = date('Y-m-d 00:00:00', strtotime($from_date));
                    $to = date('Y-m-d 23:59:59', strtotime($to_date));
                    $args['date_query'] = array(
                        'after' => $from,
                        'before' => $to,
                    );
                    continue;
                }
                $v[1] = urldecode( $v[1] );
                $v[2] = urldecode( $v[2] );
                $all_terms_name = array();
                $all_terms_slug = array();
                $braapf_sliders = apply_filters('braapf_slider_data', array());
                if( is_array($braapf_sliders) && isset($braapf_sliders[$v[0]]) && isset($braapf_sliders[$v[0]]['get_terms_args']) && isset($braapf_sliders[$v[0]]['get_terms_advanced']) ) {
                    $get_terms_args = $braapf_sliders[$v[0]]['get_terms_args'];
                    $full_terms = berocket_aapf_get_terms( $get_terms_args, $braapf_sliders[$v[0]]['get_terms_advanced'] );
                    $taxonomy_terms = wp_list_pluck($full_terms, 'slug', 'term_id');
                    $search = array_values($taxonomy_terms);
                    if( in_array(br_get_value_from_array($braapf_sliders, array($v[0], 'get_terms_advanced', 'orderby')), array('name_numeric_full', 'slug_num') ) ) {
                        $v[1] = intval($v[1]);
                        $v[2] = intval($v[2]);
                        $search_new = array();
                        foreach($full_terms as $search_term) {
                            if( in_array(br_get_value_from_array($braapf_sliders, array($v[0], 'get_terms_advanced', 'orderby')), array('name_numeric_full') ) ) {
                                $name_num = floatval($search_term->name);
                            } else {
                                $name_num = floatval($search_term->slug);
                            }
                            if( $name_num >= $v[1] && $name_num <= $v[2] ) {
                                $search_new[] = $search_term->slug;
                            }
                        }
                        $search = $search_new;
                    } else {
                        $search2 = $search;
                        foreach($search2 as &$search_val) {
                            $search_val = urldecode($search_val);
                        }
                        if( isset($search_val) ) {
                            unset($search_val);
                        }
                        $start_terms    = array_search( $v[1], $search2 );
                        $end_terms      = array_search( $v[2], $search2 );
                        $search = array_slice( $search, $start_terms, ( $end_terms - $start_terms + 1 ) );
                    }
                } else {
                    $terms = get_terms( array('taxonomy' => $v[0], 'hide_empty' => false) );
                    
                    $wc_order_by = wc_attribute_orderby( $v[0] );
                    BeRocket_AAPF_Widget_functions::sort_terms( $terms, array(
                        "wc_order_by"     => $wc_order_by,
                        "order_values_by" => '',
                        "filter_type"     => 'attribute',
                        "order_values_type"=> SORT_ASC
                    ) );
                    $is_numeric = true;
                    $is_with_string = false;
                    if( is_wp_error ( $all_terms_name ) ) {
                        BeRocket_updater::$error_log[] = $all_terms_name->errors;
                    }
                    if( ! is_numeric($v[1]) || ! is_numeric($v[2]) ) {
                        $is_with_string = true;
                    }
                    foreach ( $terms as $term ) {
                        if( ! is_numeric( substr( $term->name[0], 0, 1 ) ) ) {
                            $is_numeric = false;
                        }
                        if( ! is_numeric( $term->name ) ) {
                            $is_with_string = true;
                        }
                        array_push( $all_terms_name, $term->slug );
                        array_push( $all_terms_slug, $term->name );
                    }
                    if( $is_numeric ) {
                        array_multisort( $all_terms_slug, SORT_NUMERIC, $all_terms_name, $all_terms_slug );
                    } elseif(! in_array($v[0], $wc_attributes)) {
                        //array_multisort( $all_terms_name, $all_terms_name, $all_terms_slug );
                    }
                    $taxonomy_terms = get_terms(array('fields' => 'id=>slug', 'taxonomy' => $v[0]));
                    if( $is_with_string ) {
                        $start_terms    = array_search( $v[1], $all_terms_name );
                        $end_terms      = array_search( $v[2], $all_terms_name );
                        $all_terms_name = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
                        $search = $all_terms_name;
                    } else {
                        $start_terms = false;
                        $end_terms = false;
                        $previous_pos = false;
                        $search = array();
                        foreach($all_terms_slug as $term_pos => $term) {
                            if( $term >= $v[1] && $start_terms === false ) {
                                $start_terms = $term_pos;
                            }
                            if( $end_terms === false ) {
                                if( $term > $v[2] ) {
                                    if( $previous_pos !== false ) {
                                        $end_terms = $previous_pos;
                                    }
                                } elseif( $term == $v[2] ) {
                                    $end_terms = $term_pos;
                                }
                            }
                            $previous_pos = $term_pos;
                        }
                        if( $start_terms > $end_terms ) {
                            $search = array();
                        } elseif( $v[1] > $v[2] ) {
                            $search = array();
                        } else {
                            $search = array_slice( $all_terms_name, $start_terms, ( $end_terms - $start_terms + 1 ) );
                        }
                    }
                }
                $ids_array = array();
                foreach($search as $search_el) {
                    $id = array_search($search_el, $taxonomy_terms);
                    if( $id !== FALSE ) {
                        $ids_array[] = $id;
                    }
                }
                if( empty($_POST['limits_arr']) ) {
                    $_POST['limits_arr'] = array();
                }
                $_POST['limits_arr'][$v[0]] = $ids_array;
                if( ! empty($options['slider_250_fix']) ) {
                    $args_send = apply_filters('berocket_aapf_tax_query_attribute', array(
                        'taxonomy'          => $v[0],
                        'field'             => 'id',
                        'terms'             => $ids_array,
                        'operator'          => 'IN',
                        'include_children'  => false,
                        'is_berocket'       => true
                    ));
                } else {
                    $args_send = array('relation' => 'OR');
                    if( count($ids_array) ) {
                        foreach($ids_array as $id) {
                            $args_send[] = apply_filters('berocket_aapf_tax_query_attribute', array(
                                'taxonomy'          => $v[0],
                                'field'             => 'id',
                                'terms'             => $id,
                                'operator'          => 'IN',
                                'include_children'  => false,
                                'is_berocket'       => true
                            ));
                        }
                    } else {
                            $args_send[] = apply_filters('berocket_aapf_tax_query_attribute', array(
                                'taxonomy'          => $v[0],
                                'field'             => 'id',
                                'terms'             => array(),
                                'operator'          => 'IN',
                                'include_children'  => false,
                                'is_berocket'       => true
                            ));
                    }
                }
                $tax_query['relation'] = 'AND';
                $tax_query[] = $args_send;
                unset($search);
            }
        }
        $args['tax_query'] = $tax_query;
        return $args;
    }
    //NICE URL
    public function nice_url_init () {
        $option_permalink = $this->get_permalinks_oprions();
        add_rewrite_endpoint($option_permalink['variable'], EP_PERMALINK|EP_SEARCH|EP_CATEGORIES|EP_TAGS|EP_PAGES);
    }
    function add_rewrite_rules ( $rules ) {
        $newrules = array();
        $shop_id = wc_get_page_id('shop');
        $shop_page_slugs = array(_x( 'shop', 'default-slug', 'woocommerce' ) => _x( 'shop', 'default-slug', 'woocommerce' ));
        $languages = apply_filters('wpml_active_languages', array());
        if( ! is_array($languages) || ! count($languages) ) {
            $languages = array('0' => array());
        }
        $option_permalink_languages = array();
        $wpml_active_languages = apply_filters('wpml_current_language', NULL);
        foreach($languages as $language_code => $language) {
            do_action( 'wpml_switch_language', $language_code );
            $option_permalink_languages[$language_code] = $this->get_permalinks_oprions();
        }
        do_action( 'wpml_switch_language', $wpml_active_languages );
        if( ! empty($shop_id) ) {
            foreach($languages as $language_code => $language) {
                $option_permalink = $option_permalink_languages[$language_code];
                $shop_slug = get_post(apply_filters('wpml_object_id', $shop_id, 'page', TRUE, $language_code));
                $newrules[$option_permalink['variable'].'/(.*)/?'] = 'index.php?post_type=product&'.$option_permalink['variable'].'=$matches[1]';
                if ( ! empty( $shop_slug ) and is_object( $shop_slug ) and ! empty( $shop_slug->post_name ) ) {
                    $shop_post_name = $shop_slug->post_name;
                    $shop_page_slug = get_page_uri($shop_slug);
                    if ( br_get_woocommerce_version() >= 2.7 ) {
                        $newrules[ urldecode($shop_page_slug) . '/' . $option_permalink[ 'variable' ] . '/(.*)/?' ] = 'index.php?post_type=product&' . $option_permalink[ 'variable' ] . '=$matches[1]';
                    } else {
                        $newrules[ $shop_page_slug . '/' . $option_permalink[ 'variable' ] . '/(.*)/?' ] = 'index.php?pagename=' . $shop_post_name . '&' . $option_permalink[ 'variable' ] . '=$matches[1]';
                    }
                }
            }
        }
        $option_permalink = $this->get_permalinks_oprions();
        $category_base = get_option( 'woocommerce_permalinks' );
        $tag_base = $category_base['tag_base'];
        $category_base = $category_base['category_base'];

        if ( empty($category_base) ) {
            $category_base = _x( 'product-category', 'slug', 'woocommerce' );
        }
        if ( empty($tag_base) ) {
            $tag_base = _x( 'product-tag', 'slug', 'woocommerce' );
        }
        $product_taxonomies = get_object_taxonomies('product');
        $product_taxonomies = array_diff($product_taxonomies, array('product_type', 'product_visibility', 'product_cat', 'product_tag', 'product_shipping_class'));
        
        foreach($languages as $language_code => $language) {
            $option_permalink = $option_permalink_languages[$language_code];
            $newrules[$category_base.'/(.+?)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_cat=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
            $newrules[$tag_base.'/([^/]+)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?product_tag=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
            foreach($product_taxonomies as $product_taxonomy) {
                $product_taxonomy = get_taxonomy($product_taxonomy);
                if( ! empty($product_taxonomy->public) ) {
                    if( ! empty($product_taxonomy->rewrite) && ! empty($product_taxonomy->rewrite['slug']) ) {
                        $taxonomy_base = $product_taxonomy->rewrite['slug'];
                    } else {
                        $taxonomy_base = $product_taxonomy->name;
                    }
                    if( $taxonomy_base[0] == '/' ) {
                        $taxonomy_base = substr($taxonomy_base, 1);
                    }
                    $newrules[$taxonomy_base.'/([^/]+)/'.$option_permalink['variable'].'/(.*)/?'] = 'index.php?'.$product_taxonomy->name.'=$matches[1]&'.$option_permalink['variable'].'=$matches[2]';
                }
            }
        }

        $newrules = apply_filters('br_filters_rewrite_rules', $newrules);

        return $newrules + $rules;
    }
    function add_queryvars( $query_vars ) {
        $option_permalink = $this->get_permalinks_oprions();
        $query_vars[] = $option_permalink['variable'];
        return $query_vars;
    }
    function current_page_url($current_page_url, $br_options) {
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_values = explode( 'values', $option_permalink['value'] );
        $current_page_url = preg_replace( "~".$option_permalink['variable']."/.+~", "", $current_page_url );
        $current_page_url = preg_replace( "~".urlencode($option_permalink['variable'])."/.+~", "", $current_page_url );
        return $current_page_url;
    }
    function is_filtered_with_nice_url($check, $func, $query = false) {
        if( $query === false ) {
            global $wp_query;
            $query = $wp_query;
        }
        $option_permalink = $this->get_permalinks_oprions();
        $check = ( $check || $query->get( $option_permalink['variable'], '' ) );
        return $check;
    }
    function is_filtered_word_changed($check, $func, $query = false) {
        $nn_option_permalink = $this->get_nn_permalinks_oprions();
        $check = ( $check || isset( $_GET[ $nn_option_permalink['variable'] ] ) );
        return $check;
    }
    function args_converter($filters, $br_options, $query) {
        global $wp_rewrite;
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_variable = $query->get( $option_permalink['variable'], '' );
        $pagination_base = ( (! empty($wp_rewrite) && is_object($wp_rewrite) && property_exists($wp_rewrite, 'pagination_base')) ? $wp_rewrite->pagination_base : 'page' );
        if( ! empty($permalink_variable) ) {
            $values_split = $option_permalink['value'];
            $values_split = explode( 'values', $values_split );
            $regex = '#(.+?)'.preg_quote($values_split[0]).'(.+?)'.preg_quote($values_split[1].$option_permalink['split']).'#';
            $filters = $query->get( $option_permalink['variable'], '' );
            if( empty($br_options['seo_uri_decode']) && ! empty($filters) ) {
                $filters = urlencode($filters);
                $filters = str_replace('+', urlencode('+'), $filters);
                $filters = urldecode($filters);
                $query->set($option_permalink['variable'], $filters);
            }
            $filters = str_replace('+', '%2B', $filters);
            $filters = urldecode( $filters );
            if( preg_match('#\/'.$pagination_base.'\/(\d+)#', $filters, $page_match) ) {
                $filters = preg_replace( '#\/'.$pagination_base.'\/(\d+)#', '', $filters );
                $_GET['paged'] = $page_match[1];
                set_query_var( 'paged', $page_match[1] );
            }
            $filters = $filters.$option_permalink['split'];
            $query_string = '';
            $matches = array();
            preg_match_all( $regex, $filters, $matches );
            for($i = 0; $i < count($matches[1]); $i++ ) {
                if( strlen($query_string) > 0 ) {
                    $query_string .= '|';
                }
                $query_string .= $matches[1][$i].'['.$matches[2][$i].']';
            }
            $filters = $query_string;
        } else {
            $nn_option_permalink = $this->get_nn_permalinks_oprions();
            $values_split = $nn_option_permalink['value'];
            $values_split = explode( 'values', $values_split );
            $regex = '#(.+?)'.preg_quote($values_split[0]).'(.+?)'.preg_quote($values_split[1].$nn_option_permalink['split']).'#';
            $filters = (isset( $_GET[ $nn_option_permalink['variable'] ] ) ? $_GET[ $nn_option_permalink['variable'] ] : '' );
            if( empty($br_options['seo_uri_decode']) && ! empty($filters) ) {
                $filters = urlencode($filters);
                $filters = str_replace('+', urlencode('+'), $filters);
                $filters = urldecode($filters);
                $_GET[ $nn_option_permalink['variable'] ] = $filters;
            }
            $filters = str_replace('+', '%2B', $filters);
            $filters = urldecode( $filters );

            if( preg_match('#\/'.$pagination_base.'\/(\d+)#', $filters, $page_match) ) {
                $filters = preg_replace( '#\/'.$pagination_base.'\/(\d+)#', '', $filters );
                $_GET['paged'] = $page_match[1];
                set_query_var( 'paged', $page_match[1] );
            }
            $filters = $filters.$nn_option_permalink['split'];
            $query_string = '';
            $matches = array();
            preg_match_all( $regex, $filters, $matches );
            for($i = 0; $i < count($matches[1]); $i++ ) {
                if( strlen($query_string) > 0 ) {
                    $query_string .= '|';
                }
                $query_string .= $matches[1][$i].'['.$matches[2][$i].']';
            }
            $filters = $query_string;
        }
        return $filters;
    }
    function br_aapf_args_converter($query) {
        global $wp_rewrite;
        global $br_aapf_args_converted;

        if ( ! empty( $br_aapf_args_converted ) ) return true;
        $br_aapf_args_converted = true;

        $option_permalink = $this->get_permalinks_oprions();
        $permalink_variable = $query->get( $option_permalink['variable'], '' );
        if( ! empty($permalink_variable) ) {
            $values_split = $option_permalink['value'];
            $values_split = explode( 'values', $values_split );
            $regex = '#(.+?)'.preg_quote($values_split[0]).'(.+?)'.preg_quote($values_split[1].$option_permalink['split']).'#';
            $filters = $query->get( $option_permalink['variable'], '' );
            $filters = str_replace('+', '%2B', $filters);
            $filters = urldecode( $filters );
            if( preg_match('#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', $filters, $page_match) ) {
                $filters = preg_replace( '#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', '', $filters );
                $_GET['paged'] = $page_match[1];
                set_query_var( 'paged', $page_match[1] );
            }
            $filters = $filters.$option_permalink['split'];
            $query_string = '';
            $matches = array();
            preg_match_all( $regex, $filters, $matches );
            for($i = 0; $i < count($matches[1]); $i++ ) {
                if( strlen($query_string) > 0 ) {
                    $query_string .= '|';
                }
                $query_string .= $matches[1][$i].'['.$matches[2][$i].']';
            }
            $_GET['filters'] = $query_string;
        } else {
            $nn_option_permalink = $this->get_nn_permalinks_oprions();
            $values_split = $nn_option_permalink['value'];
            $values_split = explode( 'values', $values_split );
            $regex = '#(.+?)'.preg_quote($values_split[0]).'(.+?)'.preg_quote($values_split[1].$nn_option_permalink['split']).'#';
            $filters = (isset( $_GET[ $nn_option_permalink['variable'] ] ) ? $_GET[ $nn_option_permalink['variable'] ] : '' );
            $filters = str_replace('+', '%2B', $filters);
            $filters = urldecode( $filters );

            if( preg_match('#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', $filters, $page_match) ) {
                $filters = preg_replace( '#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', '', $filters );
                $_GET['paged'] = $page_match[1];
                set_query_var( 'paged', $page_match[1] );
            }
            $filters = $filters.$nn_option_permalink['split'];
            $query_string = '';
            $matches = array();
            preg_match_all( $regex, $filters, $matches );
            for($i = 0; $i < count($matches[1]); $i++ ) {
                if( strlen($query_string) > 0 ) {
                    $query_string .= '|';
                }
                $query_string .= $matches[1][$i].'['.$matches[2][$i].']';
            }
            $_GET['filters'] = $query_string;
        }
    }
    function add_filter_to_link_explode($vars, $current_url) {
        list($url_string, $query_string, $filters) = $vars;
        $settings = $this->get_nn_permalinks_oprions();
        if( $settings['variable'] != 'filters' ) {
            parse_str( parse_url( $current_url, PHP_URL_QUERY ), $filters );
            $filters = br_get_value_from_array( $filters, $settings['variable'] );
            if( ! empty($query_string) ) {
                $current_url = $url_string.'?'.$query_string;
                $current_url = remove_query_arg( $settings['variable'], $current_url );
                if( strpos($current_url, '?') !== FALSE ) {
                    list( $url_string, $query_string ) = explode( '?', $current_url );
                } else {
                    $url_string = $current_url;
                    $query_string = '';
                }
            }
        }
        return array($url_string, $query_string, $filters);
    }
    function TEMP_generate_url_strip_symbols($strip_symbols) {
        $option_permalink = $this->get_nn_permalinks_oprions();
        if( ! empty($option_permalink) ) {
            $permalink_values = explode( 'values', $option_permalink['value'] );
            $strip_symbols['filters'] = $option_permalink['split'];
            $strip_symbols['before_val'] = $permalink_values[0];
            $strip_symbols['after_val'] = $permalink_values[1];
        }
        return $strip_symbols;
    }
    function add_filter_to_link_explode_nice_url($vars) {
        global $wp_rewrite;
        list($url_string, $query_string, $filters) = $vars;
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_var = $option_permalink['variable'];
        $permalink_values = explode( 'values', $option_permalink['value'] );
        $url_string = urldecode( $url_string );
        if( strpos($url_string, '/'.$permalink_var.'/') === FALSE ) {
            $filters = '';
        } else {
            list($url_string, $filters) = explode('/'.$permalink_var.'/', $url_string);
            $url_string = $url_string.'/';
        }
        if( $filters ) {
            $regex = '#(.+?)'.preg_quote($permalink_values[0]).'(.+?)'.preg_quote($permalink_values[1]).preg_quote($option_permalink['split']).'#';
            $filters = str_replace('+', '%2B', $filters);
            $filters = urldecode( $filters );
            if( preg_match('#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', $filters, $page_match) ) {
                $filters = preg_replace( '#\/'.$wp_rewrite->pagination_base.'\/(\d+)#', '', $filters );
            }
            if( substr($filters, -1) == '/' ) {
                $filters = substr($filters, 0, -1);
            }
            if( strpos(substr($filters, -2),$option_permalink['split']) === FALSE ) {
                $filters = $filters.$option_permalink['split'];
            }
            $query_filter = '';
            $matches = array();
            preg_match_all( $regex, $filters, $matches );
            for($i = 0; $i < count($matches[1]); $i++ ) {
                if( strlen($query_filter) > 0 ) {
                    $query_filter .= '|';
                }
                $query_filter .= $matches[1][$i].'['.$matches[2][$i].']';
            }
            $filters = $query_filter;
        }
        return array($url_string, $query_string, $filters);
    }
    function add_filter_to_link_filters_str_nice_url($vars) {
        list($filter_array, $implode) = $vars;
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_var = $option_permalink['variable'];
        $permalink_values = explode( 'values', $option_permalink['value'] );
        foreach($filter_array as &$filter_str) {
            $filter_str = str_replace(array('[', ']'), $permalink_values, $filter_str);
        }
        if( isset($filter_str) ) {
            unset($filter_str);
        }
        $implode = $option_permalink['split'];
        return array($filter_array, $implode);
    }
    function add_filter_to_link_implode($vars) {
        list($url_string, $query_string, $filters) = $vars;
        $settings = $this->get_nn_permalinks_oprions();
        if( $settings['variable'] != 'filters' ) {
            $permalink_var = $settings['variable'];
            $query_string = $permalink_var.'='.$filters.(empty($query_string) ? '' : '&'.$query_string);
            $filters = '';
        }
        return array($url_string, $query_string, $filters);
    }
    function add_filter_to_link_implode_nice_url($vars) {
        list($url_string, $query_string, $filters) = $vars;
        $option_permalink = $this->get_permalinks_oprions();
        $permalink_var = $option_permalink['variable'];
        $permalink_values = explode( 'values', $option_permalink['value'] );
        if( ! empty($filters) ) {
            $permalink_structure = get_option('permalink_structure');
            if ( $permalink_structure ) {
                $permalink_structure = substr($permalink_structure, -1);
                if ( $permalink_structure == '/' ) {
                    $permalink_structure = true;
                } else {
                    $permalink_structure = false;
                }
            } else {
                $permalink_structure = false;
            }
            $url_string .= (substr($url_string, -1) == '/' ? '' : '/');
            $url_string = $url_string . $option_permalink['variable'] . '/' . $filters . ($permalink_structure ? '/' : '');
            $filters = '';
        }
        return array($url_string, $query_string, $filters);
    }
    //REPLACE LINK FOR VARIABLE PRODUCTS
    public function woocommerce_loop_product_link($link, $product) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        global $berocket_filters_session;
        if( $product->is_type('variable') && ! empty($berocket_filters_session) ) {
            if( ! empty($berocket_filters_session['terms']) ) {
                $filter_attribute = $BeRocket_AAPF->get_attribute_for_variation_link($product, $berocket_filters_session['terms']);
                foreach($filter_attribute as $attribute_name => $attribute_val) {
                    $link = add_query_arg('attribute_'.$attribute_name, $attribute_val, $link);
                }
            }
        }
        return $link;
    }
    public function wp_head() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( session_id() && ! empty($_SESSION['BeRocket_filters']) && is_product()) {
            $product_id = get_the_ID();
            $product = wc_get_product($product_id);
            if( $product->is_type('variable') ) {
                if( ! empty($_SESSION['BeRocket_filters']['terms']) ) {
                    $filter_attribute = $BeRocket_AAPF->get_attribute_for_variation_link($product, $_SESSION['BeRocket_filters']['terms']);
                    foreach($filter_attribute as $attribute_name => $attribute_val) {
                        if( empty($_REQUEST['attribute_'.$attribute_name]) ) {
                            $_REQUEST['attribute_'.$attribute_name] = $attribute_val;
                        }
                    }
                }
            }
            if( ! empty($options['select_filter_variation']) && strpos($options['select_filter_variation'], 'url') !== FALSE ) {
                unset($_SESSION['BeRocket_filters']);
            }
        }
    }
    //CHECKBOX/RADIO/COLOR WITH LINKS
    function check_radio_color_filter_term_text($text, $term, $operator, $single) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');

        if( empty($term_taxonomy_echo) ) {
            $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
        }

        $noindex = false;
        if ( ! empty( $option['use_noindex'] ) and
             (
                 $option['use_noindex'] == 1 or
                 $option['use_noindex'] == 2 and
                 apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'check_radio_color_filter_term_text' )
             )
        ) {
            $noindex = true;
        }

        $new_text = '';
        if ( $noindex ) {
            $new_text = '<noindex>';
        }

        $new_text .= '<a href="'.apply_filters('berocket_add_filter_to_link', FALSE, array(
            'attribute'         => $term_taxonomy_echo,
            'values'            => berocket_isset($term, (! empty($option['slug_urls']) ? 'slug' : 'term_id')),
            'operator'          => $operator,
            'remove_attribute'  => $single,
        )).'"';

        if (
            ! empty( $option['use_nofollow'] ) and
            (
                $option['use_nofollow'] == 1 or
                $option['use_nofollow'] == 2 and
                apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'check_radio_color_filter_term_text' )
            )
        ) {
            $new_text .= ' rel="nofollow"';
        }

        $new_text .= '>'.$text.'</a>';

        if ( $noindex ) {
            $new_text .= '</noindex>';
        }

        return $new_text;
    }
    //CANONICAL URL
    function wp_head_canonical() {
        if ( $this->is_canonical_applied() ) {
            $current_page_url = $this->get_current_canonical_url();
            echo '<link rel="canonical" href="' . $current_page_url . '">';
        }
    }
    function yoast_canonical($canonical) {
        remove_action('wp_head', array($this, 'wp_head_canonical'), 99999);
        if ( $this->is_canonical_applied() ) {
            $canonical = $this->get_current_canonical_url();
        }
        return $canonical;
    }
    function is_canonical_applied() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();
        $show_canonical   = ( is_post_type_archive( 'product' ) || is_shop() || is_product_taxonomy() );
        $show_canonical   = $show_canonical && br_get_value_from_array($br_options, 'canonicalization') != '2';
        return apply_filters( 'berocket_wp_head_canonical', $show_canonical, ! empty( $br_options['nice_urls'] ), br_get_value_from_array($br_options, 'canonicalization') );
    }
    function get_current_canonical_url() {
        global $wp, $sitepress, $wp_rewrite;
        $permalink_structure = get_option( 'permalink_structure' );
        $option_permalink = $this->get_permalinks_oprions();
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options = $BeRocket_AAPF->get_option();
        $current_page_url = preg_replace( "~paged?/[0-9]+/?~", "", home_url( $wp->request ) );
        if( ! empty($br_options['nice_urls']) ) {
            $current_page_url = preg_replace( "~".$option_permalink['variable']."/.+~", "", $current_page_url );
            $current_page_url = preg_replace( "~".urlencode($option_permalink['variable'])."/.+~", "", $current_page_url );
        }
        if( strpos($current_page_url, '?') !== FALSE ) {
            $current_page_url = explode('?', $current_page_url);
            $current_page_url = $current_page_url[0];
        }
        if( substr($permalink_structure, -1) == '/' ) {
            $current_page_url = trailingslashit($current_page_url);
        }
        if( empty($br_options['canonicalization']) ) {
            $current_page_url = $this->add_sorted_filters($current_page_url);
        }

        return apply_filters( 'berocket_wp_head_canonical_page_url', $current_page_url );
    }
    function add_sorted_filters($link) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        global $wp_query, $wp_rewrite;
        $paged = get_query_var('paged');
        if( br_is_filtered(true, true, true, false) ) {
            if( ! empty($_POST['terms']) && is_array($_POST['terms']) ) {
                $term_sort = array();
                $term_operator = array();
                foreach($_POST['terms'] as $term) {
                    if( ! array_key_exists($term[0], $term_sort) ) {
                        $term_sort[$term[0]] = array();
                        $term_operator[$term[0]] = $term[2];
                    }
                    $term_sort[$term[0]][$term[3]] = (empty($option['slug_urls']) ? $term[1] : $term[3]);
                }
                ksort($term_sort);
                
                foreach($term_sort as $term_attr => $term_sort_el) {
                    ksort($term_sort_el);
                    $link = apply_filters('berocket_add_filter_to_link', $link, array(
                        'attribute'         => $term_attr,
                        'values'            => $term_sort_el,
                        'operator'          => $term_operator[$term_attr],
                    ));
                }
            }
            if( ! empty($_POST['limits']) && is_array($_POST['limits']) ) {
                $term_sort = array();
                foreach($_POST['limits'] as $limit) {
                    $term_sort[$limit[0]] = $limit;
                }
                ksort($term_sort);
                foreach($term_sort as $term_attr => $term_sort_el) {
                    if( $term_attr == '_date' ) {
                        $term_sort_el[1] = str_replace('/', '', $term_sort_el[1]);
                        $term_sort_el[2] = str_replace('/', '', $term_sort_el[2]);
                    }
                    $link = apply_filters('berocket_add_filter_to_link', $link, array(
                        'attribute'         => $term_attr,
                        'values'            => array($term_sort_el[1], $term_sort_el[2]),
                        'slider'            => TRUE
                    ));
                }
            }
            if( ! empty($_POST['price']) && is_array($_POST['price']) ) {
                $link = apply_filters('berocket_add_filter_to_link', $link, array(
                    'attribute'         => 'price',
                    'values'            => array($_POST['price'][0], $_POST['price'][1]),
                    'slider'            => TRUE
                ));
            }
        }
        if( ! empty($_POST['add_terms']) && is_array($_POST['add_terms']) ) {
            $term_sort = array();
            $term_operator = array();
            foreach($_POST['add_terms'] as $term) {
                if( ! array_key_exists($term[0], $term_sort) ) {
                    $term_sort[$term[0]] = array();
                    $term_operator[$term[0]] = $term[2];
                }
                $term_sort[$term[0]][$term[3]] = (empty($option['slug_urls']) ? $term[1] : $term[3]);
            }
            ksort($term_sort);
            
            foreach($term_sort as $term_attr => $term_sort_el) {
                ksort($term_sort_el);
                $link = apply_filters('berocket_add_filter_to_link', $link, array(
                    'attribute'         => $term_attr,
                    'values'            => $term_sort_el,
                    'operator'          => $term_operator[$term_attr],
                ));
            }
        }
        if( ! empty($_POST['price_ranges']) && is_array($_POST['price_ranges']) ) {
            $price_ranges = $_POST['price_ranges'];
            sort($price_ranges);
            $link = apply_filters('berocket_add_filter_to_link', $link, array(
                'attribute'         => 'price',
                'values'            => $price_ranges
            ));
        }
        if( intval($paged) > 1 ) {
            if ( $wp_rewrite->using_permalinks() ) {
                $link_array = explode('?', $link);
                $link_array[0] = trailingslashit( $link_array[0] );
                $link_array[0] .= trailingslashit( $wp_rewrite->pagination_base );
                $link_array[0] = user_trailingslashit( $link_array[0] . $paged );
                $link = implode('?', $link_array);
            } else {
                $link = add_query_arg( 'page', $paged, user_trailingslashit( $link ) );
            }
        }
        return $link;
    }
    //GROUP SETTINGS
    function group_add() {
        add_action( 'berocket_aapf_filters_group_settings', array($this, 'group_settings'), 10, 3 );
        add_filter( 'berocket_aapf_group_before_all', array($this, 'search_box_before_group_start'), 10, 2 );
        add_filter( 'berocket_aapf_group_after_all', array($this, 'search_box_after_group_end'), 10, 2 );
        add_filter( 'berocket_aapf_group_before_filter', array($this, 'search_box_before_group_filter'), 10, 2 );
        add_filter( 'berocket_aapf_group_after_filter', array($this, 'search_box_after_group_filter'), 10, 2 );
        add_filter( 'berocket_aapf_group_new_args', array($this, 'group_new_args'), 10, 2 );
        add_filter( 'berocket_aapf_group_new_args_filter', array($this, 'group_new_args_filter'), 10, 3 );
        add_filter( 'wp_footer', array($this, 'search_box_loaded_footer') );
    }
    function group_settings($filters, $post_name, $post) {
        include AAPF_TEMPLATE_PATH . "paid/filters_group.php";
    }
    //GROUP SEARCH BOX
    function search_box_before_group_start($custom_vars, $filters) {
        if( ! empty($filters['search_box']) ) {
            $search_box_main_class = array('berocket_search_box_block');
            if( ! empty($filters['hide_group']['mobile']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_mobile';
            }
            if( ! empty($filters['hide_group']['tablet']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_tablet';
            }
            if( ! empty($filters['hide_group']['desktop']) ) {
                $search_box_main_class[] = 'bapf_sngl_hd_desktop';
            }
            if( ! empty($filters['search_box_recount']) ) {
                $search_box_main_class[] = 'bapf_sbrecount';
            }
            $search_box_link_type = br_get_value_from_array($filters, 'search_box_link_type');
            $search_box_url = br_get_value_from_array($filters, 'search_box_url');
            $search_box_style = br_get_value_from_array($filters, 'search_box_style');
            $search_box_category = br_get_value_from_array($filters, 'search_box_category');
            if( $search_box_link_type == 'shop_page' ) {
                if( function_exists('wc_get_page_id') ) {
                    $search_box_url = get_permalink( wc_get_page_id( 'shop' ) );
                } else {
                    $search_box_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
                }
            } elseif( $search_box_link_type == 'category' ) {
                $search_box_url = get_term_link( $search_box_category, 'product_cat' );
            }
            $sb_style = '';
            if ( $search_box_style['position'] == 'horizontal' ) {
                $sb_count = count($filters['filters']);
                if( $search_box_style['search_position'] == 'before_after' ) {
                    $sb_count += 2;
                } else {
                    $sb_count++;
                }
                $search_box_width = (int)(100 / $sb_count);
                $sb_style .= 'width:'.$search_box_width.'%;display:inline-block;padding: 4px;';
            }
            $search_box_button_class = 'search_box_button_class_'.rand();
            $sbb_style = '';
            if( ! empty($search_box_style['background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['background'][0] == '#' ? $search_box_style['background'] : '#'.$search_box_style['background']).';';
            }
            $sbb_style .= 'opacity:'.$search_box_style['back_opacity'].';';
            if( ! empty($title) ) { ?><h3 class="widget-title berocket_aapf_widget-title" style="<?php echo ( empty($uo['style']['title']) ? '' : $uo['style']['title'] ) ?>"><span><?php echo $title; ?></span></h3><?php }
            echo '<div data-url="'.$search_box_url.'" class="'.implode(' ', $search_box_main_class).'"'.(empty($filters['search_box_recount']) ? '' : ' data-id="'.$filters['group_id'].'"').'>';
            echo '<div class="berocket_search_box_background" style="'.$sbb_style.'"></div>';
            echo '<div class="berocket_search_box_background_all">';
            $custom_vars['sb_style'] = $sb_style;
            $custom_vars['search_box_button_class'] = $search_box_button_class;
            $custom_vars['search_box_link_type'] = $search_box_link_type;
            $custom_vars['search_box_url'] = $search_box_url;
            $custom_vars['search_box_style'] = $search_box_style;
            $custom_vars['search_box_category'] = $search_box_category;
        }
        return $custom_vars;
    }
    function search_box_after_group_end($custom_vars, $filters) {
        extract($custom_vars);
        if( ! empty($filters['search_box']) ) {
            echo '</div></div>';
        }
        return $custom_vars;
    }
    function search_box_before_group_filter($custom_vars, $filters) {
        extract($custom_vars);
        if( ! empty($filters['search_box']) ) {
            echo '<div style="'.$sb_style.'">';
        }
        return $custom_vars;
    }
    function search_box_after_group_filter($custom_vars, $filters) {
        if( ! empty($filters['search_box']) ) {
            echo '</div>';
        }
        return $custom_vars;
    }
    function search_box_loaded_footer() {
        if( ! empty($_GET['bapf_gid']) ) {
            $group_id = intval($_GET['bapf_gid']);
            add_filter('braapf_check_widget_by_instance_group', array($this, 'search_box_loaded_footer_condition'), 10, 1);
            echo do_shortcode('[br_filters_group group_id='.$group_id.']');
            remove_filter('braapf_check_widget_by_instance_group', array($this, 'search_box_loaded_footer_condition'), 10, 1);
        }
    }
    function search_box_loaded_footer_condition() {
        return false;
    }
    //GROUP INLINE
    function group_new_args($new_args, $filters) {
        $title_class = array();
        $additional_class = array();
        if( ! empty($filters['hide_group']['mobile']) ) {
            $additional_class[] = 'bapf_sngl_hd_mobile';
            $title_class[] = 'bapf_sngl_hd_mobile';
        }
        if( ! empty($filters['hide_group']['tablet']) ) {
            $additional_class[] = 'bapf_sngl_hd_tablet';
            $title_class[] = 'bapf_sngl_hd_tablet';
        }
        if( ! empty($filters['hide_group']['desktop']) ) {
            $additional_class[] = 'bapf_sngl_hd_desktop';
            $title_class[] = 'bapf_sngl_hd_desktop';
        }
        $style = '';
        if( ! empty($filters['hidden_clickable']) ) {
            $additional_class[] = 'berocket_hidden_clickable';
            if( ! empty($filters['display_inline']) ) {
                $additional_class[] = 'berocket_inline_clickable';
            }
            if( ! empty($filters['hidden_clickable_hover']) ) {
                $additional_class[] = 'berocket_inline_clickable_hover';
            }
            $new_args['filter_data'] = array(
                'widget_is_hide' => 1,
                'widget_collapse' => 'with_arrow',
                'additional_data_options' => array(
                    'widget_is_hide_on_load' => true,
                ),
                'widget_collapse_enable' => 1 //DEPRECATED
            );
        }
        if( ! empty($filters['display_inline']) && ( empty($filters['hidden_clickable']) || ! empty($filters['display_inline_count']) ) ) {
            $additional_class[] = 'berocket_inline_filters';
            if( ! empty($filters['display_inline_count']) ) {
                $additional_class[] = 'berocket_inline_filters_count_'.$filters['display_inline_count'];
            }
            $style .= 'opacity:0!important;';
        }
        if( empty($new_args['inline_style']) ) {
            $new_args['inline_style'] = '';
        }
        $new_args['inline_style'] .= $style;
        if( empty($new_args['additional_class']) || ! is_array($new_args['additional_class']) ) {
            $new_args['additional_class'] = array();
        }
        if( ! empty($filters['min_filter_width_inline']) ) {
            $min_filter_width_inline = max(25, intval($filters['min_filter_width_inline']));
            $new_args['additional_data_inline'] = br_get_value_from_array($new_args, 'additional_data_inline') . ' data-min_filter_width_inline='.$min_filter_width_inline.'';
        }
        $new_args['additional_class'] = array_merge($new_args['additional_class'], $additional_class);
        $new_args['title_class'] = $title_class;
        return $new_args;
    }
    function group_new_args_filter($new_args, $filters, $filter) {
        if( ! empty($filters['hidden_clickable']) && $widget_inline_width = br_get_value_from_array($filters, array('filters_data', $filter, 'width')) ) {
            if(strpos($widget_inline_width, 'px') === FALSE
            && strpos($widget_inline_width, '%') === FALSE
            && strpos($widget_inline_width, 'em') === FALSE ) {
                $widget_inline_width = $widget_inline_width.'px';
            }
            $new_args['widget_inline_style'] = "width:{$widget_inline_width}!important;";
        } else {
            $new_args['widget_inline_style'] = "";
        }
        return $new_args;
    }
    //Show products count before filtering
    function listener_product_count(){
        global $wp_query, $wp_rewrite;
        $br_options = BeRocket_AAPF::get_aapf_option();

        $wp_query = BeRocket_AAPF_Widget_functions::listener_wp_query();

        $product_count = $wp_query->found_posts;
        
        echo json_encode( array( 'product_count' => $product_count ) );

        die();
    }
    //CACHE OPTIONS
    function br_get_cache( $return, $key, $group ){
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $cache_type = $option['object_cache'];
        $language = br_get_current_language_code();
        $group = $group.$language;
        if ( $cache_type == 'wordpress' ) {
            $return = get_site_transient( md5($group.$key) );
        } elseif ( $cache_type == 'persistent' ) {
            $return = wp_cache_get( $key, $group );
        }
        return $return;
    }
    function br_set_cache( $return, $key, $value, $group, $expire ){
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        $cache_type = $option['object_cache'];
        $language = br_get_current_language_code();
        $group = $group.$language;
        if ( $cache_type == 'wordpress' ) {
            set_site_transient( md5($group.$key), $value, $expire );
        } elseif ( $cache_type == 'persistent' ) {
            wp_cache_add( $key, $value, $group, $expire );
        }
        return $return;
    }
    //FILTER
    function filter_add() {
        add_filter( 'berocket_filter_filter_type_array', array($this, 'filter_filter_type_array') );
        add_filter( 'berocket_admin_filter_types_by_attr', array($this, 'admin_filter_types_by_attr'), 10, 2 );
        add_filter( 'berocket_widget_widget_type_array', array($this, 'widget_widget_type_array') );
        add_filter( 'berocket_custom_post_br_product_filter_default_settings', array($this, 'single_filter_default_settings') );
        add_filter( 'berocket_widget_advanced_settings_elements', array($this, 'widget_advanced_settings_elements'), 10, 3 );
        add_filter( 'berocket_widget_attribute_type_terms', array($this, 'widget_attribute_type_terms'), 10, 4 );
        add_filter( 'berocket_widget_load_template_name', array($this, 'widget_load_template_name'), 10, 1 );
        add_filter( 'berocket_aapf_widget_display_custom_filter', array($this, 'widget_display_custom_filter'), 10, 5 );
        add_filter( 'berocket_radio_filter_term_name', array($this, 'filter_term_name'), 10, 2 );
        add_filter( 'berocket_select_filter_term_name', array($this, 'select_filter_term_name'), 10, 2 );
        add_filter( 'berocket_widget_color_image_temp_meta_class_init', array($this, 'temp_meta_class_init'), 10, 2 );
        add_filter( 'berocket_widget_color_image_temp_meta_ready', array($this, 'temp_meta_class_ready'), 10, 3 );
        add_filter( 'berocket_widget_color_image_temp_span_class', array($this, 'temp_span_class'), 10, 3 );
        add_filter( 'berocket_widget_aapf_start_temp_class', array($this, 'start_temp_class'), 10, 1 );
        //INCLUDE/EXCLUDE LIST
        add_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'hook_include_exclude_items'), 10, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'hook_include_exclude_items_args'), 10, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'child_parent_newterms'), 5, 2 );
        add_filter( 'berocket_aapf_get_terms_args', array($this, 'display_child_of'), 7, 2 );
        add_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'child_parent_newterms_exclude'), 5, 5 );

        add_action( 'berocket_widget_filter_post_end', array($this, 'widget_filter_post_end'), 10, 2 );
        add_action( 'berocket_widget_filter_advanced_settings_end', array($this, 'widget_filter_advanced_settings_end'), 10, 2 );
        add_action( 'berocket_widget_filter_output_limitation_end', array($this, 'widget_filter_output_limitation_end'), 10, 2 );
    }
    function filter_filter_type_array($filter_type) {
        $filter_type['price']['templates'][] = 'checkbox';
        $filter_type['price']['templates'][] = 'select';
        $filter_type['price']['positions'][] = '30000';
        $filter_type['price']['positions'][] = '40000';
        
        $filter_type['attribute']['templates'][] = 'new_slider';
        $filter_type['attribute']['templates'][] = 'slider';
        $filter_type['attribute']['templates'][] = 'datepicker';
        $filter_type['attribute']['positions'][] = '30000';
        $filter_type['attribute']['positions'][] = '40000';
        $filter_type['attribute']['positions'][] = '50000';
        
        $filter_type['tag']['templates'][] = 'new_slider';
        $filter_type['tag']['templates'][] = 'slider';
        $filter_type['tag']['templates'][] = 'datepicker';
        $filter_type['tag']['positions'][] = '30000';
        $filter_type['tag']['positions'][] = '40000';
        $filter_type['tag']['positions'][] = '50000';
        
        $filter_type['all_product_cat']['templates'][] = 'new_slider';
        $filter_type['all_product_cat']['templates'][] = 'slider';
        $filter_type['all_product_cat']['templates'][] = 'datepicker';
        $filter_type['all_product_cat']['positions'][] = '30000';
        $filter_type['all_product_cat']['positions'][] = '40000';
        $filter_type['all_product_cat']['positions'][] = '50000';
        if ( function_exists('wc_get_product_visibility_term_ids') ) {
            $filter_type['_rating']['templates'][] = 'new_slider';
            $filter_type['_rating']['templates'][] = 'slider';
            $filter_type['_rating']['positions'][] = '30000';
            $filter_type['_rating']['positions'][] = '40000';
        }
        $filter_type = berocket_insert_to_array(
            $filter_type,
            'tag',
            array(
                'custom_taxonomy' => array(
                    'name' => __('Custom Taxonomy', 'BeRocket_AJAX_domain'),
                    'sameas' => 'custom_taxonomy',
                    'templates' => array('checkbox', 'slider', 'new_slider', 'select', 'datepicker'),
                    'specific'  => array('', 'color', 'image')
                ),
                '_stock_status' => array(
                    'name' => __('Stock status', 'BeRocket_AJAX_domain'),
                    'sameas' => '_stock_status',
                    'templates' => array('checkbox', 'select'),
                    'specific'  => array('')
                ),
                'date' => array(
                    'name' => __('Date', 'BeRocket_AJAX_domain'),
                    'sameas' => 'date',
                    'templates' => array('datepicker'),
                    'specific'  => array('')
                ),
                '_sale' => array(
                    'name' => __('Sale', 'BeRocket_AJAX_domain'),
                    'sameas' => '_sale',
                    'templates' => array('checkbox', 'select'),
                    'specific'  => array('')
                ),
            )
        );
        return $filter_type;
    }
    function admin_filter_types_by_attr($vars, $type = 'main') {
        list($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr) = $vars;
        if( $type != 'simple' ) {
            $berocket_admin_filter_types_by_attr['ranges'] = array('value' => 'ranges', 'text' => __('Ranges', 'BeRocket_AJAX_domain'));
            $berocket_admin_filter_types['price'][] = "ranges";
        }
        $berocket_admin_filter_types['custom_taxonomy'][] = "slider";
        $berocket_admin_filter_types['attribute'][] = "slider";
        $berocket_admin_filter_types['filter_by'][] = "slider";
        return array($berocket_admin_filter_types, $berocket_admin_filter_types_by_attr);
    }
    function widget_widget_type_array($widget_types) {
        $widget_types['search_box'] = __('Search Box (DEPRECATED)', 'BeRocket_AJAX_domain');
        return $widget_types;
    }
    function single_filter_default_settings($default_settings) {
        $default_settings = array_merge(
            $default_settings,
            array(
                'child_parent'                  => '',
                'child_parent_depth'            => '1',
                'child_parent_no_values'        => '',
                'child_parent_previous'         => '',
                'child_parent_no_products'      => '',
                'child_onew_count'              => '1',
                'child_onew_childs'             => array(
                    1                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    2                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    3                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    4                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    5                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    6                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    7                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    8                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    9                               => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                    10                              => array('title' => '', 'no_product' => '', 'no_values' => '', 'previous' => ''),
                ),
                'search_box_link_type'          => 'shop_page',
                'search_box_url'                => '',
                'search_box_category'           => '',
                'search_box_count'              => '1',
                'search_box_attributes'             => array(
                    1                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    2                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    3                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    4                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    5                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    6                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    7                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    8                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    9                               => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                    10                              => array('type' => 'attribute', 'attribute' => '', 'custom_taxonomy' => '', 'title' => '', 'visual_type' => 'select'),
                ),
                'search_box_style'              => array(
                    'position'                      => 'vertical',
                    'search_position'               => 'after',
                    'search_text'                   => 'Search',
                    'background'                    => 'bbbbff',
                    'back_opacity'                  => '0',
                    'button_background'             => '888800',
                    'button_background_over'        => 'aaaa00',
                    'text_color'                    => '000000',
                    'text_color_over'               => '000000',
                ),
                'ranges'                        => array( 1, 10 ),
                'hide_first_last_ranges'        => '',
                'include_exclude_select'        => '',
                'include_exclude_list'          => array(),
            )
        );
        
        return $default_settings;
    }
    function widget_filter_post_end($post_name, $instance) {
        $attributes        = br_aapf_get_attributes();
        $categories        = BeRocket_AAPF_Widget_functions::get_product_categories( @ json_decode( $instance['product_cat'] ) );
        $categories        = BeRocket_AAPF_Widget_functions::set_terms_on_same_level( $categories );
        $tags              = get_terms( 'product_tag' );
        $custom_taxonomies = get_object_taxonomies( 'product' );
        $custom_taxonomies = array_combine($custom_taxonomies, $custom_taxonomies);
        ?>
<div class="berocket_aapf_admin_search_box"<?php if( $instance['widget_type'] != 'search_box' ) echo ' style="display:none;"'; ?>>
    <div class="br_accordion">
        <h3><?php _e('Attributes', 'BeRocket_AJAX_domain') ?></h3>
        <div>
            <div>
                <label><?php _e('URL to search', 'BeRocket_AJAX_domain') ?></label>
                <select name="<?php echo $post_name.'[search_box_link_type]'; ?>" class="berocket_search_link_select br_select_menu_left">
                    <option value="shop_page"<?php if ($instance['search_box_link_type'] == 'shop_page' ) echo ' selected'; ?>><?php _e('Shop page', 'BeRocket_AJAX_domain') ?></option>
                    <option value="category"<?php if ($instance['search_box_link_type'] == 'category' ) echo ' selected'; ?>><?php _e('Category page', 'BeRocket_AJAX_domain') ?></option>
                    <option value="url"<?php if ($instance['search_box_link_type'] == 'url' ) echo ' selected'; ?>><?php _e('URL', 'BeRocket_AJAX_domain') ?></option>
                </select>
            </div>
            <div class="berocket_search_link berocket_search_link_category"<?php if( $instance['search_box_link_type'] != 'category' ) echo ' style="display:none;"'; ?>>
                <label><?php _e('Category', 'BeRocket_AJAX_domain') ?></label>
                <select class="br_select_menu_left" name="<?php echo $post_name.'[search_box_category]'; ?>">
                <?php 
                $instance['search_box_category'] = ( empty($instance['search_box_category']) ? '' : urldecode($instance['search_box_category']) );
                foreach( $categories as $category ){
                    echo '<option value="'.$category->slug.'"'.($instance['search_box_category'] == $category->slug ? ' selected' : '').'>'.$category->name.'</option>';
                } ?>
                </select>
            </div>
            <div class="berocket_search_link berocket_search_link_url"<?php if( $instance['search_box_link_type'] != 'url' ) echo ' style="display:none;"'; ?>>
                <label><?php _e('URL for search', 'BeRocket_AJAX_domain') ?></label>
                <input class="br_admin_full_size" id="<?php echo 'search_box_url'; ?>" name="<?php echo $post_name.'[search_box_url]'; ?>" type="text" value="<?php echo $instance['search_box_url']; ?>">
            </div>
            <div>
                <label><?php _e('Attributes count', 'BeRocket_AJAX_domain') ?></label>
                <select id="<?php echo 'search_box_count'; ?>" name="<?php echo $post_name.'[search_box_count]'; ?>" class="br_search_box_count br_select_menu_left">
                    <?php 
                    for ( $i = 1; $i < 11; $i++ ) {
                        echo '<option value="'.$i.'"'.($instance['search_box_count'] == $i ? ' selected' : '').'>'.$i.'</option>';
                    }
                    ?>
                </select>
            </div>
            <?php for( $i = 1; $i < 11; $i++ ) {
                echo '<div class="berocket_search_box_attribute_'.$i.'"'.($instance['search_box_count'] >= $i ? '' : ' style="display:none;"').'>';
                ?>
                <div class="br_accordion">
                    <h3><?php _e('Attribute', 'BeRocket_AJAX_domain') ?> <?php echo $i; ?></h3>
                    <div class="br_search_box_attribute_block">
                        <div>
                            <label class="br_admin_center" for="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>_title"><?php _e('Title', 'BeRocket_AJAX_domain') ?> </label>
                            <input class="br_admin_full_size" id="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>_title" type="text" name="<?php echo $post_name.'[search_box_attributes]'; ?>[<?php echo $i; ?>][title]" value="<?php echo $instance['search_box_attributes'][$i]['title']; ?>"/>
                        </div>
                        <div>
                            <label class="br_admin_center"><?php _e('Filter By', 'BeRocket_AJAX_domain') ?></label>
                            <select id="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>" name="<?php echo $post_name.'[search_box_attributes]'; ?>[<?php echo $i; ?>][type]" class="br_search_box_attribute_type br_select_menu_left">
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'type')) == 'attribute' ) echo 'selected'; ?> value="attribute"><?php _e('Attribute', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'type')) == 'tag' ) echo 'selected'; ?> value="tag"><?php _e('Tag', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'type')) == 'custom_taxonomy' ) echo 'selected'; ?> value="custom_taxonomy"><?php _e('Custom Taxonomy', 'BeRocket_AJAX_domain') ?></option>
                            </select>
                        </div>
                        <div class="br_search_box_attribute_attribute_block" <?php if ( $instance['search_box_attributes'][$i]['type'] and $instance['search_box_attributes'][$i]['type'] != 'attribute') echo 'style="display: none;"'; ?>>
                            <label class="br_admin_center"><?php _e('Attribute', 'BeRocket_AJAX_domain') ?></label>
                            <select id="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>_attribute" name="<?php echo $post_name.'[search_box_attributes]'; ?>[<?php echo $i; ?>][attribute]" class="br_search_box_attribute_attribute br_select_menu_right">
                                <?php foreach ( $attributes as $k => $v ) { ?>
                                    <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'attribute')) == $k ) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="br_search_box_attribute_custom_taxonomy_block" <?php if ( $instance['search_box_attributes'][$i]['type'] != 'custom_taxonomy') echo 'style="display: none;"'; ?>>
                            <label class="br_admin_center"><?php _e('Custom Taxonomies', 'BeRocket_AJAX_domain') ?></label>
                            <select id="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>_custom" name="<?php echo $post_name.'[search_box_attributes]'; ?>[<?php echo $i; ?>][custom_taxonomy]" class="br_search_box_attribute_custom_taxonomy br_select_menu_right">
                                <?php foreach( $custom_taxonomies as $k => $v ){ ?>
                                    <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'custom_taxonomy')) == $k ) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="br_clearfix"></div>
                        <div>
                            <label class="br_admin_center"><?php _e('Type', 'BeRocket_AJAX_domain') ?></label>
                            <select id="<?php echo 'search_box_attributes'; ?>_<?php echo $i; ?>_visual_type" name="<?php echo $post_name.'[search_box_attributes]'; ?>[<?php echo $i; ?>][visual_type]" class="br_select_menu_left">
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'visual_type')) == 'select' ) echo 'selected'; ?> value="select"><?php _e('Select', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'visual_type')) == 'checkbox' ) echo 'selected'; ?> value="checkbox"><?php _e('Checkbox', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'visual_type')) == 'radio' ) echo 'selected'; ?> value="radio"><?php _e('Radio', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'visual_type')) == 'color' ) echo 'selected'; ?> value="color"><?php _e('Color', 'BeRocket_AJAX_domain') ?></option>
                                <option <?php if ( br_get_value_from_array($instance, array('search_box_attributes', $i, 'visual_type')) == 'image' ) echo 'selected'; ?> value="image"><?php _e('Image', 'BeRocket_AJAX_domain') ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <?php
                echo '</div>';
            } ?>
            <div class="br_clearfix"></div>
        </div>
    </div>
    <div class="br_accordion">
        <h3><?php _e('Styles', 'BeRocket_AJAX_domain') ?></h3>
        <div>
            <div>
                <label><?php _e('Elements position', 'BeRocket_AJAX_domain') ?></label>
                <select class="br_select_menu_left" name="<?php echo $post_name.'[search_box_style]'; ?>[position]">
                    <option value="vertical"<?php if( br_get_value_from_array($instance, array('search_box_style', 'position')) == 'vertical' ) echo ' selected'; ?>><?php _e('Vertical', 'BeRocket_AJAX_domain') ?></option>
                    <option value="horizontal"<?php if( br_get_value_from_array($instance, array('search_box_style', 'position')) == 'horizontal' ) echo ' selected'; ?>><?php _e('Horizontal', 'BeRocket_AJAX_domain') ?></option>
                </select>
            </div>
            <div>
                <label><?php _e('Search button position', 'BeRocket_AJAX_domain') ?></label>
                <select class="br_select_menu_left" name="<?php echo $post_name.'[search_box_style]'; ?>[search_position]">
                    <option value="before"<?php if( br_get_value_from_array($instance, array('search_box_style', 'search_position')) == 'before' ) echo ' selected'; ?>><?php _e('Before', 'BeRocket_AJAX_domain') ?></option>
                    <option value="after"<?php if( br_get_value_from_array($instance, array('search_box_style', 'search_position')) == 'after' ) echo ' selected'; ?>><?php _e('After', 'BeRocket_AJAX_domain') ?></option>
                    <option value="before_after"<?php if( br_get_value_from_array($instance, array('search_box_style', 'search_position')) == 'before_after' ) echo ' selected'; ?>><?php _e('Before and after', 'BeRocket_AJAX_domain') ?></option>
                </select>
            </div>
            <div>
                <label><?php _e('Search button text', 'BeRocket_AJAX_domain') ?></label>
                <input type="text" class="br_admin_full_size" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'search_text')); ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[search_text]">
            </div>
            <div>
                <label><?php _e('Background color', 'BeRocket_AJAX_domain') ?></label>
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($instance, array('search_box_style', 'background'), '000000'); ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'background')) ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[background]">
            </div>
            <div>
                <label><?php _e('Background transparency', 'BeRocket_AJAX_domain') ?></label>
                <select class="br_select_menu_left" name="<?php echo $post_name.'[search_box_style]'; ?>[back_opacity]">
                    <?php
                    $back_opacity = array(
                        '0'     => __('100%', 'BeRocket_AJAX_domain'),
                        '0.1'   => __('90%', 'BeRocket_AJAX_domain'),
                        '0.2'   => __('80%', 'BeRocket_AJAX_domain'),
                        '0.3'   => __('70%', 'BeRocket_AJAX_domain'),
                        '0.4'   => __('60%', 'BeRocket_AJAX_domain'),
                        '0.5'   => __('50%', 'BeRocket_AJAX_domain'),
                        '0.6'   => __('40%', 'BeRocket_AJAX_domain'),
                        '0.7'   => __('30%', 'BeRocket_AJAX_domain'),
                        '0.8'   => __('20%', 'BeRocket_AJAX_domain'),
                        '0.9'   => __('10%', 'BeRocket_AJAX_domain'),
                        '1'     => __('0%', 'BeRocket_AJAX_domain'),
                    );
                    foreach($back_opacity as $key => $value) {
                        echo '<option value="', $key, '"', 
                        ( (br_get_value_from_array($instance, array('search_box_style', 'back_opacity')) == $key) ? ' selected' : '' ),
                        '>', $value, '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label><?php _e('Button background color', 'BeRocket_AJAX_domain') ?></label>
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($instance, array('search_box_style', 'button_background'), '000000'); ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'button_background')) ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[button_background]">
            </div>
            <div>
                <label><?php _e('Button background color on mouse over', 'BeRocket_AJAX_domain') ?></label>
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($instance, array('search_box_style', 'button_background_over'), '000000'); ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'button_background_over')) ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[button_background_over]">
            </div>
            <div>
                <label><?php _e('Button text color', 'BeRocket_AJAX_domain') ?></label>
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($instance, array('search_box_style', 'text_color'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'text_color')) ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[text_color]">
            </div>
            <div>
                <label><?php _e('Button text color on mouse over', 'BeRocket_AJAX_domain') ?></label>
                <div class="br_colorpicker_field" data-color="<?php echo br_get_value_from_array($instance, array('search_box_style', 'text_color_over'), '000000') ?>"></div>
                <input type="hidden" value="<?php echo br_get_value_from_array($instance, array('search_box_style', 'text_color_over')) ?>" name="<?php echo $post_name.'[search_box_style]'; ?>[text_color_over]">
            </div>
        </div>
    </div>
</div>
        <?php
    }
    function widget_filter_advanced_settings_end($post_name, $instance) {
        ?>
            <div class="br_aapf_child_parent_selector" <?php if ( $instance['filter_type'] == 'attribute' and $instance['attribute'] == 'price'  or $instance['filter_type'] == 'product_cat' or $instance['filter_type'] == '_stock_status' or $instance['filter_type'] == 'tag' or $instance['type'] == 'slider' or $instance['filter_type'] == 'date' or $instance['filter_type'] == '_sale' or $instance['filter_type'] == '_rating' ) echo " style='display: none;'"; ?>>
                <div>
                    <label class="br_admin_center"><?php _e('Child/Parent Limitation', 'BeRocket_AJAX_domain') ?></label>
                    <select name="<?php echo $post_name.'[child_parent]'; ?>" class="br_select_menu_left berocket_aapf_widget_child_parent_select">
                        <option value="" <?php if ( ! $instance['child_parent'] ) echo 'selected' ?>><?php _e('Default', 'BeRocket_AJAX_domain') ?></option>
                        <option value="depth" <?php if ( $instance['child_parent'] == 'depth' ) echo 'selected' ?>><?php _e('Child Count', 'BeRocket_AJAX_domain') ?></option>
                        <option value="parent" <?php if ( $instance['child_parent'] == 'parent' ) echo 'selected' ?>><?php _e('Parent', 'BeRocket_AJAX_domain') ?></option>
                        <option value="child" <?php if ( $instance['child_parent'] == 'child' ) echo 'selected' ?>><?php _e('Child', 'BeRocket_AJAX_domain') ?></option>
                    </select>
                </div>
                <div class="berocket_aapf_widget_child_parent_depth_block" <?php if( $instance['child_parent'] != 'child' ) echo 'style="display: none;"'; ?>>
                    <label for="<?php echo 'child_parent_depth'; ?>" class="br_admin_full_size"><?php _e('Child depth', 'BeRocket_AJAX_domain') ?></label>
                    <input name="<?php echo $post_name.'[child_parent_depth]'; ?>" id="<?php echo 'child_parent_depth'; ?>" type="number" min="1" value="<?php echo $instance['child_parent_depth']; ?>">
                    <div>
                        <label><?php _e('"No values" messages', 'BeRocket_AJAX_domain') ?></label>
                        <input class="br_admin_full_size" name="<?php echo $post_name.'[child_parent_no_values]'; ?>" type="text" value="<?php echo $instance['child_parent_no_values']; ?>">
                    </div>
                    <div>
                        <label><?php _e('"Select previous" messages', 'BeRocket_AJAX_domain') ?></label>
                        <input class="br_admin_full_size" name="<?php echo $post_name.'[child_parent_previous]'; ?>" type="text" value="<?php echo $instance['child_parent_previous']; ?>">
                    </div>
                    <div>
                        <label><?php _e('"No Products" messages', 'BeRocket_AJAX_domain') ?></label>
                        <input class="br_admin_full_size" name="<?php echo $post_name.'[child_parent_no_products]'; ?>" type="text" value="<?php echo $instance['child_parent_no_products']; ?>">
                    </div>
                </div>
                <div class="berocket_aapf_widget_child_parent_one_widget" <?php if( $instance['child_parent'] != 'depth' ) echo 'style="display: none;"'; ?>>
                    <label for="<?php echo 'child_onew_count'; ?>" class="br_admin_full_size"><?php _e('Child count', 'BeRocket_AJAX_domain') ?></label>
                    <select class="br_onew_child_count_select br_select_menu_left" id="<?php echo 'child_onew_count'; ?>" name="<?php echo $post_name.'[child_onew_count]'; ?>">
                        <?php 
                        $instance['child_onew_count'] = (int)$instance['child_onew_count'];
                        if ( $instance['child_onew_count'] < 1 ) {
                            $instance['child_onew_count'] = 1;
                        } 
                        for($i = 1; $i < 11; $i++) {
                            echo '<option value="'.$i.'"'.($instance['child_onew_count'] == $i ? ' selected' : '').'>'.$i.'</option>';
                        }
                        ?>
                    </select>
                    <?php 
                    for($i = 1; $i < 11; $i++) {
                        ?>
                        <div class="child_onew_childs_settings child_onew_childs_<?php echo $i; ?>"<?php if($i > $instance['child_onew_count']) echo ' style="display:none;"'; ?>>
                            <h4 class="br_admin_full_size"><?php echo __('Child', 'BeRocket_AJAX_domain').' '.$i; ?></h4>
                            <div>
                                <label><?php _e('Title', 'BeRocket_AJAX_domain') ?></label>
                                <input class="br_admin_full_size" name="<?php echo $post_name.'[child_onew_childs]'.'['.$i.'][title]'; ?>" type="text" value="<?php echo $instance['child_onew_childs'][$i]['title']; ?>">
                            </div>
                            <div>
                                <label><?php _e('"No products" messages', 'BeRocket_AJAX_domain') ?></label>
                                <input class="br_admin_full_size" name="<?php echo $post_name.'[child_onew_childs]'.'['.$i.'][no_product]'; ?>" type="text" value="<?php echo $instance['child_onew_childs'][$i]['no_product']; ?>">
                            </div>
                            <div>
                                <label><?php _e('"No values" messages', 'BeRocket_AJAX_domain') ?></label>
                                <input class="br_admin_full_size" name="<?php echo $post_name.'[child_onew_childs]'.'['.$i.'][no_values]'; ?>" type="text" value="<?php echo $instance['child_onew_childs'][$i]['no_values']; ?>">
                            </div>
                            <div>
                                <label><?php _e('"Select previous" messages', 'BeRocket_AJAX_domain') ?></label>
                                <input class="br_admin_full_size" name="<?php echo $post_name.'[child_onew_childs]'.'['.$i.'][previous]'; ?>" type="text" value="<?php echo $instance['child_onew_childs'][$i]['previous']; ?>">
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="br_aapf_date_style_select" <?php if ( $instance['filter_type'] != 'date') echo " style='display: none;'"; ?>>
                <p>
                    <label>
                        <input type="checkbox" value="1" name="<?php echo $post_name.'[date_change_month]'; ?>"<?php echo (empty($instance['date_change_month']) ? '' : ' checked'); ?>>
                        <?php _e('Dates of the month Drop-down list', 'BeRocket_AJAX_domain') ?>
                    </label>
                </p>
                <p>
                    <label>
                        <input type="checkbox" value="1" name="<?php echo $post_name.'[date_change_year]'; ?>"<?php echo (empty($instance['date_change_year']) ? '' : ' checked'); ?>>
                        <?php _e('Dates of the year Drop-down list', 'BeRocket_AJAX_domain') ?>
                    </label>
                </p>
                <p>
                    <label><?php _e('Date visual style', 'BeRocket_AJAX_domain') ?></label>
                    <select name="<?php echo $post_name.'[date_style]'; ?>" class="br_select_menu_left berocket_aapf_widget_child_parent_select">
                        <?php
                        $date_styles = array(
                            'm/d/Y' => 'mm/dd/yyyy',
                            'd/m/Y' => 'dd/mm/yyyy',
                            'Y/m/d' => 'yyyy/mm/dd',
                            'Y/d/m' => 'yyyy/dd/mm',
                            'm-d-Y' => 'mm-dd-yyyy',
                            'd-m-Y' => 'dd-mm-yyyy',
                            'Y-m-d' => 'yyyy-mm-dd',
                            'Y-d-m' => 'yyyy-dd-mm',
                            'm.d.Y' => 'mm.dd.yyyy',
                            'd.m.Y' => 'dd.mm.yyyy',
                            'Y.m.d' => 'yyyy.mm.dd',
                            'Y.d.m' => 'yyyy.dd.mm',
                        );
                        foreach($date_styles as $date_style_val => $date_style) {
                            echo '<option value="'.$date_style_val.'"'.( br_get_value_from_array($instance,'date_style') == $date_style_val ? ' selected' : '' ).'>'.$date_style.'</option>';
                        }
                        ?>
                    </select>
                </p>
            </div>
        <?php
    }
    function widget_filter_output_limitation_end($post_name, $instance) {
        $taxonomy_name = false;
        if( $instance['filter_type'] == 'product_cat' ) {
            $taxonomy_name = 'product_cat';
        } elseif( $instance['filter_type'] == 'tag' ) {
            $taxonomy_name = 'product_tag';
        } elseif( $instance['filter_type'] == 'custom_taxonomy' ) {
            $taxonomy_name = $instance['custom_taxonomy'];
        } elseif( $instance['filter_type'] == 'attribute' && $instance['attribute'] != 'price' ) {
            $taxonomy_name = $instance['attribute'];
        }
        ?>
        <div class="include_exclude_select"<?php if( $taxonomy_name === false ) echo ' style="display: none;"' ?>>
            <select name="<?php echo $post_name.'[include_exclude_select]'; ?>">
                <option value=""><?php _e('Disabled', 'BeRocket_AJAX_domain') ?></option>
                <option value="include"<?php if( $instance['include_exclude_select'] == 'include' ) echo ' selected'; ?>><?php _e('Display only', 'BeRocket_AJAX_domain') ?></option>
                <option value="exclude"<?php if( $instance['include_exclude_select'] == 'exclude' ) echo ' selected'; ?>><?php _e('Remove', 'BeRocket_AJAX_domain') ?></option>
            </select>
            <label><?php _e('values selected in Include / Exclude List', 'BeRocket_AJAX_domain') ?></label>
        </div>
        <div class="include_exclude_list" data-name="<?php echo $post_name.'[include_exclude_list]'; ?>"<?php if( empty($instance['include_exclude_select']) ) echo ' style="display: none;"'; ?>>
            <?php
            if( $taxonomy_name !== false ) {
                $list = BeRocket_AAPF_Widget_functions::include_exclude_terms_list($taxonomy_name, $instance['include_exclude_list']);
                $list = str_replace('%field_name%', $post_name.'[include_exclude_list]', $list);
                echo $list;
            }
            ?>
        </div>
        <?php
    }
    function widget_advanced_settings_elements($advanced_settings_elements, $post_name, $instance) {
        $advanced_settings_elements = berocket_insert_to_array(
            $advanced_settings_elements,
            'attribute_count',
            array(
                'slider_numeric' => '
                    <div class="berocket_attributes_slider_data"'
                    .( ( ( $instance['filter_type'] != 'custom_taxonomy' and $instance['filter_type'] != 'attribute' ) or $instance['type'] != 'slider' or ( $instance['filter_type'] == 'attribute' && $instance['attribute'] == 'price' )) ? ' style="display:none;"' : '' ).'>
                        <input id="slider_numeric" type="checkbox" name="'.$post_name.'[slider_numeric]"'.( empty($instance['slider_numeric']) ? '' : ' checked').' value="1">
                        <label for="slider_numeric">'.__('Use as numeric', 'BeRocket_AJAX_domain').'</label>
                    </div>
                ',
            )
        );
        $advanced_settings_elements = berocket_insert_to_array(
            $advanced_settings_elements,
            'widget_is_hide',
            array(
                'show_product_count_per_attr' =>'
                    <div class="berocket_aapf_widget_admin_non_price_tag_cloud"'
                    .( ( $instance['filter_type'] == 'date' || ( $instance['filter_type'] != 'date' && ( $instance['type'] == 'tag_cloud' || $instance['type'] == 'slider' ) ) ) ? ' style="display:none;"' : '' ).'>
                        <input id="show_product_count_per_attr" type="checkbox" name="'.$post_name.'[show_product_count_per_attr]"'.( empty($instance['show_product_count_per_attr']) ? '' : ' checked' ).' value="1" />
                        <label for="show_product_count_per_attr">'.__('Show products count per attribute value?', 'BeRocket_AJAX_domain').'</label>
                    </div>
                ',
            )
        );
        $advanced_settings_elements = berocket_insert_to_array(
            $advanced_settings_elements,
            'hide_child_attributes',
            array(
                'values_per_row' => '
                    <div class="br_admin_full_size"'.( ( ( ! $instance['filter_type'] or $instance['filter_type'] == 'attribute' ) and $instance['attribute'] == 'price' or $instance['filter_type'] == 'product_cat' or $instance['type'] == 'slider' or $instance['type'] == 'select' or $instance['type'] == 'tag_cloud' or ( $instance['filter_type'] == 'custom_taxonomy' and $instance['custom_taxonomy'] == 'product_cat' ) ) ? " style='display: none;'" : '' ).'>
                        <label class="br_admin_center">'.__('Values per row', 'BeRocket_AJAX_domain').'</label>
                        <select id="values_per_row" name="'.$post_name.'[values_per_row]" class="berocket_aapf_widget_admin_values_per_row br_select_menu_left">
                            <option'.( ( empty($instance['operator']) || $instance['values_per_row'] == '1' ) ? ' selected' : '' ).' value="1">Default</option>
                            <option'.( $instance['values_per_row'] == '2' ? ' selected' : '' ).' value="2">2</option>
                            <option'.( $instance['values_per_row'] == '3' ? ' selected' : '' ).' value="3">3</option>
                            <option'.( $instance['values_per_row'] == '4' ? ' selected' : '' ).' value="4">4</option>
                        </select>
                    </div>
                ',
            )
        );
        return $advanced_settings_elements;
    }
    
    public static function get_page_text() {
        global $wp_query;
        $text = '';
        $object_id = $wp_query->get_queried_object_id();
        if( $object_id == 0 ) {
            if( is_shop() ) {
                $text = 'shop';
            } elseif( is_home() ) {
                $text = 'home';
            } else {
                $text = 'other';
            }
        } else {
            if ( $wp_query->is_category || $wp_query->is_tag || $wp_query->is_tax ) {
                $text = 'taxonomy' . $object_id;
            } elseif( $wp_query->is_post_type_archive ) {
                $text = 'archive' . $object_id;
            } elseif( $wp_query->is_posts_page || ($wp_query->is_singular && ! empty( $wp_query->post )) ) {
                $text = 'post' . $object_id;
            } else {
                $text = 'other' . $object_id;
            }
        }
        return $text;
    }
    function widget_attribute_type_terms($vars, $attr_type, $attr_filter_type, $instance) {
        extract($instance);

        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options    = $BeRocket_AAPF->get_option();

        list( $terms_error_return, $terms_ready, $terms, $type ) = $vars;

        if ( $attr_filter_type == 'attribute' ) {
            if ( (berocket_isset($type) == 'ranges' || in_array(berocket_isset($new_template), array('checkbox', 'select'))) && $attr_type == 'price' ) {
                $woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget_functions::woocommerce_hide_out_of_stock_items();
                $wp_check_product_cat = $this->get_page_text();
                $terms_ready = true;

                if ( count( $ranges ) < 2 ) {
                    $terms_error_return = 'ranges < 2';
                    $terms = $ranges;
                    return array($terms_error_return, $terms_ready, $terms, $type);
                }
                $terms = array();
                $ranges[0]--;

                if ( ! empty( $hide_first_last_ranges ) or ! empty( $show_last_to_infinity ) ) {
                    $price_range = br_get_cache( 'price_range', $wp_check_product_cat );
                    if ( $price_range === false ) {
                        $price_range = BeRocket_AAPF_Widget_functions::get_price_range( ( isset($cat_value_limit) ? $cat_value_limit : null ) );
                        br_set_cache( 'price_range', $price_range, $wp_check_product_cat, BeRocket_AJAX_cache_expire );
                    }
                }

                for ( $i = 1; $i < count( $ranges ); $i++ ) {
                    if( empty($ranges[ $i ]) || empty($ranges[ $i - 1 ]) ) continue;
                    $add_term_ranges = true;
                    if ( ! empty( $hide_first_last_ranges ) ) {
                        if ( ! empty( $price_range ) and count( $price_range ) >= 2 ) {
                            if ( $price_range[ 0 ] >= $ranges[ $i ] or $price_range[ 1 ] <= $ranges[ $i - 1 ] ) {
                                $add_term_ranges = false;
                            }
                        }
                    }

                    if ( $add_term_ranges ) {
                        $range_from = intval( apply_filters( 'berocket_price_filter_widget_min_amount', apply_filters( 'woocommerce_price_filter_widget_min_amount', $ranges[ $i - 1 ] ), $ranges[ $i - 1 ] ) ) + 1;
                        $range_to   = intval( apply_filters( 'berocket_price_filter_widget_max_amount', apply_filters( 'woocommerce_price_filter_widget_max_amount', $ranges[ $i ] ), $ranges[ $i ] ) );
                        $t_id       = ( $ranges[ $i - 1 ] + 1 ) . '*' . $ranges[ $i ];
                        $t_name = $this->ranges_name_generate('', $i, $range_from, $range_to, $instance);
                        /*$t_name     = ( ! empty( $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_before_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_before_value . '" alt=""></i>' ) : '' ) . $text_before_price .
                                      berocket_format_number( $range_from, $br_options['number_style'] ) . $text_after_price . ( ! empty( $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_after_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_after_value . '" alt=""></i>' ) : '' ) . ' - ' . ( ! empty( $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_before_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_before_value . '" alt=""></i>' ) : '' ) . $text_before_price .
                                      berocket_format_number( $range_to, $br_options['number_style'] ) . $text_after_price . ( ! empty( $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_after_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_after_value . '" alt=""></i>' ) : '' );*/
                        $term       = array( 'term_id'  => $t_id,
                                             'slug'     => $t_id,
                                             'value'    => $t_id,
                                             'name'     => $t_name,
                                             'count'    => 1,
                                             'taxonomy' => $attribute
                        );
                        $term       = (object) $term;

                        if ( braapf_filters_must_be_recounted() || ! empty( $show_product_count_per_attr ) ) {
                            $range = apply_filters('berocket_min_max_filter_range', array($ranges[ $i - 1 ], $ranges[ $i ]));
                            $this->price_range_count( $term, $range[0], $range[1] );
                        }

                        $terms[] = $term;
                    }
                }

                if ( ! empty( $show_last_to_infinity ) and ! empty( $price_range ) and count( $price_range ) >= 2 ) {
                    if ( ! ( $price_range[ 1 ] > ( $range_last_value = end( $ranges ) ) ) and ! empty( $hide_first_last_ranges ) ) {
                        $term = array_pop( $terms );
                        list( $range_last_value, $temp ) = explode( "*", $term->term_id );
                        $range_last_value--;
                    }

                    $range_from = intval( apply_filters( 'berocket_price_filter_widget_min_amount', apply_filters( 'woocommerce_price_filter_widget_min_amount', $range_last_value ), $range_last_value ) ) + 1;
                    $t_id       = $range_last_value + 1 . '*' . (intval($price_range[ 1 ]) + 1);
                    $infinity_text = (empty($to_infinity_text) ? '&#8734;' : $to_infinity_text);
                    $t_name = $this->ranges_name_generate('', 'infinity', $range_from, $infinity_text, $instance);
                    /*$t_name     = ( ! empty( $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_before_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_before_value . '" alt=""></i>' ) : '' ) . $text_before_price .
                                                    berocket_format_number( $range_from, $br_options['number_style'] ) . $text_after_price . ( ! empty( $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_after_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_after_value . '" alt=""></i>' ) : '' ) . ' - ' . ( ! empty( $icon_before_value ) ? ( ( substr( $icon_before_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_before_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_before_value . '" alt=""></i>' ) : '' ) . ( '&#8734;' ) . ( ! empty( $icon_after_value ) ? ( ( substr( $icon_after_value, 0, 3 ) == 'fa-' ) ? '<i class="fa ' . $icon_after_value . '"></i>' : '<i class="fa"><img class="berocket_widget_icon" src="' . $icon_after_value . '" alt=""></i>' ) : '' );*/
                    $term       = array( 'term_id'  => $t_id,
                                         'slug'     => $t_id,
                                         'value'    => $t_id,
                                         'name'     => $t_name,
                                         'count'    => 1,
                                         'taxonomy' => $attribute
                    );
                    $term       = (object) $term;

                    if ( braapf_filters_must_be_recounted() || ! empty( $show_product_count_per_attr ) ) {
                        $range = apply_filters('berocket_min_max_filter_range', array($range_last_value, $price_range[ 1 ]));
                        $this->price_range_count( $term, $range[0], $range[1] );
                    }

                    $terms[] = $term;
                }
            } elseif ( $attr_type == '_stock_status' ) {
                $terms_ready = true;
                $terms       = array();
                array_push( $terms, (object) array( 'term_id'           => '1',
                                                    'term_taxonomy_id'  => '1',
                                                    'name'              => __( 'In stock', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'instock',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '1' : 'instock' ),
                                                    'taxonomy'          => '_stock_status',
                                                    'count'             => 1
                ) );
                array_push( $terms, (object) array( 'term_id'           => '2',
                                                    'term_taxonomy_id'  => '2',
                                                    'name'              => __( 'Out of stock', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'outofstock',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '2' : 'outofstock' ),
                                                    'taxonomy'          => '_stock_status',
                                                    'count'             => 1
                ) );

                $terms = BeRocket_AAPF_Widget_functions::get_attribute_values(
                    $attr_type,
                    'id',
                    ( braapf_filters_must_be_recounted('first') ),
                    ( braapf_filters_must_be_recounted() ),
                    $terms,
                    ( isset( $cat_value_limit ) ? $cat_value_limit : null ),
                    $operator
                );
            } elseif ( $attr_type == '_sale' ) {
                $terms_ready = true;
                $terms       = array();
                array_push( $terms, (object) array( 'term_id'           => '1',
                                                    'term_taxonomy_id'  => '1',
                                                    'name'              => __( 'On sale', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'sale',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '1' : 'sale' ),
                                                    'taxonomy'          => '_sale',
                                                    'count'             => 1
                ) );
                array_push( $terms, (object) array( 'term_id'           => '2',
                                                    'term_taxonomy_id'  => '2',
                                                    'name'              => __( 'Not on sale', 'BeRocket_AJAX_domain' ),
                                                    'slug'              => 'notsale',
                                                    'value'             => ( empty($br_options['slug_urls']) ? '2' : 'notsale' ),
                                                    'taxonomy'          => '_sale',
                                                    'count'             => 1
                ) );
                $terms = BeRocket_AAPF_Widget_functions::get_attribute_values(
                    $attr_type,
                    'id',
                    ( braapf_filters_must_be_recounted('first') ),
                    ( braapf_filters_must_be_recounted() ),
                    $terms,
                    ( isset( $cat_value_limit ) ? $cat_value_limit : null ),
                    $operator
                );
            }
        } elseif( $attr_filter_type == 'date' ) {
            $terms_ready = true;
            $type        = 'date';
        }

        return array( $terms_error_return, $terms_ready, $terms, $type );
    }

    function ranges_name_generate($name, $i, $start_value, $end_value, $instance) {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $br_options    = $BeRocket_AAPF->get_option();
        $range_display_type = br_get_value_from_array($instance, array('range_display_type'));
        if( $range_display_type == 'same' ) {
            $range_from = intval($start_value);
            if( $i != 1 ) {
                $range_from = $range_from -1;
            }
            if( $i != 'infinity' ) {
                $range_to   = intval($end_value);
            }
        } elseif( $range_display_type == 'decimal' ) {
            $range_from = intval($start_value);
            if( $i != 1 ) {
                $range_from = $range_from -1;
            }
            if( $i != 'infinity' ) {
                $range_to   = intval($end_value) - 0.01;
            }
        } else {
            $range_from = intval($start_value);
            if( $i != 'infinity' ) {
                $range_to   = intval($end_value);
            }
        }
        $price_args = array();
        if(! empty($instance['number_style']) ) {
            $price_args['decimal_separator'] = br_get_value_from_array($instance, array('number_style_decimal_separate'));
            $price_args['thousand_separator'] = br_get_value_from_array($instance, array('number_style_thousand_separate'));
            $price_args['decimals'] = intval(br_get_value_from_array($instance, array('number_style_decimal_number')));
        }
        if( ! empty($instance['custom_price_ranges']) && ! empty($instance['custom_price_ranges']) ) {
            $price_args['price_format'] = '';
        }
        $range_from = $this->wc_price( $range_from, $price_args );
        if( $i == 'infinity' ) {
            $range_to = $end_value;
        } else {
            $range_to = $this->wc_price( $range_to, $price_args );
        }
        if( ! empty($instance['custom_price_ranges']) && ! empty($instance['custom_price_ranges_text']) ) {
            $args_price = apply_filters(
                'bapf_wc_price_args',
                array(
                    'currency'           => '',
                    'decimal_separator'  => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals'           => wc_get_price_decimals(),
                    'price_format'       => get_woocommerce_price_format(),
                )
            );
            $cur_slug = $args_price['currency'];
            if( empty($args_price['currency']) ) {
                $cur_slug = get_woocommerce_currency();
            }
            $cur_symbol = get_woocommerce_currency_symbol($cur_slug);
            $t_name = str_replace(array('%from%', '%to%', '%cur_symbol%', '%cur_slug%'), array($range_from, $range_to, $cur_symbol, $cur_slug), $instance['custom_price_ranges_text']);
        } else {
            $t_name = $range_from . apply_filters('bapf_price_ranges_separate', ' - ', $range_from ) . $range_to;
        }
        return $t_name;
    }
    function wc_price( $price, $args = array() ) {
        $args = apply_filters(
            'bapf_wc_price_args',
            wp_parse_args(
                $args,
                array(
                    'currency'           => '',
                    'decimal_separator'  => wc_get_price_decimal_separator(),
                    'thousand_separator' => wc_get_price_thousand_separator(),
                    'decimals'           => wc_get_price_decimals(),
                    'price_format'       => get_woocommerce_price_format(),
                )
            )
        );

        $unformatted_price = $price;
        $negative          = $price < 0;
        $price             = apply_filters( 'bapf_raw_woocommerce_price', floatval( $negative ? $price * -1 : $price ) );
        $price             = apply_filters( 'bapf_formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

        if ( apply_filters( 'bapf_woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
            $price = wc_trim_zeros( $price );
        }

        if( empty($args['price_format']) ) {
            $formatted_price = ( $negative ? '-' : '' ) . $price;
        } else {
            $formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], get_woocommerce_currency_symbol( $args['currency'] ), $price );
        }

        /**
         * Filters the string of price markup.
         *
         * @param string $return            Price HTML markup.
         * @param string $price             Formatted price.
         * @param array  $args              Pass on the args.
         * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
         */
        return apply_filters( 'bapf_wc_price', $formatted_price, $price, $args, $unformatted_price );
    }
    function widget_load_template_name($name) {
        if( in_array($name, array('date', 'ranges')) ) {
            $name = 'paid/'.$name;
        }
        return $name;
    }

    function price_range_count($term, $from, $to) {
        if( class_exists('WP_Meta_Query') && class_exists('WP_Tax_Query') ) {
            global $wpdb, $wp_query;
            $old_join_posts = '';
            $has_new_function = method_exists('WC_Query', 'get_main_query') && method_exists('WC_Query', 'get_main_meta_query') && method_exists('WC_Query', 'get_main_tax_query');
            if( $has_new_function ) {
                $WC_Query_get_main_query = WC_Query::get_main_query();
                $has_new_function = ! empty($WC_Query_get_main_query);
            }
            if( ! $has_new_function ) {
                $old_query_vars = BeRocket_AAPF_Widget_functions::old_wc_compatible($wp_query);
                $old_meta_query = (empty( $old_query_vars[ 'meta_query' ] ) || ! is_array($old_query_vars[ 'meta_query' ]) ? array() : $old_query_vars['meta_query']);
                $old_tax_query = (empty($old_query_vars['tax_query']) || ! is_array($old_query_vars[ 'tax_query' ]) ? array() : $old_query_vars['tax_query']);
            } else {
                $old_query_vars = BeRocket_AAPF_Widget_functions::old_wc_compatible($wp_query, true);
            }
            if( ! empty( $old_query_vars['posts__in'] ) ) {
                $old_join_posts = " AND {$wpdb->posts}.ID IN (".implode(',', $old_query_vars['posts__in']).") ";
            }
            if( $has_new_function ) {
                $tax_query  = WC_Query::get_main_tax_query();
            } else {
                $tax_query = $old_tax_query;
            }
            if( $has_new_function ) {
                $meta_query  = WC_Query::get_main_meta_query();
            } else {
                $meta_query = $old_meta_query;
            }
            foreach( $meta_query as $key => $val ) {
                if( is_array($val) ) {
                    if ( ! empty( $val['price_filter'] ) || ! empty( $val['rating_filter'] ) ) {
                        unset( $meta_query[ $key ] );
                    }
                    if( isset( $val['relation']) ) {
                        unset($val['relation']);
                        foreach( $val as $key2 => $val2 ) {
                            if ( isset( $val2['key'] ) && $val2['key'] == apply_filters('berocket_price_filter_meta_key', '_price', 'paid_1783') ) {
                                if ( isset( $meta_query[ $key ][ $key2 ] ) ) unset( $meta_query[ $key ][ $key2 ] );
                            }
                        }
                        if( count($meta_query[ $key ]) <= 1 ) {
                            unset( $meta_query[ $key ] );
                        }
                    } else {
                        if ( isset( $val['key'] ) && $val['key'] == apply_filters('berocket_price_filter_meta_key', '_price', 'paid_1791') ) {
                            if ( isset( $meta_query[ $key ] ) ) unset( $meta_query[ $key ] );
                        }
                    }
                }
            }
            $meta_query      = new WP_Meta_Query( $meta_query );
            $tax_query       = new WP_Tax_Query( ( empty($tax_query) || ! is_array($tax_query) ? array() : $tax_query ) );
            $meta_query_sql  = $meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
            $tax_query_sql   = $tax_query->get_sql( $wpdb->posts, 'ID' );

            // Generate query
            $query           = array();
            $query['select'] = "SELECT COUNT( DISTINCT {$wpdb->posts}.ID ) as range_count";
            $query['from']   = "FROM {$wpdb->posts}";
            $query['join']   = "
                INNER JOIN {$wpdb->postmeta} AS price_term ON {$wpdb->posts}.ID = price_term.post_id
                " . $tax_query_sql['join'] . $meta_query_sql['join'];
            $query['where']   = "
                WHERE {$wpdb->posts}.post_type IN ( 'product' )
                AND " . br_select_post_status() . "
                " . $tax_query_sql['where'] . $meta_query_sql['where'] . "
                AND price_term.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'paid_1824')."' AND price_term.meta_value >= {$from} AND price_term.meta_value <= {$to}
            ";
            if( ! empty($_POST['price_ranges']) ) {
                $old_price_ranges = $_POST['price_ranges'];
            }
            $_POST['price_ranges'] = array($from.'*'.$to);
            $limit_post__not_in = apply_filters('berocket_add_out_of_stock_variable', array(), berocket_isset($_POST['terms']), berocket_isset($_POST['limits_arr']));
            if( isset($old_price_ranges) ) {
                $_POST['price_ranges'] = $old_price_ranges;
            } else {
                unset($_POST['price_ranges']);
            }
            if( !empty($limit_post__not_in) && is_array($limit_post__not_in) && count($limit_post__not_in) ) {
                $query['where'] = $query['where']." AND {$wpdb->posts}.ID NOT IN (" . implode(',', $limit_post__not_in) . ")";
            }
            if( defined( 'WCML_VERSION' ) && defined('ICL_LANGUAGE_CODE') ) {
                $query['join'] = $query['join']." INNER JOIN {$wpdb->prefix}icl_translations as wpml_lang ON ( {$wpdb->posts}.ID = wpml_lang.element_id )";
                $query['where'] = $query['where']." AND wpml_lang.language_code = '".ICL_LANGUAGE_CODE."' AND wpml_lang.element_type = 'post_product'";
            }
            br_where_search( $query );
            $query['where'] .= $old_join_posts;
            $query             = apply_filters( 'woocommerce_get_filtered_ranges_product_counts_query', $query );
            $query             = implode( ' ', $query );

            $results           = $wpdb->get_results( $query );
            if( isset( $results[0]->range_count ) ) {
                $term->count = $results[0]->range_count;
            }
        }
        return $term;
    }
    function add_terms( $filtered_posts ) {
        if ( empty($_POST['add_terms']) ) {
            return $filtered_posts;
        }
        global $berocket_post_before_add_terms;
        if( ! empty($_POST['add_terms']) && is_array($_POST['add_terms']) ) {
            if( ! isset($berocket_post_before_add_terms) ) {
                $berocket_post_before_add_terms = $filtered_posts;
            }
            $add_terms = array('_sale' => array());
            foreach($_POST['add_terms'] as $terms) {
                if( isset($add_terms[$terms[0]]) ) {
                    $add_terms[$terms[0]][] = $terms[1];
                }
            }
            foreach($add_terms as $term_name => $terms) {
                if( count($terms) > 0 ) {
                    $term_posts = array(0);
                    if($term_name == '_sale') {
                        if( in_array('2', $terms) ) {
                            $products = $this->wc_get_product_ids_not_on_sale();
                            $term_posts = array_merge($term_posts, $products);
                            unset($products);
                        }
                        if( in_array('1', $terms) ) {
                            $products = wc_get_product_ids_on_sale();
                            $term_posts = array_merge($term_posts, $products);
                            unset($products);
                        }
                    }
                    if ( sizeof( $filtered_posts ) == 0 ) {
                        $filtered_posts = $term_posts;
                    } else {
                        $filtered_posts = array_intersect( $filtered_posts, $term_posts );
                    }
                }
            }
        }
        return $filtered_posts;
    }
    function add_terms_recount($taxonomy_data) {
        if( ! empty($_POST['add_terms']) && is_array($_POST['add_terms']) ) {
            global $berocket_post_before_add_terms;
            if( isset($berocket_post_before_add_terms) && (! $taxonomy_data['use_filters'] || ($taxonomy_data['use_filters'] && $taxonomy_data['taxonomy'] == '_sale')) ) {
                $taxonomy_data['post__in'] = $berocket_post_before_add_terms;
            }
        }
        return $taxonomy_data;
    }
    function wc_get_product_ids_not_on_sale() {
        global $wpdb;

        // Load from cache
        $product_ids_not_on_sale = get_transient( 'wc_products_notonsale' );

        // Valid cache found
        if ( false !== $product_ids_not_on_sale ) {
            return $product_ids_not_on_sale;
        }
        delete_transient( 'wc_products_onsale' );
        $product_ids_on_sale = wc_get_product_ids_on_sale();
        $product_ids_on_sale[] = -1;

        $on_sale_posts = $wpdb->get_results( "
            SELECT post.ID, post.post_parent FROM `$wpdb->posts` AS post
            LEFT JOIN `$wpdb->postmeta` AS meta ON post.ID = meta.post_id
            WHERE post.post_type IN ( 'product', 'product_variation' )
                AND post.post_status = 'publish'
                AND meta.meta_key = '".apply_filters('berocket_price_filter_meta_key', '_price', 'paid_1900')."'
                AND post.ID NOT IN (".implode(',', $product_ids_on_sale).")
                AND post.post_parent NOT IN (".implode(',', $product_ids_on_sale).")
            GROUP BY post.ID;
        " );

        $product_ids_not_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

        set_transient( 'wc_products_notonsale', $product_ids_not_on_sale, DAY_IN_SECONDS * 30 );

        return $product_ids_not_on_sale;
    }
    function widget_display_custom_filter($return, $widget_type, $instance, $args, $widget_instance) {
        if ( $widget_type == 'search_box' ) {
            extract($instance);
            extract($args);
            if( $search_box_link_type == 'shop_page' ) {
                if( function_exists('wc_get_page_id') ) {
                    $search_box_url = get_permalink( wc_get_page_id( 'shop' ) );
                } else {
                    $search_box_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
                }
            } elseif( $search_box_link_type == 'category' ) {
                $search_box_url = get_term_link( $search_box_category, 'product_cat' );
            }
            $sb_style = '';
            if ( $search_box_style['position'] == 'horizontal' ) {
                $sb_count = $search_box_count;
                if( $search_box_style['search_position'] == 'before_after' ) {
                    $sb_count += 2;
                } else {
                    $sb_count++;
                }
                $search_box_width = (int)(100 / $sb_count);
                $sb_style .= 'width:'.$search_box_width.'%;display:inline-block;padding: 4px;';
            }
            echo $before_widget;
            $search_box_button_class = 'search_box_button_class_'.rand();
            $sbb_style = '';
            if( ! empty($search_box_style['background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['background'][0] == '#' ? $search_box_style['background'] : '#'.$search_box_style['background']).';';
            }
            $sbb_style .= 'opacity:'.$search_box_style['back_opacity'].';';
            if( ! empty($title) ) { ?><h3 class="widget-title berocket_aapf_widget-title" style="<?php echo ( empty($uo['style']['title']) ? '' : $uo['style']['title'] ) ?>"><span><?php echo $title; ?></span></h3><?php }
            echo '<div class="berocket_search_box_block">';
            echo '<div class="berocket_search_box_background" style="'.$sbb_style.'"></div>';
            echo '<div class="berocket_search_box_background_all">';
            $sbb_style = '';
            if( ! empty($search_box_style['button_background']) ) {
                $sbb_style .= 'background-color:'.($search_box_style['button_background'][0] == '#' ? $search_box_style['button_background'] : '#'.$search_box_style['button_background']).';';
            }
            if( ! empty($search_box_style['text_color']) ) {
                $sbb_style .= 'color:'.($search_box_style['text_color'][0] == '#' ? $search_box_style['text_color'] : '#'.$search_box_style['text_color']).';';
            }
            if( ! empty($search_box_style['button_background_over']) ) {
                $sbb_style_hover = 'background-color:'.($search_box_style['button_background_over'][0] == '#' ? $search_box_style['button_background_over'] : '#'.$search_box_style['button_background_over']).';';
            }
            if( ! empty($search_box_style['text_color_over']) ) {
                $sbb_style_hover .= 'color:'.($search_box_style['text_color_over'][0] == '#' ? $search_box_style['text_color_over'] : '#'.$search_box_style['text_color_over']).';';
            }
            if ( $search_box_style['search_position'] == 'before' || $search_box_style['search_position'] == 'before_after' ) {
                echo '<div style="'.$sb_style.'"><a data-url="'.$search_box_url.'" class="'.$search_box_button_class.' berocket_search_box_button">'.$search_box_style['search_text'].'</a></div>';
            }
            for($i = 1; $i <= $search_box_count; $i++) {
                echo '<div style="'.$sb_style.'">';
                $current_box = $search_box_attributes[$i];
                $BeRocket_AAPF_single_filter = BeRocket_AAPF_single_filter::getInstance();
                $search_instance = $BeRocket_AAPF_single_filter->default_settings;
                $search_instance['filter_type'] = ( empty($current_box['type']) ? '' : $current_box['type'] );
                $search_instance['attribute'] = ( empty($current_box['attribute']) ? '' : $current_box['attribute'] );
                $search_instance['custom_taxonomy'] = ( empty($current_box['custom_taxonomy']) ? '' : $current_box['custom_taxonomy'] );
                $search_instance['type'] = ( empty($current_box['visual_type']) ? '' : $current_box['visual_type'] );
                $search_instance['height'] = ( empty($current_box['height']) ? '' : $current_box['height'] );
                $search_instance['scroll_theme'] = ( empty($current_box['scroll_theme']) ? '' : $current_box['scroll_theme'] );
                $search_instance['selected_area_show'] = ( empty($current_box['selected_area_show']) ? '' : $current_box['selected_area_show'] );
                $search_instance['hide_selected_arrow'] = ( empty($current_box['hide_selected_arrow']) ? '' : $current_box['hide_selected_arrow'] );
                $search_instance['selected_is_hide'] = ( empty($current_box['selected_is_hide']) ? '' : $current_box['selected_is_hide'] );
                $search_instance['is_hide_mobile'] = ( empty($current_box['is_hide_mobile']) ? '' : $current_box['is_hide_mobile'] );
                $search_instance['cat_propagation'] = ( empty($current_box['cat_propagation']) ? '' : $current_box['cat_propagation'] );
                $search_instance['cat_propagation'] = ( empty($current_box['cat_propagation']) ? '' : $current_box['cat_propagation'] );
                $search_instance['product_cat'] = ( empty($current_box['product_cat']) ? '' : $current_box['product_cat'] );
                $search_instance['show_page'] = ( empty($current_box['show_page']) ? '' : $current_box['show_page'] );
                $search_instance['cat_value_limit'] = ( empty($current_box['cat_value_limit']) ? '' : $current_box['cat_value_limit'] );
                $search_instance['widget_id'] = $widget_instance->id;
                $search_instance['widget_id_number'] = $widget_instance->number;

                $widget_search = new BeRocket_AAPF_Widget($search_instance, array('before_widget' => '<h4>'.$current_box['title'].'</h4>', 'after_widget' =>''));
                echo '</div>';
            }
            if ( $search_box_style['search_position'] == 'after' || $search_box_style['search_position'] == 'before_after' ) {
                echo '<div style="'.$sb_style.'">
                <a data-url="'.$search_box_url.'" 
                class="'.$search_box_button_class.' berocket_search_box_button">
                '.$search_box_style['search_text'].'</a></div>';
            }
            echo '</div></div>';
            echo '<style>.'.$search_box_button_class.'{'.$sbb_style.'}.'.$search_box_button_class.':hover{'.$sbb_style_hover.'}</style>';
            echo $after_widget;
            $return = true;
        }
        if( !( $instance['filter_type'] == 'attribute'
        && ( $instance['attribute'] == 'price' || $instance['attribute'] == 'product_cat' ) )
        || $instance['filter_type'] == 'product_cat'
        || $instance['filter_type'] == '_stock_status'
        || $instance['filter_type'] == 'tag'
        || $instance['type'] == 'slider' ) {
            if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'depth' ) {
                global $bapf_unique_id;
                $bapf_unique_id_start = $bapf_unique_id;
                $bapf_unique_id = $bapf_unique_id_start + 1000;
                $count = ( empty($instance['child_onew_count']) ? '' : $instance['child_onew_count'] );
                $title = ( empty($instance['title']) ? '' : $instance['title'] );
                $instance['child_parent'] = 'parent';
                $childs = ( empty($instance['child_onew_childs']) ? '' : $instance['child_onew_childs'] );
                
                $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget($instance, $args);
                $instance['child_parent'] = 'child';
                for( $i = 1; $i <= $count; $i++ ) {
                    $bapf_unique_id = $bapf_unique_id_start + ($i + 1) * 1000;
                    $child = $childs[$i];
                    $new_args = $args;
                    $instance['child_parent_depth'] = $i;
                    $instance['title'] = $childs[$i]['title'];
                    $instance['filter_title'] = $childs[$i]['title'];
                    $instance['child_parent_no_values'] = ( empty($childs[$i]['no_values']) ? '' : $childs[$i]['no_values'] );
                    $instance['child_parent_previous'] = ( empty($childs[$i]['previous']) ? '' : $childs[$i]['previous'] );
                    $instance['child_parent_no_products'] = ( empty($childs[$i]['no_product']) ? '' : $childs[$i]['no_product'] );
                    $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget($instance, $new_args);
                }
                $bapf_unique_id = $bapf_unique_id_start;
                $return = true;
            }
        }
        return $return;
    }
    function filter_term_name($name, $term) {
        $berocket_query_var_title = get_query_var('berocket_query_var_title');
        $show_product_count_per_attr = br_get_value_from_array($berocket_query_var_title, 'show_product_count_per_attr');
        if( ! empty($show_product_count_per_attr) ) {
            $name = $name . ' <span class="berocket_aapf_count">' . berocket_isset($term, 'count') . '</span>';
        }
        return $name;
    }
    function select_filter_term_name($name, $term) {
        $berocket_query_var_title = get_query_var('berocket_query_var_title');
        $show_product_count_per_attr = br_get_value_from_array($berocket_query_var_title, 'show_product_count_per_attr');
        if( ! empty($show_product_count_per_attr) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $br_options = $BeRocket_AAPF->get_option();
            $text_before = $text_after = ' ';
            if( ! empty($br_options['styles_input']['product_count']) ) {
                if( $br_options['styles_input']['product_count'] == 'round' ) {
                    $text_before = ' (';
                    $text_after = ')';
                } elseif( $br_options['styles_input']['product_count'] == 'quad' ) {
                    $text_before = ' [';
                    $text_after = ']';
                }
            }
            $name = $name . $text_before . berocket_isset($term, 'count') . $text_after;
        }
        return $name;
    }
    function temp_meta_class_init($meta_class, $term) {
        $berocket_query_var_title = get_query_var('berocket_query_var_title');
        $show_product_count_per_attr = br_get_value_from_array($berocket_query_var_title, 'show_product_count_per_attr');
        if( ! empty($show_product_count_per_attr) ) {
            $meta_class = berocket_isset($term, 'count');
        }
        return $meta_class;
    }
    function temp_meta_class_ready($vars, $term, $meta_color_init) {
        $berocket_query_var_title       = get_query_var('berocket_query_var_title');
        $show_product_count_per_attr    = br_get_value_from_array($berocket_query_var_title, 'show_product_count_per_attr');
        $product_count_position         = br_get_value_from_array($berocket_query_var_title, array('styles_input', 'product_count_position_image'));
        list($meta_class, $meta_after, $meta_color) = $vars;
        if( ! empty($show_product_count_per_attr) ) {
            $type = br_get_value_from_array($berocket_query_var_title, 'type');
            if( $type == 'color' ) {
                if( count($meta_color_init) != 1 ) {
                    $meta_class .= '<span class="berocket_color_span_absolute"><span>'.berocket_isset($term, 'count').'</span></span>';
                }
            } elseif( $type == 'image' ) {
                if ( ! empty($meta_color_init[0]) ) {
                    if( empty($product_count_position) ) {
                        $meta_class .= '<span class="berocket_color_span_absolute"><span>'.berocket_isset($term, 'count').'</span></span>';
                    } else {
                        $meta_after = '<span class="berocket_aapf_count">'.$term->count.'</span>';
                    }
                }
            }
        }
        return array($meta_class, $meta_after, $meta_color);
    }
    function temp_span_class($class, $vars, $term) {
        list($meta_class, $meta_after, $meta_color) = $vars;
        $berocket_query_var_title = get_query_var('berocket_query_var_title');
        $show_product_count_per_attr = br_get_value_from_array($berocket_query_var_title, 'show_product_count_per_attr');
        if( ! empty($show_product_count_per_attr) && empty($meta_after) ) {
            $class .= ' berocket_aapf_count';
        }
        return $class;
    }
    function start_temp_class($class) {
        $berocket_query_var_title = get_query_var('berocket_query_var_title');
        $child_parent = br_get_value_from_array($berocket_query_var_title, 'child_parent');
        $attribute = br_get_value_from_array($berocket_query_var_title, 'attribute');
        $child_parent_depth = br_get_value_from_array($berocket_query_var_title, 'child_parent_depth');
        $values_per_row = br_get_value_from_array($berocket_query_var_title, 'values_per_row');
        if( $child_parent == 'child' ) {
            $class .= ' '.$attribute.'_'.$child_parent.'_'.berocket_isset($child_parent_depth);
        }
        if( ! empty($values_per_row) ) {
            $class .= ' '.'berocket_values_'.$values_per_row;
        }
        return $class;
    }
    function display_child_of($get_terms_args, $instance) {
        $parent_value_get = 'parent_'.berocket_isset($get_terms_args['taxonomy']);
        if( ! empty($instance[$parent_value_get]) ) {
            $parent_value = $instance[$parent_value_get];
            if( $parent_value == 'bapf4current' ) {
                $cate = get_queried_object();
                if( isset($cate->term_id) && $cate->taxonomy == $get_terms_args['taxonomy'] ) {
                    $cateID = $cate->term_id;
                } else {
                    $cateID = 0;
                }
                $parent_value = $cateID;
            } elseif( $parent_value == 'bapf1level' ) {
                $parent_value = 0;
            }
            $get_terms_args['child_of'] = intval($parent_value);
            add_filter( 'berocket_aapf_get_terms_additional', array($this, 'display_child_of_advanced'), 7, 2 );
        }
        return $get_terms_args;
    }
    function display_child_of_advanced($get_terms_advanced, $instance) {
        remove_filter( 'berocket_aapf_get_terms_additional', array($this, 'display_child_of_advanced'), 7, 2 );
        $get_terms_advanced['depth'] = intval(berocket_isset($instance['depth_count']));
        return $get_terms_advanced;
    }
    function child_parent_newterms($get_terms_args, $instance) {
        if( ! empty($instance['child_parent']) ) {
            $get_terms_args['hierarchical'] = true;
        }
        if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'parent' ) {
            $get_terms_args['parent'] = 0;
        }
        return $get_terms_args;
    }
    function child_parent_newterms_exclude($terms, $instance, $get_terms_args = false, $get_terms_advanced = false, $set_query_var_title = array()) {
        if( $get_terms_args === false || $get_terms_advanced === false ) {
            return $terms;
        }
        if( ! empty($instance['child_parent']) && $instance['child_parent'] == 'child' ) {
            if( ! empty($get_terms_args['taxonomy']) && is_array($get_terms_args['taxonomy']) && count($get_terms_args['taxonomy']) == 1 ) {
                $get_terms_args['taxonomy'] = array_pop($get_terms_args['taxonomy']);
            }
            $taxonomy_object = get_taxonomy($get_terms_args['taxonomy']);
            if( empty($taxonomy_object->hierarchical) ) {
                return array();
            }
            $child_parent_depth = max(intval(berocket_isset($instance['child_parent_depth'])), 1);
            $current_terms = $selected_terms_id = array();
            $selected_terms = br_get_selected_term( $get_terms_args['taxonomy'] );
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $get_terms_args['taxonomy'] );
                if( count( $ancestors ) >= ( $child_parent_depth - 1 ) ) {
                    if( count( $ancestors ) > ( $child_parent_depth - 1 ) ) {
                        $selected_term = $ancestors[count( $ancestors ) - ( $child_parent_depth )];
                    }
                    if ( ! in_array( $selected_term, $selected_terms_id ) ) {
                        $args_terms = array(
                            'orderby'    => 'id',
                            'order'      => 'ASC',
                            'hide_empty' => false,
                            'parent'     => $selected_term,
                            'fields'     => 'ids'
                        );
                        $selected_terms_id[] = $selected_term;
                        $additional_terms = get_terms( $get_terms_args['taxonomy'], $args_terms );
                        $current_terms = array_merge( $current_terms, $additional_terms );
                    }
                }
            }
            if( empty($set_query_var_title['new_template']) ) {
                $newterms = array( (object) array( 'depth' => 0, 'child' => 0, 'term_id' => 'R__term_id__R', 'count' => 'R__count__R', 'slug' => 'R__slug__R', 'name' => 'R__name__R', 'taxonomy' => 'R__taxonomy__R' ) );
            } else {
                $newterms = array();
            }
            foreach($terms as $i => $term) {
                if( in_array($term->term_id, $current_terms) ) {
                    $newterms[] = $term;
                }
            }
            $terms = $newterms;
        }
        return $terms;
    }
    function hook_include_exclude_items_args($args, $instance) {
        remove_filter( 'berocket_aapf_widget_include_exclude_items', array($this, 'hook_include_exclude_items'), 10, 2 );
        $include_exclude_select = br_get_value_from_array($instance, 'include_exclude_select');
        $include_exclude_list = br_get_value_from_array($instance, 'include_exclude_list');
        if( ! empty($include_exclude_select) ) {
            if( $include_exclude_select == 'include' ) {
                $args['include'] = $include_exclude_list;
            } elseif($include_exclude_select == 'exclude') {
                $args['exclude'] = $include_exclude_list;
            }
        }
        return $args;
    }
    function hook_include_exclude_items($terms, $instance) {
        $include_exclude_select = br_get_value_from_array($instance, 'include_exclude_select');
        $include_exclude_list = br_get_value_from_array($instance, 'include_exclude_list');
        $terms = $this->include_exclude_items($terms, $include_exclude_select, $include_exclude_list);
        return $terms;
    }

    function include_exclude_items($terms, $include_exclude_select, $include_exclude_list) {
        if ( isset($terms) && is_array($terms) && count( $terms ) > 0 ) {
            if( $include_exclude_select == 'include' ) {
                $new_terms = array();
                foreach($terms as $term) {
                    if( in_array($term->term_id, $include_exclude_list) ) {
                        $new_terms[] = $term;
                    }
                }
                $terms = $new_terms;
            } elseif( $include_exclude_select == 'exclude' ) {
                $new_terms = array();
                foreach($terms as $term) {
                    if( ! in_array($term->term_id, $include_exclude_list) ) {
                        $new_terms[] = $term;
                    }
                }
                $terms = $new_terms;
            }
        }
        return $terms;
    }
    public function get_permalinks_oprions() {
        $option_permalink = get_option( 'berocket_permalink_option' );
        if( ! is_array($option_permalink) ) {
            $option_permalink = array();
        }
        $option_permalink = array_merge($this->default_permalink, $option_permalink);
        return $option_permalink;
    }
    public function get_nn_permalinks_oprions() {
        $option_permalink = get_option( 'berocket_nn_permalink_option' );
        if( ! is_array($option_permalink) ) {
            $option_permalink = array();
        }
        $option_permalink = array_merge($this->default_nn_permalink, $option_permalink);
        return $option_permalink;
    }
    public function register_permalink_option() {
        $screen = get_current_screen();
        $default_values = $this->default_permalink;
        if($screen->id == 'options-permalink') {
            $this->save_permalink_option($default_values);
            $this->_register_permalink_option($default_values);
        }
        if(strpos($screen->id, 'widgets') !== FALSE || strpos($screen->id, 'br-product-filters') !== FALSE) {
            $this->register_admin_scripts();
        }
    }
    public function register_admin_scripts(){
        wp_enqueue_script( 'brjsf-ui');
        wp_enqueue_style( 'brjsf-ui' );
        wp_enqueue_style( 'font-awesome' );
    }
    public function _register_permalink_option($default_values) {
        $permalink_option = 'berocket_permalink_option';
        $option_values = get_option( $permalink_option );
        $data = shortcode_atts( $default_values, $option_values );
        update_option($permalink_option, $data);

        $permalink_option = 'berocket_nn_permalink_option';
        $option_values = get_option( $permalink_option );
        $data = shortcode_atts( $this->default_nn_permalink, $option_values );
        update_option($permalink_option, $data);
        
        add_settings_section(
            'berocket_permalinks',
            'BeRocket AJAX Filters',
            'br_permalink_input_section_echo',
            'permalink'
        );
    }
    public function save_permalink_option( $default_values ) {
        if ( isset( $_POST['berocket_permalink_option'] ) ) {
            $option_values    = $_POST['berocket_permalink_option'];
            $data             = shortcode_atts( $default_values, $option_values );
            $data['variable'] = $data['variable'];
            if( empty($data['variable']) ) {
                $data['variable'] = $default_values['variable'];
            }

            update_option( 'berocket_permalink_option', $data );
        }
        if ( isset( $_POST['berocket_nn_permalink_option'] ) ) {
            $option_values    = $_POST['berocket_nn_permalink_option'];
            $data             = shortcode_atts( $this->default_nn_permalink, $option_values );
            if ( empty($data['variable']) ) {
                $data['variable'] = $default_values['variable'];
            }

            update_option( 'berocket_nn_permalink_option', $data );
        }
    }
    //SLIDER ATTRIBUTES
    public function attribute_price_var_title($set_query_var_title, $type, $instance, $args = false, $terms = false) {
        if( $args === false || $terms === false ) return $set_query_var_title;
        extract($instance);
        extract($args);
        $slider_with_string = false;
        $stringed_is_numeric = true;
        $slider_step = 1;
        if ( (($filter_type == 'attribute' && $attribute != 'price') || $filter_type == 'custom_taxonomy') && $type == 'slider' ) {
            if( $filter_type == 'custom_taxonomy' ) {
                $attribute = $custom_taxonomy;
            }
            $min = $max   = false;
            $main_class   = 'slider';
            $slider_class = 'berocket_filter_slider';

            if ( $attribute == 'price' && $type != 'slider' ){
                if ( ! empty($price_values) ) {
                    $price_range = explode( ",", $price_values );
                } elseif( $use_min_price && $use_max_price ) {
                    $price_range = array($min_price, $max_price);
                }
                if( BeRocket_AAPF::$debug_mode ) {
                    $widget_error_log['price_range'] = berocket_isset($price_range);
                }
                wp_localize_script(
                    'berocket_aapf_widget-script',
                    'br_price_text',
                    array(
                        'before'  => (isset($text_before_price) ? $text_before_price : ''),
                        'after'   => (isset($text_after_price) ? $text_after_price : ''),
                    )
                );
                if ( ! empty($price_values) ) {
                    $all_terms_name = $price_range;
                    $all_terms_slug = $price_range;
                    $stringed_is_numeric = true;
                    $min = 0;
                    $max = count( $all_terms_name ) - 1;
                    $slider_with_string = true;
                } else {
                    if( ! empty($price_range) ) {
                        foreach ( $price_range as $price ) {
                            if ( $min === false or $min > (int) $price ) {
                                $min = $price;
                            }
                            if ( $max === false or $max < (int) $price ) {
                                $max = $price;
                            }
                        }
                    }
                    if( $use_min_price ) {
                        $min = $min_price;
                    }
                    if ( $use_max_price ) {
                        $max = $max_price;
                    }
                }
                $id = 'br_price';
                $slider_class .= ' berocket_filter_price_slider';
                $main_class .= ' price';

                $min = floor( $min );
                $max = ceil( $max );
                if( ! empty($_POST['price']) ) {
                    if ( ! empty($price_values) ) {
                        $slider_value1 = array_search( $_POST['price'][0], $all_terms_name );
                        $slider_value2 = array_search( $_POST['price'][1], $all_terms_name );
                    } else {
                        $slider_value1 = apply_filters('berocket_price_filter_widget_min_amount', apply_filters('berocket_price_slider_widget_min_amount', apply_filters('woocommerce_price_filter_widget_min_amount', $_POST['price'][0])), $_POST['price'][0]);
                        $slider_value2 = apply_filters('berocket_price_filter_widget_max_amount', apply_filters('berocket_price_slider_widget_max_amount', apply_filters('woocommerce_price_filter_widget_max_amount', $_POST['price'][1])), $_POST['price'][1]);
                    }
                } else {
                    $slider_value1 = $min;
                    $slider_value2 = $max;
                }
            } else {
                if( ! empty($slider_numeric) ) {
                    $slider_with_string = $stringed_is_numeric = false;
                    
                    $min = $max = false;
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        $name_num = floatval($term->name);
                        if( $min === false || $name_num < $min ) {
                            $min = $name_num;
                        }
                        if( $max === false || $name_num > $max ) {
                            $max = $name_num;
                        }
                    }
                    $max = intval(ceil($max));
                    $min = intval(floor($min));
                    $slider_value1 = $min;
                    $slider_value2 = $max;
                    if( ! empty($_POST['limits']) && is_array($_POST['limits']) ) {
                        foreach ( $_POST['limits'] as $p_limit ) {
                            if ( $p_limit[0] == $attribute ) {
                                $slider_value1 = intval( $p_limit[1] );
                                $slider_value2 = intval( $p_limit[2] );
                            }
                        }
                    }
                } else {
                    $all_terms_name = $all_terms_slug = array();
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        array_push( $all_terms_name, urldecode($term->slug) );
                        array_push( $all_terms_slug, $term->name );
                    }
                    $min = 0;
                    $max = count($all_terms_name) - 1;
                    $slider_with_string = true;
                    $slider_value1 = $min;
                    $slider_value2 = $max;
                    if( ! empty($_POST['limits']) && is_array($_POST['limits']) ) {
                        foreach ( $_POST['limits'] as $p_limit ) {
                            if ( $p_limit[0] == $attribute ) {
                                $slider_value1 = urldecode( $p_limit[1] );
                                $slider_value2 = urldecode( $p_limit[2] );
                                $slider_value1 = array_search( $p_limit[1], $all_terms_name );
                                $slider_value2 = array_search( $p_limit[2], $all_terms_name );
                                if( $slider_value1 === FALSE ) {
                                    $slider_value1 = 0;
                                }
                                if( $slider_value2 === FALSE ) {
                                    $slider_value2 = $max;
                                }
                            }
                        }
                    }
                }
            }
            if( BeRocket_AAPF::$debug_mode ) {
                $widget_error_log['value_1'] = $slider_value1;
                $widget_error_log['value_2'] = $slider_value2;
                $widget_error_log['step'] = $slider_step;
            }

            $wpml_id = preg_replace( '#^pa_#', '', $id );
            $wpml_id = 'pa_'.berocket_wpml_attribute_translate($wpml_id);
            $set_query_var_title['slider_value1'] = $slider_value1;
            $set_query_var_title['slider_value2'] = $slider_value2;
            $set_query_var_title['filter_slider_id'] = $wpml_id;
            $set_query_var_title['main_class'] = $main_class;
            $set_query_var_title['slider_class'] = $slider_class;
            $set_query_var_title['min'] = $min;
            $set_query_var_title['max'] = $max;
            $set_query_var_title['step'] = $slider_step;
            $set_query_var_title['slider_with_string'] = $slider_with_string;
            $set_query_var_title['all_terms_name'] = ( empty($all_terms_name) ? null : $all_terms_name );
            $set_query_var_title['all_terms_slug'] = ( empty($all_terms_slug) ? null : $all_terms_slug );
            $set_query_var_title['text_before_price'] = (isset($text_before_price) ? $text_before_price : null);
            $set_query_var_title['text_after_price'] = (isset($text_after_price) ? $text_after_price : null);
            $set_query_var_title['enable_slider_inputs'] = (isset($enable_slider_inputs) ? $enable_slider_inputs : null);
            if( ! empty($number_style) ) {
                $set_query_var_title['number_style'] = array(
                    ( empty($number_style_thousand_separate) ? '' : $number_style_thousand_separate ), 
                    ( empty($number_style_decimal_separate) ? '' : $number_style_decimal_separate ), 
                    ( empty($number_style_decimal_number) ? '' : $number_style_decimal_number )
                );
            } else {
                $set_query_var_title['number_style'] = '';
            }
        }
        return $set_query_var_title;
    }
    public function add_slider_numeric_sorting($get_terms_advanced, $instance) {
        if( berocket_isset($instance['new_template']) == 'datepicker' ) {
            $get_terms_advanced['orderby'] = 'slug_num';
        } elseif ( berocket_isset($instance['type']) == 'slider' && ! empty($instance['slider_numeric']) ) {
            $get_terms_advanced['orderby'] = 'name_numeric_full';
        }
        return $get_terms_advanced;
    }
    //SEO META TITLE
    public function seo_meta_filtered_terms($terms_name) {
        $custom_name = array(
            '_stock_status' => array(
                'instock' => __('In stock', 'BeRocket_AJAX_domain'),
                'outofstock' => __('Out of stock', 'BeRocket_AJAX_domain'),
            ),
            '_sale' => array(
                'sale' => __('On sale', 'BeRocket_AJAX_domain'),
                'notsale' => __('Not on sale', 'BeRocket_AJAX_domain'),
            )
        );
        if( ! empty($_POST['price_ranges']) && is_array($_POST['price_ranges']) ) {
            if( ! isset($terms_name['wc_price']) ) {
                $terms_name['wc_price'] = array(
                    'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_price_label', __('Price', 'woocommerce')),
                    'values' => array(),
                    'is_price' => TRUE
                );
            }
            foreach($_POST['price_ranges'] as $price_range) {
                $price_range = explode('*', $price_range);
                $min_price = BeRocket_AAPF_addon_woocommerce_seo_title::wc_price($price_range[0]);
                $max_price = BeRocket_AAPF_addon_woocommerce_seo_title::wc_price($price_range[1]);
                $terms_name['wc_price']['values'][] = apply_filters('berocket_aapf_seo_meta_filtered_price_label', wc_format_price_range($min_price, $max_price), $_POST['price_ranges'], array($min_price, $max_price));
            }
        }
        if( isset($_POST['terms']) && is_array($_POST['terms']) ) {
            foreach($_POST['terms'] as $term_parsed) {
                if( ! in_array($term_parsed[0], array('_stock_status')) ) continue;
                $term = get_term($term_parsed[1], $term_parsed[0]);
                if( ! isset($terms_name[$term_parsed[0]]) ) {
                    $terms_name[$term_parsed[0]] = array(
                        'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_label_custom', '', $term_parsed, $custom_name), 
                        'values' => array(),
                        'is_price' => TRUE
                    );
                }
                $term_name = '';
                if( isset($custom_name[$term_parsed[0]], $custom_name[$term_parsed[0]][$term_parsed[3]]) ) {
                    $term_name = $custom_name[$term_parsed[0]][$term_parsed[3]];
                }
                $terms_name[$term_parsed[0]]['values'][$term_parsed[3]] = apply_filters('berocket_aapf_seo_meta_filtered_term_label_custom', $term_name, $term_parsed, $custom_name);
            }
        }
        if( isset($_POST['limits']) && is_array($_POST['limits']) ) {
            foreach($_POST['limits'] as $term_parsed) {
                if( ! in_array($term_parsed[0], array('pa__date', '_date')) ) continue;
                if( ! isset($terms_name[$term_parsed[0]]) ) {
                    $terms_name[$term_parsed[0]] = array(
                        'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_label_custom', '', $term_parsed, $custom_name), 
                        'values' => array(),
                        'is_price' => TRUE
                    );
                }
                $terms_name[$term_parsed[0]]['values'][$term_parsed[1].'_'.$term_parsed[2]] = apply_filters('berocket_aapf_seo_meta_filtered_term_label_custom', sprintf( _x( '%1$s &ndash; %2$s', 'Price range: from-to', 'woocommerce' ), $term_parsed[1], $term_parsed[2] ), $term_parsed);
            }
        }
        if( isset($_POST['add_terms']) && is_array($_POST['add_terms']) ) {
            foreach($_POST['add_terms'] as $term_parsed) {
                if( ! isset($terms_name[$term_parsed[0]]) ) {
                    $terms_name[$term_parsed[0]] = array(
                        'name' => apply_filters('berocket_aapf_seo_meta_filtered_taxonomy_label_custom', '', $term_parsed, $custom_name), 
                        'values' => array(),
                        'is_price' => TRUE
                    );
                }
                $term_name = '';
                if( isset($custom_name[$term_parsed[0]], $custom_name[$term_parsed[0]][$term_parsed[3]]) ) {
                    $term_name = $custom_name[$term_parsed[0]][$term_parsed[3]];
                }
                $terms_name[$term_parsed[0]]['values'][$term_parsed[3]] = apply_filters('berocket_aapf_seo_meta_filtered_term_label_custom', $term_name, $term_parsed, $custom_name);
            }
        }
        return $terms_name;
    }
    public function seo_meta_filtered_term_continue($continue, $term_parsed) {
        if( in_array($term_parsed[0], array('_stock_status', 'pa__date', '_date')) ) {
            $continue = true;
        }
        return $continue;
    }
    public function ranges_add_query_var_title( $set_query_var_title, $type, $instance ) {
        if ( $type == 'ranges' ) {
            $set_query_var_title['disable_multiple_ranges'] = ( ! empty( $instance['disable_multiple_ranges'] ) ? true : false );
        }

        return $set_query_var_title;
    }
    public function ranges() {
        add_filter('berocket_query_var_title_before_widget', array($this, 'ranges_add_query_var_title'), 10, 3);
        add_filter('berocket_query_var_title_before_widget_deprecated', array($this, 'ranges_add_query_var_title'), 10, 3);
    }
    //MULTIPLE COLOR TERMS
    public function multiple_color() {
        add_filter('berocket_aapf_color_term_select_line', array($this, 'multiple_color_term_select'), 10, 2);
        add_filter('berocket_widget_color_image_temp_meta_ready', array($this, 'multiple_color_echo'), 5, 4);
        add_filter('berocket_aapf_meta_color_values', array($this, 'multiple_color_get'), 5, 3);
    }
    public function multiple_color_term_select($html, $term) {
        $html = '<tr>';
        $html .= '<td>'.berocket_isset($term, 'name').'</td>';
        $color_list = array('color', 'color_2', 'color_3', 'color_4');
        foreach($color_list as $color_name) {
            $color_meta = get_metadata('berocket_term', $term->term_id, $color_name); 
            $html .= '<td class="br_colorpicker_field' . ( ( empty($color_meta) && $color_name != 'color' ) ? ' colorpicker_removed' : '' ) . '" data-color="' . br_get_value_from_array($color_meta, 0, 'ffffff') . '">';
            if( $color_name != 'color') {
                $html .= '<i class="fa fa-times"></i>';
            }
            $html .= '</td>';
            $html .= '<input class="br_colorpicker_field_input" type="hidden" value="' . br_get_value_from_array($color_meta, 0) . '"
                   name="br_widget_color[' . $color_name . '][' . $term->term_id . ']" />';
        }
        $html .= '</tr>';
        return $html;
    }
    public function multiple_color_echo($vars, $term, $meta_color_init, $options) {
        list($meta_class, $meta_after, $meta_color) = $vars;
        if( $options['type'] == 'color' ) {
            if( count($meta_color_init) > 1 ) {
                $meta_class = '<span class="berocket_color_multiple berocket_color_multiple_'.count($meta_color_init).'">';
                foreach($meta_color_init as $meta_color_key => $meta_color_val) {
                    $meta_color_val = str_replace('#', '', $meta_color_val);
                    $meta_color_val = 'background-color: #'.$meta_color_val.';';
                    $meta_class .= '<span style="'.$meta_color_val.'" class="berocket_color_multiple_single berocket_color_multiple_single_'.$meta_color_key.'"></span>';
                }
                $meta_class .= '</span>';
                $meta_color = '';
            }
        }
        return array($meta_class, $meta_after, $meta_color);
    }
    public function multiple_color_get($meta_color, $term, $variables_for_hooks) {
        if ( !$variables_for_hooks['is_child_parent'] || !$variables_for_hooks['is_first'] ) {
            if( $variables_for_hooks['type'] == 'color' ) {
                $color_list = array('color', 'color_2', 'color_3', 'color_4');
                $meta_color = array();
                foreach($color_list as $color_name) {
                    $berocket_term = get_metadata( 'berocket_term', $term->term_id, $color_name );
                    $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                    if( empty($berocket_term) && $color_name != 'color') continue;
                    $meta_color[] = $berocket_term;
                }
            }
        }
        return $meta_color;
    }
    function query_var_title($set_query_var_title, $instance, $br_options) {
        $set_query_var_title['date_style'] = br_get_value_from_array($instance, 'date_style');
        $set_query_var_title['date_change_month'] = ! empty($instance['date_change_month']);
        $set_query_var_title['date_change_year'] = ! empty($instance['date_change_year']);
        $set_query_var_title['enable_slider_inputs'] = ! empty($instance['enable_slider_inputs']);
        $set_query_var_title['slider_numeric'] = ! empty($instance['slider_numeric']);
        return $set_query_var_title;
    }
    //NEW TEMPLATES FUNCTIONS
    //NEW SLIDER ATTRIBUTE
    public function new_slider_vars($set_query_var_title, $type, $instance, $args = false, $terms = false) {
        if( in_array(berocket_isset($set_query_var_title['new_template']),array('slider', 'new_slider')) ) {
            extract($set_query_var_title);
            if( $filter_type != 'attribute' || $attribute != 'price' ) {
                if( ! empty($slider_numeric) ) {
                    $slider_with_string = $stringed_is_numeric = false;
                    
                    $min = $max = false;
                    foreach ( $terms as $term ) {
                        $id = $term->taxonomy;
                        $name_num = floatval($term->name);
                        if( $min === false || $name_num < $min ) {
                            $min = $name_num;
                        }
                        if( $max === false || $name_num > $max ) {
                            $max = $name_num;
                        }
                    }
                    $max = intval(ceil($max));
                    $min = intval(floor($min));
                    if( $max != $min ) {
                        $set_query_var_title['terms'] = array(
                            (object)array(
                                'term_id'  => $min.'_'.$max,
                                'slug'     => $min.'_'.$max,
                                'value'    => $min.'_'.$max,
                                'name'     => $term->taxonomy,
                                'count'    => 1,
                                'taxonomy' => $term->taxonomy,
                                'min'      => $min,
                                'max'      => $max,
                                'step'     => '1',
                            )
                        );
                    } else {
                        $set_query_var_title['terms'] = array();
                    }
                    $set_query_var_title['slider_display_data'] = 'num_attr';
                } else {
                    if( count($terms) == 1 ) {
                        foreach($terms as $term){}
                        $terms[] = $term;
                    }
                    foreach($terms as &$term) {
                        $term->min      = 0;
                        $term->max      = count($terms) - 1;
                        $term->step     = 1;
                        $term->value    = $term->slug;
                    }
                    if( isset($term) ) {
                        unset($term);
                    }
                    
                    $set_query_var_title['slider_display_data'] = 'arr_attr';
                    $set_query_var_title['terms'] = $terms;
                }
            }
        } elseif( in_array(berocket_isset($set_query_var_title['new_template']),array('datepicker')) ) {
            if( $set_query_var_title['filter_type'] == 'date' ) {
                global $wpdb;
                $query = "SELECT post_date FROM {$wpdb->posts} WHERE post_type = 'product' ORDER BY post_date ASC LIMIT 1";
                $query = $wpdb->get_var($query);
                $datetime = strtotime('-30 days');
                if( ! empty($query) ) {
                    $datetime = new DateTime($query);
                    $datetime = $datetime->getTimestamp();
                }
                $min = date('Ymd', $datetime);
                $max = date('Ymd', strtotime('+1 day'));
                $terms = array(
                    (object)array(
                        'term_id'  => $min.'_'.$max,
                        'slug'     => $min.'_'.$max,
                        'value'    => $min.'_'.$max,
                        'name'     => __('Date', 'BeRocket_AJAX_domain'),
                        'count'    => 1,
                        'taxonomy' => '_date',
                        'min'      => $min,
                        'max'      => $max,
                        'step'     => '1',
                    )
                );
            } else {
                $terms = array_values($terms);
                $regexr = '/[\D]*(\d{4})[\D]*(\d{2})[\D]*(\d{2})[\D]*/';
                $start = preg_match($regexr, $terms[0]->slug, $matches_start);
                $end   = preg_match($regexr, $terms[count($terms) - 1]->slug, $matches_end);
                if( $start && $end ) {
                    $start =  $matches_start[1] . $matches_start[2] . $matches_start[3];
                    $end =    $matches_end[1] . $matches_end[2] . $matches_end[3];
                    foreach($terms as &$term) {
                        $term->min      = $start;
                        $term->max      = $end;
                        if( preg_match($regexr, $term->slug, $matches) ) {
                            $term->value    = $matches[1] . $matches[2] . $matches[3];
                        } else {
                            $terms = array();
                            break;
                        }
                    }
                    if( isset($term) ) {
                        unset($term);
                    }
                } else {
                    $terms = array();
                }
            }
            $set_query_var_title['terms'] = $terms;
        }
        return $set_query_var_title;
    }
    function new_attribute_slider($template_content, $terms, $berocket_query_var_title) {
        if( in_array($berocket_query_var_title['new_template'], array('slider', 'new_slider')) && count($terms) > 1 ) {
            $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['class']['bapf_slidr_type'] = 'bapf_slidr_arr';
            $slider_data = array();
            foreach($terms as $term) {
                $slider_data[] = array('v' => $term->value, 'n' => $term->name);
            }
            $template_content['template']['content']['filter']['content']['slider_all']['content']['slider']['attributes']['data-attr'] = json_encode($slider_data);
            if( in_array($berocket_query_var_title['new_template'], array('slider')) && ! empty($berocket_query_var_title['enable_slider_inputs']) ) {
                $template_content['template']['content']['filter']['content']['slider_all']['content']['from']['content']['input'] = array(
                    'type'          => 'tag',
                    'tag'           => 'select',
                    'attributes'    => array(),
                );
                $template_content['template']['content']['filter']['content']['slider_all']['content']['to']['content']['input'] = array(
                    'type'          => 'tag',
                    'tag'           => 'select',
                    'attributes'    => array(),
                );
            }
        } elseif( in_array($berocket_query_var_title['new_template'], array('slider')) && ! empty($berocket_query_var_title['enable_slider_inputs']) ) {
            $template_content['template']['content']['filter']['content']['slider_all']['content']['from']['content']['input'] = array(
                'type'          => 'tag_open',
                'tag'           => 'input',
                'attributes'    => array(
                    'type'          => 'text'
                ),
            );
            $template_content['template']['content']['filter']['content']['slider_all']['content']['to']['content']['input'] = array(
                'type'          => 'tag_open',
                'tag'           => 'input',
                'attributes'    => array(
                    'type'          => 'text'
                ),
            );
        }
        return $template_content;
    }
    function datepicker_selected($template_content, $terms, $berocket_query_var_title) {
        if( in_array($berocket_query_var_title['new_template'], array('datepicker')) ) {
            foreach($terms as $term){break;}
            if( ! empty($_POST['limits']) && is_array($_POST['limits']) ) {
                $regexr = '/[\D]*(\d{4})[\D]*(\d{2})[\D]*(\d{2})[\D]*/';
                foreach($_POST['limits'] as $limit) {
                    if( berocket_isset($limit[0]) == $term->taxonomy ) {
                        $terms_numeric = array_values($terms);
                        if( preg_match($regexr, $limit[1], $matches_start) ) {
                            $template_content['template']['content']['filter']['content']['datepicker_all']['attributes']['data-start'] = $matches_start[1] . $matches_start[2] . $matches_start[3];
                        }
                        if( preg_match($regexr, $limit[2], $matches_end) ) {
                            $template_content['template']['content']['filter']['content']['datepicker_all']['attributes']['data-end'] = $matches_end[1] . $matches_end[2] . $matches_end[3];
                        }
                        break;
                    }
                }
            }
        }
        return $template_content;
    }
    //SHOW COUNT BEFORE UPDATE
    function count_before_update() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        if( ! empty($options['ub_product_count']) ) {
            add_filter('berocket_filters_query_vars_already_filtered', array($this, 'count_before_update_query_vars'));
            add_filter('berocket_filters_query_already_filtered', array($this, 'count_before_update_query'));
            add_action('wp_footer', array($this, 'generate_count_before_update'), 1);
        }
    }
    public $count_before_query_vars;
    function count_before_update_query_vars($query_vars) {
        $this->count_before_query_vars = $query_vars;
        return $query_vars;
    }
    function count_before_update_query($query) {
        $this->count_before_query_vars = $query->query_vars;
        return $query;
    }
    function generate_count_before_update() {
        if( ! empty($this->count_before_query_vars) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            BeRocket_tooltip_display::include_assets();
            $query = new WP_Query($this->count_before_query_vars);
            $query = apply_filters('bapf_query_count_before_update', $query);
            $posts = $query->get_posts();
            echo '<span style="display:none!important;" class="bapf_count_before_update">'.$query->found_posts.'</span>';
            echo '<div id="bapf_footer_count_before" data-theme="'.(empty($options['tippy_product_count_theme']) ? 'light' : $options['tippy_product_count_theme']).'"></div>';
            if( ! empty($options['tippy_product_count_fontsize']) && intval($options['tippy_product_count_fontsize']) > 5 ) {
                echo '<style>#bapf_footer_count_before .tippy-content{
                    font-size: '.$options['tippy_product_count_fontsize'].'px;
                }</style>';
            }
        }
    }
    function pll_rewrite_rules($rewrite_rules) {
        $rewrite_rules[] = 'br_filters';
        return $rewrite_rules;
    }
    function pll_filtered_taxonomies($taxonomies, $is_settings) {
        if( ! $is_settings ) {
            $taxonomies[] = 'br_filters';
        }
        return $taxonomies;
    }
}
new BeRocket_AAPF_paid();
class BeRocket_AAPF_paid_new extends BeRocket_plugin_variations {
    function __construct() {
        add_action( 'plugins_loaded', array($this, 'plugins_loaded') );
        add_action( 'braapf_register_frontend_assets', array(__CLASS__, 'register_paid_file_script'), 10, 1 );
        add_action( 'braapf_wp_enqueue_script_after', array(__CLASS__, 'include_paid_file_script'), 10, 1 );
        add_filter('berocket_aapf_recount_remove_all_berocket_meta_query', array(__CLASS__, 'stock_status_recount'), 10, 3);
        add_action( 'bapf_include_all_tempate_styles', array(__CLASS__, 'include_paid_tempate_styles') );
        add_filter( 'brapf_filter_instance', array(__CLASS__, 'datepicker_fix_instance'), 10, 3 );
        add_filter('BeRocket_AAPF_template_full_content', array(__CLASS__, 'value_icon_datepicker'), 600, 4);
        add_action('braapf_single_filter_required', array(__CLASS__, 'datepicker_required'), 800, 2);
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'custom_text_price_ranges'), 800, 2);
        add_action( 'wp_ajax_braapf_datepicker_important_current', array( __CLASS__, 'datepicker_important_current' ) );
        add_filter('berocket_aapf_widget_include_exclude_items', array($this, 'correct_terms_child_parent'), 10100, 5);
        add_filter('berocket_query_var_title_before_widget', array($this, 'correct_terms_child_parent_fix'), 10100, 5);
        add_filter('braapf_single_filter_hide_cat_value_limit', array($this, 'display_cat_value_limit'));
        self::search_field();
    }
    public static function include_paid_tempate_styles() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        foreach (glob($BeRocket_AAPF->info['plugin_dir'] . "/template_styles/paid/*.php") as $filename)
        {
            include_once($filename);
        }
    }
    public static function register_paid_file_script() {
        $admin_file = 'assets/paid/script.js';
        if( file_exists(plugin_dir_path(BeRocket_AJAX_filters_file).$admin_file) ) {
            wp_register_script( 'berocket_aapf_widget-script_paid',
                plugins_url( $admin_file, BeRocket_AJAX_filters_file ),
                array( 'jquery', 'jquery-ui-slider' ) );
        }
    }
    public static function include_paid_file_script($handle) {
        $admin_file = 'assets/paid/script.js';
        if( ! BeRocket_AAPF::$concat_enqueue_files && $handle == 'berocket_aapf_widget-script' && file_exists(plugin_dir_path(BeRocket_AJAX_filters_file).$admin_file) ) {
            BeRocket_AAPF::wp_enqueue_script('berocket_aapf_widget-script_paid');
        }
    }
    function plugins_loaded() {
        $this->single_edit_elements();
        $this->color_elements();
    }
    function single_edit_elements() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $option = $BeRocket_AAPF->get_option();
        add_action('braapf_advanced_single_filter_attribute_setup', array(__CLASS__, 'include_exclude'), 500, 2);
        //REQUIRED
        add_action('braapf_single_filter_required', array(__CLASS__, 'price_ranges'), 500, 2);
        //ADDITIONAL
        //FOR ALL FILTERS
        add_action('braapf_single_filter_additional', array(__CLASS__, 'show_product_count_per_attr'), 250, 2);
        add_action('braapf_advanced_single_filter_attribute_setup', array(__CLASS__, 'parent_product_cat'), 300, 2);
        //CHECKBOX
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'child_parent'), 850, 2);
        add_action('braapf_advanced_single_filter_additional', array(__CLASS__, 'values_per_row'), 950, 2);
        //SLIDER
        add_action('braapf_single_filter_additional', array(__CLASS__, 'enable_slider_inputs'), 350, 2);
        add_action('braapf_single_filter_additional', array(__CLASS__, 'slider_numeric'), 350, 2);
        //DATE
        add_action('braapf_single_filter_additional', array(__CLASS__, 'date_style'), 950, 2);
        if( ! empty( $option['use_links_filters'] ) ) {
            add_filter('BeRocket_AAPF_template_single_item', array($this, 'checkbox_links_filters'), 2000, 4);
        }
    }
    function checkbox_links_filters($element, $term, $i, $berocket_query_var_title) {
        if( $berocket_query_var_title['new_template'] == 'checkbox' && br_get_value_from_array($element, array('content', 'checkbox', 'attributes', 'disabled')) != 'disabled' ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            $operator = $berocket_query_var_title['operator'];
            $single = ! empty($berocket_query_var_title['single_selection']);
            $term_taxonomy_echo = berocket_isset($term, 'wpml_taxonomy');

            if( empty($term_taxonomy_echo) ) {
                $term_taxonomy_echo = berocket_isset($term, 'taxonomy');
            }
            $term_link = apply_filters('berocket_add_filter_to_link', FALSE, array(
                'attribute'         => $term_taxonomy_echo,
                'values'            => berocket_isset($term, 'value'),
                'operator'          => $operator,
                'remove_attribute'  => $single,
            ));
            $element['content']['label']['content'] = array(
                'link'     => array(
                    'type'          => 'tag',
                    'tag'           => 'a',
                    'attributes'    => array(
                        'href'          => $term_link
                    ),
                    'content'       => $element['content']['label']['content']
                ),
            );
            if (
                ! empty( $option['use_nofollow'] ) and
                (
                    $option['use_nofollow'] == 1 or
                    $option['use_nofollow'] == 2 and
                    apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'check_radio_color_filter_term_text' )
                )
            ) {
                $element['content']['label']['content']['link']['attributes']['rel'] = 'nofollow';
            }
            $noindex = false;
            if ( ! empty( $option['use_noindex'] ) and
                 (
                     $option['use_noindex'] == 1 or
                     $option['use_noindex'] == 2 and
                     apply_filters( 'berocket_aapf_is_filtered_page_check', ! empty($_GET['filters']), 'check_radio_color_filter_term_text' )
                 )
            ) {
                $element['content']['label']['content'] = array(
                    'noindex'       => array(
                        'type'          => 'tag',
                        'tag'           => 'noindex',
                        'content'       => $element['content']['label']['content']
                    ),
                );
            }
        }
        return $element;
    }
    static function include_exclude($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_include_exclude_select braapf_full_select_full">';
                $include_exclude_select = br_get_value_from_array($braapf_filter_settings, 'include_exclude_select', '0');
                echo '<label for="braapf_include_exclude_select">' . __('Display only selected values / Remove selected values', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_include_exclude_select" name="'.$settings_name.'[include_exclude_select]">';
                    echo '<option value="">' . __('Disabled', 'BeRocket_AJAX_domain') . '</option>';
                    echo '<option value="include"'.($include_exclude_select == 'include' ? ' selected' : '').'>' . __('Display only', 'BeRocket_AJAX_domain') . '</option>';
                    echo '<option value="exclude"'.($include_exclude_select == 'exclude' ? ' selected' : '').'>' . __('Remove', 'BeRocket_AJAX_domain') . '</option>';
                echo '</select>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            $taxonomy_name = braapf_single_filter_edit_elements::get_curent_taxonomy_name($braapf_filter_settings);
            echo '<div class="braapf_include_exclude_list braapf_full_select_full" data-name="' . $settings_name . '[include_exclude_list]" data-taxonomy="'.( empty($taxonomy_name) ? '' : $taxonomy_name ).'">';
                $include_exclude_list = br_get_value_from_array($braapf_filter_settings, 'include_exclude_list', array());
                if( $taxonomy_name !== false ) {
                    $list = BeRocket_AAPF_Widget_functions::include_exclude_terms_list($taxonomy_name, $include_exclude_list);
                    $list = str_replace('%field_name%', $settings_name.'[include_exclude_list]', $list);
                    echo $list;
                }
            echo '</div>';
        echo '</div>';
        ?>
        <script>
            var braapf_include_exclude_list_load,
            braapf_include_or_exclude_class;
            (function ($){$(document).ready(function() {
                braapf_include_exclude_list_load = function() {
                    berocket_show_element_hooked_data.push('#braapf_attribute');
                    berocket_show_element_hooked_data.push('#braapf_custom_taxonomy');
                    berocket_show_element_hooked_data.push('#braapf_filter_type');
                    var taxonomy_name = braapf_get_current_taxonomy_name();
                    if( $('.braapf_include_exclude_list').data('taxonomy') != taxonomy_name ) {
                        var exclude_include_name = $('.braapf_include_exclude_list').data('name');
                        if( taxonomy_name !== false ) {
                            var data = {
                                'action': 'br_include_exclude_list',
                                'taxonomy_name': taxonomy_name,
                            };
                            $.post(ajaxurl, data, function(data) {
                                $('.braapf_include_exclude_list').data('taxonomy', taxonomy_name);
                                if( data ) {
                                    var replace_str = /%field_name%/g;
                                    data = data.replace(replace_str, exclude_include_name);
                                    $('.braapf_include_exclude_list').html(data);
                                } else {
                                    $('.braapf_include_exclude_list').text("");
                                }
                            });
                        }
                    }
                    return true;
                }
                braapf_include_or_exclude_class = function (show, element, data_string, init) {
                    berocket_show_element_callback(show, element, data_string, init);
                    if( show == "1" ) {
                        $('.braapf_include_exclude_list').removeClass('braapf_include').removeClass('braapf_exclude');
                        if($('#braapf_include_exclude_select').val() == 'include') {
                            $('.braapf_include_exclude_list').addClass('braapf_include');
                        } else {
                            $('.braapf_include_exclude_list').addClass('braapf_exclude');
                        }
                    }
                }
                berocket_show_element('.braapf_include_exclude_select', '{#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute!');
                berocket_show_element('.braapf_include_exclude_list', '({#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute!) && {#braapf_include_exclude_select} != "" && !braapf_include_exclude_list_load! == true', true, braapf_include_or_exclude_class);
            });})(jQuery);
        </script>
        <?php
    }
    static function price_ranges_generate_single_html($i, $ranges, $settings_name) {
        $j = $i - 1;
        $html = '<div class="berocket_ranges">';
            $html .= '<span class="berocket_ranges_from"><input type="number" min="1" '. 
                ($j == 0 ? 'name="' . $settings_name . '[ranges][]"' : 'readonly') . ' value="' . ($j == 0 ? $ranges[$j] : $ranges[$j] + 1) 
            . '"></span>';
            $html .= '<span class="berocket_ranges_glue"></span>';
            $html .= '<span class="berocket_ranges_to"><input type="number" min="1" name="' . $settings_name . '[ranges][]" value="' . $ranges[$i] . '"></span>';
            if( $i != 1 ) {
                $html .= '<a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a>';
            }
        $html .= '</div>';
        return $html;
    }
    static function price_ranges($settings_name, $braapf_filter_settings) {
        $ranges = br_get_value_from_array($braapf_filter_settings, 'ranges', '');
        if ( ! empty( $ranges ) && is_array( $ranges ) && count( $ranges ) > 0 ) {
            foreach($ranges as $i => $range ) {
                if( empty($range) ) {
                    unset($ranges[$i]);
                }
            }
        }
        if ( empty( $ranges ) || ! is_array( $ranges ) || count( $ranges ) <= 1 ) {
            $ranges = array(1,100,1000);
        }
        $ranges = array_values($ranges);
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="berocket_ranges_block  braapf_full_select_full"><div>';
            for($i = 1; $i < count($ranges); $i++) {
                echo self::price_ranges_generate_single_html($i, $ranges, $settings_name);
            }
            ?><a href="#add" class="berocket_add_ranges" data-html='<div class="berocket_ranges"><input type="number" min="1" name="<?php echo $settings_name; ?>[ranges][]" value=""><a href="#remove" class="berocket_remove_ranges"><i class="fa fa-times"></i></a></div>'>
                <i class="fa fa-plus"></i>
            </a>
            <script>
                jQuery(document).on('click', '.berocket_add_ranges',function(event) {
                    event.preventDefault();
                    var html = '<?php echo self::price_ranges_generate_single_html(2, array('', '0', ''), $settings_name); ?>';
                    jQuery(this).before(jQuery(html));
                    jQuery('.berocket_ranges_to input').trigger('change');
                });
                jQuery(document).on('change', '.berocket_ranges_to input', function() {
                    var value = jQuery(this).val();
                    var next = jQuery(this).closest('.berocket_ranges').next();
                    if( next.length && next.is('.berocket_ranges') ) {
                        var setValue = value
                        if( setValue != '' ) {
                            setValue = parseInt(setValue) + 1;
                        }
                        next.find('.berocket_ranges_from input').val(setValue);
                    }
                });
            </script><?php
            echo '</div></div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_range_display_type braapf_half_select_full">';
                $range_types = array(
                    array('value' => '',        'name' => __('1.00-100.00, 101.00-200.00, 201.00-1000.00', 'BeRocket_AJAX_domain')),
                    array('value' => 'same',    'name' => __('1.00-100.00, 100.00-200.00, 200.00-1000.00', 'BeRocket_AJAX_domain')),
                    array('value' => 'decimal', 'name' => __('1.00-99.99, 100.00-199.99, 200.00-999.99', 'BeRocket_AJAX_domain')),
                );
                $range_display_type = br_get_value_from_array($braapf_filter_settings, 'range_display_type', '0');
                echo '<select id="braapf_range_display_type" name="'.$settings_name.'[range_display_type]">';
                    echo '<optgroup label="' . __('Ranges: 1,100,200,1000', 'BeRocket_AJAX_domain') . '">';
                    foreach($range_types as $range_type) {
                        echo '<option value="'.$range_type['value'].'"'.($range_display_type == $range_type['value'] ? ' selected' : '').'>'.$range_type['name'].'</option>';
                    }
                    echo '</optgroup>';
                echo '</select>';
            echo '</div>';
            echo '<div class="braapf_range_display_type braapf_half_select_full">';
                $hide_first_last_ranges = br_get_value_from_array($braapf_filter_settings, 'hide_first_last_ranges', '0');
                echo '<p>';
                    echo '<input id="braapf_hide_first_last_ranges" type="checkbox" name="' . $settings_name . '[hide_first_last_ranges]"' . ( empty($hide_first_last_ranges) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_hide_first_last_ranges">'.__('Hide first and last ranges without products', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_show_last_to_infinity braapf_half_select_full">';
                $show_last_to_infinity = br_get_value_from_array($braapf_filter_settings, 'show_last_to_infinity', '0');
                echo '<p>';
                    echo '<input id="braapf_show_last_to_infinity" type="checkbox" name="' . $settings_name . '[show_last_to_infinity]"' . ( empty($show_last_to_infinity) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_show_last_to_infinity">'.__('Replace the last range value with the infinity symbol', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_to_infinity_text braapf_half_select_full">';
                $to_infinity_text = br_get_value_from_array($braapf_filter_settings, 'to_infinity_text', '');
                echo '<label for="braapf_to_infinity_text">'.__('Show last range to the infinity', 'BeRocket_AJAX_domain').'</label>';
                echo '<input id="braapf_to_infinity_text" type="text" name="' . $settings_name . '[to_infinity_text]" value="'.$to_infinity_text.'" placeholder="&#8734;">';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.berocket_ranges_block', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_range_display_type', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_show_last_to_infinity', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
            berocket_show_element('.braapf_to_infinity_text', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && {#braapf_show_last_to_infinity} == true');
        });
        </script>
        <?php
    }
    static function show_product_count_per_attr ($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_show_product_count_per_attr braapf_half_select_full">';
                $show_product_count_per_attr = br_get_value_from_array($braapf_filter_settings, 'show_product_count_per_attr', '0');
                echo '<p>';
                    echo '<input id="braapf_show_product_count_per_attr" type="checkbox" name="' . $settings_name . '[show_product_count_per_attr]"' . ( empty($show_product_count_per_attr) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_show_product_count_per_attr">'.__('Show products count per attribute value?', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_product_count_per_attr_style braapf_half_select_full">';
				echo '<label for="braapf_product_count_per_attr_style">'.__('Products count per attribute value style', 'BeRocket_AJAX_domain').'</label>';
				$count_per_attr_styles = array(
                    array('value' => '',        'name' => __('Value(5)', 'BeRocket_AJAX_domain')),
                    array('value' => 'space',    'name' => __('Value (5)', 'BeRocket_AJAX_domain')),
                    array('value' => 'value', 'name' => __('Value( 5 )', 'BeRocket_AJAX_domain')),
                    array('value' => 'space_value', 'name' => __('Value ( 5 )', 'BeRocket_AJAX_domain')),
                );
                $product_count_per_attr_style = br_get_value_from_array($braapf_filter_settings, 'product_count_per_attr_style', '');
                echo '<select id="braapf_product_count_per_attr_style" name="'.$settings_name.'[product_count_per_attr_style]">';
                    foreach($count_per_attr_styles as $count_per_attr_style) {
                        echo '<option value="'.$count_per_attr_style['value'].'"'.($product_count_per_attr_style == $count_per_attr_style['value'] ? ' selected' : '').'>'.$count_per_attr_style['name'].'</option>';
                    }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
            jQuery(document).ready(function() {
                berocket_show_element('.braapf_show_product_count_per_attr', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
                berocket_show_element('.braapf_product_count_per_attr_style', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || ( !braapf_current_template! == "checkbox" && ( ( !braapf_current_specific! != "color" && !braapf_current_specific! != "image" ) || ({#braapf_use_value_with_color} != "" && {#braapf_use_value_with_color} != "tooltip" ) ) ) ) && {#braapf_show_product_count_per_attr} == true');
            });
        </script>
        <?php
    }
    static function slider_numeric($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_slider_numeric braapf_full_select_full">';
                $slider_numeric = br_get_value_from_array($braapf_filter_settings, 'slider_numeric', '0');
                echo '<p>';
                    echo '<input id="braapf_slider_numeric" type="checkbox" name="' . $settings_name . '[slider_numeric]"' . ( empty($slider_numeric) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_slider_numeric">'.__('Use as numeric', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_slider_numeric', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "slider" || !braapf_current_template! == "new_slider") && {#braapf_filter_type} != "price"');
        });
        </script>
        <?php
    }
    static function values_per_row($settings_name, $braapf_filter_settings){
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_values_per_row braapf_full_select_full">';
                echo '<label for="braapf_values_per_row">' . __('Values per row', 'BeRocket_AJAX_domain') . '</label>';
                $values_per_row = br_get_value_from_array($braapf_filter_settings, 'values_per_row', '');
                echo '<select id="braapf_values_per_row" name="'.$settings_name.'[values_per_row]">';
                    echo '<option value=""'. ($values_per_row == "" ? ' selected' : '') .'>Default</option>';
                    echo '<option value="1"'.($values_per_row == "1" ? ' selected' : '').'>1</option>';
                    echo '<option value="2"'.($values_per_row == "2" ? ' selected' : '').'>2</option>';
                    echo '<option value="3"'.($values_per_row == "3" ? ' selected' : '').'>3</option>';
                    echo '<option value="4"'.($values_per_row == "4" ? ' selected' : '').'>4</option>';
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_values_per_row', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "checkbox"');
        });
        </script>
        <?php
    }
    static function child_parent($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_child_parent braapf_half_select_full">';
                $child_parent = br_get_value_from_array($braapf_filter_settings, 'child_parent', '');
                echo '<label for="braapf_child_parent">' . __('Child/Parent Limitation', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_child_parent" name="'.$settings_name.'[child_parent]">';
                    echo '<option value=""'.($child_parent == "" ? ' selected' : '').'>'.__('Disabled', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="depth"'.($child_parent == "depth" ? ' selected' : '').'>'.__('Child Count', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="parent"'.($child_parent == "parent" ? ' selected' : '').'>'.__('Parent', 'BeRocket_AJAX_domain').'</option>';
                    echo '<option value="child"'.($child_parent == "child" ? ' selected' : '').'>'.__('Child', 'BeRocket_AJAX_domain').'</option>';
                echo '</select>';
            echo '</div>';
            echo '<div class="braapf_child_parent_depth braapf_half_select_full">';
                $child_parent_depth = br_get_value_from_array($braapf_filter_settings, 'child_parent_depth', '');
                echo '<label for="braapf_child_parent_depth">' . __('Child depth', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_depth" type="text" name="' . $settings_name . '[child_parent_depth]" value="'.$child_parent_depth.'">';
            echo '</div>';
            echo '<div class="braapf_child_onew_count braapf_half_select_full">';
                $child_onew_count = br_get_value_from_array($braapf_filter_settings, 'child_onew_count', '');
                echo '<label for="braapf_child_onew_count">' . __('Child count', 'BeRocket_AJAX_domain') . '</label>';
                echo '<select id="braapf_child_onew_count" name="'.$settings_name.'[child_onew_count]">';
                for($i = 1; $i < 11; $i++) {
                    echo '<option value="'.$i.'"'.($child_onew_count == $i ? ' selected' : '').'>'.$i.'</option>';
                }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_child_parent_child braapf_full_select_full">';
                $child_parent_no_values = br_get_value_from_array($braapf_filter_settings, 'child_parent_no_values', '');
                echo '<label for="braapf_child_parent_no_values">' . __('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_no_values" type="text" name="' . $settings_name . '[child_parent_no_values]" value="'.$child_parent_no_values.'">';
                
                $child_parent_previous = br_get_value_from_array($braapf_filter_settings, 'child_parent_previous', '');
                echo '<label for="braapf_child_parent_previous">' . __('"Select previous" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_previous" type="text" name="' . $settings_name . '[child_parent_previous]" value="'.$child_parent_previous.'">';
                
                $child_parent_no_products = br_get_value_from_array($braapf_filter_settings, 'child_parent_no_products', '');
                echo '<label for="braapf_child_parent_no_products">' . __('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input id="braapf_child_parent_no_products" type="text" name="' . $settings_name . '[child_parent_no_products]" value="'.$child_parent_no_products.'">';
            echo '</div>';
            for($i = 1; $i < 11; $i++) {
                echo '<div class="braapf_child_parent_depth_'.$i.' braapf_half_select_full">';
                    $title = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'title'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_title">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('Title', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_title" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][title]" value="'.$title.'">';
                    
                    $no_values = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'no_values'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_no_values">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('"No values" messages', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_no_values" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][no_values]" value="'.$no_values.'">';
                    
                    $previous = br_get_value_from_array($braapf_filter_settings, array('child_onew_childs', $i, 'previous'), '');
                    echo '<label for="braapf_child_onew_childs_'.$i.'_previous">' . __('Child', 'BeRocket_AJAX_domain').' '.$i.' '.__('"Select previous" messages', 'BeRocket_AJAX_domain') . '</label>';
                    echo '<input id="braapf_child_onew_childs_'.$i.'_previous" type="text" name="' . $settings_name . '[child_onew_childs]['.$i.'][previous]" value="'.$previous.'">';
                    ?>
                    <script>
                    jQuery(document).ready(function() {
                        berocket_show_element('.braapf_child_parent_depth_<?php echo $i; ?>', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "depth" && {#braapf_child_onew_count} >= <?php echo $i; ?>');
                    });
                    </script>
                    <?php
                echo '</div>';
            }
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_child_parent', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true');
            berocket_show_element('.braapf_child_parent_depth', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "child"');
            berocket_show_element('.braapf_child_parent_child', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "child"');
            berocket_show_element('.braapf_child_onew_count', '{.braapf_widget_type input[type=radio]} == "filter" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox") && !braapf_current_taxonomy_hierarchical! == true && {#braapf_child_parent} == "depth"');
        });
        </script>
        <?php
    }
    static function date_style($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_date_change_month braapf_half_select_full">';
                $date_change_month = br_get_value_from_array($braapf_filter_settings, 'date_change_month', '0');
                echo '<p>';
                    echo '<input id="braapf_date_change_month" type="checkbox" name="' . $settings_name . '[date_change_month]"' . ( empty($date_change_month) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_date_change_month">'.__('Dates of the month Drop-down list', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_date_change_year braapf_half_select_full">';
                $date_change_year = br_get_value_from_array($braapf_filter_settings, 'date_change_year', '0');
                echo '<p>';
                    echo '<input id="braapf_date_change_year" type="checkbox" name="' . $settings_name . '[date_change_year]"' . ( empty($date_change_year) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_date_change_year">'.__('Dates of the year Drop-down list', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_date_style braapf_full_select_full">';
                echo '<label for="braapf_date_style">' . __('Date visual style', 'BeRocket_AJAX_domain') . '</label>';
                $date_style = br_get_value_from_array($braapf_filter_settings, 'date_style', '');
                echo '<select id="braapf_date_style" name="'.$settings_name.'[date_style]">';
                $date_styles = array(
                    'm/d/Y' => 'mm/dd/yyyy',
                    'd/m/Y' => 'dd/mm/yyyy',
                    'Y/m/d' => 'yyyy/mm/dd',
                    'Y/d/m' => 'yyyy/dd/mm',
                    'm-d-Y' => 'mm-dd-yyyy',
                    'd-m-Y' => 'dd-mm-yyyy',
                    'Y-m-d' => 'yyyy-mm-dd',
                    'Y-d-m' => 'yyyy-dd-mm',
                    'm.d.Y' => 'mm.dd.yyyy',
                    'd.m.Y' => 'dd.mm.yyyy',
                    'Y.m.d' => 'yyyy.mm.dd',
                    'Y.d.m' => 'yyyy.dd.mm',
                );
                foreach($date_styles as $date_style_val => $date_style_name) {
                    echo '<option value="'.$date_style_val.'"'.( $date_style == $date_style_val ? ' selected' : '' ).'>'.$date_style_name.'</option>';
                }
                echo '</select>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        jQuery(document).ready(function() {
            berocket_show_element('.braapf_date_change_month', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
            berocket_show_element('.braapf_date_change_year', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
            berocket_show_element('.braapf_date_style', '{.braapf_widget_type input[type=radio]} == "filter" && !braapf_current_template! == "datepicker"');
        });
        </script>
        <?php
    }
    static function datepicker_required($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_datepicker_important braapf_full_select_full">';
                echo '<h2><strong style="color:red;">'.__('IMPORTANT FOR DATEPICKER', 'BeRocket_AJAX_domain').'</strong></h2>';
                echo '<p>'.__('Datepicker required specific slug for attribute/taxonomy values', 'BeRocket_AJAX_domain').'</p>';
                echo '<p>'.__('Slug must be like', 'BeRocket_AJAX_domain').' <strong>YYYYMMDD</strong></p>';
                echo '<h3>'.__('Example:', 'BeRocket_AJAX_domain').'</h3>';
                echo '<p>'.__('For date', 'BeRocket_AJAX_domain').' <strong>2012/05/17</strong> '.__('slug must be', 'BeRocket_AJAX_domain').' <strong>20120517</strong></p>';
                echo '<h3>'.__('Current attribute/taxonomy values', 'BeRocket_AJAX_domain').'</h3>';
                echo '<p class="braapf_datepicker_important_current"></p>';
            echo '</div>';
        echo '</div>';
        ?>
        <script>
        function braapf_datepicker_required_taxonomy_check(show, element, data_string, init) {
            berocket_show_element_callback(show, element, data_string, init);
            var taxonomy_name = braapf_get_current_taxonomy_name();
            var template = braapf_current_template();
            if ( show == '1' && template == 'datepicker' ) {
                var data = {
                    'action': 'braapf_datepicker_important_current',
                    'tax_name': taxonomy_name
                };
                jQuery.post(ajaxurl, data, function(data) {
                    jQuery('.braapf_datepicker_important_current').html(data);
                });
                return true;
            } else {
                jQuery('.braapf_datepicker_important_current').text("");
                return false;
            }
        }
        berocket_show_element('.braapf_datepicker_important', '({#braapf_filter_type} == !braapf_all_sameas_custom_taxonomy! || {#braapf_filter_type} == !braapf_all_sameas_attribute!) && !braapf_current_template! == "datepicker"', true, braapf_datepicker_required_taxonomy_check);
        </script>
        <?php
    }
    static function datepicker_important_current() {
        if ( current_user_can( 'manage_woocommerce' ) && ! empty($_POST['tax_name']) ) {
            $taxonomy_name = $_POST['tax_name'];
            $terms = get_terms( $taxonomy_name, array( 'hide_empty' => false ) );
            if( is_array($terms) && count($terms) > 0 ) {
                echo '<table style="width: 100%;">';
                echo '<tr>';
                echo '<th>'.__('OK', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Name', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Slug', 'BeRocket_AJAX_domain').'</th>';
                echo '<th>'.__('Date detected', 'BeRocket_AJAX_domain').'</th>';
                echo '</tr>';
                foreach($terms as $term) {
                    $is_date = preg_match('/^(\d{4})((?:0[1-9])|(?:1[0-2]))((?:0[1-9])|(?:[1-2][0-9])|(?:3[0-1]))$/', $term->slug, $matches);
                    echo '<tr>';
                    echo '<td><strong><i class="fa ' . ($is_date ? 'fa-check' : 'fa-times') . '"></strong></td>';
                    echo '<td><a href="'.admin_url('term.php?taxonomy='.$term->taxonomy.'&tag_ID='.$term->term_id).'" target="_blank">'.$term->name.'</a></td>';
                    echo '<td>'.$term->slug.'</td>';
                    echo '<td>';
                    if( $is_date ) {
                        echo __('Year:', 'BeRocket_AJAX_domain').' <strong>'.$matches[1].'</strong>'.'; '.
                        __('Month:', 'BeRocket_AJAX_domain').' <strong>'.$matches[2].'</strong>'.'; '.
                        __('Day:', 'BeRocket_AJAX_domain').' <strong>'.$matches[3].'</strong>';
                    } else {
                        echo __('Slug is incorrect', 'BeRocket_AJAX_domain');
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                _e('Empty attribute/taxonomy', 'BeRocket_AJAX_domain');
            }
        }
        wp_die();
    }
    static function custom_text_price_ranges($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_custom_price_ranges braapf_half_select_full">';
                $custom_price_ranges = br_get_value_from_array($braapf_filter_settings, 'custom_price_ranges', '0');
                echo '<p>';
                    echo '<input id="braapf_custom_price_ranges" type="checkbox" name="' . $settings_name . '[custom_price_ranges]"' . ( empty($custom_price_ranges) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_custom_price_ranges">'.__('Use specific Price Ranges text', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
            echo '<div class="braapf_custom_price_ranges_text braapf_half_select_full">';
                $custom_price_ranges_text = br_get_value_from_array($braapf_filter_settings, 'custom_price_ranges_text', '');
                echo '<label for="braapf_custom_price_ranges_text">'.__('Text will be used for Price Ranges', 'BeRocket_AJAX_domain')
                .'<span id="braapf_custom_price_ranges_text_info" class="dashicons dashicons-editor-help"></span></label>';
                echo '<input id="braapf_custom_price_ranges_text" type="text" name="' . $settings_name . '[custom_price_ranges_text]" value="'.$custom_price_ranges_text.'">';
            echo '</div>';
        echo '</div>';
        $tooltip_text = '<strong>' . __('You can use some replacements', 'BeRocket_AJAX_domain') . '</strong>'
        . '<ul>'
        . '<li><i>%from%</i> - ' . __('first value, from this price', 'BeRocket_AJAX_domain') . '</li>'
        . '<li><i>%to%</i> - ' . __('second value, to this price', 'BeRocket_AJAX_domain') . '</li>'
        .'<li><i>%cur_symbol%</i> - ' . __('currency symbol($)', 'BeRocket_AJAX_domain') . '</li>'
        . '<li><i>%cur_slug%</i> - ' . __('currency code(USD)', 'BeRocket_AJAX_domain') . '</li>'
        .'</ul>';
        BeRocket_tooltip_display::add_tooltip(
            array(
                'appendTo'      => 'document.body',
                'arrow'         => true,
                'interactive'   => true, 
                'placement'     => 'top'
            ),
            $tooltip_text,
            '#braapf_custom_price_ranges_text_info'
        );
        ?>
        <script>
        berocket_show_element('.braapf_custom_price_ranges', '{#braapf_filter_type} == "price" && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
        berocket_show_element('.braapf_custom_price_ranges_text', '{#braapf_filter_type} == "price" && {#braapf_custom_price_ranges} == true && (!braapf_current_template! == "select" || !braapf_current_template! == "checkbox")');
        </script>
        <?php
    }
    function color_elements() {
        add_filter('BeRocket_AAPF_template_single_item', array($this, 'multiple_color'), 1000, 4);
    }
    static function multiple_color($template, $term, $i, $berocket_query_var_title) {
        if( ! empty($berocket_query_var_title['new_style']) && berocket_isset($berocket_query_var_title['new_style']['specific']) == 'color' ) {
            $color_list = array('color', 'color_2', 'color_3', 'color_4');
            $meta_color = array();
            foreach($color_list as $color_name) {
                $berocket_term = get_metadata( 'berocket_term', $term->term_id, $color_name );
                $berocket_term = br_get_value_from_array($berocket_term, 0, '');
                if( empty($berocket_term) && $color_name != 'color') continue;
                $meta_color[] = $berocket_term;
            }
            if( count($meta_color) > 1 ) {
                unset($template['content']['label']['content']['color']['attributes']['style']['bg-color']);
                $multiple_colors = array();
                foreach($meta_color as $i => $meta_color_single) {
                    $meta_color_single = str_replace('#', '', $meta_color_single);
                    $multiple_colors['color_'.$i] = array(
                        'type'          => 'tag',
                        'tag'           => 'span',
                        'attributes'    => array(
                            'class'         => array(
                                'main'          => 'bapf_clr_multi_singl',
                                'number'        => 'bapf_clr_multi_singl_'.$i,
                            ),
                            'style'         => array(
                                'bg-color'      => 'background-color: #'.$meta_color_single.';'
                            ),
                        )
                    );
                }
                $template['content']['label']['content']['color']['content']['multiple-color'] = array(
                    'type'          => 'tag',
                    'tag'           => 'span',
                    'attributes'    => array(
                        'class'         => array(
                            'main'          => 'bapf_clr_multi',
                            'number'        => 'bapf_clr_multi_'.count($meta_color),
                        ),
                    ),
                    'content'       => $multiple_colors
                );
            }
        }
        return $template;
    }
    static function stock_status_recount($meta_query, $terms, $taxonomy_data) {
        if( ! empty($taxonomy_data['taxonomy']) && $taxonomy_data['taxonomy'] == '_stock_status' ) {
            $meta_query = self::remove_all_berocket_meta_query($meta_query, $taxonomy_data['taxonomy']);
        }
        return $meta_query;
    }
    static function remove_all_berocket_meta_query($meta_query, $taxonomy = FALSE, $inside = FALSE ) {
        global $wpdb;
        if( is_array($meta_query) ) {
            $md5_exist = array();
            foreach($meta_query as $key => $value) {
                if( $key === 'relation' ) continue;
                if( ! $inside ) {
                    if( in_array(md5(json_encode($value)), $md5_exist) ) {
                        unset($meta_query[$key]);
                        continue;
                    }
                    $md5_exist[] = md5(json_encode($value));
                }
                if( array_key_exists('relation', $value) ) {
                    $value = self::remove_all_berocket_meta_query($value, $taxonomy, true);
                    if( $value === FALSE ) {
                        unset($meta_query[$key]);
                    } else {
                        $meta_query[$key] = $value;
                    }
                } elseif( isset($value['key']) && ($taxonomy === FALSE || $taxonomy == $value['key']) ) {
                    unset($meta_query[$key]);
                }
            }
            if( count($meta_query) == 1 && isset($meta_query['relation']) ) {
                $meta_query = ( $inside ? FALSE : array() );
            }
        }
        return $meta_query;
    }
    public static function datepicker_fix_instance($instance, $args, $set_query_var_title) {
         if( in_array(br_get_value_from_array($set_query_var_title, 'new_template'), array('datepicker')) ) {
            $instance['type']               = 'slider';
            $instance['order_values_by']    = 'Numeric';
            $instance['order_values_type']  = 'asc';
            $instance['height']             = '';
        }
        return $instance;
    }
    public static function value_icon_datepicker($template_content, $terms, $berocket_query_var_title) {
        if( $berocket_query_var_title['new_template'] == 'datepicker' ) {
            if( ! empty($berocket_query_var_title['icon_before_value']) ) {
                $icon = $berocket_query_var_title['icon_before_value'];
                $icon_element = array(
                    'type'          => 'tag',
                    'tag'           => 'i',
                    'attributes'    => array(
                        'class'         => array(
                            'fa'
                        )
                    ),
                    'content' => array()
                );
                if( substr( $icon, 0, 3) == 'fa-' ) {
                    $icon_element['attributes']['class']['icon'] = $icon;
                } else {
                    $icon_element['content']['icon'] = array(
                        'type'          => 'tag_open',
                        'tag'           => 'img',
                        'attributes'    => array(
                            'class'         => array(
                                'berocket_widget_icon'
                            ),
                            'src'           => $icon,
                            'alt'           => ''
                        )
                    );
                }
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'],
                    'input',
                    array(
                        'icon_before' => $icon_element,
                    ),
                    true
                );
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'],
                    'input',
                    array(
                        'icon_before' => $icon_element,
                    ),
                    true
                );
            }
            if( ! empty($berocket_query_var_title['icon_after_value']) ) {
                $icon = $berocket_query_var_title['icon_after_value'];
                $icon_element = array(
                    'type'          => 'tag',
                    'tag'           => 'i',
                    'attributes'    => array(
                        'class'         => array(
                            'fa'
                        )
                    ),
                    'content' => array()
                );
                if( substr( $icon, 0, 3) == 'fa-' ) {
                    $icon_element['attributes']['class']['icon'] = $icon;
                } else {
                    $icon_element['content']['icon'] = array(
                        'type'          => 'tag_open',
                        'tag'           => 'img',
                        'attributes'    => array(
                            'class'         => array(
                                'berocket_widget_icon'
                            ),
                            'src'           => $icon,
                            'alt'           => ''
                        )
                    );
                }
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['from']['content'],
                    'input',
                    array(
                        'icon_after_price' => $icon_element
                    )
                );
                $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'] = berocket_insert_to_array (
                    $template_content['template']['content']['filter']['content']['datepicker_all']['content']['to']['content'],
                    'input',
                    array(
                        'icon_after_price' => $icon_element
                    )
                );
            }
        }
        return $template_content;
    }
    static function parent_product_cat($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_parent_product_cat braapf_half_select_full">';
                $custom_taxonomies_list = braapf_single_filter_edit_elements::get_custom_taxonomies();
                foreach($custom_taxonomies_list as $value => $data) {
                    if( ! empty($data['hierarchical']) ) {
                        $parent_product_cat = br_get_value_from_array($braapf_filter_settings, 'parent_'.$value, '');
                        echo '<div class="braapf_all_parent_product_cat braapf_parent_product_cat_'.$value.'">';
                            echo '<label for="braapf_parent_product_cat_'.$value.'">' . __('Display only child of', 'BeRocket_AJAX_domain') . '</label>';
                            $hrterms = berocket_aapf_get_terms(array(
                                'taxonomy'          => $value,
                                'hide_empty'        => false
                            ), array(
                                'disable_recount'   => true,
                                'hierarchical'      => true
                            ));
                            echo '<select id="braapf_parent_product_cat_'.$value.'" name="'.$settings_name.'[parent_'.$value.']">';
                                echo '<option value="">' . __('Display All', 'BeRocket_AJAX_domain') . ' '.$data['name'].'</option>';
                                echo '<option value="bapf1level"'.($parent_product_cat == "bapf1level" ? ' selected' : '').'>'.__('Start from parent values', 'BeRocket_AJAX_domain').'</option>';
                                echo '<option value="bapf4current"'.($parent_product_cat == "bapf4current" ? ' selected' : '').'>'.__('Child for current page value', 'BeRocket_AJAX_domain').'</option>';
                                echo '<optgroup label="'.__('Child for value:', 'BeRocket_AJAX_domain').'">';
                                foreach($hrterms as $hrterm) {
                                    echo '<option value="'.$hrterm->term_id.'"'.($parent_product_cat == $hrterm->term_id ? ' selected' : '').'>';
                                    for( $i = 0; $i < $hrterm->depth; $i++ ) {
                                        echo '- ';
                                    }
                                    echo $hrterm->name.'</option>';
                                }
                                echo '</optgroup>';
                            echo '</select>';
                        echo '</div>';
                        ?>
                        <script>
                            jQuery(document).ready(function() {
                                berocket_show_element('.braapf_parent_product_cat_<?php echo $value; ?>', '!braapf_current_attribute! == "<?php echo $value; ?>"');
                            });
                        </script>
                        <?php
                    }
                }
            echo '</div>';
            echo '<div class="braapf_depth_count braapf_half_select_full">';
                $depth_count = br_get_value_from_array($braapf_filter_settings, 'depth_count', '0');
                echo '<label for="braapf_depth_count">' . __('Depth level', 'BeRocket_AJAX_domain') . '</label>';
                echo '<input min=0 id="braapf_depth_count" type="number" name="'.$settings_name.'[depth_count]" value="'.$depth_count.'">';
            echo '</div>';
            ?>
            <script>
            braapf_braapf_parent_product_cat_enabled = function() {
                berocket_show_element_hooked_data.push('#braapf_attribute');
                berocket_show_element_hooked_data.push('#braapf_custom_taxonomy');
                berocket_show_element_hooked_data.push('#braapf_filter_type');
                berocket_show_element_hooked_data.push('.braapf_all_parent_product_cat select');
                if( braapf_current_taxonomy_hierarchical() ) {
                    var attribute = braapf_current_attribute();
                    if( jQuery('#braapf_parent_product_cat_'+attribute).length && jQuery('#braapf_parent_product_cat_'+attribute).val() ) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            jQuery(document).ready(function() {
                berocket_show_element('.braapf_depth_count', '!braapf_braapf_parent_product_cat_enabled! != "" && !braapf_braapf_parent_product_cat_enabled! != false');
            });
            </script>
            <?php
        echo '</div>';
    }
    public static function correct_terms_child_parent($terms, $instance, $get_terms_args = false, $get_terms_advanced = false, $set_query_var_title = array()) {
        if( isset($terms) && is_array($terms) && count( $terms ) < 1 && $instance['child_parent'] == 'child' ) {
            $terms = array('empty_child' => $get_terms_args);
            
        }
        return $terms;
    }
    public static function correct_terms_child_parent_fix($set_query_var_title, $type, $instance, $args, $terms) {
        if( $instance['child_parent'] == 'child' && count($terms) == 1 
        && array_key_exists('empty_child', $terms) ) {
            $selected_terms_id = false;
            $selected_terms = br_get_selected_term( $terms['empty_child']['taxonomy'] );
            
            foreach( $selected_terms as $selected_term ) {
                $ancestors = get_ancestors( $selected_term, $terms['empty_child']['taxonomy'] );
                if( count( $ancestors ) >= ( $set_query_var_title['child_parent_depth'] - 1 ) ) {
                    $selected_terms_id = true;
                    break;
                }
            }
            if( $selected_terms_id ) {
                $set_query_var_title['child_parent_previous'] = $set_query_var_title['child_parent_no_values'];
            }
            $set_query_var_title['terms'] = array();
            if( ! empty($set_query_var_title['child_parent_previous']) ) {
                set_query_var( 'berocket_query_var_title', $set_query_var_title);
                br_get_template_part('paid/child_empty');
            }
        }
        return $set_query_var_title;
    }
    static function enable_slider_inputs($settings_name, $braapf_filter_settings) {
        echo '<div class="braapf_attribute_setup_flex">';
            echo '<div class="braapf_enable_slider_inputs braapf_full_select_full">';
                $enable_slider_inputs = br_get_value_from_array($braapf_filter_settings, 'enable_slider_inputs', '0');
                echo '<p>';
                    echo '<input id="braapf_enable_slider_inputs" type="checkbox" name="' . $settings_name . '[enable_slider_inputs]"' . ( empty($enable_slider_inputs) ? '' : ' checked' ) . ' value="1">';
                    echo '<label for="braapf_enable_slider_inputs">'.__('Enable Slider input fields', 'BeRocket_AJAX_domain').'</label>';
                echo '</p>';
            echo '</div>';
        echo '</div>';
    }
    static function search_field() {
        add_filter('braapf_new_widget_edit_page_widget_types', array(__CLASS__, 'widget_type_search_field'), 1, 1000); 
    }
    static function widget_type_search_field($widget_types) {
        $widget_types['search_field'] = array(
            'value' => 'search_field',
            'name'  => __('Search Field', 'BeRocket_AJAX_domain'),
            'image' => plugin_dir_url( BeRocket_AJAX_filters_file ) . 'assets/paid/search_field.png',
            'templates' => array('input'),
            'specific'  => array('elements'),
            'info'  => '<p>' . __('Create search input field for products.', 'BeRocket_AJAX_domain') . '</p>'
            . '<p>' . __('Default WordPress functionality for searching is being used.', 'BeRocket_AJAX_domain') . '</p>'
        );
        return $widget_types;
    }
    public function display_cat_value_limit($hide) {
        return false;
    }
}
new BeRocket_AAPF_paid_new();
if( ! function_exists( 'br_permalink_input_section_echo' ) ){
    /**
     * Permalink block in settings
     *
     */
    function br_permalink_input_section_echo() {
        echo '<div>'.__('Nice URLs settings', 'BeRocket_AJAX_domain').'</div>';
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $BeRocket_AAPF->br_get_template_part( 'paid/permalink_option' );
    }
}