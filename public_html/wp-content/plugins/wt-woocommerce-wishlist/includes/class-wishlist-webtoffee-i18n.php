<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class Wishlist_Webtoffee_i18n {

    public function load_plugin_textdomain() {

        load_plugin_textdomain( 'wt-woocommerce-wishlist', false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/' );
    }

}