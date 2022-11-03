<?php
if( ! class_exists('BeRocket_AAPF_Elemets_Style_input_default') ) {
    class BeRocket_AAPF_Elemets_Style_input_default extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'input_default',
                'template'      => 'input',
                'name'          => 'Default',
                'file'          => __FILE__,
                'style_file'    => 'css/search_field.css',
                'script_file'   => 'js/search_field.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/input_default.png',
                'version'       => '1.0',
                'specific'      => 'elements',
                'sort_pos'      => '1',
            );
            parent::__construct();
        }
        function filters($action = 'add') {
            parent::filters($action);
            $filter_func = 'add_filter';
            $action_func = 'add_action';
            if( $action != 'add' ) {
                $filter_func = 'remove_filter';
                $action_func = 'remove_action';
            }
            $filter_func('BeRocket_AAPF_template_full_element_content', array($this, 'template_element_full'), 10, 2);
        }
        function template_element_full($template, $berocket_query_var_title) {
            if( ! isset($template['template']['attributes']) || ! is_array($template['template']['attributes']) ) {
                $template['template']['attributes'] = array();
            }
            if( ! isset($template['template']['attributes']['class']) ) {
                $template['template']['attributes']['class'] = array();
            }
            if( ! is_array($template['template']['attributes']['class']) ) {
                $template['template']['attributes']['class'] = array($template['template']['attributes']['class']);
            }
            $template['template']['attributes']['class']['search'] = 'bapf_srch';
            return $template;
        }
    }
    new BeRocket_AAPF_Elemets_Style_input_default();
}
if( ! class_exists('BeRocket_AAPF_Elemets_Style_input_button_icon') ) {
    class BeRocket_AAPF_Elemets_Style_input_button_icon extends BeRocket_AAPF_Elemets_Style_input_default {
        function __construct() {
            parent::__construct();
            $this->data['slug'] = 'input_button_icon';
            $this->data['name'] = 'Button Icon';
            $this->data['image'] = plugin_dir_url( __FILE__ ) . 'images/input_button_icon.png';
            $this->data['version'] = '1.0';
            $this->data['sort_pos'] = '900';
        }
        function template_element_full($template, $berocket_query_var_title) {
            $template = parent::template_element_full($template, $berocket_query_var_title);
            $template['template']['attributes']['class']['button_icon'] = 'bapf_button_icon';
            $template['template']['content']['filter']['content']['form']['content']['button']['tag'] = 'span';
            $template['template']['content']['filter']['content']['form']['content']['button']['content'] = array('<i class="fa fa-search"></i>');
            return $template;
        }
    }
    new BeRocket_AAPF_Elemets_Style_input_button_icon();
}