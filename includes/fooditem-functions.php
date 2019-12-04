<?php
/**
 * FoddItems Functions
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
 * Retrieve a fooditem by a given field
 *
 * @since       2.0
 * @param       string $field The field to retrieve the discount with
 * @param       mixed $value The value for field
 * @return      mixed
 */
function rpress_get_fooditem_by( $field = '', $value = '' ) {

	if( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch( strtolower( $field ) ) {

		case 'id':
			$fooditem = get_post( $value );

			if( get_post_type( $fooditem ) != 'fooditem' ) {
				return false;
			}

			break;

		case 'slug':
		case 'name':
			$fooditem = get_posts( array(
				'post_type'      => 'fooditem',
				'name'           => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $fooditem ) {
				$fooditem = $fooditem[0];
			}

			break;

		case 'sku':
			$fooditem = get_posts( array(
				'post_type'      => 'fooditem',
				'meta_key'       => 'rpress_sku',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'post_status'    => 'any'
			) );

			if( $fooditem ) {
				$fooditem = $fooditem[0];
			}

			break;

		default:
			return false;
	}

	if( $fooditem ) {
		return $fooditem;
	}

	return false;
}

/**
 * Retrieves a fooditem post object by ID or slug.
 *
 * @since 1.0
 * @since 2.9 - Return an RPRESS_Fooditem object.
 *
 * @param int $fooditem_id Item ID.
 *
 * @return RPRESS_Fooditem $fooditem Entire fooditem data.
 */
function rpress_get_fooditem( $fooditem_id = 0 ) {
	$fooditem = null;

	if ( is_numeric( $fooditem_id ) ) {

		$found_fooditem = new RPRESS_Fooditem( $fooditem_id );

		if ( ! empty( $found_fooditem->ID ) ) {
			$fooditem = $found_fooditem;
		}

	} else { // Support getting a fooditem by name.
		$args = array(
			'post_type'     => 'fooditem',
			'name'          => $fooditem_id,
			'post_per_page' => 1,
			'fields'        => 'ids',
		);

		$fooditems = new WP_Query( $args );
		if ( is_array( $fooditems->posts ) && ! empty( $fooditems->posts ) ) {

			$fooditem_id = $fooditems->posts[0];

			$fooditem = new RPRESS_Fooditem( $fooditem_id );

		}
	}

	return $fooditem;
}

/**
 * Checks whether or not a fooditem is free
 *
 * @since  1.0.0
 * @author RestroPress
 * @param int $fooditem_id ID number of the fooditem to check
 * @param int $price_id (Optional) ID number of a variably priced item to check
 * @return bool $is_free True if the product is free, false if the product is not free or the check fails
 */
function rpress_is_free_fooditem( $fooditem_id = 0, $price_id = false ) {

	if( empty( $fooditem_id ) ) {
		return false;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->is_free( $price_id );
}

/**
 * Returns the price of a fooditem, but only for non-variable priced fooditems.
 *
 * @since 1.0
 * @param int $fooditem_id ID number of the fooditem to retrieve a price for
 * @return mixed|string|int Price of the fooditem
 */
function rpress_get_fooditem_price( $fooditem_id = 0 ) {

	if( empty( $fooditem_id ) ) {
		return false;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_price();
}

/**
 * Displays a formatted price for a fooditem
 *
 * @since 1.0
 * @param int $fooditem_id ID of the fooditem price to show
 * @param bool $echo Whether to echo or return the results
 * @param int $price_id Optional price id for variable pricing
 * @return void
 */
function rpress_price( $fooditem_id = 0, $echo = true, $price_id = false ) {

	if( empty( $fooditem_id ) ) {
		$fooditem_id = get_the_ID();
	}

	$price = rpress_get_fooditem_price( $fooditem_id );

	$price           = apply_filters( 'rpress_fooditem_price', rpress_sanitize_amount( $price ), $fooditem_id, $price_id );
	$formatted_price = '<span class="rpress_price" id="rpress_price_' . $fooditem_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'rpress_fooditem_price_after_html', $formatted_price, $fooditem_id, $price, $price_id );

	if ( $echo ) {
		echo $formatted_price;
	} else {
		return $formatted_price;
	}
}
add_filter( 'rpress_fooditem_price', 'rpress_format_amount', 10 );
add_filter( 'rpress_fooditem_price', 'rpress_currency_filter', 20 );

/**
 * Retrieves the final price of a fooditemable product after purchase
 * this price includes any necessary discounts that were applied
 *
 * @since 1.0
 * @param int $fooditem_id ID of the fooditem
 * @param array $user_purchase_info - an array of all information for the payment
 * @param string $amount_override a custom amount that over rides the 'rpress_price' meta, used for variable prices
 * @return string - the price of the fooditem
 */
function rpress_get_fooditem_final_price( $fooditem_id = 0, $user_purchase_info, $amount_override = null ) {
	if ( is_null( $amount_override ) ) {
		$original_price = get_post_meta( $fooditem_id, 'rpress_price', true );
	} else {
		$original_price = $amount_override;
	}
	if ( isset( $user_purchase_info['discount'] ) && $user_purchase_info['discount'] != 'none' ) {
		// if the discount was a %, we modify the amount. Flat rate discounts are ignored
		if ( rpress_get_discount_type( rpress_get_discount_id_by_code( $user_purchase_info['discount'] ) ) != 'flat' )
			$price = rpress_get_discounted_amount( $user_purchase_info['discount'], $original_price );
		else
			$price = $original_price;
	} else {
		$price = $original_price;
	}
	return apply_filters( 'rpress_final_price', $price, $fooditem_id, $user_purchase_info );
}

/**
 * Retrieves the variable prices for a fooditem
 *
 * @since 1.0.0
 * @param int $fooditem_id ID of the fooditem
 * @return array Variable prices
 */
function rpress_get_variable_prices( $fooditem_id = 0 ) {

	if( empty( $fooditem_id ) ) {
		return false;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_prices();
}

/**
 * Checks to see if a fooditem has variable prices enabled.
 *
 * @since 1.0.0
 * @param int $fooditem_id ID number of the fooditem to check
 * @return bool true if has variable prices, false otherwise
 */
function rpress_has_variable_prices( $fooditem_id = 0 ) {

	if( empty( $fooditem_id ) ) {
		return false;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->has_variable_prices();
}

/**
 * Returns the default price ID for variable pricing, or the first
 * price if none is set
 *
 * @since 1.0
 * @param  int $fooditem_id ID number of the fooditem to check
 * @return int              The Price ID to select by default
 */
function rpress_get_default_variable_price( $fooditem_id = 0 ) {

	if ( ! rpress_has_variable_prices( $fooditem_id ) ) {
		return false;
	}

	$prices = rpress_get_variable_prices( $fooditem_id );
	$default_price_id = get_post_meta( $fooditem_id, '_rpress_default_price_id', true );

	if ( $default_price_id === '' ||  ! isset( $prices[$default_price_id] ) ) {
		if( is_array( $prices) ) {
			$default_price_id = current( array_keys( $prices ) );
		}

	}

	return apply_filters( 'rpress_variable_default_price_id', absint( $default_price_id ), $fooditem_id );

}

/**
 * Retrieves the name of a variable price option
 *
 * @since 1.0.9
 * @param int $fooditem_id ID of the fooditem
 * @param int $price_id ID of the price option
 * @param int $payment_id optional payment ID for use in filters
 * @return string $price_name Name of the price option
 */
function rpress_get_price_option_name( $fooditem_id = 0, $price_id = 0, $payment_id = 0 ) {
	$prices = rpress_get_variable_prices( $fooditem_id );
	$price_name = '';

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$price_name = $prices[ $price_id ]['name'];
	}

	return apply_filters( 'rpress_get_price_option_name', $price_name, $fooditem_id, $payment_id, $price_id );
}

/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.0
 * @param int $fooditem_id ID of the fooditem
 * @param int $price_id ID of the price option
 * @param int $payment_id ID of the payment
 * @return float $amount Amount of the price option
 */
function rpress_get_price_option_amount( $fooditem_id = 0, $price_id = 0 ) {
	$prices = rpress_get_variable_prices( $fooditem_id );
	$amount = 0.00;

	if ( $prices && is_array( $prices ) ) {
		if ( isset( $prices[ $price_id ] ) )
			$amount = $prices[ $price_id ]['amount'];
	}

	return apply_filters( 'rpress_get_price_option_amount', rpress_sanitize_amount( $amount ), $fooditem_id, $price_id );
}

/**
 * Retrieves cheapest price option of a variable priced fooditem
 *
 * @since  1.0.0
 * @param int $fooditem_id ID of the fooditem
 * @return float Amount of the lowest price
 */
function rpress_get_lowest_price_option( $fooditem_id = 0 ) {
	if ( empty( $fooditem_id ) )
		$fooditem_id = get_the_ID();

	if ( ! rpress_has_variable_prices( $fooditem_id ) ) {
		return rpress_get_fooditem_price( $fooditem_id );
	}

	$prices = rpress_get_variable_prices( $fooditem_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}

		$low = $prices[ $min_id ]['amount'];

	}

	return rpress_sanitize_amount( $low );
}

/**
 * Retrieves the ID for the cheapest price option of a variable priced fooditem
 *
 * @since  1.0.0
 * @param int $fooditem_id ID of the fooditem
 * @return int ID of the lowest price
 */
function rpress_get_lowest_price_id( $fooditem_id = 0 ) {
	if ( empty( $fooditem_id ) )
		$fooditem_id = get_the_ID();

	if ( ! rpress_has_variable_prices( $fooditem_id ) ) {
		return rpress_get_fooditem_price( $fooditem_id );
	}

	$prices = rpress_get_variable_prices( $fooditem_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['amount'];
			} else {
				$min = min( $min, $price['amount'] );
			}

			if ( $price['amount'] == $min ) {
				$min_id = $key;
			}
		}
	}

	return (int) $min_id;
}

/**
 * Retrieves most expensive price option of a variable priced fooditem
 *
 * @since  1.0.0
 * @param int $fooditem_id ID of the fooditem
 * @return float Amount of the highest price
 */
function rpress_get_highest_price_option( $fooditem_id = 0 ) {

	if ( empty( $fooditem_id ) ) {
		$fooditem_id = get_the_ID();
	}

	if ( ! rpress_has_variable_prices( $fooditem_id ) ) {
		return rpress_get_fooditem_price( $fooditem_id );
	}

	$prices = rpress_get_variable_prices( $fooditem_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {

		$max = 0;

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['amount'] ) ) {
				continue;
			}

			$max = max( $max, $price['amount'] );

			if ( $price['amount'] == $max ) {
				$max_id = $key;
			}
		}

		$high = $prices[ $max_id ]['amount'];
	}

	return rpress_sanitize_amount( $high );
}

