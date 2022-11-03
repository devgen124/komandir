<?php

namespace Itgalaxy\Wc\Exchange1c\Includes;

use Itgalaxy\PluginCommon\DependencyPluginChecker;
use Itgalaxy\PluginCommon\MainHelperLoader;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\CreateProductInDraft;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindAttribute;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindAttributeValueTermId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindProductCatId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindProductId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FindProductTagId;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\FixedSeparatedIdCharacteristicInOfferXmlData;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\IgnoreCatalogFileProcessing;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\MaxImageSize;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\OfferIsRemoved;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\ProductIsRemoved;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters\SkipProductByXml;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;

class Bootstrap
{
    const PLUGIN_ID = '24768513';
    const PLUGIN_VERSION = '1.108.0';

    const OPTIONS_KEY = 'wc-itgalaxy-1c-exchange-settings';
    const OPTION_INFO_KEY = 'wc-itgalaxy-1c-exchange-additional-info';
    const OPTION_UNITS_KEY = 'itglx_wc1c_nomenclature_units';
    const PURCHASE_CODE_OPTIONS_KEY = 'wc-itgalaxy-1c-exchange-purchase-code';
    const CRON = 'wc-itgalaxy-1c-exchange-cron';

    const DEPENDENCY_PLUGIN_LIST = ['woocommerce/woocommerce.php'];

    public static $plugin = '';

    /**
     * @var string Absolute path (with a trailing slash) to the plugin directory.
     */
    public static $pluginDir;

    /**
     * @var string URL to the plugin directory (with a trailing slash).
     */
    public static $pluginUrl;

    /**
     * @var MainHelperLoader
     */
    public static $common;

    private static $instance = false;

    protected function __construct($file)
    {
        self::$plugin = $file;
        self::$pluginDir = \plugin_dir_path($file);
        self::$pluginUrl = \plugin_dir_url($file);
        self::$common = new MainHelperLoader($this, false);

        SettingsHelper::init();
        self::pluginLifeCycleActionsRegister();

        if (!DependencyPluginChecker::isActivated(self::DEPENDENCY_PLUGIN_LIST)) {
            DependencyPluginChecker::showRequirementPluginsNotice(
                esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
                self::DEPENDENCY_PLUGIN_LIST
            );

            return;
        }

        if (
            get_option('ITGALAXY_WC_1C_PLUGIN_VERSION') !== false
            && get_option('ITGALAXY_WC_1C_PLUGIN_VERSION') !== self::PLUGIN_VERSION
        ) {
            self::convertOldSettings();
        }

        // bind cron actions
        Cron::getInstance();

        // processing request from the accounting system
        // https://developer.wordpress.org/reference/hooks/init/
        add_action('init', function () {
            // check is exchange request
            if (!Helper::isExchangeRequest()) {
                return;
            }

            // do not cache requests for `LiteSpeed Cache` - https://wordpress.org/plugins/litespeed-cache/
            \do_action('litespeed_disable_all', '1C exchange request');

            // bind filters
            CreateProductInDraft::getInstance();
            FindAttribute::getInstance();
            FindAttributeValueTermId::getInstance();
            FindProductCatId::getInstance();
            FindProductTagId::getInstance();
            FindProductId::getInstance();
            ProductIsRemoved::getInstance();
            OfferIsRemoved::getInstance();
            SkipProductByXml::getInstance();
            FixedSeparatedIdCharacteristicInOfferXmlData::getInstance();
            MaxImageSize::getInstance();
            IgnoreCatalogFileProcessing::getInstance();

            // exchange start
            RootProcessStarter::getInstance();
        }, PHP_INT_MAX);
    }

    public static function getInstance($file)
    {
        if (!self::$instance) {
            self::$instance = new self($file);
        }

        return self::$instance;
    }

    public static function pluginLifeCycleActionsRegister()
    {
        /**
         * @see https://developer.wordpress.org/reference/functions/register_activation_hook/
         */
        \register_activation_hook(self::$plugin, [self::class, 'pluginActivation']);

        /**
         * @see https://developer.wordpress.org/reference/functions/register_deactivation_hook/
         */
        \register_deactivation_hook(self::$plugin, [self::class, 'pluginDeactivation']);
    }

