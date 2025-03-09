<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_YooKassa_Blocks_Support extends AbstractPaymentMethodType
{
    private $gateway;

    protected $name = 'yookassa_epl'; // default gateway

    public function __construct($gateway_name = null)
    {
        if (!empty($gateway_name)) {
            $this->name = $gateway_name;
        }
        $this->initialize();
    }

    public function initialize()
    {
        // you can also initialize your payment gateway here
        $gateways = WC()->payment_gateways()->payment_gateways();
        $this->gateway = $gateways[$this->name];
    }

    public function is_active()
    {
        return !empty( $this->gateway->enabled ) && 'yes' === $this->gateway->enabled;
    }

    public function get_payment_method_script_handles()
    {

        $asset_path = plugin_dir_path(__DIR__) . 'build/yookassa-blocks-support.asset.php';
        $version = null;
        $dependencies = [];
        if (file_exists($asset_path)) {
            $asset = require $asset_path;
            $version = isset($asset['version']) ? $asset['version'] : $version;
            $dependencies = isset($asset['dependencies']) ? $asset['dependencies'] : $dependencies;
        }

        wp_register_script(
            'wc-yookassa-blocks-integration',
            plugin_dir_url(__DIR__) . 'build/yookassa-blocks-support.js',
            $dependencies,
            $version,
            true
        );

        return ['wc-yookassa-blocks-integration'];
    }

    public function get_payment_method_data()
    {
        return [
            'title' => $this->gateway->getTitle(),
            'description' => $this->gateway->getDescription(),
            'icon' => $this->gateway->icon,
            // if $this->gateway was initialized on line 15
            'supports' => array_filter($this->gateway->supports, [$this->gateway, 'supports']),
        ];
    }
}
