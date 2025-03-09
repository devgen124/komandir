<?php


class WC_Payment_Token_SBP extends WC_Payment_Token
{
    /** @protected string Token Type String */
    protected $type = 'SBP';

    public function get_display_name($deprecated = '')
    {
        return __('Система быстрых платежей', 'woocommerce');
    }

    protected function get_hook_prefix()
    {
        return 'woocommerce_payment_token_yookassa_get_';
    }
}
