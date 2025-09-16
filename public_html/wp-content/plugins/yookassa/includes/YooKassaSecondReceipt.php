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
use YooKassa\Model\Receipt\PaymentMode;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;
use YooKassa\Request\Receipts\CreatePostReceiptRequest;
use YooKassa\Request\Receipts\ReceiptResponseInterface;
use YooKassa\Request\Receipts\ReceiptResponseItemInterface;

/**
 * The second-receipt functionality of the plugin.
 */
class YooKassaSecondReceipt
{
    /** @var string|null Тип кассовой системы */
    private $provider;

    /**
     * @var Client
     */
    private $apiClient;

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
    }

    /**
     * @return Client
     * @throws Exception
     */
    private function getApiClient()
    {
        return YooKassaClientFactory::getYooKassaClient();
    }

    /**
     * @return array
     */
    public static function getValidPaymentMode()
    {
        return array(
            PaymentMode::FULL_PREPAYMENT,
//        пока не нужно, возможно в скором времени будем делать и для них.
//        PaymentMode::PARTIAL_PREPAYMENT,
//        PaymentMode::ADVANCE,
        );
    }

    /**
     * @param int $order_id
     *
     * @throws Exception
     */
    public function changeOrderStatusToProcessing($order_id)
    {
        $this->changeOrderStatus($order_id, 'ChangeOrderStatusToProcessing');
    }

    /**
     * @param int $order_id
     *
     * @throws Exception
     */
    public function changeOrderStatusToCompleted($order_id)
    {
        $this->changeOrderStatus($order_id, 'ChangeOrderStatusToCompleted');
    }

    /**
     * @param ReceiptResponseInterface $lastReceipt
     * @param string $paymentId
     * @param WC_Order $order
     *
     * @return CreatePostReceiptRequest|null
     * @throws Exception
     */
    private function buildSecondReceipt($lastReceipt, $paymentId, $order)
    {
        YooKassaLogger::sendHeka(array('second-receipt.create.init'));
        if ($lastReceipt instanceof ReceiptResponseInterface) {
            if ($lastReceipt->getType() === "refund") {
                YooKassaLogger::sendHeka(array('second-receipt.create.skip'));
                return null;
            }

            $resendItems = $this->getResendItems($lastReceipt->getItems(), $order);

            if (count($resendItems['items']) < 1) {
                YooKassaLogger::info('Second receipt is not required');
                YooKassaLogger::sendHeka(array('second-receipt.create.skip'));
                return null;
            }

            try {
                $customer = $this->getReceiptCustomer($order);

                if (empty($customer)) {
                    YooKassaLogger::error('Need customer phone or email for second receipt');
                    YooKassaLogger::sendHeka(array('second-receipt.create.fail'));
                    return null;
                }

                $receiptBuilder = CreatePostReceiptRequest::builder();
                $receiptBuilder->setObjectId($paymentId)
                    ->setType(ReceiptType::PAYMENT)
                    ->setObjectType(ReceiptType::PAYMENT)
                    ->setItems($resendItems['items'])
                    ->setSettlements(
                        array(
                            new Settlement(
                                array(
                                    'type' => 'prepayment',
                                    'amount' => array(
                                        'value' => $resendItems['amount'],
                                        'currency' => 'RUB',
                                    ),
                                )
                            ),
                        )
                    )
                    ->setCustomer($customer)
                    ->setSend(true);

                if (YooKassaHandler::isLegalEntity()) {
                    $taxSystemCode = $lastReceipt->getTaxSystemCode() ?: get_option('yookassa_default_tax_system_code');
                    if (!empty($taxSystemCode)) {
                        $receiptBuilder->setTaxSystemCode($taxSystemCode);
                    }
                }

                $receipt = $receiptBuilder->build();
                YooKassaLogger::sendHeka(array('second-receipt.create.success'));
                return $receipt;
            } catch (Exception $e) {
                YooKassaLogger::error($e->getMessage() . '. Property name: '. $e->getProperty());
                YooKassaLogger::sendAlertLog('Build SecondReceipt error', array(
                    'methodid' => 'POST/buildSecondReceipt',
                    'exception' => $e,
                ), array('second-receipt.create.fail'));
            }
        }
        YooKassaLogger::sendHeka(array('second-receipt.create.skip'));
        return null;
    }

    /**
     * @param WC_Order $order
     * @return bool|ReceiptCustomer
     */
    private function getReceiptCustomer($order)
    {
        $customerData = array();

        if (!empty($order->get_billing_email())) {
            $customerData['email'] = $order->get_billing_email();
        }

        if (!empty($order->get_billing_phone())) {
            $customerData['phone'] = preg_replace('/[^\d]/', '', $order->get_billing_phone());
        }

        return new ReceiptCustomer($customerData);
    }

    /**
     * @param ReceiptResponseInterface $response
     * @return float
     */
    private function getSettlementsAmountSum($response)
    {
        $amount = 0;

        foreach ($response->getSettlements() as $settlement) {
            $amount += $settlement->getAmount()->getIntegerValue();
        }

        return number_format($amount / 100.0, 2, '.', ' ');
    }

    /**
     * @param ReceiptResponseItemInterface[] $items
     *
     * @return array
     * @throws Exception
     */
    private function getResendItems($items, WC_Order $order)
    {
        $orderId = $order->get_id();
        $itemsCount = count($items);
        $markingEnabled = get_option('yookassa_marking_enabled') && $this->provider !== null;

        YooKassaLogger::info(sprintf(
            '[Order #%d] Starting items processing. Items: %d, Marking enabled: %s',
            $orderId,
            $itemsCount,
            $markingEnabled ? 'yes' : 'no'
        ));

        $result = array(
            'items'  => array(),
            'amount' => 0,
        );

        if (empty($items)) {
            YooKassaLogger::info(sprintf(
                '[Order #%d] No items to process',
                $orderId
            ));
            return $result;
        }

        $orderItems = $order->get_items();

        foreach ($items as $item) {
            if (!$this->isNeedResendItem($item->getPaymentMode())) {
                continue;
            }

            $item->setPaymentMode(PaymentMode::FULL_PAYMENT);
            try {
                if ($markingEnabled) {
                    $processedItems = $this->processItem($item, $orderItems, new YooKassaMarkingCodeHandler($this->provider));
                    $result['items'] = array_merge($result['items'], $processedItems);
                } else {
                    $result['items'][] = new ReceiptItem($item->jsonSerialize());
                }
                $result['amount'] += $item->getAmount() / 100.0;
            } catch (Exception $e) {
                YooKassaLogger::error(sprintf(
                    'Error processing item "%s": %s.',
                    $item->getDescription(),
                    $e->getMessage()
                ));
                throw $e;
            }
        }

        return $result;
    }

    /**
     * Обрабатывает один товар, включая маркировку
     *
     * @param ReceiptResponseItemInterface $item
     * @param WC_Order_Item[] $orderItems
     * @param YooKassaMarkingCodeHandler $markingCodeHandler
     * @return array
     * @throws Exception
     */
    private function processItem($item, $orderItems, $markingCodeHandler)
    {
        $productName = $item->getDescription();
        $productQuantity = (int)$item->getQuantity();
        $productPrice = $item->getAmount() / 100.0;

        YooKassaLogger::info(sprintf(
            'Processing item: Name="%s", Quantity=%d, Price=%.2f',
            $productName,
            $productQuantity,
            $productPrice
        ));

        foreach ($orderItems as $orderItem) {
            $itemData = $orderItem->get_data();
            $orderItemTotal = (int)$orderItem->get_total() + (int)$orderItem->get_total_tax();
            $orderItemName = mb_substr($itemData['name'], 0, ReceiptItem::DESCRIPTION_MAX_LENGTH);

            // Проверка соответствия товара
            if ($orderItemName !== $productName
                || (int)$productPrice !== $orderItemTotal
                || $productQuantity !== $itemData['quantity']
            ) {
                continue;
            }

            // Получаем оставшееся количество товаров после возвратов, если они были
            $productQuantity = YooKassaMarkingOrder::getRemainingQuantity($orderItem);
            $item->setQuantity($productQuantity);
            if ($productQuantity <= 0) {
                YooKassaLogger::info(sprintf(
                    'Item "%s" refunded, skipping receipt generation',
                    $productName
                ));
                continue;
            }

            // Получение и проверка продукта
            $productId = isset($itemData['product_id']) ? $itemData['product_id'] : null;
            if ($productId === null) {
                $error = sprintf('Product ID not found in order item data: %s', json_encode($itemData));
                YooKassaLogger::error($error);
                throw new Exception($error);
            }

            $product = wc_get_product($productId);
            if (!$product) {
                $error = sprintf('Product not found for ID: %d', $productId);
                YooKassaLogger::error($error);
                throw new Exception($error);
            }

            // Настройка маркировки
            $markingFields = wc_get_order_item_meta(
                $orderItem->get_id(),
                YooKassaMarkingOrder::MARKING_FIELD_META_KEY
            );

            YooKassaLogger::info(sprintf(
                'Setting marking for product ID %d. Marking fields exist: %s',
                $productId,
                $markingFields !== false ? 'yes' : 'no'
            ));

            $markingCodeHandler
                ->setQuantity($productQuantity)
                ->setCategory($product->get_meta(YooKassaMarkingProduct::CATEGORY_KEY))
                ->setMeasure($product->get_meta(YooKassaMarkingProduct::MEASURE_KEY))
                ->setDenominator($product->get_meta(YooKassaMarkingProduct::DENOMINATOR_KEY))
                ->setMarkCodeInfo($product->get_meta(YooKassaMarkingProduct::MARK_CODE_INFO_KEY))
                ->setMarkingFields($markingFields !== false ? $markingFields : array());

            break; // Прерываем после нахождения совпадения
        }

        return $markingCodeHandler->splitProductsByCode($item);
    }

    /**
     * @param string $status
     * @return bool
     */
    private function isNeedSecondReceipt($status)
    {
        $status = $this->convertToWCStatus($status);
        YooKassaLogger::info('New status of order is ' . $status . '. We need is ' . $this->getSecondReceiptStatus() . '!');

        if (!$this->isSendReceiptEnable()) {
            return false;
        } elseif (!$this->isSecondReceiptEnable()) {
            return false;
        } elseif ($this->getSecondReceiptStatus() != $status) {
            return false;
        }

        return true;
    }

    /**
     * @param string $paymentMode
     *
     * @return bool
     */
    private function isNeedResendItem($paymentMode)
    {
        return in_array($paymentMode, self::getValidPaymentMode());
    }

    /**
     * @param $paymentId
     * @return mixed|ReceiptResponseInterface
     * @throws ApiException
     * @throws BadApiRequestException
     * @throws ForbiddenException
     * @throws InternalServerError
     * @throws NotFoundException
     * @throws ResponseProcessingException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    private function getLastReceipt($paymentId)
    {
        $receipts = $this->getApiClient()->getReceipts(array('payment_id' => $paymentId))->getItems();

        return array_pop($receipts);
    }

    /**
     * @return bool
     */
    private function isSendReceiptEnable()
    {
        return (bool) get_option('yookassa_enable_receipt');
    }

    /**
     * @return bool
     */
    private function isSecondReceiptEnable()
    {
        return (bool) get_option('yookassa_enable_second_receipt');
    }

    /**
     * @return string
     */
    private function getSecondReceiptStatus()
    {
        return get_option('yookassa_second_receipt_order_status');
    }

    /**
     * Добавляет префикс 'wc-' для текущего статуса, если его нет
     *
     * @param string $status
     * @return string
     */
    private function convertToWCStatus($status)
    {
        return 'wc-' . ('wc-' === substr($status, 0, 3) ? substr($status, 3) : $status);
    }

    /**
     * @param int $order_id
     * @param string $type
     */
    private function changeOrderStatus($order_id, $type)
    {
        if (YooKassaHandler::isSelfEmployed()) {
            return;
        }

        YooKassaLogger::sendHeka(array('second-receipt.webhook.init'));
        YooKassaLogger::info('Init YooKassaSecondReceipt::' . $type);

        if (!$order_id) {
            YooKassaLogger::info('Order ID is empty!');
            YooKassaLogger::sendHeka(array('second-receipt.webhook.skip'));
            return;
        }

        $order = wc_get_order($order_id);
        $paymentId = $order->get_transaction_id();

        if (!$this->isYooKassaOrder($order)) {
            YooKassaLogger::info('Payment method is not YooKassa!');
            YooKassaLogger::sendHeka(array('second-receipt.webhook.skip'));
            return;
        }

        if (!$this->isNeedSecondReceipt($order->get_status())) {
            YooKassaLogger::info('Second receipt is not need!');
            YooKassaLogger::sendHeka(array('second-receipt.webhook.skip'));
            return;
        }

        YooKassaLogger::info($type . ' PaymentId: ' . $paymentId);

        try {
            if (!($lastReceipt = $this->getLastReceipt($paymentId))) {
                YooKassaLogger::info($type . ' LastReceipt is empty!');
                YooKassaLogger::sendHeka(array('second-receipt.webhook.fail'));
                return;
            }

            YooKassaLogger::info($type . ' LastReceipt:' . PHP_EOL . json_encode($lastReceipt->jsonSerialize()));

            $this->provider = $this->getProvider();
            if ($receiptRequest = $this->buildSecondReceipt($lastReceipt, $paymentId, $order)) {
                $this->processReceiptSending($receiptRequest, $order);
            } else {
                YooKassaLogger::sendHeka(array('second-receipt.webhook.fail'));
            }
        } catch (Exception $e) {
            YooKassaLogger::error($type . ' Error: ' . $e->getMessage());
            YooKassaLogger::sendAlertLog('Change Order Status error', array(
                'methodid' => 'POST/changeOrderStatus',
                'exception' => $e,
            ), array('second-receipt.webhook.fail'));
            return;
        }
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

    /**
     * Получение провайдера
     *
     * @return string|null
     */
    private function getProvider()
    {
        $shopInfo = YooKassaAdmin::getShopInfo();
        return isset($shopInfo['fiscalization']['provider']) ? $shopInfo['fiscalization']['provider'] : null;
    }

    /**
     * Обработка отправки чека с учетом лимитов
     *
     * @param CreatePostReceiptRequest $receiptRequest
     * @param WC_Order $order
     */
    protected function processReceiptSending($receiptRequest, $order)
    {
        $items = $receiptRequest->getItems();
        $limit = ($this->provider === 'avanpost') ? 80 : 100;

        if (count($items) <= $limit) {
            $this->sendSingleReceipt($receiptRequest, $order);
            return;
        }

        $this->sendSplitReceipts($receiptRequest, $order, $limit);
    }

    /**
     * Отправка единого чека
     *
     * @param CreatePostReceiptRequest $receipt
     * @param WC_Order $order
     */
    private function sendSingleReceipt($receipt, $order)
    {
        $orderId = $order->get_id();
        YooKassaLogger::sendHeka(array('second-receipt.send.init'));
        YooKassaLogger::info(sprintf(
            'Starting single receipt for order #%d. Receipt data: %s',
            $orderId,
            json_encode($receipt->jsonSerialize())
        ));

        try {
            $receipt = apply_filters('woocommerce_yookassa_second_receipt_request', $receipt);
            $response = $this->getApiClient()->createReceipt($receipt);

            if ($response === null) {
                $error = sprintf('Failed to create receipt (null response) for order #%d', $orderId);
                YooKassaLogger::error($error);
                throw new Exception($error);
            }

            $amount = $this->getSettlementsAmountSum($response);
            $order->add_order_note(
                sprintf(__('Отправлен второй чек. Сумма %s рублей.', 'yookassa'), $amount)
            );

            YooKassaLogger::info(sprintf(
                'Successfully sent single receipt for order #%d. Amount: %.2f RUB',
                $orderId,
                $amount
            ));
            YooKassaLogger::sendHeka(array('second-receipt.send.success', 'second-receipt.webhook.success'));
        } catch (Exception $e) {
            YooKassaLogger::error(sprintf(
                'Error sending single receipt for order #%d: %s.',
                $orderId,
                $e->getMessage()
            ));
            YooKassaLogger::sendAlertLog(
                sprintf('Request second receipt error for order #%d', $orderId),
                array(
                    'methodid' => 'POST/changeOrderStatus',
                    'exception' => $e,
                ),
                array('second-receipt.send.fail')
            );
        }
    }

    /**
     * Отправка разделенных чеков
     *
     * @param CreatePostReceiptRequest $originalReceipt
     * @param WC_Order $order
     * @param int $limit
     */
    private function sendSplitReceipts($originalReceipt, $order, $limit)
    {
        $orderId = $order->get_id();
        $items = $originalReceipt->getItems();
        $itemParts = array_chunk($items, $limit);
        $totalParts = count($itemParts);

        YooKassaLogger::info(sprintf(
            'Starting split receipts for order #%d. Total items: %d, parts: %d, limit per part: %d',
            $orderId,
            count($items),
            $totalParts,
            $limit
        ));
        YooKassaLogger::sendHeka(array('second-receipt.send.init'));

        foreach ($itemParts as $index => $parts) {
            $partNumber = $index + 1;

            try {
                // Подготовка части чека
                $receiptPart = clone $originalReceipt;
                $receiptPart->setItems($parts);

                // Получение суммы для части
                $partAmount = $this->calculateTotalAmount($receiptPart->getItems());

                $receiptPart->setSettlements(array(
                    new Settlement(array(
                        'type' => 'prepayment',
                        'amount' => array(
                            'value' => $partAmount,
                            'currency' => 'RUB',
                        ),
                    ))
                ));

                // Применение фильтров
                $receiptPart = apply_filters('woocommerce_yookassa_second_receipt_request', $receiptPart);

                // Отправка части
                YooKassaLogger::info(sprintf(
                    'Sending receipt part %d/%d for order #%d. Items count: %d, amount: %.2f RUB',
                    $partNumber,
                    $totalParts,
                    $orderId,
                    count($parts),
                    $partAmount
                ));
                $response = $this->getApiClient()->createReceipt($receiptPart);

                if ($response === null) {
                    $error = sprintf(
                        'Failed to create receipt part %d/%d for order #%d (null response)',
                        $partNumber,
                        $totalParts,
                        $orderId
                    );
                    YooKassaLogger::error($error);
                    throw new Exception($error);
                }

                // Получаем сумму из ответа
                $partAmount = $this->getSettlementsAmountSum($response);

                // Логирование успеха
                $order->add_order_note(
                    sprintf(__('Отправлен второй чек (часть %d/%d). Сумма %s рублей.', 'yookassa'),
                        $partNumber,
                        $totalParts,
                        $partAmount
                    )
                );

                YooKassaLogger::info(sprintf(
                    'Successfully sent receipt part %d/%d for order #%d. Amount: %.2f RUB. Response: %s',
                    $partNumber,
                    $totalParts,
                    $orderId,
                    $partAmount,
                    json_encode($response->jsonSerialize())
                ));
                YooKassaLogger::sendHeka(array('second-receipt.send.success'));
            } catch (Exception $e) {
                YooKassaLogger::error(sprintf(
                    'Error sending receipt part %d/%d for order #%d: %s.',
                    $partNumber,
                    $totalParts,
                    $orderId,
                    $e->getMessage()
                ));
                YooKassaLogger::sendAlertLog(
                    sprintf('Receipt part %d/%d error for order #%d', $partNumber, $totalParts, $orderId),
                    array(
                        'part' => $partNumber,
                        'total' => $totalParts,
                        'exception' => $e
                    ),
                    array('second-receipt.send.fail')
                );
            }
        }

        YooKassaLogger::sendHeka(array('second-receipt.webhook.success'));
    }

    /**
     * Вычисляет общую сумму из массива товаров
     *
     * @param ReceiptItemInterface[] $items
     * @return float
     */
    private function calculateTotalAmount($items)
    {
        return array_reduce($items, static function($total, $item) {
            return $total + $item->getPrice()->getValue() * $item->getQuantity();
        }, 0);
    }
}
