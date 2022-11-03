<?php
class BeRocket_AAPF_custom_sidebar {
    function __construct() {
        add_filter('brfr_data_ajax_filters', array($this, 'settings_page'), 25);
        add_filter('brfr_ajax_filters_custom_sidebar', array($this, 'section_custom_sidebar'), 25, 3);
        //CUSTOM SIDEBAR
        add_filter('widgets_init', array($this, 'custom_sidebar'));
        add_shortcode( 'braapf_sidebar_button', array( $this, 'shortcode_sidebar_button' ) );
        if ( ! is_admin() ) {
            add_action('wp_head', array( $this, 'wp_head_sidebar'));
            add_action('wp_footer', array( $this, 'wp_footer_sidebar' ), 1);
            add_action('init', array( $this, 'wp_init_sidebar'));
        }
    }
    public function wp_init_sidebar() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        add_action( 'wp_enqueue_scripts', array( $BeRocket_AAPF, 'include_all_scripts' ) );
    }
    public function wp_head_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $option = $BeRocket_AAPF->get_option();
            add_action ( br_get_value_from_array($option, 'elements_position_hook', 'woocommerce_archive_description'), array($this, 'custom_sidebar_toggle'), 1 );
            BeRocket_AAPF::require_all_scripts();
            do_action('br_footer_script');
            BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
        }
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
        $theme_class = (empty($args['theme']) ? ( ( ! empty( $option['sidebar_collapse_theme'] ) ) ? ' theme-' . $option['sidebar_collapse_theme'] : '' ) : ' theme-'.$args['theme'] );
        $icon_theme_class = (empty($args['icon-theme']) ? ( ( ! empty( $option['sidebar_collapse_icon_theme'] ) ) ? ' icon-theme-' . $option['sidebar_collapse_icon_theme'] : '' ) : ' icon-theme-'.$args['icon-theme'] );
        $button_text = (empty($args['title']) ? __( 'SHOW FILTERS', 'BeRocket_AJAX_domain' ) : $args['title']);
        echo '<a href="#toggle-sidebar" class="berocket_ajax_filters_sidebar_toggle berocket_ajax_filters_toggle ' . $theme_class . ' ' . $icon_theme_class . '"><span><i></i><b></b><s></s></span>' . $button_text . '</a>';
        BeRocket_AAPF::wp_enqueue_style('berocket_aapf_widget-themes');
    }
    function settings_page($data) {
        $data['Design'] = berocket_insert_to_array(
            $data['Design'],
            'header_part_tooltip',
            array(
                'header_part_custom_sidebar' => array(
                    'section' => 'header_part',
                    "value"   => __('Custom Sidebar Styles', 'BeRocket_AJAX_domain'),
                ),
                'custom_sidebar' => array(
                    "section"   => "custom_sidebar",
                    "value"     => "",
                )
            ),
            true
        );
        return $data;
    }
    public function section_custom_sidebar() {
        $BeRocket_AAPF = BeRocket_AAPF::getInstance();
        $options = $BeRocket_AAPF->get_option();
        $html = '
            </table>
            <table class="form-table">
                <tbody>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Collapse Button style', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_theme]" style="display:none!important;" id="sidebar_collapse_theme_" value="" ' . ( empty( $options['sidebar_collapse_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/default.png" /></label>
                                    </div>';
                                for ( $theme_key = 1; $theme_key <= 10; $theme_key++ ) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_theme]" style="display:none!important;" id="sidebar_collapse_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_collapse_theme'] ) and $options['sidebar_collapse_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button/' . $theme_key . '.png" /></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Collapse Button Icon style', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider icon_size">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_icon_theme]" style="display:none!important;" id="sidebar_collapse_icon_theme_" value="" ' . ( empty( $options['sidebar_collapse_icon_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_icon_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/default.png" /></label>
                                    </div>';
                                for ( $theme_key = 1; $theme_key <= 6; $theme_key++ ) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_collapse_icon_theme]" style="display:none!important;" id="sidebar_collapse_icon_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_collapse_icon_theme'] ) and $options['sidebar_collapse_icon_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_collapse_icon_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-button-icon/' . $theme_key . '.png" /></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                    <tr class="berocket_group_is_hide_theme_option_data">
                        <th class="row">' . __('Sidebar Shadow', 'BeRocket_AJAX_domain') . '</th>
                        <td>
                            <div class="berocket_group_is_hide_theme_option_slider slider_shadow">';
                                $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_shadow_theme]" style="display:none!important;" id="sidebar_shadow_theme_" value="" ' . ( empty( $options['sidebar_shadow_theme'] ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_shadow_theme_"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-shadow/default.png" />
                                        <span>'.__('Dark', 'BeRocket_AJAX_domain').'</span></label>
                                    </div>';
                                $shadow_themes = array(
                                    '1' => __('No Shadow', 'BeRocket_AJAX_domain'),
                                    '2' => __('White', 'BeRocket_AJAX_domain'),
                                );
                                foreach($shadow_themes as $theme_key => $theme_name) {
                                    $html .= '
                                    <div>
                                        <input type="radio" name="br_filters_options[sidebar_shadow_theme]" style="display:none!important;" id="sidebar_shadow_theme_' . $theme_key . '" value="' . $theme_key . '" ' . ( ( ! empty( $options['sidebar_shadow_theme'] ) and $options['sidebar_shadow_theme'] == $theme_key ) ? ' checked' : '' ) . ' />
                                        <label for="sidebar_shadow_theme_' . $theme_key . '"><img src="' . plugin_dir_url(BeRocket_AJAX_filters_file) . 'images/themes/sidebar-shadow/' . $theme_key . '.png" />
                                        <span>'.$theme_name.'</span></label>
                                    </div>';
                                }
                                $html .= '
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="framework-form-table berocket_framework_menu_design">';
        return $html;
    }

    public function custom_sidebar() {
        register_sidebar(
            array (
                'name' => __( 'BeRocket AJAX Filters', 'BeRocket_AJAX_domain' ),
                'id' => 'berocket-ajax-filters',
                'description' => __( 'Sidebar for BeRocket AJAX Filters', 'BeRocket_AJAX_domain' ),
                'before_widget' => '<div class="berocket-widget-content">',
                'after_widget' => "</div>",
                'before_title' => '<h3 class="berocket-widget-title">',
                'after_title' => '</h3>',
            )
        );
    }
    public function wp_footer_sidebar() {
        if ( is_active_sidebar( 'berocket-ajax-filters' ) ) {
            wp_enqueue_script( 'braapf_custom_sidebar',
            plugins_url( 'js/custom_sidebar.js', __FILE__ ),
            array( 'jquery' ),
            BeRocket_AJAX_filters_version );
            $BeRocket_AAPF = BeRocket_AAPF::getInstance();
            $options = $BeRocket_AAPF->get_option();
            echo "<div id='berocket-ajax-filters-sidebar' class='" . ( ! empty( $options['sidebar_shadow_theme'] ) ? 'sidebar-theme-' . $options['sidebar_shadow_theme'] : '' ) . "'>";
            echo "<a href='#close-sidebar' id='berocket-ajax-filters-sidebar-close'>" . __('Close &#10005;', 'BeRocket_AJAX_domain') . "</a>";
            dynamic_sidebar( 'berocket-ajax-filters' );
            echo "</div>";
            echo "<div id='berocket-ajax-filters-sidebar-shadow'></div>";
        }
    }
}
new BeRocket_AAPF_custom_sidebar();