/**
 * Retrieves a price from from low to high of a variable priced fooditem
 *
 * @since  1.0.0
 * @param int $fooditem_id ID of the fooditem
 * @return string $range A fully formatted price range
 */
function rpress_price_range( $fooditem_id = 0 ) {
	$low   = rpress_get_lowest_price_option( $fooditem_id );
	$high  = rpress_get_highest_price_option( $fooditem_id );
	$range = '<span class="rpress_price rpress_price_range_low" id="rpress_price_low_' . $fooditem_id . '">' . rpress_currency_filter( rpress_format_amount( $low ) ) . '</span>';
	$range .= '<span class="rpress_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="rpress_price rpress_price_range_high" id="rpress_price_high_' . $fooditem_id . '">' . rpress_currency_filter( rpress_format_amount( $high ) ) . '</span>';

	return apply_filters( 'rpress_price_range', $range, $fooditem_id, $low, $high );
}

/**
 * Checks to see if multiple price options can be purchased at once
 *
 * @since 1.0.0
 * @param int $fooditem_id Item ID
 * @return bool
 */
function rpress_single_price_option_mode( $fooditem_id = 0 ) {

	if ( empty( $fooditem_id ) ) {
		$fooditem = get_post();

		$fooditem_id = isset( $fooditem->ID ) ? $fooditem->ID : 0;
	}

	if ( empty( $fooditem_id ) ) {
		return false;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->is_single_price_mode();

}

/**
 * Get product types
 *
 * @since 1.0
 * @return array $types Item types
 */
function rpress_get_fooditem_types() {

	$types = array(
		'0'       => __( 'Default', 'restropress' ),
		'bundle'  => __( 'Bundle', 'restropress' )
	);

	return apply_filters( 'rpress_fooditem_types', $types );
}

/**
 * Gets the Item type
 *
 * @since  1.0.0
 * @param int $fooditem_id Item ID
 * @return string $type Item type
 */
function rpress_get_fooditem_type( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->type;
}

/**
 * Determines if a product is a bundle
 *
 * @since  1.0.0
 * @param int $fooditem_id Item ID
 * @return bool
 */
function rpress_is_bundled_product( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->is_bundled_fooditem();
}


/**
 * Retrieves the product IDs of bundled products
 *
 * @since  1.0.0
 * @param int $fooditem_id Item ID
 * @return array $products Products in the bundle
 *
 * @since 1.0
 * @param int $price_id Variable price ID
 */
function rpress_get_bundled_products( $fooditem_id = 0, $price_id = null ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	if ( null !== $price_id ) {
		return $fooditem->get_variable_priced_bundled_fooditems( $price_id );
	} else {
		return $fooditem->bundled_fooditems;
	}
}

/**
 * Returns the total earnings for a fooditem.
 *
 * @since 1.0
 * @param int $fooditem_id Item ID
 * @return int $earnings Earnings for a certain fooditem
 */
function rpress_get_fooditem_earnings_stats( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->earnings;
}

/**
 * Return the sales number for a fooditem.
 *
 * @since 1.0
 * @param int $fooditem_id Item ID
 * @return int $sales Amount of sales for a certain fooditem
 */
function rpress_get_fooditem_sales_stats( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->sales;
}

/**
 * Record Sale In Log
 *
 * Stores log information for a fooditem sale.
 *
 * @since 1.0
 * @global $rpress_logs
 * @param int $fooditem_id Item ID
 * @param int $payment_id Payment ID
 * @param bool|int $price_id Price ID, if any
 * @param string|null $sale_date The date of the sale
 * @return void
*/
function rpress_record_sale_in_log( $fooditem_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	global $rpress_logs;

	$log_data = array(
		'post_parent'   => $fooditem_id,
		'log_type'      => 'sale',
		'post_date'     => ! empty( $sale_date ) ? $sale_date : null,
		'post_date_gmt' => ! empty( $sale_date ) ? get_gmt_from_date( $sale_date ) : null
	);

	$log_meta = array(
		'payment_id'    => $payment_id,
		'price_id'      => (int) $price_id
	);

	$rpress_logs->insert_log( $log_data, $log_meta );
}

/**
 * Record Order In Log
 *
 * Stores a log entry for a file fooditem.
 *
 * @since 1.0
 * @global $rpress_logs
 * @param int $fooditem_id Item ID
 * @param int $file_id ID of the file fooditemed
 * @param array $user_info User information (Deprecated)
 * @param string $ip IP Address
 * @param int $payment_id Payment ID
 * @param int $price_id Price ID, if any
 * @return void
 */
function rpress_record_fooditem_in_log( $fooditem_id = 0, $file_id, $user_info, $ip, $payment_id, $price_id = false ) {
	global $rpress_logs;

	$log_data = array(
		'post_parent' => $fooditem_id,
		'log_type'    => 'file_fooditem',
	);

	$payment = new RPRESS_Payment( $payment_id );

	$log_meta = array(
		'customer_id' => $payment->customer_id,
		'user_id'     => $payment->user_id,
		'file_id'     => (int) $file_id,
		'ip'          => $ip,
		'payment_id'  => $payment_id,
		'price_id'    => (int) $price_id,
	);

	$rpress_logs->insert_log( $log_data, $log_meta );
}

/**
 * Delete log entries when deleting fooditem product
 *
 * Removes all related log entries when a fooditem is completely deleted.
 * (Does not run when a fooditem is trashed)
 *
 * @since  1.0.0
 * @param int $fooditem_id ID
 * @return void
 */
function rpress_remove_fooditem_logs_on_delete( $fooditem_id = 0 ) {
	if ( 'fooditem' !== get_post_type( $fooditem_id ) )
		return;

	global $rpress_logs;

	// Remove all log entries related to this fooditem
	$rpress_logs->delete_logs( $fooditem_id );
}
add_action( 'delete_post', 'rpress_remove_fooditem_logs_on_delete' );

/**
 *
 * Increases the sale count of a fooditem.
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @param int $quantity Quantity to increase purchase count by
 * @return bool|int
 */
function rpress_increase_purchase_count( $fooditem_id = 0, $quantity = 1 ) {
	$quantity = (int) $quantity;
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->increase_sales( $quantity );
}

/**
 * Decreases the sale count of a fooditem. Primarily for when a purchase is
 * refunded.
 *
 * @since 1.0.0.1
 * @param int $fooditem_id ID
 * @return bool|int
 */
function rpress_decrease_purchase_count( $fooditem_id = 0, $quantity = 1 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->decrease_sales( $quantity );
}

/**
 * Increases the total earnings of a fooditem.
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @param int $amount Earnings
 * @return bool|int
 */
function rpress_increase_earnings( $fooditem_id = 0, $amount ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->increase_earnings( $amount );
}

/**
 * Decreases the total earnings of a fooditem. Primarily for when a purchase is refunded.
 *
 * @since 1.0.0.1
 * @param int $fooditem_id ID
 * @param int $amount Earnings
 * @return bool|int
 */
function rpress_decrease_earnings( $fooditem_id = 0, $amount ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->decrease_earnings( $amount );
}

/**
 * Retrieves the average monthly earnings for a specific fooditem
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @return float $earnings Average monthly earnings
 */
function rpress_get_average_monthly_fooditem_earnings( $fooditem_id = 0 ) {
	$earnings 	  = rpress_get_fooditem_earnings_stats( $fooditem_id );
	$release_date = get_post_field( 'post_date', $fooditem_id );

	$diff 	= abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 ) {
		$earnings = ( $earnings / $months );
	}

	return $earnings < 0 ? 0 : $earnings;
}

