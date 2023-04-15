<?php

/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_edit_account_form' ); ?>

<div class="woocommerce-edit-account-wrapper">

	<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>

		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<label class="edit-account-label">
			<input type="text" name="account_first_name" autocomplete="given-name"
				value="<?php echo esc_attr( $user->first_name ); ?>"
				placeholder="<?php esc_html_e( 'First name', 'woocommerce' ); ?>" />
		</label>
		<label class="edit-account-label">
			<input type="text" name="account_last_name" autocomplete="family-name"
				value="<?php echo esc_attr( $user->last_name ); ?>"
				placeholder="<?php esc_html_e( 'Last name', 'woocommerce' ); ?>" />
		</label>
		<label class="edit-account-label">
			<input type="text" name="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>"
				placeholder="<?php esc_html_e( 'Display name', 'woocommerce' ); ?>" />
		</label>
		<label class="edit-account-label">
			<input type="email" name="account_email" id="account_email" autocomplete="email"
				value="<?php echo esc_attr( $user->user_email ); ?>"
				placeholder="<?php esc_html_e( 'Email address', 'woocommerce' ); ?>" />
		</label>

		<fieldset class="edit-account-fieldset">
			<legend class="edit-account-legend">Телефон</legend>
			<label class="edit-account-label">
				<input disabled type="tel" name="account_phone" id="account_phone"
					value="<?php echo WC()->customer->get_billing_phone(); ?>" placeholder="Телефон" />
			</label>
			<a href="#change-phone-number-popup" class="button edit-account-button change-phone-number-link">Изменить
				номер телефона</a>
		</fieldset>

		<fieldset class="edit-account-fieldset">
			<legend class="edit-account-legend">
				<?php esc_html_e( 'Password change', 'woocommerce' ); ?>
			</legend>
			<label class="edit-account-label">
				<input type="password" name="password_current" autocomplete="off" placeholder="Действующий пароль" />
				<button class="show-pass" aria-label="Показать пароль"></button>
			</label>
			<label class="edit-account-label">
				<input type="password" name="password_1" autocomplete="off" placeholder="Новый пароль" />
				<button class="show-pass" aria-label="Показать пароль"></button>
			</label>
			<label class="edit-account-label">
				<input type="password" name="password_2" autocomplete="off"
					placeholder="<?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?>" />
				<button class="show-pass" aria-label="Показать пароль"></button>
			</label>
		</fieldset>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>


		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit"
			class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?> edit-account-button"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
		<input type="hidden" name="action" value="save_account_details" />

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_edit_account_form' ); ?>

</div>

<div id="change-phone-number-popup" class="custom-popup mfp-hide"></div>

<template id="change-phone-number-send-code">
	<div class="custom-popup-section change-phone-number-send-code">
		<h2>Новый номер телефона</h2>
		<form class="form-send-code" method="post">
			<label class="custom-popup-form-label">
				<input type="tel" placeholder="Телефон" name="phone" />
				<button class="input-tip" aria-label="Подсказка"></button>
			</label>
			<?php wp_nonce_field( 'komandir-send-code', 'komandir-send-code-nonce' ); ?>
			<button type="submit" class="custom-popup-submit form-send-code-submit">Получить код</button>
		</form>
	</div>
</template>

<template id="change-phone-number-sms">
	<div class="custom-popup-section change-phone-number-sms">
		<h2>Введите SMS-код</h2>
		<form class="form-sms" method="post">
			<label class="custom-popup-form-label">
				<input type="text" placeholder="Код из SMS" name="sms" maxlength="4" autocomplete="off" />
			</label>
			<?php wp_nonce_field( 'komandir-sms', 'komandir-sms-nonce' ); ?>
			<button class="custom-popup-submit form-sms-submit"></button>
		</form>
		<div class="counter-block"></div>
	</div>
</template>
