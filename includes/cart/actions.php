<?php
/**
 * Cart Actions
 *
 * @package     RPRESS
 * @subpackage  Cart
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Endpoints for the Cart
 *
 * These endpoints are used for adding/removing items from the cart
 *
 * @since  1.0.0
 * @return void
 */
function rpress_add_rewrite_endpoints( $rewrite_rules ) {
	add_rewrite_endpoint( 'rpress-add', EP_ALL );
	add_rewrite_endpoint( 'rpress-remove', EP_ALL );
}
add_action( 'init', 'rpress_add_rewrite_endpoints' );

/**
 * Process Cart Endpoints
 *
 * Listens for add/remove requests sent from the cart
 *
 * @since  1.0.0
 * @global $wp_query Used to access the current query that is being requested
 * @return void
*/
function rpress_process_cart_endpoints() {
	global $wp_query;

	// Adds an item to the cart with a /rpress-add/# URL
	if ( isset( $wp_query->query_vars['rpress-add'] ) ) {
		$fooditem_id = absint( $wp_query->query_vars['rpress-add'] );
		$cart        = rpress_add_to_cart( $fooditem_id, array() );

		wp_redirect( rpress_get_checkout_uri() ); rpress_die();
	}

	// Removes an item from the cart with a /rpress-remove/# URL
	if ( isset( $wp_query->query_vars['rpress-remove'] ) ) {
		$cart_key = absint( $wp_query->query_vars['rpress-remove'] );
		$cart     = rpress_remove_from_cart( $cart_key );

		wp_redirect( rpress_get_checkout_uri() ); rpress_die();
	}
}
add_action( 'template_redirect', 'rpress_process_cart_endpoints', 100 );

/**
 * Process the Add to Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function rpress_process_add_to_cart( $data ) {
	$fooditem_id = absint( $data['fooditem_id'] );
	$options     = isset( $data['rpress_options'] ) ? $data['rpress_options'] : array();

	if ( ! empty( $data['fooditem_qty'] ) ) {
		$options['quantity'] = absint( $data['fooditem_qty'] );
	}

	if ( isset( $options['price_id'] ) && is_array( $options['price_id'] ) ) {
		foreach ( $options['price_id'] as  $key => $price_id ) {
			$options['quantity'][ $key ] = isset( $data[ 'rpress_fooditem_quantity_' . $price_id ] ) ? absint( $data[ 'rpress_fooditem_quantity_' . $price_id ] ) : 1;
		}
	}

	$cart        = rpress_add_to_cart( $fooditem_id, $options );

	if ( rpress_straight_to_checkout() && ! rpress_is_checkout() ) {
		$query_args 	= remove_query_arg( array( 'rpress_action', 'fooditem_id', 'rpress_options' ) );
		$query_part 	= strpos( $query_args, "?" );
		$url_parameters = '';

		if ( false !== $query_part ) { 
			$url_parameters = substr( $query_args, $query_part ); 
		}

		wp_redirect( rpress_get_checkout_uri() . $url_parameters, 303 );
		rpress_die();
	} else {
		wp_redirect( remove_query_arg( array( 'rpress_action', 'fooditem_id', 'rpress_options' ) ) ); rpress_die();
	}
}
add_action( 'rpress_add_to_cart', 'rpress_process_add_to_cart' );

/**
 * Process the Remove from Cart request
 *
 * @since 1.0
 *
 * @param $data
 */
function rpress_process_remove_from_cart( $data ) {
	$cart_key = absint( $_GET['cart_item'] );
	rpress_remove_from_cart( $cart_key );
	wp_redirect( remove_query_arg( array( 'rpress_action', 'cart_item', 'nocache' ) ) ); rpress_die();
}
add_action( 'rpress_remove', 'rpress_process_remove_from_cart' );

/**
 * Process the Remove fee from Cart request
 *
 * @since  1.0.0
 *
 * @param $data
 */
function rpress_process_remove_fee_from_cart( $data ) {
	$fee = sanitize_text_field( $data['fee'] );
	RPRESS()->fees->remove_fee( $fee );
	wp_redirect( remove_query_arg( array( 'rpress_action', 'fee', 'nocache' ) ) ); rpress_die();
}
add_action( 'rpress_remove_fee', 'rpress_process_remove_fee_from_cart' );

/**
 * Process the Collection Purchase request
 *
 * @since 1.0
 *
 * @param $data
 */
function rpress_process_collection_purchase( $data ) {
	$taxonomy   = urldecode( $data['taxonomy'] );
	$terms      = urldecode( $data['terms'] );
	$cart_items = rpress_add_collection_to_cart( $taxonomy, $terms );
	wp_redirect( add_query_arg( 'added', '1', remove_query_arg( array( 'rpress_action', 'taxonomy', 'terms' ) ) ) );
	rpress_die();
}
add_action( 'rpress_purchase_collection', 'rpress_process_collection_purchase' );


/**
 * Process cart updates, primarily for quantities
 *
 * @since  1.0.0
 */
function rpress_process_cart_update( $data ) {

	foreach( $data['rpress-cart-fooditems'] as $key => $cart_fooditem_id ) {
		$options  = json_decode( stripslashes( $data['rpress-cart-fooditem-' . $key . '-options'] ), true );
		$quantity = absint( $data['rpress-cart-fooditem-' . $key . '-quantity'] );
		rpress_set_cart_item_quantity( $cart_fooditem_id, $quantity, $options );
	}

}
add_action( 'rpress_update_cart', 'rpress_process_cart_update' );

/**
 * Process cart save
 *
 * @since 1.0
 * @return void
 */
function rpress_process_cart_save( $data ) {

	$cart = rpress_save_cart();
	if( ! $cart ) {
		wp_redirect( rpress_get_checkout_uri() ); exit;
	}

}
add_action( 'rpress_save_cart', 'rpress_process_cart_save' );

/**
 * Process cart save
 *
 * @since 1.0
 * @return void
 */
function rpress_process_cart_restore( $data ) {

	$cart = rpress_restore_cart();
	if( ! is_wp_error( $cart ) ) {
		wp_redirect( rpress_get_checkout_uri() ); exit;
	}

}
add_action( 'rpress_restore_cart', 'rpress_process_cart_restore' );
