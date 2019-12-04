<?php
/**
 * Process Purchase
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process Purchase Form
 *
 * Handles the purchase form process.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function rpress_process_purchase_form() {

	do_action( 'rpress_pre_process_purchase' );

	// Make sure the cart isn't empty
	if ( ! rpress_get_cart_contents() && ! rpress_cart_has_fees() ) {
		$valid_data = false;
		rpress_set_error( 'empty_cart', __( 'Your cart is empty', 'restropress' ) );
	} else {
		// Validate the form $_POST data
		$valid_data = rpress_purchase_form_validate_fields();

		// Allow themes and plugins to hook to errors
		do_action( 'rpress_checkout_error_checks', $valid_data, $_POST );
	}

	$is_ajax = isset( $_POST['rpress_ajax'] );

	// Process the login form
	if ( isset( $_POST['rpress_login_submit'] ) ) {
		rpress_process_purchase_login();
	}

	// Validate the user
	$user = rpress_get_purchase_form_user( $valid_data );

	// Let extensions validate fields after user is logged in if user has used login/registration form
	do_action( 'rpress_checkout_user_error_checks', $user, $valid_data, $_POST );

	if ( false === $valid_data || rpress_get_errors() || ! $user ) {
		if ( $is_ajax ) {
			do_action( 'rpress_ajax_checkout_errors' );
			rpress_die();
		} else {
			return false;
		}
	}

	if ( $is_ajax ) {
		echo 'success';
		rpress_die();
	}

	// Setup user information
	$user_info = array(
		'id'         => $user['user_id'],
		'email'      => $user['user_email'],
		'first_name' => $user['user_first'],
		'last_name'  => $user['user_last'],
		'discount'   => $valid_data['discount'],
		'address'    => ! empty( $user['address'] ) ? $user['address'] : array(),
	);

	// Update a customer record if they have added/updated information
	$customer = new RPRESS_Customer( $user_info['email'] );

	$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	if ( empty( $customer->name ) || $name != $customer->name ) {
		$update_data = array(
			'name' => $name
		);

		// Update the customer's name and update the user record too
		$customer->update( $update_data );
		wp_update_user( array(
			'ID'         => get_current_user_id(),
			'first_name' => $user_info['first_name'],
			'last_name'  => $user_info['last_name']
		) );
	}

	// Update the customer's address if different to what's in the database
	$address = get_user_meta( $customer->user_id, '_rpress_user_address', true );
	if ( ! is_array( $address ) ) {
		$address = array();
	}

	if ( 0 == strlen( implode( $address ) ) || count( array_diff( $address, $user_info['address'] ) ) > 0 ) {
		update_user_meta( $user['user_id'], '_rpress_user_address', $user_info['address'] );
	}

	$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

	$card_country = isset( $valid_data['cc_info']['card_country'] ) ? $valid_data['cc_info']['card_country'] : false;
	$card_state   = isset( $valid_data['cc_info']['card_state'] )   ? $valid_data['cc_info']['card_state']   : false;
	$card_zip     = isset( $valid_data['cc_info']['card_zip'] )     ? $valid_data['cc_info']['card_zip']     : false;

	// Set up the unique purchase key. If we are resuming a payment, we'll overwrite this with the existing key.
	$purchase_key     = strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'rpress', true ) ) );
	$existing_payment = RPRESS()->session->get( 'rpress_resume_payment' );

	if ( ! empty( $existing_payment ) ) {
		$payment = new RPRESS_Payment( $existing_payment );
		if( $payment->is_recoverable() && ! empty( $payment->key ) ) {
			$purchase_key = $payment->key;
		}
	}

	// Setup purchase information
	$purchase_data = array(
		'fooditems'    => rpress_get_cart_contents(),
		'fees'         => rpress_get_cart_fees(),        // Any arbitrary fees that have been added to the cart
		'subtotal'     => rpress_get_cart_subtotal(),    // Amount before taxes and discounts
		'discount'     => rpress_get_cart_discounted_amount(), // Discounted amount
		'tax'          => rpress_get_cart_tax(),               // Taxed amount
		'tax_rate'     => rpress_use_taxes() ? rpress_get_cart_tax_rate( $card_country, $card_state, $card_zip ) : 0, // Tax rate
		'price'        => rpress_get_cart_total(),    // Amount after taxes
		'purchase_key' => $purchase_key,
		'user_email'   => $user['user_email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => stripslashes_deep( $user_info ),
		'post_data'    => $_POST,
		'cart_details' => rpress_get_cart_content_details(),
		'gateway'      => $valid_data['gateway'],
		'card_info'    => $valid_data['cc_info'],
	);


	// Add the user data for hooks
	$valid_data['user'] = $user;

	// Allow themes and plugins to hook before the gateway
	do_action( 'rpress_checkout_before_gateway', $_POST, $user_info, $valid_data );

	// If the total amount in the cart is 0, send to the manual gateway. This emulates a free fooditem purchase
	if ( !$purchase_data['price'] ) {
		// Revert to manual
		$purchase_data['gateway'] = 'manual';
		$_POST['rpress-gateway'] = 'manual';
	}

	// Allow the purchase data to be modified before it is sent to the gateway
	$purchase_data = apply_filters(
		'rpress_purchase_data_before_gateway',
		$purchase_data,
		$valid_data
	);

	// Setup the data we're storing in the purchase session
	$session_data = $purchase_data;

	// Make sure credit card numbers are never stored in sessions
	unset( $session_data['card_info']['card_number'] );

	// Used for showing fooditem links to non logged-in users after purchase, and for other plugins needing purchase data.
	rpress_set_purchase_session( $session_data );

	// Send info to the gateway for payment processing
	rpress_send_to_gateway( $purchase_data['gateway'], $purchase_data );
	rpress_die();
}
add_action( 'rpress_purchase', 'rpress_process_purchase_form' );
add_action( 'wp_ajax_rpress_process_checkout', 'rpress_process_purchase_form' );
add_action( 'wp_ajax_nopriv_rpress_process_checkout', 'rpress_process_purchase_form' );

/**
 * Verify that when a logged in user makes a purchase that the email address used doesn't belong to a different customer
 *
 * @since  1.0.0
 * @param  array $valid_data Validated data submitted for the purchase
 * @param  array $post       Additional $_POST data submitted
 * @return void
 */
