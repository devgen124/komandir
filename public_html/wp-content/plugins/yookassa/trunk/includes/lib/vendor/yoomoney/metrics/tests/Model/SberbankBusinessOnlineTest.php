<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\SberbankBusinessOnline;
use PHPUnit\Framework\TestCase;

class SberbankBusinessOnlineTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDescriptionTemplate($data)
    {
        $sbbol = new SberbankBusinessOnline();
        $sbbol->setPurposeTemplate($data['purpose_template']);
        $this->assertEquals($data['purpose_template'], $sbbol->getPurposeTemplate());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDefaultTaxRate($data)
    {
        $sbbol = new SberbankBusinessOnline();
        $sbbol->setDefaultTaxRate($data['default_tax_rate']);
        $this->assertEquals($data['default_tax_rate'], $sbbol->getDefaultTaxRate());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetTaxRates($data)
    {
        $sbbol = new SberbankBusinessOnline();
        $sbbol->setTaxRates($data['tax_rates']);
        $this->assertEquals($data['tax_rates'], $sbbol->getTaxRates());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $sbbol = new SberbankBusinessOnline($data);

        $this->assertEquals($data['purpose_template'], $sbbol->getPurposeTemplate());
        $this->assertEquals($data['default_tax_rate'], $sbbol->getDefaultTaxRate());
        $this->assertEquals($data['tax_rates'], $sbbol->getTaxRates());

        if (!empty($data['tax_rates'])) {
            $result = $sbbol->toArray();
            $this->assertCount(count($data['tax_rates']), $result['tax_rates']);
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'purpose_template' => 'Оплата заказа №%order_number%',
                    'default_tax_rate' => 'untaxed',
                    'tax_rates' => array(
                        '1' => 'untaxed',
                        '2' => 'mixed',
                    ),
                )
            ),
            array(
                array(
                    'purpose_template' => 'Оплата заказа №%order_number%',
                    'default_tax_rate' => 'untaxed',
                    'tax_rates' => array(
                        '1' => 'untaxed',
                        '2' => 'calculated',
                    ),
                )
            ),
            array(
                array(
                    'purpose_template' => 'Оплата заказа №%order_number%',
                    'default_tax_rate' => 'untaxed',
                    'tax_rates' => false,
                )
            ),
        );
    }
}