/**
 * Retrieves the average monthly sales for a specific fooditem
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @return float $sales Average monthly sales
 */
function rpress_get_average_monthly_fooditem_sales( $fooditem_id = 0 ) {
	$sales          = rpress_get_fooditem_sales_stats( $fooditem_id );
	$release_date   = get_post_field( 'post_date', $fooditem_id );

	$diff   = abs( current_time( 'timestamp' ) - strtotime( $release_date ) );

	$months = floor( $diff / ( 30 * 60 * 60 * 24 ) ); // Number of months since publication

	if ( $months > 0 )
		$sales = ( $sales / $months );

	return $sales;
}

/**
 * Gets all fooditem files for a product
 *
 * Can retrieve files specific to price ID
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @param int $variable_price_id Variable pricing option ID
 * @return array $files  files
 */
function rpress_get_fooditem_files( $fooditem_id = 0, $variable_price_id = null ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_files( $variable_price_id );
}

/**
 * Retrieves a file name for a product's fooditem file
 *
 * Defaults to the file's actual name if no 'name' key is present
 *
 * @since  1.0.0
 * @param array $file File array
 * @return string The file name
 */
function rpress_get_file_name( $file = array() ) {
	if( empty( $file ) || ! is_array( $file ) ) {
		return false;
	}

	$name = ! empty( $file['name'] ) ? esc_html( $file['name'] ) : basename( $file['file'] );

	return $name;
}

