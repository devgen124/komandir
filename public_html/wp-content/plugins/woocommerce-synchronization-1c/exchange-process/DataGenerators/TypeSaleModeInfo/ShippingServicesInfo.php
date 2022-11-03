<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataGenerators\TypeSaleModeInfo;

class ShippingServicesInfo
{
    /**
     * @param \SimpleXMLElement $xml
     *
     * @return void
     */
    public static function generate(\SimpleXMLElement $xml)
    {
        $shippingServicesList = $xml->addChild('СлужбыДоставки');

        foreach (self::getList() as $id => $name) {
            $shippingServiceElement = $shippingServicesList->addChild('Элемент');
            $shippingServiceElement->addChild('Ид', $id);
            $shippingServiceElement->addChild('Название', $name);
        }
    }

    /**
     * @return array
     */
    private static function getList()
    {
        $shippingServicesList = [];

        foreach (\WC()->shipping->get_shipping_methods() as $id => $method) {
            if (!isset($method->enabled) || $method->enabled !== 'yes') {
                continue;
            }

            $shippingServicesList[$id] = !empty($method->title) ? $method->title : $method->method_title;
        }

        return $shippingServicesList;
    }
}
