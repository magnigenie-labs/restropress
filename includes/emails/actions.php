<?php
/**
 * Email Actions
 *
 * @package     RPRESS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Triggers Purchase Receipt to be sent after the payment status is updated
 *
 * @since 1.0
 * @since 1.0.0 - Add parameters for RPRESS_Payment and RPRESS_Customer object.
 *
 * @param int          $payment_id Payment ID.
 * @param RPRESS_Payment  $payment    Payment object for payment ID.
 * @param RPRESS_Customer $customer   Customer object for associated payment.
 * @return void
 */
function rpress_trigger_purchase_receipt( $payment_id = 0, $payment = null, $customer = null ) {
	// Make sure we don't send a purchase receipt while editing a payment
	if ( isset( $_POST['rpress-action'] ) && 'edit_payment' == $_POST['rpress-action'] ) {
		return;
	}

	// Send email with secure fooditem link
	rpress_email_purchase_receipt( $payment_id, true, '', $payment );
}
add_action( 'rpress_complete_purchase', 'rpress_trigger_purchase_receipt', 999, 3 );

/**
 * Resend the Email Purchase Receipt. (This can be done from the Payment History page)
 *
 * @since 1.0
 * @param array $data Payment Data
 * @return void
 */
function rpress_resend_purchase_receipt( $data ) {

	$purchase_id = absint( $data['purchase_id'] );

	if( empty( $purchase_id ) ) {
		return;
	}

	if( ! current_user_can( 'edit_shop_payments' ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$email = ! empty( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : '';

	if( empty( $email ) ) {
		$customer = new RPRESS_Customer( rpress_get_payment_customer_id( $purchase_id ) );
		$email    = $customer->email;
	}

	rpress_email_purchase_receipt( $purchase_id, false, $email );

	// Grab all fooditems of the purchase and update their file fooditem limits, if needed
	// This allows admins to resend purchase receipts to grant additional file fooditems
	$fooditems = rpress_get_payment_meta_cart_details( $purchase_id, true );

	wp_redirect( add_query_arg( array( 'rpress-message' => 'email_sent', 'rpress-action' => false, 'purchase_id' => false ) ) );
	exit;
}
add_action( 'rpress_email_links', 'rpress_resend_purchase_receipt' );

/**
 * Trigger the sending of a Test Email
 *
 * @since 1.0
 * @param array $data Parameters sent from Settings page
 * @return void
 */
function rpress_send_test_email( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'rpress-test-email' ) ) {
		return;
	}

	// Send a test email
	rpress_email_test_purchase_receipt();

	// Remove the test email query arg
	wp_redirect( remove_query_arg( 'rpress_action' ) ); exit;
}
add_action( 'rpress_send_test_email', 'rpress_send_test_email' );

//Send notification to customer
function send_customer_purchase_notification( $payment_id, $new_status ) {

    $order_status = rpress_get_option( $new_status );
    $check_notification_enabled = isset( $order_status['enable_notification'] ) ? true : false;

    if ( !empty( $payment_id ) && $check_notification_enabled && $new_status !== 'pending' ) {
        $customer = new RPRESS_Customer( rpress_get_payment_customer_id( $payment_id ) );
        $email    = $customer->email;
        rpress_email_purchase_receipt( $payment_id, false, $email, null, $new_status );
    }
}
add_action( 'rpress_update_order_status', 'send_customer_purchase_notification' , 10, 2 );