function rpress_checkout_check_existing_email( $valid_data, $post ) {

	// Verify that the email address belongs to this customer
	if ( is_user_logged_in() ) {

		$email    = strtolower( $valid_data['logged_in_user']['user_email'] );
		$customer = new RPRESS_Customer( get_current_user_id(), true );

		// If this email address is not registered with this customer, see if it belongs to any other customer
		if ( $email != strtolower( $customer->email ) && ( is_array( $customer->emails ) && ! in_array( $email, array_map( 'strtolower', $customer->emails ) ) ) ) {
			$found_customer = new RPRESS_Customer( $email );
			if ( $found_customer->id > 0 ) {
				rpress_set_error( 'rpress-customer-email-exists', sprintf( __( 'The email address %s is already in use.', 'restropress' ), $email ) );
			}
		}


	}

}
add_action( 'rpress_checkout_error_checks', 'rpress_checkout_check_existing_email', 10, 2 );

/**
 * Process the checkout login form
 *
 * @access      private
 * @since 1.0
 * @return      void
 */
function rpress_process_purchase_login() {

	$is_ajax = isset( $_POST['rpress_ajax'] );

	$user_data = rpress_purchase_form_validate_user_login();

	if ( rpress_get_errors() || $user_data['user_id'] < 1 ) {
		if ( $is_ajax ) {
			do_action( 'rpress_ajax_checkout_errors' );
			rpress_die();
		} else {
			wp_redirect( $_SERVER['HTTP_REFERER'] ); exit;
		}
	}

	rpress_log_user_in( $user_data['user_id'], $user_data['user_login'], $user_data['user_pass'] );

	if ( $is_ajax ) {
		echo 'success';
		rpress_die();
	} else {
		wp_redirect( rpress_get_checkout_uri( $_SERVER['QUERY_STRING'] ) );
	}
}
add_action( 'wp_ajax_rpress_process_checkout_login', 'rpress_process_purchase_login' );
add_action( 'wp_ajax_nopriv_rpress_process_checkout_login', 'rpress_process_purchase_login' );

/**
 * Purchase Form Validate Fields
 *
 * @access      private
 * @since       1.0.8.1
 * @return      bool|array
 */
function rpress_purchase_form_validate_fields() {
	// Check if there is $_POST
	if ( empty( $_POST ) ) return false;

	// Start an array to collect valid data
	$valid_data = array(
		'gateway'          => rpress_purchase_form_validate_gateway(), // Gateway fallback
		'discount'         => rpress_purchase_form_validate_discounts(),    // Set default discount
		'need_new_user'    => false,     // New user flag
		'need_user_login'  => false,     // Login user flag
		'logged_user_data' => array(),   // Logged user collected data
		'new_user_data'    => array(),   // New user collected data
		'login_user_data'  => array(),   // Login user collected data
		'guest_user_data'  => array(),   // Guest user collected data
		'cc_info'          => rpress_purchase_form_validate_cc()    // Credit card info
	);

	// Validate of min order amount is enabled and the cart contains the order amount
	if ( '1' === rpress_get_option( 'allow_minimum_order', false ) ) {
		rpress_check_minimum_order_amount();
	}

	// Validate agree to terms
	if ( '1' === rpress_get_option( 'show_agree_to_terms', false ) ) {
		rpress_purchase_form_validate_agree_to_terms();
	}

	// Validate agree to privacy policy
	if ( '1' === rpress_get_option( 'show_agree_to_privacy_policy', false ) ) {
		rpress_purchase_form_validate_agree_to_privacy_policy();
	}

	if ( is_user_logged_in() ) {
		// Collect logged in user data
		$valid_data['logged_in_user'] = rpress_purchase_form_validate_logged_in_user();
	} else if ( isset( $_POST['rpress-purchase-var'] ) && $_POST['rpress-purchase-var'] == 'needs-to-register' ) {
		// Set new user registration as required
		$valid_data['need_new_user'] = true;

		// Validate new user data
		$valid_data['new_user_data'] = rpress_purchase_form_validate_new_user();
		// Check if login validation is needed
	} else if ( isset( $_POST['rpress-purchase-var'] ) && $_POST['rpress-purchase-var'] == 'needs-to-login' ) {
		// Set user login as required
		$valid_data['need_user_login'] = true;

		// Validate users login info
		$valid_data['login_user_data'] = rpress_purchase_form_validate_user_login();
	} else {
		// Not registering or logging in, so setup guest user data
		$valid_data['guest_user_data'] = rpress_purchase_form_validate_guest_user();
	}

	// Return collected data
	return $valid_data;
}

