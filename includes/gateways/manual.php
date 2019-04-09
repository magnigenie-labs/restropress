<?php
/**
 * Manual Gateway
 *
 * @package     RPRESS
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Manual Gateway does not need a CC form, so remove it.
 *
 * @since 1.0
 * @return void
 */
add_action( 'rpress_manual_cc_form', '__return_false' );

/**
 * Processes the purchase data and uses the Manual Payment gateway to record
 * the transaction in the Order History
 *
 * @since 1.0
 * @param array $purchase_data Purchase Data
 * @return void
*/
function rpress_manual_payment( $purchase_data ) {
	if( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'rpress-gateway' ) ) {
		wp_die( __( 'Nonce verification has failed', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	/*
	* Purchase data comes in like this
	*
	$purchase_data = array(
		'fooditems' => array of fooditem IDs,
		'price' => total price of cart contents,
		'purchase_key' =>  // Random key
		'user_email' => $user_email,
		'date' => date('Y-m-d H:i:s'),
		'user_id' => $user_id,
		'post_data' => $_POST,
		'user_info' => array of user's information and used discount code
		'cart_details' => array of cart details,
	);
	*/

	$payment_data = array(
		'price' 		=> $purchase_data['price'],
		'date' 			=> $purchase_data['date'],
		'user_email' 	=> $purchase_data['user_email'],
		'purchase_key' 	=> $purchase_data['purchase_key'],
		'currency' 		=> rpress_get_currency(),
		'fooditems' 	=> $purchase_data['fooditems'],
		'user_info' 	=> $purchase_data['user_info'],
		'cart_details' 	=> $purchase_data['cart_details'],
		'status' 		=> 'pending'
	);

	// Record the pending payment
	$payment = rpress_insert_payment( $payment_data );

	if ( $payment ) {
		rpress_update_payment_status( $payment, 'processing' );
		// Empty the shopping cart
		rpress_empty_cart();
		rpress_send_to_success_page();
	} else {
		rpress_record_gateway_error( __( 'Payment Error', 'restropress' ), sprintf( __( 'Payment creation failed while processing a manual (free or test) order. Payment data: %s', 'restropress' ), json_encode( $payment_data ) ), $payment );
		// If errors are present, send the user back to the purchase page so they can be corrected
		rpress_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['rpress-gateway'] );
	}
}
add_action( 'rpress_gateway_manual', 'rpress_manual_payment' );