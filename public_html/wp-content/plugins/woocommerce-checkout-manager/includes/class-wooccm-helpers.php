<?php

class WOOCCM_Field_Helpers {

	public static function get_form_action() {

		if ( isset( $_REQUEST['action'] ) && 'edit_address' === $_REQUEST['action'] ) {
			return 'account';
		}

		if ( isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) {
			return 'save';
		}

		if ( isset( $_REQUEST['post_data'] ) && isset( $_REQUEST['wc-ajax'] ) && $_REQUEST['wc-ajax'] == 'update_order_review' ) {
			return 'update';
		}
	}

}
