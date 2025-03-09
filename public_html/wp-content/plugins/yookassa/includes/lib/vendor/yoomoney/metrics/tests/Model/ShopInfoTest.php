<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\ShopInfo;
use PHPUnit\Framework\TestCase;

class ShopInfoTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetAccountId($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setAccountId($data['account_id']);
        $this->assertEquals($data['account_id'], $shopInfo->getAccountId());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetStatus($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setStatus($data['status']);
        $this->assertEquals($data['status'], $shopInfo->getStatus());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetTest($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setTest($data['test']);
        $this->assertEquals($data['test'], $shopInfo->getTest());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetFiscalization($data)
    {
        $shopInfo = new ShopInfo();
        if (isset($data['fiscalization'])) {
            $shopInfo->setFiscalization($data['fiscalization']);
            $this->assertEquals($data['fiscalization'], $shopInfo->getFiscalization());
        }
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetFiscalizationEnabled($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setFiscalizationEnabled($data['fiscalization_enabled']);
        $this->assertEquals($data['fiscalization_enabled'], $shopInfo->getFiscalizationEnabled());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetPaymentMethods($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setPaymentMethods($data['payment_methods']);
        $this->assertEquals($data['payment_methods'], $shopInfo->getPaymentMethods());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAndGetItn($data)
    {
        $shopInfo = new ShopInfo();
        $shopInfo->setItn($data['itn']);
        $this->assertEquals($data['itn'], $shopInfo->getItn());
    }


    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $shopInfo = new ShopInfo($data);
        $this->assertEquals($data, $shopInfo->toArray());

        $this->assertEquals($data['account_id'], $shopInfo->getAccountId());
        $this->assertEquals($data['status'], $shopInfo->getStatus());
        $this->assertEquals($data['test'], $shopInfo->getTest());
        $this->assertEquals($data['fiscalization_enabled'], $shopInfo->getFiscalizationEnabled());
        $this->assertEquals($data['payment_methods'], $shopInfo->getPaymentMethods());
        $this->assertEquals($data['itn'], $shopInfo->getItn());
        if (isset($data['fiscalization'])) {
            $this->assertEquals($data['fiscalization'], $shopInfo->getFiscalization());
        }
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'account_id'=> '400044',
                    'status'=> 'enabled',
                    'test'=> false,
                    'fiscalization'=> array(
                        'enabled'=> true,
                        'provider'=> 'avanpost',
                    ),
                    'fiscalization_enabled'=> true,
                    'payment_methods'=> array( 'yoo_money', 'cash', 'bank_card', 'sberbank', ),
                    'itn'=> '52050607001',
                )
            ),
            array(
                array(
                    'account_id'=> '400045',
                    'status'=> 'enabled',
                    'test'=> true,
                    'fiscalization_enabled'=> false,
                    'payment_methods'=> array( 'yoo_money', 'cash', 'bank_card', 'sberbank', ),
                    'itn'=> '52050607001',
                )
            ),
            array(
                array(
                    'account_id'=> '400045',
                    'status'=> 'enabled',
                    'test'=> true,
                    'fiscalization'=> array(
                        'enabled'=> true,
                        'provider'=> 'atol',
                    ),
                    'fiscalization_enabled'=> true,
                    'payment_methods'=> array( 'yoo_money', 'cash', 'bank_card', 'sberbank', ),
                    'itn'=> '52050607001',
                )
            ),
        );
    }

}