/**
 * Gets the number of times a file has been fooditemed for a specific purchase
 *
 * @since  1.0.0
 * @param int $fooditem_id ID
 * @param int $file_key File key
 * @param int $payment_id The ID number of the associated payment
 * @return int Number of times the file has been fooditemed for the purchase
 */
function rpress_get_file_fooditemed_count( $fooditem_id = 0, $file_key = 0, $payment_id = 0 ) {
	global $rpress_logs;

	$meta_query = array(
		'relation'	=> 'AND',
		array(
			'key' 	=> '_rpress_log_file_id',
			'value' => (int) $file_key
		),
		array(
			'key' 	=> '_rpress_log_payment_id',
			'value' => (int) $payment_id
		)
	);

	return $rpress_logs->get_log_count( $fooditem_id, 'file_fooditem', $meta_query );
}


/**
 * Gets the file fooditem file limit for a particular fooditem
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be fooditemed.
 *
 * @since  1.0.0
 * @param int $fooditem_id ID
 * @return int $limit File fooditem limit
 */
function rpress_get_file_fooditem_limit( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_file_fooditem_limit();
}

/**
 * Gets the file fooditem file limit override for a particular fooditem
 *
 * The override allows the main file fooditem limit to be bypassed
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @param int $payment_id Payment ID
 * @return int $limit_override The new limit
*/
function rpress_get_file_fooditem_limit_override( $fooditem_id = 0, $payment_id = 0 ) {
	$limit_override = get_post_meta( $fooditem_id, '_rpress_fooditem_limit_override_' . $payment_id, true );
	if ( $limit_override ) {
		return absint( $limit_override );
	}
	return 0;
}

