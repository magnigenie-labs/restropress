<?php
/**
 * Customers Export Class
 *
 * This class handles customer export
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
 * RPRESS_Customers_Export Class
 *
 * @since  1.0.0
 */
class RPRESS_Customers_Export extends RPRESS_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'customers';

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

		$extra = '';

		if ( ! empty( $_POST['rpress_export_fooditem'] ) ) {
			$extra = sanitize_title( get_the_title( absint( $_POST['rpress_export_fooditem'] ) ) ) . '-';
		}

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'rpress_customers_export_filename', 'rpress-export-' . $extra . $this->export_type . '-' . date( 'm-d-Y' ) ) . '.csv' );
		header( "Expires: 0" );
	}

	/**
	 * Set the CSV columns
	 *
	 * @since  1.0.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		if ( ! empty( $_POST['rpress_export_fooditem'] ) ) {
			$cols = array(
				'first_name' => __( 'First Name',   'restropress' ),
				'last_name'  => __( 'Last Name',   'restropress' ),
				'email'      => __( 'Email', 'restropress' ),
				'date'       => __( 'Date Purchased', 'restropress' )
			);
		} else {

			$cols = array();

			if( 'emails' != $_POST['rpress_export_option'] ) {
				$cols['name'] = __( 'Name',   'restropress' );
			}

			$cols['email'] = __( 'Email',   'restropress' );

			if( 'full' == $_POST['rpress_export_option'] ) {
				$cols['purchases'] = __( 'Total Purchases',   'restropress' );
				$cols['amount']    = __( 'Total Purchased', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')';
			}

		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$data = array();

		if ( ! empty( $_POST['rpress_export_fooditem'] ) ) {

			// Export customers of a specific product
			global $rpress_logs;

			$args = array(
				'post_parent' => absint( $_POST['rpress_export_fooditem'] ),
				'log_type'    => 'sale',
				'nopaging'    => true
			);

			if( isset( $_POST['rpress_price_option'] ) ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_rpress_log_price_id',
						'value' => (int) $_POST['rpress_price_option']
					)
				);
			}

			$logs = $rpress_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {
					$payment_id = get_post_meta( $log->ID, '_rpress_log_payment_id', true );
					$user_info  = rpress_get_payment_meta_user_info( $payment_id );
					$data[] = array(
						'first_name' => $user_info['first_name'],
						'last_name'  => $user_info['last_name'],
						'email'      => $user_info['email'],
						'date'       => $log->post_date
					);
				}
			}

		} else {

			// Export all customers
			$customers = RPRESS()->customers->get_customers( array( 'number' => -1 ) );

			$i = 0;

			foreach ( $customers as $customer ) {

				if( 'emails' != $_POST['rpress_export_option'] ) {
					$data[$i]['name'] = $customer->name;
				}

				$data[$i]['email'] = $customer->email;

				if( 'full' == $_POST['rpress_export_option'] ) {

					$data[$i]['purchases'] = $customer->purchase_count;
					$data[$i]['amount']    = rpress_format_amount( $customer->purchase_value );

				}
				$i++;
			}
		}

		$data = apply_filters( 'rpress_export_get_data', $data );
		$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
