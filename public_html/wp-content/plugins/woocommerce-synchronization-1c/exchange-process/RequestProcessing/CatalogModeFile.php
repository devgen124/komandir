<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ZipHelper;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;

class CatalogModeFile
{
    public static function process()
    {
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

        if (ZipHelper::isUseZip()) {
            $_SESSION['IMPORT_1C']['zip_file'] = RootProcessStarter::getCurrentExchangeFileAbsPath();
        }

        SuccessResponse::getInstance()->send();
    }
}