/**
 * Purchase Form Validate Gateway
 *
 * @access      private
 * @since       1.0
 * @return      string
 */
function rpress_purchase_form_validate_gateway() {

	$gateway = rpress_get_default_gateway();

	// Check if a gateway value is present
	if ( ! empty( $_REQUEST['rpress-gateway'] ) ) {

		$gateway = sanitize_text_field( $_REQUEST['rpress-gateway'] );

		if ( '0.00' == rpress_get_cart_total() ) {

			$gateway = 'manual';

		} elseif ( ! rpress_is_gateway_active( $gateway ) ) {

			rpress_set_error( 'invalid_gateway', __( 'The selected payment gateway is not enabled', 'restropress' ) );

		}

	}

	return $gateway;

}

/**
 * Purchase Form Validate Discounts
 *
 * @access      private
 * @since       1.0.8.1
 * @return      string
 */
function rpress_purchase_form_validate_discounts() {
	// Retrieve the discount stored in cookies
	$discounts = rpress_get_cart_discounts();

	$user = '';
	if ( isset( $_POST['rpress_user_login'] ) && ! empty( $_POST['rpress_user_login'] ) ) {
		$user = sanitize_text_field( $_POST['rpress_user_login'] );
	} else if ( isset( $_POST['rpress_email'] ) && ! empty($_POST['rpress_email'] ) ) {
		$user = sanitize_text_field( $_POST['rpress_email'] );
	} else if ( is_user_logged_in() ) {
		$user = wp_get_current_user()->user_email;
	}

	$error = false;

	// Check for valid discount(s) is present
	if ( ! empty( $_POST['rpress-discount'] ) && __( 'Enter coupon code', 'restropress' ) != $_POST['rpress-discount'] ) {
		// Check for a posted discount
		$posted_discount = isset( $_POST['rpress-discount'] ) ? trim( $_POST['rpress-discount'] ) : false;

		// Add the posted discount to the discounts
		if ( $posted_discount && ( empty( $discounts ) || rpress_multiple_discounts_allowed() ) && rpress_is_discount_valid( $posted_discount, $user ) ) {
			rpress_set_cart_discount( $posted_discount );
		}

	}

	// If we have discounts, loop through them
	if ( ! empty( $discounts ) ) {

		foreach ( $discounts as $discount ) {
			// Check if valid
			if (  ! rpress_is_discount_valid( $discount, $user ) ) {
				// Discount is not valid
				$error = true;
			}
		}
	} else {
		// No discounts
		return 'none';
	}

	if ( $error ) {
		rpress_set_error( 'invalid_discount', __( 'One or more of the discounts you entered is invalid', 'restropress' ) );
	}

	return implode( ', ', $discounts );
}

/**
 * Purchase Form Validate Agree To Terms
 *
 * @access      private
 * @since       1.0.8.1
 * @return      void
 */
function rpress_purchase_form_validate_agree_to_terms() {
	// Validate agree to terms
	if ( ! isset( $_POST['rpress_agree_to_terms'] ) || $_POST['rpress_agree_to_terms'] != 1 ) {
		// User did not agree
		rpress_set_error( 'agree_to_terms', apply_filters( 'rpress_agree_to_terms_text', __( 'You must agree to the terms of use', 'restropress' ) ) );
	}
}

/**
 * Purchase Form Validate Agree To Privacy Policy
 *
 * @since       2.9.1
 * @return      void
 */
function rpress_purchase_form_validate_agree_to_privacy_policy() {
	// Validate agree to terms
	if ( ! isset( $_POST['rpress_agree_to_privacy_policy'] ) || $_POST['rpress_agree_to_privacy_policy'] != 1 ) {
		// User did not agree
		rpress_set_error( 'agree_to_privacy_policy', apply_filters( 'rpress_agree_to_privacy_policy_text', __( 'You must agree to the privacy policy', 'restropress' ) ) );
	}
}

/**
 * Purchase Form Required Fields
 *
 * @access      private
 * @since       1.5
 * @return      array
 */
function rpress_purchase_form_required_fields() {
	$required_fields = array(
		'rpress_email' => array(
			'error_id' => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'restropress' )
		),
		'rpress_first' => array(
			'error_id' => 'invalid_first_name',
			'error_message' => __( 'Please enter your first name', 'restropress' )
		)
	);

	// Let payment gateways and other extensions determine if address fields should be required
	$require_address = apply_filters( 'rpress_require_billing_address', rpress_use_taxes() && rpress_get_cart_total() );

	if ( $require_address ) {
		$required_fields['card_zip'] = array(
			'error_id' => 'invalid_zip_code',
			'error_message' => __( 'Please enter your zip / postal code', 'restropress' )
		);
		$required_fields['card_city'] = array(
			'error_id' => 'invalid_city',
			'error_message' => __( 'Please enter your billing city', 'restropress' )
		);
		$required_fields['billing_country'] = array(
			'error_id' => 'invalid_country',
			'error_message' => __( 'Please select your billing country', 'restropress' )
		);
		$required_fields['card_state'] = array(
			'error_id' => 'invalid_state',
			'error_message' => __( 'Please enter billing state / province', 'restropress' )
		);

		// Check if the Customer's Country has been passed in and if it has no states.
		if ( isset( $_POST['billing_country'] ) && isset( $required_fields['card_state'] ) ){
			$customer_billing_country = sanitize_text_field( $_POST['billing_country'] );
			$states = rpress_get_shop_states( $customer_billing_country );

			// If this country has no states, remove the requirement of a card_state.
			if ( empty( $states ) ){
				unset( $required_fields['card_state'] );
			}
		}
	}

	return apply_filters( 'rpress_purchase_form_required_fields', $required_fields );
}

