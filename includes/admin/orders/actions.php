<?php
/**
 * Orders Actions
 *
 * @package     RPRESS
 * @copyright   Copyright (c) 2019, MagniGenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Update order on edit
 *
 * @access      private
 * @since       2.2
 * @return      void
*/
function rpress_update_order_status( $payment_id = 0, $new_status = 'completed' ) {

  if ( empty( $payment_id ) ) {
    return;
  }

  if ( 0 >= did_action( 'rpress_update_order_status' ) ) {
    do_action( 'rpress_update_order_status', $payment_id, $new_status );
  }

  if ( $new_status == 'completed' ) {
    rpress_update_payment_status( $payment_id, 'publish' );
  }

  update_post_meta( $payment_id, '_order_status', $new_status );
}


/**
 * Get order ststus by payment id
 *
 * @access      private
 * @since       2.1
 * @param       int $payment_id Payment id
 * @return      void
*/
function rpress_get_order_status( $payment_id ) {

  if( empty( $payment_id ) ) {
    return;
  }

  $order_status = !empty( get_post_meta( $payment_id, '_order_status', true ) ) ? get_post_meta( $payment_id, '_order_status', true ) : 'pending';

  return apply_filters( 'rp_get_order_status', $order_status );

}
/**
 * Move an order to the trashed status
 *
 * @since 3.0
 *
 * @param $order_id
 *
 * @return bool      true if the order was trashed successfully, false if not
 */
function rpress_trash_order( $payment_id ) {
 

  if ( false === rpress_is_order_trashable( $payment_id ) ) {
    return false;
  }

  $payment = rpress_get_payment( $payment_id );

  $payments   = new RPRESS_Payments_Query();            
  $current_status = $payment->status;


  $trashed    =   wp_trash_post( $payment_id );
  if ( ! empty( $trashed ) ) {

    // If successfully trashed, store the pre-trashed status in meta, so we can possibly restore it.
    update_post_meta( $payment_id, '_pre_trash_status', $current_status );

  }
    // Update the customer records when an order is trashed.
    if ( ! empty( $payment->customer_id ) ) {
      $customer = new RPRESS_Customer( $payment->customer_id );
      
    }
  

  return filter_var( $trashed, FILTER_VALIDATE_BOOLEAN );
}

/**
 * Get order  by payment id
 *
 * @access      private
 * @since       2.1
 * @param       int $payment_id Payment id
 * @return      void
*/

function rpress_is_order_trashable( $payment_id ) {

  $payment      = rpress_get_payment( $payment_id );
 
  $is_trashable = false;

  if ( empty( $payment ) ) {
    return $is_trashable;
  }

  $non_trashable_statuses = apply_filters( 'rpress_non_trashable_statuses', array( 'trash' ) );

  if ( ! in_array( $payment->status, $non_trashable_statuses ) ) {

    $is_trashable = true;
  }

  return (bool) apply_filters( 'rpress_is_order_trashable', $is_trashable, $payment );
}

/**
 * Restore an order from the trashed status to it's previous status.
 *
 * @since 3.0
 *
 * @param $order_id
 *
 * @return bool      true if the order was trashed successfully, false if not
 */
function rpress_restore_order( $payment_id ) {

  if ( false === rpress_is_order_restorable( $payment_id ) ) {
    return false;
  }

  $payment = rpress_get_payment( $payment_id );

  if ( 'trash' !== $payment->status ) {
    return false;
  }
  $payments   = new RPRESS_Payments_Query();            
  $current_status = $payment->status;

  $pre_trash_status = get_post_meta( $payment_id, '_pre_trash_status', true );
  if ( empty( $pre_trash_status ) ) {
    return false;
  }

if( $current_status == "trash" ) {
    $restored = wp_update_post( array(
                   'ID'           => $payment_id,
                   'post_status'  => 'processing'
               )
    );
}


  
  return filter_var( $restored, FILTER_VALIDATE_BOOLEAN );

}
/**
 * Get order  by payment id
 *
 * @access      private
 * @since       2.1
 * @param       int $payment_id Payment id
 * @return      void
*/

function rpress_is_order_restorable( $payment_id ) {

  $payment      = rpress_get_payment( $payment_id );

  $is_restorable = false;

  if ( empty( $payment ) ) {
    return $is_restorable;
  }

  if ( 'trash' === $payment->status ) {
    $is_restorable = true;
  }

  return (bool) apply_filters( 'rpress_is_order_restorable', $is_restorable, $payment );

}
/**
 * Delete an order.
 *
 * @since 3.0
 *
 * @param int $order_id Order ID.
 * @return int|false `1` if the order was deleted successfully, false on error.
 */
function rpress_delete_order( $payment_id = 0, $update_customer = true, $delete_fooditem_logs = false ) {
global $rpress_logs;

  $payment   = new RPRESS_Payment( $payment_id );

  // Update sale counts and earnings for all purchased products
  rpress_undo_purchase( false, $payment_id );

  $amount      = rpress_get_payment_amount( $payment_id );
  $status      = $payment->post_status;
  $customer_id = rpress_get_payment_customer_id( $payment_id );

  $customer = new RPRESS_Customer( $customer_id );

  if( $status == 'trash' ) {
    // Only decrease earnings if they haven't already been decreased (or were never increased for this payment)
    rpress_decrease_total_earnings( $amount );
    // Clear the This Month earnings (this_monththis_month is NOT a typo)
    delete_transient( md5( 'rpress_earnings_this_monththis_month' ) );

    if( $customer->id && $update_customer ) {

      // Decrement the stats for the customer
      $customer->decrease_purchase_count();
      $customer->decrease_value( $amount );

    }
  }

  do_action( 'rpress_payment_delete', $payment_id );

  if( $customer->id && $update_customer ) {

    // Remove the payment ID from the customer
    $customer->remove_payment( $payment_id );

  }

  // Remove the payment
  wp_delete_post( $payment_id, true );

  // Remove related sale log entries
  $rpress_logs->delete_logs(
    null,
    'sale',
    array(
      array(
        'key'   => '_rpress_log_payment_id',
        'value' => $payment_id
      )
    )
  );

  if ( $delete_fooditem_logs ) {
    $rpress_logs->delete_logs(
      null,
      'file_fooditem',
      array(
        array(
          'key'   => '_rpress_log_payment_id',
          'value' => $payment_id
        )
      )
    );
  }

  do_action( 'rpress_payment_deleted', $payment_id );

}

/**
 * Get HTML for some action buttons. Used in list tables.
 *
 * @since 1.0
 * @param array $actions Actions to output.
 * @return string
 */
function rp_render_action_buttons( $actions ) {

  $actions_html = '';

  if ( !empty( $actions ) ) {
    foreach( $actions as $action ) {
      if ( isset( $action['group'] ) ) {
        $actions_html .= '<div class="rp-action-button-group"><label>' . $action['group'] . '</label> <span class="rp-action-button-group__items">' . rp_render_action_buttons( $action['actions'] ) . '</span></div>';
      } elseif ( isset( $action['action'], $action['name'] ) ) {
        $actions_html .= sprintf( '<a class="button rp-action-button rp-action-button-%1$s %1$s" data-update-status="%1$s"  aria-label="%2$s" data-payment="%3$s" data-action="rpress_update_order_status" title="%2$s" href="%4$s">%2$s</a>', esc_attr( $action['action'] ), esc_html( $action['name'] ), $action[ 'payment_id' ], $action['url'] );
      }
    }
  }

  return $actions_html;
}