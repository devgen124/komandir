<?php

class WOOCCM_Admin_Links {

	protected static $_instance;

	function __construct() {
		add_filter( 'plugin_action_links_' . WOOCCM_PLUGIN_BASENAME, array( $this, 'add_action_links' ) );
	}

	public function add_action_links( $links ) {
		$links[] = '<a target="_blank" href="' . WOOCCM_DEMO_URL . '">' . esc_html__( 'Documentation', 'woocommerce-checkout-manager' ) . '</a>';
		$links[] = '<a target="_blank" href="' . WOOCCM_SUPPORT_URL . '">' . esc_html__( 'Support', 'woocommerce-checkout-manager' ) . '</a>';
		return $links;
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}

WOOCCM_Admin_Links::instance();
