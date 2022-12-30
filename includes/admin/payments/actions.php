<?php
/**
 * Admin Payment Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process the payment details edit
 *
 * @access      private
 * @since  1.0.0
 * @return      void
*/
function rpress_update_payment_details( $data ) {


	check_admin_referer( 'rpress_update_payment_details_nonce' );

	if( ! current_user_can( 'edit_shop_payments', $data['rpress_payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$payment_id = absint( $data['rpress_payment_id'] );
	$payment    = new RPRESS_Payment( $payment_id );

	$addon_data = array();

	//Update payment meta
	$service_type = isset( $_POST['rp_service_type'] ) ? sanitize_text_field( $_POST['rp_service_type'] ) : '';
	$service_time = isset( $_POST['rp_service_time'] ) ? sanitize_text_field( $_POST['rp_service_time'] ) : '';
	$order_status = isset( $_POST['rpress_order_status'] ) ? sanitize_text_field( $_POST['rpress_order_status'] ) : '';

	update_post_meta( $payment_id , '_rpress_delivery_type', $service_type );
	update_post_meta( $payment_id , '_rpress_delivery_time', $service_time );
	update_post_meta( $payment_id , '_order_status', $order_status );

	// Retrieve the payment ID
	$payment_id = absint( $data['rpress_payment_id'] );
	$payment    = new RPRESS_Payment( $payment_id );

	// Retrieve existing payment meta
	$meta        = $payment->get_meta();
	$user_info   = $payment->user_info;

	$status      = $data['rpress-payment-status'];
	$unlimited   = isset( $data['rpress-unlimited-fooditems'] ) ? '1' : '';
	$date        = sanitize_text_field( $data['rpress-payment-date'] );
	$hour        = sanitize_text_field( $data['rpress-payment-time-hour'] );

	// Restrict to our high and low
	if ( $hour > 23 ) {
		$hour = 23;
	} elseif ( $hour < 0 ) {
		$hour = 00;
	}

	$minute      = sanitize_text_field( $data['rpress-payment-time-min'] );

	// Restrict to our high and low
	if ( $minute > 59 ) {
		$minute = 59;
	} elseif ( $minute < 0 ) {
		$minute = 00;
	}

	$address     = array_map( 'trim', explode(',',!empty( $data['rpress-payment-address'][0] )  ) );

	$curr_total  = rpress_sanitize_amount( $payment->total );
	$new_total   = rpress_sanitize_amount( sanitize_text_field( $_POST['rpress-payment-total'] ) );
	$tax         = isset( $_POST['rpress-payment-tax'] ) ? rpress_sanitize_amount( sanitize_text_field( $_POST['rpress-payment-tax'] ) ) : 0;
	$date        = date( 'Y-m-d', strtotime( $date ) ) . ' ' . $hour . ':' . $minute . ':00';

	$curr_customer_id  = sanitize_text_field( $data['rpress-current-customer'] );
	$new_customer_id   = sanitize_text_field( $data['customer-id'] );

	// Setup purchased items and price options
	$updated_fooditems = !empty( $_POST['rpress-payment-details-fooditems'] ) ? rpress_sanitize_array( $_POST['rpress-payment-details-fooditems'] ): false;

	if ( $updated_fooditems ) {

		foreach ( $updated_fooditems as $cart_position => $fooditem ) {

			if( isset( $fooditem['addon_items'] ) && !empty( $fooditem['addon_items'] ) ) {
				foreach(  $fooditem['addon_items'] as $key => $addons ) {
					$addons = explode('|', $addons);
					if( is_array( $addons ) && !empty( $addons ) ) {
						$addon_data[$fooditem['id']][$key]['addon_item_name'] = $addons[0];
						$addon_data[$fooditem['id']][$key]['addon_id'] = $addons[1];
						$addon_data[$fooditem['id']][$key]['price'] = $addons[2];
						$addon_data[$fooditem['id']][$key]['quantity'] = $addons[3];

					}
				}
			}

			// If this item doesn't have a log yet, add one for each quantity count
			$has_log = absint( $fooditem['has_log'] );
			$has_log = empty( $has_log ) ? false : true;

			if ( $has_log ) {

				$quantity   = isset( $fooditem['quantity'] ) ? absint( $fooditem['quantity'] ) : 1;
				$item_price = isset( $fooditem['item_price'] ) ? $fooditem['item_price'] : 0;
				$item_tax   = isset( $fooditem['item_tax'] ) ? $fooditem['item_tax'] : 0;

				// Format any items that are currency.
				$item_price = rpress_format_amount( $item_price );
				$item_tax    = rpress_format_amount( $item_tax );

				$args = array(
					'item_price' => $item_price,
					'quantity'   => $quantity,
					'tax'        => $item_tax,
				);

				$payment->modify_cart_item( $cart_position, $args, $addon_data );

			} else {

				// This
				if ( empty( $fooditem['item_price'] ) ) {
					$fooditem['item_price'] = 0.00;
				}

				if ( empty( $fooditem['item_tax'] ) ) {
					$fooditem['item_tax'] = 0.00;
				}

				$item_price  = $fooditem['item_price'];
				$fooditem_id = absint( $fooditem['id'] );
				$quantity    = absint( $fooditem['quantity'] ) > 0 ? absint( $fooditem['quantity'] ) : 1;
				$price_id    = false;
				$tax         = $fooditem['item_tax'];

				if ( rpress_has_variable_prices( $fooditem_id ) && isset( $fooditem['price_id'] ) ) {
					$price_id = absint( $fooditem['price_id'] );
				}

				// Set some defaults
				$args = array(
					'quantity'    => $quantity,
					'item_price'  => $item_price,
					'price_id'    => $price_id,
					'tax'         => $tax,
				);

				$addon_data = isset( $addon_data[ $fooditem_id ] ) ? $addon_data[ $fooditem_id ] : '';
				$payment->add_fooditem( $fooditem_id, $args, $addon_data );

			}

		}

		$deleted_fooditems = json_decode( stripcslashes( $data['rpress-payment-removed'] ), true );
		foreach ( $deleted_fooditems as $deleted_fooditem ) {
			$deleted_fooditem = $deleted_fooditem[0];

			if ( empty ( $deleted_fooditem['id'] ) ) {
				continue;
			}

			$price_id = false;

			if ( rpress_has_variable_prices( $deleted_fooditem['id'] ) && isset( $deleted_fooditem['price_id'] ) ) {
				$price_id = absint( $deleted_fooditem['price_id'] );
			}

			$cart_index = isset( $deleted_fooditem['cart_index'] ) ? absint( $deleted_fooditem['cart_index'] ) : false;

			$args = array(
				'quantity'   => ( int ) $deleted_fooditem['quantity'],
				'price_id'   => $price_id,
				'item_price' => ( float ) $deleted_fooditem['amount'],
				'cart_index' => $cart_index
			);

			$payment->remove_fooditem( $deleted_fooditem['id'], $args );

			do_action( 'rpress_remove_fooditem_from_payment', $payment_id, $deleted_fooditem['id'] );

		}

	}

	do_action( 'rpress_update_edited_purchase', $payment_id );

	$payment->date = $date;

	$customer_changed = false;

	if ( isset( $data['rpress-new-customer'] ) && $data['rpress-new-customer'] == '1' ) {

		$email      = isset( $data['rpress-new-customer-email'] ) ? sanitize_text_field( $data['rpress-new-customer-email'] ) : '';
		$names      = isset( $data['rpress-new-customer-name'] ) ? sanitize_text_field( $data['rpress-new-customer-name'] ) : '';

		if ( empty( $email ) || empty( $names ) ) {
			wp_die( __( 'New Customers require a name and email address', 'restropress' ) );
		}

		$customer = new RPRESS_Customer( $email );
		if ( empty( $customer->id ) ) {
			$customer_data = array( 'name' => $names, 'email' => $email );
			$user_id       = email_exists( $email );
			if ( false !== $user_id ) {
				$customer_data['user_id'] = $user_id;
			}

			if ( ! $customer->create( $customer_data ) ) {
				// Failed to crete the new customer, assume the previous customer
				$customer_changed = false;
				$customer = new RPRESS_Customer( $curr_customer_id );
				rpress_set_error( 'rpress-payment-new-customer-fail', __( 'Error creating new customer', 'restropress' ) );
			}
		}

		$new_customer_id = $customer->id;

		$previous_customer = new RPRESS_Customer( $curr_customer_id );

		$customer_changed = true;

	} elseif ( $curr_customer_id !== $new_customer_id ) {

		$customer = new RPRESS_Customer( $new_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

		$previous_customer = new RPRESS_Customer( $curr_customer_id );

		$customer_changed = true;

	} else {

		$customer = new RPRESS_Customer( $curr_customer_id );
		$email    = $customer->email;
		$names    = $customer->name;

	}

	// Setup first and last name from input values
	$names      = explode( ' ', $names );
	$first_name = ! empty( $names[0] ) ? $names[0] : '';
	$last_name  = '';
	if( ! empty( $names[1] ) ) {
		unset( $names[0] );
		$last_name = implode( ' ', $names );
	}

	if ( $customer_changed ) {
		// Remove the stats and payment from the previous customer and attach it to the new customer
		$previous_customer->remove_payment( $payment_id, false );
		$customer->attach_payment( $payment_id, false );

		// If purchase was completed and not ever refunded, adjust stats of customers
		if( 'revoked' == $status || 'publish' == $status ) {

			$previous_customer->decrease_purchase_count();
			$previous_customer->decrease_value( $new_total );

			$customer->increase_purchase_count();
			$customer->increase_value( $new_total );
		}

		$payment->customer_id = $customer->id;
	}

	// Set new meta values
	$payment->user_id        = $customer->user_id;
	$payment->email          = $customer->email;
	$payment->first_name     = $first_name;
	$payment->last_name      = $last_name;
	$payment->address        = $address;

	$payment->total          = $new_total;
	$payment->tax            = $tax;

	$payment->has_unlimited_fooditems = $unlimited;

	// Check for payment notes
	if ( ! empty( $data['rpress-payment-note'] ) ) {

		$note  = wp_kses( $data['rpress-payment-note'], array() );
		rpress_insert_payment_note( $payment->ID, $note );

	}

	// Set new status
	$payment->status = $status;

	// Adjust total store earnings if the payment total has been changed
	if ( $new_total !== $curr_total && ( 'publish' == $status || 'revoked' == $status ) ) {

		if ( $new_total > $curr_total ) {
			// Increase if our new total is higher
			$difference = $new_total - $curr_total;
			rpress_increase_total_earnings( $difference );

		} elseif ( $curr_total > $new_total ) {
			// Decrease if our new total is lower
			$difference = $curr_total - $new_total;
			rpress_decrease_total_earnings( $difference );

		}

	}

	$updated = $payment->save();

  	$order_status = isset( $_POST['rpress_order_status'] ) ? sanitize_text_field( $_POST['rpress_order_status'] ) : '';

	rpress_update_order_status( $payment_id, $order_status );

	if ( 0 === $updated ) {
		wp_die( __( 'Error Updating Payment', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 400 ) );
	}

	do_action( 'rpress_updated_edited_purchase', $payment_id );

	wp_safe_redirect( admin_url( 'admin.php?page=rpress-payment-history&view=view-order-details&rpress-message=payment-updated&id=' . $payment_id ) );
	exit;
}
add_action( 'rpress_update_payment_details', 'rpress_update_payment_details' );

/**
 * Trigger a Purchase Deletion
 *
 * @since  1.0.0
 * @param $data Arguments passed
 * @return void
 */
function rpress_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'rpress_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		rpress_delete_purchase( $payment_id );
		wp_redirect( admin_url( 'admin.php?page=rpress-payment-history&rpress-message=payment_deleted' ) );
		rpress_die();
	}
}
add_action( 'rpress_delete_payment', 'rpress_trigger_purchase_delete' );
/**
 * Trigger the action of moving an order to the 'trash' status
 *
 * @since 3.0
 *
 * @param $data
 * @return void
 */
function rpress_trigger_trash_order( $data ) {

	if ( wp_verify_nonce( $data['_wpnonce'], 'rpress_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		rpress_trash_order( $payment_id );	

		$redirect = admin_url( array(
			'page'        => 'rpress-payment-history',
			'rpress-message' => 'order_trashed',
		) );

	}
}
add_action( 'rpress_trash_order', 'rpress_trigger_trash_order' );

function rpress_trigger_restore_order( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'rpress_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		rpress_restore_order( $payment_id );

		$redirect = admin_url( array(
			'page'        => 'rpress-payment-history',
			'rpress-message' => 'order_restored',
		) );

	}
}
add_action( 'rpress_restore_order', 'rpress_trigger_restore_order' );
/**
 * New in 3.0, permanently destroys an order, and all its data, and related data.
 *
 * @since 3.0
 *
 * @param array $data Arguments passed.
 */
function rpress_trigger_destroy_order( $data ) {

	if ( wp_verify_nonce( $data['_wpnonce'], 'rpress_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this order.', 'restropress' ), esc_html__( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		rpress_delete_order( $payment_id );

		$redirect_link = admin_url(
			array(
				'page'        => 'rpress-payment-history',
				'rpress-message' => 'payment_deleted',
			) );
			
	}
}
add_action( 'rpress_delete_order', 'rpress_trigger_destroy_order' );

function rpress_ajax_store_payment_note() {

	$payment_id = absint( $_POST['payment_id'] );
	$note       = wp_kses( $_POST['note'], array() );

	if( ! current_user_can( 'edit_shop_payments', $payment_id ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( empty( $payment_id ) )
		die( '-1' );

	if( empty( $note ) )
		die( '-1' );

	$note_id = rpress_insert_payment_note( $payment_id, $note );
	die( rpress_get_payment_note_html( $note_id ) );
}
add_action( 'wp_ajax_rpress_insert_payment_note', 'rpress_ajax_store_payment_note' );

/**
 * Triggers a payment note deletion without ajax
 *
 * @since  1.0.0
 * @param array $data Arguments passed
 * @return void
*/
function rpress_trigger_payment_note_deletion( $data ) {

	if( ! wp_verify_nonce( $data['_wpnonce'], 'rpress_delete_payment_note_' . $data['note_id'] ) )
		return;

	if( ! current_user_can( 'edit_shop_payments', $data['payment_id'] ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$edit_order_url = admin_url( 'admin.php?page=rpress-payment-history&view=view-order-details&rpress-message=payment-note-deleted&id=' . absint( $data['payment_id'] ) );

	rpress_delete_payment_note( $data['note_id'], $data['payment_id'] );

	wp_redirect( $edit_order_url );
}
add_action( 'rpress_delete_payment_note', 'rpress_trigger_payment_note_deletion' );

/**
 * Delete a payment note deletion with ajax
 *
 * @since  1.0.0
 * @return void
*/
function rpress_ajax_delete_payment_note() {

	if( ! current_user_can( 'edit_shop_payments', absint( $_POST['payment_id'] ) ) ) {
		wp_die( __( 'You do not have permission to edit this payment record', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( rpress_delete_payment_note( absint( $_POST['note_id'] ), absint( $_POST['payment_id'] ) ) ) {
		die( '1' );
	} else {
		die( '-1' );
	}

}
add_action( 'wp_ajax_rpress_delete_payment_note', 'rpress_ajax_delete_payment_note' );
