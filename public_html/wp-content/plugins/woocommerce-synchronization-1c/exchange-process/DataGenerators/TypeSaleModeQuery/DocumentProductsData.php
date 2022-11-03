<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataGenerators\TypeSaleModeQuery;

use Itgalaxy\Wc\Exchange1c\Includes\Logger;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class DocumentProductsData
{
    /**
     * @param \SimpleXMLElement $document
     * @param \WC_Order         $order
     *
     * @return void
     */
    public static function generate(\SimpleXMLElement $document, \WC_Order $order)
    {
        $includeTax = !SettingsHelper::isEmpty('send_orders_include_tax_to_price_item_and_shipping');
        $productsXml = $document->addChild('Товары');
        $products = self::prepareList($order);

        foreach ($products as $product) {
            $productXml = $productsXml->addChild('Товар');

            /**
             * Filters the prepared product from an order before using it in XML.
             *
             * @since 1.84.3
             *
             * @param array     $product
             * @param \WC_Order $order
             */
            $product = \apply_filters('itglx_wc1c_xml_order_product_row_params', $product, $order);

            // has 1C guid
            if (!empty($product['_id_1c'])) {
                $productXml->addChild('Ид', $product['_id_1c']);
            } else {
                if (!SettingsHelper::isEmpty('send_orders_use_product_id_from_site')) {
                    Logger::log('used product/variation id form site in node "Ид"', [$product['id'], $order->get_id()]);

                    $productXml->addChild('Ид', $product['id']);
                } else {
                    Logger::log('generate product without node "Ид"', [$product['id'], $order->get_id()]);
                }

                if ($product['sku'] !== '') {
                    Logger::log('no 1C guid, added "Артикул"', [$product['id'], $product['sku'], $order->get_id()]);

                    $productXml->addChild('Артикул', $product['sku']);
                } else {
                    Logger::log('no 1C guid and empty sku, "Артикул" no added', [$product['id'], $order->get_id()]);
                }
            }

            $productXml->Наименование = wp_strip_all_tags(html_entity_decode($product['name']));
            $unit = get_post_meta($product['id'], '_unit', true);

            $base = $productXml->addChild('БазоваяЕдиница', $unit ? $unit['value'] : 'шт');
            $base->addAttribute('Код', $unit ? $unit['code'] : 796);
            $base->addAttribute('НаименованиеПолное', $unit ? $unit['nameFull'] : 'Штука');
            $base->addAttribute('МеждународноеСокращение', $unit ? $unit['internationalAcronym'] : 'PCE');

            if ($includeTax) {
                $product['lineTotal'] += $product['lineTax'];
            }

            $productXml->addChild(
                'ЦенаЗаЕдиницу',
                $product['quantity'] ? $product['lineTotal'] / $product['quantity'] : 0
            );
            $productXml->addChild('Количество', $product['quantity']);
            $productXml->addChild('Сумма', $product['lineTotal']);

            if (
                !SettingsHelper::isEmpty('send_orders_add_information_discount_for_each_item')
                && !empty($product['discountItem'])
            ) {
                $discounts = $productXml->addChild('Скидки');
                $discount = $discounts->addChild('Скидка');
                $discount->addChild('Наименование', 'Скидка');
                $discount->addChild('Сумма', $product['discountItem']);
                $discount->addChild('УчтеноВСумме', 'true');
            }

            if (!empty($product['attributes'])) {
                $characteristics = $productXml->addChild('ХарактеристикиТовара');

                foreach ($product['attributes'] as $attribute) {
                    if (!isset($attribute[1])) {
                        continue;
                    }

                    $characteristic = $characteristics->addChild('ХарактеристикаТовара');
                    $characteristic->addChild('Наименование', $attribute[0]);
                    $characteristic->addChild('Значение', $attribute[1]);
                }
            }

            $details = $productXml->addChild('ЗначенияРеквизитов');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ВидНоменклатуры');
            $detail->addChild('Значение', 'Товар');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ТипНоменклатуры');
            $detail->addChild('Значение', 'Товар');

            if (!empty($product['lineId'])) {
                $detail = $details->addChild('ЗначениеРеквизита');
                $detail->addChild('Наименование', 'НомерПозицииКорзины');
                $detail->addChild('Значение', $product['lineId']);
            }

            // can be used if you want to transfer custom data
            $moreProductInfo = apply_filters(
                'itglx_wc1c_xml_product_info_custom',
                [],
                $product['productId'],
                $product['variationId'],
                $product
            );

            if ($moreProductInfo) {
                foreach ($moreProductInfo as $key => $moreProductInfoValue) {
                    $productXml->addChild($key, $moreProductInfoValue);
                }
            }
        }

        self::shippingItem($order, $productsXml);
    }

    /**
     * @param \WC_Order $order
     *
     * @return array
     */
    private static function prepareList(\WC_Order $order)
    {
        $products = [];

        foreach ($order->get_items() as $item) {
            if (version_compare(WC_VERSION, '4.4', '<')) {
                $product = $order->get_product_from_item($item);
            } else {
                $product = $item->get_product();
            }

            $sku = '';

            if ($product instanceof \WC_Product && $product->get_sku()) {
                $sku = $product->get_sku();
            }

            $totalItem = round((float) $item->get_total(), wc_get_price_decimals());
            $discountItem = round((float) $item->get_subtotal() - (float) $item->get_total(), wc_get_price_decimals());
            $taxItem = round((float) $item->get_total_tax(), wc_get_price_decimals());

            if (
                !SettingsHelper::isEmpty('send_orders_combine_data_variation_as_main_product')
                && $item['variation_id']
            ) {
                if (!isset($products[$item['product_id']])) {
                    $products[$item['product_id']] = [
                        'id' => $item['product_id'],
                        'productId' => $item['product_id'],
                        'variationId' => '',
                        '_id_1c' => get_post_meta($item['product_id'], '_id_1c', true),
                        'quantity' => (float) $item['qty'],
                        'name' => htmlspecialchars(get_post_field('post_title', $item['product_id'])),
                        'lineTotal' => $totalItem,
                        'discountItem' => $discountItem,
                        'lineTax' => $taxItem,
                        'sku' => $sku,
                        'attributes' => [],
                    ];
                } else {
                    $products[$item['product_id']]['quantity'] += (float) $item['qty'];
                    $products[$item['product_id']]['lineTotal'] += $totalItem;
                    $products[$item['product_id']]['discountItem'] += $discountItem;
                    $products[$item['product_id']]['lineTax'] += $taxItem;
                }
            } else {
                $exportProduct = [
                    'originalItem' => $item,
                    'originalProduct' => $product,
                    'id' => $item['variation_id'] ? $item['variation_id'] : $item['product_id'],
                    'productId' => $item['product_id'],
                    'variationId' => $item['variation_id'],
                    '_id_1c' => get_post_meta(
                        $item['variation_id'] ? $item['variation_id'] : $item['product_id'],
                        '_id_1c',
                        true
                    ),
                    'quantity' => $item['qty'],
                    'name' => htmlspecialchars($item['name']),
                    'lineTotal' => $totalItem,
                    'discountItem' => $discountItem,
                    'lineTax' => $taxItem,
                    'lineId' => $item->get_id(),
                    'sku' => $sku,
                    'attributes' => [],
                ];

                if (
                    empty($exportProduct['_id_1c'])
                    && $product instanceof \WC_Product
                    && $item['variation_id']
                    && !SettingsHelper::isEmpty('send_orders_use_variation_characteristics_from_site')
                    && $product->get_attribute_summary()
                ) {
                    $attributes = explode(', ', $product->get_attribute_summary());

                    foreach ($attributes as $attribute) {
                        $exportProduct['attributes'][] = explode(': ', $attribute);
                    }
                }

                $products[] = $exportProduct;
            }
        }

        /**
         * Filters a prepared set of products from an order before using it in XML.
         *
         * @since 1.84.3
         *
         * @param array     $products
         * @param \WC_Order $order
         */
        return \apply_filters('itglx_wc1c_xml_order_product_rows', $products, $order);
    }

    /**
     * @param \WC_Order         $order
     * @param \SimpleXMLElement $productsXml
     *
     * @return void
     */
    private static function shippingItem(\WC_Order $order, \SimpleXMLElement $productsXml)
    {
        if ($order->get_shipping_total() <= 0) {
            return;
        }

        $shippingPrice = round($order->get_shipping_total(), \wc_get_price_decimals());

        if (!SettingsHelper::isEmpty('send_orders_include_tax_to_price_item_and_shipping')) {
            $shippingPrice += round($order->get_shipping_tax(), \wc_get_price_decimals());
        }

        $shippingItemArray = [
            'Ид' => 'ORDER_DELIVERY',
            'Наименование' => \wp_strip_all_tags(html_entity_decode($order->get_shipping_method())),
            'БазоваяЕдиница' => [
                'value' => 'шт',
                'attributes' => [
                    'Код' => 796,
                    'НаименованиеПолное' => 'Штука',
                    'МеждународноеСокращение' => 'PCE',
                ],
            ],
            'ЦенаЗаЕдиницу' => $shippingPrice,
            'Количество' => 1,
            'Сумма' => $shippingPrice,
            'ЗначенияРеквизитов' => [
                'ЗначениеРеквизита' => [
                    [
                        'Наименование' => 'ВидНоменклатуры',
                        'Значение' => 'Услуга',
                    ],
                    [
                        'Наименование' => 'ТипНоменклатуры',
                        'Значение' => 'Услуга',
                    ],
                ],
            ],
        ];

        /**
         * Filters the content of the `shipping` item by `order` before generating XML.
         *
         * @since 1.103.0
         *
         * @param array     $shippingItemArray
         * @param \WC_Order $order
         */
        $shippingItemArray = \apply_filters('itglx/wc/1c/sale/query/order-shipping-item', $shippingItemArray, $order);

        $shippingItemXml = $productsXml->addChild('Товар');

        foreach ($shippingItemArray as $key => $value) {
            if (!is_array($value)) {
                $shippingItemXml->addChild($key, $value);
            } elseif (isset($value['attributes'])) {
                $child = $shippingItemXml->addChild($key, $value['value']);

                foreach ($value['attributes'] as $attributeName => $attributeValue) {
                    $child->addAttribute($attributeName, $attributeValue);
                }
            } else {
                $child = $shippingItemXml->addChild($key);

                foreach ($value as $subKey => $subValue) {
                    foreach ($subValue as $subChildValue) {
                        $subChild = $child->addChild($subKey);

                        foreach ($subChildValue as $nodeName => $nodeValue) {
                            $subChild->addChild($nodeName, $nodeValue);
                        }
                    }
                }
            }
        }
    }
}
