<?php
if( ! class_exists('BeRocket_AAPF_Template_Style_datepicker') ) {
    class BeRocket_AAPF_Template_Style_datepicker extends BeRocket_AAPF_Template_Style {
        function __construct() {
            $this->data = array(
                'slug'          => 'datepicker',
                'template'      => 'datepicker',
                'name'          => 'Datepicker',
                'file'          => __FILE__,
                'style_file'    => '/css/datepicker.css',
                'script_file'   => '/js/datepicker.js',
                'image'         => plugin_dir_url( __FILE__ ) . 'images/datepicker.png',
                'version'       => '1.0',
                'sort_pos'      => '1',
            );
            parent::__construct();
        }
        function enqueue_all() {
            BeRocket_AAPF::wp_enqueue_style( 'jquery-ui-datepick' );
            BeRocket_AAPF::wp_enqueue_script( 'jquery-ui-datepicker' );
            BeRocket_AAPF::wp_enqueue_script( 'berocket_aapf_jquery-slider-fix');
            parent::enqueue_all();
        }
    }
    new BeRocket_AAPF_Template_Style_datepicker();
}
