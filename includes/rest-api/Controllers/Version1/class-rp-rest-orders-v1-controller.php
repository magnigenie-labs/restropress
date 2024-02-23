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
		$obj               = get_post_type_object( $this->post_type );
		$obj->show_in_rest = true;
		add_filter( "rest_prepare_{$this->post_type}", array( $this, 'rp_api_prepeare_data' ), 10, 3 );
		add_filter( "rest_{$this->post_type}_item_schema", array( $this, "{$this->post_type}_item_schema" ) );
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
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/update-status/(?P<id>[\d]+)/(?P<order_status>[a-z-]+)',
			array(
				'args' => array(
					'id'           => array(
						'description' => __( 'Unique identifier for the order id.' ),
						'type'        => 'integer',
					),
					'order_status' => array(
						'description' => __( 'Order status key.' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_order_status' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => array(),
				),

			)
		);
	}


	/**
	 * Update Order Status By ID
	 *
	 * @param WP_REST_Request $request
	 * @return  WP_REST_Response $response
	 * @since 3.0.0
	 * * */
	public function update_order_status( WP_REST_Request $request ) {

		if ( ! empty( $request['id'] ) && ! empty( $request['order_status'] ) ) {
			update_post_meta( $request['id'], '_order_status', $request['order_status'] );
			send_customer_purchase_notification( $request['id'], $request['order_status'] );
			if ( $request['order_status'] === 'completed' ) {
				$payment_status = 'publish';
				$post           = array(
					'ID'          => $request['id'],
					'post_status' => $payment_status,
				);
				wp_update_post( $post );
				// Update Payment status to "paid" .
				rpress_update_payment_status( $request['id'], 'publish' );
			}
		}
		if ( 0 >= did_action( 'rpress_update_order_status' ) ) {

			do_action( 'rpress_update_order_status', $request['id'], $request['order_status'] );
		}

		$response_array = array(
			'message' => 'Order status successfully updated.',
		);

		$response = new WP_REST_Response( $response_array );
		$response->set_status( 200 );
		return $response;
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

		if ( empty( $args['status'] ) ) {
			$args['status'] = 'any';
		}

		if ( isset( $request['order_status'] ) ) {
			$all_status = $request['order_status'];
			$compare    = 'IN';
			// Order status meta query .
			$status_meta = array(
				'key'     => '_order_status',
				'value'   => $all_status,
				'compare' => $compare,
			);

			$args['meta_query'] = array(
				'relation' => 'AND',
				$status_meta,
			);
		}

		if ( isset( $request['service_type'] ) ) {
			$service_type_all = $request['service_type'];
			$compare          = 'IN';
			// Order status meta query .
			$service_type = array(
				'key'     => '_rpress_delivery_type',
				'value'   => $service_type_all,
				'compare' => $compare,
			);

			$args['meta_query'] = array(
				'relation' => 'AND',
				$service_type,
			);
		}

		$payments_query = new RPRESS_Payments_Query( $args );
		$prepared_args  = $payments_query->get_wp_query_args();
		return $prepared_args;
	}


		/**
		 * Query for Customer (Search, Meta Query etc.)
		 *
		 * @return array
		 * @since 3.0.0
		 * * */
	public function get_collection_params(): array {
		$query_params = parent::get_collection_params();
		unset( $query_params['status'] );

		$query_params['order_status'] = array(
			'description' => __( 'Limits results to order with the given order status.' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
				'enum' => array_keys( rpress_get_order_statuses() ),
			),
		);

		$query_params['service_type'] = array(
			'description' => __( 'Limits results to order with the given service type.' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
				'enum' => array_keys( rpress_get_service_types() ),
			),
		);

		$query_params['payment_status'] = array(
			'description' => __( 'Limits results to order with the given payment status.' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
				'enum' => array_keys( rpress_get_payment_statuses() ),
			),
		);
		$query_params['customer']       = array(
			'description'       => __( 'Search Order by customer id.' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['start_date'] = array(
			'description'       => __( 'Filter Order with Start Date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['end_date'] = array(
			'description'       => __( 'Filter order with End Date.' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['gateway'] = array(
			'description'       => __( 'Filter order with gateway.' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['search_in_notes'] = array(
			'description'       => __( 'Search in notes of Orders.' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$query_params['fooditem'] = array(
			'description' => __( 'Limits results to order with the given food items Id.' ),
			'type'        => 'array',
			'items'       => array(
				'type' => 'string',
			),
		);
		return $query_params;
	}


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
					'state'     => array(
						'title'       => __( 'State', 'restropress' ),
						'description' => __( 'State', 'restropress' ),
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
			'customer'              => array(
				'title'       => __( 'Customer Details', 'restropress' ),
				'description' => __( 'If order is placing by admin then admin can place order for customer.', 'restropress' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit', 'embed' ),
				'properties'  => array(
					'id'         => array(
						'title'       => __( 'ID', 'restropress' ),
						'description' => __( 'ID of Customer this can be -1 if not a customer.', 'restropress' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'email'      => array(
						'title'       => __( 'Email', 'restropress' ),
						'description' => __( 'Email of Customer', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),

					'first_name' => array(
						'title'       => __( 'First Name', 'restropress' ),
						'description' => __( 'First Name of Customer', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),
					'last_name'  => array(
						'title'       => __( 'Last Name', 'restropress' ),
						'description' => __( 'Last Name of Customer', 'restropress' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit', 'embed' ),
						'readonly'    => true,
					),

				),
			),
		);

		$additional_schema['order_status'] = array(
			'description' => __( 'Give order status.' ),
			'type'        => 'string',
			'items'       => array(
				'type' => 'string',
				'enum' => array_keys( rpress_get_order_statuses() ),
			),
		);
		$additional_schema                 = apply_filters( "rp_rest_api_{$this->post_type}_schema", $additional_schema );
		foreach ( $additional_schema as $schema_key => $schema_value ) {
			$schema['properties'][ $schema_key ] = $schema_value;
		}
		$cart_controller                      = new RP_REST_Cart_V1_Controller();
		$cart_schema                          = $cart_controller->get_item_schema();
		$schema['properties']['cart_details'] = $cart_schema['properties']['cart_details'];

		// remove unused properties .
		unset( $schema['properties']['password'] );
		unset( $schema['properties']['template'] );
		unset( $schema['properties']['title'] );
		unset( $schema['properties']['slug'] );
		unset( $schema['properties']['date'] );
		unset( $schema['properties']['date_gmt'] );
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

		$response->data['delivery_adrress_meta']   = $payment->get_meta( '_rpress_delivery_address' );
		$response->data['order_note']              = $payment->order_note;
		$response->data['total']                   = $payment->total;
		$response->data['subtotal']                = $payment->subtotal;
		$response->data['tax']                     = $payment->tax;
		$response->data['discounted_amount']       = $payment->discounted_amount;
		$response->data['tax_rate']                = $payment->tax_rate;
		$response->data['fees_total']              = $payment->fees_total;
		$response->data['discounts']               = $payment->discounts;
		$response->data['date']                    = $payment->date;
		$response->data['completed_date']          = $payment->completed_date;
		$response->data['status_nicename']         = $payment->status_nicename;
		$response->data['post_status']             = $payment->post_status;
		$response->data['user_id']                 = $payment->user_id;
		$response->data['customer_id']             = $payment->customer_id;
		$response->data['ip']                      = $payment->ip;
		$response->data['gateway']                 = $payment->gateway;
		$response->data['has_unlimited_fooditems'] = $payment->has_unlimited_fooditems;
		$response->data['parent_payment']          = $payment->parent_payment;
		$response->data['service_type']            = $payment->get_meta( '_rpress_delivery_type' );
		$response->data['service_type_name']       = rpress_service_label( $payment->get_meta( '_rpress_delivery_type' ) );
		$response->data['order_status']            = rpress_get_order_status( $payment_post->ID );
		$response->data['service_date']            = $payment->get_meta( '_rpress_delivery_date' );
		$response->data['service_time']            = $payment->get_meta( '_rpress_delivery_time' );
		$response->data                            = array_merge( $response->data, $payment->payment_meta );

		return $response;
	}

	/**
	 * Overriding default create_item
	 *
	 * @param WP_REST_REquest $request ,
	 * @return WP_REST_Response $response
	 * @since 3.0.1
	 * * */
	public function create_item( $request ): WP_REST_Response {
		if ( ! empty( $request['id'] ) ) {
			return new WP_Error(
				'rest_post_exists',
				__( 'Cannot create existing post.' ),
				array( 'status' => 400 )
			);
		}

		$json_params = $request->get_json_params();

		$cart_details          = $json_params['cart_details'];
		$delivery_adrress_meta = $json_params['delivery_adrress_meta'];
		$customer              = $json_params['customer'];
		$status                = $json_params['status'];
		$order_status          = $json_params['order_status'];

		if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
			rpress_empty_cart();
			$cart_controller = new RP_REST_Cart_V1_Controller();
			$cart_data       = $cart_controller->prepare_item_for_database( $request );

			if ( is_array( $cart_data ) && ! empty( $cart_data ) ) {
				$posts = array();
				for ( $index = 0; $index < count( $cart_data ); $index++ ) {
					if ( property_exists( $cart_data[ $index ], 'id' ) ) {
						$is_added = rpress_add_to_cart( $cart_data[ $index ]->id, (array) $cart_data[ $index ] );
					}
				}
			}
		}

		$cart_contain = rpress_get_cart_contents();
		if ( empty( $cart_contain ) ) {
			$response = rest_ensure_response( array() );

			$response->set_status( 400 );
			$response->set_data( array( 'message' => __( 'Cart is empty please add some item than you can place an order.', 'restropress' ) ) );

			return $response;

		}
		if ( current_user_can( 'manage_options' ) ) {
			// if user is admin ...

			if ( ! empty( $customer ) && is_array( $customer ) ) {
					$user_info = array(
						'id'         => isset( $customer['id'] ) ? $customer['id'] : '',
						'email'      => isset( $customer['email'] ) ? $customer['email'] : '',
						'first_name' => isset( $customer['first_name'] ) ? $customer['first_name'] : '',
						'last_name'  => isset( $customer['last_name'] ) ? $customer['last_name'] : '',
						'discount'   => isset( $customer['discount'] ) ? $customer['discount'] : 0,
						'address'    => isset( $customer['address'] ) && is_array( $customer['address'] ) ? $customer['address'] : array(),
					);

			}
		} else {
			$user      = get_user_by( 'id', get_current_user_id() );
			$user_info = array(
				'id'         => $user->ID,
				'email'      => $user->user_email,
				'first_name' => $user->first_name,
				'last_name'  => $user->last_name,
				'discount'   => 0,
				'address'    => array(),
			);
		}

		$payment_data = array(
			'price'        => rpress_get_cart_total(),
			'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'user_email'   => $user->user_email,
			'purchase_key' => strtolower( md5( uniqid() ) ),
			'currency'     => rpress_get_currency(),
			'fooditems'    => $cart_contain,
			'user_info'    => $user_info,
			'cart_details' => rpress_get_cart_content_details(),
			'status'       => ! empty( $order_status ) ? $order_status : 'pending',
		);

		$post_id = rpress_insert_payment( $payment_data );
		if ( ! empty( $status ) ) {
			// Create an array of post data to update .
			$post_data = array(
				'ID'          => $post_id,
				'post_status' => $status,
			);

			// Update the post with wp_update_post .
			wp_update_post( $post_data );
		}
		if ( $post_id ) {
			rpress_update_payment_status( $post_id, 'processing' );
			// empty the shopping cart .
			rpress_empty_cart();
			// add delivery address meta .

			if ( ! empty( $delivery_adrress_meta ) && is_array( $delivery_adrress_meta ) ) {

				// Assuming $delivery_adrress_meta is an associative array with keys like 'address', 'flat', 'postcode', 'city'.
				$delivery_adrress = array(
					'address'  => isset( $delivery_adrress_meta['address'] ) ? $delivery_adrress_meta['address'] : '',
					'flat'     => isset( $delivery_adrress_meta['flat'] ) ? $delivery_adrress_meta['flat'] : '',
					'postcode' => isset( $delivery_adrress_meta['postcode'] ) ? $delivery_adrress_meta['postcode'] : '',
					'city'     => isset( $delivery_adrress_meta['city'] ) ? $delivery_adrress_meta['city'] : '',
					'state'     => isset( $delivery_adrress_meta['state'] ) ? $delivery_adrress_meta['state'] : '',
				);

				update_post_meta( $post_id, '_rpress_delivery_address', $delivery_adrress );
			}
		}

		if ( is_wp_error( $post_id ) ) {

			if ( 'db_insert_error' === $post_id->get_error_code() ) {
				$post_id->add_data( array( 'status' => 500 ) );
			} else {
				$post_id->add_data( array( 'status' => 400 ) );
			}

			return $post_id;
		}

		$post = get_post( $post_id );

		/**
		* Fires after a single post is created or updated via the REST API .
		*
		* The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug .
		*
		* Possible hook names include :
		*
		* - `rest_insert_post`
		* - `rest_insert_page`
		* - `rest_insert_attachment`
		*
		* @since 4.7.0
		*
		* @param WP_Post         $post     Inserted or updated post object .
		* @param WP_REST_Request $request  Request object .
		* @param bool            $creating true when creating a post, false when updating .
		*/
		do_action( "rest_insert_{$this->post_type}", $post, $request, true );

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['sticky'] ) ) {
			if ( ! empty( $request['sticky'] ) ) {
				stick_post( $post_id );
			} else {
				unstick_post( $post_id );
			}
		}

		if ( ! empty( $schema['properties']['featured_media'] ) && isset( $request['featured_media'] ) ) {
			$this->handle_featured_media( $request['featured_media'], $post_id );
		}

		if ( ! empty( $schema['properties']['format'] ) && ! empty( $request['format'] ) ) {
			set_post_format( $post, $request['format'] );
		}

		if ( ! empty( $schema['properties']['template'] ) && isset( $request['template'] ) ) {
			$this->handle_template( $request['template'], $post_id, true );
		}

		$terms_update = $this->handle_terms( $post_id, $request );

		if ( is_wp_error( $terms_update ) ) {
			return $terms_update;
		}

		if ( ! empty( $schema['properties']['meta'] ) && isset( $request['meta'] ) ) {
			$meta_update = $this->meta->update_value( $request['meta'], $post_id );

			if ( is_wp_error( $meta_update ) ) {
				return $meta_update;
			}
		}

		$post          = get_post( $post_id );
		$fields_update = $this->update_additional_fields_for_object( $post, $request );

		if ( is_wp_error( $fields_update ) ) {
			return $fields_update;
		}

		$request->set_param( 'context', 'edit' );

		/**
		* Fires after a single post is completely created or updated via the REST API .
		*
		* The dynamic portion of the hook name, `$this->post_type`, refers to the post type slug .
		*
		* Possible hook names include :
		*
		* - `rest_after_insert_post`
		* - `rest_after_insert_page`
		* - `rest_after_insert_attachment`
		*
		* @since 5.0.0
		*
		* @param WP_Post         $post     Inserted or updated post object .
		* @param WP_REST_Request $request  Request object .
		* @param bool            $creating true when creating a post, false when updating .
		* */
		do_action( "rest_after_insert_{$this->post_type}", $post, $request, true );

		wp_after_insert_post( $post, false, null );

		$response = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 201 );
		$response->header( 'Location', rest_url( rest_get_route_for_post( $post ) ) );

		return $response;
	}

	/**
	 * Overriding update_item
	 * * */
	public function update_item( $request ) {
		$valid_check = $this->get_post( $request['id'] );
		if ( is_wp_error( $valid_check ) ) {
			return $valid_check;
		}

		$json_params = $request->get_json_params();

		$post_id               = $request['id'];
		$cart_details          = $json_params['cart_details'];
		$delivery_adrress_meta = $json_params['delivery_adrress_meta'];
		$customer              = $json_params['customer'];
		$status                = $json_params['status'];
		$order_status          = $json_params['order_status'];
		// // Instantiate payment .
		$payment = new RPRESS_Payment( $post_id );

        if ( ! empty( $order_status ) ) {
            update_post_meta( $request['id'], '_order_status', $order_status );
            send_customer_purchase_notification( $post_id, $order_status );
            if ( 0 >= did_action( 'rpress_update_order_status' ) ) {

                do_action( 'rpress_update_order_status', $request['id'], $order_status );
            }
        }

		if ( ! empty( $delivery_adrress_meta ) && is_array( $delivery_adrress_meta ) ) {

			// Assuming $delivery_adrress_meta is an associative array with keys like 'address', 'flat', 'postcode', 'city'.
			$delivery_adrress = array(
				'address'  => isset( $delivery_adrress_meta['address'] ) ? $delivery_adrress_meta['address'] : '',
				'flat'     => isset( $delivery_adrress_meta['flat'] ) ? $delivery_adrress_meta['flat'] : '',
				'postcode' => isset( $delivery_adrress_meta['postcode'] ) ? $delivery_adrress_meta['postcode'] : '',
				'city'     => isset( $delivery_adrress_meta['city'] ) ? $delivery_adrress_meta['city'] : '',
			);

			$payment->update_meta( '_rpress_delivery_address', $delivery_adrress );

		}
		if ( ! empty( $customer ) && is_array( $customer ) ) {
				$user_info = array(
					'id'         => isset( $customer['id'] ) ? $customer['id'] : '',
					'email'      => isset( $customer['email'] ) ? $customer['email'] : '',
					'first_name' => isset( $customer['first_name'] ) ? $customer['first_name'] : '',
					'last_name'  => isset( $customer['last_name'] ) ? $customer['last_name'] : '',
					'discount'   => isset( $customer['discount'] ) ? $customer['discount'] : 0,
					'address'    => isset( $customer['address'] ) && is_array( $customer['address'] ) ? $customer['address'] : array(),
				);

				$payment_meta              = $payment->payment_meta;
				$payment_meta['user_info'] = $user_info;
				$payment_meta['email']     = $user_info['email'];
				
				$payment->update_meta( '_rpress_payment_meta', $payment_meta );

		}
		if ( ! empty( $status ) ) {
			// Create an array of post data to update .
			$post_data = array(
				'ID'          => $post_id,
				'post_status' => $status,
			);

			// Update the post with wp_update_post .
			wp_update_post( $post_data );
            rpress_update_payment_status( $request['id'], $status );

		}

		if ( is_array( $cart_details ) && ! empty( $cart_details ) ) {
			$cart_controller = new RP_REST_Cart_V1_Controller();
			$cart_data       = $cart_controller->prepare_item_for_database( $request );

			if ( is_array( $cart_data ) && ! empty( $cart_data ) ) {
				foreach ( $payment->cart_details as $cart_index => $fooditem_item ) {

					$quantity   = $fooditem_item['quantity'];
					$item_price = $fooditem_item['item_price'];
					$item_args  = array(
						'quantity'   => $quantity,
						'item_price' => $item_price,
						'price_id'   => false,
						'cart_index' => false,
					);

							$payment->remove_fooditem( $fooditem_item['id'], $item_args );
							$payment->save();
				}
				for ( $index = 0; $index < count( $cart_data ); $index++ ) {
					if ( property_exists( $cart_data[ $index ], 'id' ) ) {
						$fooditem_id = $cart_data[ $index ]->id;
						$quantity    = $cart_data[ $index ]->quantity;
						$item_price  = $cart_data[ $index ]->price;
						$price_id    = $cart_data[ $index ]->price_id;
						$addon_items = $cart_data[ $index ]->addon_items;
						$instruction = $cart_data[ $index ]->instruction;

						$item_args = array(
							'quantity'    => $quantity,
							'price_id'    => $price_id,
							'item_price'  => $item_price,
							'discount'    => 0,
							'instruction' => $instruction,
						);
						$payment->add_fooditem( $fooditem_id, $item_args, $addon_items );

					}
				}
				$payment->save();

			}
		}

		$response = $this->prepare_item_for_response( $valid_check, $request );
		$response = rest_ensure_response( $response );

		$response->set_status( 200 );
		$response->header( 'Location', rest_url( rest_get_route_for_post( $valid_check ) ) );

		return $response;
	}

}
