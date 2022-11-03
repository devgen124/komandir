<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\Product;

class GuidProductDataTab
{
    private static $instance = false;

    private function __construct()
    {
        \add_filter('woocommerce_product_data_tabs', [$this, 'addTab'], 10, 1);
        \add_action('woocommerce_product_data_panels', [$this, 'tabContent']);
        \add_action('woocommerce_process_product_meta', [$this, 'tabContentSave'], 10, 1);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param array $tabs
     *
     * @return array
     */
    public function addTab($tabs)
    {
        $tabs['itgalaxy-woocommerce-1c-product-guid'] = [
            'label' => \esc_html__('Exchange with 1C ', 'itgalaxy-woocommerce-1c'),
            'target' => 'itgalaxy-woocommerce-1c-product-guid',
        ];

        return $tabs;
    }

    /**
     * @return void
     */
    public function tabContent()
    {
        ?>
        <div id="itgalaxy-woocommerce-1c-product-guid" class="panel woocommerce_options_panel">
        <?php
        \woocommerce_wp_text_input(
            [
                'id' => 'product_1c_guid',
                'value' => \esc_attr(\get_post_meta(\get_the_ID(), '_id_1c', true)),
                'label' => \esc_html__('GUID', 'itgalaxy-woocommerce-1c'),
            ]
        ); ?>
        <hr class="show_if_variable">
        <p class="show_if_variable">
            <strong>
                <?php \esc_html_e('Each variation has its own field with information about GUID.', 'itgalaxy-woocommerce-1c'); ?>
            </strong>
        </p>
        </div>
        <?php
    }

    /**
     * @param int $postID
     *
     * @return void
     */
    public function tabContentSave($postID)
    {
        if (!isset($_POST['product_1c_guid'])) {
            return;
        }

        \update_post_meta($postID, '_id_1c', \wp_unslash($_POST['product_1c_guid']));
    }
}
