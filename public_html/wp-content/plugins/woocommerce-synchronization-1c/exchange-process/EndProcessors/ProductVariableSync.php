<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\EndProcessors;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Exceptions\ProgressException;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductVariableSync
{
    /**
     * @throws ProgressException
     */
    public static function process()
    {
        if (isset($_SESSION['IMPORT_1C']['variableProductsSync'])) {
            return;
        }

        if (!isset($_SESSION['IMPORT_1C']['hasVariation'])) {
            return;
        }

        Logger::log('variable product sync - start');

        if (!isset($_SESSION['IMPORT_1C']['numberOfSyncProducts'])) {
            $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = 0;
        }

        $numberOfSyncProducts = 0;

        foreach ($_SESSION['IMPORT_1C']['hasVariation'] as $productID => $_) {
            if (HeartBeat::limitIsExceeded()) {
                Logger::log('variable product sync - progress');

                throw new ProgressException("variable product sync {$numberOfSyncProducts}...");
            }

            ++$numberOfSyncProducts;

            if ($numberOfSyncProducts <= $_SESSION['IMPORT_1C']['numberOfSyncProducts']) {
                continue;
            }

            if (!get_post_meta($productID, '_is_set_variable', true)) {
                $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = $numberOfSyncProducts;
                continue;
            }

            \WC_Product_Variable::sync($productID);

            // clear cache by caching plugins
            Helper::clearCachePluginsPostCache($productID);

            Logger::log('(product) sync variable product - ' . $productID);

            $_SESSION['IMPORT_1C']['numberOfSyncProducts'] = $numberOfSyncProducts;
        }

        Logger::log('variable product sync - end');

        $_SESSION['IMPORT_1C']['variableProductsSync'] = true;
    }
}