/**
 * Purchase Form Validate Logged In User
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function rpress_purchase_form_validate_logged_in_user() {
	global $user_ID;

	// Start empty array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => -1
	);

	// Verify there is a user_ID
	if ( $user_ID > 0 ) {
		// Get the logged in user data
		$user_data = get_userdata( $user_ID );

		// Loop through required fields and show error messages
		foreach ( rpress_purchase_form_required_fields() as $field_name => $value ) {
			if ( in_array( $value, rpress_purchase_form_required_fields() ) && empty( $_POST[ $field_name ] ) ) {
				rpress_set_error( $value['error_id'], $value['error_message'] );
			}
		}

		// Verify data
		if ( $user_data ) {
			// Collected logged in user data
			$valid_user_data = array(
				'user_id'    => $user_ID,
				'user_email' => isset( $_POST['rpress_email'] ) ? sanitize_email( $_POST['rpress_email'] ) : $user_data->user_email,
				'user_first' => isset( $_POST['rpress_first'] ) && ! empty( $_POST['rpress_first'] ) ? sanitize_text_field( $_POST['rpress_first'] ) : $user_data->first_name,
				'user_last'  => isset( $_POST['rpress_last'] ) && ! empty( $_POST['rpress_last']  ) ? sanitize_text_field( $_POST['rpress_last']  ) : $user_data->last_name,
			);

			if ( ! is_email( $valid_user_data['user_email'] ) ) {
				rpress_set_error( 'email_invalid', __( 'Invalid email', 'restropress' ) );
			}

		} else {
			// Set invalid user error
			rpress_set_error( 'invalid_user', __( 'The user information is invalid', 'restropress' ) );
		}
	}

	// Return user data
	return $valid_user_data;
}

/**
 * Purchase Form Validate New User
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
 */
function rpress_purchase_form_validate_new_user() {
	$registering_new_user = false;

	// Start an empty array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => -1,
		// Get first name
		'user_first' => isset( $_POST["rpress_first"] ) ? sanitize_text_field( $_POST["rpress_first"] ) : '',
		// Get last name
		'user_last' => isset( $_POST["rpress_last"] ) ? sanitize_text_field( $_POST["rpress_last"] ) : '',
	);

	// Check the new user's credentials against existing ones
	$user_login   = isset( $_POST["rpress_user_login"] ) ? trim( $_POST["rpress_user_login"] ) : false;
	$user_email   = isset( $_POST['rpress_email'] ) ? trim( $_POST['rpress_email'] ) : false;
	$user_pass    = isset( $_POST["rpress_user_pass"] ) ? trim( $_POST["rpress_user_pass"] ) : false;
	$pass_confirm = isset( $_POST["rpress_user_pass_confirm"] ) ? trim( $_POST["rpress_user_pass_confirm"] ) : false;

	// Loop through required fields and show error messages
	foreach ( rpress_purchase_form_required_fields() as $field_name => $value ) {
		if ( in_array( $value, rpress_purchase_form_required_fields() ) && empty( $_POST[ $field_name ] ) ) {
			rpress_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	// Check if we have an username to register
	if ( $user_login && strlen( $user_login ) > 0 ) {
		$registering_new_user = true;

		// We have an user name, check if it already exists
		if ( username_exists( $user_login ) ) {
			// Username already registered
			rpress_set_error( 'username_unavailable', __( 'Username already taken', 'restropress' ) );
			// Check if it's valid
		} else if ( ! rpress_validate_username( $user_login ) ) {
				// Invalid username
				if ( is_multisite() )
					rpress_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'restropress' ) );
				else
					rpress_set_error( 'username_invalid', __( 'Invalid username', 'restropress' ) );
			} else {
			// All the checks have run and it's good to go
			$valid_user_data['user_login'] = $user_login;
		}
	} else {
		if ( rpress_no_guest_checkout() ) {
			rpress_set_error( 'registration_required', __( 'You must register or login to complete your purchase', 'restropress' ) );
		}
	}

	// Check if we have an email to verify
	if ( $user_email && strlen( $user_email ) > 0 ) {
		// Validate email
		if ( ! is_email( $user_email ) ) {
			rpress_set_error( 'email_invalid', __( 'Invalid email', 'restropress' ) );
			// Check if email exists
		} else {
			$customer = new RPRESS_Customer( $user_email );
			if ( $registering_new_user && email_exists( $user_email ) ) {
				rpress_set_error( 'email_used', __( 'Email already used. Login or use a different email to complete your order.', 'restropress' ) );
			} else {
				// All the checks have run and it's good to go
				$valid_user_data['user_email'] = $user_email;
			}
		}
	} else {
		// No email
		rpress_set_error( 'email_empty', __( 'Enter an email', 'restropress' ) );
	}

	// Check password
	if ( $user_pass && $pass_confirm ) {
		// Verify confirmation matches
		if ( $user_pass != $pass_confirm ) {
			// Passwords do not match
			rpress_set_error( 'password_mismatch', __( 'Passwords don\'t match', 'restropress' ) );
		} else {
			// All is good to go
			$valid_user_data['user_pass'] = $user_pass;
		}
	} else {
		// Password or confirmation missing
		if ( ! $user_pass && $registering_new_user ) {
			// The password is invalid
			rpress_set_error( 'password_empty', __( 'Enter a password', 'restropress' ) );
		} else if ( ! $pass_confirm && $registering_new_user ) {
			// Confirmation password is invalid
			rpress_set_error( 'confirmation_empty', __( 'Enter the password confirmation', 'restropress' ) );
		}
	}

	return $valid_user_data;
}

