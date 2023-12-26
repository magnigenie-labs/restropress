<?php

/**
 * REST API Foods controller
 *
 * Handles requests to the /foods endpoint.
 *
 * @author   WooThemes
 * @category API
 * @package Restropress\RestApi
 * @since    3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Foods controller class.
 *
 * @package Restropress\RestApi
 * @extends WC_REST_Posts_Controller
 */
class RP_REST_Foods_V1_Controller extends RP_REST_Posts_Controller {

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
	protected $rest_base = 'food';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'fooditem';

	/**
	 * Initialize foods actions.
	 */
	public function __construct() {
		$obj                 = get_post_type_object( $this->post_type );
		$obj->show_in_rest   = true;
		$obj->rest_namespace = $this->namespace;
		add_filter( "rest_prepare_{$this->post_type}", array( $this, 'rp_api_prepeare_data' ), 10, 3 );
		add_filter( "rest_{$this->post_type}_item_schema", array( $this, "{$this->post_type}_item_schema" ) );
		add_filter( "rest_after_insert_{$this->post_type}", array( $this, "{$this->post_type}_create_item" ), 10, 3 );
		parent::__construct( $this->post_type, $this );
	}

	/**
	 * Callback of Insert fooditems
	 * This callback method Inserting Data  to meta data when third param is
	 * true and Updating when third parameter is false
	 *
	 * @param WP_Post         $post |  Post data of food items
	 * @param WP_REST_Request $request | Rest  Request data
	 * @param boolean         $is_crete |  If true then it create if false it update
	 * @since 3.0.0
	 * @access public
	 * @return void
	 * * */
	public function fooditem_create_item( WP_Post $fooitem_post, WP_REST_Request $request, bool $is_create ): void {

		$fooditem_id = $fooitem_post->ID;
		$post        = $this->prepare_item_for_database( $request );
		$post->ID    = $fooditem_id;
		// Adding/updating price
		if ( property_exists( $post, 'price' ) && ! empty( $post->price ) ) {
			$clean_food_price = sanitize_meta( 'rpress_price', $post->price, 'post' );
			update_post_meta( $post->ID, 'rpress_price', $clean_food_price );
		}

		// Adding/updating featured media id
		if ( property_exists( $post, 'featured_media' ) && ! empty( $post->featured_media ) ) {
			set_post_thumbnail( $post->ID, $post->featured_media );
		}
		// Adding/updating Food Type
		if ( property_exists( $post, 'food_type' ) && ! empty( $post->food_type ) ) {
			$clean_food_type = sanitize_meta( 'rpress_food_type', $post->food_type, 'post' );
			update_post_meta( $post->ID, 'rpress_food_type', $clean_food_type );
		}
		// Adding/updating Food SKU
		if ( property_exists( $post, 'sku' ) && ! empty( $post->sku ) ) {
			$clean_food_sku = sanitize_meta( 'rpress_sku', $post->sku, 'post' );
			update_post_meta( $post->ID, 'rpress_sku', $clean_food_sku );
		}
		// Adding/Updating Food Categories
		if ( property_exists( $post, 'food_categories' ) && count( $post->food_categories ) > 0 ) {
			$food_categories = rpress_sanitize_array( $post->food_categories );
			wp_set_post_terms( $post->ID, $food_categories, 'food-category' );
		}
		// Adding/Updating Variable Price
		if ( property_exists( $post, 'variable_prices' ) && is_array( $post->variable_prices ) && count( $post->variable_prices ) > 0 ) {
			$variable_prices       = rpress_sanitize_array( $post->variable_prices );
			$clean_variable_prices = sanitize_meta( 'rpress_variable_prices', $variable_prices, 'post' );
			update_post_meta( $post->ID, 'rpress_variable_prices', $clean_variable_prices );
		}
		// Adding/updating has variable pricess
		if ( property_exists( $post, 'has_variable_prices' ) && $post->has_variable_prices ) {
			$clean_has_variable = sanitize_meta( '_variable_pricing', $post->has_variable_prices, 'post' );
			update_post_meta( $post->ID, '_variable_pricing', $clean_has_variable );
		}
		// Adding/updating variable price label
		if ( property_exists( $post, 'variable_price_label' ) && ! empty( $post->variable_price_label ) ) {
			$clean_label = sanitize_meta( 'rpress_variable_price_label', $post->variable_price_label, 'post' );
			update_post_meta( $post->ID, 'rpress_variable_price_label', $clean_label );
		}
		// Adding/Updating Addon Items
		if ( property_exists( $post, 'food_addons' ) && count( $post->food_addons ) > 0 ) {
			$addons         = $post->food_addons;
			$category_idies = array_keys( $addons );
			$addon_terms    = array();
			for ( $i = 0; $i < count( $category_idies ); $i++ ) {
				$ID            = $category_idies[ $i ];
				$addon_terms[] = $ID;
				if ( is_array( $addons[ $ID ]['items'] ) ) {
					$addon_terms = array_merge( $addon_terms, $addons[ $ID ]['items'] );
				}
			}
			$addon_terms   = array_unique( $addon_terms );
			$product_terms = wp_get_post_terms( $post->ID, 'addon_category', array( 'fields' => 'ids' ) );

			if ( ! is_wp_error( $product_terms ) ) {
				$terms_to_remove = array_diff( $product_terms, $addon_terms );
				wp_remove_object_terms( $post->ID, $terms_to_remove, 'addon_category' );
			}
			wp_set_post_terms( $post->ID, $addon_terms, 'addon_category', true );
			$sanitize_addon   = rpress_sanitize_array( $addons );
			$clean_addon_meta = sanitize_meta( '_addon_items', $sanitize_addon, 'post' );
			update_post_meta( $post->ID, '_addon_items', $clean_addon_meta );
		}
	}

