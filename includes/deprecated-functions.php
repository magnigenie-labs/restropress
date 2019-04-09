<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     RPRESS
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Download Sales Log
 *
 * Returns an array of sales and sale info for a fooditem.
 *
 * @since       1.0
 * @deprecated  1.3.4
 *
 * @param int $fooditem_id ID number of the fooditem to retrieve a log for
 * @param bool $paginate Whether to paginate the results or not
 * @param int $number Number of results to return
 * @param int $offset Number of items to skip
 *
 * @return mixed array|bool
*/
function rpress_get_fooditem_sales_log( $fooditem_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$sales_log = get_post_meta( $fooditem_id, '_rpress_sales_log', true );

	if ( $sales_log ) {
		$sales_log = array_reverse( $sales_log );
		$log = array();
		$log['number'] = count( $sales_log );
		$log['sales'] = $sales_log;

		if ( $paginate ) {
			$log['sales'] = array_slice( $sales_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get File Download Log
 *
 * Returns an array of file fooditem dates and user info.
 *
 * @deprecated 1.3.4
 * @since 1.0
 *
 * @param int $fooditem_id the ID number of the fooditem to retrieve a log for
 * @param bool $paginate whether to paginate the results or not
 * @param int $number the number of results to return
 * @param int $offset the number of items to skip
 *
 * @return mixed array|bool
*/
function rpress_get_file_fooditem_log( $fooditem_id, $paginate = false, $number = 10, $offset = 0 ) {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.3.4', null, $backtrace );

	$fooditem_log = get_post_meta( $fooditem_id, '_rpress_file_fooditem_log', true );

	if ( $fooditem_log ) {
		$fooditem_log = array_reverse( $fooditem_log );
		$log = array();
		$log['number'] = count( $fooditem_log );
		$log['fooditems'] = $fooditem_log;

		if ( $paginate ) {
			$log['fooditems'] = array_slice( $fooditem_log, $offset, $number );
		}

		return $log;
	}

	return false;
}

/**
 * Get RestroPress Of Purchase
 *
 * Retrieves an array of all files purchased.
 *
 * @since 1.0
 * @deprecated 1.4
 *
 * @param int  $payment_id ID number of the purchase
 * @param null $payment_meta
 * @return bool|mixed
 */
function rpress_get_fooditems_of_purchase( $payment_id, $payment_meta = null ) {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.4', 'rpress_get_payment_meta_fooditems', $backtrace );

	if ( is_null( $payment_meta ) ) {
		$payment_meta = rpress_get_payment_meta( $payment_id );
	}

	$fooditems = maybe_unserialize( $payment_meta['fooditems'] );

	if ( $fooditems )
		return $fooditems;

	return false;
}

/**
 * Get Menu Access Level
 *
 * Returns the access level required to access the fooditems menu. Currently not
 * changeable, but here for a future update.
 *
 * @since 1.0
 * @deprecated 1.4.4
 * @return string
*/
function rpress_get_menu_access_level() {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.4.4', 'current_user_can(\'manage_shop_settings\')', $backtrace );

	return apply_filters( 'rpress_menu_access_level', 'manage_options' );
}



/**
 * Check if only local taxes are enabled meaning users must opt in by using the
 * option set from the RPRESS Settings.
 *
 * @since 1.0.0
 * @deprecated 1.6
 * @global $rpress_options
 * @return bool $local_only
 */
function rpress_local_taxes_only() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	global $rpress_options;

	$local_only = isset( $rpress_options['tax_condition'] ) && $rpress_options['tax_condition'] == 'local';

	return apply_filters( 'rpress_local_taxes_only', $local_only );
}

/**
 * Checks if a customer has opted into local taxes
 *
 * @since 1.0
 * @deprecated 1.6
 * @uses RPRESS_Session::get()
 * @return bool
 */
function rpress_local_tax_opted_in() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.6', 'no alternatives', $backtrace );

	$opted_in = RPRESS()->session->get( 'rpress_local_tax_opt_in' );
	return ! empty( $opted_in );
}

/**
 * Show taxes on individual prices?
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @global $rpress_options
 * @return bool Whether or not to show taxes on prices
 */
function rpress_taxes_on_prices() {
	global $rpress_options;

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.9', 'no alternatives', $backtrace );

	return apply_filters( 'rpress_taxes_on_prices', isset( $rpress_options['taxes_on_prices'] ) );
}

/**
 * Show Has Purchased Item Message
 *
 * Prints a notice when user has already purchased the item.
 *
 * @since 1.0
 * @deprecated 1.8
 * @global $user_ID
 */
function rpress_show_has_purchased_item_message() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.8', 'no alternatives', $backtrace );

	global $user_ID, $post;

	if( !isset( $post->ID ) )
		return;

	if ( rpress_has_user_purchased( $user_ID, $post->ID ) ) {
		$alert = '<p class="rpress_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'restropress' ) . '</p>';
		echo apply_filters( 'rpress_show_has_purchased_item_message', $alert );
	}
}

/**
 * Flushes the total earning cache when a new payment is created
 *
 * @since 1.0.0
 * @deprecated 1.8.4
 * @param int $payment Payment ID
 * @param array $payment_data Payment Data
 * @return void
 */
function rpress_clear_earnings_cache( $payment, $payment_data ) {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.8.4', 'no alternatives', $backtrace );

	delete_transient( 'rpress_total_earnings' );
}
//add_action( 'rpress_insert_payment', 'rpress_clear_earnings_cache', 10, 2 );

/**
 * Get Cart Amount
 *
 * @since 1.0
 * @deprecated 1.9
 * @param bool $add_taxes Whether to apply taxes (if enabled) (default: true)
 * @param bool $local_override Force the local opt-in param - used for when not reading $_POST (default: false)
 * @return float Total amount
*/
function rpress_get_cart_amount( $add_taxes = true, $local_override = false ) {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '1.9', 'rpress_get_cart_subtotal() or rpress_get_cart_total()', $backtrace );

	$amount = rpress_get_cart_subtotal( );
	if ( ! empty( $_POST['rpress-discount'] ) || rpress_get_cart_discounts() !== false ) {
		// Retrieve the discount stored in cookies
		$discounts = rpress_get_cart_discounts();

		// Check for a posted discount
		$posted_discount = isset( $_POST['rpress-discount'] ) ? trim( $_POST['rpress-discount'] ) : '';

		if ( $posted_discount && ! in_array( $posted_discount, $discounts ) ) {
			// This discount hasn't been applied, so apply it
			$amount = rpress_get_discounted_amount( $posted_discount, $amount );
		}

		if( ! empty( $discounts ) ) {
			// Apply the discounted amount from discounts already applied
			$amount -= rpress_get_cart_discounted_amount();
		}
	}

	if ( rpress_use_taxes() && rpress_is_cart_taxed() && $add_taxes ) {
		$tax = rpress_get_cart_tax();
		$amount += $tax;
	}

	if( $amount < 0 )
		$amount = 0.00;

	return apply_filters( 'rpress_get_cart_amount', $amount, $add_taxes, $local_override );
}

