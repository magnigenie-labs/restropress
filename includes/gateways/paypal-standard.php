<?php
/**
 * PayPal Standard Gateway
 *
 * @package     RPRESS
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, MagniGenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PayPal Remove CC Form
 *
 * PayPal Standard does not need a CC form, so remove it.
 *
 * @access private
 * @since 1.0
 */
add_action( 'rpress_paypal_cc_form', '__return_false' );

/**
 * Register the PayPal Standard gateway subsection
 *
 * @since  1.0
 * @param  array $gateway_sections  Current Gateway Tab subsections
 * @return array                    Gateway subsections with PayPal Standard
 */
function rpress_register_paypal_gateway_section( $gateway_sections ) {
	$gateway_sections['paypal'] = __( 'PayPal Standard', 'restropress' );

	return $gateway_sections;
}
add_filter( 'rpress_settings_sections_gateways', 'rpress_register_paypal_gateway_section', 1, 1 );

/**
 * Registers the PayPal Standard settings for the PayPal Standard subsection
 *
 * @since  1.0
 * @param  array $gateway_settings  Gateway tab settings
 * @return array                    Gateway tab settings with the PayPal Standard settings
 */
function rpress_register_paypal_gateway_settings( $gateway_settings ) {

		$paypal_settings = array (
			'paypal_settings' => array(
				'id'   => 'paypal_settings',
				'name' => '<strong>' . __( 'PayPal Standard Settings', 'restropress' ) . '</strong>',
				'type' => 'header',
			),
			'paypal_email' => array(
				'id'   => 'paypal_email',
				'name' => __( 'PayPal Email', 'restropress' ),
				'desc' => __( 'Enter your PayPal account\'s email', 'restropress' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'paypal_id' => array(
				'id'   => 'paypal_id',
				'name' => __( 'PayPal ID', 'restropress' ),
				'desc' => __( 'Enter your PayPal account\'s ID', 'restropress' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'paypal_image_url' => array(
				'id'   => 'paypal_image_url',
				'name' => __( 'PayPal Image URL', 'restropress' ),
				'desc' => __( 'Upload an image to display on the PayPal checkout page.', 'restropress' ),
				'type' => 'upload',
				'size' => 'regular',
			),
		);

		$pdt_desc = sprintf(
			__( 'Enter your PayPal Identity Token in order to enable Payment Data Transfer (PDT). This allows payments to be verified without relying on the PayPal IPN. See our <a href="%s" target="_blank">documentation</a> for further information.', 'restropress' ),
			'https://docs.restropress.com/docs/restropress/payment-gateway/paypal-gateway/'
		);

		$paypal_settings['paypal_identify_token'] = array(
			'id'   => 'paypal_identity_token',
			'name' => __( 'PayPal Identity Token', 'restropress' ),
			'type' => 'text',
			'desc' => $pdt_desc,
			'size' => 'regular',
		);

		$disable_ipn_desc = __( 'If you are unable to use Payment Data Transfer and payments are not getting marked as complete, then check this box. This forces the site to use a slightly less secure method of verifying purchases.', 'restropress' );

		$paypal_settings['disable_paypal_verification'] = array(
			'id'   => 'disable_paypal_verification',
			'name' => __( 'Disable PayPal IPN Verification', 'restropress' ),
			'desc' => $disable_ipn_desc,
			'type' => 'checkbox',
		);

		$api_key_settings = array(
			'paypal_api_keys_desc' => array(
				'id'   => 'paypal_api_keys_desc',
				'name' => __( 'API Credentials', 'restropress' ),
				'type' => 'descriptive_text',
				'desc' => sprintf(
					__( 'API credentials are necessary to process PayPal refunds from inside WordPress. These can be obtained from <a href="%s" target="_blank">your PayPal account</a>.', 'restropress' ),
					'https://developer.paypal.com/docs/classic/api/apiCredentials/#creating-an-api-signature'
				)
			),
			'paypal_live_api_username' => array(
				'id'   => 'paypal_live_api_username',
				'name' => __( 'Live API Username', 'restropress' ),
				'desc' => __( 'Your PayPal live API username. ', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_live_api_password' => array(
				'id'   => 'paypal_live_api_password',
				'name' => __( 'Live API Password', 'restropress' ),
				'desc' => __( 'Your PayPal live API password.', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_live_api_signature' => array(
				'id'   => 'paypal_live_api_signature',
				'name' => __( 'Live API Signature', 'restropress' ),
				'desc' => __( 'Your PayPal live API signature.', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_username' => array(
				'id'   => 'paypal_test_api_username',
				'name' => __( 'Test API Username', 'restropress' ),
				'desc' => __( 'Your PayPal test API username.', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_password' => array(
				'id'   => 'paypal_test_api_password',
				'name' => __( 'Test API Password', 'restropress' ),
				'desc' => __( 'Your PayPal test API password.', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'paypal_test_api_signature' => array(
				'id'   => 'paypal_test_api_signature',
				'name' => __( 'Test API Signature', 'restropress' ),
				'desc' => __( 'Your PayPal test API signature.', 'restropress' ),
				'type' => 'text',
				'size' => 'regular'
			)
		);

		$paypal_settings = array_merge( $paypal_settings, $api_key_settings );

		$paypal_settings            = apply_filters( 'rpress_paypal_settings', $paypal_settings );
		$gateway_settings['paypal'] = $paypal_settings;

		return $gateway_settings;
}
add_filter( 'rpress_settings_gateways', 'rpress_register_paypal_gateway_settings', 1, 1 );


/**
 * Process PayPal Purchase
 *
 * @since 1.0
 * @param array   $purchase_data Purchase Data
 * @return void
 */
function rpress_process_paypal_purchase( $purchase_data ) {


	if( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'rpress-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	// Collect payment data
	$payment_data = array(
		'price'         => $purchase_data['price'],
		'date'          => $purchase_data['date'],
		'user_email'    => $purchase_data['user_email'],
		'purchase_key'  => $purchase_data['purchase_key'],
		'currency'      => rpress_get_currency(),
		'fooditems'     => $purchase_data['fooditems'],
		'user_info'     => $purchase_data['user_info'],
		'cart_details'  => $purchase_data['cart_details'],
		'gateway'       => 'paypal',
		'status'        => ! empty( $purchase_data['buy_now'] ) ? 'private' : 'pending'
	);

	// Record the pending payment
	$payment = rpress_insert_payment( $payment_data );

	// Check payment
	if ( ! $payment ) {
		// Record the error
		rpress_record_gateway_error( __( 'Payment Error', 'restropress' ), sprintf( __( 'Payment creation failed before sending buyer to PayPal. Payment data: %s', 'restropress' ), json_encode( $payment_data ) ), $payment );
		// Problems? send back
		rpress_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['rpress-gateway'] );
	} else {
		// Only send to PayPal if the pending payment is created successfully
		$listener_url = add_query_arg( 'rpress-listener', 'IPN', home_url( 'index.php' ) );

		// Set the session data to recover this payment in the event of abandonment or error.
		RPRESS()->session->set( 'rpress_resume_payment', $payment );

		// Get the success url
		$return_url = add_query_arg( array(
				'payment-confirmation' => 'paypal',
				'payment-id' => $payment
			), get_permalink( rpress_get_option( 'success_page', false ) ) );

		// Get the PayPal redirect uri
		$paypal_redirect = trailingslashit( rpress_get_paypal_redirect() ) . '?';

		// Setup PayPal arguments
		$paypal_args = array(
			'business'      => rpress_get_option( 'paypal_email', false ),
			'email'         => $purchase_data['user_email'],
			'first_name'    => $purchase_data['user_info']['first_name'],
			'last_name'     => $purchase_data['user_info']['last_name'],
			'invoice'       => $purchase_data['purchase_key'],
			'no_shipping'   => '1',
			'shipping'      => '0',
			'no_note'       => '1',
			'currency_code' => rpress_get_currency(),
			'charset'       => get_bloginfo( 'charset' ),
			'custom'        => $payment,
			'rm'            => '2',
			'return'        => $return_url,
			'cancel_return' => rpress_get_failed_transaction_uri( '?payment-id=' . $payment ),
			'notify_url'    => $listener_url,
			'image_url'     => rpress_get_paypal_image_url(),
			'cbt'           => get_bloginfo( 'name' ),
			'bn'            => 'RestroPress_SP'
		);

		if ( ! empty( $purchase_data['user_info']['address'] ) ) {
			$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
			$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
			$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
			$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
		}

		$paypal_extra_args = array(
			'cmd'    => '_cart',
			'upload' => '1'
		);

		$paypal_args = array_merge( $paypal_extra_args, $paypal_args );

		// Add cart items
		$i = 1;
		$paypal_sum = 0;
		if( is_array( $purchase_data['cart_details'] ) && ! empty( $purchase_data['cart_details'] ) ) {
			foreach ( $purchase_data['cart_details'] as $item ) {

				$item_amount = round( ( $item['subtotal'] / $item['quantity'] ) - ( $item['discount'] / $item['quantity'] ), 2 );

				if( $item_amount <= 0 ) {
					$item_amount = 0;
				}

				$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( rpress_get_cart_item_name( $item ), ENT_COMPAT, 'UTF-8' ) );
				$paypal_args['quantity_' . $i ]  = $item['quantity'];
				$paypal_args['amount_' . $i ]    = $item_amount;

				$paypal_sum += ( $item_amount * $item['quantity'] );

				$i++;

			}
		}

		// Calculate discount
		$discounted_amount = 0.00;
		if ( ! empty( $purchase_data['fees'] ) ) {
			$i = empty( $i ) ? 1 : $i;
			foreach ( $purchase_data['fees'] as $fee ) {
				if ( empty( $fee['fooditem_id'] ) && floatval( $fee['amount'] ) > '0' ) {
					// this is a positive fee
					$paypal_args['item_name_' . $i ] = stripslashes_deep( html_entity_decode( wp_strip_all_tags( $fee['label']."\n" ), ENT_COMPAT, 'UTF-8' ) );
					$paypal_args['quantity_' . $i ]  = '1';
					$paypal_args['amount_' . $i ]    = rpress_sanitize_amount( $fee['amount'] );
					$i++;
				} else if ( empty( $fee['fooditem_id'] ) ) {

					// This is a negative fee (discount) not assigned to a specific fooditem
					$discounted_amount += abs( $fee['amount'] );
				}
			}
		}

		if ( $discounted_amount > '0' ) {
			$paypal_args['discount_amount_cart'] = rpress_sanitize_amount( $discounted_amount );
		}

		if( $paypal_sum > $purchase_data['price'] ) {
			$difference = round( $paypal_sum - $purchase_data['price'], 2 );
			if( ! isset( $paypal_args['discount_amount_cart'] ) ) {
				$paypal_args['discount_amount_cart'] = 0;
			}
			$paypal_args['discount_amount_cart'] += $difference;
		}

		// Add taxes to the cart
		if ( rpress_use_taxes() ) {

			$paypal_args['tax_cart'] = rpress_sanitize_amount( $purchase_data['tax'] );

		}

		$paypal_args = apply_filters( 'rpress_paypal_redirect_args', $paypal_args, $purchase_data );

		rpress_debug_log( 'PayPal arguments: ' . print_r( $paypal_args, true ) );

		// Build query
		$paypal_redirect .= http_build_query( $paypal_args );

		// Fix for some sites that encode the entities
		$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );
		// Redirect to PayPal
		wp_redirect( $paypal_redirect );
		exit;
	}

}
add_action( 'rpress_gateway_paypal', 'rpress_process_paypal_purchase' );

/**
 * Listens for a PayPal IPN requests and then sends to the processing function
 *
 * @since 1.0
 * @return void
 */
function rpress_listen_for_paypal_ipn() {

	// print_r($_REQUEST);exit;
	// Regular PayPal IPN
	if ( isset( $_GET['payment-confirmation'] ) && 'paypal' === strtolower( $_GET['payment-confirmation'] ) ) {

		rpress_debug_log( 'PayPal IPN endpoint loaded' );

		/**
		 * This is necessary to delay execution of PayPal PDT and to avoid a race condition causing the order status
		 * updates to be triggered twice.
		 *
		 */
		$token = rpress_get_option( 'paypal_identity_token' );
		if ( $token ) {
			sleep( 8 );
		}

		do_action( 'rpress_verify_paypal_ipn' );
	}
}
add_action( 'init', 'rpress_listen_for_paypal_ipn' );

/**
 * Process PayPal IPN
 *
 * @since 1.0
 * @return void
 */
function rpress_process_paypal_ipn() {
	// Check the request method is POST
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		return;
	}

	rpress_debug_log( 'rpress_process_paypal_ipn() running during PayPal IPN processing' );

	// Set initial post data to empty string
	$post_data = '';

	// Start the encoded data collection with notification command
	$encoded_data = 'cmd=_notify-validate';

	// Get current arg separator
	$arg_separator = rpress_get_php_arg_separator_output();

	// Verify there is a post_data
	if ( $post_data || strlen( $post_data ) > 0 ) {
		// Append the data
		$encoded_data .= $arg_separator . $post_data;
	} else {
		// Check if POST is empty
		if ( empty( $_POST ) ) {
			// Nothing to do
			return;
		} else {
			$data = rpress_sanitize_array( $_POST );
			// Loop through each POST
			foreach ( $data as $key => $value ) {
				// Encode the value and append the data
				$encoded_data .= $arg_separator . "$key=" . urlencode( $value );
			}
		}
	}

	// Convert collected post data to an array
	parse_str( $encoded_data, $encoded_data_array );

	foreach ( $encoded_data_array as $key => $value ) {

		if ( false !== strpos( $key, 'amp;' ) ) {
			$new_key = str_replace( '&amp;', '&', $key );
			$new_key = str_replace( 'amp;', '&', $new_key );

			unset( $encoded_data_array[ $key ] );
			$encoded_data_array[ $new_key ] = $value;
		}

	}

	/**
	 * PayPal Web IPN Verification
	 *
	 * Allows filtering the IPN Verification data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 2.8.13
	 *
	 * @param array $data      The PayPal Web Accept Data
	 */
	$encoded_data_array = apply_filters( 'rpress_process_paypal_ipn_data', $encoded_data_array );

	rpress_debug_log( 'encoded_data_array data array: ' . print_r( $encoded_data_array, true ) );

	if ( ! rpress_get_option( 'disable_paypal_verification' ) ) {

		// Validate the IPN
		$host = rpress_is_test_mode() ? 'sandbox.paypal.com' : 'www.paypal.com';
		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => $host,
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',
				'user-agent'   => 'RPRESS IPN Verification/' . RP_VERSION . '; ' . get_bloginfo( 'url' )

			),
			'sslverify'   => false,
			'body'        => $encoded_data_array
		);

		rpress_debug_log( 'Attempting to verify PayPal IPN. Data sent for verification: ' . print_r( $remote_post_vars, true ) );

		// Get response
		$api_response = wp_remote_post( rpress_get_paypal_redirect( true, true ), $remote_post_vars );

		if ( is_wp_error( $api_response ) ) {
			rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'restropress' ), json_encode( $api_response ) ) );
			rpress_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) );

			return; // Something went wrong
		}

		if ( wp_remote_retrieve_body( $api_response ) !== 'VERIFIED' && rpress_get_option( 'disable_paypal_verification', false ) ) {
			rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid IPN verification response. IPN data: %s', 'restropress' ), json_encode( $api_response ) ) );
			rpress_debug_log( 'Invalid IPN verification response. IPN data: ' . print_r( $api_response, true ) );

			return; // Response not okay
		}

		rpress_debug_log( 'IPN verified successfully' );
	}

	// Check if $post_data_array has been populated
	if ( ! is_array( $encoded_data_array ) && ! empty( $encoded_data_array ) ) {
		return;
	}

	$defaults = array(
		'txn_type'       => '',
		'payment_status' => ''
	);

	$encoded_data_array = wp_parse_args( $encoded_data_array, $defaults );

	$payment_id = 0;

	if ( ! empty( $encoded_data_array[ 'parent_txn_id' ] ) ) {
		$payment_id = rpress_get_purchase_id_by_transaction_id( $encoded_data_array[ 'parent_txn_id' ] );
	} elseif ( ! empty( $encoded_data_array[ 'txn_id' ] ) ) {
		$payment_id = rpress_get_purchase_id_by_transaction_id( $encoded_data_array[ 'txn_id' ] );
	}

	if ( empty( $payment_id ) ) {
		$payment_id = ! empty( $encoded_data_array[ 'custom' ] ) ? absint( $encoded_data_array[ 'custom' ] ) : 0;
	}

	if ( has_action( 'rpress_paypal_' . $encoded_data_array['txn_type'] ) ) {
		// Allow PayPal IPN types to be processed separately
		do_action( 'rpress_paypal_' . $encoded_data_array['txn_type'], $encoded_data_array, $payment_id );
	} else {
		// Fallback to web accept just in case the txn_type isn't present
		do_action( 'rpress_paypal_web_accept', $encoded_data_array, $payment_id );
	}
	exit;
}
add_action( 'rpress_verify_paypal_ipn', 'rpress_process_paypal_ipn' );

