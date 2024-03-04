<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of class-rp-rest-others-v1-controller
 *
 * @author PC
 */
class RP_REST_Others_V1_Controller extends WP_REST_Controller {

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
	protected $rest_base = 'others';



	/**
	 * Registering Route
	 * * */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/statuses',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rpress_order_status_callback' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/services',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rpress_services_callback' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
					'args'                => array(),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/tax',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rpress_tax_callback' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}

    /**
     * Permission checking for get request
     * @param WP_REST_Request $request 
     * @since 3.0.0
     * @return bool | WP_Error 
     * * */
    public function get_permissions_check( WP_REST_Request $request ){
        $object = new RP_JWT_Verifier( $request );
        return $object->result;
    }


	/**
	 * RestroPress Order Status list callback .
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function rpress_order_status_callback( WP_REST_Request $request ) {

		$payment_statuses = rpress_get_payment_statuses();
		if ( function_exists( 'rpress_get_payment_status_colors' ) ) {
			$payment_color_codes = rpress_get_payment_status_colors();
		} else {
			$payment_color_codes = array(
				'pending'         => '#fcbdbd',
				'pending_text'    => '#333333',
				'publish'         => '#e0f0d7',
				'publish_text'    => '#3a773a',
				'refunded'        => '#e5e5e5',
				'refunded_text'   => '#777777',
				'failed'          => '#e76450',
				'failed_text'     => '#ffffff',
				'processing'      => '#f7ae18',
				'processing_text' => '#ffffff',
			);
		}

		$statuses = rpress_get_order_statuses();
		if ( function_exists( 'rpress_get_order_status_colors' ) ) {
			$color_codes = rpress_get_order_status_colors();
		} else {
			$color_codes = array(
				'pending'    => '#800000',
				'accepted'   => '#008000',
				'processing' => '#808000',
				'ready'      => '#00FF00',
				'transit'    => '#800080',
				'cancelled'  => '#FF0000',
				'completed'  => '#FFFF00',
			);
		}

		$response_array = array(
			'statuses'         => $statuses,
			'status_colors'    => $color_codes,
			'payment_statuses' => $payment_statuses,
			'payment_colors'   => $payment_color_codes,

		);
		$response = new WP_REST_Response( $response_array );
		$response->set_status( 200 );
		return $response;
	}



	/**
	 * RestroPress services list callback .
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function rpress_services_callback( WP_REST_Request $request ) {

		
		$statuses = rpress_get_service_types();

		$response_array = array(
			'services' => $statuses,
		);

		$response = new WP_REST_Response( $response_array );
		$response->set_status( 200 );
		return $response;
	}


	/**

	 * RestroPress tax callback.

	 * Have multiple arguments to filter the results with.
	 *
	 * @since 1.0.0

	 * @return arr $response object tax
	 */
	public function rpress_tax_callback( WP_REST_Request $request ) {

		$result = new stdClass();
		$result->is_enable = rpress_use_taxes();
		$result->is_prices_include = rpress_prices_include_tax();
		$result->name = rpress_get_tax_name();
		$result->rate = rpress_get_formatted_tax_rate();
		$result->currency = rpress_currency_symbol();

		$response['message'] = __( 'Successful', 'restropress' );

		$response['data'] = $result;

		return new WP_REST_Response( $response, 200 );
	}




}