/**
 * Get Purchase Receipt Template Tags
 *
 * Displays all available template tags for the purchase receipt.
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @author RestroPress
 * @return string $tags
 */
function rpress_get_purchase_receipt_template_tags() {
	$tags = __('Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:','restropress' ) . '<br/>' .
			'{fooditem_list} - ' . __('A list of fooditem purchased','restropress' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','restropress' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','restropress' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','restropress' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','restropress' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','restropress' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','restropress' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','restropress' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','restropress' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','restropress' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','restropress' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','restropress' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','restropress' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','restropress' ) . '<br/>' .
			'{receipt_link} - ' . __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'restropress' );

	return apply_filters( 'rpress_purchase_receipt_template_tags_description', $tags );
}


/**
 * Get Sale Notification Template Tags
 *
 * Displays all available template tags for the sale notification email
 *
 * @since  1.0.0
 * @deprecated 1.9
 * @author RestroPress
 * @return string $tags
 */
function rpress_get_sale_notification_template_tags() {
	$tags = __( 'Enter the email that is sent to sale notification emails after completion of a purchase. HTML is accepted. Available template tags:', 'restropress' ) . '<br/>' .
			'{fooditem_list} - ' . __('A list of fooditem purchased','restropress' ) . '<br/>' .
			'{file_urls} - ' . __('A plain-text list of fooditem URLs for each fooditem purchased','restropress' ) . '<br/>' .
			'{name} - ' . __('The buyer\'s first name','restropress' ) . '<br/>' .
			'{fullname} - ' . __('The buyer\'s full name, first and last','restropress' ) . '<br/>' .
			'{username} - ' . __('The buyer\'s user name on the site, if they registered an account','restropress' ) . '<br/>' .
			'{user_email} - ' . __('The buyer\'s email address','restropress' ) . '<br/>' .
			'{billing_address} - ' . __('The buyer\'s billing address','restropress' ) . '<br/>' .
			'{date} - ' . __('The date of the purchase','restropress' ) . '<br/>' .
			'{subtotal} - ' . __('The price of the purchase before taxes','restropress' ) . '<br/>' .
			'{tax} - ' . __('The taxed amount of the purchase','restropress' ) . '<br/>' .
			'{price} - ' . __('The total price of the purchase','restropress' ) . '<br/>' .
			'{payment_id} - ' . __('The unique ID number for this purchase','restropress' ) . '<br/>' .
			'{receipt_id} - ' . __('The unique ID number for this purchase receipt','restropress' ) . '<br/>' .
			'{payment_method} - ' . __('The method of payment used for this purchase','restropress' ) . '<br/>' .
			'{sitename} - ' . __('Your site name','restropress' );

	return apply_filters( 'rpress_sale_notification_template_tags_description', $tags );
}