/**
 * Process web accept (one time) payment IPNs
 *
 * @since 1.0
 * @param array   $data IPN Data
 * @return void
 */
function rpress_process_paypal_web_accept_and_cart( $data, $payment_id ) {
	/**
	 * PayPal Web Accept Data
	 *
	 * Allows filtering the Web Accept data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 1.0
	 *
	 * @param array $data      The PayPal Web Accept Data
	 * @param int  $payment_id The Payment ID associated with this IPN request
	 */
	$data = apply_filters( 'rpress_paypal_web_accept_and_cart_data', $data, $payment_id );

	if ( $data['txn_type'] != 'web_accept' && $data['txn_type'] != 'cart' && $data['payment_status'] != 'Refunded' ) {
		return;
	}

	if( empty( $payment_id ) ) {
		return;
	}

	$payment = new RPRESS_Payment( $payment_id );

	// Collect payment details
	$purchase_key   = isset( $data['invoice'] ) ? $data['invoice'] : $data['item_number'];
	$paypal_amount  = $data['mc_gross'];
	$payment_status = strtolower( $data['payment_status'] );
	$currency_code  = strtolower( $data['mc_currency'] );
	$receiver_id = isset( $data['receiver_id'] );


	if ( $payment->gateway != 'paypal' ) {
		return; // this isn't a PayPal standard IPN
	}

	// Verify payment recipient
	if ( $receiver_id != trim( rpress_get_option( 'paypal_id', true ) ) ) {
		rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid business email in IPN response. IPN data: %s', 'restropress' ), json_encode( $data ) ), $payment_id );
		rpress_debug_log( 'Invalid business email in IPN response. IPN data: ' . print_r( $data, true ) );
		rpress_update_payment_status( $payment_id, 'failed' );
		rpress_insert_payment_note( $payment_id, __( 'Payment failed due to invalid PayPal business email.', 'restropress' ) );
		return;
	}

	// Verify payment currency
	if ( $currency_code != strtolower( $payment->currency ) ) {

		rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid currency in IPN response. IPN data: %s', 'restropress' ), json_encode( $data ) ), $payment_id );
		rpress_debug_log( 'Invalid currency in IPN response. IPN data: ' . print_r( $data, true ) );
		rpress_update_payment_status( $payment_id, 'failed' );
		rpress_insert_payment_note( $payment_id, __( 'Payment failed due to invalid currency in PayPal IPN.', 'restropress' ) );
		return;
	}

	if ( empty( $payment->email ) ) {

		// This runs when a Buy Now purchase was made. It bypasses checkout so no personal info is collected until PayPal

		// Setup and store the customers's details
		$address = array();
		$address['line1']    = ! empty( $data['address_street']       ) ? sanitize_text_field( $data['address_street'] )       : false;
		$address['city']     = ! empty( $data['address_city']         ) ? sanitize_text_field( $data['address_city'] )         : false;
		$address['state']    = ! empty( $data['address_state']        ) ? sanitize_text_field( $data['address_state'] )        : false;
		$address['country']  = ! empty( $data['address_country_code'] ) ? sanitize_text_field( $data['address_country_code'] ) : false;
		$address['zip']      = ! empty( $data['address_zip']          ) ? sanitize_text_field( $data['address_zip'] )          : false;

		$payment->email      = sanitize_text_field( $data['payer_email'] );
		$payment->first_name = sanitize_text_field( $data['first_name'] );
		$payment->last_name  = sanitize_text_field( $data['last_name'] );
		$payment->address    = $address;

		if( empty( $payment->customer_id ) ) {

			$customer = new RPRESS_Customer( $payment->email );
			if( ! $customer || $customer->id < 1 ) {

				$customer->create( array(
					'email'   => $payment->email,
					'name'    => $payment->first_name . ' ' . $payment->last_name,
					'user_id' => $payment->user_id
				) );

			}

			$payment->customer_id = $customer->id;
		}

		$payment->save();

	}

	if( empty( $customer ) ) {

		$customer = new RPRESS_Customer( $payment->customer_id );

	}

	// Record the payer email on the RPRESS_Customer record if it is different than the email entered on checkout
	if( ! empty( $data['payer_email'] ) && ! in_array( strtolower( $data['payer_email'] ), array_map( 'strtolower', $customer->emails ) ) ) {

		$customer->add_email( strtolower( $data['payer_email'] ) );

	}


	if ( $payment_status == 'refunded' || $payment_status == 'reversed' ) {

		// Process a refund
		rpress_process_paypal_refund( $data, $payment_id );

	} else {
		if ( get_post_status( $payment_id ) == 'publish' ) {
			return; // Only complete payments once
		}

		// Retrieve the total purchase amount (before PayPal)
		$payment_amount = rpress_get_payment_amount( $payment_id );
		if ( number_format( (float) $paypal_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {
			// The prices don't match
			rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid payment amount in IPN response. IPN data: %s', 'restropress' ), json_encode( $data ) ), $payment_id );
			rpress_debug_log( 'Invalid payment amount in IPN response. IPN data: ' . printf( $data, true ) );
			rpress_update_payment_status( $payment_id, 'failed' );
			rpress_insert_payment_note( $payment_id, __( 'Payment failed due to invalid amount in PayPal IPN.', 'restropress' ) );
			return;
		}
		if ( $purchase_key != rpress_get_payment_key( $payment_id ) ) {
			// Purchase keys don't match
			rpress_debug_log( 'Invalid purchase key in IPN response. IPN data: ' . printf( $data, true ) );
			rpress_record_gateway_error( __( 'IPN Error', 'restropress' ), sprintf( __( 'Invalid purchase key in IPN response. IPN data: %s', 'restropress' ), json_encode( $data ) ), $payment_id );
			rpress_update_payment_status( $payment_id, 'failed' );
			rpress_insert_payment_note( $payment_id, __( 'Payment failed due to invalid purchase key in PayPal IPN.', 'restropress' ) );
			return;
		}


		if ( 'completed' == $payment_status || rpress_is_test_mode() ) {

			rpress_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'restropress' ) , $data['txn_id'] ) );
			rpress_set_payment_transaction_id( $payment_id, $data['txn_id'] );
			rpress_update_payment_status( $payment_id, 'publish' );

			$sucess_url = add_query_arg( array(
				'payment_key' => $purchase_key,
			), get_permalink( rpress_get_option( 'success_page', false ) ) );
			wp_redirect($sucess_url);

		} else if ( 'pending' == $payment_status && isset( $data['pending_reason'] ) ) {

			// Look for possible pending reasons, such as an echeck

			$note = '';

			switch( strtolower( $data['pending_reason'] ) ) {

				case 'echeck' :

					$note = __( 'Payment made via eCheck and will clear automatically in 5-8 days', 'restropress' );
					$payment->status = 'processing';
					$payment->save();
					break;

				case 'address' :

					$note = __( 'Payment requires a confirmed customer address and must be accepted manually through PayPal', 'restropress' );

					break;

				case 'intl' :

					$note = __( 'Payment must be accepted manually through PayPal due to international account regulations', 'restropress' );

					break;

				case 'multi-currency' :

					$note = __( 'Payment received in non-shop currency and must be accepted manually through PayPal', 'restropress' );

					break;

				case 'paymentreview' :
				case 'regulatory_review' :

					$note = __( 'Payment is being reviewed by PayPal staff as high-risk or in possible violation of government regulations', 'restropress' );

					break;

				case 'unilateral' :

					$note = __( 'Payment was sent to non-confirmed or non-registered email address.', 'restropress' );

					break;

				case 'upgrade' :

					$note = __( 'PayPal account must be upgraded before this payment can be accepted', 'restropress' );

					break;

				case 'verify' :

					$note = __( 'PayPal account is not verified. Verify account in order to accept this payment', 'restropress' );

					break;

				case 'other' :

					$note = __( 'Payment is pending for unknown reasons. Contact PayPal support for assistance', 'restropress' );

					break;

			}

			if( ! empty( $note ) ) {

				rpress_debug_log( 'Payment not marked as completed because: ' . $note );
				rpress_insert_payment_note( $payment_id, $note );

			}

		}
	}
}
add_action( 'rpress_paypal_web_accept', 'rpress_process_paypal_web_accept_and_cart', 10, 2 );