/**
 * Sets the file fooditem file limit override for a particular fooditem
 *
 * The override allows the main file fooditem limit to be bypassed
 * If no override is set yet, the override is set to the main limit + 1
 * If the override is already set, then it is simply incremented by 1
 *
 * @since 1.0
 * @param int $fooditem_id ID
 * @param int $payment_id Payment ID
 * @return void
 */
function rpress_set_file_fooditem_limit_override( $fooditem_id = 0, $payment_id = 0 ) {
	$override 	= rpress_get_file_fooditem_limit_override( $fooditem_id, $payment_id );
	$limit 		= rpress_get_file_fooditem_limit( $fooditem_id );

	if ( ! empty( $override ) ) {
		$override = $override += 1;
	} else {
		$override = $limit += 1;
	}

	update_post_meta( $fooditem_id, '_rpress_fooditem_limit_override_' . $payment_id, $override );
}

/**
 * Checks if a file is at its fooditem limit
 *
 * This limit refers to the maximum number of times files connected to a product
 * can be fooditemed.
 *
 * @since  1.0.0
 * @uses RPRESS_Logging::get_log_count()
 * @param int $fooditem_id ID
 * @param int $payment_id Payment ID
 * @param int $file_id File ID
 * @param int $price_id Price ID
 * @return bool True if at limit, false otherwise
 */
