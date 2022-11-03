<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductImages;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Exceptions\ProgressException;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ZipHelper;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXml;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXml31;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeImport
{
    /**
     * @throws \Exception
     */
    public static function process()
    {
        $baseName = basename(RootProcessStarter::getCurrentExchangeFileAbsPath());

        if (!isset($_SESSION['IMPORT_1C'])) {
            $_SESSION['IMPORT_1C'] = [];
        }

        if (
            isset($_SESSION['IMPORT_1C']['zip_file'])
            && file_exists($_SESSION['IMPORT_1C']['zip_file'])
        ) {
            ZipHelper::extract($_SESSION['IMPORT_1C']['zip_file']);

            $archiveFileName = basename($_SESSION['IMPORT_1C']['zip_file']);

            unset($_SESSION['IMPORT_1C']['zip_file']);

            throw new ProgressException("[zip] extracted - {$archiveFileName}");
        }

        /**
         * Filters the sign of ignoring file processing.
         *
         * @since 1.80.2
         *
         * @param bool   $ignoreProcessing Default: false.
         * @param string $filename         File basename.
         */
        $ignoreProcessing = \apply_filters('itglx_wc1c_ignore_catalog_file_processing', false, $baseName);

        if ($ignoreProcessing) {
            Logger::log('ignore file processing by `itglx_wc1c_ignore_catalog_file_processing', [$baseName]);

            self::success();

            return;
        }

        // check requested parse file exists
        if (!file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath())) {
            throw new \Exception(esc_html('File not exists! - ' . $baseName));
        }

        /**
         * Load required image working functions.
         *
         * @psalm-suppress MissingFile
         */
        include_once ABSPATH . 'wp-admin/includes/image.php';
        /** @psalm-suppress MissingFile */
        include_once ABSPATH . 'wp-admin/includes/file.php';
        /** @psalm-suppress MissingFile */
        include_once ABSPATH . 'wp-admin/includes/media.php';
        /** @psalm-suppress MissingFile */
        include_once ABSPATH . 'wp-includes/pluggable.php';

        // product image progress check
        if (ProductImages::hasInProgress()) {
            ProductImages::continueProgress();
        }

        // get version scheme
        $reader = new \XMLReader();
        $reader->open(RootProcessStarter::getCurrentExchangeFileAbsPath());
        $reader->read();
        $_SESSION['xmlVersion'] = (float) $reader->getAttribute('ВерсияСхемы');

        Logger::log(
            'XML, schema version - ' . $reader->getAttribute('ВерсияСхемы')
            . ', generation date - ' . $reader->getAttribute('ДатаФормирования')
        );

        // resolve parser base version
        if ($_SESSION['xmlVersion'] < 3) {
            $parser = new ParserXml();
        } else {
            $parser = new ParserXml31();
        }

        $parser->parse($reader);

        // clear session
        unset($_SESSION['IMPORT_1C']);

        if (
            strpos($baseName, 'offers') !== false
            || strpos($baseName, 'rests') !== false // scheme 3.1
        ) {
            $_SESSION['IMPORT_1C_PROCESS'] = [];
        }

        self::success();
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    private static function success()
    {
        $baseName = basename(RootProcessStarter::getCurrentExchangeFileAbsPath());

        SuccessResponse::getInstance()->send("import file {$baseName} completed");

        /**
         * Hook makes it possible to perform some of your actions when the file is processing processing.
         *
         * @since 1.84.9
         *
         * @param string $baseName The name of the file that has been processed
         */
        \do_action('itglx_wc1c_exchange_catalog_import_file_processing_completed', $baseName);
    }
}
