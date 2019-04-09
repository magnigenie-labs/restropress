<?php
/**
 * Email Functions
 *
 * @package     RPRESS
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email the fooditem link(s) and payment confirmation to the buyer in a
 * customizable Purchase Receipt
 *
 * @since 1.0
 * @since 1.0.0 - Add parameters for RPRESS_Payment and RPRESS_Customer object.
 *
 * @param int          $payment_id   Payment ID
 * @param bool         $admin_notice Whether to send the admin email notification or not (default: true)
 * @param RPRESS_Payment  $payment      Payment object for payment ID.
 * @param RPRESS_Customer $customer     Customer object for associated payment.
 * @return void
 */
function rpress_email_purchase_receipt( $payment_id, $admin_notice = true, $to_email = '', $payment = null, $customer = null ) {
	if ( is_null( $payment ) ) {
		$payment = rpress_get_payment( $payment_id );
	}

	$payment_data = $payment->get_meta( '_rpress_payment_meta', true );

	$from_name    = rpress_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name    = apply_filters( 'rpress_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email   = rpress_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email   = apply_filters( 'rpress_purchase_from_address', $from_email, $payment_id, $payment_data );

	if ( empty( $to_email ) ) {
		$to_email = $payment->email;
	}

	$subject      = rpress_get_option( 'purchase_subject', __( 'Purchase Receipt', 'restropress' ) );
	$subject      = apply_filters( 'rpress_purchase_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject      = wp_specialchars_decode( rpress_do_email_tags( $subject, $payment_id ) );

	$heading      = rpress_get_option( 'purchase_heading', __( 'Purchase Receipt', 'restropress' ) );
	$heading      = apply_filters( 'rpress_purchase_heading', $heading, $payment_id, $payment_data );
	$heading      = rpress_do_email_tags( $heading, $payment_id );

	$attachments  = apply_filters( 'rpress_receipt_attachments', array(), $payment_id, $payment_data );

	$message      = rpress_do_email_tags( rpress_get_email_body_content( $payment_id, $payment_data ), $payment_id );

	$emails = RPRESS()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'rpress_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

	if ( $admin_notice && ! rpress_admin_notices_disabled( $payment_id ) ) {
		do_action( 'rpress_admin_order_notice', $payment_id, $payment_data );
	}
}

/**
 * Email the fooditem link(s) and payment confirmation to the admin accounts for testing.
 *
 * @since 1.0
 * @return void
 */
function rpress_email_test_purchase_receipt() {

	$from_name   = rpress_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'rpress_purchase_from_name', $from_name, 0, array() );

	$from_email  = rpress_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'rpress_test_purchase_from_address', $from_email, 0, array() );

	$subject     = rpress_get_option( 'purchase_subject', __( 'Purchase Receipt', 'restropress' ) );
	$subject     = apply_filters( 'rpress_purchase_subject', wp_strip_all_tags( $subject ), 0 );
	$subject     = rpress_do_email_tags( $subject, 0 );

	$heading     = rpress_get_option( 'purchase_heading', __( 'Purchase Receipt', 'restropress' ) );
	$heading     = apply_filters( 'rpress_purchase_heading', $heading, 0, array() );

	$attachments = apply_filters( 'rpress_receipt_attachments', array(), 0, array() );

	$message     = rpress_do_email_tags( rpress_get_email_body_content( 0, array() ), 0 );

	$emails = RPRESS()->emails;
	$emails->__set( 'from_name' , $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading'   , $heading );

	$headers = apply_filters( 'rpress_receipt_headers', $emails->get_headers(), 0, array() );
	$emails->__set( 'headers', $headers );

	$emails->send( rpress_get_admin_notice_emails(), $subject, $message, $attachments );

}

/**
 * Sends the Admin Sale Notification Email
 *
 * @since 1.0.0
 * @param int $payment_id Payment ID (default: 0)
 * @param array $payment_data Payment Meta and Data
 * @return void
 */
function rpress_admin_email_notice( $payment_id = 0, $payment_data = array() ) {

	$payment_id = absint( $payment_id );

	if( empty( $payment_id ) ) {
		return;
	}

	if( ! rpress_get_payment_by( 'id', $payment_id ) ) {
		return;
	}

	$from_name   = rpress_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_name   = apply_filters( 'rpress_purchase_from_name', $from_name, $payment_id, $payment_data );

	$from_email  = rpress_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$from_email  = apply_filters( 'rpress_admin_order_from_address', $from_email, $payment_id, $payment_data );

	$subject     = rpress_get_option( 'order_notification_subject', sprintf( __( 'New fooditem order - Order #%1$s', 'restropress' ), $payment_id ) );
	$subject     = apply_filters( 'rpress_admin_order_notification_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject     = wp_specialchars_decode( rpress_do_email_tags( $subject, $payment_id ) );

	$heading     = rpress_get_option( 'order_notification_heading', __( 'New Order Placed!', 'restropress' ) );
	$heading     = apply_filters( 'rpress_admin_order_notification_heading', $heading, $payment_id, $payment_data );
	$heading     = rpress_do_email_tags( $heading, $payment_id );

	$attachments = apply_filters( 'rpress_admin_order_notification_attachments', array(), $payment_id, $payment_data );

	$message     = rpress_get_order_notification_body_content( $payment_id, $payment_data );

	$emails = RPRESS()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$headers = apply_filters( 'rpress_admin_order_notification_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( rpress_get_admin_notice_emails(), $subject, $message, $attachments );

}
add_action( 'rpress_admin_order_notice', 'rpress_admin_email_notice', 10, 2 );

/**
 * Retrieves the emails for which admin notifications are sent to (these can be
 * changed in the RPRESS Settings)
 *
 * @since 1.0
 * @return mixed
 */
function rpress_get_admin_notice_emails() {
	$emails = rpress_get_option( 'admin_notice_emails', false );
	$emails = strlen( trim( $emails ) ) > 0 ? $emails : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'rpress_admin_notice_emails', $emails );
}

/**
 * Checks whether admin sale notices are disabled
 *
 * @since 1.0
 *
 * @param int $payment_id
 * @return mixed
 */
function rpress_admin_notices_disabled( $payment_id = 0 ) {
	$ret = rpress_get_option( 'disable_admin_notices', false );
	return (bool) apply_filters( 'rpress_admin_notices_disabled', $ret, $payment_id );
}

/**
 * Get sale notification email text
 *
 * Returns the stored email text if available, the standard email text if not
 *
 * @since  1.0.0
 * @author RestroPress
 * @return string $message
 */
function rpress_get_default_sale_notification_email() {
	$default_email_body = __( 'Hello', 'restropress' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'restropress' ), rpress_get_label_plural() ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'restropress' ), rpress_get_label_plural() ) . "\n\n";
	$default_email_body .= '{fooditem_list}' . "\n\n";
	$default_email_body .= __( 'Purchased by: ', 'restropress' ) . ' {name}' . "\n";
	$default_email_body .= __( 'Amount: ', 'restropress' ) . ' {price}' . "\n";
	$default_email_body .= __( 'Payment Method: ', 'restropress' ) . ' {payment_method}' . "\n\n";
	$default_email_body .= __( 'Thank you', 'restropress' );

	$message = rpress_get_option( 'order_notification', false );
	$message = ! empty( $message ) ? $message : $default_email_body;

	return $message;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since  1.0.0
 * @param $user_info
 * @param $payment   RPRESS_Payment for getting the names
 *
 * @return array $email_names
 */
function rpress_get_email_names( $user_info, $payment = false ) {
	$email_names = array();
	$email_names['fullname'] = '';

	if ( $payment instanceof RPRESS_Payment ) {

		if ( $payment->user_id > 0 ) {

			$user_data = get_userdata( $payment->user_id );
			$email_names['name']      = $payment->first_name;
			$email_names['fullname']  = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username']  = $user_data->user_login;

		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		} else {

			$email_names['name']     = $payment->email;
			$email_names['username'] = $payment->email;

		}

	} else {

		if ( is_serialized( $user_info ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}

		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data = get_userdata( $user_info['id'] );
			$email_names['name']      = $user_info['first_name'];
			$email_names['fullname']  = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username']  = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}

	}

	return $email_names;
}