    public static function convertOldSettings()
    {
        $settings = get_option(self::OPTIONS_KEY, []);

        if (empty($settings)) {
            \update_option('ITGALAXY_WC_1C_PLUGIN_VERSION', self::PLUGIN_VERSION);

            return;
        }

        // to 1.106.0
        if (!empty($settings['remove_missing_products'])) {
            $settings['remove_missing_full_unload_product_categories'] = 1;
            $settings['remove_missing_full_unload_products'] = 'completely';

            unset($settings['remove_missing_products']);
        }

        \update_option(self::OPTIONS_KEY, $settings);
        \update_option('ITGALAXY_WC_1C_PLUGIN_VERSION', self::PLUGIN_VERSION);
    }

    public static function pluginActivation()
    {
        self::$common->requester->call('plugin_activate');

        DependencyPluginChecker::activateHelper(
            self::$plugin,
            self::DEPENDENCY_PLUGIN_LIST,
            esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c')
        );

        self::addWcAttributesTableColumn();
        self::addMetaValueIndexes();
        self::copyRootEntryImportFile();

        // add option row in table for plugin settings
        if (get_option(self::OPTIONS_KEY) === false) {
            add_option(
                self::OPTIONS_KEY,
                [
                    'enable_exchange' => 1,
                    'use_file_zip' => 1,
                    'send_orders_last_success_export' => str_replace(' ', 'T', date_i18n('Y-m-d H:i')),
                    'log_days' => 5,
                    'enable_logs_protocol' => 1,
                ],
                '',
                'no'
            );
        }

        if (get_option(self::PURCHASE_CODE_OPTIONS_KEY) === false) {
            add_option(self::PURCHASE_CODE_OPTIONS_KEY, '', '', 'no');
        }

        if (get_option(self::OPTION_INFO_KEY) === false) {
            add_option(self::OPTION_INFO_KEY, [], '', 'no');
        }

        if (get_option('all_prices_types') === false) {
            add_option('all_prices_types', [], '', 'no');
        }

        if (get_option(self::OPTION_UNITS_KEY) === false) {
            add_option(self::OPTION_UNITS_KEY, [], '', 'no');
        }

        if (get_option('itglx_wc1c_nomenclature_categories') === false) {
            add_option('itglx_wc1c_nomenclature_categories', [], '', 'no');
        }

        if (get_option('currentAll1cGroup') === false) {
            add_option('currentAll1cGroup', [], '', 'no');
        }

        if (get_option('all1cProducts') === false) {
            add_option('all1cProducts', [], '', 'no');
        }

        if (get_option('ITGALAXY_WC_1C_PLUGIN_VERSION') === false) {
            add_option('ITGALAXY_WC_1C_PLUGIN_VERSION', self::PLUGIN_VERSION, '', 'no');
        }
    }

    public static function pluginDeactivation()
    {
        self::$common->requester->call('plugin_deactivate');
        \wp_clear_scheduled_hook(self::CRON);
    }

    public static function pluginUninstall()
    {
        self::$common->requester->call('plugin_uninstall');
    }

    private static function copyRootEntryImportFile()
    {
        if (!file_exists(self::$pluginDir . 'import-1c.php')) {
            return;
        }

        if (file_exists(ABSPATH . 'import-1c.php')) {
            return;
        }

        copy(self::$pluginDir . 'import-1c.php', ABSPATH . 'import-1c.php');
    }

    private static function addWcAttributesTableColumn()
    {
        global $wpdb;

        $dbName = DB_NAME;

        // phpcs:disable
        $columnExists = $wpdb->query(
            "SELECT * FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = '{$dbName}'
                  AND TABLE_NAME = '{$wpdb->prefix}woocommerce_attribute_taxonomies'
                  AND COLUMN_NAME = 'id_1c'"
        );

        if (!$columnExists) {
            $wpdb->query(
                "ALTER TABLE {$wpdb->prefix}woocommerce_attribute_taxonomies
                ADD id_1c varchar(191) NOT NULL"
            );
        }
        // phpcs:enable
    }

    private static function addMetaValueIndexes()
    {
        global $wpdb;

        // phpcs:disable
        if (
            !$wpdb->query(
                "SHOW KEYS FROM `{$wpdb->postmeta}` WHERE Key_name='meta_value-1c'"
            )
        ) {
            $wpdb->query(
                "ALTER TABLE `{$wpdb->postmeta}`" .
                " ADD INDEX `meta_value-1c` (`meta_value` (191)) COMMENT ''"
            );
        }

        if (
            !$wpdb->query(
                "SHOW KEYS FROM `{$wpdb->termmeta}` WHERE Key_name='meta_value-1c'"
            )
        ) {
            $wpdb->query(
                "ALTER TABLE `{$wpdb->termmeta}`" .
                " ADD INDEX `meta_value-1c` (`meta_value` (191)) COMMENT ''"
            );
        }
        // phpcs:enable
    }
}