/**
 * Purchase Form Validate User Login
 *
 * @access      private
 * @since       1.0.8.1
 * @return      array
 */
function rpress_purchase_form_validate_user_login() {

	// Start an array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => -1
	);

	// Username
	if ( empty( $_POST['rpress_user_login'] ) && rpress_no_guest_checkout() ) {
		rpress_set_error( 'must_log_in', __( 'You must log in or register to complete your purchase', 'restropress' ) );
		return $valid_user_data;
	}

	$login_or_email = strip_tags( $_POST['rpress_user_login'] );

	if ( is_email( $login_or_email ) ) {
		// Get the user by email
		$user_data = get_user_by( 'email', $login_or_email );
	} else {
		// Get the user by login
		$user_data = get_user_by( 'login', $login_or_email );
	}

	// Check if user exists
	if ( $user_data ) {
		// Get password
		$user_pass = isset( $_POST["rpress_user_pass"] ) ? $_POST["rpress_user_pass"] : false;

		// Check user_pass
		if ( $user_pass ) {
			// Check if password is valid
			if ( ! wp_check_password( $user_pass, $user_data->user_pass, $user_data->ID ) ) {
				// Incorrect password
				rpress_set_error(
					'password_incorrect',
					sprintf(
						__( 'The password you entered is incorrect. %sReset Password%s', 'restropress' ),
						'<a href="' . wp_lostpassword_url( rpress_get_checkout_uri() ) . '">',
						'</a>'
					)
				);
				// All is correct
			} else {
				// Repopulate the valid user data array
				$valid_user_data = array(
					'user_id' => $user_data->ID,
					'user_login' => $user_data->user_login,
					'user_email' => $user_data->user_email,
					'user_first' => $user_data->first_name,
					'user_last' => $user_data->last_name,
					'user_pass' => $user_pass,
				);
			}
		} else {
			// Empty password
			rpress_set_error( 'password_empty', __( 'Enter a password', 'restropress' ) );
		}
	} else {
		// no username
		rpress_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'restropress' ) );
	}

	return $valid_user_data;
}

/**
 * Purchase Form Validate Guest User
 *
 * @access  private
 * @since  1.0.8.1
 * @return  array
 */
