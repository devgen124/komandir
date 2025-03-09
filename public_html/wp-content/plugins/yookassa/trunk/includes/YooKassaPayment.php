<?php

use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Notification\NotificationFactory;
use YooKassa\Model\NotificationEventType;
use YooKassa\Model\PaymentMethod\PaymentMethodBankCard;
use YooKassa\Model\PaymentMethod\PaymentMethodSberLoan;
use YooKassa\Model\PaymentMethod\PaymentMethodYooMoney;
use YooKassa\Model\PaymentMethodType;
use YooKassa\Model\PaymentStatus;
use YooKassa\Request\Payments\PaymentResponse;
use YooKassa\Request\Refunds\RefundResponse;
use Yoomoney\Includes\PaymentsTableModel;
use Yoomoney\Includes\CaptureNotificationChecker;
use Yoomoney\Includes\SucceededNotificationChecker;

/**
 * The payment-facing functionality of the plugin.
 */
class YooKassaPayment
{
    /** @var Client */
    private $apiClient;

    /** @var PaymentsTableModel */
    private $paymentsTableModel;

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param      string $plugin_name The name of the plugin.
     * @param      string $version The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
        $this->initRouter();
    }

    /**
     * @todo сделать нормальный роутер для ЧПУ
     */
    public function initRouter()
    {
        add_action( 'wp_loaded', function () {
            add_rewrite_endpoint('yookassa/returnUrl', EP_ALL);
        });

        add_filter('query_vars', function( $query_vars ) {
            if (in_array('yookassa/returnUrl', $query_vars)) {
                $query_vars[] = 'yookassa-order-id';
            }
            return $query_vars;
        });

        add_action('template_redirect', function() {
            $orderId = get_query_var('yookassa-order-id');
            if (!empty($orderId)) {
                $gateway = new YooKassaGateway();
                $gateway->processReturnUrl($orderId);
            }
        });
    }

    /**
     * @return PaymentsTableModel
     */
    public function getPaymentTableModel()
    {
        if (!$this->paymentsTableModel) {
            global $wpdb;
            $this->paymentsTableModel = new PaymentsTableModel($wpdb);
        }
        return $this->paymentsTableModel;
    }

    /**
     * @param $viewPath
     * @param $args
     *
     * @return false|string
     */
    private function render($viewPath, $args)
    {
        ob_start();
        extract($args);
        include (plugin_dir_path(__FILE__) . $viewPath);
//        ob_flush();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


    public function addGateways($methods)
    {
        if (get_option('yookassa_pay_mode') == '1') {
            $methods[] = 'YooKassaGatewayEPL';
        } else {
            $methods[] = 'YooKassaWidgetGateway';
        }
        if (get_option('yookassa_enable_sbbol') == '1') {
            $methods[] = 'YooKassaGatewayB2BSberbank';
        }

        return $methods;
    }

    public function loadGateways()
    {
        require_once plugin_dir_path(dirname(__FILE__)).'gateway/YooKassaGateway.php';
        require_once plugin_dir_path(dirname(__FILE__)).'gateway/YooKassaGatewayEPL.php';
        require_once plugin_dir_path(dirname(__FILE__)).'gateway/YooKassaGatewayB2bSberbank.php';
        require_once plugin_dir_path(dirname(__FILE__)).'gateway/YooKassaWidgetGateway.php';
    }

    public function addGatewaysScripts()
    {
        $enabled_gateways = [];
        foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
            /** @var YooKassaGateway $gateway */
            if ( property_exists( $gateway, 'pluginKey' ) && $gateway->pluginKey === 'yookassa' ) {
                $enabled_gateways[] = $gateway->id;
            }
        }
        echo '<script id="yookassa-own-payment-methods-head">' . PHP_EOL;
        echo 'window.yookassaOwnPaymentMethods = ' . json_encode($enabled_gateways) . ';' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }

    public function processCallback()
    {
        if (
            $_SERVER['REQUEST_METHOD'] == "POST" &&
            isset($_REQUEST['yookassa'])
            && $_REQUEST['yookassa'] == 'callback'
        ) {

            YooKassaLogger::sendHeka(array('notification.callback.init'));
            YooKassaLogger::info('Notification init');
            $body           = @file_get_contents('php://input');
            $callbackParams = json_decode($body, true);
            YooKassaLogger::info('Notification body: '.$body);

            if (!json_last_error()) {
                try {
                    $this->processNotification($callbackParams);
                    YooKassaLogger::sendHeka(array('notification.callback.success'));
                } catch (Exception $e) {
                    YooKassaLogger::error("Error while process notification: ".$e->getMessage());
                    YooKassaLogger::sendAlertLog('Error while process notification', array(
                        'methodid' => 'POST/processCallback',
                        'exception' => $e,
                    ), array('notification.callback.fail'));
                }
            } else {
                YooKassaLogger::info('Notification json error');
                header("HTTP/1.1 400 Bad Request");
                header("Status: 400 Bad Request");
                YooKassaLogger::sendHeka(array('notification.callback.fail'));
            }
            exit();
        }
    }

    /**
     * @param $callbackParams
     *
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     * @throws Exception
     */
    protected function processNotification($callbackParams)
    {
        YooKassaLogger::sendHeka(array('payment.notification.init'));
        YooKassaLogger::info('Process notification init');

        $paymentTableModel = $this->getPaymentTableModel();
        $captureNtfChecker = new CaptureNotificationChecker($paymentTableModel);
        $succeededNtfChecker = new SucceededNotificationChecker($paymentTableModel);

        $gateway = new YooKassaGateway();

        try {
            $fabric = new NotificationFactory();
            $notificationModel = $fabric->factory($callbackParams);
            // todo: refund.succeeded support
            if ($notificationModel->getEvent() === NotificationEventType::REFUND_SUCCEEDED) {
                /** @var RefundResponse $refund */
                $refund = $notificationModel->getObject();
                $order = YooKassaOrderHelper::getOrderIdByPayment($refund->getPaymentId());
                YooKassaLogger::info(sprintf(
                    'Refund %s %s for Order #%s (paymentId %s)',
                    $refund->getAmount()->getValue(),
                    $refund->getAmount()->getCurrency(),
                    $order ? $order->get_order_number() : '???',
                    $refund->getPaymentId()
                ));
                YooKassaLogger::sendHeka(array('payment.notification.success'));
                exit();
            }
        } catch (\Exception $e) {
            YooKassaLogger::error('Invalid notification object - '.$e->getMessage());
            YooKassaLogger::sendAlertLog('Invalid notification object', array(
                'methodid' => 'POST/processNotification',
                'exception' => $e,
            ), array('payment.notification.fail'));
            header("HTTP/1.1 400 Bad Request");
            header("Status: 400 Bad Request");
            exit();
        }

        /** @var PaymentResponse $payment */
        $payment = $notificationModel->getObject();
        $cms_name = $payment->getMetadata()->offsetGet('cms_name');
        if (empty($cms_name) || !in_array($cms_name, array(YooKassaGateway::CMS_NAME, YooKassaGateway::CMS_NAME_OLD), true)) {
            YooKassaLogger::info(sprintf('This notification not for this module. This notification for: «%s»', $cms_name));
            YooKassaLogger::sendHeka(array('payment.notification.skip'));
            exit();
        }
        $shopId = get_option('yookassa_shop_id', 'null');
        $order   = YooKassaOrderHelper::getOrderIdByPayment($payment->getId());
        if (!$order) {
            $paymentMethod = $payment->getPaymentMethod();
            $userId        = $payment->getMetadata()->offsetGet('wp_user_id');
            $token         = null;
            if (!empty($userId)) {
                $tokens = WC_Payment_Tokens::get_customer_tokens($userId);
                foreach ($tokens as $tokenObject) {
                    if ($tokenObject->get_token() == $paymentMethod->id) {
                        $token = $tokenObject;
                        break;
                    }
                }
            }

            if ($paymentMethod->getSaved()
                && $payment->getMetadata()->offsetExists('wp_user_id')
                && $payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE) {
                YooKassaLogger::info('Token init');
                try {
                    $token = $this->prepareToken($paymentMethod, $payment);
                } catch (\Exception $e) {
                    YooKassaLogger::error('Token prepare failed '.$e->getMessage());
                }

                YooKassaLogger::info('Token before save');

                if ($token && $token->save()) {
                    YooKassaLogger::info('Token saved id:'.$token->get_id());
                    $this->getApiClient()->cancelPayment($payment->getId());
                    YooKassaLogger::sendHeka(array('payment.notification.skip'));
                    exit();
                } else {
                    YooKassaLogger::info('Token validate failed');
                }
            }

            YooKassaLogger::error('Order not found for payment '.$payment->getId());
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            YooKassaLogger::sendHeka(array('payment.notification.skip'));
            exit();
        }

        $payment = $this->getApiClient()->getPaymentInfo($payment->getId());

        if ($payment->getMetadata()->offsetExists('subscribe_trial')) {
            if($this->cancelPayment($payment) === false) {
                YooKassaLogger::error('Wrong payment status: '.$payment->getStatus());
                header("HTTP/1.1 402 Payment Required");
                header("Status: 402 Payment Required");
                YooKassaLogger::sendHeka(array('payment.notification.skip'));
                exit();
            }
            YooKassaHandler::competeSubscribe($order, $payment);
            $saveMethod = $payment->getMetadata()->offsetGet('subscribe_payment_save_card');
            if ($saveMethod) {
                $token = $this->prepareToken($payment->getPaymentMethod(), $payment);
                if ($token && $token->save()) {
                    YooKassaLogger::info('Token saved id:'.$token->get_id());
                } else {
                    YooKassaLogger::info('Token validate failed');
                }
            }
            YooKassaLogger::sendHeka(array('payment.notification.skip'));
            exit();
        }

        $updateData = array(
            'status' => $payment->getStatus(),
        );
        if ($paymentMethod = $payment->getPaymentMethod()) {
            $updateData['payment_method_id'] = $paymentMethod->getId();
        }

        if ($payment->getStatus() === PaymentStatus::SUCCEEDED) {
            if ($succeededNtfChecker->isHandled($notificationModel)) {
                YooKassaLogger::sendHeka(array('payment.notification.skip'));
                return;
            }
            $paymentMethod = $payment->paymentMethod;

            if ($paymentMethod->getType() === PaymentMethodType::SBER_LOAN && $paymentMethod->getDiscountAmount()) {
                /** @var PaymentMethodSberLoan $paymentMethod */
                try {
                    $discountAmount = $paymentMethod->getDiscountAmount()->value;
                    YooKassaLogger::info('Adding discount to order. Discount amount: ' . $discountAmount);
                    $this->wcOrderAddDiscount(
                        $order,
                        __('Рассрочка от СберБанка', 'yookassa'),
                        $discountAmount
                    );
                } catch (Exception $e) {
                    YooKassaLogger::error('Error adding discount to order - ' . json_encode($e));
                    header("HTTP/1.1 500 Internal server error");
                    header("Status: 500 Internal server error");
                    YooKassaLogger::sendAlertLog('Error adding discount to order', array(
                        'methodid' => 'POST/processNotification',
                        'exception' => $e,
                    ), array('payment.notification.fail'));
                    exit();
                }
            }

            $userId = $payment->getMetadata()->offsetGet('wp_user_id');

            $isNeedSavedCard = $paymentMethod->getSaved() && !empty($userId);
            if ($payment->getMetadata()->offsetExists('subscribe_payment_save_card')) {
                $isNeedSavedCard = $paymentMethod->getSaved()
                    && !empty($userId)
                    && $payment->getMetadata()->offsetGet('subscribe_payment_save_card');
            }

            if ($isNeedSavedCard) {
                $token = $this->prepareToken($paymentMethod, $payment);
                if ($token && $token->save()) {
                    YooKassaLogger::info('Token saved id:'.$token->get_id());
                } else {
                    YooKassaLogger::info('Token validate failed');
                }
            } else {
                YooKassaLogger::info('Token not entered saved = ' . $paymentMethod->getSaved() . ' !empty($userId) = ' . $userId);
            }

            YooKassaLogger::info('Init complete order');
            if (!YooKassaHandler::completeOrder($order, $payment)) {
                header("HTTP/1.1 500 Internal server error");
                header("Status: 500 Internal server error");
                YooKassaLogger::sendHeka(array('payment.notification.fail'));
                exit();
            }

            $stat = $this->paymentsTableModel->getSuccessPaymentStat();
            $host = str_replace(array('http://','https://','.','/',':'), array('','','-','-','-'), get_site_url());
            YooKassaLogger::sendHeka(array(
                'shop.'.$shopId.'.payment.succeeded',
                'shop.'.$shopId.'.host.'.$host.'.payment-count' => array(
                    'metric_type' => "gauges",
                    'metric_count' => $stat['count']
                ),
                'shop.'.$shopId.'.host.'.$host.'.payment-total' => array(
                    'metric_type' => "gauges",
                    'metric_count' => $stat['total']
                ),
            ));

            $updateData['paid'] = 'Y';
            $updateData['captured_at'] = date('Y-m-d H:i:s');
            $order->set_transaction_id($payment->getId());
        } elseif ($payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE) {
            if ($captureNtfChecker->isHandled($notificationModel)) {
                return;
            }
            YooKassaLogger::info('Init waiting for capture');

            $updateData['paid'] = 'Y';

            $capturePaymentMethods = array(
                PaymentMethodType::BANK_CARD,
                PaymentMethodType::YOO_MONEY,
                PaymentMethodType::GOOGLE_PAY,
                PaymentMethodType::APPLE_PAY,
                PaymentMethodType::TINKOFF_BANK,
                PaymentMethodType::SBERBANK,
                PaymentMethodType::SBER_LOAN,
            );
            if (in_array($payment->getPaymentMethod()->getType(), $capturePaymentMethods)) {
                YooKassaHandler::holdOrder($order, $payment);
            } else {
                $updateData['captured_at'] = date('Y-m-d H:i:s');
                YooKassaHandler::capturePayment($this->getApiClient(), $order, $payment);
            }

            $order->set_transaction_id($payment->getId());

            YooKassaLogger::sendHeka(array(
                'shop.'.$shopId.'.payment.waiting_for_capture',
            ));
        } elseif ($payment->getStatus() === PaymentStatus::CANCELED) {
            $updateData['paid'] = 'N';
            YooKassaLogger::sendHeka(array(
                'shop.'.$shopId.'.payment.canceled',
            ));
        } else {
            YooKassaLogger::error('Wrong payment status: '.$payment->getStatus());
            header("HTTP/1.1 402 Payment Required");
            header("Status: 402 Payment Required");
            $updateData['paid'] = 'N';
        }

        $gateway->updatePaymentData($payment->getId(), $updateData);
        YooKassaLogger::sendHeka(array('payment.notification.success'));
        exit();
    }

    /**
     * Добавление скидки в заказ (применяется к товарам и налогу)
     * и пересчет общей стоимости заказа
     *
     * @param WC_Order $order Объект заказа
     * @param string $title Название для скидки
     * @param mixed $amount Сумма скидки
     *
     * @throws WC_Data_Exception
     */
    function wcOrderAddDiscount($order, $title, $amount)
    {
        $subtotal = $order->get_subtotal();

        $discount = (float)str_replace(' ', '', $amount);
        $discount = $discount > $subtotal ? -$subtotal : -$discount;

        /** Высчитываем процент, по которому будем вычислять скидку для всех позиций в заказе */
        $percentDiscount = round(100 / ($order->get_total() / (int)$amount), 1);
        /** Рассчитываем новую сумму для позиций в заказе с учетом процента */
        foreach ($order->get_items(['line_item', 'shipping', 'tax']) as $item) {
            if ($item instanceof WC_Order_Item_Tax) {
                /** Задаем перерасчет для налогов на товары и доставку */
                $item->set_tax_total($this->calculateTotalFromPercent((float)$item->get_tax_total(), $percentDiscount));
                $item->set_shipping_tax_total($this->calculateTotalFromPercent((float)$item->get_shipping_tax_total(), $percentDiscount));
            } else {
                /** Задаем перерасчет для товаров и доставки */
                $item->set_total($this->calculateTotalFromPercent((float)$item->get_total(), $percentDiscount));
            }
        }

        /** Устанавливаем новую сумму для доставки, чтобы корректно отображалась в подытоге */
        if ($order->get_shipping_method() && $order->get_shipping_total() > 0) {
            $order->set_shipping_total($this->calculateTotalFromPercent((float)$order->get_shipping_total(), $percentDiscount));
        }

        /** Добавляем запись о скидке и ее сумме */
        $item = new WC_Order_Item_Fee();
        $item->set_name($title);
        $item->set_amount($discount);
        $item->set_total($discount);

        if ('0' !== $item->get_tax_class() && 'taxable' === $item->get_tax_status() && wc_tax_enabled()) {
            $tax_for   = array(
                'country'   => $order->get_shipping_country(),
                'state'     => $order->get_shipping_state(),
                'postcode'  => $order->get_shipping_postcode(),
                'city'      => $order->get_shipping_city(),
                'tax_class' => $item->get_tax_class(),
            );
            $tax_rates = WC_Tax::find_rates($tax_for);
            /** Высчитываем сумму скидки для каждого налога с учетом установленного % в налогах */
            $taxes = WC_Tax::calc_tax($item->get_amount(), $tax_rates, $order->get_prices_include_tax());
            $taxes_total = array_sum($taxes);
            /** Высчитываем оставшуюся сумму скидки после подсчета общей суммы скидки по налогам */
            $discount_total = $discount - $taxes_total;
            $item->set_total($discount_total);

            if (method_exists($item, 'get_subtotal')) {
                $subtotal_taxes = WC_Tax::calc_tax($item->get_subtotal(), $tax_rates, $order->get_prices_include_tax());
                $item->set_taxes(array('total' => $taxes, 'subtotal' => $subtotal_taxes));
                $item->set_total_tax($taxes_total);
            } else {
                $item->set_taxes(array('total' => $taxes));
                $item->set_total_tax($taxes_total);
            }
            $has_taxes = true;
        } else {
            $item->set_taxes(array());
            $has_taxes = false;
        }
        /** Сохраняем заказ, чтобы применились все изменения по ценам до перерасчета общей стоимости */
        $order->save();
        /** Делаем перерасчет общей стоимости заказа с обновленными суммами в заказе */
        $order->calculate_totals($has_taxes);
        /** Сохраняем запись о скидке */
        $item->save();
        /** Добавляем в заказ запись о скидке уже после перерасчета, чтобы эта скидка не применилась еще раз */
        $order->add_item($item);
        /** Сохраняем заказ с добавленной записью о скидке */
        $order->save();
    }

    /**
     * Вычисление процента из числа
     *
     * @param float $total Общая сумма
     * @param float $percent Процент, который хотим вычесть из числа
     *
     * @return float
     */
    private function calculateTotalFromPercent($total, $percent)
    {
        return $total - ($percent * ($total / 100));
    }

    public function validStatuses()
    {
        return array('processing', 'completed', 'on-hold', 'pending');
    }

    public function checkPaymentStatus()
    {
        $order_id  = sanitize_key($_GET['order-id']);
        YooKassaLogger::info('CheckPaymentStatus Init: ' . $order_id);

        $order     = wc_get_order($order_id);
        $paymentId = $order->get_transaction_id();

        if (!$this->isYooKassaOrder($order)) {
            YooKassaLogger::info('Payment method is not YooKassa!');
            wp_die();
        }

        try {
            $payment = $this->getApiClient()->getPaymentInfo($paymentId);
            $result = json_encode(array(
                'result' => 'success',
                'status' => $payment->getStatus(),
                'redirectUrl' => $order->get_checkout_payment_url()
            ));
            YooKassaLogger::info('CheckPaymentStatus: ' . $result);
            echo $result;
        } catch (Exception $e) {
            YooKassaLogger::error('CheckPaymentStatus Error: ' . $e->getMessage());
            YooKassaLogger::sendAlertLog('CheckPaymentStatus Error', array(
                'methodid' => 'POST/checkPaymentStatus',
                'exception' => $e,
            ));
        }
        wp_die();
    }

    /**
     * @param int $order_id
     *
     * @throws Exception
     */
    public function changeOrderStatusToProcessing($order_id)
    {
        YooKassaLogger::sendHeka(array('order-status.change.init'));
        YooKassaLogger::info('Init changeOrderStatusToProcessing');
        if (!$order_id || !get_option('yookassa_enable_hold')) {
            YooKassaLogger::sendHeka(array('order-status.change.skip'));
            return;
        }

        $order     = wc_get_order($order_id);
        $paymentId = $order->get_transaction_id();

        if (!$this->isYooKassaOrder($order)) {
            YooKassaLogger::info('Payment method is not YooKassa!');
            YooKassaLogger::sendHeka(array('order-status.change.skip'));
            return;
        }

        try {
            $payment = $this->getApiClient()->getPaymentInfo($paymentId);
            $paymentMethod = $payment->paymentMethod;
            if ($paymentMethod->getType() === PaymentMethodType::SBER_LOAN && $paymentMethod->getDiscountAmount()) {
                /** @var PaymentMethodSberLoan $paymentMethod */
                $order->set_total($order->get_total() - $paymentMethod->getDiscountAmount()->value);
            }
            $payment = YooKassaHandler::capturePayment($this->getApiClient(), $order, $payment);
            if ($payment->getStatus() === PaymentStatus::SUCCEEDED) {
                $order->payment_complete($payment->getId());
                $order->add_order_note(__('Вы подтвердили платёж в ЮKassa.', 'yookassa'));
            } elseif ($payment->getStatus() === PaymentStatus::CANCELED) {
                YooKassaHandler::cancelOrder($order, $payment);
                $order->add_order_note(__('Платёж не подтвердился. Попробуйте ещё раз.', 'yookassa'));
            } else {
                $order->update_status(YooKassaOrderHelper::WC_STATUS_ON_HOLD);
                $order->add_order_note(__('Платёж не подтвердился. Попробуйте ещё раз.', 'yookassa'));
            }
            YooKassaLogger::sendHeka(array('order-status.change.success'));
        } catch (Exception $e) {
            $order->update_status(YooKassaOrderHelper::WC_STATUS_ON_HOLD);
            $order->add_order_note(__('Платёж не подтвердился. Попробуйте ещё раз.', 'yookassa'));
            YooKassaLogger::error('Api error: '.$e->getMessage());
            YooKassaLogger::sendAlertLog('Api error', array(
                'methodid' => 'POST/changeOrderStatusToProcessing',
                'exception' => $e,
            ), array('order-status.change.fail'));
        }
    }

    /**
     * @param int $order_id
     *
     * @throws Exception
     */
    public function changeOrderStatusToCancelled($order_id)
    {
        YooKassaLogger::sendHeka(array('order-status.change.init'));
        YooKassaLogger::info('Init changeOrderStatusToCancelled');
        if (!$order_id || !get_option('yookassa_enable_hold')) {
            YooKassaLogger::sendHeka(array('order-status.change.skip'));
            return;
        }

        $order     = wc_get_order($order_id);
        $paymentId = $order->get_transaction_id();

        if (!$this->isYooKassaOrder($order)) {
            YooKassaLogger::info('Payment method is not YooKassa!');
            YooKassaLogger::sendHeka(array('order-status.change.skip'));
            return;
        }

        try {
            $payment = $this->getApiClient()->cancelPayment($paymentId);
            if ($payment->getStatus() === PaymentStatus::CANCELED) {
                $order->add_order_note(__('Вы отменили платёж в ЮKassa. Деньги вернутся клиенту.', 'yookassa'));
            } else {
                $order->update_status(YooKassaOrderHelper::WC_STATUS_ON_HOLD);
                $order->add_order_note(__('Платёж не отменился. Попробуйте ещё раз.', 'yookassa'));
            }
            YooKassaLogger::sendHeka(array('order-status.change.success'));
        } catch (Exception $e) {
            $order->update_status(YooKassaOrderHelper::WC_STATUS_ON_HOLD);
            $order->add_order_note(__('Платёж не отменился. Попробуйте ещё раз.', 'yookassa'));
            YooKassaLogger::error('Api error: '.$e->getMessage());
            YooKassaLogger::sendAlertLog('Api error', array(
                'methodid' => 'POST/changeOrderStatusToCancelled',
                'exception' => $e,
            ), array('order-status.change.fail'));
        }
    }

    public function getAccountSavedPaymentMethodsListItem($item, $payment_token)
    {
        if ('ym' === strtolower($payment_token->get_type())) {
            $item['method']['last4'] = $payment_token->get_last4();
            $item['method']['brand'] = esc_html__('Кошелек ЮMoney', 'yookassa');
        }

        return $item;
    }

    /**
     * @param $payment
     * @return bool
     */
    protected function cancelPayment($payment)
    {
        $apiClient = $this->getApiClient();
        if ($payment->getStatus() === PaymentStatus::WAITING_FOR_CAPTURE) {
            try {
                $response = $apiClient->cancelPayment($payment->getId());
            } catch (Exception $e) {
                YooKassaLogger::info($e->getMessage());
                return false;
            }

            if ($response->getStatus() === PaymentStatus::CANCELED) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return Client
     */
    private function getApiClient()
    {
        return YooKassaClientFactory::getYooKassaClient();
    }

    /**
     * @param $paymentMethod
     * @param $payment
     *
     * @return WC_Payment_Token|WC_Payment_Token_CC|null
     */
    protected function prepareToken($paymentMethod, $payment)
    {
        $gatewayId = get_option('yookassa_pay_mode') == '1' ? 'yookassa_epl' : 'yookassa_widget';
        $userId = $payment->getMetadata()->offsetGet('wp_user_id');

        if (in_array($paymentMethod->getType(), [PaymentMethodType::BANK_CARD, PaymentMethodType::SBERBANK, PaymentMethodType::TINKOFF_BANK], true)) {
            if (!empty($userId)) {
                $tokens = WC_Payment_Tokens::get_customer_tokens($userId, $gatewayId);
                foreach ($tokens as $token) {
                    if ($token->get_token() == $paymentMethod->id || $token->get_last4() == $paymentMethod->getLast4()) {
                        return $token;
                    }
                }
            }

            $token = new WC_Payment_Token_CC();
            $card = $paymentMethod->getCard();
            /** @var PaymentMethodBankCard $paymentMethod */
            $token->set_card_type($card->getCardType());
            $token->set_last4($card->getLast4());
            $token->set_expiry_month($card->getExpiryMonth());
            $token->set_expiry_year($card->getExpiryYear());
        } elseif ($paymentMethod->getType() === PaymentMethodType::YOO_MONEY) {
            $accountLast4 = substr($paymentMethod->getAccountNumber(), -4);
            if (!empty($userId)) {
                $tokens = WC_Payment_Tokens::get_customer_tokens($userId, $gatewayId);
                foreach ($tokens as $token) {
                    if ($token->get_token() == $paymentMethod->id || $token->get_last4() == $accountLast4) {
                        return $token;
                    }
                }
            }

            $token = new WC_Payment_Token_YooKassa();
            /** @var PaymentMethodYooMoney $paymentMethod */
            $token->set_last4($accountLast4);
        } elseif ($paymentMethod->getType() === PaymentMethodType::SBP) {
            if (!empty($userId)) {
                $tokens = WC_Payment_Tokens::get_customer_tokens($userId, $gatewayId);
                foreach ($tokens as $token) {
                    if ($token->get_token() == $paymentMethod->id) {
                        return $token;
                    }
                }
            }
            $token = new WC_Payment_Token_SBP();
        } else {
            return null;
        }

        $token->set_gateway_id($gatewayId);
        $token->set_token($paymentMethod->id);
        $token->set_user_id($userId);

        return $token;
    }

    /**
     * @param WC_Order $order
     * @return bool
     */
    private function isYooKassaOrder(WC_Order $order)
    {
        $wcPaymentMethod = $order->get_payment_method();
        YooKassaLogger::info('Check PaymentMethod: ' . $wcPaymentMethod);

        return (strpos($wcPaymentMethod, 'yookassa_') !== false);
    }
}
