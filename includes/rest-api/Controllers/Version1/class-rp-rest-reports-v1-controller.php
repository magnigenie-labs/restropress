<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;

/**
 * Description of class-rp-rest-reports-v1-controller
 *
 * @author magnigeeks <info@magnigeeks.com>
 */
class RP_REST_Reports_v1_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'rp/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'reports';

	public function __construct() {
	}

	/**
	 * Registering Rest API.
	 * * */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/count',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_order_count' ),
					'permission_callback' => array( $this, 'get_report_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
	}



	/**
	 * RestroPress API Callback to Count Orders
	 *
	 * @param WP_REST_Request $request
	 * @return  WP_REST_Response $response
	 * @since 3.0.0
	 * * */
	public function get_order_count( WP_REST_Request $request ) {

		global $wpdb;
		$select = 'SELECT g.meta_value,count( * ) AS num_posts';
		$join   = "LEFT JOIN $wpdb->postmeta g ON (p.ID = g.post_id)";
		$where  = "WHERE p.post_type = 'rpress_payment' AND g.meta_key = '_order_status'";
		$arg    = array();

		if ( isset( $request['start_date'] ) && ! empty( $request['start_date'] ) ) {
			$post_count_start_date = sanitize_text_field( $request['start_date'] );
			$post_count_end_date   = isset( $request['end_date'] ) && ! empty( $request['end_date'] ) ? sanitize_text_field( $request['end_date'] ) : $post_count_start_date;
			$arg['start-date']     = date( 'm/d/Y', strtotime( $post_count_start_date ) );
			$post_count_end_date   = date( 'Y-m-d', strtotime( "$post_count_end_date +1 day" ) );
			$where                .= " AND ( p.post_date BETWEEN CAST( '$post_count_start_date' AS DATE ) AND CAST( '$post_count_end_date' AS DATE ) )";
			$arg['end-date']       = date( 'm/d/Y', strtotime( "$post_count_end_date +1 day" ) );
		}

		$cache_key = '';
			$query = "$select
			FROM $wpdb->posts p
			$join
			$where
			GROUP BY g.meta_value
			";

		$cache_key = md5( $query );
		$count     = wp_cache_get( $cache_key, 'counts' );

		if ( false !== $count ) {
			return $count;
		}

		$count    = $wpdb->get_results( $query, ARRAY_A );
		$stats    = array();
		$statuses = get_post_stati();

		if ( isset( $statuses['private'] ) && empty( $args['s'] ) ) {
			unset( $statuses['private'] );
		}

		foreach ( $statuses as $state ) {
			$stats[ $state ] = 0;
		}

		foreach ( (array) $count as $row ) {
			if ( array_key_exists( 'post_status', $row ) && 'private' == $row['post_status'] && empty( $args['s'] ) ) {
				continue;
			}
			$stats[ $row['meta_value'] ] = $row['num_posts'];
		}

		$stats = (object) $stats;
		wp_cache_set( $cache_key, $stats, 'counts' );
		$purchases      = rpress_count_payments( $arg );
		$response_array = array(
			'payments_count' => $purchases,
			'orders_count'   => $stats,
		);
		$response       = new WP_REST_Response( $response_array );
		$response->set_status( 200 );
		return $response;
	}




	/**
	 * Query for Reports
	 *
	 * @return array
	 * @since 3.0.0
	 * * */
	public function get_collection_params(): array {
		$query_params               = parent::get_collection_params();
		$query_params['start_date'] = array(
			'description'       => __( 'Start Date of the report.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$query_params['end_date']   = array(
			'description'       => __( 'End Date of the report.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $query_params;
	}


	/**
	 * Permission checking for get request
	 *
	 * @param WP_REST_Request $request
	 * @since 3.0.0
	 * @return bool | WP_Error
	 * * */
	public function get_report_permissions_check( WP_REST_Request $request ) {
		$object = new RP_JWT_Verifier( $request );
		return $object->result;
	}

}