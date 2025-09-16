<?php


use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Model\ConfirmationType;
use YooKassa\Model\PaymentMethodType;
use YooKassa\Model\PaymentStatus;
use YooKassa\Request\Payments\CreatePaymentRequest;
use YooKassa\Request\Payments\CreatePaymentRequestBuilder;
use YooKassa\Request\Payments\CreatePaymentRequestSerializer;
use YooKassa\Request\Payments\CreatePaymentResponse;

class YooKassaWidgetGateway extends YooKassaGateway
{
    const PAY_BY_SHOP_SIDE = 0;
    const PAY_BY_YOOMONEY_SIDE = 1;

    public $paymentMethod = PaymentMethodType::BANK_CARD;

    public $confirmationType = ConfirmationType::EMBEDDED;

    public $id = 'yookassa_widget';

    public function __construct()
    {
        parent::__construct();

        $this->icon               = YooKassa::$pluginUrl . 'assets/images/kassa.png';

        $this->method_title       = __('Виджет ЮKassa', 'yookassa');
        $this->method_description = __('Покупатель сможет выбрать способ оплаты в платёжной форме, которая встроена в ваш сайт — переходить на нашу страницу для оплаты не нужно.', 'yookassa');

        $this->defaultTitle       = __('Онлайн-оплата', 'yookassa');
        $this->defaultDescription = __('Банковской картой или другими способами', 'yookassa');

        $this->title              = $this->getTitle();
        $this->description        = $this->getDescription();

        $this->enableRecurrentPayment = $this->get_option('save_payment_method') === 'yes';
        $this->supports               = array_merge($this->supports, array(
            'subscriptions',
            'tokenization',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_date_changes',
        ));
        $this->has_fields             = true;

        add_action('admin_notices', array($this, 'initial_notice'));
        add_action('template_redirect', array($this, 'load_widget_on_payment_pages'));

        if (!empty($_POST['action']) && $_POST['action'] === 'woocommerce_toggle_gateway_enabled'
            && !empty($_POST['gateway_id']) && $_POST['gateway_id'] === $this->id
        ) {
            //вызывается до переключение enable в yes
            if ($this->enabled === 'no') {
                $this->init_apple_pay();
            }
        } else if ($this->enabled === 'yes') {
            $this->init_apple_pay();
        }
    }

    public function load_widget_on_payment_pages()
    {
        if (
            (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) ||
            (strpos($_SERVER['REQUEST_URI'], '/checkout/order-pay/') !== false)
        ) {
            wp_register_script(
                'yookassa-widget',
                'https://static.yoomoney.ru/checkout-client/checkout-widget.js',
                array(),
                YOOKASSA_VERSION,
                true
            );
            wp_enqueue_script('yookassa-widget');
        }
    }

    public function init_form_fields()
    {
        parent::init_form_fields();
        $this->form_fields['save_payment_method'] = array(
            'title'   => __('Сохранять платежный метод', 'yookassa'),
            'type'    => 'checkbox',
            'label'   => __('Покупатели могут сохранять карту для повторной оплаты', 'yookassa'),
            'default' => 'no',
        );
    }

    public function is_available()
    {
        if (is_add_payment_method_page() && !$this->enableRecurrentPayment) {
            return false;
        }

        return parent::is_available();
    }

    public function payment_fields()
    {
        parent::payment_fields();
        $displayTokenization = $this->supports('tokenization') && is_checkout() && $this->enableRecurrentPayment;
        if ($displayTokenization) {
            $this->saved_payment_methods();
            $this->save_payment_method_checkbox();
        }
    }

    /**
     * Receipt Page
     *
     * @param int $order_id
     *
     * @throws Exception
     */
    public function receipt_page($order_id)
    {
        global $woocommerce;
        $order     = new WC_Order($order_id);
        $paymentId = $order->get_transaction_id();

        $data = array(
            'error' => '',
            'token' => '',
            'return_url' => get_site_url(null, sprintf(self::getReturnUrlPattern(), $order->get_order_key())),
            'payment_url' => $order->get_checkout_payment_url(),
        );

        try {
            $payment = $this->getApiClient()->getPaymentInfo($paymentId);
            if ($confirmation = $payment->getConfirmation()) {
                if ($confirmation->getType() === ConfirmationType::REDIRECT) {
                    if ($redirectUrl = $confirmation->getConfirmationUrl()) {
                        $data['error'] = '<p>' . __('Что-то пошло не так!', 'yookassa') . '</p>'
                            . '<p><a href="' . $order->get_checkout_payment_url() . '" target="_top" class="woocommerce-button button pay">'
                            . __('Попробовать заново', 'yookassa') . '</a></p>';
                    }
                } else {
                    $data['token'] = $payment->getConfirmation()->getConfirmationToken();
                }
            } else {
                if (in_array($payment->getStatus(), self::getValidPaidStatuses())
                    || ($payment->getStatus() === PaymentStatus::PENDING && $payment->getPaid())) {
                    $woocommerce->cart->empty_cart();
                    wp_redirect($this->get_success_fail_url('yookassa_success', $order));
                } else {
                    wp_redirect($this->get_success_fail_url('yookassa_fail', $order));
                }
            }

        } catch (ApiException $e) {
            $data['error'] = '<p>' . __('Что-то пошло не так!', 'yookassa') . '</p>'
                . '<p><a href="' . $order->get_checkout_payment_url() . '" target="_top" class="woocommerce-button button pay">'
                . __('Попробовать заново', 'yookassa') . '</a></p>';
            YooKassaLogger::error('Api error: ' . $e->getMessage());
            YooKassaLogger::sendAlertLog('Api error', array(
                'methodid' => 'GET/receipt_page',
                'exception' => $e,
            ));
        }

        $this->render('../includes/partials/widget.php', array(
            'data' => $data,
        ));

        if (empty($data['error'])) {
            $js = <<<JS
    document.addEventListener("DOMContentLoaded", function (event) {
        const checkout = new window.YooMoneyCheckoutWidget({
            confirmation_token: '{$data['token']}',
            return_url: '{$data['return_url']}',
            newDesign: true,
            error_callback: function (error) {
                if (error.error === 'token_expired') {
                    document.location.redirect('{$data['payment_url']}');
                }
                console.log(error);
            }
        });
        checkout.render('yookassa-widget-ui');
    });
JS;

            wp_add_inline_script('yookassa-widget', $js, 'after');
        }
    }

