<?php
/**
 * Cart Functions
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
 * Get the contents of the cart
 *
 * @since 1.0
 * @return array Returns an array of cart contents, or an empty array if no items in the cart
 */
function rpress_get_cart_contents() {
	return RPRESS()->cart->get_contents();
}

/**
 * Retrieve the Cart Content Details
 *
 * Includes prices, tax, etc of all items.
 *
 * @since 1.0
 * @return array $details Cart content details
 */
function rpress_get_cart_content_details() {
	return RPRESS()->cart->get_contents_details();
}

/**
 * Get Cart Quantity
 *
 * @since 1.0
 * @return int Sum quantity of items in the cart
 */
function rpress_get_cart_quantity() {
	return RPRESS()->cart->get_quantity();
}

/**
 * Add To Cart
 *
 * Adds a fooditem ID to the shopping cart.
 *
 * @since 1.0
 *
 * @param int $fooditem_id Download IDs to be added to the cart
 * @param array $options Array of options, such as variable price
 *
 * @return string Cart key of the new item
 */
function rpress_add_to_cart( $fooditem_id, $options = array() ) {
	return RPRESS()->cart->add( $fooditem_id, $options );
}

/**
 * Removes a Download from the Cart
 *
 * @since 1.0
 * @param int $cart_key the cart key to remove. This key is the numerical index of the item contained within the cart array.
 * @return array Updated cart items
 */
function rpress_remove_from_cart( $cart_key ) {
	return RPRESS()->cart->remove( $cart_key );
}

/**
 * Checks to see if an item is already in the cart and returns a boolean
 *
 * @since 1.0
 *
 * @param int   $fooditem_id ID of the fooditem to remove
 * @param array $options
 * @return bool Item in the cart or not?
 */
function rpress_item_in_cart( $fooditem_id = 0, $options = array() ) {
	return RPRESS()->cart->is_item_in_cart( $fooditem_id, $options );
}

/**
 * Get the Item Position in Cart
 *
 * @since 1.0
 *
 * @param int   $fooditem_id ID of the fooditem to get position of
 * @param array $options array of price options
 * @return bool|int|string false if empty cart |  position of the item in the cart
 */
function rpress_get_item_position_in_cart( $fooditem_id = 0, $options = array() ) {
	return RPRESS()->cart->get_item_position( $fooditem_id, $options );
}

/**
 * Check if quantities are enabled
 *
 * @since  1.0.0
 * @return bool
 */
function rpress_item_quantities_enabled() {
	$ret = rpress_get_option( 'item_quantities', false );
	return (bool) apply_filters( 'rpress_item_quantities_enabled', $ret );
}

/**
 * Set Cart Item Quantity
 *
 * @since  1.0.0
 *
 * @param int   $fooditem_id Download (cart item) ID number
 * @param int   $quantity
 * @param array $options Download options, such as price ID
 * @return mixed New Cart array
 */
function rpress_set_cart_item_quantity( $fooditem_id = 0, $quantity = 1, $options = array() ) {
	return RPRESS()->cart->set_item_quantity( $fooditem_id, $quantity, $options );
}

/**
 * Get Cart Item Quantity
 *
 * @since 1.0
 * @param int $fooditem_id Download (cart item) ID number
 * @param array $options Download options, such as price ID
 * @return int $quantity Cart item quantity
 */
function rpress_get_cart_item_quantity( $fooditem_id = 0, $options = array() ) {
	return RPRESS()->cart->get_item_quantity( $fooditem_id, $options );
}

/**
 * Get Cart Item Price
 *
 * @since 1.0
 *
 * @param int   $item_id Download (cart item) ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return string Fully formatted price
 */
function rpress_cart_item_price( $item_id = 0, $options = array() ) {
	return RPRESS()->cart->item_price( $item_id, $options );
}

/**
 * Get Cart Item Price
 *
 * Gets the price of the cart item. Always exclusive of taxes
 *
 * Do not use this for getting the final price (with taxes and discounts) of an item.
 * Use rpress_get_cart_item_final_price()
 *
 * @since 1.0
 * @param int   $fooditem_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @param bool  $remove_tax_from_inclusive Remove the tax amount from tax inclusive priced products.
 * @return float|bool Price for this item
 */
function rpress_get_cart_item_price( $fooditem_id = 0, $options = array(), $remove_tax_from_inclusive = false ) {
	return RPRESS()->cart->get_item_price( $fooditem_id, $options, $remove_tax_from_inclusive );
}

