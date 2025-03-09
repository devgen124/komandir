<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\Settings;
use Cmssdk\Metrics\Model\ModuleInfo;
use Cmssdk\Metrics\Model\ShopInfo;
use Cmssdk\Metrics\Model\Payment;
use Cmssdk\Metrics\Model\Fiscalization;
use Cmssdk\Metrics\Model\SberbankBusinessOnline;
use Cmssdk\Metrics\Model\Advanced;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     * @throws Exception
     */
    public function testSetAndGetEventTime($data)
    {
        $settings = $this->getInstance();
        if ($data['eventTime'] === null || $data['eventTime'] === '') {
            $settings->setEventTime($data['eventTime']);
            $this->assertEquals(date('Y-m-d H:i:s'), $settings->getEventTime()->format('Y-m-d H:i:s'));
        } elseif (!empty($data['eventTime'])) {
            $eventTime = DateTime::createFromFormat(YOOKASSA_DATE, $data['eventTime']);
            $settings->setEventTime($data['eventTime']);
            $this->assertEquals($eventTime->format('Y-m-d H:i:s'), $settings->getEventTime()->format('Y-m-d H:i:s'));
        } else {
            $this->assertEquals(date('Y-m-d H:i:s'), $settings->getEventTime()->format('Y-m-d H:i:s'));
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetModuleInfo($data)
    {
        $settings = $this->getInstance();
        $moduleInfo = new ModuleInfo($data['module_info']);

        $settings->setModuleInfo($data['module_info']);
        if (is_array($data['module_info']) && !empty($data['module_info'])) {
            $this->assertEquals($moduleInfo, $settings->getModuleInfo());
            $this->assertEquals($data['module_info'], $settings->getModuleInfo()->toArray());
        }

        $settings->setModuleInfo($moduleInfo);
        if (is_array($data['module_info']) && !empty($data['module_info'])) {
            $this->assertEquals($data['module_info'], $settings->getModuleInfo()->toArray());
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetShopInfo($data)
    {
        $settings = $this->getInstance();
        $shopInfo = new ShopInfo($data['shop_info']);

        $settings->setShopInfo($data['shop_info']);
        if (is_array($data['shop_info']) && !empty($data['shop_info'])) {
            $this->assertEquals($shopInfo, $settings->getShopInfo());
            $this->assertEquals($data['shop_info'], $settings->getShopInfo()->toArray());
        }

        $settings->setShopInfo($shopInfo);
        if (is_array($data['shop_info']) && !empty($data['shop_info'])) {
            $this->assertEquals($data['shop_info'], $settings->getShopInfo()->toArray());
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetPayment($data)
    {
        $settings = $this->getInstance();
        $payment = new Payment($data['payment']);

        $settings->setPayment($data['payment']);
        if (is_array($data['payment']) && !empty($data['payment'])) {
            $this->assertEquals($payment, $settings->getPayment());
            $this->assertEquals($data['payment'], $settings->getPayment()->toArray());
        }

        $settings->setPayment($payment);
        if (is_array($data['payment']) && !empty($data['payment'])) {
            $this->assertEquals($data['payment'], $settings->getPayment()->toArray());
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetFiscalization($data)
    {
        $settings = $this->getInstance();
        $fiscalization = new Fiscalization($data['fiscalization']);

        $settings->setFiscalization($data['fiscalization']);
        if (is_array($data['fiscalization']) && !empty($data['fiscalization'])) {
            $this->assertEquals($fiscalization, $settings->getFiscalization());
            $this->assertEquals($data['fiscalization'], $settings->getFiscalization()->toArray());
        }

        $settings->setFiscalization($fiscalization);
        if (is_array($data['fiscalization']) && !empty($data['fiscalization'])) {
            $this->assertEquals($data['fiscalization'], $settings->getFiscalization()->toArray());
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetSbbol($data)
    {
        $settings = $this->getInstance();
        $sbbol = new SberbankBusinessOnline($data['sbbol']);

        $settings->setSbbol($data['sbbol']);
        if (is_array($data['sbbol']) && !empty($data['sbbol'])) {
            $this->assertEquals($sbbol, $settings->getSbbol());
            $this->assertEquals($data['sbbol']['default_tax_rate'], $settings->getSbbol()->toArray()['default_tax_rate']);
        }

        $settings->setSbbol($sbbol);
        if (is_array($data['sbbol']) && !empty($data['sbbol'])) {
            $this->assertEquals($data['sbbol']['default_tax_rate'], $settings->getSbbol()->toArray()['default_tax_rate']);
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testSetAndGetAdvanced($data)
    {
        $settings = $this->getInstance();
        $advanced = new Advanced($data['advanced']);

        $settings->setAdvanced($data['advanced']);
        if (is_array($data['advanced']) && !empty($data['advanced'])) {
            $this->assertEquals($advanced, $settings->getAdvanced());
            $this->assertEquals($data['advanced'], $settings->getAdvanced()->toArray());
        }

        $settings->setAdvanced($advanced);
        if (is_array($data['advanced']) && !empty($data['advanced'])) {
            $this->assertEquals($data['advanced'], $settings->getAdvanced()->toArray());
        }
    }

    /**
     * @dataProvider validDataSettingsProvider
     * @return void
     */
    public function testAllSettings($data)
    {
        $settings = $this->getInstance($data);

        $this->assertIsString($settings->getId());
        $this->assertIsObject($settings->getEventTime());

        if (!empty($data['id'])) {
            $this->assertEquals($data['id'], $settings->getId());
        }
        if (is_array($data['module_info']) && !empty($data['module_info'])) {
            $this->assertEquals($data['module_info'], $settings->getModuleInfo()->toArray());
        }
        if (is_array($data['shop_info']) && !empty($data['shop_info'])) {
            $this->assertEquals($data['shop_info'], $settings->getShopInfo()->toArray());
        }
        if (is_array($data['payment']) && !empty($data['payment'])) {
            $this->assertEquals($data['payment'], $settings->getPayment()->toArray());
        }
        if (is_array($data['fiscalization']) && !empty($data['fiscalization'])) {
            $this->assertEquals($data['fiscalization'], $settings->getFiscalization()->toArray());
        }
        if (is_array($data['sbbol']) && !empty($data['sbbol'])) {
            $this->assertEquals($data['sbbol']['default_tax_rate'], $settings->getSbbol()->toArray()['default_tax_rate']);
        }
        if (is_array($data['advanced']) && !empty($data['advanced'])) {
            $this->assertEquals($data['advanced'], $settings->getAdvanced()->toArray());
        }
        $result = $settings->toArray();
        $this->assertTrue(empty($result['event_time']));
    }

    public function validDataSettingsProvider()
    {
        return array(
            array(
                array(
                    'eventTime'=> '2024-11-27T16:00:00.123Z',
                    'module_info'=> array(
                        'os_version'=> 'Debian.GNU.Linux/12',
                        'php_version'=> 'PHP/8.3.6',
                        'cms_version'=> 'Wordpress/6.5.5',
                        'framework_version'=> 'Woocommerce/9.3.3',
                        'module_version'=> 'PaymentGateway/2.10.1',
                        'sdk_version'=> 'YooKassa.PHP/3.7.1',
                    ),
                    'shop_info'=> array(
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
                    ),
                    'payment'=> array(
                        'scenario'=> 'epl',
                        'save_card_enabled'=> false,
                        'hold_enabled'=> true,
                        'sbbol_enabled'=> true,
                    ),
                    'fiscalization'=> array(
                        'receipt_enabled'=> true,
                        'self_employed'=> false,
                        'ffd'=> 'ffd11',
                        'default_tax_rate'=> '1',
                        'default_tax_system_code'=> '1',
                        'default_payment_subject'=> 'commodity',
                        'default_payment_mode'=> 'full_prepayment',
                        'default_shipping_payment_subject'=> 'service',
                        'default_shipping_payment_mode'=> 'full_payment',
                        'second_receipt_enabled'=> true,
                        'second_receipt_order_status'=> 'wc-completed',
                    ),
                    'sbbol'=> array(
                        'purpose_template'=> 'Оплата заказа №%order_number%',
                        'default_tax_rate'=> 'untaxed',
                        'tax_rates'=> array(
                            '1'=> 'untaxed',
                            '2'=> 'mixed',
                        ),
                    ),
                    'advanced'=> array(
                        'description_template'=> 'Оплата заказа №%order_number%',
                        'success_url'=> 'wc_success',
                        'failure_url'=> 'wc_checkout',
                        'yookassa_currency'=> 'RUB',
                        'yookassa_currency_convert'=> true,
                        'force_clear_cart'=> true,
                        'debug_enabled'=> true,
                        'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                    ),
                )
            ),
            array(
                array(
                    'eventTime'=> '2024-11-27T16:00:00.123Z',
                    'module_info'=> array(
                        'os_version'=> 'Debian.GNU.Linux/12',
                        'php_version'=> 'PHP/8.3.6',
                        'cms_version'=> 'Wordpress/6.5.5',
                        'framework_version'=> 'Woocommerce/9.3.3',
                        'module_version'=> 'PaymentGateway/2.10.1',
                        'sdk_version'=> 'YooKassa.PHP/3.7.1',
                    ),
                    'shop_info'=> array(
                        'account_id'=> '400045',
                        'status'=> 'enabled',
                        'test'=> true,
                        'fiscalization_enabled'=> false,
                        'payment_methods'=> array( 'yoo_money', 'cash', 'bank_card', 'sberbank', ),
                        'itn'=> '52050607001',
                    ),
                    'payment'=> array(
                        'scenario'=> 'widget',
                        'save_card_enabled'=> false,
                        'hold_enabled'=> true,
                        'sbbol_enabled'=> true,
                    ),
                    'fiscalization'=> array(
                        'receipt_enabled'=> true,
                        'self_employed'=> false,
                        'ffd'=> 'ffd12',
                        'default_tax_rate'=> '6',
                        'default_tax_system_code'=> '6',
                        'default_payment_subject'=> 'commodity',
                        'default_payment_mode'=> 'full_prepayment',
                        'default_shipping_payment_subject'=> 'service',
                        'default_shipping_payment_mode'=> 'full_payment',
                        'second_receipt_enabled'=> true,
                        'second_receipt_order_status'=> 'wc-completed',
                    ),
                    'sbbol'=> array(
                        'purpose_template'=> 'Оплата заказа №%order_number%',
                        'default_tax_rate'=> 'untaxed',
                        'tax_rates'=> array(
                            '1'=> 'untaxed',
                            '2'=> 'calculated',
                        ),
                    ),
                    'advanced'=> array(
                        'description_template'=> 'Оплата заказа №%order_number%',
                        'success_url'=> 'wc_success',
                        'failure_url'=> 'wc_checkout',
                        'yookassa_currency'=> 'USD',
                        'yookassa_currency_convert'=> true,
                        'force_clear_cart'=> true,
                        'debug_enabled'=> true,
                        'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                    ),
                )
            ),
            array(
                array(
                    'id'=> '123-456-789',
                    'eventTime'=> null,
                    'module_info'=> array(
                        'os_version'=> 'Centos/9',
                        'php_version'=> 'PHP/8.0.11',
                        'cms_version'=> 'Opencart/3.3.2',
                        'framework_version'=> '',
                        'module_version'=> 'PaymentGateway/2.12.3',
                        'sdk_version'=> 'YooKassa.PHP/2.7.4',
                    ),
                    'shop_info'=> array(
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
                    ),
                    'payment'=> array(
                        'scenario'=> 'epl',
                        'save_card_enabled'=> false,
                        'hold_enabled'=> true,
                        'sbbol_enabled'=> true,
                    ),
                    'fiscalization'=> array(
                        'receipt_enabled'=> true,
                        'self_employed'=> false,
                        'ffd'=> 'ffd11|ffd12',
                        'default_tax_rate'=> '3',
                        'default_tax_system_code'=> '4',
                        'default_payment_subject'=> 'commodity',
                        'default_payment_mode'=> 'full_prepayment',
                        'default_shipping_payment_subject'=> 'service',
                        'default_shipping_payment_mode'=> 'full_payment',
                        'second_receipt_enabled'=> true,
                        'second_receipt_order_status'=> 'wc-completed',
                    ),
                    'sbbol'=> array(
                        'purpose_template'=> 'Оплата заказа №%order_number%',
                        'default_tax_rate'=> 'untaxed',
                        'tax_rates'=> false,
                    ),
                    'advanced'=> array(
                        'description_template'=> 'Оплата заказа №%order_number%',
                        'success_url'=> 'wc_success',
                        'failure_url'=> 'wc_checkout',
                        'yookassa_currency'=> 'RUB',
                        'yookassa_currency_convert'=> true,
                        'force_clear_cart'=> false,
                        'debug_enabled'=> true,
                        'notify_url'=> 'https://merchant-site.com/yookassa-notify',
                    ),
                )
            ),
            array(
                array(
                    'eventTime'=> null,
                    'module_info'=> null,
                    'shop_info'=> null,
                    'payment'=> null,
                    'fiscalization'=> null,
                    'sbbol'=> null,
                    'advanced'=> null,
                )
            ),
        );
    }

    /**
     * @return Settings
     */
    private function getInstance($data = null)
    {
        return new Settings($data);
    }
}
