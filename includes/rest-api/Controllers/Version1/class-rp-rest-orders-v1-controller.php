<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of class-rp-rest-orders-v1-controller
 *
 * @author PC
 */
class RP_REST_Orders_V1_Controller extends RP_REST_Posts_Controller {

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
	protected $rest_base = 'order';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'rpress_payment';

	/**
	 * Initialize foods actions.
	 */
	public function __construct() {
		$obj                 = get_post_type_object( $this->post_type );
		$obj->show_in_rest   = true;
		$obj->rest_namespace = $this->namespace;
		// add_filter( "rest_{$this->post_type}_query", array( $this, 'post_query' ) );
		add_filter( "rest_prepare_{$this->post_type}", array( $this, 'rp_api_prepeare_data' ), 10, 3 );
		// add_filter( "rest_{$this->post_type}_item_schema", array( $this, "{$this->post_type}_item_schema" ) );
		parent::__construct( $this->post_type, $this );
	}
	/**
	 * Register the routes for order's status.
	 */
	public function register_routes() {
		parent::register_routes();
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/statuses',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'rpress_order_status_callback' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
			)
		);
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
	 * Determine the allowed query_vars for a get_items() response and
	 * prepare for WP_Query.
	 *
	 * @param array           $prepared_args Prepared arguments.
	 * @param WP_REST_Request $request Request object.
	 * @return array          $query_args
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$args                    = $prepared_args;
		$args['customer']        = $request['customer'];
		$args['status']          = $request['payment_status'];
		$args['start_date']      = $request['start_date'];
		$args['end_date']        = $request['end_date'];
		$args['gateway']         = $request['gateway'];
		$args['search_in_notes'] = $request['search_in_notes'];
		$args['fooditem']        = $request['fooditem'];
		if ( isset( $request['order_id'] ) ) {
			$args['post__in'] = array( $request['order_id'] );
		}

		if ( empty( $args['status'] ) ) {
			$args['status'] = 'any';
		}
		$payments_query = new RPRESS_Payments_Query( $args );
		$prepared_args  = $payments_query->get_wp_query_args();
		return $prepared_args;
	}


	// public function rpress_check_condtion( array $check_array_data ): stdClass {

	// $prepared_post          = $check_array_data['prepared_post'] ?? new stdClass();
	// $schema                 = $check_array_data['schema'] ?? array();
	// $add_food_items_index   = $check_array_data['add_food_items_index'];
	// $add_food_items_request = $check_array_data['add_food_items_request'];
	// $add_food_items_schema  = $schema['properties']['add_food_items']['items']['properties'];
	// if ( is_array( $add_food_items_request ) ) {
	// foreach ( $add_food_items_request as $key => $request_value ) {
	// if ( ! empty( $add_food_items_schema ) && isset( $add_food_items_schema[ $key ] ) && ! empty( $request_value ) ) {
	// if ( ! is_array( $request_value ) ) {
	// $prepared_post->add_food_items[ $add_food_items_index ][ $key ] = $request_value;
	// } else {
	// foreach ( $request_value as $i => $addon_data ) {
	// $addon_schema = $add_food_items_schema['addon_items']['items']['properties'];
	// foreach ( $addon_data as $addon_key => $addon ) {
	// if ( ! empty( $addon_schema ) && ! empty( $addon_schema[ $addon_key ] ) ) {
	// $prepared_post->add_food_items[ $add_food_items_index ]['addon_items'][ $i ][ $addon_key ] = $addon;
	// }
	// }
	// }
	// }
	// }
	// }
	// }
	// return $prepared_post;
	// }

	/**
	 * Callback of pre-insert
	 * This method responsible for adding extra pre insert validation
	 *
	 * @param stdClass        $prepared_post | Default Prepared validation array
	 * @param  WP_REST_Request $request Description
	 * * */
	// public function rpress_payment_pre_insert( stdClass $prepared_post, WP_REST_Request $request ): stdClass {
	// $schema = $this->get_item_schema();
	// if ( ! empty( $schema['properties']['add_food_items'] ) && isset( $request['add_food_items'] ) && ! empty( $request['add_food_items'] ) && is_array( $request['add_food_items'] ) ) {
	// for ( $index = 0; $index < count( $request['add_food_items'] ); $index++ ) {
	// $schema_item      = $schema['properties']['add_food_items']['items']['properties'];
	// $request_data     = $request['add_food_items'][ $index ];
	// $check_array_data = array(
	// 'add_food_items_index'   => $index,
	// 'schema'                 => $schema,
	// 'add_food_items_request' => $request_data,
	// 'prepared_post'          => $prepared_post,
	// );
	// $prepared_post    = $this->rpress_check_condtion( $check_array_data );
	// $prepared_post = call_user_func( [ $this, 'rpress_check_condtion' ], $check_array_data );
	// print_r($prepared_post);
	// }
	// }
	// return $prepared_post;
	// }

	/**
	 * Callback of prepare schema
	 * Basically this callback method adding additional schema
	 *
	 * @param array $schema | default schema array
	 * @return array $schema |  Modified array
	 * @since  3.0.0
	 * @access public
	 * * */
	public function rpress_payment_item_schema( array $schema ): array {

		$additional_schema = array(
			'add_food_items'        => array(
				'title'       => __( 'Food Items' ),
				'description' => __( 'All Food Items' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit', 'embed' ),
				'items'       => array(
					'title'       => __( 'Food Item' ),
					'description' => __( 'Fodd Item' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),
					'properties'  => array(
						'price'       => array(
							'title'       => __( 'Price', 'restropress' ),
							'description' => __( 'Price of item' ),
							'type'        => 'number',
							'context'     => array( 'edit' ),
						),
						'id'          => array(
							'title'       => __( 'ID', 'restropress' ),
							'description' => __( 'ID of food item', 'restropress' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
						'quantity'    => array(
							'title'       => __( 'Quantity', 'restropress' ),
							'description' => __( 'Quantity of food item', 'restropress' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
						'instruction' => array(
							'title'       => __( 'Instruction', 'restropress' ),
							'description' => __( 'Instruction Of food item', 'restropress' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),
							'readonly'    => true,
						),
						'addon_items' => array(
							'title'       => __( 'Addons', 'restropress' ),
							'description' => __( 'Addon of food item', 'restropress' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit', 'embed' ),
							'items'       => array(
								'title'       => __( 'Items', 'restropress' ),
								'description' => __( 'Addons Items', 'restropress' ),
								'type'        => 'object',
								'context'     => array( 'view', 'edit', 'embed' ),
								'properties'  => array(
									'addon_item_name' => array(
										'title'       => __( 'Addon item name', 'restropress' ),
										'description' => __( 'Addon item name', 'restropress' ),
										'type'        => 'string',
										'context'     => array( 'view', 'edit', 'embed' ),
										'readonly'    => true,
									),
									'addon_id'        => array(
										'title'       => __( 'Addon ID', 'restropress' ),
										'description' => __( 'ID of addons', 'restropress' ),
										'type'        => 'integer',
										'context'     => array( 'view', 'edit', 'embed' ),
										'readonly'    => true,
									),
									'price'           => array(
										'title'       => __( 'Addon Price', 'restropress' ),
										'description' => __( 'Price of addon', 'restropress' ),
										'type'        => 'number',
										'context'     => array( 'view', 'edit', 'embed' ),
										'readonly'    => true,
									),
									'quantity'        => array(
										'title'       => __( 'Addon Quantity', 'restropress' ),
										'description' => __( 'Addon Quantity', 'restropress' ),
										'type'        => 'integer',
										'context'     => array( 'view', 'edit', 'embed' ),
										'readonly'    => true,
									),
								),
							),
						),
					),
				),
			),
			'is_add_fooditems'      => array(
				'title'       => __( 'Is add fooditems' ),
				'description' => __( 'Check Whether food item should add' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'is_remove_fooditems'   => array(
				'title'       => __( 'Is remove fooditems' ),
				'description' => __( 'Check Whether food item should remove' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'is_modify_cart_item'   => array(
				'title'       => __( 'Is remove fooditems' ),
				'description' => __( 'Check Whether food item should remove' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'cart_key'              => array(
				'title'       => __( 'Cart key', 'restropress' ),
				'description' => __( 'Cart key to modification', 'restropress' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'delivery_adrress_meta' => array(
				'title'       => __( 'Delivery Address', 'restropress' ),
				'description' => __( 'Delivery Address meta', 'restropress' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit', 'embed' ),
				'properties'  => array(
					'address'  => array(
						'title'       => __( 'Address', 'restropress' ),
						'description' => __( 'Address of delivery', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'flat'     => array(
						'title'       => __( 'Flat', 'restropress' ),
						'description' => __( 'Flat', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'city'     => array(
						'title'       => __( 'City', 'restropress' ),
						'description' => __( 'City', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'postcode' => array(
						'title'       => __( 'Post code', 'restropress' ),
						'description' => __( 'Post code', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
				),
			),
		);

		$additional_schema = apply_filters( "rp_rest_api_{$this->post_type}_schema", $additional_schema );
		foreach ( $additional_schema as $schema_key => $schema_value ) {
			$schema['properties'][ $schema_key ] = $schema_value;
		}
		return $schema;
	}

	/**
	 * Callback of rest_prepare
	 *
	 * @param WP_REST_Response $response Having all data need to display
	 * @param WP_Post          $post || This case rpress_payment post type data
	 * @param  WP_REST_Request  $request || Request Object
	 * @return WP_REST_Response || Returning some modifing data
	 * @since 3.0.0
	 * * */
	public function rp_api_prepeare_data( WP_REST_Response $response, WP_Post $payment_post, WP_REST_Request $request ): WP_REST_Response {

		$payment = new RPRESS_Payment( $payment_post->ID );

		$response->data['delivery_adrress_meta'] = $payment->get_meta( '_rpress_delivery_address' );
		// $response->data['cart_details']            = $payment->cart_details;
		$response->data['order_note']              = $payment->order_note;
		$response->data['address']                 = $payment->address;
		$response->data['key']                     = $payment->key;
		$response->data['total']                   = $payment->total;
		$response->data['subtotal']                = $payment->subtotal;
		$response->data['tax']                     = $payment->tax;
		$response->data['discounted_amount']       = $payment->discounted_amount;
		$response->data['tax_rate']                = $payment->tax_rate;
		$response->data['fees']                    = $payment->fees;
		$response->data['fees_total']              = $payment->fees_total;
		$response->data['discounts']               = $payment->discounts;
		$response->data['date']                    = $payment->date;
		$response->data['completed_date']          = $payment->completed_date;
		$response->data['status']                  = $payment->status;
		$response->data['status_nicename']         = $payment->status_nicename;
		$response->data['post_status']             = $payment->post_status;
		$response->data['old_status']              = $payment->old_status;
		$response->data['user_id']                 = $payment->user_id;
		$response->data['customer_id']             = $payment->customer_id;
		$response->data['user_info']               = $payment->user_info;
		$response->data['ip']                      = $payment->ip;
		$response->data['gateway']                 = $payment->gateway;
		$response->data['currency']                = $payment->currency;
		$response->data['has_unlimited_fooditems'] = $payment->has_unlimited_fooditems;
		$response->data['pending']                 = $payment->pending;
		$response->data['parent_payment']          = $payment->parent_payment;
		$response->data['service_type']            = $payment->get_meta( '_rpress_delivery_type' );
		$response->data['service_date']            = $payment->get_meta( '_rpress_delivery_date' );
		$response->data['service_time']            = $payment->get_meta( '_rpress_delivery_time' );
		$response->data['payment_meta']            = $payment->payment_meta;

		return $response;
	}

	public function post_query( array $query ): array {
		if ( isset( $query['order_status'] ) && ! empty( $query['order_status'] ) ) {
			$query['post_status'] = $query['order_status'];
		} else {
			$post_status       = rpress_get_order_statuses();
			$order_status_keys = array_unique( array_keys( $post_status ) );
			$order_status      = apply_filters( 'rp_api_order_status', $order_status_keys );
			if ( is_array( $order_status ) ) {
				for ( $i = 0; $i < count( $order_status ); $i++ ) {
					$query['post_status'][] = $order_status[ $i ];
				}
			}
		}

		return $query;
	}

	/**
	 * Overriding default create_item
	 *
	 * @param WP_REST_REquest $request ,
	 * @return WP_REST_Response $response
	 * @since 3.0.1
	 * * */
	// public function create_item( $request ): WP_REST_Response {
	// if ( ! empty( $request['id'] ) ) {
	// return new WP_Error(
	// 'rest_post_exists',
	// __( 'Cannot create existing post.' ),
	// array( 'status' => 400 )
	// );
	// }
	// $cart_details = $request->get_param( 'cart_details' );

	// if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
	// for ( $index = 0; $index < count( $cart_details ); $index++ ) {
	// rpress_add_to_cart( $cart_details[ $index ]['id'], $cart_details[ $index ] );
	// }
	// }

	// $user      = get_user_by( 'id', get_current_user_id() );
	// $user_info = array(
	// 'id'         => $user->ID,
	// 'email'      => $user->user_email,
	// 'first_name' => $user->first_name,
	// 'last_name'  => $user->last_name,
	// 'discount'   => 0,
	// 'address'    => array(),
	// );

	// $payment_data = array(
	// 'price'        => rpress_get_cart_total(),
	// 'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
	// 'user_email'   => $user->user_email,
	// 'purchase_key' => strtolower( md5( uniqid() ) ),
	// 'currency'     => rpress_get_currency(),
	// 'fooditems'    => rpress_get_cart_contents(),
	// 'user_info'    => $user_info,
	// 'cart_details' => rpress_get_cart_content_details(),
	// 'status'       => 'pending',
	// );

	// print_r( $payment_data );
	// $post_id = rpress_insert_payment( $payment_data );
	// if ( $post_id ) {
	// rpress_update_payment_status( $post_id, 'processing' );
	// Empty the shopping cart
	// rpress_empty_cart();
	// }

	// if ( is_wp_error( $post_id ) ) {

	// if ( 'db_insert_error' === $post_id->get_error_code() ) {
	// $post_id->add_data( array( 'status' => 500 ) );
	// } else {
	// $post_id->add_data( array( 'status' => 400 ) );
	// }

	// return $post_id;
	// }

	// $post = get_post( $post_id );

	// **
	// * Fires after a single post is created or updated via the REST API.
	// *
	// * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
	// *
	// * Possible hook names include:
	// *
	// *  - `rest_insert_post`
	// *  - `rest_insert_page`
	// *  - `rest_insert_attachment`
	// *
	// * @since 4.7.0
	// *
	// * @param WP_Post         $post     Inserted or updated post object.
	// * @param WP_REST_Request $request  Request object.
	// * @param bool            $creating True when creating a post, false when updating.
	// */
	// do_action( "rest_insert_{$this->post_type}", $post, $request, true );

	// $schema = $this->get_item_schema();

	// if ( ! empty( $schema['properties']['sticky'] ) ) {
	// if ( ! empty( $request['sticky'] ) ) {
	// stick_post( $post_id );
	// } else {
	// unstick_post( $post_id );
	// }
	// }

	// if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
	// $this->handle_featured_media( $request['featured_media'], $post_id );
	// }

	// if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
	// set_post_format( $post, $request['format'] );
	// }

	// if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
	// $this->handle_template( $request['template'], $post_id, true );
	// }

	// $terms_update = $this->handle_terms( $post_id, $request );

	// if ( is_wp_error( $terms_update ) ) {
	// return $terms_update;
	// }

	// if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
	// $meta_update = $this->meta->update_value( $request['meta'], $post_id );

	// if ( is_wp_error( $meta_update ) ) {
	// return $meta_update;
	// }
	// }

	// $post          = get_post( $post_id );
	// $fields_update = $this->update_additional_fields_for_object( $post, $request );

	// if ( is_wp_error( $fields_update ) ) {
	// return $fields_update;
	// }

	// $request->set_param( 'context', 'edit' );

	// **
	// * Fires after a single post is completely created or updated via the REST API.
	// *
	// * The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
	// *
	// * Possible hook names include:
	// *
	// *  - `rest_after_insert_post`
	// *  - `rest_after_insert_page`
	// *  - `rest_after_insert_attachment`
	// *
	// * @since 5.0.0
	// *
	// * @param WP_Post         $post     Inserted or updated post object.
	// * @param WP_REST_Request $request  Request object.
	// * @param bool            $creating True when creating a post, false when updating.
	// */
	// do_action( "rest_after_insert_{$this->post_type}", $post, $request, true );

	// wp_after_insert_post( $post, false, null );

	// $response = $this->prepare_item_for_response( $post, $request );
	// $response = rest_ensure_response( $response );

	// $response->set_status( 201 );
	// $response->header( 'Location', rest_url( rest_get_route_for_post( $post ) ) );

	// return $response;
	// }

	/**
	 * Overriding update_item
	 * * */
	// public function update_item( $request ) {
	// $valid_check = $this->get_post( $request['id'] );
	// if ( is_wp_error( $valid_check ) ) {
	// return $valid_check;
	// }

	// $post_before = get_post( $request['id'] );
	// $post        = $this->prepare_item_for_database( $request );
	// $this->dump_data( $post );
	// if ( is_wp_error( $post ) ) {
	// return $post;
	// }
	// if ( ! empty( $post->post_status ) ) {
	// $post_status = $post->post_status;
	// } else {
	// $post_status = $post_before->post_status;
	// }

	// Instantiate payment
	// $payment = new RPRESS_Payment( $post->ID );

	// Adding food item
	// if ( isset( $post->add_food_items ) && is_array( $post->add_food_items ) && ! empty( $post->add_food_items ) ) {
	// for ( $j = 0; $j < count( $post->add_food_items ); $j++ ) {
	// $fooditem_id = $post->add_food_items[ $j ]['id'];
	// $args        = $post->add_food_items[ $j ];
	// $options     = array();
	// if ( isset( $post->add_food_items[ $j ]['addon_items'] ) ) {
	// $options = $post->add_food_items[ $j ]['addon_items'];
	// $payment->add_fooditem( $fooditem_id, $args, $options );
	// }
	// }
	// }

	// $payment->save();
	// $classes = get_class_methods( $payment );
	// $this->dump_data( $classes );
	// }

}
