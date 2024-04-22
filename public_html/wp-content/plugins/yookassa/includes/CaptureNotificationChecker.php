<?php

namespace Yoomoney\Includes;

use YooKassa\Model\Notification\AbstractNotification;

class CaptureNotificationChecker
{
    /** @var PaymentsTableModel */
    private $paymentsTableModel;

    public function __construct($paymentsTableModel)
    {
        $this->paymentsTableModel = $paymentsTableModel;
    }

    public function isHandled(AbstractNotification $notification)
    {
        $paymentId = $notification->getObject()->getId();

        return $this->paymentsTableModel->isPaymentPaid($paymentId);
    }
}