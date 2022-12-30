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

	$price = apply_filters( 'rpress_fooditem_price', rpress_sanitize_amount( $price ), $fooditem_id, $price_id );
	$formatted_price = '<span class="rpress_price" id="rpress_price_' . $fooditem_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'rpress_fooditem_price_after_html', $formatted_price, $fooditem_id, $price, $price_id );

	if ( $echo ) {
		echo wp_kses_post( $formatted_price );
	} else {
		return wp_kses_post( $formatted_price );
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
function rpress_get_fooditem_final_price( $fooditem_id = 0, $user_purchase_info = null, $amount_override = null ) {
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

	if ( $default_price_id === '' ||  ! isset( $prices[ $default_price_id ] ) ) {
		if( is_array( $prices ) ) {
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
	if( $low < $high ){
		$range .= '<span class="rpress_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
		$range .= '<span class="rpress_price rpress_price_range_high" id="rpress_price_high_' . $fooditem_id . '">' . rpress_currency_filter( rpress_format_amount( $high ) ) . '</span>';
	}
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
function rpress_record_sale_in_log( $fooditem_id = 0, $payment_id = null, $price_id = false, $sale_date = null ) {
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
function rpress_increase_earnings( $fooditem_id = 0, $amount = null ) {
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
function rpress_decrease_earnings( $fooditem_id = 0, $amount = null ) {
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

/**
 * Returns the addon categories
 * @param  integer $parent id of the parent category
 * @return array   array of cateories
 * @since 3.0
 */
function rpress_get_addons( $parent = 0 ) {

  $addons_args = apply_filters(
    'rpress_get_addons_args',
    array(
      'taxonomy'  	=> 'addon_category',
      'orderby'   	=> 'name',
      'parent'    	=> $parent,
      'hide_empty'  => false
    )
  );

  $addons = get_terms( $addons_args );

  return apply_filters( 'rpress_get_addons', $addons );
}


/**
 * Returns the addon meta data
 * @param  integer $term id of the addon
 * @param  string $field of the addon
 * @return array   array of cateories
 * @since 3.0
 */
function rpress_get_addon_data( $term_id, $field ) {
  
	if ( ! $term_id ) return;
	
	$meta = get_term_meta( $term_id, $field, true );
	
	return apply_filters( 'rpress_addon_meta', $meta, $field );
}

/**
 * Get addon type
 */
function rpress_get_addon_types() {

  $addon_types = apply_filters(
    'rpress_addon_types',
    array(
      'single'    => 'Single',
      'multiple'  => 'Multiple'
    )
  );

  return apply_filters( 'rpress_addon_types', $addon_types );
}

/**
 * Get dynamic price of addon
 */
function rpress_dynamic_addon_price( $post_id, $child_addon, $parent_addon = null, $price_id = null ) {

	if( is_null( $price_id ) )
		$price_id = 0;

	if( is_null( $parent_addon ) ) {
		$term = get_term( $child_addon, 'addon_category' );
		$parent_addon = $term->parent;
	}

	$addon_price 	= rpress_get_addon_data( $child_addon, '_price' );
	$item_addons 	= get_post_meta( $post_id, '_addon_items', true );

	if( empty( $item_addons ) )
		return $addon_price;

	if( ! isset( $item_addons[ $parent_addon ]['prices'] ) )
		return $addon_price;

	if( rpress_has_variable_prices( $post_id ) ) {
		$prices = rpress_get_variable_prices( $post_id );
		$name = sanitize_key( $prices[ $price_id ]['name'] );
		$addon_price = $item_addons[$parent_addon]['prices'][ $child_addon ][ $name ];
	} else {
		$addon_price = $item_addons[ $parent_addon ]['prices'][ $child_addon ];
	}


	return $addon_price;
}
