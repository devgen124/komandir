<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes;

use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class MetaBoxShopOrder
{
    private static $instance = false;

    private function __construct()
    {
        if (SettingsHelper::isEmpty('handle_get_order_status_change')) {
            return;
        }
        // https://developer.wordpress.org/reference/hooks/add_meta_boxes/
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function addMetaBox()
    {
        // https://developer.wordpress.org/reference/functions/add_meta_box/
        add_meta_box(
            'id_1c',
            esc_html__('Exchange with 1C ', 'itgalaxy-woocommerce-1c'),
            [$this, 'metaBoxContent'],
            'shop_order',
            'side',
            'high'
        );
    }

    public function metaBoxContent($post)
    {
        if (!$post || !isset($post->ID)) {
            return;
        }

        $requisites = get_post_meta($post->ID, '_itgxl_wc1c_order_requisites', true);

        if (empty($requisites)) {
            echo '<strong>'
                . esc_html__('no data', 'itgalaxy-woocommerce-1c')
                . '</strong>';
        } else {
            foreach ($requisites as $name => $value) {
                echo '<strong>'
                    . esc_html($name)
                    . ':</strong> '
                    . esc_html($value)
                    . '<br>';
            }
        }
    }
}
