<?php

namespace Tests\Cmssdk\Metrics\Model;

use Cmssdk\Metrics\Model\ModuleInfo;
use PHPUnit\Framework\TestCase;

class ModuleInfoTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function testGettersAndSetters($data)
    {
        $moduleInfo = new ModuleInfo();

        $moduleInfo->setOsVersion($data['os_version']);
        $moduleInfo->setPhpVersion($data['php_version']);
        $moduleInfo->setCmsVersion($data['cms_version']);
        $moduleInfo->setFrameworkVersion($data['framework_version']);
        $moduleInfo->setModuleVersion($data['module_version']);
        $moduleInfo->setSdkVersion($data['sdk_version']);
        $moduleInfo->setHost($data['host']);

        $this->assertEquals($data['os_version'], $moduleInfo->getOsVersion());
        $this->assertEquals($data['php_version'], $moduleInfo->getPhpVersion());
        $this->assertEquals($data['cms_version'], $moduleInfo->getCmsVersion());
        $this->assertEquals($data['framework_version'], $moduleInfo->getFrameworkVersion());
        $this->assertEquals($data['module_version'], $moduleInfo->getModuleVersion());
        $this->assertEquals($data['sdk_version'], $moduleInfo->getSdkVersion());
        $this->assertEquals($data['host'], $moduleInfo->getHost());
    }

    /**
     * @dataProvider dataProvider
     * @return void
     */
    public function testSetAll($data)
    {
        $moduleInfo = new ModuleInfo($data);
        $this->assertEquals($data, $moduleInfo->toArray());

        $this->assertEquals($data['os_version'], $moduleInfo->getOsVersion());
        $this->assertEquals($data['php_version'], $moduleInfo->getPhpVersion());
        $this->assertEquals($data['cms_version'], $moduleInfo->getCmsVersion());
        $this->assertEquals($data['framework_version'], $moduleInfo->getFrameworkVersion());
        $this->assertEquals($data['module_version'], $moduleInfo->getModuleVersion());
        $this->assertEquals($data['sdk_version'], $moduleInfo->getSdkVersion());
        $this->assertEquals($data['host'], $moduleInfo->getHost());
    }

    public function dataProvider()
    {
        return array(
            array(
                array(
                    'os_version'=> 'Debian.GNU.Linux/12',
                    'php_version'=> 'PHP/8.3.6',
                    'cms_version'=> 'Wordpress/6.5.5',
                    'framework_version'=> 'Woocommerce/9.3.3',
                    'module_version'=> 'PaymentGateway/2.10.1',
                    'sdk_version'=> 'YooKassa.PHP/3.7.1',
                    'host'=> 'localhost',
                )
            ),
            array(
                array(
                    'os_version'=> 'Debian.GNU.Linux/12',
                    'php_version'=> 'PHP/8.3.6',
                    'cms_version'=> 'Wordpress/6.5.5',
                    'framework_version'=> 'Woocommerce/9.3.3',
                    'module_version'=> 'PaymentGateway/2.10.1',
                    'sdk_version'=> 'YooKassa.PHP/3.7.1',
                    'host'=> 'woocommerce.merchant.com',
                )
            ),
            array(
                array(
                    'os_version'=> 'Centos/9',
                    'php_version'=> 'PHP/8.0.11',
                    'cms_version'=> 'Opencart/3.3.2',
                    'framework_version'=> '',
                    'module_version'=> 'PaymentGateway/2.12.3',
                    'sdk_version'=> 'YooKassa.PHP/2.7.4',
                    'host'=> 'opencart.merchant.com',
                )
            ),
        );
    }
}


