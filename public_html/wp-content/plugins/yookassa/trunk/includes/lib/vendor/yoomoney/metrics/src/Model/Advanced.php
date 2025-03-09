<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class Advanced extends AbstractObject
{
    /** @var string|null */
    private $descriptionTemplate;
    /** @var string|null */
    private $successUrl;
    /** @var string|null */
    private $failureUrl;
    /** @var string|null */
    private $yookassaCurrency;
    /** @var bool|null */
    private $yookassaCurrencyConvert;
    /** @var bool|null */
    private $forceClearCart;
    /** @var bool|null */
    private $debugEnabled;
    /** @var string|null */
    private $notifyUrl;

    /**
     * @return string|null
     */
    public function getDescriptionTemplate()
    {
        return $this->descriptionTemplate;
    }

    /**
     * @return string|null
     */
    public function getSuccessUrl()
    {
        return $this->successUrl;
    }

    /**
     * @return string|null
     */
    public function getFailureUrl()
    {
        return $this->failureUrl;
    }

    /**
     * @return string|null
     */
    public function getYookassaCurrency()
    {
        return $this->yookassaCurrency;
    }

    /**
     * @return bool|null
     */
    public function getYookassaCurrencyConvert()
    {
        return $this->yookassaCurrencyConvert;
    }

    /**
     * @return bool|null
     */
    public function getForceClearCart()
    {
        return $this->forceClearCart;
    }

    /**
     * @return bool|null
     */
    public function getDebugEnabled()
    {
        return $this->debugEnabled;
    }

    /**
     * @return string|null
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setDescriptionTemplate($value = null)
    {
        $this->descriptionTemplate = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setSuccessUrl($value = null)
    {
        $this->successUrl = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setFailureUrl($value = null)
    {
        $this->failureUrl = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setYookassaCurrency($value = null)
    {
        $this->yookassaCurrency = $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setYookassaCurrencyConvert($value = null)
    {
        $this->yookassaCurrencyConvert = (bool) $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setForceClearCart($value = null)
    {
        $this->forceClearCart = (bool) $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setDebugEnabled($value = null)
    {
        $this->debugEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setNotifyUrl($value = null)
    {
        $this->notifyUrl = $value;
        return $this;
    }
}
