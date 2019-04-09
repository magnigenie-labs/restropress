<?php
/**
 * Discount Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sets up and stores a new discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses rpress_store_discount()
 * @return void
 */
function rpress_add_discount( $data ) {
	
	if ( ! isset( $data['rpress-discount-nonce'] ) || ! wp_verify_nonce( $data['rpress-discount-nonce'], 'rpress_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'restr-press' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	// Setup the discount code details
	$posted = array();

	if ( empty( $data['name'] ) || empty( $data['code'] ) || empty( $data['type'] ) || empty( $data['amount'] ) ) {
		wp_redirect( add_query_arg( 'rpress-message', 'discount_validation_failed' ) );
		rpress_die();
	}

	// Verify only accepted characters
	$sanitized = preg_replace('/[^a-zA-Z0-9-_]+/', '', $data['code'] );
	if ( strtoupper( $data['code'] ) !== strtoupper( $sanitized ) ) {
		wp_redirect( add_query_arg( 'rpress-message', 'discount_invalid_code' ) );
		rpress_die();
	}

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$posted[ $key ] = $value;

		} else if ( $key != 'rpress-discount-nonce' && $key != 'rpress-action' && $key != 'rpress-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$posted[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$posted[ $key ] = array_map( 'absint', $value );

			}
		}

	}

	// Ensure this discount doesn't already exist
	if ( ! rpress_get_discount_by_code( $posted['code'] ) ) {

		// Set the discount code's default status to active
		$posted['status'] = 'active';

		if ( rpress_store_discount( $posted ) ) {

			wp_redirect( add_query_arg( 'rpress_discount_added', '1', $data['rpress-redirect'] ) ); rpress_die();

		} else {

			wp_redirect( add_query_arg( 'rpress-message', 'discount_add_failed', $data['rpress-redirect'] ) ); rpress_die();

		}

	} else {

		wp_redirect( add_query_arg( 'rpress-message', 'discount_exists', $data['rpress-redirect'] ) ); rpress_die();

	}

}
add_action( 'rpress_add_discount', 'rpress_add_discount' );

/**
 * Saves an edited discount
 *
 * @since 1.0.6
 * @param array $data Discount code data
 * @return void
 */
function rpress_edit_discount( $data ) {

	if ( ! isset( $data['rpress-discount-nonce'] ) || ! wp_verify_nonce( $data['rpress-discount-nonce'], 'rpress_discount_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	// Setup the discount code details
	$discount = array();

	foreach ( $data as $key => $value ) {

		if ( $key === 'products' || $key === 'excluded-products' ) {

			foreach ( $value as $product_key => $product_value ) {
				$value[ $product_key ] = preg_replace("/[^0-9_]/", '', $product_value );
			}

			$discount[ $key ] = $value;

		} else if ( $key != 'rpress-discount-nonce' && $key != 'rpress-action' && $key != 'discount-id' && $key != 'rpress-redirect' ) {

			if ( is_string( $value ) || is_int( $value ) ) {

				$discount[ $key ] = strip_tags( addslashes( $value ) );

			} elseif ( is_array( $value ) ) {

				$discount[ $key ] = array_map( 'absint', $value );

			}

		}

	}

	$old_discount     = new RPRESS_Discount( (int) $data['discount-id'] );
	$discount['uses'] = rpress_get_discount_uses( $old_discount->ID );

	if ( rpress_store_discount( $discount, $data['discount-id'] ) ) {

		wp_redirect( add_query_arg( 'rpress_discount_updated', '1', $data['rpress-redirect'] ) ); rpress_die();

	} else {

		wp_redirect( add_query_arg( 'rpress-message', 'discount_update_failed', $data['rpress-redirect'] ) ); rpress_die();

	}

}
add_action( 'rpress_edit_discount', 'rpress_edit_discount' );

/**
 * Listens for when a discount delete button is clicked and deletes the
 * discount code
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses rpress_remove_discount()
 * @return void
 */
function rpress_delete_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rpress_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to delete discount codes', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$discount_id = $data['discount'];
	rpress_remove_discount( $discount_id );
}
add_action( 'rpress_delete_discount', 'rpress_delete_discount' );

/**
 * Activates Discount Code
 *
 * Sets a discount code's status to active
 *
 * @since 1.0
 * @param array $data Discount code data
 * @uses rpress_update_discount_status()
 * @return void
 */
function rpress_activate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rpress_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to edit discount codes', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	rpress_update_discount_status( $id, 'active' );
}
add_action( 'rpress_activate_discount', 'rpress_activate_discount' );

/**
 * Deactivate Discount
 *
 * Sets a discount code's status to deactivate
 *
 * @since 1.0.6
 * @param array $data Discount code data
 * @uses rpress_update_discount_status()
 * @return void
*/
function rpress_deactivate_discount( $data ) {

	if ( ! isset( $data['_wpnonce'] ) || ! wp_verify_nonce( $data['_wpnonce'], 'rpress_discount_nonce' ) ) {
		wp_die( __( 'Trying to cheat or something?', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to create discount codes', 'restropress' ), array( 'response' => 403 ) );
	}

	$id = absint( $data['discount'] );
	rpress_update_discount_status( $id, 'inactive' );
}
add_action( 'rpress_deactivate_discount', 'rpress_deactivate_discount' );