function rpress_is_file_at_fooditem_limit( $fooditem_id = 0, $payment_id = 0, $file_id = 0, $price_id = false ) {

	// Checks to see if at limit
	$logs = new RPRESS_Logging();

	$meta_query = array(
		'relation'	=> 'AND',
		array(
			'key' 	=> '_rpress_log_file_id',
			'value' => (int) $file_id
		),
		array(
			'key' 	=> '_rpress_log_payment_id',
			'value' => (int) $payment_id
		),
		array(
			'key' 	=> '_rpress_log_price_id',
			'value' => (int) $price_id
		)
	);

	$ret                = false;
	$fooditem_count     = $logs->get_log_count( $fooditem_id, 'file_fooditem', $meta_query );

	$fooditem_limit     = rpress_get_file_fooditem_limit( $fooditem_id );
	$unlimited_purchase = rpress_payment_has_unlimited_fooditems( $payment_id );

	if ( ! empty( $fooditem_limit ) && empty( $unlimited_purchase ) ) {
		if ( $fooditem_count >= $fooditem_limit ) {
			$ret = true;

			// Check to make sure the limit isn't overwritten
			// A limit is overwritten when purchase receipt is resent
			$limit_override = rpress_get_file_fooditem_limit_override( $fooditem_id, $payment_id );

			if ( ! empty( $limit_override ) && $fooditem_count < $limit_override ) {
				$ret = false;
			}
		}
	}

	return (bool) apply_filters( 'rpress_is_file_at_fooditem_limit', $ret, $fooditem_id, $payment_id, $file_id );
}

/**
 * Gets the Price ID that can fooditem a file
 *
 * @since 1.0.9
 * @param int $fooditem_id ID
 * @param string $file_key File Key
 * @return string - the price ID if restricted, "all" otherwise
 */
function rpress_get_file_price_condition( $fooditem_id = 0, $file_key ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_file_price_condition( $file_key );
}

/**
 * Get Item Url
 * Constructs a secure file fooditem url for a specific file.
 *
 * @since 1.0
 *
 * @param string    $key Payment key. Use rpress_get_payment_key() to get key.
 * @param string    $email Customer email address. Use rpress_get_payment_user_email() to get user email.
 * @param int       $filekey Index of array of files returned by rpress_get_fooditem_files() that this fooditem link is for.
 * @param int       $fooditem_id Optional. ID of fooditem this fooditem link is for. Default is 0.
 * @param bool|int  $price_id Optional. Price ID when using variable prices. Default is false.
 *
 * @return string A secure fooditem URL
 */
