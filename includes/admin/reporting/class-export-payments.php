<?php
/**
 * Payments Export Class
 *
 * This class handles payment export
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Payments_Export Class
 *
 * @since  1.0.0
 */
class RPRESS_Payments_Export extends RPRESS_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'payments';

	/**
	 * Set the export headers
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function headers() {
		ignore_user_abort( true );

		if ( ! rpress_is_func_disabled( 'set_time_limit' ) )
			set_time_limit( 0 );

		$month = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' );
		$year  = isset( $_POST['year']  ) ? absint( $_POST['year']  ) : date( 'Y' );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'rpress_payments_export_filename', 'rpress-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'       => __( 'ID',   'restropress' ), // unaltered payment ID (use for querying)
			'seq_id'   => __( 'Payment Number',   'restropress' ), // sequential payment ID
			'email'    => __( 'Email', 'restropress' ),
			'first'    => __( 'First Name', 'restropress' ),
			'last'     => __( 'Last Name', 'restropress' ),
			'address1' => __( 'Address', 'restropress' ),
			'address2' => __( 'Address (Line 2)', 'restropress' ),
			'city'     => __( 'City', 'restropress' ),
			'state'    => __( 'State', 'restropress' ),
			'country'  => __( 'Country', 'restropress' ),
			'zip'      => __( 'Zip / Postal Code', 'restropress' ),
			'products' => __( 'Products', 'restropress' ),
			'skus'     => __( 'SKUs', 'restropress' ),
			'amount'   => __( 'Amount', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')',
			'tax'      => __( 'Tax', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')',
			'discount' => __( 'Discount Code', 'restropress' ),
			'gateway'  => __( 'Payment Method', 'restropress' ),
			'trans_id' => __( 'Transaction ID', 'restropress' ),
			'key'      => __( 'Purchase Key', 'restropress' ),
			'date'     => __( 'Date', 'restropress' ),
			'user'     => __( 'User', 'restropress' ),
			'status'   => __( 'Status', 'restropress' )
		);

		if( ! rpress_use_skus() ){
			unset( $cols['skus'] );
		}
		if ( ! rpress_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		$payments = rpress_get_payments( array(
			'offset' => 0,
			'number' => -1,
			'mode'   => rpress_is_test_mode() ? 'test' : 'live',
			'status' => isset( $_POST['rpress_export_payment_status'] ) ? $_POST['rpress_export_payment_status'] : 'any',
			'month'  => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'   => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		) );

		foreach ( $payments as $payment ) {
			$payment_meta   = rpress_get_payment_meta( $payment->ID );
			$user_info      = rpress_get_payment_meta_user_info( $payment->ID );
			$fooditems      = rpress_get_payment_meta_cart_details( $payment->ID );
			$total          = rpress_get_payment_amount( $payment->ID );
			$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
			$products       = '';
			$skus           = '';

			if ( $fooditems ) {
				foreach ( $fooditems as $key => $fooditem ) {
					// Food Item ID
					$id = isset( $payment_meta['cart_details'] ) ? $fooditem['id'] : $fooditem;

					// If the fooditem has variable prices, override the default price
					$price_override = isset( $payment_meta['cart_details'] ) ? $fooditem['price'] : null;

					$price = rpress_get_fooditem_final_price( $id, $user_info, $price_override );

					// Display the Downoad Name
					$products .= get_the_title( $id ) . ' - ';

					if ( rpress_use_skus() ) {
						$sku = rpress_get_fooditem_sku( $id );

						if ( ! empty( $sku ) )
							$skus .= $sku;
					}

					if ( isset( $fooditems[ $key ]['item_number'] ) && isset( $fooditems[ $key ]['item_number']['options'] ) ) {
						$price_options = $fooditems[ $key ]['item_number']['options'];

						if ( isset( $price_options['price_id'] ) ) {
							$products .= rpress_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) . ' - ';
						}
					}
					$products .= html_entity_decode( rpress_currency_filter( $price ) );

					if ( $key != ( count( $fooditems ) -1 ) ) {
						$products .= ' / ';

						if( rpress_use_skus() )
							$skus .= ' / ';
					}
				}
			}

			if ( is_numeric( $user_id ) ) {
				$user = get_userdata( $user_id );
			} else {
				$user = false;
			}

			$data[] = array(
				'id'       => $payment->ID,
				'seq_id'   => rpress_get_payment_number( $payment->ID ),
				'email'    => $payment_meta['email'],
				'first'    => $user_info['first_name'],
				'last'     => $user_info['last_name'],
				'address1' => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
				'address2' => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
				'city'     => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
				'state'    => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
				'country'  => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
				'zip'      => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
				'products' => $products,
				'skus'     => $skus,
				'amount'   => html_entity_decode( rpress_format_amount( $total ) ),
				'tax'      => html_entity_decode( rpress_format_amount( rpress_get_payment_tax( $payment->ID, $payment_meta ) ) ),
				'discount' => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'restropress' ),
				'gateway'  => rpress_get_gateway_admin_label( rpress_get_payment_meta( $payment->ID, '_rpress_payment_gateway', true ) ),
				'trans_id' => rpress_get_payment_transaction_id( $payment->ID ),
				'key'      => $payment_meta['key'],
				'date'     => $payment->post_date,
				'user'     => $user ? $user->display_name : __( 'guest', 'restropress' ),
				'status'   => rpress_get_payment_status( $payment, true )
			);

		}

		$data = apply_filters( 'rpress_export_get_data', $data );
		$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
