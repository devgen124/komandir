<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Compability with WooCommerce PayPal Payments
 */
add_filter( 'woocommerce_paypal_payments_early_wc_checkout_validation_enabled', '__return_false' );

/**
 * Compatibility with woocommerce-checkout-manager-pro 6.x
 */
function WOOCCM() {
	return Quadlayers\WOOCCM\WOOCCM();
}
