<?php
/**
 * Login / Register Functions
 *
 * @package     RPRESS
 * @subpackage  Functions/Login
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Login Form
 *
 * @since 1.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Login form
*/
function rpress_login_form( $redirect = '' ) {
	global $rpress_login_redirect;

	if ( empty( $redirect ) ) {
		$redirect = rpress_get_current_page_url();
	}

	$rpress_login_redirect = $redirect;

	ob_start();

	rpress_get_template_part( 'shortcode', 'login' );

	return apply_filters( 'rpress_login_form', ob_get_clean() );
}

/**
 * Registration Form
 *
 * @since  1.0.0
 * @global $post
 * @param string $redirect Redirect page URL
 * @return string Register form
*/
function rpress_register_form( $redirect = '' ) {
	global $rpress_register_redirect;

	if ( empty( $redirect ) ) {
		$redirect = rpress_get_current_page_url();
	}

	$rpress_register_redirect = $redirect;

	ob_start();

	rpress_get_template_part( 'shortcode', 'register' );

	return apply_filters( 'rpress_register_form', ob_get_clean() );
}

/**
 * Process Login Form
 *
 * @since 1.0
 * @param array $data Data sent from the login form
 * @return void
*/
function rpress_process_login_form( $data ) {
	if ( wp_verify_nonce( $data['rpress_login_nonce'], 'rpress-login-nonce' ) ) {
		$user_data = get_user_by( 'login', $data['rpress_user_login'] );
		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $data['rpress_user_login'] );
		}
		if ( $user_data ) {
			$user_ID = $user_data->ID;
			$user_email = $user_data->user_email;

			if ( wp_check_password( $data['rpress_user_pass'], $user_data->user_pass, $user_data->ID ) ) {

				if ( isset( $data['rememberme'] ) ) {
					$data['rememberme'] = true;
				} else {
					$data['rememberme'] = false;
				}

				rpress_log_user_in( $user_data->ID, $data['rpress_user_login'], $data['rpress_user_pass'], $data['rememberme'] );
			} else {
				rpress_set_error( 'password_incorrect', __( 'The password you entered is incorrect', 'restropress' ) );
			}
		} else {
			rpress_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'restropress' ) );
		}
		// Check for errors and redirect if none present
		$errors = rpress_get_errors();
		if ( ! $errors ) {
			$redirect = apply_filters( 'rpress_login_redirect', $data['rpress_redirect'], $user_ID );
			wp_redirect( $redirect );
			rpress_die();
		}
	}
}
add_action( 'rpress_user_login', 'rpress_process_login_form' );

/**
 * Log User In
 *
 * @since 1.0
 * @param int $user_id User ID
 * @param string $user_login Username
 * @param string $user_pass Password
 * @param boolean $remember Remember me
 * @return void
*/
function rpress_log_user_in( $user_id, $user_login, $user_pass, $remember = false ) {
	if ( $user_id < 1 )
		return;

	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id, $user_login );
	do_action( 'wp_login', $user_login, get_userdata( $user_id ) );
	do_action( 'rpress_log_user_in', $user_id, $user_login, $user_pass );
}


/**
 * Process Register Form
 *
 * @since  1.0.0
 * @param array $data Data sent from the register form
 * @return void
*/
function rpress_process_register_form( $data ) {

	if( is_user_logged_in() ) {
		return;
	}

	if( empty( $_POST['rpress_register_submit'] ) ) {
		return;
	}

	do_action( 'rpress_pre_process_register_form' );

	if( empty( $data['rpress_user_login'] ) ) {
		rpress_set_error( 'empty_username', __( 'Invalid username', 'restropress' ) );
	}

	if( username_exists( $data['rpress_user_login'] ) ) {
		rpress_set_error( 'username_unavailable', __( 'Username already taken', 'restropress' ) );
	}

	if( ! validate_username( $data['rpress_user_login'] ) ) {
		rpress_set_error( 'username_invalid', __( 'Invalid username', 'restropress' ) );
	}

	if( email_exists( $data['rpress_user_email'] ) ) {
		rpress_set_error( 'email_unavailable', __( 'Email address already taken', 'restropress' ) );
	}

	if( empty( $data['rpress_user_email'] ) || ! is_email( $data['rpress_user_email'] ) ) {
		rpress_set_error( 'email_invalid', __( 'Invalid email', 'restropress' ) );
	}

	if( ! empty( $data['rpress_payment_email'] ) && $data['rpress_payment_email'] != $data['rpress_user_email'] && ! is_email( $data['rpress_payment_email'] ) ) {
		rpress_set_error( 'payment_email_invalid', __( 'Invalid payment email', 'restropress' ) );
	}

	if( empty( $_POST['rpress_user_pass'] ) ) {
		rpress_set_error( 'empty_password', __( 'Please enter a password', 'restropress' ) );
	}

	if( ( ! empty( $_POST['rpress_user_pass'] ) && empty( $_POST['rpress_user_pass2'] ) ) || ( $_POST['rpress_user_pass'] !== $_POST['rpress_user_pass2'] ) ) {
		rpress_set_error( 'password_mismatch', __( 'Passwords do not match', 'restropress' ) );
	}

	do_action( 'rpress_process_register_form' );

	// Check for errors and redirect if none present
	$errors = rpress_get_errors();

	if (  empty( $errors ) ) {

		$redirect = apply_filters( 'rpress_register_redirect', $data['rpress_redirect'] );

		rpress_register_and_login_new_user( array(
			'user_login'      => $data['rpress_user_login'],
			'user_pass'       => $data['rpress_user_pass'],
			'user_email'      => $data['rpress_user_email'],
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => get_option( 'default_role' )
		) );

		wp_redirect( $redirect );
		rpress_die();
	}
}
add_action( 'rpress_user_register', 'rpress_process_register_form' );
