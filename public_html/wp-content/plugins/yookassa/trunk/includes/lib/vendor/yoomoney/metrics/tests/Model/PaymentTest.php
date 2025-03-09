<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetScenario($data)
    {
        $payment = new Payment();
        $payment->setScenario($data['scenario']);
        $this->assertEquals($data['scenario'], $payment->getScenario());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetSaveCardEnabled($data)
    {
        $payment = new Payment();
        $payment->setSaveCardEnabled($data['save_card_enabled']);
        $this->assertEquals($data['save_card_enabled'], $payment->getSaveCardEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetHoldEnabled($data)
    {
        $payment = new Payment();
        $payment->setHoldEnabled($data['hold_enabled']);
        $this->assertEquals($data['hold_enabled'], $payment->getHoldEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetSbbolEnabled($data)
    {
        $payment = new Payment();
        $payment->setSbbolEnabled($data['sbbol_enabled']);
        $this->assertEquals($data['sbbol_enabled'], $payment->getSbbolEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $payment = new Payment($data);
        $this->assertEquals($data, $payment->toArray());

        $this->assertEquals($data['scenario'], $payment->getScenario());
        $this->assertEquals($data['save_card_enabled'], $payment->getSaveCardEnabled());
        $this->assertEquals($data['hold_enabled'], $payment->getHoldEnabled());
        $this->assertEquals($data['sbbol_enabled'], $payment->getSbbolEnabled());
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'scenario'=> 'epl',
                    'save_card_enabled'=> false,
                    'hold_enabled'=> true,
                    'sbbol_enabled'=> true,
                )
            ),
            array(
                array(
                    'scenario'=> 'widget',
                    'save_card_enabled'=> false,
                    'hold_enabled'=> true,
                    'sbbol_enabled'=> true,
                )
            ),
            array(
                array(
                    'scenario'=> 'epl',
                    'save_card_enabled'=> false,
                    'hold_enabled'=> true,
                    'sbbol_enabled'=> true,
                )
            ),
        );
    }
}