    public function process_admin_options()
    {
        if ($this->enabled === 'yes') {
            $this->init_apple_pay();
        }
        return parent::process_admin_options();
    }

    /**
     * Process the payment and return the result
     *
     * @param $order_id
     *
     * @return array
     * @throws WC_Data_Exception
     * @throws Exception
     */
    public function process_payment($order_id)
    {
        global $woocommerce;

        $order = new WC_Order($order_id);

        if (YooKassaHandler::isReceiptEnabled() && YooKassaHandler::isSelfEmployed()) {
            try {
                YooKassaHandler::checkConditionForSelfEmployed($order);
            } catch (Exception $e) {
                YooKassaLogger::error(sprintf(__('Не удалось создать платеж. Для заказа %1$s', 'yookassa'), $order_id) . ' ' . $e->getMessage());
                YooKassaNotice::front_notice_error($e->getMessage());
                YooKassaLogger::sendAlertLog('Create payment error', array(
                    'methodid' => 'POST/process_payment',
                    'exception' => $e,
                ));
                return array('result' => 'failure', 'redirect' => '');
            }
        }

        $result     = $this->createPayment($order);
        $receiptUrl = $order->get_checkout_payment_url(true);

        if ($result) {
            $order->set_transaction_id($result->id);
            $this->savePaymentData($result, $order);

            if ($result->status == PaymentStatus::PENDING) {
                $order->update_status('wc-pending');
                if (get_option('yookassa_force_clear_cart') == '1') {
                    $woocommerce->cart->empty_cart();
                }
                return array(
                    'result' => 'success',
                    'redirect' => $receiptUrl,
                );
            } elseif ($result->status == PaymentStatus::WAITING_FOR_CAPTURE) {
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_success_fail_url("yookassa_success", $order)
                );
            } elseif ($result->status == PaymentStatus::SUCCEEDED) {
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_success_fail_url('yookassa_success', $order),
                );
            } else {
                /* translators: %1$s - order_id */
                YooKassaLogger::warning(sprintf(__('Не удалось создать платеж. Для заказа %1$s', 'yookassa'), $order_id));
                YooKassaNotice::front_notice_error(__('Платеж не прошел. Попробуйте еще или выберите другой способ оплаты', 'yookassa'));
                $order->update_status('wc-cancelled');

                return array('result' => 'failure', 'redirect' => '');
            }
        } else {
            /* translators: %1$s - order_id */
            YooKassaLogger::warning(sprintf(__('Не удалось создать платеж. Для заказа %1$s', 'yookassa'), $order_id));
            YooKassaNotice::front_notice_error(__('Платеж не прошел. Попробуйте еще или выберите другой способ оплаты', 'yookassa'));

            return array('result' => 'failure', 'redirect' => '');
        }
    }

    /**
     * @param WC_Order $order
     *
     * @return CreatePaymentResponse|void|WP_Error
     * @throws Exception
     */
    public function createPayment($order)
    {
        if (!$order) {
            return;
        }

        $builder        = $this->getBuilder($order);
        $paymentRequest = $builder->build();
        /** if merchant wants to change */
        $paymentRequest = apply_filters('woocommerce_yookassa_create_payment_request', $paymentRequest);
        if (YooKassaHandler::isReceiptEnabled()) {
            $receipt = $paymentRequest->getReceipt();
            if ($receipt instanceof \YooKassa\Model\Receipt) {
                $receipt->normalize($paymentRequest->getAmount());
            }
        }
        $serializer     = new CreatePaymentRequestSerializer();
        $serializedData = $serializer->serialize($paymentRequest);
        YooKassaLogger::info('Create payment request: ' . json_encode($serializedData));
        YooKassaLogger::sendHeka(array('payment.request.init'));
        try {
            $response = $this->getApiClient()->createPayment($paymentRequest);
            YooKassaLogger::info('Create payment response: '.json_encode($response->toArray()));
            YooKassaLogger::sendHeka(array('payment.request.success'));
            return $response;
        } catch (ApiException $e) {
            YooKassaLogger::error('Api error: ' . $e->getMessage());
            YooKassaLogger::sendAlertLog('Api error', array(
                'methodid' => 'POST/createPayment',
                'exception' => $e,
            ), array('payment.request.fail'));
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    public function initial_notice()
    {
        if ($this->enabled === 'yes') {
            clearstatcache();
            if ($this->isVerifyApplePayFileExist()) {
                return;
            }
            echo '<div class="notice notice-warning is-dismissible"><p>' . __('Чтобы покупатели могли заплатить вам через Apple Pay, <a href="https://yookassa.ru/docs/merchant.ru.yandex.kassa">скачайте файл apple-developer-merchantid-domain-association</a> и добавьте его в папку ./well-known на вашем сайте. Если не знаете, как это сделать, обратитесь к администратору сайта или в поддержку хостинга. Не забудьте также подключить оплату через Apple Pay <a href="https://yookassa.ru/my/payment-methods/settings#applePay">в личном кабинете ЮKassa</a>. <a href="https://yookassa.ru/developers/payment-forms/widget#apple-pay-configuration">Почитать о подключении Apple Pay в документации ЮKassa</a>', 'yookassa') . '</p></div>';
        }
    }

    private function init_apple_pay()
    {
        clearstatcache();
        $rootDir = $_SERVER['DOCUMENT_ROOT'];
        $domainAssociationPath = $rootDir . '/.well-known/apple-developer-merchantid-domain-association';
        $pluginAssociationPath = YooKassa::$pluginUrl . 'apple-developer-merchantid-domain-association';
        if ($this->isVerifyApplePayFileExist()) {
            return false;
        }

        if (!file_exists($rootDir . '/.well-known')) {
            if (!@mkdir($concurrentDirectory = $rootDir . '/.well-known', 0755) && !is_dir($concurrentDirectory)) {
                YooKassaLogger::error("Error create dir $rootDir/.well-known");
                return false;
            }
        }

        if (!@copy($pluginAssociationPath, $domainAssociationPath)) {
            YooKassaLogger::error('Error copy association path');
            return false;
        }

        YooKassaLogger::info('Copy association path succeeded');
        return true;
    }

    /**
     *
     * @return bool
     */
    private function isVerifyApplePayFileExist()
    {
        $rootDir = $_SERVER['DOCUMENT_ROOT'];
        $domainAssociationPath = $rootDir . '/.well-known/apple-developer-merchantid-domain-association';
        return file_exists($domainAssociationPath);
    }

    /**
     * @param WC_Order $order
     * @param $save
     *
     * @return \YooKassa\Request\Payments\CreatePaymentRequestBuilder
     * @throws Exception
     */
    protected function getBuilder($order)
    {
        $enableHold = get_option('yookassa_enable_hold');

        $amount = YooKassaOrderHelper::getTotal($order);
        $metadata = $this->createMetadata();
        $metadata['cms_name'] = self::CMS_NAME_OLD;
        $builder = CreatePaymentRequest::builder()
            ->setAmount(YooKassaOrderHelper::getAmountByCurrency($amount))
            ->setDescription($this->createDescription($order))
            ->setCapture(!$enableHold)
            ->setConfirmation(array(
                'type' => ConfirmationType::EMBEDDED,
                'locale' => $this->getLocaleFromBrowser(),
            ))
            ->setMetadata($metadata);

        YooKassaLogger::info('Return url: ' . $order->get_checkout_payment_url(true));
        YooKassaHandler::setReceiptIfNeeded($builder, $order);
        if (
            is_user_logged_in()
            && get_option('yookassa_save_card')
            && get_option('yookassa_pay_mode') == self::PAY_BY_SHOP_SIDE
        ) {
            $this->setMerchantCustomerId($builder, $order);
        }

        return $builder;
    }

    private function getLocaleFromBrowser()
    {
        $locale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

        switch ($locale) {
            case 'ru': // Русский
            case 'uk': // Украинский
            case 'be': // Белорусский
            case 'az': // Азербайджанский
            case 'hy': // Армянский
            case 'kk': // Казахский
            case 'ky': // Киргизский
            case 'tg': // Таджикский
            case 'tk': // Туркменский
            case 'uz': // Узбекский
                $return = self::YM_LANG_RU;
                break;
            case 'de':
                $return = self::YM_LANG_DE;
                break;
            default:
                $return = self::YM_LANG_EN;
                break;
        }

        return $return;
    }

    private function render($viewPath, $args)
    {
        extract($args);

        include(plugin_dir_path(__FILE__) . $viewPath);
    }

}
