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
if ( !defined( 'ABSPATH' ) ) {
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
        $obj = get_post_type_object( $this->post_type );
        $obj->show_in_rest = true;
        $obj->rest_namespace = $this->namespace;
        $obj->rest_base = $this->rest_base;
        add_filter( "rest_prepare_{$this->post_type}", [ $this, 'rp_api_prepeare_data' ], 10, 3 );
        add_filter( "rest_{$this->post_type}_item_schema", [ $this, "{$this->post_type}_item_schema" ] );
        add_filter( "rest_after_insert_{$this->post_type}", [ $this, "{$this->post_type}_create_item" ], 10, 3 );
        parent::__construct( $this->post_type, $this );
    }

    /**
     * Callback of Insert fooditems
     * This callback method Inserting Data  to meta data when third param is 
     * true and Updating when third parameter is false
     * 
     * @param WP_Post $post |  Post data of food items
     * @param WP_REST_Request $request | Rest  Request data
     * @param boolean $is_crete |  If true then it create if false it update
     * @since 3.0.0
     * @access public
     * @return void 
     * * */
    public function fooditem_create_item( WP_Post $fooitem_post, WP_REST_Request $request, bool $is_create ): void {
        //print_r($fooitem_post);
        //$request->get_params();
        $post = $this->prepare_item_for_database( $request );
        //Adding/updating price
        if ( property_exists( $post, 'price' ) && !empty( $post->price ) ) {
            $clean_food_price = sanitize_meta( "rpress_price", $post->price, "post" );
            update_post_meta( $post->ID, "rpress_price", $clean_food_price );
        }
        //Adding/updating Food Type 
        if ( property_exists( $post, "food_type" ) && !empty( $post->food_type ) ) {
            $clean_food_type = sanitize_meta( "rpress_food_type", $post->food_type, "post" );
            update_post_meta( $post->ID, "rpress_food_type", $clean_food_type );
        }
        //Adding/Updating Food Categories 
        if ( property_exists( $post, "food_categories" ) && count( $post->food_categories ) > 0 ) {
            $food_categories = rpress_sanitize_array( $post->food_categories );
            wp_set_post_terms( $post->ID, $food_categories, 'food-category' );
        }
        //Adding/Updating Variable Price
        if ( property_exists( $post, 'variable_prices' ) && is_array( $post->variable_prices ) && count( $post->variable_prices ) > 0 ) {
            $variable_prices = rpress_sanitize_array( $post->variable_prices );
            $clean_variable_prices = sanitize_meta( "rpress_variable_prices", $variable_prices, "post" );
            update_post_meta( $post->ID, "rpress_variable_prices", $clean_variable_prices );
        }
        //Adding/updating has variable pricess
        if ( property_exists( $post, 'has_variable_prices' ) && $post->has_variable_prices ) {
            $clean_has_variable = sanitize_meta( "_variable_pricing", $post->has_variable_prices, "post" );
            update_post_meta( $post->ID, "_variable_pricing", $clean_has_variable );
        }
        //Adding/updating variable price label
        if ( property_exists( $post, 'variable_price_label' ) && !empty( $post->variable_price_label ) ) {
            $clean_label = sanitize_meta( "rpress_variable_price_label", $post->variable_price_label, "post" );
            update_post_meta( $post->ID, "rpress_variable_price_label", $clean_label );
        }
        //Adding/Updating Addon Items
        if ( property_exists( $post, "food_addons" ) && count( $post->food_addons ) > 0 ) {
            $addons = $post->food_addons;
            $category_idies = array_keys( $addons );
            $addon_terms = [];
            for ( $i = 0; $i < count( $category_idies ); $i++ ) {
                $ID = $category_idies[ $i ];
                $addon_terms[] = $ID;
                if ( is_array( $addons[ $ID ][ 'items' ] ) ) {
                    $addon_terms = array_merge( $addon_terms, $addons[ $ID ][ 'items' ] );
                }
            }
            $addon_terms = array_unique( $addon_terms );
            $product_terms = wp_get_post_terms( $post->ID, 'addon_category', array( 'fields' => 'ids' ) );

            if ( !is_wp_error( $product_terms ) ) {
                $terms_to_remove = array_diff( $product_terms, $addon_terms );
                wp_remove_object_terms( $post->ID, $terms_to_remove, 'addon_category' );
            }
            wp_set_post_terms( $post->ID, $addon_terms, 'addon_category', true );
            $sanitize_addon = rpress_sanitize_array( $addons );
            $clean_addon_meta = sanitize_meta( "_addon_items", $sanitize_addon, "post" );
            update_post_meta( $post->ID, '_addon_items', $clean_addon_meta );
        }
        //Adding/updating Notes
        if ( property_exists( $post, "notes" ) && !empty( $post->notes ) ) {
            $clean_notes = sanitize_meta( "rpress_product_notes", $post->notes, "post" );
            update_post_meta( $post->ID, "rpress_product_notes", $clean_notes );
        }
    }

    /**
     * Callback of pre-insert
     * This method responsible for adding extra pre insert validation 
     * @param stdClass $prepared_post | Default Prepared validation array
     * @param  WP_REST_Request $request Description
     * * */
    public function fooditem_pre_insert( stdClass $prepared_post, WP_REST_Request $request ): stdClass {
        $schema = $this->get_item_schema();
        //Checking for food type
        if ( !empty( $schema[ 'properties' ][ 'food_type' ] ) && !empty( $request->get_param( "food_type" ) ) ) {
            $prepared_post->food_type = $request->get_param( "food_type" );
        }
        //Checking for price
        if ( !empty( $schema[ 'properties' ][ 'price' ] ) && !empty( $request->get_param( "price" ) ) ) {
            $prepared_post->price = $request->get_param( "price" );
        }
        //Checking for Categories 
        if ( !empty( $schema[ 'properties' ][ 'food_categories' ] ) && !empty( $request->get_param( "food_categories" ) ) ) {
            $prepared_post->food_categories = $request->get_param( "food_categories" );
        }
        //Checking for notes 
        if ( !empty( $schema[ 'properties' ][ 'notes' ] ) && !empty( $request->get_param( "notes" ) ) ) {
            $prepared_post->notes = $request->get_param( "notes" );
        }
        //Checking for has veriable price
        $prepared_post->has_variable_prices = false;
        if ( !empty( $schema[ 'properties' ][ 'has_variable_prices' ] ) && !empty( $request->get_param( "has_variable_prices" ) ) ) {
            $prepared_post->has_variable_prices = true;
        }
        //Checking for Variable price 
        if ( !empty( $schema[ 'properties' ][ 'variable_prices' ] ) && $prepared_post->has_variable_prices && is_array( $request->get_param( "variable_prices" ) ) && count( $request->get_param( "variable_prices" ) ) > 0 ) {
            $prepared_post->variable_prices = array();
            $variable_prices = $request->get_param( "variable_prices" );
            foreach ( $variable_prices as $count => $prices ) {
                if ( isset( $prices[ "name" ] ) ) {
                    $prepared_post->variable_prices[ $count ][ "name" ] = $prices[ "name" ];
                }
                if ( isset( $prices[ "amount" ] ) ) {
                    $prepared_post->variable_prices[ $count ][ "amount" ] = $prices[ "amount" ];
                }
            }
        }
        //Checkingfor variable price label
        if ( !empty( $schema[ 'properties' ][ 'variable_price_label' ] ) && $prepared_post->has_variable_prices && !empty( $request->get_param( "variable_price_label" ) ) ) {
            $prepared_post->variable_price_label = $request->get_param( "variable_price_label" );
        }
        //Checking for food addons
        if ( !empty( $schema[ 'properties' ][ 'food_addons' ] ) && is_array( $request->get_param( "food_addons" ) ) && count( $request->get_param( "food_addons" ) ) > 0 ) {
            $prepared_post->food_addons = $request->get_param( "food_addons" );
        }
        return $prepared_post;
    }

    /**
     * Callback of prepare schema 
     * Basically this callback method adding additional schema
     * @param array $schema | default schema array
     * @return array $schema |  Modified array
     * @since  3.0.0
     * @access public
     * * */
    public function fooditem_item_schema( array $schema ): array {
        $additional_schema = array(
            'food_type' => array(
                "title" => __( "Food Type", "restropress" ),
                'description' => __( "Food Type", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "price" => array(
                "title" => __( "Food Price", "restropress" ),
                'description' => __( "Food Price", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "variable_prices" => array(
                "title" => __( "Food Veriable Price", "restropress" ),
                'description' => __( "Food Veriable Price", "restropress" ),
                'type' => 'array',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
                "items" => array(
                    "title" => __( "Price", "restropress" ),
                    'description' => __( "Veriable Price", "restropress" ),
                    'type' => 'object',
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                    "properties" => array(
                        "name" => array(
                            "title" => __( "Name", "restropress" ),
                            'description' => __( "Veriable Price Name", "restropress" ),
                            'type' => 'string',
                            'context' => array( 'view', 'edit', 'embed' ),
                            'readonly' => true,
                        ),
                        "amount" => array(
                            "title" => __( "Amount", "restropress" ),
                            'description' => __( "Veriable Amount", "restropress" ),
                            'type' => 'string',
                            'context' => array( 'view', 'edit', 'embed' ),
                            'readonly' => true,
                        )
                    )
                )
            ),
            "is_single_price_mode" => array(
                "title" => __( "Is Single Price Mode", "restropress" ),
                'description' => __( "Is Single Price Mode", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "has_variable_prices" => array(
                "title" => __( "Has variable Prices", "restropress" ),
                'description' => __( "Has variable Prices", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "variable_price_label" => array(
                "title" => __( "Variable Price Label", "restropress" ),
                'description' => __( "Variable price label", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "food_categories" => array(
                "title" => __( "Food Categories", "restropress" ),
                'description' => __( "Food categories", "restropress" ),
                'type' => 'array',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
                "items" => array(
                    "title" => __( "Catgory ID", "restropress" ),
                    'description' => __( "Food category ID", "restropress" ),
                    'type' => 'integer',
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                )
            ),
            'fodditem_type' => array(
                "title" => __( "Food Item Type", "restropress" ),
                'description' => __( "Food Item Type", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "is_bundled_fooditem" => array(
                "title" => __( "Is bundled FoodItem", "restropress" ),
                'description' => __( "Is Bundled FoodItem", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "bundled_fooditems" => array(
                "title" => __( "Food Categories", "restropress" ),
                'description' => __( "Food categories", "restropress" ),
                'type' => 'array',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
                "items" => array(
                    "title" => __( "Catgory ID", "restropress" ),
                    'description' => __( "Food category ID", "restropress" ),
                    'type' => 'integer',
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
            ),
            "notes" => array(
                "title" => __( "Notes", "restropress" ),
                'description' => __( "Food Notes", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "sku" => array(
                "title" => __( "SKU", "restropress" ),
                'description' => __( "Food SKU", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "button_behavior" => array(
                "title" => __( "Button behavior", "restropress" ),
                'description' => __( "Button behavior", "restropress" ),
                'type' => 'string',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "sales" => array(
                "title" => __( "Foods sales", "restropress" ),
                'description' => __( "Number of times this has been purchased", "restropress" ),
                'type' => 'integer',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "earnings" => array(
                "title" => __( "Foods Earning", "restropress" ),
                'description' => __( "Total fooditem earnings", "restropress" ),
                'type' => 'integer',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "is_free" => array(
                "title" => __( "Is Free", "restropress" ),
                'description' => __( "True when the fooditem is free", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "is_quantities_disabled" => array(
                "title" => __( "Is Quntities Disabled", "restropress" ),
                'description' => __( "Is quantity input disabled on this food", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            "can_purchase" => array(
                "title" => __( "Can Purchase", "restropress" ),
                'description' => __( "If the current fooditem ID can be purchased", "restropress" ),
                'type' => 'boolean',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
            ),
            'food_addons' => array(
                "title" => __( "Food Addons", "restropress" ),
                'description' => __( "Food Addons", "restropress" ),
                'type' => 'object',
                'context' => array( 'view', 'edit', 'embed' ),
                'readonly' => true,
                'properties' => array(
                    'category_id' => array(
                        "title" => __( "Food Addons", "restropress" ),
                        'description' => __( "Food Addons", "restropress" ),
                        'type' => 'object',
                        'context' => array( 'view', 'edit', 'embed' ),
                        'readonly' => true,
                        "properties" => array(
                            "category" => array(
                                "title" => __( "category ID", "restropress" ),
                                'description' => __( "category ID", "restropress" ),
                                'type' => 'string',
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "max_addons" => array(
                                "title" => __( "Max Addons", "restropress" ),
                                'description' => __( "Max Addons", "restropress" ),
                                'type' => 'integer',
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "is_required" => array(
                                "title" => __( "Is Required", "restropress" ),
                                'description' => __( "Is Required", "restropress" ),
                                'type' => 'string',
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "items" => array(
                                "title" => __( "Food Addons", "restropress" ),
                                'description' => __( "Food Addons", "restropress" ),
                                'type' => 'array',
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "items" => array(
                                    "title" => __( "Category ID", "restropress" ),
                                    'description' => __( "Category ID", "restropress" ),
                                    'type' => 'integer',
                                    'context' => array( 'view', 'edit', 'embed' ),
                                    'readonly' => true,
                                )
                            ),
                            "prices" => array(
                                "title" => __( "Food Addons Prices", "restropress" ),
                                'description' => __( "Food Addons Prices", "restropress" ),
                                'type' => [  "object", "array" ],
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "properties" => array(
                                    "child_category_id" => array(
                                        "title" => __( "Child Category ID", "restropress" ),
                                        'description' => __( "Child Category ID", "restropress" ),
                                        'type' => 'object',
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                        "properties" => array(
                                            "addon_name" => array(
                                                "title" => __( "Addon Name", "restropress" ),
                                                'description' => __( "Addon Name", "restropress" ),
                                                'type' => 'string',
                                                'context' => array( 'view', 'edit', 'embed' ),
                                                'readonly' => true,
                                            )
                                        )
                                    )
                                )
                            ),
                            "default" => array(
                                "title" => __( "Default Values", "restropress" ),
                                'description' => __( "Default Values", "restropress" ),
                                'type' => 'array',
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "items" => array(
                                    "title" => __( "Default Values", "restropress" ),
                                    'description' => __( "Default Values", "restropress" ),
                                    'type' => 'string',
                                    'context' => array( 'view', 'edit', 'embed' ),
                                    'readonly' => true,
                                )
                            )
                        )
                    )
                )
            )
        );
        $additional_schema = apply_filters( "rp_rest_api_{$this->post_type}_schema", $additional_schema );
        foreach ( $additional_schema as $schema_key => $schema_value ) {
            $schema[ 'properties' ][ $schema_key ] = $schema_value;
        }
        return $schema;
    }

    /**
     * Callback of rest_prepare
     * @param WP_REST_Response $response Having all data need to display
     * @param WP_Post $post || This case rpress_payment post type data
     * @param  WP_REST_Request $request || Request Object
     * @return WP_REST_Response || Returning some modifing data
     * @since 3.0.0
     * * */
    public function rp_api_prepeare_data( WP_REST_Response $response, WP_Post $fooditem_post, WP_REST_Request $request ): WP_REST_Response {
        $food_item = new RPRESS_Fooditem( $fooditem_post->ID );
        $fields = $this->get_fields_for_response( $request );
        $data = $response->get_data();

        //Checking food type and adding food type
        if ( rest_is_field_included( 'food_type', $fields ) ) {
            $data[ "food_type" ] = $food_item->get_food_type();
        }

        //Checking whether single price
        if ( rest_is_field_included( 'is_single_price_mode', $fields ) ) {
            $data[ "is_single_price_mode" ] = $food_item->is_single_price_mode();
        }

        //Checking whether has_variable_price
        if ( rest_is_field_included( 'has_variable_prices', $fields ) ) {
            $data[ "has_variable_prices" ] = $food_item->has_variable_prices();
        }
        //Checking whether free
        if ( rest_is_field_included( 'is_free', $fields ) ) {
            $data[ "is_free" ] = $food_item->is_free();
        }
        //Checking whether free
        if ( rest_is_field_included( 'can_purchase', $fields ) ) {
            $data[ "can_purchase" ] = $food_item->can_purchase();
        }
        //Checking whether quantity disabled
        if ( rest_is_field_included( 'is_quantities_disabled', $fields ) ) {
            $data[ "is_quantities_disabled" ] = $food_item->quantities_disabled();
        }
        //Checking price and adding price
        if ( rest_is_field_included( 'price', $fields ) ) {
            $data[ "price" ] = $food_item->get_price();
        }
        //Checkking variable price and adding 
        if ( rest_is_field_included( 'variable_prices', $fields ) ) {
            $data[ "variable_prices" ] = $food_item->get_prices();
        }
        //Checkking type and adding 
        if ( rest_is_field_included( 'fodditem_type', $fields ) ) {
            $data[ "fodditem_type" ] = $food_item->get_type();
        }
        //Checkking sales and adding 
        if ( rest_is_field_included( 'sales', $fields ) ) {
            $data[ "sales" ] = $food_item->get_sales();
        }
        //Checkking sales and adding 
        if ( rest_is_field_included( 'earnings', $fields ) ) {
            $data[ "earnings" ] = $food_item->get_earnings();
        }
        //Checkking button behavior and adding 
        if ( rest_is_field_included( 'button_behavior', $fields ) ) {
            $data[ "button_behavior" ] = $food_item->get_button_behavior();
        }
        //Checkking sku and adding 
        if ( rest_is_field_included( 'sku', $fields ) ) {
            $data[ "sku" ] = $food_item->get_sku();
        }
        //Checkking notes and adding 
        if ( rest_is_field_included( 'notes', $fields ) ) {
            $data[ "notes" ] = $food_item->get_notes();
        }
        //Checkking is bundled fooditem and adding 
        if ( rest_is_field_included( 'is_bundled_fooditem', $fields ) ) {
            $data[ "is_bundled_fooditem" ] = $food_item->is_bundled_fooditem();
        }
        //Checkking bundled food item and adding 
        if ( rest_is_field_included( 'bundled_fooditems', $fields ) ) {
            $data[ "bundled_fooditems" ] = $food_item->get_bundled_fooditems();
        }
        //Checkking food categories and adding 
        if ( rest_is_field_included( 'food_categories', $fields ) ) {
            $data[ "food_categories" ] = $food_item->get_food_categories();
        }
        $response->set_data( $data );

        return $response;
    }

}
