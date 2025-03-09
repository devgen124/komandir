<?php

require_once plugin_dir_path(__FILE__) . 'YooKassaInstaller.php';

/**
 * Fired during plugin deactivation
 */
class YooKassaDeactivator extends YooKassaInstaller
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        YooKassaLogger::sendHeka(array('module.uninstall.init'));
        try {
            delete_option('woocommerce_yookassa_qiwi_settings');
            delete_option('woocommerce_yookassa_bank_card_settings');
            delete_option('woocommerce_yookassa_epl_settings');
            delete_option('woocommerce_yookassa_sberbank_settings');
            delete_option('woocommerce_yookassa_wallet_settings');
            delete_option('woocommerce_yookassa_cash_settings');
            delete_option('woocommerce_yookassa_webmoney_settings');
            delete_option('woocommerce_yookassa_alfabank_settings');
            delete_option('woocommerce_yookassa_installments_settings');

            YooKassaLogger::sendHeka(array('module.uninstall.success'));
            self::log('info', 'YooKassa plugin deactivate!');
        } catch (Exception $ex) {
            $message = 'YooKassa plugin deactivate error: ' . $ex->getMessage();
            self::log('error', $message);
            YooKassaLogger::sendAlertLog($message, array('exception' => $ex), array('module.uninstall.fail'));
        }
    }

}