function rpress_get_fooditem_file_url( $key, $email, $filekey, $fooditem_id = 0, $price_id = false ) {

	$hours = absint( rpress_get_option( 'fooditem_link_expiration', 24 ) );

	if ( ! ( $date = strtotime( '+' . $hours . 'hours', current_time( 'timestamp') ) ) ) {
		$date = 2147472000; // Highest possible date, January 19, 2038
	}

	// Leaving in this array and the filter for backwards compatibility now
	$old_args = array(
		'fooditem_key' 	=> rawurlencode( $key ),
		'email'         => rawurlencode( $email ),
		'file'          => rawurlencode( $filekey ),
		'price_id'      => (int) $price_id,
		'fooditem_id'   => $fooditem_id,
		'expire'        => rawurlencode( $date )
	);

	$params  = apply_filters( 'rpress_fooditem_file_url_args', $old_args );
	$payment = rpress_get_payment_by( 'key', $params['fooditem_key'] );

	if ( ! $payment ) {
		return false;
	}

	$args = array();

	if ( ! empty( $payment->ID ) ) {

		// Simply the URL by concatenating required data using a colon as a delimiter.
		$args = array(
			'rpressfile' => rawurlencode( sprintf( '%d:%d:%d:%d', $payment->ID, $params['fooditem_id'], $params['file'], $price_id ) )
		);

		if ( isset( $params['expire'] ) ) {
			$args['ttl'] = $params['expire'];
		}

		// Ensure all custom args registered with extensions through rpress_fooditem_file_url_args get added to the URL, but without adding all the old args
		$args = array_merge( $args, array_diff_key( $params, $old_args ) );

		$args = apply_filters( 'rpress_get_fooditem_file_url_args', $args, $payment->ID, $params );

		$args['file']  = $params['file'];
		$args['token'] = rpress_get_fooditem_token( add_query_arg( $args, untrailingslashit( site_url() ) ) );
	}

	$fooditem_url = add_query_arg( $args, site_url( 'index.php' ) );

	return $fooditem_url;
}

/**
 * Get product notes
 *
 * @since  1.0.0
 * @param int $fooditem_id ID
 * @return string $notes Product notes
 */
function rpress_get_product_notes( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->notes;
}

/**
 * Retrieves a fooditem SKU by ID.
 *
 * @since  1.0.0
 *
 * @author RestroPress
 * @param int $fooditem_id
 *
 * @return mixed|void SKU
 */
function rpress_get_fooditem_sku( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->sku;
}

/**
 * get the button behavior, either add to cart or direct
 *
 * @since  1.0.0
 *
 * @param int $fooditem_id
 * @return mixed|void Add to Cart or Direct
 */
function rpress_get_fooditem_button_behavior( $fooditem_id = 0 ) {
	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->button_behavior;
}

/**
 * Is quantity input disabled on this product?
 *
 * @since 1.0
 * @return bool
 */
function rpress_fooditem_quantities_disabled( $fooditem_id = 0 ) {

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->quantities_disabled();
}

/**
 * Get the  method
 *
 * @since  1.0.0
 * @return string The method to use for file fooditems
 */
function rpress_get_file_fooditem_method() {
	$method = rpress_get_option( 'fooditem_method', 'direct' );
	return apply_filters( 'rpress_file_fooditem_method', $method );
}

/**
 * Returns a random fooditem
 *
 * @since  1.0.0
 * @author RestroPress
 * @param bool $post_ids True for array of post ids, false if array of posts
 * @return array Returns an array of post ids or post objects
 */
function rpress_get_random_fooditem( $post_ids = true ) {
	 return rpress_get_random_fooditems( 1, $post_ids );
}

/**
 * Returns random fooditems
 *
 * @since  1.0.0
 * @author RestroPress
 * @param int $num The number of posts to return
 * @param bool $post_ids True for array of post objects, else array of ids
 * @return mixed $query Returns an array of id's or an array of post objects
 */
function rpress_get_random_fooditems( $num = 3, $post_ids = true ) {
	if ( $post_ids ) {
		$args = array( 'post_type' => 'fooditem', 'orderby' => 'rand', 'numberposts' => $num, 'fields' => 'ids' );
	} else {
		$args = array( 'post_type' => 'fooditem', 'orderby' => 'rand', 'numberposts' => $num );
	}
	$args  = apply_filters( 'rpress_get_random_fooditems', $args );
	return get_posts( $args );
}

/**
 * Generates a token for a given URL.
 *
 * An 'o' query parameter on a URL can include optional variables to test
 * against when verifying a token without passing those variables around in
 * the URL. For example, fooditems can be limited to the IP that the URL was
 * generated for by adding 'o=ip' to the query string.
 *
 * Or suppose when WordPress requested a URL for automatic updates, the user
 * agent could be tested to ensure the URL is only valid for requests from
 * that user agent.
 *
 * @since 1.0
 *
 * @param string $url The URL to generate a token for.
 * @return string The token for the URL.
 */