function rpress_purchase_form_validate_guest_user() {
	// Start an array to collect valid user data
	$valid_user_data = array(
		// Set a default id for guests
		'user_id' => 0,
	);

	// Show error message if user must be logged in
	if ( rpress_logged_in_only() ) {
		rpress_set_error( 'logged_in_only', __( 'You must be logged into an account to purchase', 'restropress' ) );
	}

	// Get the guest email
	$guest_email = isset( $_POST['rpress_email'] ) ? $_POST['rpress_email'] : false;

	// Check email
	if ( $guest_email && strlen( $guest_email ) > 0 ) {
		// Validate email
		if ( ! is_email( $guest_email ) ) {
			// Invalid email
			rpress_set_error( 'email_invalid', __( 'Invalid email', 'restropress' ) );
		} else {
			// All is good to go
			$valid_user_data['user_email'] = $guest_email;
		}
	} else {
		// No email
		rpress_set_error( 'email_empty', __( 'Enter an email', 'restropress' ) );
	}

	// Loop through required fields and show error messages
	foreach ( rpress_purchase_form_required_fields() as $field_name => $value ) {
		if ( in_array( $value, rpress_purchase_form_required_fields() ) && empty( $_POST[ $field_name ] ) ) {
			rpress_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	return $valid_user_data;
}

/**
 * Register And Login New User
 *
 * @param array   $user_data
 *
 * @access  private
 * @since  1.0.8.1
 * @return  integer
 */
function rpress_register_and_login_new_user( $user_data = array() ) {
	// Verify the array
	if ( empty( $user_data ) )
		return -1;

	if ( rpress_get_errors() )
		return -1;

	$user_args = apply_filters( 'rpress_insert_user_args', array(
		'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
		'user_pass'       => isset( $user_data['user_pass'] )  ? $user_data['user_pass']  : '',
		'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
		'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
		'last_name'       => isset( $user_data['user_last'] )  ? $user_data['user_last']  : '',
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => get_option( 'default_role' )
	), $user_data );

	// Insert new user
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user
	if ( is_wp_error( $user_id ) )
		return -1;

	// Allow themes and plugins to filter the user data
	$user_data = apply_filters( 'rpress_insert_user_data', $user_data, $user_args );

	// Allow themes and plugins to hook
	do_action( 'rpress_insert_user', $user_id, $user_data );

	// Login new user
	rpress_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id
	return $user_id;
}

/**
 * Get Purchase Form User
 *
 * @param array   $valid_data
 *
 * @access  private
 * @since  1.0.8.1
 * @return  array
 */
function rpress_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user
	$user    = false;
	$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	if ( $is_ajax ) {
		// Do not create or login the user during the ajax submission (check for errors only)
		return true;
	} else if ( is_user_logged_in() ) {
		// Set the valid user as the logged in collected data
		$user = $valid_data['logged_in_user'];
	} else if ( $valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true  ) {
		// New user registration
		if ( $valid_data['need_new_user'] === true ) {
			// Set user
			$user = $valid_data['new_user_data'];
			// Register and login new user
			$user['user_id'] = rpress_register_and_login_new_user( $user );
			// User login
		} else if ( $valid_data['need_user_login'] === true  && ! $is_ajax ) {
			/*
			 * The login form is now processed in the rpress_process_purchase_login() function.
			 * This is still here for backwards compatibility.
			 * This also allows the old login process to still work if a user removes the
			 * checkout login submit button.
			 *
			 * This also ensures that the customer is logged in correctly if they click "Purchase"
			 * instead of submitting the login form, meaning the customer is logged in during the purchase process.
			 */

			// Set user
			$user = $valid_data['login_user_data'];

			// Login user
			if ( empty( $user ) || $user['user_id'] == -1 ) {
				rpress_set_error( 'invalid_user', __( 'The user information is invalid', 'restropress' ) );
				return false;
			} else {
				rpress_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
			}
		}
	}

	// Check guest checkout
	if ( false === $user && false === rpress_no_guest_checkout() ) {
		// Set user
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user
	if ( false === $user || empty( $user ) ) {
		// Return false
		return false;
	}

	// Get user first name
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST["rpress_first"] ) ? strip_tags( trim( $_POST["rpress_first"] ) ) : '';
	}

	// Get user last name
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST["rpress_last"] ) ? strip_tags( trim( $_POST["rpress_last"] ) ) : '';
	}

	// Get the user's billing address details
	$user['address'] = array();
	$user['address']['line1']   = ! empty( $_POST['card_address']    ) ? sanitize_text_field( $_POST['card_address']    ) : '';
	$user['address']['line2']   = ! empty( $_POST['card_address_2']  ) ? sanitize_text_field( $_POST['card_address_2']  ) : '';
	$user['address']['city']    = ! empty( $_POST['card_city']       ) ? sanitize_text_field( $_POST['card_city']       ) : '';
	$user['address']['state']   = ! empty( $_POST['card_state']      ) ? sanitize_text_field( $_POST['card_state']      ) : '';
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$user['address']['zip']     = ! empty( $_POST['card_zip']        ) ? sanitize_text_field( $_POST['card_zip']        ) : '';

	if ( empty( $user['address']['country'] ) )
		$user['address'] = false; // Country will always be set if address fields are present

	if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $user['address'] ) ) {
		// Store the address in the user's meta so the cart can be pre-populated with it on return purchases
		update_user_meta( $user['user_id'], '_rpress_user_address', $user['address'] );
	}

	// Return valid user
	return $user;
}

/**
 * Validates the credit card info
 *
 * @access  private
 * @since  1.4.4
 * @return  array
 */
function rpress_purchase_form_validate_cc() {
	$card_data = rpress_get_purchase_cc_info();

	// Validate the card zip
	if ( ! empty( $card_data['card_zip'] ) && rpress_get_cart_total() > 0.00 ) {
		if ( ! rpress_purchase_form_validate_cc_zip( $card_data['card_zip'], $card_data['card_country'] ) ) {
			rpress_set_error( 'invalid_cc_zip', __( 'The zip / postal code you entered for your billing address is invalid', 'restropress' ) );
		}
	}

	// This should validate card numbers at some point too
	return $card_data;
}

/**
 * Get Credit Card Info
 *
 * @access  private
 * @since  1.4.4
 * @return  array
 */
function rpress_get_purchase_cc_info() {
	$cc_info = array();
	$cc_info['card_name']      = isset( $_POST['card_name'] )       ? sanitize_text_field( $_POST['card_name'] )       : '';
	$cc_info['card_number']    = isset( $_POST['card_number'] )     ? sanitize_text_field( $_POST['card_number'] )     : '';
	$cc_info['card_cvc']       = isset( $_POST['card_cvc'] )        ? sanitize_text_field( $_POST['card_cvc'] )        : '';
	$cc_info['card_exp_month'] = isset( $_POST['card_exp_month'] )  ? sanitize_text_field( $_POST['card_exp_month'] )  : '';
	$cc_info['card_exp_year']  = isset( $_POST['card_exp_year'] )   ? sanitize_text_field( $_POST['card_exp_year'] )   : '';
	$cc_info['card_address']   = isset( $_POST['card_address'] )    ? sanitize_text_field( $_POST['card_address'] )    : '';
	$cc_info['card_address_2'] = isset( $_POST['card_address_2'] )  ? sanitize_text_field( $_POST['card_address_2'] )  : '';
	$cc_info['card_city']      = isset( $_POST['card_city'] )       ? sanitize_text_field( $_POST['card_city'] )       : '';
	$cc_info['card_state']     = isset( $_POST['card_state'] )      ? sanitize_text_field( $_POST['card_state'] )      : '';
	$cc_info['card_country']   = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$cc_info['card_zip']       = isset( $_POST['card_zip'] )        ? sanitize_text_field( $_POST['card_zip'] )        : '';

	// Return cc info
	return $cc_info;
}