/**
 * Get cart item's final price
 *
 * Gets the amount after taxes and discounts
 *
 * @since  1.0.0
 * @param int    $item_key Cart item key
 * @return float Final price for the item
 */
function rpress_get_cart_item_final_price( $item_key = 0 ) {
	return RPRESS()->cart->get_item_final_price( $item_key );
}

/**
 * Get cart item tax
 *
 * @since  1.0.0
 * @param array $fooditem_id Download ID
 * @param array $options Cart item options
 * @param float $subtotal Cart item subtotal
 * @return float Tax amount
 */
function rpress_get_cart_item_tax( $fooditem_id = 0, $options = array(), $subtotal = '' ) {
	return RPRESS()->cart->get_item_tax( $fooditem_id, $options, $subtotal );
}

/**
 * Get Price Name
 *
 * Gets the name of the specified price option,
 * for variable pricing only.
 *
 * @since 1.0
 *
 * @param       $fooditem_id Download ID number
 * @param array $options Optional parameters, used for defining variable prices
 * @return mixed|void Name of the price option
 */
function rpress_get_price_name( $fooditem_id = 0, $options = array() ) {
	$return = false;
	if( rpress_has_variable_prices( $fooditem_id ) && ! empty( $options ) ) {
		$prices = rpress_get_variable_prices( $fooditem_id );
		$name   = false;
		if( $prices ) {
			if( isset( $prices[ $options['price_id'] ] ) )
				$name = $prices[ $options['price_id'] ]['name'];
		}
		$return = $name;
	}
	return apply_filters( 'rpress_get_price_name', $return, $fooditem_id, $options );
}

/**
 * Get cart item price id
 *
 * @since 1.0
 *
 * @param array $item Cart item array
 * @return int Price id
 */
function rpress_get_cart_item_price_id( $item = array() ) {
	return RPRESS()->cart->get_item_price_id( $item );
}

/**
 * Get cart item price name
 *
 * @since 1.0
 * @param int $item Cart item array
 * @return string Price name
 */
function rpress_get_cart_item_price_name( $item = array() ) {
	return RPRESS()->cart->get_item_price_name( $item );
}

/**
 * Get cart item title
 *
 * @since 2.4.3
 * @param int $item Cart item array
 * @return string item title
 */
function rpress_get_cart_item_name( $item = array() ) {
	return RPRESS()->cart->get_item_name( $item );
}

/**
 * Cart Subtotal
 *
 * Shows the subtotal for the shopping cart (no taxes)
 *
 * @since  1.0.0
 * @return float Total amount before taxes fully formatted
 */
function rpress_cart_subtotal() {
	return RPRESS()->cart->subtotal();
}

/**
 * Get Cart Subtotal
 *
 * Gets the total price amount in the cart before taxes and before any discounts
 * uses rpress_get_cart_contents().
 *
 * @since 1.0.0
 * @return float Total amount before taxes
 */
function rpress_get_cart_subtotal() {
	return RPRESS()->cart->get_subtotal();
}

/**
 * Get Cart Discountable Subtotal.
 *
 * @return float Total discountable amount before taxes
 */
function rpress_get_cart_discountable_subtotal( $code_id ) {
	return RPRESS()->cart->get_discountable_subtotal( $code_id );
}

/**
 * Get cart items subtotal
 * @param array $items Cart items array
 *
 * @return float items subtotal
 */
function rpress_get_cart_items_subtotal( $items ) {
	return RPRESS()->cart->get_items_subtotal( $items );
}
/**
 * Get Total Cart Amount
 *
 * Returns amount after taxes and discounts
 *
 * @since 1.0
 * @param bool $discounts Array of discounts to apply (needed during AJAX calls)
 * @return float Cart amount
 */
function rpress_get_cart_total( $discounts = false ) {
	return RPRESS()->cart->get_total( $discounts );
}


/**
 * Get Total Cart Amount
 *
 * Gets the fully formatted total price amount in the cart.
 * uses rpress_get_cart_amount().
 *
 * @since 1.0.0
 *
 * @param bool $echo
 * @return mixed|string|void
 */
function rpress_cart_total( $echo = true ) {
	if ( ! $echo ) {
		return RPRESS()->cart->total( $echo );
	}

	RPRESS()->cart->total( $echo );
}

