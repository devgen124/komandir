<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\GlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Groups;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\NomenclatureCategories;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\PriceTypes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Stocks;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Tags;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\Units;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Exceptions\ProgressException;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

abstract class Parser
{
    protected $rate = 1;

    public function __construct()
    {
        // https://developer.wordpress.org/reference/functions/wp_defer_term_counting/
        // disable allows to make the exchange much faster, since a large number of resources are saved for
        // each quantity recount, and the final recount is performed through the cron plugin task
        \wp_defer_term_counting(true);

        if (class_exists('\\WPSEO_Sitemaps_Cache')) {
            \add_filter('wpseo_enable_xml_sitemap_transient_caching', '__return_false');
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'])) {
            $_SESSION['IMPORT_1C_PROCESS']['allCurrentProducts'] = [];
        }

        $this->setAuthUser();

        Groups::prepare();
    }

    /**
     * @param \XMLReader $reader
     *
     * @return void
     */
    public function parse(\XMLReader $reader)
    {
    }

    /**
     * @param \XMLReader $reader
     * @param string     $node   Node name.
     *
     * @return bool
     */
    protected function isEmptyNode(\XMLReader $reader, $node)
    {
        $resolveResult = str_replace(
            [' xmlns="' . $reader->namespaceURI . '"', ' '],
            '',
            $reader->readOuterXml()
        );

        if ($resolveResult === '<' . $node . '/>') {
            return true;
        }

        return false;
    }

    /**
     * @param \XMLReader $reader
     *
     * @throws ProgressException
     *
     * @return void
     */
    protected function parseClassificator(\XMLReader $reader)
    {
        if ($reader->name !== 'Классификатор') {
            return;
        }

        if (Groups::isParsed() && Tags::isParsed() && GlobalProductAttributes::isParsed()) {
            return;
        }

        $reader->read();

        while (
            $reader->read()
            && !($reader->name === 'Классификатор' && $reader->nodeType === \XMLReader::END_ELEMENT)
        ) {
            // resolve attributes
            if (
                !GlobalProductAttributes::isParsed()
                && $reader->name === 'Свойства'
                && $reader->nodeType === \XMLReader::ELEMENT
                && !$this->isEmptyNode($reader, 'Свойства')
            ) {
                GlobalProductAttributes::process($reader);
            }

            // resolve groups
            if (!Groups::isParsed() && !Groups::isDisabled() && Groups::isGroupNode($reader)) {
                Groups::process($reader);
            }

            // resolve tags
            if (!Tags::isParsed() && Tags::isTagNode($reader)) {
                Tags::process($reader);
            }

            // resolve price types
            if (PriceTypes::isPriceTypesNode($reader)) {
                PriceTypes::process($reader);
            }

            // resolve units
            if (Units::isUnitsNode($reader) && !$this->isEmptyNode($reader, 'ЕдиницыИзмерения')) {
                Units::process($reader);
            }

            // resolve stocks
            if (!Stocks::isParsed() && Stocks::isStocksNode($reader)) {
                Stocks::process($reader);
            }

            // resolve `Категории -> Свойства`
            if (NomenclatureCategories::isNomenclatureCategoriesNode($reader)) {
                NomenclatureCategories::process($reader);
            }
        }

        Groups::setParsed();
        Tags::setParsed();
        GlobalProductAttributes::setParsed();

        delete_option('product_cat_children');
        wp_cache_flush();
    }

    private function setAuthUser()
    {
        if (\is_user_logged_in()) {
            return;
        }

        $userID = SettingsHelper::get('exchange_post_author', 0);

        if (!$userID) {
            if ($users = \get_users(['role' => 'administrator'])) {
                $userID = array_shift($users)->ID;
            } else {
                $userID = 1;
            }
        }

        if (!(int) $userID) {
            return;
        }

        $user = \get_userdata((int) $userID);

        if (!$user || \is_wp_error($user)) {
            return;
        }

        \wp_set_current_user($user->ID);

        $this->disableCleanHtmlInCategoryDescription();
    }

    private function disableCleanHtmlInCategoryDescription()
    {
        if (current_user_can('unfiltered_html')) {
            remove_filter('pre_term_description', 'wp_filter_kses');
        }

        remove_filter('term_description', 'wp_kses_data');
    }
}