function rpress_get_fooditem_token( $url = '' ) {

	$args    = array();
	$hash    = apply_filters( 'rpress_get_url_token_algorithm', 'sha256' );
	$secret  = apply_filters( 'rpress_get_url_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options ) ) {

				$args['ip'] = rpress_get_ip();

			}

			if ( in_array( 'ua', $options ) ) {

				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$args['user_agent'] = rawurlencode( $ua );

			}

		}

	}

	/*
	 * Filter to modify arguments and allow custom options to be tested.
	 * Be sure to rawurlencode any custom options for consistent results.
	 */
	$args = apply_filters( 'rpress_get_url_token_args', $args, $url, $options );

	$args['secret'] = $secret;
	$args['token']  = false; // Removes a token if present.

	$url   = add_query_arg( $args, $url );
	$parts = parse_url( $url );

	// In the event there isn't a path, set an empty one so we can MD5 the token
	if ( ! isset( $parts['path'] ) ) {

		$parts['path'] = '';

	}

	$token = md5( $parts['path'] . '?' . $parts['query'] );

	return $token;

}

/**
 * Generate a token for a URL and match it against the existing token to make
 * sure the URL hasn't been tampered with.
 *
 * @since 1.0
 *
 * @param string $url URL to test.
 * @return bool
 */
function rpress_validate_url_token( $url = '' ) {

	$ret   = false;
	$parts = parse_url( $url );

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// These are the only URL parameters that are allowed to affect the token validation
		$allowed = apply_filters( 'rpress_url_token_allowed_params', array(
			'rpressfile',
			'file',
			'ttl',
			'token'
		) );

		// Parameters that will be removed from the URL before testing the token
		$remove = array();

		foreach( $query_args as $key => $value ) {
			if( false === in_array( $key, $allowed ) ) {
				$remove[] = $key;
			}
		}

		if( ! empty( $remove ) ) {

			$url = remove_query_arg( $remove, $url );

		}

		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

			wp_die( apply_filters( 'rpress_fooditem_link_expired_text', __( 'Sorry but your fooditem link has expired.', 'restropress' ) ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );

		}

		if ( isset( $query_args['token'] ) && $query_args['token'] == rpress_get_fooditem_token( $url ) ) {

			$ret = true;

		}

	}

	return apply_filters( 'rpress_validate_url_token', $ret, $url, $query_args );
}

/**
 * Allows parsing of the values saved by the product drop down.
 *
 * @since  1.0.0.9
 * @param  array $values Parse the values from the product dropdown into a readable array
 * @return array         A parsed set of values for fooditem_id and price_id
 */
function rpress_parse_product_dropdown_values( $values = array() ) {

	$parsed_values = array();

	if ( is_array( $values ) ) {

		foreach ( $values as $value ) {
			$value = rpress_parse_product_dropdown_value( $value );

			$parsed_values[] = array(
				'fooditem_id' => $value['fooditem_id'],
				'price_id'    => $value['price_id'],
			);
		}

	} else {

		$value = rpress_parse_product_dropdown_value( $values );
		$parsed_values[] = array(
			'fooditem_id' => $value['fooditem_id'],
			'price_id'    => $value['price_id'],
		);

	}

	return $parsed_values;
}

/**
 * Given a value from the product dropdown array, parse it's parts
 *
 * @since  1.0.0.9
 * @param  string $values A value saved in a product dropdown array
 * @return array          A parsed set of values for fooditem_id and price_id
 */
function rpress_parse_product_dropdown_value( $value ) {
	$parts       = explode( '_', $value );
	$fooditem_id = $parts[0];
	$price_id    = isset( $parts[1] ) ? $parts[1] : false;

	return array( 'fooditem_id' => $fooditem_id, 'price_id' => $price_id );
}

/**
 * Get bundle pricing variations
 *
 * @since 1.0
 * @param  int $fooditem_id
 * @return array|void
 */
function rpress_get_bundle_pricing_variations( $fooditem_id = 0 ) {
	if ( $fooditem_id == 0 ) {
		return;
	}

	$fooditem = new RPRESS_Fooditem( $fooditem_id );
	return $fooditem->get_bundle_pricing_variations();
}
