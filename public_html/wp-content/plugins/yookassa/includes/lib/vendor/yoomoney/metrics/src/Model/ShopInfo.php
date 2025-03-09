<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class ShopInfo extends AbstractObject
{
    /** @var string|null */
    private $accountId;
    /** @var string|null */
    private $status;
    /** @var bool|null */
    private $test;
    /** @var Fiscalization|null */
    private $fiscalization;
    /** @var bool|null */
    private $fiscalizationEnabled;
    /** @var array<int, string> */
    private $paymentMethods;
    /** @var string|null */
    private $itn;

    /**
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return bool|null
     */
    public function getTest()
    {
        return $this->test;
    }

    /**
     * @return Fiscalization|null
     */
    public function getFiscalization()
    {
        return $this->fiscalization;
    }

    /**
     * @return bool|null
     */
    public function getFiscalizationEnabled()
    {
        return $this->fiscalizationEnabled;
    }

    /**
     * @return array<int, string>
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * @return string|null
     */
    public function getItn()
    {
        return $this->itn;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setAccountId($value = null)
    {
        $this->accountId = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setStatus($value = null)
    {
        $this->status = $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setTest($value = null)
    {
        $this->test = (bool) $value;
        return $this;
    }

    /**
     * @param array|null $value
     * @return self
     */
    public function setFiscalization($value = null)
    {
        $this->fiscalization = $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setFiscalizationEnabled($value = null)
    {
        $this->fiscalizationEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param array<int, string> $value
     * @return self
     */
    public function setPaymentMethods(array $value = array())
    {
        $this->paymentMethods = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setItn($value = null)
    {
        $this->itn = $value;
        return $this;
    }
}