/**
 * Process PayPal IPN Refunds
 *
 * @since 1.0
 * @param array   $data IPN Data
 * @return void
 */
function rpress_process_paypal_refund( $data, $payment_id = 0 ) {

	/**
	 * PayPal Process Refund Data
	 *
	 * Allows filtering the Refund data that PayPal passes back in via IPN with PayPal Standard
	 *
	 * @since 1.0
	 *
	 * @param array $data      The PayPal Refund data
	 * @param int  $payment_id The Payment ID associated with this IPN request
	 */
	$data = apply_filters( 'rpress_process_paypal_refund_data', $data, $payment_id );

	// Collect payment details
	if( empty( $payment_id ) ) {
		return;
	}

	if ( get_post_status( $payment_id ) == 'refunded' ) {
		return; // Only refund payments once
	}

	$payment_amount = rpress_get_payment_amount( $payment_id );
	$refund_amount  = $data['mc_gross'] * -1;

	if ( number_format( (float) $refund_amount, 2 ) < number_format( (float) $payment_amount, 2 ) ) {

		rpress_insert_payment_note( $payment_id, sprintf( __( 'Partial PayPal refund processed: %s', 'restropress' ), $data['parent_txn_id'] ) );
		return; // This is a partial refund

	}

	rpress_insert_payment_note( $payment_id, sprintf( __( 'PayPal Payment #%s Refunded for reason: %s', 'restropress' ), $data['parent_txn_id'], $data['reason_code'] ) );
	rpress_insert_payment_note( $payment_id, sprintf( __( 'PayPal Refund Transaction ID: %s', 'restropress' ), $data['txn_id'] ) );
	rpress_update_payment_status( $payment_id, 'refunded' );
}

