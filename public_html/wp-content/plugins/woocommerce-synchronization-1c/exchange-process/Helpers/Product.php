<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductCountry;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductManufacturer;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductRequisites;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductResolver;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductUnit;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Entities\ImageEntity;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class Product
{
    /**
     * @param \SimpleXMLElement $element      Note `Товар`.
     * @param array             $productEntry
     * @param array             $categoryIds
     * @param string            $productHash
     *
     * @throws \Exception
     *
     * @return array
     */
    public static function mainProductData($element, $productEntry, $categoryIds, $productHash)
    {
        $productMeta = [];
        $productMeta['_unit'] = ProductUnit::process($element);

        $productEntry['categoryID'] = [];

        if (
            SettingsHelper::isEmpty('skip_categories')
            && isset($element->Группы->Ид)
        ) {
            foreach ($element->Группы->Ид as $groupXmlId) {
                if (isset($categoryIds[(string) $groupXmlId])) {
                    $productEntry['categoryID'][] = $categoryIds[(string) $groupXmlId];
                }
            }

            $productEntry['categoryID'] = array_unique($productEntry['categoryID']);
        }

        $productMeta['_md5'] = $productHash;

        // resolve requisites
        $requisites = ProductRequisites::process($element);

        $productMeta['_all_product_requisites'] = $requisites['allRequisites'];

        // support the choice of where to get sku
        if (SettingsHelper::isEmpty('get_product_sku_from') || SettingsHelper::get('get_product_sku_from') === 'sku') {
            $productMeta['_sku'] = isset($element->Артикул) ? trim((string) $element->Артикул) : '';
        } elseif (SettingsHelper::get('get_product_sku_from') === 'requisite_code') {
            $productMeta['_sku'] = isset($requisites['allRequisites']['Код'])
                ? trim($requisites['allRequisites']['Код'])
                : '';
        } elseif (SettingsHelper::get('get_product_sku_from') === 'code') {
            $productMeta['_sku'] = isset($element->Код) ? trim((string) $element->Код) : '';
        } elseif (SettingsHelper::get('get_product_sku_from') === 'barcode') {
            $productMeta['_sku'] = isset($element->Штрихкод) ? trim((string) $element->Штрихкод) : '';
        }

        if (!empty($requisites['fullName'])) {
            $productEntry['title'] = $requisites['fullName'];
        } else {
            $productEntry['title'] = wp_strip_all_tags(
                html_entity_decode(
                    trim((string) $element->Наименование)
                )
            );
        }

        // set weight
        if (SettingsHelper::isEmpty('skip_product_weight') && !empty($requisites['weight'])) {
            // apply factor if configured
            if (
                !SettingsHelper::isEmpty('product_weight_use_factor')
                && !SettingsHelper::isEmpty('product_weight_factor_value')
            ) {
                $requisites['weight'] *= (float) SettingsHelper::get('product_weight_factor_value');
            }

            $productMeta['_weight'] = $requisites['weight'];
        }

        if (SettingsHelper::isEmpty('skip_product_sizes')) {
            // set length
            if (isset($requisites['length'])) {
                $productMeta['_length'] = $requisites['length'];
            }

            // set width
            if (isset($requisites['width'])) {
                $productMeta['_width'] = $requisites['width'];
            }

            // set height
            if (isset($requisites['height'])) {
                $productMeta['_height'] = $requisites['height'];
            }
        }

        if (SettingsHelper::isEmpty('skip_post_content_excerpt')) {
            if (!empty($requisites['htmlPostContent'])) {
                $productEntry['post_content'] = $requisites['htmlPostContent'];
            }

            $description = html_entity_decode((string) $element->Описание);

            // if write the product description in excerpt
            if (!SettingsHelper::isEmpty('write_product_description_in_excerpt')) {
                $productEntry['post_excerpt'] = $description;
            // else usual logic
            } elseif (!empty($description)) {
                if (empty($productEntry['post_content'])) {
                    $productEntry['post_content'] = $description;
                } else {
                    $productEntry['post_excerpt'] = $description;
                }
            }
        }

        $isNewProduct = true;

        // if exists product
        if (!empty($productEntry['ID'])) {
            $currentPostStatus = self::getStatus($productEntry['ID']);

            $params = [
                'ID' => $productEntry['ID'],
            ];

            $isNewProduct = false;

            if (isset($productEntry['post_content'])) {
                $params['post_content'] = $productEntry['post_content'];
            }

            if (isset($productEntry['post_excerpt'])) {
                $params['post_excerpt'] = $productEntry['post_excerpt'];
            }

            if (SettingsHelper::isEmpty('skip_post_title')) {
                $params['post_title'] = $productEntry['title'];
            }

            // restore product from trash
            if ($currentPostStatus === 'trash' && !SettingsHelper::isEmpty('restore_products_from_trash')) {
                $params['post_status'] = 'publish';

                Logger::log(
                    '(product) restore from trash, ID - ' . $productEntry['ID'],
                    [get_post_meta($productEntry['ID'], '_id_1c', true)]
                );
            }

            /**
             * Filters the list of values when updating a `product` post.
             *
             * @since 1.83.0
             *
             * @param array             $params
             * @param \SimpleXMLElement $element Node 'Товар'.
             *
             * @see https://developer.wordpress.org/reference/functions/wp_update_post/
             */
            $params = \apply_filters('itglx_wc1c_update_post_product_params', $params, $element);

            \wp_update_post($params);

            foreach ($productMeta as $key => $value) {
                self::saveMetaValue($productEntry['ID'], $key, $value);
            }

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);

            Logger::log(
                '(product) updated post, ID - ' . $productEntry['ID'] . ', status - ' . $currentPostStatus,
                [get_post_meta($productEntry['ID'], '_id_1c', true)]
            );
        } else {
            Logger::log('(product) insert start', [(string) $element->Ид]);

            $params = [
                'post_title' => $productEntry['title'],
                'post_type' => 'product',
                'post_status' => 'publish',
            ];

            if (isset($productEntry['post_content'])) {
                $params['post_content'] = $productEntry['post_content'];
            }

            if (isset($productEntry['post_excerpt'])) {
                $params['post_excerpt'] = $productEntry['post_excerpt'];
            }

            /**
             * Filters the list of values when creating a `product` post.
             *
             * @since 1.58.0
             * @since 1.58.1 The `$element` parameter was added.
             *
             * @param array             $params
             * @param \SimpleXMLElement $element Node 'Товар'.
             *
             * @see https://developer.wordpress.org/reference/functions/wp_insert_post/
             */
            $params = \apply_filters('itglx_wc1c_insert_post_new_product_params', $params, $element);

            $productEntry['ID'] = \wp_insert_post($params);

            if (\is_wp_error($productEntry['ID'])) {
                Logger::log('(product) `wp_error` adding', [(string) $element->Ид, $productEntry['ID']], 'warning');

                return [];
            }

            $productMeta['_sale_price'] = '';
            $productMeta['_stock'] = 0;
            $productMeta['_manage_stock'] = get_option('woocommerce_manage_stock'); // yes or no
            $productMeta['_id_1c'] = ProductResolver::getNomenclatureGuid($element);

            foreach ($productMeta as $key => $value) {
                self::saveMetaValue($productEntry['ID'], $key, $value);
            }

            Logger::log('(product) added post, ID - ' . $productEntry['ID'], [$productMeta['_id_1c']]);

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);
            self::hide($productEntry['ID'], true);

            Logger::log('(product) insert end', [$productMeta['_id_1c']]);
        }

        /*
         * Example xml structure
         * position - Товар -> Метки
         *
        <Метки>
            <Ид>f108c911-3bca-11eb-841f-ade4b337caca</Ид>
            <Ид>f108c912-3bca-11eb-841f-ade4b337caca</Ид>
            <Ид>f108c913-3bca-11eb-841f-ade4b337caca</Ид>
        </Метки>
        */
        if (isset($element->Метки->Ид) && !empty($_SESSION['IMPORT_1C']['productTags'])) {
            $tagIds = [];

            foreach ($element->Метки->Ид as $tagXmlId) {
                if (isset($_SESSION['IMPORT_1C']['productTags'][(string) $tagXmlId])) {
                    $tagIds[] = $_SESSION['IMPORT_1C']['productTags'][(string) $tagXmlId];
                }
            }

            \wp_set_object_terms($productEntry['ID'], array_map('intval', $tagIds), 'product_tag');
        }

        // is new or not disabled attribute data processing
        if ($isNewProduct || SettingsHelper::isEmpty('skip_post_attributes')) {
            ProductAttributes::process($element, $productEntry['ID']);

            // resolve product manufacturer data to attribute
            ProductManufacturer::process($element, $productEntry['ID']);

            // resolve product country data to attribute
            ProductCountry::process($element, $productEntry['ID']);
        }

        // index/reindex relevanssi
        if (function_exists('relevanssi_insert_edit')) {
            relevanssi_insert_edit($productEntry['ID']);
        }

        return $productEntry;
    }

    public static function setCategory($productID, $categoryIds)
    {
        if (empty($categoryIds) || !is_array($categoryIds)) {
            Logger::log(
                '(product) empty `product_cat` list, ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );

            return;
        }

        if (empty($_SESSION['IMPORT_1C']['product_cat_list'])) {
            $_SESSION['IMPORT_1C']['product_cat_list'] = Term::getProductCatIDs(false);
        }

        // https://developer.wordpress.org/reference/functions/wp_get_object_terms/
        $currentProductCats = wp_get_object_terms($productID, 'product_cat', ['fields' => 'ids']);

        //add only categories not from 1C to the main set
        if (!empty($currentProductCats)) {
            foreach ($currentProductCats as $termID) {
                if (!in_array($termID, $_SESSION['IMPORT_1C']['product_cat_list'])) {
                    $categoryIds[] = $termID;
                }
            }
        }

        $categoryIds = array_map('intval', $categoryIds);

        Logger::log(
            '(product) set `product_cat` list, ID - ' . $productID,
            [get_post_meta($productID, '_id_1c', true), $categoryIds]
        );

        Term::setObjectTerms($productID, $categoryIds, 'product_cat');
    }

    public static function show($productID, $withSetStatus = false, $statusValue = 'instock')
    {
        if ($withSetStatus) {
            Logger::log('(show) set stock status, ID - ' . $productID, [$statusValue]);

            $oldStatus = get_post_meta($productID, '_stock_status', true);

            if ($oldStatus !== $statusValue) {
                \update_post_meta($productID, '_stock_status', $statusValue);
                self::triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, $statusValue);
            }
        }

        $setTerms = [];

        if (has_term('featured', 'product_visibility', $productID)) {
            $setTerms[] = 'featured';
        }

        Term::setObjectTerms($productID, $setTerms, 'product_visibility');
    }

    public static function hide($productID, $withSetStatus = false)
    {
        if (apply_filters('itglx_wc1c_stop_hide_product_method', false, $productID)) {
            return;
        }

        if ($withSetStatus) {
            Logger::log('(hide) set stock status, ID - ' . $productID, ['outofstock']);

            $oldStatus = get_post_meta($productID, '_stock_status', true);

            if ($oldStatus !== 'outofstock') {
                \update_post_meta($productID, '_stock_status', 'outofstock');
                self::triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, 'outofstock');
            }
        }

        $setTerms = [
            'outofstock',
        ];

        if (SettingsHelper::get('products_stock_null_rule', '0') !== '2') {
            $setTerms[] = 'exclude-from-catalog';
            $setTerms[] = 'exclude-from-search';
        }

        if (has_term('featured', 'product_visibility', $productID)) {
            $setTerms[] = 'featured';
        }

        Term::setObjectTerms($productID, $setTerms, 'product_visibility');
    }

    /**
     * @param \SimpleXMLElement $element 'Товар/Предложение' node object.
     * @param string            $guid
     *
     * @return null|int
     */
    public static function getSiteProductId(\SimpleXMLElement $element, $guid)
    {
        $product = self::getProductIdByMeta($guid);

        if ($product) {
            return $product;
        }

        /**
         * @since 1.13.0
         */
        $product = \apply_filters('itglx_wc1c_find_product_id', $product, $element);

        if ($product) {
            Logger::log('(product) filter `itglx_wc1c_find_product_id`, `ID` - ' . $product, [$guid]);
            self::saveMetaValue($product, '_id_1c', $guid);
        }

        return $product;
    }

    public static function saveMetaValue($productID, $metaKey, $metaValue, $parentProductID = null)
    {
        if ($parentProductID) {
            ProductVariation::saveMetaValue($productID, $metaKey, $metaValue, $parentProductID);

            return;
        }

        $metaValue = \apply_filters('itglx_wc1c_product_meta_' . $metaKey . '_value', $metaValue, $productID, $parentProductID);

        \update_post_meta($productID, $metaKey, $metaValue);
    }

    public static function getProductIdByMeta($value, $metaKey = '_id_1c')
    {
        global $wpdb;

        $product = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `meta`.`post_id` as `post_id`, `posts`.`post_type` as `post_type` FROM `{$wpdb->postmeta}` as `meta`
                INNER JOIN `{$wpdb->posts}` as `posts` ON (`meta`.`post_id` = `posts`.`ID`)
                WHERE `meta`.`meta_value` = %s AND `meta`.`meta_key` = %s",
                (string) $value,
                (string) $metaKey
            )
        );

        if (!isset($product->post_type)) {
            return null;
        }

        return $product->post_type === 'product' ? $product->post_id : null;
    }

    public static function removeProductImages($productID)
    {
        $thumbnailID = \get_post_meta($productID, '_thumbnail_id', true);

        if ($thumbnailID) {
            ImageEntity::remove($thumbnailID, $productID);
            \delete_post_meta($productID, '_thumbnail_id');
        }

        $images = \get_post_meta($productID, '_product_image_gallery', true);

        if (!empty($images)) {
            $images = explode(',', $images);

            foreach ($images as $image) {
                ImageEntity::remove($image, $productID);
            }

            \delete_post_meta($productID, '_product_image_gallery');

            Logger::log('(image) removed gallery for ID - ' . $productID, [\get_post_meta($productID, '_id_1c', true)]);
        }
    }

    /**
     * @param int $productId
     *
     * @see https://developer.wordpress.org/reference/functions/wp_parse_id_list/
     */
    public static function removeVariations($productId)
    {
        $variationIds = wp_parse_id_list(
            get_posts(
                [
                    'post_parent' => $productId,
                    'post_type' => 'product_variation',
                    'fields' => 'ids',
                    'post_status' => ['any', 'trash', 'auto-draft'],
                    'numberposts' => -1,
                ]
            )
        );

        if (!empty($variationIds)) {
            foreach ($variationIds as $variationId) {
                ProductVariation::remove($variationId, $productId);
            }
        }

        Logger::log('(product) removed variations for ID - ' . $productId, [\get_post_meta($productId, '_id_1c', true)]);
    }

    /**
     * @param int  $productId
     * @param bool $toTrash   Default: false.
     *
     * @return void
     *
     * @see https://developer.wordpress.org/reference/functions/wp_delete_post/
     * @see https://developer.wordpress.org/reference/functions/wp_trash_post/
     */
    public static function removeProduct($productId, $toTrash = false)
    {
        if (\get_post_type($productId) !== 'product') {
            return;
        }

        if (!$toTrash) {
            self::removeProductImages($productId);

            if (\get_post_meta($productId, '_is_set_variable', true)) {
                self::removeVariations($productId);
            }

            Logger::log('(product) removed, ID - ' . $productId, [\get_post_meta($productId, '_id_1c', true)]);
            \wp_delete_post($productId, true);

            return;
        }

        Logger::log('(product) to trash, ID - ' . $productId, [\get_post_meta($productId, '_id_1c', true)]);
        \wp_trash_post($productId);

        // clear hash control meta
        \update_post_meta($productId, '_md5', '');
    }

    /**
     * Getting status value by ID.
     *
     * @param int $productID
     *
     * @return null|string
     */
    public static function getStatus($productID)
    {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_status` FROM `{$wpdb->posts}` WHERE `ID` = %d",
                $productID
            )
        );
    }

    /**
     * Calling standard hooks when changing the stock status of a product / variation.
     *
     * @param int    $productID Product or Variation ID.
     * @param string $oldStatus Current status of the stock.
     * @param string $newStatus New status of the stock.
     *
     * @return void
     */
    private static function triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, $newStatus)
    {
        $product = \wc_get_product($productID);

        if (
            !$product
            || \is_wp_error($product)
            || !method_exists($product, 'is_type')
        ) {
            return;
        }

        if ($product->is_type('variation')) {
            \do_action('woocommerce_variation_set_stock_status', $productID, $newStatus, $product);

            return;
        }

        \do_action('woocommerce_product_set_stock_status', $productID, $newStatus, $product);
    }
}