/**
 * Validate zip code based on country code
 *
 * @since  1.4.4
 *
 * @param int     $zip
 * @param string  $country_code
 *
 * @return bool|mixed|void
 */
function rpress_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {
	$ret = false;

	if ( empty( $zip ) || empty( $country_code ) )
		return $ret;

	$country_code = strtoupper( $country_code );

	$zip_regex = array(
		"AD" => "AD\d{3}",
		"AM" => "(37)?\d{4}",
		"AR" => "^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$",
		"AS" => "96799",
		"AT" => "\d{4}",
		"AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
		"AX" => "22\d{3}",
		"AZ" => "\d{4}",
		"BA" => "\d{5}",
		"BB" => "(BB\d{5})?",
		"BD" => "\d{4}",
		"BE" => "^[1-9]{1}[0-9]{3}$",
		"BG" => "\d{4}",
		"BH" => "((1[0-2]|[2-9])\d{2})?",
		"BM" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
		"BN" => "[A-Z]{2}[ ]?\d{4}",
		"BR" => "\d{5}[\-]?\d{3}",
		"BY" => "\d{6}",
		"CA" => "^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$",
		"CC" => "6799",
		"CH" => "^[1-9][0-9][0-9][0-9]$",
		"CK" => "\d{4}",
		"CL" => "\d{7}",
		"CN" => "\d{6}",
		"CR" => "\d{4,5}|\d{3}-\d{4}",
		"CS" => "\d{5}",
		"CV" => "\d{4}",
		"CX" => "6798",
		"CY" => "\d{4}",
		"CZ" => "\d{3}[ ]?\d{2}",
		"DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
		"DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
		"DO" => "\d{5}",
		"DZ" => "\d{5}",
		"EC" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
		"EE" => "\d{5}",
		"EG" => "\d{5}",
		"ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
		"ET" => "\d{4}",
		"FI" => "\d{5}",
		"FK" => "FIQQ 1ZZ",
		"FM" => "(9694[1-4])([ \-]\d{4})?",
		"FO" => "\d{3}",
		"FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
		"GE" => "\d{4}",
		"GF" => "9[78]3\d{2}",
		"GL" => "39\d{2}",
		"GN" => "\d{3}",
		"GP" => "9[78][01]\d{2}",
		"GR" => "\d{3}[ ]?\d{2}",
		"GS" => "SIQQ 1ZZ",
		"GT" => "\d{5}",
		"GU" => "969[123]\d([ \-]\d{4})?",
		"GW" => "\d{4}",
		"HM" => "\d{4}",
		"HN" => "(?:\d{5})?",
		"HR" => "\d{5}",
		"HT" => "\d{4}",
		"HU" => "\d{4}",
		"ID" => "\d{5}",
		"IE" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
		"IL" => "\d{5}",
		"IN"=> "^[1-9][0-9][0-9][0-9][0-9][0-9]$", //india
		"IO" => "BBND 1ZZ",
		"IQ" => "\d{5}",
		"IS" => "\d{3}",
		"IT" => "^(V-|I-)?[0-9]{5}$",
		"JO" => "\d{5}",
		"JP" => "\d{3}-\d{4}",
		"KE" => "\d{5}",
		"KG" => "\d{6}",
		"KH" => "\d{5}",
		"KR" => "\d{5}",
		"KW" => "\d{5}",
		"KZ" => "\d{6}",
		"LA" => "\d{5}",
		"LB" => "(\d{4}([ ]?\d{4})?)?",
		"LI" => "(948[5-9])|(949[0-7])",
		"LK" => "\d{5}",
		"LR" => "\d{4}",
		"LS" => "\d{3}",
		"LT" => "\d{5}",
		"LU" => "\d{4}",
		"LV" => "\d{4}",
		"MA" => "\d{5}",
		"MC" => "980\d{2}",
		"MD" => "\d{4}",
		"ME" => "8\d{4}",
		"MG" => "\d{3}",
		"MH" => "969[67]\d([ \-]\d{4})?",
		"MK" => "\d{4}",
		"MN" => "\d{6}",
		"MP" => "9695[012]([ \-]\d{4})?",
		"MQ" => "9[78]2\d{2}",
		"MT" => "[A-Z]{3}[ ]?\d{2,4}",
		"MU" => "(\d{3}[A-Z]{2}\d{3})?",
		"MV" => "\d{5}",
		"MX" => "\d{5}",
		"MY" => "\d{5}",
		"NC" => "988\d{2}",
		"NE" => "\d{4}",
		"NF" => "2899",
		"NG" => "(\d{6})?",
		"NI" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
		"NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
		"NO" => "\d{4}",
		"NP" => "\d{5}",
		"NZ" => "\d{4}",
		"OM" => "(PC )?\d{3}",
		"PF" => "987\d{2}",
		"PG" => "\d{3}",
		"PH" => "\d{4}",
		"PK" => "\d{5}",
		"PL" => "\d{2}-\d{3}",
		"PM" => "9[78]5\d{2}",
		"PN" => "PCRN 1ZZ",
		"PR" => "00[679]\d{2}([ \-]\d{4})?",
		"PT" => "\d{4}([\-]\d{3})?",
		"PW" => "96940",
		"PY" => "\d{4}",
		"RE" => "9[78]4\d{2}",
		"RO" => "\d{6}",
		"RS" => "\d{5}",
		"RU" => "\d{6}",
		"SA" => "\d{5}",
		"SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
		"SG" => "\d{6}",
		"SH" => "(ASCN|STHL) 1ZZ",
		"SI" => "\d{4}",
		"SJ" => "\d{4}",
		"SK" => "\d{3}[ ]?\d{2}",
		"SM" => "4789\d",
		"SN" => "\d{5}",
		"SO" => "\d{5}",
		"SZ" => "[HLMS]\d{3}",
		"TC" => "TKCA 1ZZ",
		"TH" => "\d{5}",
		"TJ" => "\d{6}",
		"TM" => "\d{6}",
		"TN" => "\d{4}",
		"TR" => "\d{5}",
		"TW" => "\d{3}(\d{2})?",
		"UA" => "\d{5}",
		"UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
		"US" => "^\d{5}([\-]?\d{4})?$",
		"UY" => "\d{5}",
		"UZ" => "\d{6}",
		"VA" => "00120",
		"VE" => "\d{4}",
		"VI" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
		"WF" => "986\d{2}",
		"YT" => "976\d{2}",
		"YU" => "\d{5}",
		"ZA" => "\d{4}",
		"ZM" => "\d{5}"
	);

	if ( ! isset ( $zip_regex[ $country_code ] ) || preg_match( "/" . $zip_regex[ $country_code ] . "/i", $zip ) )
		$ret = true;

	return apply_filters( 'rpress_is_zip_valid', $ret, $zip, $country_code );
}