/**
 * Get PayPal Redirect
 *
 * @since 1.0
 * @param bool    $ssl_check Is SSL?
 * @param bool    $ipn       Is this an IPN verification check?
 * @return string
 */
function rpress_get_paypal_redirect( $ssl_check = false, $ipn = false ) {

	$protocol = 'http://';
	if ( is_ssl() || ! $ssl_check ) {
		$protocol = 'https://';
	}

	// Check the current payment mode
	if ( rpress_is_test_mode() ) {

		// Test mode

		if( $ipn ) {

			$paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

		} else {

			$paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';

		}

	} else {

		// Live mode

		if( $ipn ) {

			$paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';

		} else {

			$paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';

		}

	}

	return apply_filters( 'rpress_paypal_uri', $paypal_uri, $ssl_check, $ipn );
}

/**
 * Get the image for the PayPal purchase page.
 *
 * @since 1.0
 * @return string
 */
function rpress_get_paypal_image_url() {
	$image_url = trim( rpress_get_option( 'paypal_image_url', '' ) );
	return apply_filters( 'rpress_paypal_image_url', $image_url );
}

/**
 * Shows "Purchase Processing" message for PayPal payments are still pending on site return.
 *
 * This helps address the Race Condition, as detailed in issue #1839
 *
 * @since 1.0
 * @return string
 */