/**
 * Email Template Header
 *
 * @access private
 * @since 1.0
 * @deprecated 2.0
 * @return string Email template header
 */
function rpress_get_email_body_header() {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	?>
	<html>
	<head>
		<style type="text/css">#outlook a { padding: 0; }</style>
	</head>
	<body dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
	<?php
	do_action( 'rpress_email_body_header' );
	return ob_get_clean();
}

/**
 * Email Template Footer
 *
 * @since 1.0
 * @deprecated 2.0
 * @return string Email template footer
 */
function rpress_get_email_body_footer() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	ob_start();
	do_action( 'rpress_email_body_footer' );
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Applies the Chosen Email Template
 *
 * @since 1.0
 * @deprecated 2.0
 * @param string $body The contents of the receipt email
 * @param int $payment_id The ID of the payment we are sending a receipt for
 * @param array $payment_data An array of meta information for the payment
 * @return string $email Formatted email with the template applied
 */
function rpress_apply_email_template( $body, $payment_id, $payment_data=array() ) {
	global $rpress_options;

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.0', '', $backtrace );

	$template_name = isset( $rpress_options['email_template'] ) ? $rpress_options['email_template'] : 'default';
	$template_name = apply_filters( 'rpress_email_template', $template_name, $payment_id );

	if ( $template_name == 'none' ) {
		if ( is_admin() )
			$body = rpress_email_preview_template_tags( $body );

		return $body; // Return the plain email with no template
	}

	ob_start();

	do_action( 'rpress_email_template_' . $template_name );

	$template = ob_get_clean();

	if ( is_admin() )
		$body = rpress_email_preview_template_tags( $body );

	$body = apply_filters( 'rpress_purchase_receipt_' . $template_name, $body );

	$email = str_replace( '{email}', $body, $template );

	return $email;

}

