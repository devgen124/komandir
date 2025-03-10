<?php

/*
 * The MIT License
 *
 * Copyright (c) 2025 "YooMoney", NBСO LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YooKassa\Request\Receipts;

use YooKassa\Common\AbstractRequest;
use YooKassa\Common\Exceptions\EmptyPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueException;
use YooKassa\Common\Exceptions\InvalidPropertyValueTypeException;
use YooKassa\Helpers\TypeCast;
use YooKassa\Model\Receipt\AdditionalUserProps;
use YooKassa\Model\Receipt\IndustryDetails;
use YooKassa\Model\Receipt\OperationalDetails;
use YooKassa\Model\ReceiptCustomer;
use YooKassa\Model\ReceiptCustomerInterface;
use YooKassa\Model\ReceiptItem;
use YooKassa\Model\ReceiptItemInterface;
use YooKassa\Model\ReceiptType;
use YooKassa\Model\Settlement;
use YooKassa\Model\SettlementInterface;

/**
 * Класс объекта запроса к API на создание чека
 *
 * @example 02-builder.php 91 56 Пример использования билдера
 *
 * @package YooKassa
 */
class CreatePostReceiptRequest extends AbstractRequest implements CreatePostReceiptRequestInterface
{
    /** @var ReceiptCustomerInterface Информация о плательщике */
    private $_customer;

    /** @var string Тип чека в онлайн-кассе: приход "payment" или возврат "refund". */
    private $_type;

    /** @var bool Признак отложенной отправки чека. */
    private $_send = true;

    /** @var int Код системы налогообложения. Число 1-6. */
    private $_taxSystemCode;

    /** @var AdditionalUserProps Дополнительный реквизит пользователя */
    private $_additionalUserProps;

    /** @var IndustryDetails[] Отраслевой реквизит предмета расчета */
    private $_receiptIndustryDetails;

    /** @var OperationalDetails Операционный реквизит чека */
    private $_receiptOperationalDetails;

    /** @var ReceiptItemInterface[] Список товаров в заказе. Для чеков по 54-ФЗ можно передать не более 100 товаров, для чеков самозанятых — не более шести. */
    private $_items = array();

    /** @var SettlementInterface[] Список платежей */
    private $_settlements = array();

    /** @var string Идентификатор объекта оплаты */
    private $_object_id;

    /** @var string Тип объекта: приход "payment" или возврат "refund". */
    private $_object_type;

    /** @var string Идентификатор магазина в ЮKassa */
    private $_onBehalfOf;

    /**
     * Возвращает билдер объектов запросов создания платежа
     * @return CreatePostReceiptRequestBuilder Инстанс билдера объектов запросов
     */
    public static function builder()
    {
        return new CreatePostReceiptRequestBuilder();
    }

    /**
     * Возвращает Id объекта чека
     *
     * @return string Id объекта чека
     */
    public function getObjectId()
    {
        return $this->_object_id;
    }

    /**
     * Устанавливает Id объекта чека
     *
     * @param string $value Id объекта чека
     * @return CreatePostReceiptRequest
     */
    public function setObjectId($value)
    {
        $this->_object_id = $value;
        return $this;
    }

    /**
     * Возвращает тип объекта чека
     *
     * @return string Тип объекта чека
     */
    public function getObjectType()
    {
        return $this->_object_type;
    }

    /**
     * Устанавливает тип объекта чека
     *
     * @param string $value Тип объекта чека
     * @return CreatePostReceiptRequest
     */
    public function setObjectType($value)
    {
        $this->_object_type = $value;
        return $this;
    }

    /**
     * Проверяет наличие данных о плательщике
     *
     * @return bool
     */
    public function hasCustomer()
    {
        return !empty($this->_customer);
    }

