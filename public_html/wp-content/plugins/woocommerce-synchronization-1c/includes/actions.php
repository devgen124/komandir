<?php

use Itgalaxy\Wc\Exchange1c\Includes\Actions\DeleteAttachment;
use Itgalaxy\Wc\Exchange1c\Includes\Actions\PreDeleteTerm;
use Itgalaxy\Wc\Exchange1c\Includes\Actions\WcBeforeCalculateTotalsSetCartItemPrices;
use Itgalaxy\Wc\Exchange1c\Includes\Actions\WooCommerceAttributeDeleted;

if (!defined('ABSPATH')) {
    return;
}

// bind actions
DeleteAttachment::getInstance();
PreDeleteTerm::getInstance();
WcBeforeCalculateTotalsSetCartItemPrices::getInstance();
WooCommerceAttributeDeleted::getInstance();
