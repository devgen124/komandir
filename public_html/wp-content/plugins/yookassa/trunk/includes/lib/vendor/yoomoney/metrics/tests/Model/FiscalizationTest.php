<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\Fiscalization;
use PHPUnit\Framework\TestCase;

class FiscalizationTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetReceiptEnabled($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setReceiptEnabled($data['receipt_enabled']);
        $this->assertEquals($data['receipt_enabled'], $fiscalization->getReceiptEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetSelfEmployed($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setSelfEmployed($data['self_employed']);
        $this->assertEquals($data['self_employed'], $fiscalization->getSelfEmployed());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetFfd($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setFfd($data['ffd']);
        $this->assertEquals($data['ffd'], $fiscalization->getFfd());
    }


    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */public function testSetAndGetDefaultTaxRate($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultTaxRate($data['default_tax_rate']);
        $this->assertEquals($data['default_tax_rate'], $fiscalization->getDefaultTaxRate());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDefaultTaxSystemCode($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultTaxSystemCode($data['default_tax_system_code']);
        $this->assertEquals($data['default_tax_system_code'], $fiscalization->getDefaultTaxSystemCode());
    }


    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */public function testSetAndGetDefaultPaymentSubject($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultPaymentSubject($data['default_payment_subject']);
        $this->assertEquals($data['default_payment_subject'], $fiscalization->getDefaultPaymentSubject());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDefaultPaymentMode($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultPaymentMode($data['default_payment_mode']);
        $this->assertEquals($data['default_payment_mode'], $fiscalization->getDefaultPaymentMode());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDefaultShippingPaymentSubject($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultShippingPaymentSubject($data['default_shipping_payment_subject']);
        $this->assertEquals($data['default_shipping_payment_subject'], $fiscalization->getDefaultShippingPaymentSubject());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDefaultShippingPaymentMode($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setDefaultShippingPaymentMode($data['default_shipping_payment_mode']);
        $this->assertEquals($data['default_shipping_payment_mode'], $fiscalization->getDefaultShippingPaymentMode());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetTaxRates($data)
    {
        $sbbol = new Fiscalization();
        $sbbol->setTaxRates($data['tax_rates']);
        $this->assertEquals($data['tax_rates'], $sbbol->getTaxRates());
    }


    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetSecondReceiptEnabled($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setSecondReceiptEnabled($data['second_receipt_enabled']);
        $this->assertEquals($data['second_receipt_enabled'], $fiscalization->getSecondReceiptEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetSecondReceiptOrderStatus($data)
    {
        $fiscalization = new Fiscalization();
        $fiscalization->setSecondReceiptOrderStatus($data['second_receipt_order_status']);
        $this->assertEquals($data['second_receipt_order_status'], $fiscalization->getSecondReceiptOrderStatus());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $fiscalization = new Fiscalization($data);

        $this->assertEquals($data['receipt_enabled'], $fiscalization->getReceiptEnabled());
        $this->assertEquals($data['self_employed'], $fiscalization->getSelfEmployed());
        $this->assertEquals($data['ffd'], $fiscalization->getFfd());
        $this->assertEquals($data['default_tax_rate'], $fiscalization->getDefaultTaxRate());
        $this->assertEquals($data['default_tax_system_code'], $fiscalization->getDefaultTaxSystemCode());
        $this->assertEquals($data['second_receipt_enabled'], $fiscalization->getSecondReceiptEnabled());

        if (!empty($data['tax_rates'])) {
            $result = $fiscalization->toArray();
            $this->assertCount(count($data['tax_rates']), $result['tax_rates']);
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'receipt_enabled'=> true,
                    'self_employed'=> false,
                    'ffd'=> 'ffd11',
                    'default_tax_rate'=> '1',
                    'default_tax_system_code'=> '1',
                    'default_payment_subject'=> 'commodity',
                    'default_payment_mode'=> 'full_prepayment',
                    'default_shipping_payment_subject'=> 'service',
                    'default_shipping_payment_mode'=> 'full_payment',
                    'tax_rates' => array(
                        '1' => 'untaxed',
                        '2' => 'mixed',
                    ),
                    'second_receipt_enabled'=> true,
                    'second_receipt_order_status'=> 'wc-completed',
                )
            ),
            array(
                array(
                    'receipt_enabled'=> true,
                    'self_employed'=> false,
                    'ffd'=> 'ffd12',
                    'default_tax_rate'=> '6',
                    'default_tax_system_code'=> '6',
                    'default_payment_subject'=> 'commodity',
                    'default_payment_mode'=> 'full_prepayment',
                    'default_shipping_payment_subject'=> 'service',
                    'default_shipping_payment_mode'=> 'full_payment',
                    'tax_rates' => array(
                        '1' => 'untaxed',
                        '2' => 'calculated',
                    ),
                    'second_receipt_enabled'=> true,
                    'second_receipt_order_status'=> 'wc-completed',
                )
            ),
            array(
                array(
                    'receipt_enabled'=> true,
                    'self_employed'=> false,
                    'ffd'=> 'ffd11|ffd12',
                    'default_tax_rate'=> '3',
                    'default_tax_system_code'=> '4',
                    'default_payment_subject'=> 'commodity',
                    'default_payment_mode'=> 'full_prepayment',
                    'default_shipping_payment_subject'=> 'service',
                    'default_shipping_payment_mode'=> 'full_payment',
                    'tax_rates' => false,
                    'second_receipt_enabled'=> true,
                    'second_receipt_order_status'=> 'wc-completed',
                )
            ),
        );
    }
}