	/**
	 * Callback of pre-insert
	 * This method responsible for adding extra pre insert validation
	 *
	 * @param stdClass        $prepared_post | Default Prepared validation array
	 * @param  WP_REST_Request $request Description
	 * * */
	public function fooditem_pre_insert( stdClass $prepared_post, WP_REST_Request $request ): stdClass {
		$schema = $this->get_item_schema();
		// Checking for food type
		if ( ! empty( $schema['properties']['food_type'] ) && ! empty( $request->get_param( 'food_type' ) ) ) {
			$prepared_post->food_type = $request->get_param( 'food_type' );
		}
		// Checking for price
		if ( ! empty( $schema['properties']['price'] ) && ! empty( $request->get_param( 'price' ) ) ) {
			$prepared_post->price = $request->get_param( 'price' );
		}
		// Checking for price
		if ( ! empty( $schema['properties']['sku'] ) && ! empty( $request->get_param( 'sku' ) ) ) {
			$prepared_post->sku = $request->get_param( 'sku' );
		}
		// Checking for Categories
		if ( ! empty( $schema['properties']['food_categories'] ) && ! empty( $request->get_param( 'food_categories' ) ) ) {
			$prepared_post->food_categories = $request->get_param( 'food_categories' );
		}
		// Checking for notes
		if ( ! empty( $schema['properties']['notes'] ) && ! empty( $request->get_param( 'notes' ) ) ) {
			$prepared_post->notes = $request->get_param( 'notes' );
		}
		// Checking for has veriable price
		$prepared_post->has_variable_prices = false;
		if ( ! empty( $schema['properties']['has_variable_prices'] ) && ! empty( $request->get_param( 'has_variable_prices' ) ) ) {
			$prepared_post->has_variable_prices = true;
		}
		// Checking for Variable price
		if ( ! empty( $schema['properties']['variable_prices'] ) && $prepared_post->has_variable_prices && is_array( $request->get_param( 'variable_prices' ) ) && count( $request->get_param( 'variable_prices' ) ) > 0 ) {
			$prepared_post->variable_prices = array();
			$variable_prices                = $request->get_param( 'variable_prices' );
			foreach ( $variable_prices as $count => $prices ) {
				if ( isset( $prices['name'] ) ) {
					$prepared_post->variable_prices[ $count ]['name'] = $prices['name'];
				}
				if ( isset( $prices['amount'] ) ) {
					$prepared_post->variable_prices[ $count ]['amount'] = $prices['amount'];
				}
			}
		}
		// Checkingfor variable price label
		if ( ! empty( $schema['properties']['variable_price_label'] ) && $prepared_post->has_variable_prices && ! empty( $request->get_param( 'variable_price_label' ) ) ) {
			$prepared_post->variable_price_label = $request->get_param( 'variable_price_label' );
		}
		// Checking for food addons
		if ( ! empty( $schema['properties']['food_addons'] ) && is_array( $request->get_param( 'food_addons' ) ) && count( $request->get_param( 'food_addons' ) ) > 0 ) {
			$prepared_post->food_addons = $request->get_param( 'food_addons' );
		}
		return $prepared_post;
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
	public function fooditem_item_schema( array $schema ): array {
		$additional_schema = array(
			'title'                  => array(
				'title'       => __( 'Food Title', 'restropress' ),
				'description' => __( 'Food Title', 'restropress' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'sku'                    => array(
				'title'       => __( 'SKU', 'restropress' ),
				'description' => __( 'Food SKU', 'restropress' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'food_type'              => array(
				'title'       => __( 'Food Type', 'restropress' ),
				'description' => __( 'Food Type', 'restropress' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'price'                  => array(
				'title'       => __( 'Food Price', 'restropress' ),
				'description' => __( 'Food Price', 'restropress' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit', 'embed' ),
			),
			'variable_prices'        => array(
				'title'       => __( 'Food Veriable Price', 'restropress' ),
				'description' => __( 'Food Veriable Price', 'restropress' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit', 'embed' ),

				'items'       => array(
					'title'       => __( 'Price', 'restropress' ),
					'description' => __( 'Veriable Price', 'restropress' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit', 'embed' ),

					'properties'  => array(
						'name'   => array(
							'title'       => __( 'Name', 'restropress' ),
							'description' => __( 'Veriable Price Name', 'restropress' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),

						),
						'amount' => array(
							'title'       => __( 'Amount', 'restropress' ),
							'description' => __( 'Veriable Amount', 'restropress' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit', 'embed' ),

						),
					),
				),
			),
			'is_single_price_mode'   => array(
				'title'       => __( 'Is Single Price Mode', 'restropress' ),
				'description' => __( 'Is Single Price Mode', 'restropress' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'has_variable_prices'    => array(
				'title'       => __( 'Has variable Prices', 'restropress' ),
				'description' => __( 'Has variable Prices', 'restropress' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'variable_price_label'   => array(
				'title'       => __( 'Variable Price Label', 'restropress' ),
				'description' => __( 'Variable price label', 'restropress' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'food_categories'        => array(
				'title'       => __( 'Food Categories', 'restropress' ),
				'description' => __( 'Food categories', 'restropress' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit', 'embed' ),

				'items'       => array(
					'title'       => __( 'Catgory ID', 'restropress' ),
					'description' => __( 'Food category ID', 'restropress' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),

				),
			),
			'fodditem_type'          => array(
				'title'       => __( 'Food Item Type', 'restropress' ),
				'description' => __( 'Food Item Type', 'restropress' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'sales'                  => array(
				'title'       => __( 'Foods sales', 'restropress' ),
				'description' => __( 'Number of times this has been purchased', 'restropress' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'earnings'               => array(
				'title'       => __( 'Foods Earning', 'restropress' ),
				'description' => __( 'Total fooditem earnings', 'restropress' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit', 'embed' ),
				'readonly'    => true,
			),
			'is_free'                => array(
				'title'       => __( 'Is Free', 'restropress' ),
				'description' => __( 'True when the fooditem is free', 'restropress' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'is_quantities_disabled' => array(
				'title'       => __( 'Is Quntities Disabled', 'restropress' ),
				'description' => __( 'Is quantity input disabled on this food', 'restropress' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'can_purchase'           => array(
				'title'       => __( 'Can Purchase', 'restropress' ),
				'description' => __( 'If the current fooditem ID can be purchased', 'restropress' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit', 'embed' ),

			),
			'food_addons'            => array(
				'title'       => __( 'Food Addons', 'restropress' ),
				'description' => __( 'Selected Food Addons', 'restropress' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit', 'embed' ),

				'properties'  => array(
					'category_id' => array(
						'title'       => __( 'Food Addons', 'restropress' ),
						'description' => __( 'Food Addons', 'restropress' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit', 'embed' ),

						'properties'  => array(
							'category'    => array(
								'title'       => __( 'category ID', 'restropress' ),
								'description' => __( 'category ID', 'restropress' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),

							),
							'max_addons'  => array(
								'title'       => __( 'Max Addons', 'restropress' ),
								'description' => __( 'Max Addons', 'restropress' ),
								'type'        => 'integer',
								'context'     => array( 'view', 'edit', 'embed' ),

							),
							'is_required' => array(
								'title'       => __( 'Is Required', 'restropress' ),
								'description' => __( 'Is Required', 'restropress' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit', 'embed' ),

							),
							'items'       => array(
								'title'       => __( 'Food Addons', 'restropress' ),
								'description' => __( 'Food Addons', 'restropress' ),
								'type'        => 'array',
								'context'     => array( 'view', 'edit', 'embed' ),

								'items'       => array(
									'title'       => __( 'Category ID', 'restropress' ),
									'description' => __( 'Category ID', 'restropress' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit', 'embed' ),

								),
							),
							'prices'      => array(
								'title'       => __( 'Food Addons Prices', 'restropress' ),
								'description' => __( 'Food Addons Prices', 'restropress' ),
								'type'        => array( 'object', 'array' ),
								'context'     => array( 'view', 'edit', 'embed' ),

								'properties'  => array(
									'child_category_id' => array(
										'title'       => __( 'Child Category ID', 'restropress' ),
										'description' => __( 'Child Category ID', 'restropress' ),
										'type'        => 'object',
										'context'     => array( 'view', 'edit', 'embed' ),

										'properties'  => array(
											'addon_name' => array(
												'title'    => __( 'Addon Name', 'restropress' ),
												'description' => __( 'Addon Name', 'restropress' ),
												'type'     => 'string',
												'context'  => array( 'view', 'edit', 'embed' ),
												'readonly' => true,
											),
										),
									),
								),
							),
							'default'     => array(
								'title'       => __( 'Default Values', 'restropress' ),
								'description' => __( 'Default Values', 'restropress' ),
								'type'        => 'array',
								'context'     => array( 'view', 'edit', 'embed' ),

								'items'       => array(
									'title'       => __( 'Default Values', 'restropress' ),
									'description' => __( 'Default Values', 'restropress' ),
									'type'        => 'string',
									'context'     => array( 'view', 'edit', 'embed' ),

								),
							),
						),
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
	 * @param WP_REST_Response $response Having all data need to display .
	 * @param WP_Post          $fooditem_post || This case rpress_payment post type data .
	 * @param  WP_REST_Request  $request || Request Object .
	 * @return WP_REST_Response || Returning some modifing data .
	 * @since 3.0.0
	 * * */
	public function rp_api_prepeare_data( WP_REST_Response $response, WP_Post $fooditem_post, WP_REST_Request $request ): WP_REST_Response {
		$food_item = new RPRESS_Fooditem( $fooditem_post->ID );
		$fields    = $this->get_fields_for_response( $request );
		$data      = $response->get_data();

		// Checking food type and adding food type
		if ( rest_is_field_included( 'food_type', $fields ) ) {
			$data['food_type'] = $food_item->get_food_type();
		}

		// Checking whether single price
		if ( rest_is_field_included( 'is_single_price_mode', $fields ) ) {
			$data['is_single_price_mode'] = $food_item->is_single_price_mode();
		}

		// Checking whether has_variable_price
		if ( rest_is_field_included( 'has_variable_prices', $fields ) ) {
			$data['has_variable_prices'] = $food_item->has_variable_prices();
		}
		// Checking whether free
		if ( rest_is_field_included( 'is_free', $fields ) ) {
			$data['is_free'] = $food_item->is_free();
		}
		// Checking whether free
		if ( rest_is_field_included( 'can_purchase', $fields ) ) {
			$data['can_purchase'] = $food_item->can_purchase();
		}
		// Checking whether quantity disabled
		if ( rest_is_field_included( 'is_quantities_disabled', $fields ) ) {
			$data['is_quantities_disabled'] = $food_item->quantities_disabled();
		}
		// Checking price and adding price
		if ( rest_is_field_included( 'price', $fields ) ) {
			$data['price'] = $food_item->get_price();
		}
		// Checking variable price and adding
		if ( rest_is_field_included( 'variable_prices', $fields ) ) {
			$data['variable_prices'] = $food_item->get_prices();
		}
		// Checking variable price and adding rpress_variable_price_label
		if ( rest_is_field_included( 'variable_price_label', $fields ) ) {
			$data['variable_price_label'] = get_post_meta( $fooditem_post->ID, 'rpress_variable_price_label', true );
		}
		// Checking type and adding
		if ( rest_is_field_included( 'fodditem_type', $fields ) ) {
			$data['fodditem_type'] = $food_item->get_type();
		}
		// Checking sku
		if ( rest_is_field_included( 'sku', $fields ) ) {
			$data['sku'] = $food_item->get_sku();
		}
		// Checking sales and adding .
		if ( rest_is_field_included( 'sales', $fields ) ) {
			$data['sales'] = $food_item->get_sales();
		}
		// Checking sales and adding
		if ( rest_is_field_included( 'earnings', $fields ) ) {
			$data['earnings'] = $food_item->get_earnings();
		}
		// Checking button behavior and adding .
		if ( rest_is_field_included( 'button_behavior', $fields ) ) {
			$data['button_behavior'] = $food_item->get_button_behavior();
		}
		// Checking sku and adding
		if ( rest_is_field_included( 'sku', $fields ) ) {
			$data['sku'] = $food_item->get_sku();
		}
		// Checking notes and adding
		if ( rest_is_field_included( 'notes', $fields ) ) {
			$data['notes'] = $food_item->get_notes();
		}
		// Checking is bundled fooditem and adding
		if ( rest_is_field_included( 'is_bundled_fooditem', $fields ) ) {
			$data['is_bundled_fooditem'] = $food_item->is_bundled_fooditem();
		}
		// Checking bundled food item and adding
		if ( rest_is_field_included( 'bundled_fooditems', $fields ) ) {
			$data['bundled_fooditems'] = $food_item->get_bundled_fooditems();
		}
		// Checking food categories and adding
		if ( rest_is_field_included( 'food_categories', $fields ) ) {
			$data['food_categories'] = $food_item->get_food_categories();
		}
		$response->set_data( $data );

		return $response;
	}

	/**
	 * Over riding prepare_item_for_response
	 *
	 * @param WP_POST         $fooditem .
	 * @param WP_REST_Request $request .
	 * @return WP_REST_Response
	 * @since 3.0.0
	 * * */
	public function prepare_item_for_response( $fooditem, $request ): WP_REST_Response {
		$fields = $this->get_fields_for_response( $request );
		$data   = parent::prepare_item_for_response( $fooditem, $request );

		$data = $data->get_data();

		if ( is_object( $data ) ) {
			$fooditem_id = $data->id;
		} else {
			$fooditem_id = $data['id'];
		}
		unset( $data['is_bundled_fooditem'] );
		unset( $data['template'] );
		unset( $data['fodditem_type'] );
		unset( $data['button_behavior'] );
		unset( $data['bundled_fooditems'] );
		unset( $data['notes'] );
		$images                     = get_the_post_thumbnail_url( $fooditem_id, 'thumbnail' );
		$images_full                = get_the_post_thumbnail_url( $fooditem_id, 'full' );
		$data['featured_media_url'] = $images_full;
		$data['thumbnail']          = $images;
		$addons                     = get_post_meta( $fooditem_id, '_addon_items', true );
		$data['have_addons']        = ! empty( $addons );
		if ( isset( $request['with_addons'] ) && $request['with_addons'] && is_array( $addons ) ) {
			$data['food_addons'] = $addons;
			$category_idies      = array_keys( $addons );
			$addon_terms         = array();
			foreach ( $category_idies as $id ) {
				$addon_terms[] = $id;
				if ( is_array( $addons[ $id ]['items'] ) ) {
					$addon_terms = array_merge( $addon_terms, $addons[ $id ]['items'] );
				}
			}
			$terms = get_terms(
				array(
					'taxonomy' => 'addon_category',
					'include'  => $addon_terms,
				)
			);

			$data['addons'] = $terms;
		}

		$response = new WP_REST_Response( $data );
		return $response;
	}



	/**
	 * Determines the allowed query_vars for a get_items() response and prepares
	 * them for WP_Query.
	 *
	 * @since 4.7.0
	 *
	 * @param array           $prepared_args Optional. Prepared WP_Query arguments. Default empty array.
	 * @param WP_REST_Request $request       Optional. Full details about the request.
	 * @return array Items query arguments.
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {
		$query_args = parent::prepare_items_query( $prepared_args, $request );
		if ( ! empty( $request['food_type'] ) ) {

			$query_args['meta_key'] = 'rpress_food_type';

			$query_args['meta_value'] = $request['food_type'];

		}
		// print_r($query_args);
		return $query_args;
	}

	/**
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 4.7.0
	 * @since 5.4.0 The `tax_relation` query parameter was added.
	 * @since 5.7.0 The `modified_after` and `modified_before` query parameters were added.
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		$query_params['food_type'] = array(
			'description' => __( 'Filter Food Item with Its type.' ),
			'type'        => 'string',
			'enum'        => array(
				'veg',
				'non_veg',
			),
		);

		$query_params['with_addons'] = array(
			'description' => __( 'Item response will have selected addons of food.' ),
			'type'        => 'string',
			'enum'        => array(
				'true',
				'false',
			),
		);
		return $query_params;
	}

}
