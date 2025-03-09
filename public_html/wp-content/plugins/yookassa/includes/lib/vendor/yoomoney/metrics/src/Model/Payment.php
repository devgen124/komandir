<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class Payment extends AbstractObject
{
    /** @var string|null */
    private $scenario;
    /** @var bool|null */
    private $saveCardEnabled;
    /** @var bool|null */
    private $holdEnabled;
    /** @var bool|null */
    private $sbbolEnabled;

    /**
     * @return string|null
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    /**
     * @return bool|null
     */
    public function getSaveCardEnabled()
    {
        return $this->saveCardEnabled;
    }

    /**
     * @return bool|null
     */
    public function getHoldEnabled()
    {
        return $this->holdEnabled;
    }

    /**
     * @return bool|null
     */
    public function getSbbolEnabled()
    {
        return $this->sbbolEnabled;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setScenario($value = null)
    {
        $this->scenario = $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setSaveCardEnabled($value = null)
    {
        $this->saveCardEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setHoldEnabled($value = null)
    {
        $this->holdEnabled = (bool) $value;
        return $this;
    }

    /**
     * @param bool|null $value
     * @return self
     */
    public function setSbbolEnabled($value = null)
    {
        $this->sbbolEnabled = (bool) $value;
        return $this;
    }
}
