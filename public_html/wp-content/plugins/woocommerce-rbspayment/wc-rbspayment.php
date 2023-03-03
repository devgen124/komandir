<?php
/*
    Plugin Name: WooCommerce RBSPayment Checkout Gateway
    Plugin URI:
    Description: Allows to use a payment gateway with the WooCommerce.
    Version: 3.5.3
    Author: RBSPayment
    Author URI: http://www.rbspayment.ru/
    Text Domain: wc-rbspayment-text-domain
    Domain Path: /lang
 */


if (!defined('ABSPATH')) exit;
require_once(ABSPATH . 'wp-admin/includes/plugin.php');
require_once(__DIR__ . '/include.php');

add_filter('plugin_row_meta', 'rbs_register_plugin_links', 10, 2);
function rbs_register_plugin_links($links, $file)
{
    $base = plugin_basename(__FILE__);
    if ($file == $base) {
        $links[] = '<a href="admin.php?page=wc-settings&tab=checkout&section=rbspayment">' . __('Settings', 'woocommerce') . '</a>';
    }
    return $links;
}


add_action('plugins_loaded', 'woocommerce_rbspayment', 0);
function woocommerce_rbspayment()
{

    load_plugin_textdomain('wc-rbspayment-text-domain', false, dirname(plugin_basename(__FILE__)) . '/lang');

    if (!class_exists('WC_Payment_Gateway'))
        return;
    if (class_exists('WC_RBSPayment'))
        return;

    class WC_RBSPayment extends WC_Payment_Gateway
    {
        public $currency_codes = array(
            'USD' => '840',
            'UAH' => '980',
            'RUB' => '643',
            'RON' => '946',
            'KZT' => '398',
            'KGS' => '417',
            'JPY' => '392',
            'GBR' => '826',
            'EUR' => '978',
            'CNY' => '156',
            'BYR' => '974',
            'BYN' => '933'
        );

        public function __construct()
        {

            $this->id = 'rbspayment';
            $icon_path = is_file(__DIR__ . DIRECTORY_SEPARATOR . 'logo.png') ? plugin_dir_url(__FILE__) . 'logo.png' : null;

            $this->method_title = "RBSPayment";
            $this->method_description = __('Online acquiring and payment processing.', 'wc-rbspayment-text-domain');

            // Load the settings
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables
            $this->title = $this->get_option('title');
            $this->merchant = $this->get_option('merchant');
            $this->password = $this->get_option('password');
            $this->test_mode = $this->get_option('test_mode');
            $this->stage_mode = $this->get_option('stage_mode');
            $this->description = $this->get_option('description');
            $this->order_status = $this->get_option('order_status');
            $this->icon = $icon_path;

            $this->send_order = $this->get_option('send_order');
            $this->tax_system = $this->get_option('tax_system');
            $this->tax_type = $this->get_option('tax_type');

            $this->success_url = $this->get_option('success_url');
            $this->fail_url = $this->get_option('fail_url');
            $this->FFDVersion = $this->get_option('FFDVersion');
            $this->paymentMethodType = $this->get_option('paymentMethodType');
            $this->paymentObjectType = $this->get_option('paymentObjectType');
            $this->paymentObjectType_delivery = $this->get_option('paymentMethodType_delivery');

            $this->pData = get_plugin_data(__FILE__);

            $this->logging = RBS_ENABLE_LOGGING;
            $this->fiscale_options = RBS_ENABLE_FISCALE_OPTIONS;
            $this->orderNumberById = true; //false - must be installed WooCommerce Sequential Order Numbers

            $this->allowCallbacks = RBS_ENABLE_CALLBACK;
            $this->enable_for_methods = $this->get_option( 'enable_for_methods', array() );

            // Endpoints
            $this->test_url = RBS_TEST_URL;
            $this->prod_url = RBS_PROD_URL;

            // for migrate to new payment gateway
            if (defined('RBS_PROD_URL_ALT') && defined('RBS_PROD_URL_ALT_PREFIX')) {
                if (substr($this->merchant, 0, strlen(RBS_PROD_URL_ALT_PREFIX)) == RBS_PROD_URL_ALT_PREFIX) {
                    $this->prod_url = RBS_PROD_URL_ALT;
                } else {
                    //disable callbacks for old gateway version
                    $this->allowCallbacks = false;
                }
            }

            // Actions
            add_action('valid-rbspayment-standard-ipn-request', array($this, 'successful_request'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

            // Save options
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

//            add_action( 'woocommerce_payment_complete_order_status', 'rbspayment_change_status_function' );

            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }

            $this->callback();
        }

        public function process_admin_options()
        {
            if ($this->test_mode == 'yes') {
                $action_adr = $this->test_url;

                $gate_url = str_replace("payment/rest", "mportal-uat/mvc/public/merchant/update", $action_adr);

                // "mportal-uat" error for some ONE bank
                //$gate_url = str_replace("rest", "mportal/mvc/public/merchant/update", $action_adr);

            } else {
                $action_adr = $this->prod_url;
                $gate_url = str_replace("payment/rest", "mportal/mvc/public/merchant/update", $action_adr);
            }

            $gate_url .= substr($this->merchant, 0, -4); // we guess username = login w/o "-api"

            $callback_addresses_string = get_option('siteurl') . "?wc-api=WC_RBSPayment&rbspayment=callback";
            if ($this->allowCallbacks !== false) {
                $response = $this->_updateRBSCallback($this->merchant, $this->password, $gate_url, $callback_addresses_string);

                if (RBS_ENABLE_LOGGING === true) {
                    $this->writeRBSLog("[callback_addresses_string]: " . $callback_addresses_string . "\n[RESPONSE]: " . $response);
                }
            }
            parent::process_admin_options();
        }

        public function _updateRBSCallback($login, $password, $action_address, $callback_addresses_string)
        {
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic ' . base64_encode($login . ":" . $password)
            );
            $data['callbacks_enabled'] = true;
            $data['callback_addresses'] = $callback_addresses_string;
            $data['callback_operations'] = "deposited,approved,declinedByTimeout";

            $response = $this->_sendRBSData(json_encode($data), $action_address, $headers);
            return $response;
        }

        public function _sendRBSData($data, $action_address, $headers = array())
        {

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_VERBOSE => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_URL => $action_address,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                //                CURLOPT_ENCODING, "gzip",
            ));
            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }

        public function callback()
        {
            if (isset($_GET['rbspayment'])) {
                $action = $_GET['rbspayment'];

                if ($this->test_mode == 'yes') {
                    $action_adr = $this->test_url;
                } else {
                    $action_adr = $this->prod_url;
                }
                $action_adr .= 'getOrderStatusExtended.do';

                $args = array(
                    'userName' => $this->merchant,
                    'password' => $this->password,
//                    'orderId' => $args['orderId'] = isset($_GET['mdOrder']) ? $_GET['mdOrder'] : null
                );

                switch ($action) {

                    case "result":
                        //by returnUrl
                        $args['orderId'] = isset($_GET['orderId']) ? $_GET['orderId'] : null;

                        // we already know internal order_id from returnUrl
                        $order_id = $_GET['order_id'];
                        $order = new WC_Order($order_id);

                        $response = $this->_sendRBSData(http_build_query($args, '', '&'), $action_adr);

                        if (RBS_ENABLE_LOGGING === true) {
                            $logData = $args;
                            $logData['password'] = '**removed from log**';
                            $this->writeRBSLog("[REQUEST RU]: " . $action_adr . ": " . print_r($logData, true) . "\n[RESPONSE]: " . print_r($response, true));
                        }

                        $response = json_decode($response, true);
                        $orderStatus = $response['orderStatus'];

                        if ($orderStatus == '1' || $orderStatus == '2') {
                            //here
                            if ($this->allowCallbacks === false) {
                                $order->update_status($this->order_status, __('Payment successful', 'wc-rbspayment-text-domain'));
                                try {
                                    wc_reduce_stock_levels($order_id);
                                } catch (Exception $e) {
                                    //noop
                                }
                                $order->payment_complete();
                            }

                            if (!empty($this->success_url)) {
                                WC()->cart->empty_cart();
                                wp_redirect($this->success_url . "?order_id=" . $order_id);
                                exit;
                            }
                            wp_redirect($this->get_return_url($order));
                            exit;

                        } else {

                            if ($this->allowCallbacks === false) {
                                $order->update_status('failed', __('Payment failed', 'wc-rbspayment-text-domain'));
//                                $order->cancel_order();
                            }

                            if (!empty($this->fail_url)) {
                                wp_redirect($this->fail_url . "?order_id=" . $order_id);
                                exit;
                            }
                            wc_add_notice(__('There was an error while processing payment<br/>', 'wc-rbspayment-text-domain') . $response['actionCodeDescription'], 'error');
                            wp_redirect($order->get_cancel_order_url());
                            exit;
                        }
                        break;

                    case "callback":
                        //by callback
                        $args['orderId'] = isset($_GET['mdOrder']) ? $_GET['mdOrder'] : null;

                        $response = $this->_sendRBSData(http_build_query($args, '', '&'), $action_adr);

                        if (RBS_ENABLE_LOGGING === true) {
                            $logData = $args;
                            $logData['password'] = '**removed from log**';
                            $this->writeRBSLog("[REQUEST CB]: " . $action_adr . ": " . print_r($logData, true) . "\n[RESPONSE]: " . print_r($response, true));
                        }
                        $response = json_decode($response, true);

                        // we should parse orderNumber for know internal order_id from $response
                        $p = explode("_", $response['orderNumber']);

                        if ($this->orderNumberById) {
                            $order_id = $p[0];
                        } else {
                            $order_id = wc_sequential_order_numbers()->find_order_by_order_number($p[0]);
                        }

                        $order = new WC_Order($order_id);
                        $orderStatus = $response['orderStatus'];

                        if (strpos($order->get_status(), "pending") !== false || strpos($order->get_status(), "failed") !== false) { //PLUG-4415

                            if ($orderStatus == '1' || $orderStatus == '2') {
                                $order->update_status($this->order_status, __('Payment successful', 'wc-rbspayment-text-domain'));
                                try {
                                    wc_reduce_stock_levels($order_id);
                                } catch (Exception $e) {
                                    //noop
                                }
                                $order->payment_complete();
                                echo "->" . $this->order_status;

                            } else {
                                $order->update_status('failed', __('Payment failed', 'wc-rbspayment-text-domain'));
//                                $order->cancel_order();
                            }

                        }
//                        else if (strpos($order->get_status(), "failed") !== false) { //PLUG-4495
//                            // once more payment try after wrong enter card data
//                            if ($orderStatus == '1' || $orderStatus == '2') {
//                                $order->update_status($this->order_status, __('Payment successful', 'wc-rbspayment-text-domain'));
//                                try {
//                                    wc_reduce_stock_levels($order_id);
//                                } catch (Exception $e) {
//                                    //noop
//                                }
//                                $order->payment_complete();
//                                echo "->" . $this->order_status;
//                            }
//                        }

                        break;
                }
                exit;

            }
        }

        /**
         * Check if this gateway is enabled and available in the user's country
         */
        function is_valid_for_use()
        {
            return true;
        }

        /*
         * Admin Panel Options
         */
        public function admin_options()
        {
            ?>
            <h3>RBSPayment</h3>
            <p><?php _e("Allow customers to conveniently checkout directly with ", 'wc-rbspayment-text-domain'); ?><? echo RBS_PAYMENT_NAME; ?></p>

            <?php if ($this->is_valid_for_use()) : ?>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table>
        <?php else : ?>
            <div class="inline error"><p>
                    <strong><?php _e('Error: ', 'woocommerce'); ?></strong>: <?php echo $this->id; ?><?php _e(' does not support your currency.', 'wc-rbspayment-text-domain'); ?>
                </p></div>
            <?php
        endif;

        }

        /*
         * Initialise Gateway Settings Form Fields
         */
        function init_form_fields()
        {

            $shipping_methods = array();

            if ( is_admin() )
                foreach ( WC()->shipping()->load_shipping_methods() as $method ) {
                    $shipping_methods[ $method->id ] = $method->get_method_title();
                }

            $form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'wc-rbspayment-text-domain'),
                    'type' => 'checkbox',
                    'label' => __('Enable', 'woocommerce') . " RBSPayment",
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'wc-rbspayment-text-domain'),
                    'type' => 'text',
                    'css'               => 'width: 460px;',
                    'description' => __('Title displayed to your customer when they make their order.', 'wc-rbspayment-text-domain'),
                    'desc_tip' => true,
                ),
                'merchant' => array(
                    'title' => __('Login-API', 'wc-rbspayment-text-domain'),
                    'type' => 'text',
                    'default' => '',
                    'desc_tip' => true,
                ),
                'password' => array(
                    'title' => __('Password', 'wc-rbspayment-text-domain'),
                    'type' => 'password',
                    'default' => '',
                    'desc_tip' => true,
                ),
                'test_mode' => array(
                    'title' => __('Test mode', 'wc-rbspayment-text-domain'),
                    'type' => 'checkbox',
                    'label' => __('Enable', 'woocommerce'),
                    'description' => __('In this mode no actual payments are processed.', 'wc-rbspayment-text-domain'),
                    'default' => 'no'
                ),
                'stage_mode' => array(
                    'title' => __('Payments type', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => 'one-stage',
                    'options' => array(
                        'one-stage' => __('One-phase payments', 'wc-rbspayment-text-domain'),
                        'two-stage' => __('Two-phase payments', 'wc-rbspayment-text-domain'),
                    ),
                ),
                'description' => array(
                    'title' => __('Description', 'wc-rbspayment-text-domain'),
                    'type' => 'textarea',
                    'css'               => 'width: 460px;',
                    'description' => __('Payment description displayed to your customer.', 'wc-rbspayment-text-domain'),

                ),
                'order_status' => array(
                    'title' => __('Payed order status', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'description' => __('Payed order status.', 'wc-rbspayment-text-domain'),
                    'default' => 'wc-completed',
                    'desc_tip' => true,
                    'options' => array(
                        'wc-processing' => _x('Processing', 'Order status', 'woocommerce'),
                        'wc-completed' => _x('Completed', 'Order status', 'woocommerce'),
                    ),
                ),

                'success_url' => array(
                    'title' => __('success_url', 'woocommerce'),
                    'type' => 'text',
                    'css'               => 'width: 460px;',
                    'description' => __('Page your customer will be redirected to after a <b>successful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-rbspayment-text-domain'),
                ),
                'fail_url' => array(
                    'title' => __('fail_url', 'woocommerce'),
                    'type' => 'text',
                    'css'               => 'width: 460px;',
                    'description' => __('Page your customer will be redirected to after an <b>unsuccessful payment</b>.<br/>Leave this field blank, if you want to use default settings.', 'wc-rbspayment-text-domain'),
                ),
                //
                'enable_for_methods' => array(
                    'title'             => __('Enable for shipping methods', 'wc-rbspayment-text-domain'),
                    'type'              => 'multiselect',
                    'class'             => 'wc-enhanced-select',
                    'css'               => 'width: 460px;',
                    'default'           => '',
//                    'description'       => __('If COD is only available for certain methods, set it up here. Leave blank to enable for all methods.', 'wc-rbspayment-text-domain'),
                    'options'           => $this->load_shipping_method_options(),
                    'desc_tip'          => true,
                    'custom_attributes' => array(
                        'data-placeholder' => __('Select shipping methods (empty for all)', 'wc-rbspayment-text-domain')
                    )
                )

            );

            $form_fields_ext = array(
                'send_order' => array(
                    'title' => __("Send cart data<br />(including customer info)", 'wc-rbspayment-text-domain'),
                    'type' => 'checkbox',
                    'label' => __('Enable', 'woocommerce'),
                    'description' => __('If this option is enabled order receipts will be created and sent to your customer and to the revenue service.<br/>This is a paid option, contact your bank to enable it. If you use it, configure VAT settings. VAT is calculated according to the Russian legislation. VAT amounts calculated by your store may differ from the actual VAT amounts that can be applied.', 'wc-rbspayment-text-domain'),
                    'default' => 'no'
                ),
                'tax_system' => array(
                    'title' => __('Tax system', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('General', 'wc-rbspayment-text-domain'),
                        '1' => __('Simplified, income', 'wc-rbspayment-text-domain'),
                        '2' => __('Simplified, income minus expences', 'wc-rbspayment-text-domain'),
                        '3' => __('Unified tax on imputed income', 'wc-rbspayment-text-domain'),
                        '4' => __('Unified agricultural tax', 'wc-rbspayment-text-domain'),
                        '5' => __('Patent taxation system', 'wc-rbspayment-text-domain'),
                    ),
                ),
                'tax_type' => array(
                    'title' => __('Default VAT', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => '0',
                    'options' => array(
                        '0' => __('No VAT', 'wc-rbspayment-text-domain'),
                        '1' => __('VAT 0%', 'wc-rbspayment-text-domain'),
                        '2' => __('VAT 10%', 'wc-rbspayment-text-domain'),
                        '3' => __('VAT 18%', 'wc-rbspayment-text-domain'),
                        '6' => __('VAT 20%', 'wc-rbspayment-text-domain'),
                        '4' => __('VAT applicable rate 10/110', 'wc-rbspayment-text-domain'),
                        '5' => __('VAT applicable rate 18/118', 'wc-rbspayment-text-domain'),
                        '7' => __('VAT applicable rate 20/120', 'wc-rbspayment-text-domain'),
                    ),
                ),
                'FFDVersion' => array(
                    'title' => __('Fiscal document format', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => 'v1_05',
                    'options' => array(
//                        'v1_0' => __('v1.0', 'wc-rbspayment-text-domain'),
                        'v1_05' => __('v1.05', 'wc-rbspayment-text-domain'),
                        'v1_2' => __('v1.2', 'wc-rbspayment-text-domain'),
                    ),
                    'description' => __('Also specify the version in your bank web account and in your fiscal service web account.', 'wc-rbspayment-text-domain'),
                ),
                'paymentMethodType' => array(
                    'title' => __('Payment type', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => '1',
                    'options' => array(
                        '1' => __('Full prepayment', 'wc-rbspayment-text-domain'),
                        '2' => __('Partial prepayment', 'wc-rbspayment-text-domain'),
                        '3' => __('Advance payment', 'wc-rbspayment-text-domain'),
                        '4' => __('Full payment', 'wc-rbspayment-text-domain'),
                        '5' => __('Partial payment with further credit', 'wc-rbspayment-text-domain'),
                        '6' => __('No payment with further credit', 'wc-rbspayment-text-domain'),
                        '7' => __('Payment on credit', 'wc-rbspayment-text-domain'),
                    ),
//                    'description' => __('Used in FFD starting from 1.05', 'wc-rbspayment-text-domain'),
                ),
                'paymentMethodType_delivery' => array(
                    'title' => __('Payment type for delivery', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => '1',
                    'options' => array(
                        '1' => __('Full prepayment', 'wc-rbspayment-text-domain'),
                        '2' => __('Partial prepayment', 'wc-rbspayment-text-domain'),
                        '3' => __('Advance payment', 'wc-rbspayment-text-domain'),
                        '4' => __('Full payment', 'wc-rbspayment-text-domain'),
                        '5' => __('Partial payment with further credit', 'wc-rbspayment-text-domain'),
                        '6' => __('No payment with further credit', 'wc-rbspayment-text-domain'),
                        '7' => __('Payment on credit', 'wc-rbspayment-text-domain'),
                    ),
//                    'description' => __('Used in FFD starting from 1.05', 'wc-rbspayment-text-domain'),
                ),
                'paymentObjectType' => array(
                    'title' => __('Type of goods and services', 'wc-rbspayment-text-domain'),
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'default' => '1',
                    'options' => array(
                        '1' => __('Goods', 'wc-rbspayment-text-domain'),
                        '2' => __('Excised goods', 'wc-rbspayment-text-domain'),
                        '3' => __('Job', 'wc-rbspayment-text-domain'),
                        '4' => __('Service', 'wc-rbspayment-text-domain'),
                        '5' => __('Stake in gambling', 'wc-rbspayment-text-domain'),
//                        '6' => __('Gambling gain', 'wc-rbspayment-text-domain'),
                        '7' => __('Lottery ticket', 'wc-rbspayment-text-domain'),
//                        '8' => __('Lottery gain', 'wc-rbspayment-text-domain'),
                        '9' => __('Intellectual property provision', 'wc-rbspayment-text-domain'),
                        '10' => __('Payment', 'wc-rbspayment-text-domain'),
                        '11' => __("Agent's commission", 'wc-rbspayment-text-domain'),
                        '12' => __('Combined', 'wc-rbspayment-text-domain'),
                        '13' => __('Other', 'wc-rbspayment-text-domain'),

                    ),
//                    'description' => __('Used in FFD starting from 1.05', 'wc-rbspayment-text-domain'),
                ),

            );

            if (RBS_ENABLE_FISCALE_OPTIONS === true) {
                $form_fields = array_merge($form_fields, $form_fields_ext);
            }
            $this->form_fields = $form_fields;
        }

        function get_product_price_with_discount($price, $type, $c_amount, &$order_data)
        {

            switch ($type) {
                case 'percent':
                    $new_price = ceil($price * (1 - $c_amount / 100));

                    // remove this discount from discount_total
                    $order_data['discount_total'] -= ($price - $new_price);
                    break;

//                case 'fixed_cart':
//                    //wrong
//                    $new_price = $price;
//                    break;

                case 'fixed_product':
                    $new_price = $price - $c_amount;

                    // remove this discount from discount_total
                    $order_data['discount_total'] -= $c_amount / 100;
                    break;

                default:
                    $new_price = $price;
            }
            return $new_price;
        }

        /*
         * Generate the dibs button link
         */
        public function generate_form($order_id)
        {
            $order = new WC_Order($order_id);
            $amount = $order->get_total() * 100;

            // COUPONS
            $coupons = array();

            global $woocommerce;

            if (!empty($woocommerce->cart->applied_coupons)) {
                foreach ($woocommerce->cart->applied_coupons as $code) {
                    $coupons[] = new WC_Coupon($code);
                }
            }

            if ($this->test_mode == 'yes') {
                $action_adr = $this->test_url;
            } else {
                $action_adr = $this->prod_url;
            }

            $extra_url_param = '';
            if ($this->stage_mode == 'two-stage') {
                $action_adr .= 'registerPreAuth.do';
            } else if ($this->stage_mode == 'one-stage') {
                $extra_url_param = '&wc-callb=callback_function';
                $action_adr .= 'register.do';
            }

            $order_data = $order->get_data();

            $language = substr(get_bloginfo("language"), 0, 2);
            //fix Gate bug locale2country
            switch ($language) {
                case  ('uk'):
                    $language = 'ua';
                    break;
                case ('be'):
                    $language = 'by';
                    break;
            }

            $jsonParams_array = array(
                'CMS' => 'Wordpress ' . get_bloginfo('version') . " + woocommerce version: " . wpbo_get_woo_version_number(),
                'Module-Version' => $this->pData['Version'],
            );

            if (RBS_CUSTOMER_EMAIL_SEND && !empty($order_data['billing']['email'])) {
                $jsonParams_array['email'] = $order_data['billing']['email'];
            }
            if (!empty($order_data['billing']['phone'])) {
                $jsonParams_array['phone'] = preg_replace("/(\W*)/", "", $order_data['billing']['phone']);
            }

            if (!empty($order_data['billing']['first_name'])) {
                $jsonParams_array['payerFirstName'] = $order_data['billing']['first_name'];
            }
            if (!empty($order_data['billing']['last_name'])) {
                $jsonParams_array['payerLastName'] = $order_data['billing']['last_name'];
            }
            if (!empty($order_data['billing']['address_1'])) {
                $jsonParams_array['postAddress'] = $order_data['billing']['address_1'];
            }
            if (!empty($order_data['billing']['city'])) {
                $jsonParams_array['payerCity'] = $order_data['billing']['city'];
            }
            if (!empty($order_data['billing']['state'])) {
                $jsonParams_array['payerState'] = $order_data['billing']['state'];
            }
            if (!empty($order_data['billing']['postcode'])) {
                $jsonParams_array['payerPostalCode'] = $order_data['billing']['postcode'];
            }
            if (!empty($order_data['billing']['country'])) {
                $jsonParams_array['payerCountry'] = $order_data['billing']['country'];
            }

            // prepare args array
            $args = array(
                'userName' => $this->merchant,
                'password' => $this->password,
                'amount' => $amount,
//                'description' => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
                'language' => $language,
                'returnUrl' => get_option('siteurl') . '?wc-api=WC_RBSPayment&rbspayment=result&order_id=' . $order_id . $extra_url_param,
                'currency' => $this->currency_codes[get_woocommerce_currency()],
                'jsonParams' => json_encode($jsonParams_array),
//                'additionalOfdParams' => "{\"agent_info.type\" : \"3\"}" //PLUG-4222
            );

            if ($this->send_order == 'yes' && $this->fiscale_options === true) {

                $args['taxSystem'] = $this->tax_system;

                $order_items = $order->get_items();

                $order_timestamp_created = $order_data['date_created']->getTimestamp();

                $items = array();
                $itemsCnt = 1;

                foreach ($order_items as $value) {
                    $item = array();
                    $product_variation_id = $value['variation_id'];

                    if ($product_variation_id) {
                        $product = new WC_Product_Variation($value['variation_id']);
                        $item_code = $itemsCnt . "-" . $value['variation_id'];
                    } else {
                        $product = new WC_Product($value['product_id']);
                        $item_code = $itemsCnt . "-" . $value['product_id'];
                    }

                    $product_sku = get_post_meta( $value['product_id'], '_sku', true );
                    $item_code = !empty($product_sku) ? $product_sku : $item_code;

                    $tax_type = $this->getTaxType($product);

//                    $product_price = round(($product->get_price()) * 100);
                    $product_price = round((($value['total'] + $value['total_tax']) / $value['quantity']) * 100);

                    if ($product->get_type() == 'variation') {
                        //TODO
                    }


                    // if discount (coupon etc)
                    // see DISCOUNT SECTION
//                    foreach ($coupons as $coupon) {
//                        $coupon_amount = $coupon->get_amount() * 100;
//                        $product_price = $this->get_product_price_with_discount($product_price, $coupon->get_discount_type(), $coupon_amount, $order_data );
//                    }

                    $item['positionId'] = $itemsCnt++;
                    $item['name'] = $value['name'];

                    // FFDVersion
                    if ($this->FFDVersion == 'v1_05') {
                        $item['quantity'] = array(
                            'value' => $value['quantity'],
                            'measure' => RBS_MEASUREMENT_NAME
                        );
                    } else {
                        $item['quantity'] = array(
                            'value' => $value['quantity'],
                            'measure' => RBS_MEASUREMENT_CODE
                        );

                    }

                    $item['itemAmount'] = $product_price * $value['quantity'];
                    $item['itemCode'] = $item_code;
                    $item['tax'] = array(
                        'taxType' => $tax_type
                    );
                    $item['itemPrice'] = $product_price;

                    $attributes = array();
                    $attributes[] = array(
                        "name" => "paymentMethod",
                        "value" => $this->paymentMethodType
                    );
                    $attributes[] = array(
                        "name" => "paymentObject",
                        "value" => $this->paymentObjectType
                    );

                    $item['itemAttributes']['attributes'] = $attributes;
                    $items[] = $item;
                }


                // delivery_total
                $shipping_total = $order->get_shipping_total();
                $shipping_tax = $order->get_shipping_tax();

                // DISCOUNT
//                 if (!empty($order_data['discount_total'])) {
//                     $discount = ($order_data['discount_total'] + $order_data['discount_tax']) * 100;

//                     $new_order_total = 0;

//                     // coze delivery will be another position
//                     $delivery_sum = ($shipping_total > 0) ? ($shipping_total + $shipping_tax) * 100 : 0;

//                     foreach ($items as &$i) {

//                         $p_discount = intval(round(($i['itemAmount'] / ($amount - $delivery_sum + $discount)) * $discount, 2));

//                         $this->correctBundleItem($i, $p_discount);
//                         $new_order_total += $i['itemAmount'];
//                     }

//                     // reset order amount
//                     // return delivery_sum into amount
//                     $args['amount'] = $new_order_total + $delivery_sum;
//                 }


                // DELIVERY POSITION
                if ($shipping_total > 0) {
                    $WC_Order_Item_Shipping = new WC_Order_Item_Shipping();
                    $itemShipment['positionId'] = $itemsCnt;
                    $itemShipment['name'] = __('Delivery', 'wc-rbspayment-text-domain');

                    // FFDVersion
                    if ($this->FFDVersion == 'v1_05') {
                        $itemShipment['quantity'] = array(
                            'value' => 1,
                            'measure' => RBS_MEASUREMENT_NAME
                        );
                    } else {
                        $itemShipment['quantity'] = array(
                            'value' => 1,
                            'measure' => RBS_MEASUREMENT_CODE
                        );

                    }

                    $itemShipment['itemAmount'] = $itemShipment['itemPrice'] = $shipping_total * 100;
                    $itemShipment['itemCode'] = 'delivery';
                    $itemShipment['tax'] = array(
                        'taxType' => $tax_type = $this->getTaxType($WC_Order_Item_Shipping) //$this->tax_type
                    );

                    $attributes = array();
                    $attributes[] = array(
                        "name" => "paymentMethod",
                        "value" => $this->paymentObjectType_delivery
                    );
                    $attributes[] = array(
                        "name" => "paymentObject",
                        "value" => 4
                    );

                    $itemShipment['itemAttributes']['attributes'] = $attributes;

                    $items[] = $itemShipment;
                }

                $order_bundle = array(
                    'orderCreationDate' => $order_timestamp_created,
                    'cartItems' => array('items' => $items)
                );

                if (RBS_CUSTOMER_EMAIL_SEND && !empty($order_data['billing']['email'])) {
                    $order_bundle['customerDetails']['email'] = $order_data['billing']['email'];
                }
                if (!empty($order_data['billing']['phone'])) {
                    $order_bundle['customerDetails']['phone'] = preg_replace("/(\W*)/", "", $order_data['billing']['phone']);
                }

                $args['orderBundle'] = json_encode($order_bundle);
            }
            if ($this->orderNumberById) {
                $args['orderNumber'] = $order_id . '_' . time();
            } else {
                $args['orderNumber'] = trim(str_replace('#', '', $order->get_order_number())) . "_" . time(); // PLUG-3966, PLUG-4300
            }

            $headers = array(
                'CMS: Wordpress ' . get_bloginfo('version') . " + woocommerce version: " . wpbo_get_woo_version_number(),
                'Module-Version: ' . $this->pData['Version'],
            );
            $response = $this->_sendRBSData(http_build_query($args, '', '&'), $action_adr, $headers);
            if (RBS_ENABLE_LOGGING === true) {
                $logData = $args;
                $logData['password'] = '**removed from log**';
                $this->writeRBSLog("[REQUEST]: " . $action_adr . ": \nDATA: " . print_r($logData, true) . "\n[RESPONSE]: " . $response);
            }

            $response = json_decode($response, true);

            if (empty($response['errorCode'])) {
                if (RBS_SKIP_CONFIRMATION_STEP == true) {
                    wp_redirect($response['formUrl']); //PLUG-4104 Comment this line for redirect via pressing button (step)
                }
                //EI: if is error in the headers (already send)
                echo '<p><a class="button cancel" href="' . $response['formUrl'] . '">' . __('Proceed with payment', 'wc-rbspayment-text-domain') . '</a></p>';
                exit;

            } else {
                return '<p>' . __('Error code #' . $response['errorCode'] . ': ' . $response['errorMessage'], 'wc-rbspayment-text-domain') . '</p>' .
                '<a class="button cancel" href="' . $order->get_cancel_order_url() . '">' . __('Cancel payment and return to cart', 'wc-rbspayment-text-domain') . '</a>';
            }
        }

        function getTaxType($product)
        {

            $tax = new WC_Tax();

            if (get_option("woocommerce_calc_taxes") == "no") { // PLUG-4056
                $item_rate = -1;
            } else {
                $base_tax_rates = $tax->get_base_tax_rates($product->get_tax_class(true));
                if (!empty($base_tax_rates)) {
                    $temp = $tax->get_rates($product->get_tax_class());
                    $rates = array_shift($temp);
                    $item_rate = round(array_shift($rates));
                } else {
                    $item_rate = -1;
                }
            }

            if ($item_rate == 20) {
                $tax_type = 6;

            } else if ($item_rate == 18) {
                $tax_type = 3;

            } else if ($item_rate == 10) {
                $tax_type = 2;

            } else if ($item_rate == 0) {
                $tax_type = 1;

            } else {
                $tax_type = $this->tax_type;
            }
            return $tax_type;
        }


        function correctBundleItem(&$item, $discount)
        {
            $item['itemAmount'] -= $discount;
//            $item['itemPrice'] = $item['itemAmount'] % $item['quantity']['value'];
            $diff_price = fmod($item['itemAmount'], $item['quantity']['value']); //0.5 quantity
            if ($diff_price != 0) {
                $item['itemAmount'] += $item['quantity']['value'] - $diff_price;
            }

            $item['itemPrice'] = $item['itemAmount'] / $item['quantity']['value'];
        }



        /**
         * Check If The Gateway Is Available For Use.
         *
         * @return bool
         */
        public function is_available() {
            $order          = null;
            $needs_shipping = false;

            // Test if shipping is needed first.
            if ( WC()->cart && WC()->cart->needs_shipping() ) {
                $needs_shipping = true;
            } elseif ( is_page( wc_get_page_id( 'checkout' ) ) && 0 < get_query_var( 'order-pay' ) ) {
                $order_id = absint( get_query_var( 'order-pay' ) );
                $order    = wc_get_order( $order_id );

                // Test if order needs shipping.
                if ( $order && 0 < count( $order->get_items() ) ) {
                    foreach ( $order->get_items() as $item ) {
                        $_product = $item->get_product();
                        if ( $_product && $_product->needs_shipping() ) {
                            $needs_shipping = true;
                            break;
                        }
                    }
                }
            }

            $needs_shipping = apply_filters( 'woocommerce_cart_needs_shipping', $needs_shipping );

            // Virtual order, with virtual disabled.
            if ( ! $needs_shipping ) {
                return false;
            }

            // Only apply if all packages are being shipped via chosen method, or order is virtual.
            if ( ! empty( $this->enable_for_methods ) && $needs_shipping ) {
                $order_shipping_items            = is_object( $order ) ? $order->get_shipping_methods() : false;
                $chosen_shipping_methods_session = WC()->session->get( 'chosen_shipping_methods' );

                if ( $order_shipping_items ) {
                    $canonical_rate_ids = $this->get_canonical_order_shipping_item_rate_ids( $order_shipping_items );
                } else {
                    $canonical_rate_ids = $this->get_canonical_package_rate_ids( $chosen_shipping_methods_session );
                }

                if ( ! count( $this->get_matching_rates( $canonical_rate_ids ) ) ) {
                    return false;
                }
            }

            return parent::is_available();
        }
        /**
         * Indicates whether a rate exists in an array of canonically-formatted rate IDs that activates this gateway.
         *
         * @since  3.4.0
         *
         * @param array $rate_ids Rate ids to check.
         * @return boolean
         */
        private function get_matching_rates( $rate_ids ) {
            // First, match entries in 'method_id:instance_id' format. Then, match entries in 'method_id' format by stripping off the instance ID from the candidates.
            return array_unique( array_merge( array_intersect( $this->enable_for_methods, $rate_ids ), array_intersect( $this->enable_for_methods, array_unique( array_map( 'wc_get_string_before_colon', $rate_ids ) ) ) ) );
        }
        /**
         * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
         *
         * @since  3.4.0
         *
         * @param  array $order_shipping_items  Array of WC_Order_Item_Shipping objects.
         * @return array $canonical_rate_ids    Rate IDs in a canonical format.
         */
        private function get_canonical_order_shipping_item_rate_ids( $order_shipping_items ) {

            $canonical_rate_ids = array();

            foreach ( $order_shipping_items as $order_shipping_item ) {
                $canonical_rate_ids[] = $order_shipping_item->get_method_id() . ':' . $order_shipping_item->get_instance_id();
            }

            return $canonical_rate_ids;
        }

        /**
         * Converts the chosen rate IDs generated by Shipping Methods to a canonical 'method_id:instance_id' format.
         *
         * @since  3.4.0
         *
         * @param  array $chosen_package_rate_ids Rate IDs as generated by shipping methods. Can be anything if a shipping method doesn't honor WC conventions.
         * @return array $canonical_rate_ids  Rate IDs in a canonical format.
         */
        private function get_canonical_package_rate_ids( $chosen_package_rate_ids ) {

            $shipping_packages  = WC()->shipping()->get_packages();
            $canonical_rate_ids = array();

            if ( ! empty( $chosen_package_rate_ids ) && is_array( $chosen_package_rate_ids ) ) {
                foreach ( $chosen_package_rate_ids as $package_key => $chosen_package_rate_id ) {
                    if ( ! empty( $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ] ) ) {
                        $chosen_rate          = $shipping_packages[ $package_key ]['rates'][ $chosen_package_rate_id ];
                        $canonical_rate_ids[] = $chosen_rate->get_method_id() . ':' . $chosen_rate->get_instance_id();
                    }
                }
            }

            return $canonical_rate_ids;
        }

        /*
         * Process the payment and return the result
         */
        function process_payment($order_id)
        {
            $order = new WC_Order($order_id);

            //if PLUG-2403
            if (!empty($_GET['pay_for_order']) && $_GET['pay_for_order'] == 'true') {
                $this->generate_form($order_id);
                die;
            }

            $pay_now_url = $order->get_checkout_payment_url(true);

            return array(
                'result' => 'success',
                'redirect' => $pay_now_url
            );

        }

        /*
         * Receipt page
         */
        function receipt_page($order)
        {
            echo $this->generate_form($order);
        }

        function writeRBSLog($var, $info = true)
        {
            $information = "";
            if ($var) {
                if ($info) {
                    $information = "\n\n";
                    $information .= str_repeat("-=", 64);
                    $information .= "\nDate: " . date('Y-m-d H:i:s');
                    $information .= "\nWordpress version " . get_bloginfo('version') . "; Woocommerce version: " . wpbo_get_woo_version_number() . "\n";
                }

                $result = $var;
                if (is_array($var) || is_object($var)) {
                    $result = "\n" . print_r($var, true);
                }
                $result .= "\n\n";
                $path = dirname(__FILE__) . '/wc_rbspayment_' . date('Y-m') . '.log';
                error_log($information . $result, 3, $path);
                return true;
            }
            return false;
        }


        function rbspayment_change_status_function($order_id)
        {
            $order = wc_get_order($order_id);
            $order->update_status('wc-complete');
        }



        /**
         * Loads all of the shipping method options for the enable_for_methods field.
         *
         * @return array
         */
        private function load_shipping_method_options() {

            $data_store = WC_Data_Store::load( 'shipping-zone' );
            $raw_zones  = $data_store->get_zones();

            foreach ( $raw_zones as $raw_zone ) {
                $zones[] = new WC_Shipping_Zone( $raw_zone );
            }

            $zones[] = new WC_Shipping_Zone( 0 );

            $options = array();
            foreach ( WC()->shipping()->load_shipping_methods() as $method ) {

                $options[ $method->get_method_title() ] = array();

                // Translators: %1$s shipping method name.
                $options[ $method->get_method_title() ][ $method->id ] = sprintf( __( 'Any &quot;%1$s&quot; method', 'woocommerce' ), $method->get_method_title() );

                foreach ( $zones as $zone ) {

                    $shipping_method_instances = $zone->get_shipping_methods();

                    foreach ( $shipping_method_instances as $shipping_method_instance_id => $shipping_method_instance ) {

                        if ( $shipping_method_instance->id !== $method->id ) {
                            continue;
                        }

                        $option_id = $shipping_method_instance->get_rate_id();

                        // Translators: %1$s shipping method title, %2$s shipping method id.
                        $option_instance_title = sprintf( __( '%1$s (#%2$s)', 'woocommerce' ), $shipping_method_instance->get_title(), $shipping_method_instance_id );

                        // Translators: %1$s zone name, %2$s shipping method instance name.
                        $option_title = sprintf( __( '%1$s &ndash; %2$s', 'woocommerce' ), $zone->get_id() ? $zone->get_zone_name() : __( 'Other locations', 'woocommerce' ), $option_instance_title );

                        $options[ $method->get_method_title() ][ $option_id ] = $option_title;
                    }
                }
            }

            return $options;
        }

    }

    function add_rbspayment_gateway($methods)
    {
        $methods[] = 'WC_RBSPayment';
        return $methods;
    }

    if (!function_exists('wpbo_get_woo_version_number')) {
        function wpbo_get_woo_version_number()
        {
            // If get_plugins() isn't available, require it
            if (!function_exists('get_plugins'))
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            // Create the plugins folder and file variables
            $plugin_folder = get_plugins('/' . 'woocommerce');
            $plugin_file = 'woocommerce.php';

            // If the plugin version number is set, return it
            if (isset($plugin_folder[$plugin_file]['Version'])) {
                return $plugin_folder[$plugin_file]['Version'];

            } else {
                // Otherwise return null
                return NULL;
            }
        }
    }

    add_filter('woocommerce_payment_gateways', 'add_rbspayment_gateway');

}