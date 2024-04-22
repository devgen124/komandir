<?php

use YooKassa\Model\PaymentMethodType;

if (!class_exists('YooKassaGateway')) {
    return;
}

class YooKassaGatewaySberLoan extends YooKassaGateway
{
    const MIN_AMOUNT = 3000;
    const MAX_AMOUNT = 600000;

    public $paymentMethod = PaymentMethodType::SBER_LOAN;

    public $id = 'yookassa_sber_loan';

    public function __construct()
    {
        parent::__construct();

        $this->icon = YooKassa::$pluginUrl.'assets/images/sberbank.png';

        $this->method_title       = __('«Покупки в кредит» от СберБанка', 'yookassa');
        $this->method_description = __('Покупки в рассрочку и кредит от СберБанка', 'yookassa');

        $this->defaultTitle       = __('«Покупки в кредит» от СберБанка', 'yookassa');
        $this->defaultDescription = __('Покупки в рассрочку и кредит от СберБанка', 'yookassa');

        $this->title              = $this->getTitle();
        $this->description        = $this->getDescription();
    }

    public function updateEnabledMethodOption()
    {
        $this->update_option('enabled', 'no');
    }

    /**
     * @param float $amount
     * @return bool
     */
    public static function isCorrectAmountForSberLoan($amount)
    {
        return $amount >= YooKassaGatewaySberLoan::MIN_AMOUNT && $amount <= YooKassaGatewaySberLoan::MAX_AMOUNT;
    }
}
