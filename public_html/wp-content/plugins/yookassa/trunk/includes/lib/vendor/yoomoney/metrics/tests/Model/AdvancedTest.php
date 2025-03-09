<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\Advanced;
use PHPUnit\Framework\TestCase;


class AdvancedTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDescriptionTemplate($data)
    {
        $advanced = new Advanced();
        $advanced->setDescriptionTemplate($data['description_template']);
        $this->assertEquals($data['description_template'], $advanced->getDescriptionTemplate());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetSuccessUrl($data)
    {
        $advanced = new Advanced();
        $advanced->setSuccessUrl($data['success_url']);
        $this->assertEquals($data['success_url'], $advanced->getSuccessUrl());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetFailureUrl($data)
    {
        $advanced = new Advanced();
        $advanced->setFailureUrl($data['failure_url']);
        $this->assertEquals($data['failure_url'], $advanced->getFailureUrl());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetYookassaCurrency($data)
    {
        $advanced = new Advanced();
        $advanced->setYookassaCurrency($data['yookassa_currency']);
        $this->assertEquals($data['yookassa_currency'], $advanced->getYookassaCurrency());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetYookassaCurrencyConvert($data)
    {
        $advanced = new Advanced();
        $advanced->setYookassaCurrencyConvert($data['yookassa_currency_convert']);
        $this->assertEquals($data['yookassa_currency_convert'], $advanced->getYookassaCurrencyConvert());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetForceClearCart($data)
    {
        $advanced = new Advanced();
        $advanced->setForceClearCart($data['force_clear_cart']);
        $this->assertEquals($data['force_clear_cart'], $advanced->getForceClearCart());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetDebugEnabled($data)
    {
        $advanced = new Advanced();
        $advanced->setDebugEnabled($data['debug_enabled']);
        $this->assertEquals($data['debug_enabled'], $advanced->getDebugEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testSetAndGetNotifyUrl($data)
    {
        $advanced = new Advanced();
        $advanced->setNotifyUrl($data['notify_url']);
        $this->assertEquals($data['notify_url'], $advanced->getNotifyUrl());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $advanced = new Advanced($data);
        $this->assertEquals($data, $advanced->toArray());

        $this->assertEquals($data['description_template'], $advanced->getDescriptionTemplate());
        $this->assertEquals($data['success_url'], $advanced->getSuccessUrl());
        $this->assertEquals($data['failure_url'], $advanced->getFailureUrl());
        $this->assertEquals($data['yookassa_currency'], $advanced->getYookassaCurrency());
        $this->assertEquals($data['yookassa_currency_convert'], $advanced->getYookassaCurrencyConvert());
        $this->assertEquals($data['force_clear_cart'], $advanced->getForceClearCart());
        $this->assertEquals($data['debug_enabled'], $advanced->getDebugEnabled());
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'description_template'=> 'Оплата заказа №%order_number%',
                    'success_url'=> 'wc_success',
                    'failure_url'=> 'wc_checkout',
                    'yookassa_currency'=> 'RUB',
                    'yookassa_currency_convert'=> true,
                    'force_clear_cart'=> true,
                    'debug_enabled'=> true,
                    'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                )
            ),
            array(
                array(
                    'description_template'=> 'Оплата заказа №%order_number%',
                    'success_url'=> 'wc_success',
                    'failure_url'=> 'wc_checkout',
                    'yookassa_currency'=> 'USD',
                    'yookassa_currency_convert'=> true,
                    'force_clear_cart'=> true,
                    'debug_enabled'=> true,
                    'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                )
            ),
            array(
                array(
                    'description_template'=> 'Оплата заказа №%order_number%',
                    'success_url'=> 'wc_success',
                    'failure_url'=> 'wc_checkout',
                    'yookassa_currency'=> 'RUB',
                    'yookassa_currency_convert'=> true,
                    'force_clear_cart'=> false,
                    'debug_enabled'=> true,
                    'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                )
            ),
        );
    }
}
