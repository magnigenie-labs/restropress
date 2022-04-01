<?php
/**
 * Batch API Request Logs Export Class
 *
 * This class handles API request logs export
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, MagniGenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Batch_API_Requests_Export Class
 *
 * @since 1.0
 */
class RPRESS_Batch_API_Requests_Export extends RPRESS_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'api_requests';

	/**
	 * Set the CSV columns
	 *
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'      => __( 'Log ID',   'restropress' ),
			'request' => __( 'API Request', 'restropress' ),
			'ip'      => __( 'IP Address', 'restropress' ),
			'user'    => __( 'API User', 'restropress' ),
			'key'     => __( 'API Key', 'restropress' ),
			'version' => __( 'API Version', 'restropress' ),
			'speed'   => __( 'Request Speed', 'restropress' ),
			'date'    => __( 'Date', 'restropress' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.0
 	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $rpress_logs;

		$data = array();

		$args = array(
			'log_type'       => 'api_request',
			'posts_per_page' => 30,
			'paged'          => $this->step
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		$logs = $rpress_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$data[] = array(
					'ID'      => $log->ID,
					'request' => get_post_field( 'post_excerpt', $log->ID ),
					'ip'      => get_post_meta( $log->ID, '_rpress_log_request_ip', true ),
					'user'    => get_post_meta( $log->ID, '_rpress_log_user', true ),
					'key'     => get_post_meta( $log->ID, '_rpress_log_key', true ),
					'version' => get_post_meta( $log->ID, '_rpress_log_version', true ),
					'speed'   => get_post_meta( $log->ID, '_rpress_log_time', true ),
					'date'    => $log->post_date
				);
			}

			$data = apply_filters( 'rpress_export_get_data', $data );
			$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

			return $data;
		}

		return false;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.0
	 * @return int
	 */
	public function get_percentage_complete() {
		global $rpress_logs;

		$args = array(
			'post_type'		   => 'rpress_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'rpress_log_type',
					'field'		=> 'slug',
					'terms'		=> 'api_request'
				)
			),
			'date_query'        => array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			)
		);

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end   = isset( $request['end'] )   ? sanitize_text_field( $request['end'] )   : '';
	}
}