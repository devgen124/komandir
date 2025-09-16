<?php

use YooKassa\Helpers\ProductCode;

/**
 * Класс по добавлению маркировки товара в заказе
 */
class YooKassaMarkingOrder
{
    /* @var string Название поля отсканированной маркировки */
    const MARKING_FIELD = 'marking_field';

    /* @var string meta_key для хранения отсканированных полей маркировки в БД (_marking_fields) */
    const MARKING_FIELD_META_KEY = '_' . self::MARKING_FIELD . 's';

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
     * @param string $plugin_name
     * @param string $version
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Добавляем вкладку "Маркировка" в таблицу товаров
     *
     * @param $item
     * @return void
     */
    public function addMarkingProductHeadersTab($item)
    {
        $this->enqueue_custom_order_marking_script();
        $this->enqueue_custom_order_marking_style();
        $this->render(
            'marking/marking_order/marking_order_tab_header.php',
            array()
        );
    }

    /**
     * Добавляем кнопку "Маркировка" в таблицу товаров
     *
     * @param WC_Product|null $product
     * @param WC_Order_Item $item
     * @param int $item_id
     * @return void
     */
    public function addMarkingProductValuesTab($product, $item, $item_id)
    {
        try {
            if (!$item instanceof WC_Order_Item_Product || !$product) {
                // Выводим пустую ячейку если это не товар (доставка, купон, подарок),
                // чтобы не съезжала таблица
                echo '<td></td>';
                return;
            }

            if ($product->is_virtual() || $product->is_downloadable()) {
                // Выводим пустую ячейку если это виртуальный товар или скачиваемый,
                // чтобы не съезжала таблица
                echo '<td></td>';
                return;
            }

            $categoryMeta = $product->get_meta(YooKassaMarkingProduct::CATEGORY_KEY);
            if (empty($categoryMeta)) {
                // проверяем, что товар вариативный и ищем категорию у него
                if ($product->is_type('variation')) {
                    $parent_product = wc_get_product($product->get_parent_id());
                    $categoryMeta = $parent_product->get_meta(YooKassaMarkingProduct::CATEGORY_KEY);
                }

                if (empty($categoryMeta)) {
                    $this->renderNoMarkingView(__('Не требуется', 'yookassa'));
                    return;
                }
            }

            $itemMeta = wc_get_order_item_meta($item_id, self::MARKING_FIELD_META_KEY, true);
            $order = wc_get_order($item->get_order_id());
            if (!$order) {
                YooKassaLogger::error(sprintf(
                    'Error getting order for item. OrderId: %d, ProductId: %d, ItemId: %d',
                    $item->get_order_id(),
                    $item->get_product_id(),
                    $item_id
                ));
                $this->renderErrorView(__('Не смогли загрузить карточку с маркировкой. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
                return;
            }

            $remainingQuantity = self::getRemainingQuantity($item);
            if ($remainingQuantity <= 0) {
                $this->renderNoMarkingView(__('Не требуется', 'yookassa'));
                return;
            }

            $iconClass = 'new';
            if (!empty($itemMeta)) {
                $isFilled = $this->isAllMarkingFieldsFilledInItem($item, $remainingQuantity);

                $notAllFilled = 'not-filled';
                $filled = 'filled';

                $iconClass = $isFilled ? $filled : $notAllFilled;
            }

            // Генерируем уникальный ID для кнопки по $item_id
            $buttonId = 'yookassa-marking-button-' . $item_id;

            $this->render(
                'marking/marking_order/marking_order_tab_button.php',
                array(
                    'buttonId' => $buttonId,
                    'iconClass' => $iconClass,
                )
            );
        } catch (Exception $e) {
            YooKassaLogger::error(sprintf(
                'Unexpected error while adding marking product values tab. Error: %s, ItemId: %d, Trace: %s',
                $e->getMessage(),
                $item_id,
                $e->getTraceAsString()
            ));

            $this->renderErrorView(
                __('Не смогли загрузить карточку с маркировкой. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa')
            );
        }
    }

    /**
     * Возвращает значение количества товара в заказе,
     * после применения числа возвращенных товаров
     *
     * @param WC_Order_Item $item
     * @return int
     */
    public static function getRemainingQuantity($item)
    {
        $order = $item->get_order();
        $quantity = $item->get_quantity();
        $refundedQuantity = $order->get_qty_refunded_for_item($item->get_id());
        // Вычисляем оставшееся количество
        return $quantity + $refundedQuantity; // refunded_quantity отрицательный
    }

    /**
     * Отрисовывает ячейку для товара, у которого нет категории маркировки
     *
     * @param string $message
     * @return void
     */
    protected function renderNoMarkingView($message)
    {
        $this->render(
            'marking/marking_order/marking_order_tab_no_marking.php',
            [
                'message' => $message,
                'style' => 'color: gray;'
            ]
        );
    }

    /**
     * Отрисовывает ячейку с ошибкой
     *
     * @param string $tooltip
     * @param string $message
     * @return void
     */
    protected function renderErrorView($tooltip, $message = '')
    {
        $this->render(
            'marking/marking_order/marking_order_tab_no_marking.php',
            [
                'icon' => '⚠',
                'tooltip' => $tooltip,
                'message' => $message,
                'style' => 'color: red;'
            ]
        );
    }

    /**
     * Отрисовывает модальное окно с формой маркировки
     *
     * @return void
     */
    public function addMarkingProductPopup()
    {
        $this->render(
            'marking/marking_order/marking_order_popup.php',
            array()
        );
    }

    /**
     * Отрисовывает новый таб с маркировкой
     *
     * @param string $viewPath
     * @param array $args
     * @return void
     */
    private function render($viewPath, $args)
    {
        extract($args);
        include(plugin_dir_path(__FILE__) . $viewPath);
    }

    /**
     * Добавляет скрипт на страницу
     * с редактированием заказа
     *
     * @return void
     */
    private function enqueue_custom_order_marking_script()
    {
        wp_register_script(
            $this->plugin_name . '-order-marking',
            YooKassa::$pluginUrl . 'assets/js/yookassa-order-marking.js',
            array('jquery'),
            $this->version,
            true
        );
        wp_enqueue_script($this->plugin_name . '-order-marking');
        wp_localize_script($this->plugin_name . '-order-marking', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce_save_marking_meta' => wp_create_nonce('save_marking_meta_nonce'),
            'nonce_get_order_item_meta' => wp_create_nonce('woocommerce_get_oder_item_meta_nonce'),
        ]);
    }

    /**
     * Добавляет стили на страницу
     * с редактированием заказа
     *
     * @return void
     */
    private function enqueue_custom_order_marking_style()
    {
        wp_register_style(
                $this->plugin_name . '-order-marking',
                YooKassa::$pluginUrl . 'assets/css/yookassa-order-marking.css',
                array(),
                $this->version,
                'all'
        );
        wp_enqueue_style( $this->plugin_name . '-order-marking' );
    }

    /**
     * Выгружает для AJAX запроса метаданные маркировки,
     * количество и название товара, плейсхолдер для инпутов
     *
     * @return void
     */
    public function getOderItemMetaCallback()
    {
        try {
            check_ajax_referer('woocommerce_get_oder_item_meta_nonce', 'security');

            $itemId = isset($_POST['itemId']) ? (int)$_POST['itemId'] : 0;
            if (!$itemId) {
                YooKassaLogger::error(
                    'Error while getting order item meta: required ItemId parameter was not passed.'
                );
                wp_send_json_error(__('Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
            }

            $item = WC_Order_Factory::get_order_item($itemId);
            if (!$item) {
                YooKassaLogger::error(sprintf(
                    'Error while getting order item meta: got error while getting item. ItemId: %d',
                    $itemId
                ));
                wp_send_json_error(__('Не смогли найти товар. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
            }

            $remainingQuantity = self::getRemainingQuantity($item);

            if ($remainingQuantity <= 0) {
                YooKassaLogger::error(sprintf(
                    'Error while getting order item meta: quantity of products is 0. ItemId: %d, Quantity: %d',
                    $itemId,
                    $remainingQuantity
                ));
                wp_send_json_error(__('Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
            }

            $productId = wc_get_order_item_meta($itemId, '_product_id');
            $product = $productId ? wc_get_product($productId) : null;

            if (!$product) {
                YooKassaLogger::error(sprintf(
                    'Error while getting order item meta: got error while getting product. ProductId: %d, ItemId: %d',
                    $productId,
                    $itemId
                ));
                wp_send_json_error(__('Не смогли найти товар. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
            }

            $productDenominator = $product->get_meta(YooKassaMarkingProduct::DENOMINATOR_KEY);
            $productMeasure = $product->get_meta(YooKassaMarkingProduct::MEASURE_KEY);
            // Если товара в упаковке больше 1 и единица измерения штука, то выключаем проверку на уникальность маркировки
            $isCheckDuplicateMarking = !(
                isset($productDenominator, $productMeasure)
                && (int)$productDenominator > 1
                && $productMeasure === 'piece'
            );

            $response = [
                'quantity' => $remainingQuantity,
                'title' => $this->generateProductLink($product),
                'fields' => [
                    [
                        'name' => self::MARKING_FIELD,
                        'placeholder' => __('Отсканируйте маркировку с упаковки', 'yookassa'),
                        'validate' => [
                            'pattern' => '/^[A-Za-z0-9!"%&\'()*+,\-.\/_:;=<>?]+$/',
                            'isEmpty' => true,
                            'isDuplicate' => $isCheckDuplicateMarking,
                            'denominator' => $productDenominator ?: null,
                        ]
                    ],
                ],
                'jsonMeta' => json_encode(wc_get_order_item_meta($itemId, self::MARKING_FIELD_META_KEY)) ?: '{}'
            ];

            wp_send_json_success($response);
        } catch (Exception $e) {
            YooKassaLogger::error(sprintf(
                'Unexpected error while getting order item meta: %s, Trace: %s',
                $e->getMessage(),
                $e->getTraceAsString()
            ));

            wp_send_json_error(__('Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'));
        }
    }

    /**
     * Генерирует ссылку на страницу редактирования товара
     *
     * @param WC_Product $product
     * @return string
     */
    protected function generateProductLink(WC_Product $product)
    {
        $url = admin_url('post.php?post=' . $product->get_id() . '&action=edit');
        $name = $product->get_name();

        return sprintf(
            __('Маркировка для %s', 'yookassa'),
            sprintf('<a href="%s" target="_blank">%s</a>', esc_url($url), esc_html($name))
        );
    }

    /**
     * Сохраняет метаданные маркировки
     *
     * @return void
     */
    public function saveMarkingMetaCallback()
    {
        try {
            check_ajax_referer('save_marking_meta_nonce', 'security');

            if (!current_user_can('edit_shop_orders')) {
                YooKassaLogger::error('Error while saving marking meta: user is not admin or has no edit permissions.');
                wp_send_json_error([
                    'message' => __('Нет прав на сохранение маркировки — проверьте доступы в настройках', 'yookassa'),
                    'type' => 'permission_error'
                ]);
            }

            $orderItemId = isset($_POST['orderItemId']) ? (int)$_POST['orderItemId'] : 0;
            $markingFields = isset($_POST[self::MARKING_FIELD_META_KEY]) ? $_POST[self::MARKING_FIELD_META_KEY] : [];

            if (!$orderItemId || empty($markingFields)) {
                YooKassaLogger::warning(sprintf(
                    'Error while saving marking meta: required parameters orderItemId or markingFields were not passed: OrderItemId: %d. MarkingFields: %s',
                    $orderItemId,
                    json_encode($markingFields)
                ));
                wp_send_json_error([
                    'message' => __('Что-то пошло не так. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'),
                    'type' => 'invalid_data'
                ]);
            }

            $errorFields = $this->checkCode($orderItemId, $markingFields);

            if (!empty($errorFields)) {
                wp_send_json_error([
                    'message' => __('Где-то указаны неверные данные. Нужно указать код маркировки (не штрихкод, QR-код или другой текст)', 'yookassa'),
                    'type' => 'validation_error',
                    'fields' => $errorFields,
                ]);
            }

            wc_update_order_item_meta($orderItemId, self::MARKING_FIELD_META_KEY, $markingFields);
            YooKassaLogger::info(sprintf(
                'Successfully saved marking for ItemId: %d',
                $orderItemId
            ));
            wp_send_json_success([
                'message' => __('Готово — сохранили', 'yookassa')
            ]);

        } catch (Exception $e) {
            YooKassaLogger::error(sprintf(
                'Unexpected error while saving marking meta: %s, Trace: %s',
                $e->getMessage(),
                $e->getTraceAsString()
            ));

            wp_send_json_error([
                'message' => __('Не получилось — обновите страницу и добавьте маркировку заново. Если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'),
                'type' => 'unexpected_error'
            ]);
        }
    }

    /**
     * Проверяет код на соответствие типу
     *
     * @param int $orderItemId
     * @param array $markingFields
     * @return array
     * @throws Exception
     */
    private function checkCode($orderItemId, &$markingFields)
    {
        $productId = wc_get_order_item_meta($orderItemId, '_product_id');
        $product = $productId ? wc_get_product($productId) : null;

        if (!$product) {
            YooKassaLogger::error(sprintf(
                'Error while saving marking meta: got error while getting product. ProductId: %d, ItemId: %d',
                $productId,
                $orderItemId
            ));
            wp_send_json_error([
                'message' => __('Не смогли найти товар. Обновите страницу — если ошибка не уйдёт, напишите нам на cms@yoomoney.ru', 'yookassa'),
                'type' => 'product_not_found'
            ]);
        }

        $productMarkCodeInfo = $product->get_meta(YooKassaMarkingProduct::MARK_CODE_INFO_KEY);

        $errorFields = [];

        foreach ($markingFields as $key => $value) {
            if (empty($value)) {
                unset($markingFields[$key]);
                continue;
            }

            $productCode = new ProductCode($value);
            $codeInfo = $productCode->getType();

            if ($codeInfo !== $productMarkCodeInfo) {
                $errorFields[$key] = [
                    'value' => $value,
                    'expected_type' => $productMarkCodeInfo,
                    'actual_type' => $codeInfo !== 'unknown' ? $codeInfo : null
                ];
            }
        }

        return $errorFields;
    }

    /**
     * Отображает общее уведомление на странице с заказом
     * если не все товары отсканированы
     *
     * @return void
     */
    public function displayOrderWarning()
    {
        global $pagenow;

        // Проверяем, что мы на странице редактирования заказа в новом интерфейсе
        $isNew = $pagenow === 'admin.php'
            && isset($_GET['page']) && $_GET['page'] === 'wc-orders'
            && isset($_GET['action']) && $_GET['action'] === 'edit';

        // Проверяем, что мы на странице редактирования заказа в старом интерфейсе
        $isOld = $pagenow === 'post.php'
            && isset($_GET['post']) && get_post_type($_GET['post']) === 'shop_order';

        if (!$isNew && !$isOld) {
            return;
        }

        // Получаем ID заказа в зависимости от интерфейса
        $order_id = $isNew ? absint($_GET['id']) : absint($_GET['post']);
        $order = wc_get_order($order_id);
        if ($order && !$this->isAllMarkingFieldsFilledInOrder($order)) {
            YooKassaNotice::admin_notice_error(__('Заполните пустые поля в карточке маркировки: за продажу товара без маркировки можно получить штраф', 'yookassa'));
        }
    }

    /**
     * Проверяет все ли товары в заказе
     * с обязательной маркировкой имеют маркировку
     *
     * @param $order
     * @return bool
     */
    private function isAllMarkingFieldsFilledInOrder($order)
    {
        foreach ($order->get_items() as $item) {
            $remainingQuantity = self::getRemainingQuantity($item);
            if ($remainingQuantity <= 0) {
                return true;
            }

            if (!$this->isAllMarkingFieldsFilledInItem($item, $remainingQuantity)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Проверяет есть ли маркировка в конкретном товаре
     * и по всем ли позициям товара заполнено
     *
     * @param WC_Order_Item $item
     * @param int $remainingQuantity
     * @return bool
     */
    private function isAllMarkingFieldsFilledInItem($item, $remainingQuantity)
    {
        $data = $item->get_data();

        if (empty($data['product_id'])) {
            YooKassaLogger::error(sprintf(
                'Error while checking marking fields: invalid product data for Item Id. ItemId: %d',
                $item->get_id()
            ));
            return false;
        }

        $product = wc_get_product($data['product_id']);
        if (!$product) {
            YooKassaLogger::error(sprintf(
                'Error while checking marking fields: product not found for Item Id. ProductId: %d, ItemId: %d',
                $data['product_id'],
                $item->get_id()
            ));
            return false;
        }

        $productCategory = $product->get_meta(YooKassaMarkingProduct::CATEGORY_KEY);
        if (empty($productCategory)) {
            return true;
        }

        $markingData = $item->get_meta(self::MARKING_FIELD_META_KEY);

        if (empty($markingData) || !is_array($markingData)) {
            return false;
        }

        if ($remainingQuantity !== count($markingData)) {
            return false;
        }

        return true;
    }

    /**
     * Удаление данных маркировки после применения возврата
     *
     * @param $order_id
     * @param $refund_id
     * @return void
     */
    public function deleteMarkingAfterRefund($order_id, $refund_id)
    {
        $order = wc_get_order($order_id);
        $refund = wc_get_order($refund_id);

        if (!$order || !$refund) {
            return;
        }

        foreach ($refund->get_items() as $refundedItem) {
            if (!$originalItemId = $refundedItem->get_meta('_refunded_item_id')) {
                continue;
            }

            if (!$originalItem = $order->get_item($originalItemId)) {
                continue;
            }

            $originalItem->delete_meta_data(self::MARKING_FIELD_META_KEY);
            $originalItem->save();
        }
    }
}
