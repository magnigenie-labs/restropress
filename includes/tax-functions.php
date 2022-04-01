<?php
/**
 * Tax Functions
 *
 * These are functions used for checking if taxes are enabled, calculating taxes, etc.
 * Functions for retrieving tax amounts and such for individual payments are in
 * includes/payment-functions.php and includes/cart-functions.php
 *
 * @package     RPRESS
 * @subpackage  Functions/Taxes
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Checks if taxes are enabled by using the option set from the RPRESS Settings.
 * The value returned can be filtered.
 *
 * @since 1.0.0
 * @return bool Whether or not taxes are enabled
 */
function rpress_use_taxes() {
	$ret = rpress_get_option( 'enable_taxes', false );
	return (bool) apply_filters( 'rpress_use_taxes', $ret );
}

/**
 * Retrieve tax rates
 *
 * @since  1.0.0
 * @return array Defined tax rates
 */
function rpress_get_tax_rates() {
	$rates = get_option( 'rpress_tax_rates', array() );
	return apply_filters( 'rpress_get_tax_rates', $rates );
}

/**
 * Get taxation rate
 *
 * @since 1.0.0
 * @param bool $country
 * @param bool $state
 * @return mixed|void
 */
function rpress_get_tax_rate( $country = false, $state = false ) {

    $rate = (float) rpress_get_option( 'tax_rate', 0 );
    // Convert to a number we can use
    $rate = $rate / 100;
    return apply_filters( 'rpress_tax_rate', $rate, $country, $state );
}

/**
 * Retrieve a fully formatted tax rate
 *
 * @since  1.0.0
 * @param string $country The country to retrieve a rate for
 * @param string $state The state to retrieve a rate for
 * @return string Formatted rate
 */
function rpress_get_formatted_tax_rate( $country = false, $state = false ) {
	$rate = rpress_get_tax_rate( $country, $state );
	$rate = round( $rate * 100, 4 );
	$formatted = $rate .= '%';
	return apply_filters( 'rpress_formatted_tax_rate', $formatted, $rate, $country, $state );
}

/**
 * Calculate the taxed amount
 *
 * @since 1.0.0
 * @param $amount float The original amount to calculate a tax cost
 * @param $country string The country to calculate tax for. Will use default if not passed
 * @param $state string The state to calculate tax for. Will use default if not passed
 * @return float $tax Taxed amount
 */
function rpress_calculate_tax( $amount = 0, $country = false, $state = false ) {
	$rate = rpress_get_tax_rate( $country, $state );
	$tax  = 0.00;

	if ( rpress_use_taxes() && $amount > 0 ) {
		if ( rpress_prices_include_tax() ) {
			$pre_tax = ( $amount / ( 1 + $rate ) );
			$tax = floatval($amount) - $pre_tax;
		} else {
			$tax = floatval($amount) * $rate;
		}
	}

	return apply_filters( 'rpress_taxed_amount', floatval( $tax ), $rate, $country, $state );
}

/**
 * Returns the formatted tax amount for the given year
 *
 * @since 1.0.0
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses rpress_get_sales_tax_for_year()
 * @return void
*/
function rpress_sales_tax_for_year( $year = null ) {
	echo rpress_currency_filter( rpress_format_amount( rpress_get_sales_tax_for_year( $year ) ) );
}

/**
 * Gets the sales tax for the given year
 *
 * @since 1.0.0
 * @param $year int The year to retrieve taxes for, i.e. 2012
 * @uses rpress_get_payment_tax()
 * @return float $tax Sales tax
 */
function rpress_get_sales_tax_for_year( $year = null ) {
	global $wpdb;

	// Start at zero
	$tax = 0;

	if ( ! empty( $year ) ) {


		$args = array(
			'post_type'      => 'rpress_payment',
			'post_status'    => array( 'publish', 'revoked' ),
			'posts_per_page' => -1,
			'year'           => $year,
			'fields'         => 'ids'
		);

		$payments    = get_posts( $args );
		$payment_ids = implode( ',', $payments );

		if ( count( $payments ) > 0 ) {
			$sql = "SELECT SUM( meta_value ) FROM $wpdb->postmeta WHERE meta_key = '_rpress_payment_tax' AND post_id IN( $payment_ids )";
			$tax = $wpdb->get_var( $sql );
		}

	}

	return apply_filters( 'rpress_get_sales_tax_for_year', $tax, $year );
}

/**
 * Is the cart taxed?
 *
 * This used to include a check for local tax opt-in, but that was ripped out in v1.6, so this is just a wrapper now
 *
 * @since 1.0
 * @return bool
 */
function rpress_is_cart_taxed() {
	return rpress_use_taxes();
}

/**
 * Check if the individual product prices include tax
 *
 * @since 1.0
 * @return bool $include_tax
*/
function rpress_prices_include_tax() {
	$ret = ( rpress_get_option( 'prices_include_tax', false ) == 'yes' && rpress_use_taxes() );

	return apply_filters( 'rpress_prices_include_tax', $ret );
}

/**
 * Checks whether the user has enabled display of taxes on the checkout
 *
 * @since 1.0
 * @return bool $include_tax
 */
function rpress_prices_show_tax_on_checkout() {
	$ret = ( rpress_get_option( 'checkout_include_tax', false ) == 'yes' && rpress_use_taxes() );

	return apply_filters( 'rpress_taxes_on_prices_on_checkout', $ret );
}

/**
 * Should we show address fields for taxation purposes?
 *
 * @since 1.y
 * @return bool
 */
function rpress_cart_needs_tax_address_fields() {

	if( ! rpress_is_cart_taxed() )
		return false;

	return ! did_action( 'rpress_after_cc_fields', 'rpress_default_cc_address_fields' );
}

/**
 * Is this Item excluded from tax?
 *
 * @since  1.0.0
 * @return bool
 */
function rpress_fooditem_is_tax_exclusive( $fooditem_id = 0 ) {
	$ret = false;
	return apply_filters( 'rpress_fooditem_is_tax_exclusive', $ret, $fooditem_id );
}

/**
 * Is this Addon excluded from tax?
 *
 * @since  2.7.3
 * @return bool
 */
function rpress_addon_is_tax_exclusive( $addon_id = 0 ) {
	$ret = 'no';
	return apply_filters( 'rpress_addon_is_tax_exclusive', $ret, $addon_id );
}

/**
 * Get tax name
 *
 * @since  2.6
 * @return string
 */
function rpress_get_tax_name() {

    $tax_name = rpress_get_option( 'tax_name', '' );

    if ( empty( $tax_name ) ) {
        $tax_name = __( 'Estimated Tax', 'restropress' );
    }

    $tax_name = apply_filters( 'rpress_tax_name', $tax_name );

    return $tax_name;
}

/**
 * Checks whether it needs to show the billing details or not.
 *
 * @since 2.5.5
 * @return bool Whether or the fields needs to be shown
 */
function rpress_show_billing_fields() {
    $enable_fields = rpress_get_option( 'enable_billing_fields', false );
    return (bool) apply_filters( 'rpress_show_billing_fields', $enable_fields );
}