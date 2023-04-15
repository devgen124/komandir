<?php

// this is just to prevent the user log in automatically after register

function wc_registration_redirect( $redirect_to ) {

	wp_logout();

	wp_redirect( '/sign-in/?q=' );

	exit;
}

// when user login, we will check whether this guy email is verify

function wp_authenticate_user( $userdata ) {

	$isActivated = get_user_meta( $userdata->ID, 'is_activated', true );

	if ( ! $isActivated ) {

		$userdata = new WP_Error(
			'inkfool_confirmation_error',

			__( '<strong>ERROR:</strong> Your account has to be activated before you can login. You can resend by clicking <a href="/sign-in/?u=' . $userdata->ID . '">here</a>', 'inkfool' )

		);
	}

	return $userdata;
}

// when a user register we need to send them an email to verify their account

function my_user_register( $user_id ) {

	// get user data

	$user_info = get_userdata( $user_id );

	// create md5 code to verify later
	$code = md5( time() );

	// make it into a code to send it to user via email

	$string = array( 'id' => $user_id, 'code' => $code );

	// create the activation code and activation status

	update_user_meta( $user_id, 'is_activated', 0 );

	update_user_meta( $user_id, 'activationcode', $code );
	; // create the url

	$url = get_site_url() . '/sign-in/?p=' . base64_encode( serialize( $string ) );

	// basically we will edit here to make this nicer

	$html = 'Please click the following links <br/><br/> <a href="' . $url . '">' . $url . '</a>';

	// send an email out to user

	wc_mail( $user_info->user_email, __( 'Please activate your account' ), $html );
}

// we need this to handle all the getty hacks i made

function my_init() {

	// check whether we get the activation message

	if ( isset( $_GET['p'] ) ) {

		$data = unserialize( base64_decode( $_GET['p'] ) );

		$code = get_user_meta( $data['id'], 'activationcode', true );

		// check whether the code given is the same as ours

		if ( $code == $data['code'] ) {
			// update the db on the activation process

			update_user_meta( $data['id'], 'is_activated', 1 );

			wc_add_notice( __( '<strong>Success:</strong> Your account has been activated! ', 'inkfool' ) );
		} else {
			wc_add_notice( __( '<strong>Error:</strong> Activation fails, please contact our administrator. ', 'inkfool' ) );
		}
	}

	if ( isset( $_GET['q'] ) ) {

		wc_add_notice( __( '<strong>Error:</strong> Your account has to be activated before you can login. Please check your email.', 'inkfool' ) );
	}

	if ( isset( $_GET['u'] ) ) {

		my_user_register( $_GET['u'] );

		wc_add_notice( __( '<strong>Succes:</strong> Your activation email has been resend. Please check your email.', 'inkfool' ) );
	}
}

// hooks handler

add_action( 'init', 'my_init' );

add_filter( 'woocommerce_registration_redirect', 'wc_registration_redirect' );

add_filter( 'wp_authenticate_user', 'wp_authenticate_user', 10, 2 );

add_action( 'user_register', 'my_user_register', 10, 2 );
