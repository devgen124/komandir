<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class ModuleInfo extends AbstractObject
{
    /** @var string|null */
    private $osVersion;
    /** @var string|null */
    private $phpVersion;
    /** @var string|null */
    private $cmsVersion;
    /** @var string|null */
    private $frameworkVersion;
    /** @var string|null */
    private $moduleVersion;
    /** @var string|null */
    private $sdkVersion;
    /** @var string|null */
    private $host;

    /**
     * @return string|null
     */
    public function getOsVersion()
    {
        return $this->osVersion;
    }

    /**
     * @return string|null
     */
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }

    /**
     * @return string|null
     */
    public function getCmsVersion()
    {
        return $this->cmsVersion;
    }

    /**
     * @return string|null
     */
    public function getFrameworkVersion()
    {
        return $this->frameworkVersion;
    }

    /**
     * @return string|null
     */
    public function getModuleVersion()
    {
        return $this->moduleVersion;
    }

    /**
     * @return string|null
     */
    public function getSdkVersion()
    {
        return $this->sdkVersion;
    }

    /**
     * @return string|null
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setOsVersion($value = null)
    {
        $this->osVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setPhpVersion($value = null)
    {
        $this->phpVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setCmsVersion($value = null)
    {
        $this->cmsVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setFrameworkVersion($value = null)
    {
        $this->frameworkVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setModuleVersion($value = null)
    {
        $this->moduleVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setSdkVersion($value = null)
    {
        $this->sdkVersion = $value;
        return $this;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setHost($value = null)
    {
        $this->host = $value;
        return $this;
    }
}
