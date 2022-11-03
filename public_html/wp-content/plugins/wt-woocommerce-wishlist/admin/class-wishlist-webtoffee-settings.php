<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Wishlist_Webtoffee_Settings {
    
	public static $option_prefix = 'wishlist_webtoffee';

	public static function init() {
            
		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab' ,55);
		add_action( 'woocommerce_settings_tabs_settings_tab_wt_wishlist', __CLASS__ . '::settings_tab' );
		add_action( 'woocommerce_update_options_settings_tab_wt_wishlist', __CLASS__ . '::update_settings' );
	}

	public static function add_settings_tab( $settings_tabs ) {
		$settings_tabs['settings_tab_wt_wishlist'] = __( 'WebToffee Wishlist', 'wt-woocommerce-wishlist' );
		return $settings_tabs;
	}

	public static function settings_tab() {
		woocommerce_admin_fields( self::get_settings() );
	}

	public static function update_settings() {
		woocommerce_update_options( self::get_settings() );
	}

	public static function get_settings() {

		$settings = [
			'section_title' => array(
				'name'     => __( 'Settings', 'wt-woocommerce-wishlist' ),
				'type'     => 'title',
				'desc'     => '',
				'id'       => 'wc_settings_tab_wt_wishlist_section_title'
			),
			[
				'name'   => __( 'Activation', 'wt-woocommerce-wishlist' ),
				'desc' => __('Enable / Disable', 'wt-woocommerce-wishlist'),
				'id' => self::$option_prefix . '_activation',
				'default' => 'yes',
				'type' => 'checkbox',
			]
			,
			[
				'name'    => __( 'Wishlist page title', 'wt-woocommerce-wishlist' ),
				'desc' => __('Wishlist Title in My accounts', 'wt-woocommerce-wishlist'),
				'id' => self::$option_prefix . '_title',
				'default' => __('Wishlist', 'xa-woocommerce-subscription'),
				'type' => 'text',
				'desc_tip' => true,
			],
                        [
                                'name' => __('My Account tab text', 'wt-woocommerce-wishlist'),
                                'desc' => __('My Account Tab Text for wishlist listing page.', 'wt-woocommerce-wishlist'),
                                'id' => self::$option_prefix . '_wishlist_tab_text',
                                'css' => 'min-width:150px;',
                                'default' => __('My Wishlist', 'wt-woocommerce-wishlist'),
                                'type' => 'text',
                                'desc_tip' => true,
                        ],
			[
				'name'    => __( 'Wishlist content title', 'wt-woocommerce-wishlist' ),
				'desc' => __('Wishlist Text in My accounts', 'wt-woocommerce-wishlist'),
				'id' => self::$option_prefix . '_text',
				'default' => __('My Wishlist', 'wt-woocommerce-wishlist'),
				'type' => 'text',
				'desc_tip' => true,
			],
			[
				'name'    => __( 'Wishlist button position', 'wt-woocommerce-wishlist' ),
				'id' => self::$option_prefix . '_position',
				'default' => 'woocommerce_before_add_to_cart_button',
				'type' => 'select',
				'options' => [
					'woocommerce_before_add_to_cart_button' => __("Right of 'Add to Cart'", 'wt-woocommerce-wishlist'),
					'woocommerce_after_add_to_cart_button' => __("Left of 'Add to Cart'", 'wt-woocommerce-wishlist'),
				]
			],
			'section_end' => [
				'type' => 'sectionend',
				'id' => 'wc_settings_tab_wt_wishlist_section_end'
			]
		];


		return apply_filters( 'wc_settings_tab_wt_wishlist_settings', $settings );
	}

}

// Wishlist_Webtoffee_Settings::init();