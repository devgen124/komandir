<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class Fiscalization extends AbstractObject
{
    /** @var bool|null */
    private $receiptEnabled;
    /** @var bool|null */
    private $selfEmployed;
    /** @var string|null */
    private $ffd;
    /** @var string|null */
    private $defaultTaxRate;
    /** @var string|null */
    private $defaultTaxSystemCode;
    /** @var string|null */
    private $defaultPaymentSubject;
    /** @var string|null */
    private $defaultPaymentMode;
    /** @var string|null */
    private $defaultShippingPaymentSubject;
    /** @var string|null */
    private $defaultShippingPaymentMode;
    /** @var array<string, string>|null */
    private $taxRates;
    /** @var bool|null */
    private $secondReceiptEnabled;
    /** @var string|null */
    private $secondReceiptOrderStatus;

    /**
     * @return bool|null
     */
    public function getReceiptEnabled()
    {
        return $this->receiptEnabled;
    }

    /**
     * @return bool|null
     */
    public function getSelfEmployed()
    {
        return $this->selfEmployed;
    }

    /**
     * @return string|null
     */
    public function getFfd()
    {
        return $this->ffd;
    }

    /**
     * @return string|null
     */
    public function getDefaultTaxRate()
    {
        return $this->defaultTaxRate;
    }

    /**
     * @return string|null
     */
    public function getDefaultTaxSystemCode()
    {
        return $this->defaultTaxSystemCode;
    }

    /**
     * @return string|null
     */
    public function getDefaultPaymentSubject()
    {
        return $this->defaultPaymentSubject;
    }

    /**
     * @return string|null
     */
    public function getDefaultPaymentMode()
    {
        return $this->defaultPaymentMode;
    }

    /**
     * @return string|null
     */
    public function getDefaultShippingPaymentSubject()
    {
        return $this->defaultShippingPaymentSubject;
    }

    /**
     * @return string|null
     */
    public function getDefaultShippingPaymentMode()
    {
        return $this->defaultShippingPaymentMode;
    }

    public function getTaxRates()
    {
        return $this->taxRates;
    }

    /**
     * @return bool|null
     */
    public function getSecondReceiptEnabled()
    {
        return $this->secondReceiptEnabled;
    }

    /**
     * @return string|null
     */
    public function getSecondReceiptOrderStatus()
    {
        return $this->secondReceiptOrderStatus;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setReceiptEnabled($value = null)
    {
        $this->receiptEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setSelfEmployed($value = null)
    {
        $this->selfEmployed = (bool) $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setFfd($value = null)
    {
        $this->ffd = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultTaxRate($value = null)
    {
        $this->defaultTaxRate = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultTaxSystemCode($value = null)
    {
        $this->defaultTaxSystemCode = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultPaymentSubject($value = null)
    {
        $this->defaultPaymentSubject = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultPaymentMode($value = null)
    {
        $this->defaultPaymentMode = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultShippingPaymentSubject($value = null)
    {
        $this->defaultShippingPaymentSubject = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDefaultShippingPaymentMode($value = null)
    {
        $this->defaultShippingPaymentMode = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setTaxRates($value = null)
    {
        $this->taxRates = $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setSecondReceiptEnabled($value = null)
    {
        $this->secondReceiptEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setSecondReceiptOrderStatus($value = null)
    {
        $this->secondReceiptOrderStatus = $value;
        return $this;
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        if (!empty($data['tax_rates'])) {
            $newTaxRates = array();
            foreach ($data['tax_rates'] as $key => $value) {
                $newTaxRates[] = array(
                    'key' => $key,
                    'value' => $value,
                );
            }
            $data['tax_rates'] = $newTaxRates;
        }
        return $data;
    }
}
