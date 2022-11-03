<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\EndProcessors;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Exceptions\ProgressException;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class DataDeletingOnFullExchange
{
    /**
     * @param bool $heartbeat
     *
     * @throws ProgressException
     *
     * @return void
     */
    public static function products($heartbeat = true)
    {
        global $wpdb;

        if (isset($_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove'])) {
            return;
        }

        if (SettingsHelper::isEmpty('remove_missing_full_unload_products')) {
            $_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove'] = true;

            return;
        }

        $all1cProducts = get_option('all1cProducts', []);

        if (empty($all1cProducts)) {
            $_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove'] = true;

            return;
        }

        Logger::log('remove products on full exchange - start - ' . count($all1cProducts));

        $productIds = $wpdb->get_col(
            "SELECT `post_id` FROM `{$wpdb->postmeta}` as `meta`
            INNER JOIN `{$wpdb->posts}` as `posts` ON (`meta`.`post_id` = `posts`.`ID`)
            WHERE `meta`.`meta_key` = '_id_1c' AND `meta`.`meta_value` != '' AND `posts`.`post_type` = 'product' GROUP BY `meta`.`post_id`"
        );

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['countProductRemove'])) {
            $_SESSION['IMPORT_1C_PROCESS']['countProductRemove'] = 0;
        }

        $kol = 0;
        $removeToTrash = SettingsHelper::get('remove_missing_full_unload_products') !== 'completely';

        foreach ($productIds as $productID) {
            if ($heartbeat && HeartBeat::limitIsExceeded()) {
                Logger::log('remove products on full exchange - progress');

                throw new ProgressException('remove products on full exchange...');
            }

            ++$kol;

            if ($kol < $_SESSION['IMPORT_1C_PROCESS']['countProductRemove']) {
                continue;
            }

            if (!in_array($productID, $all1cProducts)) {
                Product::removeProduct($productID, $removeToTrash);
                --$kol;
            }

            $_SESSION['IMPORT_1C_PROCESS']['countProductRemove'] = $kol;
        }

        $_SESSION['IMPORT_1C_PROCESS']['missingProductsIsRemove'] = true;

        Logger::log('remove products on full exchange - end');
    }

    /**
     * @param bool $heartbeat
     *
     * @throws ProgressException
     *
     * @return void
     */
    public static function categories($heartbeat = true)
    {
        if (isset($_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'])) {
            return;
        }

        if (
            SettingsHelper::isEmpty('remove_missing_full_unload_product_categories')
            || self::categoryProcessingSkipIsEnabled()
        ) {
            $_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'] = true;

            return;
        }

        $currentAll1cGroup = get_option('currentAll1cGroup', []);

        if (empty($currentAll1cGroup)) {
            $_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'] = true;

            return;
        }

        Logger::log('remove categories on full exchange - start - ' . count($currentAll1cGroup));

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['countTermRemove'])) {
            $_SESSION['IMPORT_1C_PROCESS']['countTermRemove'] = 0;
        }

        $kol = 0;

        foreach (Term::getProductCatIDs() as $id => $category) {
            if ($heartbeat && HeartBeat::limitIsExceeded()) {
                Logger::log('remove categories on full exchange - progress');

                throw new ProgressException('remove categories on full exchange...');
            }

            ++$kol;

            if ($kol < $_SESSION['IMPORT_1C_PROCESS']['countTermRemove']) {
                continue;
            }

            if (\get_term($category, 'product_cat') && !in_array($id, $currentAll1cGroup)) {
                \wp_delete_term($category, 'product_cat');

                --$kol;
            }

            $_SESSION['IMPORT_1C_PROCESS']['countTermRemove'] = $kol;
        }

        \delete_option('product_cat_children');
        \wp_cache_flush();

        $_SESSION['IMPORT_1C_PROCESS']['missingTermsIsRemove'] = true;

        Logger::log('remove categories on full exchange - end');
    }

    /**
     * @return void
     */
    public static function clearCache()
    {
        \update_option('all1cProducts', []);
        \update_option('currentAll1cGroup', []);
    }

    /**
     * Checking whether the deleting is enabled in the settings.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return !SettingsHelper::isEmpty('remove_missing_full_unload_products')
            || !SettingsHelper::isEmpty('remove_missing_full_unload_product_categories');
    }

    /**
     * @return bool
     */
    private static function categoryProcessingSkipIsEnabled()
    {
        return !SettingsHelper::isEmpty('skip_categories');
    }
}