function rpress_paypal_success_page_content( $content ) {

	if ( ! isset( $_GET['payment-id'] ) && ! rpress_get_purchase_session() ) {
		return $content;
	}

	rpress_empty_cart();

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if ( ! $payment_id ) {
		$session    = rpress_get_purchase_session();
		$payment_id = rpress_get_purchase_id_by_key( $session['purchase_key'] );
	}

	$payment = new RPRESS_Payment( $payment_id );

	if ( $payment->ID > 0 && 'pending' == $payment->status  ) {

		// Payment is still pending so show processing indicator to fix the Race Condition, issue #
		ob_start();

		rpress_get_template_part( 'payment', 'processing' );

		$content = ob_get_clean();

	}

	return $content;

}
add_filter( 'rpress_payment_confirm_paypal', 'rpress_paypal_success_page_content' );

/**
 * Mark payment as complete on return from PayPal if a PayPal Identity Token is present.
 *
 * @since 1.0
 * @return void
 */
function rpress_paypal_process_pdt_on_return() {

	if ( ! isset( $_GET['payment-id'] ) || ! isset( $_GET['tx'] ) ) {
		return;
	}

	$token = rpress_get_option( 'paypal_identity_token' );

	if( ! rpress_is_success_page() || ! $token || ! rpress_is_gateway_active( 'paypal' ) ) {
		return;
	}

	$payment_id = isset( $_GET['payment-id'] ) ? absint( $_GET['payment-id'] ) : false;

	if( empty( $payment_id ) ) {
		return;
	}

	$purchase_session = rpress_get_purchase_session();
	$payment          = new RPRESS_Payment( $payment_id );

	// If there is no purchase session, don't try and fire PDT.
	if ( empty( $purchase_session ) ) {
		return;
	}

	// Do not fire a PDT verification if the purchase session does not match the payment-id PDT is asking to verify.
	if ( ! empty( $purchase_session['purchase_key'] ) && $payment->key !== $purchase_session['purchase_key'] ) {
		return;
	}

	if( $token && ! empty( $_GET['tx'] ) && $payment->ID > 0 ) {

		// An identity token has been provided in settings so let's immediately verify the purchase
		$host = rpress_is_test_mode() ? 'sandbox.paypal.com' : 'www.paypal.com';
		$remote_post_vars = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'headers'     => array(
				'host'         => $host,
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
				'post'         => '/cgi-bin/webscr HTTP/1.1',
				'user-agent'   => 'RPRESS PDT Verification/' . RP_VERSION . '; ' . get_bloginfo( 'url' )

			),
			'sslverify'   => false,
			'body'        => array(
				'tx'  => sanitize_text_field( $_GET['tx'] ),
				'at'  => $token,
				'cmd' => '_notify-synch',
			)
		);

		// Sanitize the data for debug logging.
		$debug_args               = $remote_post_vars;
		$debug_args['body']['at'] = str_pad( substr( $debug_args['body']['at'], -6 ), strlen( $debug_args['body']['at'] ), '*', STR_PAD_LEFT );
		rpress_debug_log( 'Attempting to verify PayPal payment with PDT. Args: ' . print_r( $debug_args, true ) );

		rpress_debug_log( 'Sending PDT Verification request to ' . rpress_get_paypal_redirect() );

		$request = wp_remote_post( rpress_get_paypal_redirect(), $remote_post_vars );

		if ( ! is_wp_error( $request ) ) {

			$body = wp_remote_retrieve_body( $request );

			// parse the data
			$lines = explode( "\n", trim( $body ) );
			$data  = array();
			if ( strcmp ( $lines[0], "SUCCESS" ) == 0 ) {

				for ( $i = 1; $i < count( $lines ); $i++ ) {
					$parsed_line = explode( "=", $lines[ $i ],2 );
					$data[ urldecode( $parsed_line[0] ) ] = urldecode( $parsed_line[1] );
				}

				if ( isset( $data['mc_gross'] ) ) {

					$total = $data['mc_gross'];

				} else if ( isset( $data['payment_gross'] ) ) {

					$total = $data['payment_gross'];

				} else if ( isset( $_REQUEST['amt'] ) ) {

					$total = sanitize_text_field( $_REQUEST['amt'] ) ;

				} else {

					$total = null;

				}

				if ( is_null( $total ) ) {

					rpress_debug_log( 'Attempt to verify PayPal payment with PDT failed due to payment total missing' );
					$payment->add_note( __( 'Payment could not be verified while validating PayPal PDT. Missing payment total fields.', 'restropress' ) );
					$payment->status = 'pending';

				} elseif ( (float) $total < (float) $payment->total ) {

					/**
					 * Here we account for payments that are less than the expected results only. There are times that
					 * PayPal will sometimes round and have $0.01 more than the amount. The goal here is to protect store owners
					 * from getting paid less than expected.
					 */
					rpress_debug_log( 'Attempt to verify PayPal payment with PDT failed due to payment total discrepancy' );
					$payment->add_note( sprintf( __( 'Payment failed while validating PayPal PDT. Amount expected: %f. Amount Received: %f', 'restropress' ), $payment->total, $data['payment_gross'] ) );
					$payment->status = 'failed';

				} else {

					// Verify the status
					switch( strtolower( $data['payment_status'] ) ) {

						case 'completed':
							$payment->status = 'publish';
							break;

						case 'failed':
							$payment->status = 'failed';
							break;

						default:
							$payment->status = 'pending';
							break;

					}

				}

				$payment->transaction_id = sanitize_text_field( $_GET['tx'] );
				$payment->save();

			} elseif ( strcmp ( $lines[0], "FAIL" ) == 0 ) {

				rpress_debug_log( 'Attempt to verify PayPal payment with PDT failed due to PDT failure response: ' . print_r( $body, true ) );
				$payment->add_note( __( 'Payment failed while validating PayPal PDT.', 'restropress' ) );
				$payment->status = 'failed';
				$payment->save();

			} else {

				rpress_debug_log( 'Attempt to verify PayPal payment with PDT met with an unexpected result: ' . print_r( $body, true ) );
				$payment->add_note( __( 'PayPal PDT encountered an unexpected result, payment set to pending', 'restropress' ) );
				$payment->status = 'pending';
				$payment->save();

			}

		} else {

			rpress_debug_log( 'Attempt to verify PayPal payment with PDT failed. Request return: ' . print_r( $request, true ) );

		}
	}

}
add_action( 'template_redirect', 'rpress_paypal_process_pdt_on_return' );

