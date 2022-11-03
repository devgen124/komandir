<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXmlOrders;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;
use Itgalaxy\Wc\Exchange1c\Includes\SettingsHelper;

class SaleModeFile
{
    public static function process()
    {
        // if exchange order not enabled
        if (
            SettingsHelper::isEmpty('handle_get_order_status_change')
            && SettingsHelper::isEmpty('handle_get_order_product_set_change')
        ) {
            if (SettingsHelper::isEmpty('handle_get_order_status_change')) {
                Logger::log('handle_get_order_status_change not enabled');
            }

            if (SettingsHelper::isEmpty('handle_get_order_product_set_change')) {
                Logger::log('handle_get_order_product_set_change not enabled');
            }

            SuccessResponse::getInstance()->send();

            return;
        }

        $data = file_get_contents('php://input');

        if ($data === false) {
            throw new \Exception(esc_html__('Error reading http stream!', 'itgalaxy-woocommerce-1c'));
        }

        if (
            !is_writable(dirname(RootProcessStarter::getCurrentExchangeFileAbsPath()))
            || (
                file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath())
                && !is_writable(RootProcessStarter::getCurrentExchangeFileAbsPath())
            )
        ) {
            throw new \Exception(
                esc_html__('The directory / file is not writable', 'itgalaxy-woocommerce-1c')
                . ': '
                . basename(RootProcessStarter::getCurrentExchangeFileAbsPath())
            );
        }

        $fp = fopen(RootProcessStarter::getCurrentExchangeFileAbsPath(), 'ab');
        $result = fwrite($fp, $data);

        if ($result !== mb_strlen($data, 'latin1')) {
            throw new \Exception(esc_html__('Error writing file!', 'itgalaxy-woocommerce-1c'));
        }

        // old modules compatible, processing without import request
        if (empty($_SESSION['version']) && self::isValidXml()) {
            self::processingFile();
        }

        SuccessResponse::getInstance()->send();
    }

    public static function processingFile()
    {
        // check requested parse file exists
        if (!file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath())) {
            throw new \Exception(
                esc_html('File not exists! - ' . basename(RootProcessStarter::getCurrentExchangeFileAbsPath()))
            );
        }

        $parserXml = new ParserXmlOrders();
        $parserXml->parse(RootProcessStarter::getCurrentExchangeFileAbsPath());
    }

    /**
     * @throws \Exception
     *
     * @return bool
     *
     * @see https://www.php.net/manual/en/function.libxml-use-internal-errors.php
     */
    private static function isValidXml()
    {
        if (!function_exists('libxml_use_internal_errors')) {
            Logger::log('function `libxml_use_internal_errors` not exists');

            return true;
        }

        $useErrors = \libxml_use_internal_errors(true);
        \simplexml_load_file(RootProcessStarter::getCurrentExchangeFileAbsPath());

        $errors = \libxml_get_errors();

        \libxml_clear_errors();
        \libxml_use_internal_errors($useErrors);

        // if no has errors when load xml
        if (empty($errors)) {
            return true;
        }

        $messages = [];

        foreach ($errors as $error) {
            $messages[] = $error->message;
        }

        Logger::log('xml has errors - ' . basename(RootProcessStarter::getCurrentExchangeFileAbsPath()), $messages);

        return false;
    }
}
