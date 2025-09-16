<?php

use YooKassa\Helpers\FiscalizationProvider;
use YooKassa\Model\Receipt\MarkCodeInfo;
use YooKassa\Model\Receipt\MarkQuantity;
use YooKassa\Model\ReceiptItem;
use YooKassa\Request\Receipts\ReceiptResponseItemInterface;

class YooKassaMarkingCodeHandler
{
    /** @var string Тип кассовой системы */
    private $provider;

    /** @var int|null Количество единиц товара */
    private $quantity;

    /** @var string|null Категория маркировки */
    private $category;

    /** @var string|null Мера количества предмета расчета */
    private $measure;

    /** @var int|null Знаменатель — общее количество товаров в потребительской упаковке */
    private $denominator;

    /** @var string|null Код товара */
    private $mark_code_info;

    /** @var array|null Массив данных с маркировкой */
    private $marking_fields;

    /**
     * Онлайн кассы, требующие кодирования данных в base64
     */
    const BASE64_ENCODED_PROVIDER = [
        FiscalizationProvider::ATOL,          // АТОЛ Онлайн
        FiscalizationProvider::EVOTOR,        // Эвотор
        FiscalizationProvider::BUSINESS_RU,   // Бизнес.ру
        FiscalizationProvider::MODUL_KASSA,   // МодульКасса
        FiscalizationProvider::MERTRADE,      // Mertrade
        FiscalizationProvider::FIRST_OFD,     // Первый ОФД
        FiscalizationProvider::A_QSI,         // aQsi online
        FiscalizationProvider::ROCKET,        // RocketR
        FiscalizationProvider::KOMTET,        // КОМТЕТ Касса
    ];

    /**
     * Онлайн кассы, не требующие специальной обработки данных
     */
    const NO_ENCODED_PROVIDER = [
        FiscalizationProvider::LIFE_PAY,      // LIFE PAY
        FiscalizationProvider::KIT_INVEST,    // Кит Инвест
        FiscalizationProvider::AVANPOST,      // ЮЧеки
        FiscalizationProvider::DIGITAL_KASSA, // digitalkassa
    ];

    /**
     * Онлайн кассы, требующие передачи данных в mark_code_raw без кодировки
     */
    const RAW_CODE_PROVIDER = [
        FiscalizationProvider::SHTRIH_M,      // Orange Data
    ];