/**
 * Given a Payment ID, extract the transaction ID
 *
 * @since  1.0
 * @param  string $payment_id       Payment ID
 * @return string                   Transaction ID
 */
function rpress_paypal_get_payment_transaction_id( $payment_id ) {

	$transaction_id = '';
	$notes = rpress_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^PayPal Transaction ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$transaction_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'rpress_paypal_set_payment_transaction_id', $transaction_id, $payment_id );
}
add_filter( 'rpress_get_payment_transaction_id-paypal', 'rpress_paypal_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the PayPal transaction ID details
 *
 * @since  1.0
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the PayPal transaction details
 */
function rpress_paypal_link_transaction_id( $transaction_id, $payment_id ) {

	$payment = new RPRESS_Payment( $payment_id );
	$sandbox = 'test' == $payment->mode ? 'sandbox.' : '';
	$paypal_base_url = 'https://www.' . $sandbox . 'paypal.com/webscr?cmd=_history-details-from-hub&id=';
	$transaction_url = '<a href="' . esc_url( $paypal_base_url . $transaction_id ) . '" target="_blank">' . $transaction_id . '</a>';

	return apply_filters( 'rpress_paypal_link_payment_details_transaction_id', $transaction_url );

}
add_filter( 'rpress_payment_details_transaction_id-paypal', 'rpress_paypal_link_transaction_id', 10, 2 );

/**
 * Shows checkbox to automatically refund payments made in PayPal.
 *
 * @since  1.0
 *
 * @param int $payment_id The current payment ID.
 * @return void
 */
function rpress_paypal_refund_admin_js( $payment_id = 0 ) {

	// If not the proper gateway, return early.
	if ( 'paypal' !== rpress_get_payment_gateway( $payment_id ) ) {
		return;
	}

	// If our credentials are not set, return early.
	$key       = rpress_get_payment_meta( $payment_id, '_rpress_payment_mode', true );
	$username  = rpress_get_option( 'paypal_' . $key . '_api_username' );
	$password  = rpress_get_option( 'paypal_' . $key . '_api_password' );
	$signature = rpress_get_option( 'paypal_' . $key . '_api_signature' );

	if ( empty( $username ) || empty( $password ) || empty( $signature ) ) {
		return;
	}

	// Localize the refund checkbox label.
	$label = __( 'Refund Payment in PayPal', 'restropress' );

	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('select[name=rpress-payment-status]').change(function() {
				if ( 'refunded' == $(this).val() ) {
					$(this).parent().parent().append('<input type="checkbox" id="rpress-paypal-refund" name="rpress-paypal-refund" value="1" style="margin-top:0">');
					$(this).parent().parent().append('<label for="rpress-paypal-refund"><?php echo esc_html( $label ); ?></label>');
				} else {
					$('#rpress-paypal-refund').remove();
					$('label[for="rpress-paypal-refund"]').remove();
				}
			});
		});
	</script>
	<?php
}
add_action( 'rpress_view_order_details_before', 'rpress_paypal_refund_admin_js', 100 );