/**
 * Checks if the user has enabled the option to calculate taxes after discounts
 * have been entered
 *
 * @since 1.0
 * @deprecated 2.1
 * @global $rpress_options
 * @return bool Whether or not taxes are calculated after discount
 */
function rpress_taxes_after_discounts() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.1', 'none', $backtrace );

	global $rpress_options;
	$ret = isset( $rpress_options['taxes_after_discounts'] ) && rpress_use_taxes();
	return apply_filters( 'rpress_taxes_after_discounts', $ret );
}

/**
 * Verifies a fooditem purchase using a purchase key and email.
 *
 * @deprecated Please avoid usage of this function in favor of the tokenized urls with rpress_validate_url_token()
 * introduced in RPRESS 2.3
 *
 * @since 1.0
 *
 * @param int    $fooditem_id
 * @param string $key
 * @param string $email
 * @param string $expire
 * @param int    $file_key
 *
 * @return bool True if payment and link was verified, false otherwise
 */
function rpress_verify_fooditem_link( $fooditem_id = 0, $key = '', $email = '', $expire = '', $file_key = 0 ) {

	$meta_query = array(
		'relation'  => 'AND',
		array(
			'key'   => '_rpress_payment_purchase_key',
			'value' => $key
		),
		array(
			'key'   => '_rpress_payment_user_email',
			'value' => $email
		)
	);

	$accepted_stati = apply_filters( 'rpress_allowed_fooditem_stati', array( 'publish', 'complete' ) );

	$payments = get_posts( array( 'meta_query' => $meta_query, 'post_type' => 'rpress_payment', 'post_status' => $accepted_stati ) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$cart_details = rpress_get_payment_meta_cart_details( $payment->ID, true );

			if ( ! empty( $cart_details ) ) {
				foreach ( $cart_details as $cart_key => $cart_item ) {

					if ( $cart_item['id'] != $fooditem_id )
						continue;

					$price_options 	= isset( $cart_item['item_number']['options'] ) ? $cart_item['item_number']['options'] : false;
					$price_id 		= isset( $price_options['price_id'] ) ? $price_options['price_id'] : false;

					$file_condition = rpress_get_file_price_condition( $cart_item['id'], $file_key );

					// Check to see if the file fooditem limit has been reached
					if ( rpress_is_file_at_fooditem_limit( $cart_item['id'], $payment->ID, $file_key, $price_id ) )
						wp_die( apply_filters( 'rpress_fooditem_limit_reached_text', __( 'Sorry but you have hit your fooditem limit for this file.', 'restropress' ) ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );

					// If this fooditem has variable prices, we have to confirm that this file was included in their purchase
					if ( ! empty( $price_options ) && $file_condition != 'all' && rpress_has_variable_prices( $cart_item['id'] ) ) {
						if ( $file_condition == $price_options['price_id'] )
							return $payment->ID;
					}

					// Make sure the link hasn't expired

					if ( base64_encode( base64_decode( $expire, true ) ) === $expire ) {
						$expire = base64_decode( $expire ); // If it is a base64 string, decode it. Old expiration dates were in base64
					}

					if ( current_time( 'timestamp' ) > $expire ) {
						wp_die( apply_filters( 'rpress_fooditem_link_expired_text', __( 'Sorry but your fooditem link has expired.', 'restropress' ) ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
					}
					return $payment->ID; // Payment has been verified and link is still valid
				}

			}

		}

	} else {
		wp_die( __( 'No payments matching your request were found.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}
	// Payment not verified
	return false;
}

/**
 * Get Success Page URL
 *
 * @param string $query_string
 * @since       1.0
 * @deprecated  2.6 Please avoid usage of this function in favor of rpress_get_success_page_uri()
 * @return      string
*/
function rpress_get_success_page_url( $query_string = null ) {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.6', 'rpress_get_success_page_uri()', $backtrace );

	return apply_filters( 'rpress_success_page_url', rpress_get_success_page_uri( $query_string ) );
}

/**
 * Reduces earnings and sales stats when a purchase is refunded
 *
 * @since 1.0
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 * @deprecated  2.5.7 Please avoid usage of this function in favor of refund() in RPRESS_Payment
 * @internal param Arguments $data passed
 */
function rpress_undo_purchase_on_refund( $payment_id, $new_status, $old_status ) {

	$backtrace = debug_backtrace();
	_rpress_deprecated_function( 'rpress_undo_purchase_on_refund', '2.5.7', 'RPRESS_Payment->refund()', $backtrace );

	$payment = new RPRESS_Payment( $payment_id );
	$payment->refund();
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 * @deprecated 2.7
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $earnings Earnings
 */
function rpress_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null, $include_taxes = true ) {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.7', 'RPRESS_Payment_Stats()->get_earnings()', $backtrace );

	global $wpdb;

	$args = array(
		'post_type'      => 'rpress_payment',
		'nopaging'       => true,
		'year'           => $year,
		'monthnum'       => $month_num,
		'post_status'    => array( 'publish', 'revoked' ),
		'fields'         => 'ids',
		'update_post_term_cache' => false,
		'include_taxes'  => $include_taxes,
	);

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) || $hour == 0 ) {
		$args['hour'] = $hour;
	}

	$args   = apply_filters( 'rpress_get_earnings_by_date_args', $args );
	$cached = get_transient( 'rpress_stats_earnings' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			$sales = implode( ',', $sales );

			$total_earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_rpress_payment_total' AND post_id IN ({$sales})" );
			$total_tax      = 0;

			if ( ! $include_taxes ) {
				$total_tax = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_rpress_payment_tax' AND post_id IN ({$sales})" );
			}

			$earnings += ( $total_earnings - $total_tax );
		}
		// Cache the results for one hour
		$cached[ $key ] = $earnings;
		set_transient( 'rpress_stats_earnings', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return round( $result, 2 );
}

/**
 * Get Sales By Date
 *
 * @since 1.1.4.0
 * @deprecated 2.7
 * @author RestroPress
 * @param int $day Day number
 * @param int $month_num Month number
 * @param int $year Year
 * @param int $hour Hour
 * @return int $count Sales
 */
function rpress_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {
	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.7', 'RPRESS_Payment_Stats()->get_sales()', $backtrace );

	$args = array(
		'post_type'      => 'rpress_payment',
		'nopaging'       => true,
		'year'           => $year,
		'fields'         => 'ids',
		'post_status'    => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	$show_free = apply_filters( 'rpress_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key' => '_rpress_payment_total',
				'value' => 0,
				'compare' => '>',
				'type' => 'NUMERIC',
			),
		);
	}

	if ( ! empty( $month_num ) )
		$args['monthnum'] = $month_num;

	if ( ! empty( $day ) )
		$args['day'] = $day;

	if ( ! empty( $hour ) )
		$args['hour'] = $hour;

	$args = apply_filters( 'rpress_get_sales_by_date_args', $args  );

	$cached = get_transient( 'rpress_stats_sales' );
	$key    = md5( json_encode( $args ) );

	if ( ! isset( $cached[ $key ] ) ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;

		// Cache the results for one hour
		$cached[ $key ] = $count;
		set_transient( 'rpress_stats_sales', $cached, HOUR_IN_SECONDS );
	}

	$result = $cached[ $key ];

	return $result;
}

/**
 * Set the Page Style for PayPal Purchase page
 *
 * @since 1.0
 * @deprecated 2.8
 * @return string
 */
function rpress_get_paypal_page_style() {

	$backtrace = debug_backtrace();

	_rpress_deprecated_function( __FUNCTION__, '2.8', 'rpress_get_paypal_image_url', $backtrace );

	$page_style = trim( rpress_get_option( 'paypal_page_style', 'PayPal' ) );
	return apply_filters( 'rpress_paypal_page_style', $page_style );
}