/**
 * Check if cart has fees applied
 *
 * Just a simple wrapper function for RPRESS_Fees::has_fees()
 *
 * @since 1.0
 * @param string $type
 * @uses RPRESS()->fees->has_fees()
 * @return bool Whether the cart has fees applied or not
 */
function rpress_cart_has_fees( $type = 'all' ) {
	return RPRESS()->fees->has_fees( $type );
}

/**
 * Get Cart Fees
 *
 * Just a simple wrapper function for RPRESS_Fees::get_fees()
 *
 * @since 1.0
 * @param string $type
 * @param int $fooditem_id
 * @uses RPRESS()->fees->get_fees()
 * @return array All the cart fees that have been applied
 */
function rpress_get_cart_fees( $type = 'all', $fooditem_id = 0, $price_id = NULL ) {
	return RPRESS()->cart->get_fees( $type, $fooditem_id, $price_id );
}

/**
 * Get Cart Fee Total
 *
 * Just a simple wrapper function for RPRESS_Fees::total()
 *
 * @since 1.0
 * @uses RPRESS()->fees->total()
 * @return float Total Cart Fees
 */
function rpress_get_cart_fee_total() {
	return RPRESS()->cart->get_total_fees();
}

/**
 * Get cart tax on Fees
 *
 * @since  1.0.0
 * @uses RPRESS()->fees->get_fees()
 * @return float Total Cart tax on Fees
 */
function rpress_get_cart_fee_tax() {
	return RPRESS()->cart->get_tax_on_fees();
}

/**
 * Get Purchase Summary
 *
 * Retrieves the purchase summary.
 *
 * @since       1.0
 *
 * @param      $purchase_data
 * @param bool $email
 * @return string
 */
function rpress_get_purchase_summary( $purchase_data, $email = true ) {
	$summary = '';

	if ( $email ) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}

	if ( ! empty( $purchase_data['fooditems'] ) ) {
		foreach ( $purchase_data['fooditems'] as $fooditem ) {
			$summary .= get_the_title( $fooditem['id'] ) . ', ';
		}

		$summary = substr( $summary, 0, -2 );
	}

	return apply_filters( 'rpress_get_purchase_summary', $summary, $purchase_data, $email );
}

/**
 * Gets the total tax amount for the cart contents
 *
 * @since 1.0
 *
 * @return mixed|void Total tax amount
 */
function rpress_get_cart_tax() {
	return RPRESS()->cart->get_tax();
}

/**
 * Gets the tax rate charged on the cart.
 *
 * @since 1.0
 * @param string $country     Country code for tax rate.
 * @param string $state       State for tax rate.
 * @param string $postal_code Postal code for tax rate. Not used by core, but for developers.
 * @return float Tax rate.
 */
function rpress_get_cart_tax_rate( $country = '', $state = '', $postal_code = '' ) {
	$rate = rpress_get_tax_rate( $country, $state );
	return apply_filters( 'rpress_get_cart_tax_rate', floatval( $rate ), $country, $state, $postal_code );
}

/**
 * Gets the total tax amount for the cart contents in a fully formatted way
 *
 * @since 1.0
 * @param bool $echo Whether to echo the tax amount or not (default: false)
 * @return string Total tax amount (if $echo is set to true)
 */
function rpress_cart_tax( $echo = false ) {
	if ( ! $echo ) {
		return RPRESS()->cart->tax( $echo );
	} else {
		RPRESS()->cart->tax( $echo );
	}
}

/**
 * Add Collection to Cart
 *
 * Adds all fooditems within a taxonomy term to the cart.
 *
 * @since 1.0.0
 * @param string $taxonomy Name of the taxonomy
 * @param mixed $terms Slug or ID of the term from which to add ites | An array of terms
 * @return array Array of IDs for each item added to the cart
 */
function rpress_add_collection_to_cart( $taxonomy, $terms ) {
	if ( ! is_string( $taxonomy ) ) return false;

	if( is_numeric( $terms ) ) {
		$terms = get_term( $terms, $taxonomy );
		$terms = $terms->slug;
	}

	$cart_item_ids = array();

	$args = array(
		'post_type' => 'fooditem',
		'posts_per_page' => -1,
		$taxonomy => $terms
	);

	$items = get_posts( $args );
	if ( $items ) {
		foreach ( $items as $item ) {
			rpress_add_to_cart( $item->ID );
			$cart_item_ids[] = $item->ID;
		}
	}
	return $cart_item_ids;
}