/**
 * Possibly refunds a payment made with PayPal Standard or PayPal Express.
 *
 * @since  1.0
 *
 * @param int $payment_id The current payment ID.
 * @return void
 */
function rpress_maybe_refund_paypal_purchase( RPRESS_Payment $payment ) {


	if( ! current_user_can( 'edit_shop_payments', $payment->ID ) ) {
		return;
	}

	if( empty( $_POST['rpress-paypal-refund'] ) ) {
		return;
	}

	$processed = $payment->get_meta( '_rpress_paypal_refunded', true );

	// If the status is not set to "refunded", return early.
	if ( 'publish' !== $payment->old_status && 'revoked' !== $payment->old_status ) {
		return;
	}

	// If not PayPal/PayPal Express, return early.
	if ( 'paypal' !== $payment->gateway ) {
		return;
	}

	// If the payment has already been refunded in the past, return early.
	if ( $processed ) {
		return;
	}

	// Process the refund in PayPal.
	rpress_refund_paypal_purchase( $payment );

}
add_action( 'rpress_pre_refund_payment', 'rpress_maybe_refund_paypal_purchase', 999 );

/**
 * Refunds a purchase made via PayPal.
 *
 * @since  1.0
 *
 * @param object|int $payment The payment ID or object to refund.
 * @return void
 */
