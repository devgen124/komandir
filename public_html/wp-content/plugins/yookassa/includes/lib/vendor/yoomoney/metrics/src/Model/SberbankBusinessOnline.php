<?php

namespace Cmssdk\Metrics\Model;

use YooKassa\Common\AbstractObject;

class SberbankBusinessOnline extends AbstractObject
{
    /** @var string|null */
    private $purposeTemplate;
    /** @var string|null */
    private $defaultTaxRate;
    /** @var array<string, string>|null */
    private $taxRates;

    /**
     * @return string|null
     */
    public function getPurposeTemplate()
    {
        return $this->purposeTemplate;
    }

    /**
     * @return string|null
     */
    public function getDefaultTaxRate()
    {
        return $this->defaultTaxRate;
    }

    /**
     * @return array|null
     */
    public function getTaxRates()
    {
        return $this->taxRates;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setPurposeTemplate($value = null)
    {
        $this->purposeTemplate = $value;
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
     * @param array<string, string>|null $value
     * @return self
     */
    public function setTaxRates($value = null)
    {
        $this->taxRates = $value;
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