    /**
     * Возвращает информацию о плательщике
     *
     * @return ReceiptCustomerInterface Информация о плательщике
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Устанавливает информацию о плательщике
     *
     * @param ReceiptCustomerInterface|array $value Информация о плательщике
     * @return CreatePostReceiptRequest
     */
    public function setCustomer($value)
    {
        if (is_array($value)) {
            $this->_customer = new ReceiptCustomer($value);
        } elseif ($value instanceof ReceiptCustomerInterface) {
            $this->_customer = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid customer value type in receipt',
                0,
                'Receipt.customer',
                $value
            );
        }
        return $this;
    }

    /**
     * Возвращает список позиций в текущем чеке
     *
     * @return ReceiptItemInterface[] Список товаров в заказе
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Устанавливает список позиций в чеке
     *
     * Если до этого в чеке уже были установлены значения, они удаляются и полностью заменяются переданным списком
     * позиций. Все передаваемые значения в массиве позиций должны быть объектами класса, реализующего интерфейс
     * ReceiptItemInterface, в противном случае будет выброшено исключение InvalidPropertyValueTypeException.
     *
     * @param ReceiptItemInterface[]|array $value Список товаров в заказе
     *
     * @return CreatePostReceiptRequest
     * @throws EmptyPropertyValueException Выбрасывается если передали пустой массив значений
     * @throws InvalidPropertyValueTypeException Выбрасывается если в качестве значения был передан не массив и не
     * итератор, либо если одно из переданных значений не реализует интерфейс ReceiptItemInterface
     */
    public function setItems($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty items value in receipt', 0, 'Receipt.items');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid items value type in receipt',
                0,
                'Receipt.items',
                $value
            );
        }
        $this->_items = array();
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $this->addItem(new ReceiptItem($item));
            } elseif ($item instanceof ReceiptItemInterface) {
                $this->addItem($item);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid item value type in receipt',
                    0,
                    'Receipt.items[' . $key . ']',
                    $item
                );
            }
        }

        return $this;
    }

    /**
     * Добавляет товар в чек
     *
     * @param ReceiptItemInterface|array $value Объект добавляемой в чек позиции
     * @return CreatePostReceiptRequest
     */
    public function addItem($value)
    {
        $this->_items[] = $value;

        return $this;
    }

    /**
     * Возвращает код системы налогообложения
     *
     * @return int Код системы налогообложения. Число 1-6.
     */
    public function getTaxSystemCode()
    {
        return $this->_taxSystemCode;
    }

    /**
     * Устанавливает код системы налогообложения
     *
     * @param int $value Код системы налогообложения. Число 1-6
     *
     * @throws InvalidPropertyValueTypeException Выбрасывается если переданный аргумент - не число
     * @throws InvalidPropertyValueException Выбрасывается если переданный аргумент меньше одного или больше шести
     */
    public function setTaxSystemCode($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty taxSystemCode value in receipt', 0, 'Receipt.taxSystemCode');
        }
        if (!is_numeric($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid taxSystemCode value type',
                0,
                'Receipt.taxSystemCode'
            );
        }

        $castedValue = (int)$value;
        if ($castedValue < 1 || $castedValue > 6) {
            throw new InvalidPropertyValueException(
                'Invalid taxSystemCode value: ' . $value,
                0,
                'Receipt.taxSystemCode'
            );
        }
        $this->_taxSystemCode = $castedValue;

        return $this;
    }

    /**
     * Возвращает дополнительный реквизит пользователя
     *
     * @return AdditionalUserProps Дополнительный реквизит пользователя
     */
    public function getAdditionalUserProps()
    {
        return $this->_additionalUserProps;
    }

    /**
     * Устанавливает дополнительный реквизит пользователя
     *
     * @param AdditionalUserProps|array $value Дополнительный реквизит пользователя
     * @return void|CreatePostReceiptRequest
     */
    public function setAdditionalUserProps($value)
    {
        if (empty($value)) {
            $this->_additionalUserProps = null;
            return;
        }
        if (is_array($value)) {
            $this->_additionalUserProps = new AdditionalUserProps($value);
        } elseif ($value instanceof AdditionalUserProps) {
            $this->_additionalUserProps = $value;
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid additionalUserProps value type in receipt',
                0,
                'Receipt.additional_user_props',
                $value
            );
        }
        return $this;
    }

    /**
     * Возвращает отраслевой реквизит чека
     * @return IndustryDetails[] Отраслевой реквизит чека
     */
    public function getReceiptIndustryDetails()
    {
        return $this->_receiptIndustryDetails;
    }

    /**
     * Устанавливает отраслевой реквизит чека
     * @param array|IndustryDetails $value Отраслевой реквизит чека
     *
     * @return CreatePostReceiptRequest
     */
    public function setReceiptIndustryDetails($value)
    {
        if (empty($value)) {
            $this->_receiptIndustryDetails = null;
            return $this;
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid receiptIndustryDetails value type in Receipt',
                0,
                'Receipt.receipt_industry_details',
                $value
            );
        }
        $details = array();
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $item = new IndustryDetails($item);
            }
            if ($item instanceof IndustryDetails) {
                $details[] = $item;
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid receiptIndustryDetails value type in Receipt',
                    0,
                    'Receipt.receipt_industry_details[' . $key . ']',
                    $item
                );
            }
        }
        $this->_receiptIndustryDetails = $details;
        return $this;
    }

    /**
     * Возвращает операционный реквизит чека
     * @return OperationalDetails Операционный реквизит чека
     */
    public function getReceiptOperationalDetails()
    {
        return $this->_receiptOperationalDetails;
    }

    /**
     * Устанавливает операционный реквизит чека
     * @param array|OperationalDetails $value Операционный реквизит чека
     *
     * @return CreatePostReceiptRequest
     */
    public function setReceiptOperationalDetails($value)
    {
        if (empty($value)) {
            $this->_receiptOperationalDetails = null;
            return $this;
        }
        if (!is_array($value) && !($value instanceof OperationalDetails)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid receiptOperationalDetails value type in Receipt',
                0,
                'Receipt.receipt_operational_details',
                $value
            );
        }

        if (is_array($value)) {
            $value = new OperationalDetails($value);
        }

        $this->_receiptOperationalDetails = $value;
        return $this;
    }

    /**
     * Возвращает тип чека в онлайн-кассе
     *
     * @return string Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Устанавливает тип чека в онлайн-кассе
     *
     * @param string $value Тип чека в онлайн-кассе: приход "payment" или возврат "refund".
     * @return CreatePostReceiptRequest
     */
    public function setType($value)
    {
        if (TypeCast::canCastToEnumString($value)) {
            if (!ReceiptType::valueExists((string)$value)) {
                throw new InvalidPropertyValueException('Invalid receipt type value', 0, 'Receipt.type', $value);
            }
            $this->_type = (string)$value;
            if (!$this->_object_type) {
                $this->_object_type = $this->_type;
            }
        } else {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt type value type',
                0,
                'Receipt.type',
                $value
            );
        }
        return $this;
    }

    /**
     * Возвращает признак отложенной отправки чека.
     *
     * @return bool Признак отложенной отправки чека.
     */
    public function getSend()
    {
        return $this->_send;
    }

    /**
     * Устанавливает признак отложенной отправки чека.
     * @param bool $value Признак отложенной отправки чека.
     * @return CreatePostReceiptRequest
     */
    public function setSend($value)
    {
        if (!TypeCast::canCastToBoolean($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid receipt type value send',
                0,
                'Receipt.send',
                $value
            );
        }

        $this->_send = (bool)$value;

        return $this;
    }

    /**
     * Возвращает массив оплат, обеспечивающих выдачу товара.
     *
     * @return SettlementInterface[] Массив оплат, обеспечивающих выдачу товара.
     */
    public function getSettlements()
    {
        return $this->_settlements;
    }

    /**
     * Устанавливает массив оплат, обеспечивающих выдачу товара.
     *
     * @param SettlementInterface|array $value Массив оплат, обеспечивающих выдачу товара.
     * @return CreatePostReceiptRequest
     */
    public function setSettlements($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException('Empty settlements value in receipt', 0, 'Receipt.settlements');
        }
        if (!is_array($value) && !($value instanceof \Traversable)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid settlements value type in receipt',
                0,
                'Receipt.settlements',
                $value
            );
        }
        $this->_settlements = array();
        foreach ($value as $key => $val) {
            if (is_array($val) && !empty($val['type']) && !empty($val['amount'])) {
                $this->addSettlement(new Settlement($val));
            } elseif ($val instanceof SettlementInterface) {
                $this->addSettlement($val);
            } else {
                throw new InvalidPropertyValueTypeException(
                    'Invalid settlement value type in receipt',
                    0,
                    'Receipt.settlements[' . $key . ']',
                    $val
                );
            }
        }
        return $this;
    }

    /**
     * Добавляет оплату в перечень совершенных расчетов.
     *
     * @param SettlementInterface $value Информация о совершенных расчетах.
     */
    public function addSettlement(SettlementInterface $value)
    {
        $this->_settlements[] = $value;
    }

    /**
     * Возвращает идентификатор магазина, от имени которого нужно отправить чек.
     * Выдается ЮKassa, отображается в разделе Продавцы личного кабинета (столбец shopId).
     * Необходимо передавать, если вы используете решение ЮKassa для платформ.
     *
     * @return string
     */
    public function getOnBehalfOf()
    {
        return $this->_onBehalfOf;
    }

    /**
     * Устанавливает идентификатор магазина, от имени которого нужно отправить чек.
     * Выдается ЮKassa, отображается в разделе Продавцы личного кабинета (столбец shopId).
     * Необходимо передавать, если вы используете решение ЮKassa для платформ.
     *
     * @param string $value
     * @return CreatePostReceiptRequest
     */
    public function setOnBehalfOf($value)
    {
        if ($value === null || $value === '') {
            throw new EmptyPropertyValueException(
                'Empty onBehalfOf value',
                0,
                'Receipt.onBehalfOf'
            );
        }
        if (!TypeCast::canCastToString($value)) {
            throw new InvalidPropertyValueTypeException(
                'Invalid onBehalfOf value type',
                0,
                'Receipt.onBehalfOf',
                $value
            );
        }

        $this->_onBehalfOf = (string)$value;

        return $this;
    }

    /**
     * Проверяет есть ли в чеке хотя бы одна позиция товаров и оплат
     *
     * @return bool True если чек не пуст, false если в чеке нет ни одной позиции
     */
    public function notEmpty()
    {
        return !empty($this->_items) && !empty($this->_settlements);
    }

    /**
     * Устанавливает значения свойств текущего объекта из массива
     * @param array|\Traversable $sourceArray Ассоциативный массив с настройками
     */
    public function fromArray($sourceArray)
    {
        if (!empty($sourceArray['customer'])) {
            $sourceArray['customer'] = new ReceiptCustomer($sourceArray['customer']);
        }

        if (!empty($sourceArray['items'])) {
            foreach ($sourceArray['items'] as $i => $itemArray) {
                if (is_array($itemArray)) {
                    $sourceArray['items'][$i] = new ReceiptItem($itemArray);
                }
            }
        }

        if (!empty($sourceArray['settlements'])) {
            foreach ($sourceArray['settlements'] as $i => $itemArray) {
                if (is_array($itemArray)) {
                    $sourceArray['settlements'][$i] = new Settlement($itemArray);
                }
            }
        }

        parent::fromArray($sourceArray);
    }

    /**
     * Валидирует текущий запрос, проверяет все ли нужные свойства установлены
     * @return bool True если запрос валиден, false если нет
     */
    public function validate()
    {
        if (empty($this->_customer)) {
            $this->setValidationError('Receipt customer not specified');
            return false;
        }

        if (empty($this->_type) || !ReceiptType::valueExists($this->_type)) {
            $this->setValidationError('Receipt type not specified');
            return false;
        }

        if (empty($this->_object_type)) {
            $this->setValidationError('Receipt object_type not specified');
            return false;
        }

        if (empty($this->_object_id)) {
            $this->setValidationError('Receipt object_id not specified');
            return false;
        }

        if (empty($this->_send)) { // todo: пока может быть только true
            $this->setValidationError('Receipt send not specified');
            return false;
        }

        if (empty($this->_settlements)) {
            $this->setValidationError('Receipt settlements not specified');
            return false;
        }

        if (empty($this->_items)) {
            $this->setValidationError('Receipt items not specified');
            return false;
        }

        return true;
    }
}