/**
 * Returns the URL to remove an item from the cart
 *
 * @since 1.0
 * @global $post
 * @param int $cart_key Cart item key
 * @return string $remove_url URL to remove the cart item
 */
function rpress_remove_item_url( $cart_key ) {
	return RPRESS()->cart->remove_item_url( $cart_key );
}

/**
 * Returns the URL to remove an item from the cart
 *
 * @since 1.0
 * @global $post
 * @param string $fee_id Fee ID
 * @return string $remove_url URL to remove the cart item
 */
function rpress_remove_cart_fee_url( $fee_id = '') {
	return RPRESS()->cart->remove_fee_url( $fee_id );
}

/**
 * Empties the Cart
 *
 * @since 1.0
 * @uses RPRESS()->session->set()
 * @return void
 */
function rpress_empty_cart() {
	RPRESS()->cart->empty_cart();
}

/**
 * Store Purchase Data in Sessions
 *
 * Used for storing info about purchase
 *
 * @since 1.0.0
 *
 * @param $purchase_data
 *
 * @uses RPRESS()->session->set()
 */
function rpress_set_purchase_session( $purchase_data = array() ) {
	RPRESS()->session->set( 'rpress_purchase', $purchase_data );
}

/**
 * Retrieve Purchase Data from Session
 *
 * Used for retrieving info about purchase
 * after completing a purchase
 *
 * @since 1.0.0
 * @uses RPRESS()->session->get()
 * @return mixed array | false
 */
function rpress_get_purchase_session() {
	return RPRESS()->session->get( 'rpress_purchase' );
}

/**
 * Checks if cart saving has been disabled
 *
 * @since 1.0
 * @return bool Whether or not cart saving has been disabled
 */
function rpress_is_cart_saving_disabled() {
	return ! RPRESS()->cart->is_saving_enabled();
}

/**
 * Checks if a cart has been saved
 *
 * @since 1.0
 * @return bool
 */
function rpress_is_cart_saved() {
	return RPRESS()->cart->is_saved();
}

/**
 * Process the Cart Save
 *
 * @since 1.0
 * @return bool
 */
function rpress_save_cart() {
	return RPRESS()->cart->save();
}


/**
 * Process the Cart Restoration
 *
 * @since 1.0
 * @return mixed || false Returns false if cart saving is disabled
 */
function rpress_restore_cart() {
	return RPRESS()->cart->restore();
}

/**
 * Retrieve a saved cart token. Used in validating saved carts
 *
 * @since 1.0
 * @return int
 */
function rpress_get_cart_token() {
	return RPRESS()->cart->get_token();
}

/**
 * Delete Saved Carts after one week
 *
 * This function is only intended to be used by WordPress cron.
 *
 * @since 1.0
 * @global $wpdb
 * @return void
 */
function rpress_delete_saved_carts() {
	global $wpdb;

	// Bail if not in WordPress cron
	if ( ! rpress_doing_cron() ) {
		return;
	}

	$start = date( 'Y-m-d', strtotime( '-7 days' ) );
	$carts = $wpdb->get_results(
		"
		SELECT user_id, meta_key, FROM_UNIXTIME(meta_value, '%Y-%m-%d') AS date
		FROM {$wpdb->usermeta}
		WHERE meta_key = 'rpress_cart_token'
		", ARRAY_A
	);

	if ( $carts ) {
		foreach ( $carts as $cart ) {
			$user_id    = $cart['user_id'];
			$meta_value = $cart['date'];

			if ( strtotime( $meta_value ) < strtotime( '-1 week' ) ) {
				$wpdb->delete(
					$wpdb->usermeta,
					array(
						'user_id'  => $user_id,
						'meta_key' => 'rpress_cart_token'
					)
				);

				$wpdb->delete(
					$wpdb->usermeta,
					array(
						'user_id'  => $user_id,
						'meta_key' => 'rpress_saved_cart'
					)
				);
			}
		}
	}
}
add_action( 'rpress_weekly_scheduled_events', 'rpress_delete_saved_carts' );

/**
 * Generate URL token to restore the cart via a URL
 *
 * @since 1.0
 * @return string UNIX timestamp
 */
function rpress_generate_cart_token() {
	return RPRESS()->cart->generate_token();
}