function rpress_refund_paypal_purchase( $payment ) {

	if( ! $payment instanceof RPRESS_Payment && is_numeric( $payment ) ) {
		$payment = new RPRESS_Payment( $payment );
	}

	// Set PayPal API key credentials.
	$credentials = array(
		'api_endpoint'  => 'test' == $payment->mode ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp',
		'api_username'  => rpress_get_option( 'paypal_' . $payment->mode . '_api_username' ),
		'api_password'  => rpress_get_option( 'paypal_' . $payment->mode . '_api_password' ),
		'api_signature' => rpress_get_option( 'paypal_' . $payment->mode . '_api_signature' )
	);

	$credentials = apply_filters( 'rpress_paypal_refund_api_credentials', $credentials, $payment );

	$body = array(
		'USER' 			=> $credentials['api_username'],
		'PWD'  			=> $credentials['api_password'],
		'SIGNATURE' 	=> $credentials['api_signature'],
		'VERSION'       => '124',
		'METHOD'        => 'RefundTransaction',
		'TRANSACTIONID' => $payment->transaction_id,
		'REFUNDTYPE'    => 'Full'
	);

	$body = apply_filters( 'rpress_paypal_refund_body_args', $body, $payment );

	// Prepare the headers of the refund request.
	$headers = array(
		'Content-Type'  => 'application/x-www-form-urlencoded',
		'Cache-Control' => 'no-cache'
	);

	$headers = apply_filters( 'rpress_paypal_refund_header_args', $headers, $payment );

	// Prepare args of the refund request.
	$args = array(
		'body' 	      => $body,
		'headers'     => $headers,
		'httpversion' => '1.1'
	);

	$args = apply_filters( 'rpress_paypal_refund_request_args', $args, $payment );

	$error_msg = '';
	$request   = wp_remote_post( $credentials['api_endpoint'], $args );

	if ( is_wp_error( $request ) ) {

		$success   = false;
		$error_msg = $request->get_error_message();

	} else {

		$body    = wp_remote_retrieve_body( $request );
		$code    = wp_remote_retrieve_response_code( $request );
		$message = wp_remote_retrieve_response_message( $request );
		if( is_string( $body ) ) {
			wp_parse_str( $body, $body );
		}

		if( empty( $code ) || 200 !== (int) $code ) {
			$success = false;
		}

		if( empty( $message ) || 'OK' !== $message ) {
			$success = false;
		}

		if( isset( $body['ACK'] ) && 'success' === strtolower( $body['ACK'] ) ) {
			$success = true;
		} else {
			$success = false;
			if( isset( $body['L_LONGMESSAGE0'] ) ) {
				$error_msg = $body['L_LONGMESSAGE0'];
			} else {
				$error_msg = __( 'PayPal refund failed for unknown reason.', 'restropress' );
			}
		}

	}

	if( $success ) {

		// Prevents the PayPal Express one-time gateway from trying to process the refundl
		$payment->update_meta( '_rpress_paypal_refunded', true );
		$payment->add_note( sprintf( __( 'PayPal refund transaction ID: %s', 'restropress' ), $body['REFUNDTRANSACTIONID'] ) );

	} else {

		$payment->add_note( sprintf( __( 'PayPal refund failed: %s', 'restropress' ), $error_msg ) );

	}

	// Run hook letting people know the payment has been refunded successfully.
	do_action( 'rpress_paypal_refund_purchase', $payment );
}
