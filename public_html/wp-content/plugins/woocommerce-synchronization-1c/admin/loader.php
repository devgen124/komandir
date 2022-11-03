<?php

use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ClearLogsAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\ClearTempAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\LastRequestResponseAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\LogsCountAndSizeAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\AjaxActions\TempCountAndSizeAjaxAction;
use Itgalaxy\Wc\Exchange1c\Admin\MetaBoxes\MetaBoxShopOrder;
use Itgalaxy\Wc\Exchange1c\Admin\Other\AdminNoticeIfHasTrashedProductWithGuid;
use Itgalaxy\Wc\Exchange1c\Admin\Other\AdminNoticeIfNotVerified;
use Itgalaxy\Wc\Exchange1c\Admin\PluginActionLinksFilter;
use Itgalaxy\Wc\Exchange1c\Admin\ProductAttributesPage1cIdInfo;
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveLogs;
use Itgalaxy\Wc\Exchange1c\Admin\RequestProcessing\GetInArchiveTemp;
use Itgalaxy\Wc\Exchange1c\Admin\SettingsPage;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProduct;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProductAttribute;
use Itgalaxy\Wc\Exchange1c\Admin\TableColumns\TableColumnProductCat;

if (!defined('ABSPATH')) {
    return;
}

// do not continue initialization if not admin panel
if (!is_admin()) {
    return;
}

SettingsPage::getInstance();
PluginActionLinksFilter::getInstance();
ProductAttributesPage1cIdInfo::getInstance();

// table columns
TableColumnProductAttribute::getInstance();
TableColumnProductCat::getInstance();
TableColumnProduct::getInstance();

// metaboxes
MetaBoxShopOrder::getInstance();

// product
\Itgalaxy\Wc\Exchange1c\Admin\Product\GuidProductDataTab::getInstance();

// product variation
\Itgalaxy\Wc\Exchange1c\Admin\ProductVariation\HeaderGuidInfo::getInstance();
\Itgalaxy\Wc\Exchange1c\Admin\ProductVariation\GuidField::getInstance();

// bind ajax actions
ClearLogsAjaxAction::getInstance();
ClearTempAjaxAction::getInstance();
LastRequestResponseAjaxAction::getInstance();
LogsCountAndSizeAjaxAction::getInstance();
TempCountAndSizeAjaxAction::getInstance();

// bind admin request handlers
GetInArchiveLogs::getInstance();
GetInArchiveTemp::getInstance();

// bind other admin actions
AdminNoticeIfHasTrashedProductWithGuid::getInstance();
AdminNoticeIfNotVerified::getInstance();
