<?php

use Itgalaxy\Wc\Exchange1c\Includes\Filters\Plugins\WooCommerceStoreExporter;
use Itgalaxy\Wc\Exchange1c\Includes\Filters\WcCartItemPriceShowSalePrice;
use Itgalaxy\Wc\Exchange1c\Includes\Filters\WcGetPriceHtmlShowPriceListDetailProductPage;

if (!defined('ABSPATH')) {
    return;
}

// bind filters
WcCartItemPriceShowSalePrice::getInstance();
WcGetPriceHtmlShowPriceListDetailProductPage::getInstance();

// other plugins
WooCommerceStoreExporter::getInstance();