    /**
     * @param string $provider Тип кассовой системы
     */
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return int|null
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int|null $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = is_numeric($quantity) ? (int)$quantity : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory($category)
    {
        $this->category = ($category !== '' && $category !== null) ? $category : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMeasure()
    {
        return $this->measure;
    }

    /**
     * @param string|null $measure
     */
    public function setMeasure($measure)
    {
        $this->measure = ($measure !== '' && $measure !== null) ? $measure : null;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDenominator()
    {
        return $this->denominator;
    }

    /**
     * @param int|null $denominator
     */
    public function setDenominator($denominator)
    {
        $this->denominator = $denominator !== null ? (int)$denominator : null;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMarkCodeInfo()
    {
        return $this->mark_code_info;
    }

    /**
     * @param string|null $mark_code_info
     */
    public function setMarkCodeInfo($mark_code_info)
    {
        $this->mark_code_info = ($mark_code_info !== '' && $mark_code_info !== null) ? $mark_code_info : null;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMarkingFields()
    {
        return $this->marking_fields;
    }

    /**
     * @param array|null $marking_fields
     */
    public function setMarkingFields($marking_fields)
    {
        $this->marking_fields = is_array($marking_fields) ? $marking_fields : null;
        return $this;
    }

    /**
     * Подготавливает маркировочный код в зависимости от типа кассы
     *
     * @param string $provider Тип кассовой системы
     * @param string $markField Поле маркировочного кода в чеке
     * @param string $markCode Исходный маркировочный код
     * @return array Подготовленные данные для чека
     * @throws InvalidArgumentException
     */
    private function prepareMarkCode($provider, $markField, $markCode)
    {
        if (in_array($provider, self::BASE64_ENCODED_PROVIDER, true)) {
            return array($markField => base64_encode($markCode));
        }

        if (in_array($provider, self::RAW_CODE_PROVIDER, true)) {
            return array('mark_code_raw' => $markCode);
        }

        if (in_array($provider, self::NO_ENCODED_PROVIDER, true)) {
            return array($markField => $markCode);
        }

        $errorMsg = sprintf('Unknown provider: "%s"', $provider);
        YooKassaLogger::warning($errorMsg);
        YooKassaLogger::sendAlertLog($errorMsg);
        return array($markField => $markCode);
    }

    /**
     * Разбивает товары по кодам маркировки
     *
     * @param ReceiptResponseItemInterface $item Объект товара
     * @return array
     * @throws Exception
     */
    public function splitProductsByCode($item)
    {
        $logMessage = sprintf(
            'Splitting product "%s" (Quantity: %d, Category: %s, Denominator: %s, Mark code: %s, Provider: %s)',
            $item->getDescription(),
            $this->getQuantity(),
            $this->getCategory() !== null ? $this->getCategory() : 'null',
            $this->getDenominator() !== null ? $this->getDenominator() : 'null',
            $this->getMarkCodeInfo() ? 'exists' : 'null',
            $this->provider
        );
        YooKassaLogger::info($logMessage);

        // Проверяем наличие данных маркировки
        $hasMarking = $this->getCategory() !== null
                   && $this->getMarkCodeInfo() !== null
                   && $this->getMarkingFields() !== null;

        // Если нет маркировки, возвращаем клон исходного товара
        if (!$hasMarking) {
            YooKassaLogger::info('No marking data found, returning original item');
            return array(new ReceiptItem($item->toArray()));
        }

        $receiptItems = array();

        try {
            YooKassaLogger::sendHeka(array('second-receipt-marking.create.init'));
            // Получаем коды маркировки (с проверкой на существование)
            $markingCodes = $this->getMarkingFields() !== null
                ? $this->getMarkingFields()
                : [];

            // Клонируем объект, чтобы избежать изменений оригинала
            $clonedItem = clone $item;
            $groupedItems = $this->groupMarkingCodes($markingCodes);

            // Создаем ReceiptItem для каждой группы маркированных товаров
            $receiptItems = array_map(
                function($markCode, $group) use ($clonedItem) {
                    return $this->createMarkedReceiptItem($clonedItem, $markCode, $group['count']);
                },
                array_keys($groupedItems),
                array_values($groupedItems)
            );

            // Добавляем немаркированные товары (если остались)
            $remainingItems = $this->getQuantity() - count($markingCodes);
            if ($remainingItems > 0) {
                YooKassaLogger::info(sprintf(
                    'Adding %d unmarked items',
                    $remainingItems
                ));
                $receiptItem = new ReceiptItem($item->toArray());
                $receiptItem->setQuantity($remainingItems);
                $receiptItems[] = $receiptItem;
            }

            YooKassaLogger::info(sprintf(
                'Finished splitting. Total items after split: %d',
                count($receiptItems)
            ));
            YooKassaLogger::sendHeka(array('second-receipt-marking.create.success'));
        } catch (Exception $e) {
            YooKassaLogger::error('Error splitting products: ' . $e->getMessage());
            YooKassaLogger::sendHeka(array('second-receipt-marking.create.fail'));
            throw $e;
        }

        return $receiptItems;
    }

    /**
     * Группирует коды маркировки
     *
     * @param array $markingCodes Массив с кодами маркировки
     * @return array
     */
    protected function groupMarkingCodes($markingCodes)
    {
        return array_reduce($markingCodes, static function($result, $markCode) {
            if (!isset($result[$markCode])) {
                $result[$markCode] = ['count' => 0];
            }
            $result[$markCode]['count']++;
            return $result;
        }, []);
    }

    /**
     * Создает объект товара с маркировкой
     *
     * @param ReceiptResponseItemInterface $clonedItem Клон объекта товара
     * @param string $markCode Код маркировки
     * @param int $quantity Количество товаров
     * @return ReceiptItem
     */
    protected function createMarkedReceiptItem($clonedItem, $markCode, $quantity)
    {
        $markingData = $this->configureMarkingItem($clonedItem, $markCode, $quantity);
        return new ReceiptItem(array_merge($clonedItem->toArray(), $markingData));
    }

    /**
     * Добавляет маркировку к товару
     *
     * @param ReceiptResponseItemInterface $receiptItem Объект товара
     * @param string $markCode Код маркировки
     * @param int $quantity Количество товаров
     * @return array
     */
    private function configureMarkingItem($receiptItem, $markCode = null, $quantity = 1)
    {
        $receiptItem->setQuantity($quantity);
        $receiptItem->setMeasure($this->getMeasure() !== null ? $this->getMeasure() : 'piece');

        $denominator = $this->getDenominator();

        if ($denominator > 1
            && $denominator > $quantity
            && $receiptItem->getMeasure() === 'piece'
        ) {
            $markQuantity = new MarkQuantity();
            $markQuantity->setDenominator($denominator);
            $markQuantity->setNumerator($quantity);
            $receiptItem->setMarkQuantity($markQuantity);
        }

        $markCodeInfo = new MarkCodeInfo($this->prepareMarkCode(
            $this->provider,
            $this->getMarkCodeInfo(),
            $markCode
        ));
        $receiptItem->setMarkCodeInfo($markCodeInfo);
        $receiptItem->setMarkMode($markCode !== null ? 0 : null);

        return $receiptItem->toArray();
    }
}
