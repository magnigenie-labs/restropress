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
 * RPRESS_Fooditem_History_Export Class
 *
 * @since  1.0.0
 */
class RPRESS_Fooditem_History_Export extends RPRESS_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = 'fooditem_history';


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
		header( 'Content-Disposition: attachment; filename=' . apply_filters( 'rpress_fooditem_history_export_filename', 'rpress-export-' . $this->export_type . '-' . $month . '-' . $year ) . '.csv' );
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
			'date'     => __( 'Date',   'restropress' ),
			'user'     => __( 'Ordered by', 'restropress' ),
			'ip'       => __( 'IP Address', 'restropress' ),
			'fooditem' => __( 'Product', 'restropress' ),
			'file'     => __( 'File', 'restropress' )
		);
		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
 	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $rpress_logs;

		$data = array();

		$args = array(
			'nopaging' => true,
			'log_type' => 'file_fooditem',
			'monthnum' => isset( $_POST['month'] ) ? absint( $_POST['month'] ) : date( 'n' ),
			'year'     => isset( $_POST['year'] ) ? absint( $_POST['year'] ) : date( 'Y' )
		);

		$logs = $rpress_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$user_info = get_post_meta( $log->ID, '_rpress_log_user_info', true );
				$user      = get_userdata( $user_info['id'] );
				$user      = $user ? $user->user_login : $user_info['email'];

				$data[]    = array(
					'date'     => $log->post_date,
					'user'     => $user,
					'ip'       => get_post_meta( $log->ID, '_rpress_log_ip', true ),
					'fooditem' => get_the_title( $log->post_parent )
				);
			}
		}

		$data = apply_filters( 'rpress_export_get_data', $data );
		$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

		return $data;
	}
}