/**
 * Check the purchase to ensure a banned email is not allowed through
 *
 * @since       2.0
 * @return      void
 */
function rpress_check_purchase_email( $valid_data, $posted ) {

	$banned = rpress_get_banned_emails();

	if( empty( $banned ) ) {
		return;
	}

	$user_emails = array( $posted['rpress_email'] );
	if( is_user_logged_in() ) {

		// The user is logged in, check that their account email is not banned
		$user_data     = get_userdata( get_current_user_id() );
		$user_emails[] = $user_data->user_email;

	} elseif( isset( $posted['rpress-purchase-var'] ) && $posted['rpress-purchase-var'] == 'needs-to-login' ) {

		// The user is logging in, check that their email is not banned
		if( $user_data = get_user_by( 'login', $posted['rpress_user_login'] ) ) {
			$user_emails[] = $user_data->user_email;
		}

	}

	foreach ( $user_emails as $email ) {
		if ( rpress_is_email_banned( $email ) ) {
			// Set an error and give the customer a general error (don't alert them that they were banned)
			rpress_set_error( 'email_banned', __( 'An internal error has occurred, please try again or contact support.', 'restropress' ) );
			break;
		}
	}

}
add_action( 'rpress_checkout_error_checks', 'rpress_check_purchase_email', 10, 2 );


/**
 * Process a straight-to-gateway purchase
 *
 * @since  1.0.0
 * @return void
 */
function rpress_process_straight_to_gateway( $data ) {

	$fooditem_id = $data['fooditem_id'];
	$options     = isset( $data['rpress_options'] ) ? $data['rpress_options'] : array();
	$quantity    = isset( $data['rpress_fooditem_quantity'] ) ? $data['rpress_fooditem_quantity'] : 1;

	if( empty( $fooditem_id ) || ! rpress_get_fooditem( $fooditem_id ) ) {
		return;
	}

	$purchase_data    = rpress_build_straight_to_gateway_data( $fooditem_id, $options, $quantity );
	$enabled_gateways = rpress_get_enabled_payment_gateways();

	if ( ! array_key_exists( $purchase_data['gateway'], $enabled_gateways ) ) {
		foreach ( $purchase_data['fooditems'] as $fooditem ) {
			$options = isset( $fooditem['options'] ) ? $fooditem['options'] : array();

			$options['quantity'] = isset( $fooditem['quantity'] ) ? $fooditem['quantity'] : 1;
			rpress_add_to_cart( $fooditem['id'], $options );
		}

		rpress_set_error( 'rpress-straight-to-gateway-error', __( 'There was an error completing your order. Please try again.', 'restropress' ) );
		wp_redirect( rpress_get_checkout_uri() );
		exit;
	}

	rpress_set_purchase_session( $purchase_data );
	rpress_send_to_gateway( $purchase_data['gateway'], $purchase_data );
}
add_action( 'rpress_straight_to_gateway', 'rpress_process_straight_to_gateway' );


function rpress_check_minimum_order_amount() {
	$enable_minimum_order = rpress_get_option('allow_minimum_order');

	if( $enable_minimum_order ) :
		$minimum_order_price = rpress_get_option('minimum_order_price');
		$minimum_price_error = rpress_get_option('minimum_order_error') !== '' ? rpress_get_option('minimum_order_error') : 'Please add more items';

		$minimum_order_formatted = rpress_currency_filter( rpress_format_amount( $minimum_order_price ) );
		$minimum_price_error = str_replace('{min_order_price}', $minimum_order_formatted, $minimum_price_error);

		if( rpress_get_cart_total() < $minimum_order_price ) :
			rpress_set_error( 'rpress_checkout_error', $minimum_price_error );
		endif;

	endif;

}
