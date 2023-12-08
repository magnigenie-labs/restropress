<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of class-rp-rest-cart-v1-controller
 *
 * @author PC
 */
class RP_REST_Cart_V1_Controller extends WP_REST_Controller {

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
    protected $rest_base = 'cart';

    public function __construct() {
        // $this->verify = new RP_JWT_Verifier();
        $this->cart_object = new RPRESS_Cart();
    }

    /**
     * Registering Route
     * * */
    public function register_routes() {
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array( $this, 'get_cart_content' ),
                        'permission_callback' => array( $this, 'get_cart_permissions_check' ),
                        'args' => [],
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array( $this, 'add_cart_content' ),
                        'permission_callback' => array( $this, 'add_cart_permissions_check' ),
                        'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    ),
                    array(
                        'methods' => WP_REST_Server::DELETABLE,
                        'callback' => array( $this, 'delete_cart_content' ),
                        'permission_callback' => array( $this, 'delete_cart_permissions_check' ),
                        'args' => [],
                    ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<food_id>[\d]+)',
                array(
                    array(
                        'methods' => WP_REST_Server::EDITABLE,
                        'callback' => array( $this, 'update_cart_content' ),
                        'permission_callback' => array( $this, 'add_cart_permissions_check' ),
                        'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                    ),
                )
        );
    }

    /**
     * Cart Schema
     * @return array 
     * @since 3.0.0
     * * */
    public function get_item_schema(): array {
        if ( $this->schema ) {
            return $this->add_additional_fields_schema( $this->schema );
        }
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => "customer",
            'type' => 'object',
            "properties" => array(
                "cart_details" => array(
                    'description' => __( "Cart Item", "restropress" ),
                    'type' => "array",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                    "items" => array(
                        'description' => __( "Cart", "restropress" ),
                        'type' => "object",
                        'context' => array( 'view', 'edit', 'embed' ),
                        'readonly' => true,
                        "properties" => array(
                            "name" => array(
                                'description' => __( "Name of food", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "id" => array(
                                'description' => __( "ID of food", "restropress" ),
                                'type' => "integer",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "instruction" => array(
                                'description' => __( "Instruction of food", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "item_number" => array(
                                'description' => __( "Items ", "restropress" ),
                                'type' => "object",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "properties" => array(
                                    "id" => array(
                                        'description' => __( "ID", "restropress" ),
                                        'type' => "integer",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "quantity" => array(
                                        'description' => __( "Quantity", "restropress" ),
                                        'type' => "integer",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "options" => array(
                                        'description' => __( "Options", "restropress" ),
                                        'type' => array( "object", "item" ),
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                        "properties" => array(
                                            "quantity" => array(
                                                'description' => __( "Quantity", "restropress" ),
                                                'type' => "integer",
                                                'context' => array( 'view', 'edit', 'embed' ),
                                                'readonly' => true,
                                            ),
                                            "price_id" => array(
                                                'description' => __( "Price ID", "restropress" ),
                                                'type' => "integer",
                                                'context' => array( 'view', 'edit', 'embed' ),
                                                'readonly' => true,
                                            )
                                        ),
                                        "items" => array(
                                            'description' => __( "Options", "restropress" ),
                                            'type' => "object",
                                            'context' => array( 'view', 'edit', 'embed' ),
                                            'readonly' => true,
                                            "properties" => array(
                                                "addon_item_name" => array(
                                                    'description' => __( "Addon Item Name", "restropress" ),
                                                    'type' => "string",
                                                    'context' => array( 'view', 'edit', 'embed' ),
                                                    'readonly' => true,
                                                ),
                                                "addon_id" => array(
                                                    'description' => __( "Addon ID", "restropress" ),
                                                    'type' => "integer",
                                                    'context' => array( 'view', 'edit', 'embed' ),
                                                    'readonly' => true,
                                                ),
                                                "price" => array(
                                                    'description' => __( "Price", "restropress" ),
                                                    'type' => "string",
                                                    'context' => array( 'view', 'edit', 'embed' ),
                                                    'readonly' => true,
                                                ),
                                                "quantity" => array(
                                                    'description' => __( "Price", "restropress" ),
                                                    'type' => "integer",
                                                    'context' => array( 'view', 'edit', 'embed' ),
                                                    'readonly' => true,
                                                )
                                            )
                                        )
                                    ),
                                )
                            ),
                            "item_price" => array(
                                'description' => __( "Item Price", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "quantity" => array(
                                'description' => __( "Quantity", "restropress" ),
                                'type' => "integer",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "discount" => array(
                                'description' => __( "Discount", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "subtotal" => array(
                                'description' => __( "Subtotal", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "tax" => array(
                                'description' => __( "Tax", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "price" => array(
                                'description' => __( "Description", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "addon_items" => array(
                                'description' => __( "Addon Items", "restropress" ),
                                'type' => array( "object", "array" ),
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "properties" => array(
                                    "quantity" => array(
                                        'description' => __( "Quantity", "restropress" ),
                                        'type' => "integer",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "price_id" => array(
                                        'description' => __( "Price ID", "restropress" ),
                                        'type' => "integer",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    )
                                ),
                                "items" => array(
                                    'description' => __( "Addon Item", "restropress" ),
                                    'type' => "object",
                                    'context' => array( 'view', 'edit', 'embed' ),
                                    'readonly' => true,
                                    "properties" => array(
                                        "addon_item_name" => array(
                                            'description' => __( "Addon Item Name", "restropress" ),
                                            'type' => "string",
                                            'context' => array( 'view', 'edit', 'embed' ),
                                            'readonly' => true,
                                        ),
                                        "addon_id" => array(
                                            'description' => __( "Addon ID", "restropress" ),
                                            'type' => "integer",
                                            'context' => array( 'view', 'edit', 'embed' ),
                                            'readonly' => true,
                                        ),
                                        "price" => array(
                                            'description' => __( "Price", "restropress" ),
                                            'type' => "string",
                                            'context' => array( 'view', 'edit', 'embed' ),
                                            'readonly' => true,
                                        ),
                                        "quantity" => array(
                                            'description' => __( "Price", "restropress" ),
                                            'type' => "integer",
                                            'context' => array( 'view', 'edit', 'embed' ),
                                            'readonly' => true,
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );
        $schema = apply_filters( "rest_rp_cart_item_schema", $schema );

        $this->schema = $schema;

        return $this->add_additional_fields_schema( $this->schema );
    }

    /**
     * Overriding item for database
     * @param WP_Request $request
     * @return  stdClass | WP_Error 
     * @since 3.0.0
     * * */
    public function prepare_item_for_database( $request ): array {
        $cart_object_array = array();
        $cart_details = $request[ 'cart_details' ];
        $schema = $this->get_item_schema();
        $schema_properties = $schema[ "properties" ][ "cart_details" ][ "items" ][ "properties" ];
//        print_r( $schema_properties );
        foreach ( $cart_details as $cart_key => $cart_value ) {
            $prepared_post = new stdClass();
            //Adding name 
            if ( !empty( $schema_properties[ 'name' ] ) && isset( $cart_details[ $cart_key ][ 'name' ] ) ) {
                $prepared_post->name = $cart_value[ 'name' ];
            }
            //Adding ID
            if ( !empty( $schema_properties[ 'id' ] ) && isset( $cart_details[ $cart_key ][ 'id' ] ) ) {
                $prepared_post->id = $cart_value[ 'id' ];
            }
            //Adding instruction
            if ( !empty( $schema_properties[ 'instruction' ] ) && isset( $cart_details[ $cart_key ][ 'instruction' ] ) ) {
                $prepared_post->instruction = $cart_value[ 'instruction' ];
            }
            //Adding ID to item number
            if ( !empty( $schema_properties[ 'item_number' ][ 'properties' ][ 'id' ] ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'id' ] ) ) {
                $prepared_post->item_number[ 'id' ] = $cart_value[ 'item_number' ][ 'id' ];
            }

            if ( !empty( $schema_properties[ 'item_number' ][ 'properties' ][ 'quantity' ] ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'quantity' ] ) ) {
                $prepared_post->item_number[ 'quantity' ] = $cart_value[ 'item_number' ][ 'quantity' ];
            }

            if ( !empty( $schema_properties[ 'item_number' ][ 'properties' ][ 'options' ] ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'options' ] ) ) {
                $options = $schema_properties[ 'item_number' ][ 'properties' ][ 'options' ][ 'properties' ];
                if ( !empty( $options[ 'quantity' ] ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'options' ][ "quantity" ] ) ) {
                    $prepared_post->item_number[ 'options' ][ 'quantity' ] = $cart_value[ 'item_number' ][ 'options' ][ 'quantity' ];
                }
                if ( !empty( $options[ 'price_id' ] ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'options' ][ "price_id" ] ) ) {
                    $prepared_post->item_number[ 'options' ][ 'price_id' ] = $cart_value[ 'item_number' ][ 'options' ][ 'price_id' ];
                }

                $options_item = $schema_properties[ 'item_number' ][ 'properties' ][ 'options' ][ 'items' ][ 'properties' ];
                if ( !empty( $options_item ) && isset( $cart_details[ $cart_key ][ 'item_number' ][ 'options' ] ) && is_array( $cart_details[ $cart_key ][ 'item_number' ][ 'options' ] ) ) {
                    $addon_data = $cart_details[ $cart_key ][ 'item_number' ][ 'options' ];
                    foreach ( $addon_data as $addon_key => $addon_value ) {
                        if ( is_array( $addon_value ) ) {
                            if ( !empty( $options_item[ 'addon_item_name' ] ) && isset( $addon_value[ 'addon_item_name' ] ) ) {
                                $prepared_post->item_number[ 'options' ][ $addon_key ][ "addon_item_name" ] = $addon_value[ 'addon_item_name' ];
                            }

                            if ( !empty( $options_item[ 'addon_id' ] ) && isset( $addon_value[ 'addon_id' ] ) ) {
                                $prepared_post->item_number[ 'options' ][ $addon_key ][ "addon_id" ] = $addon_value[ 'addon_id' ];
                            }

                            if ( !empty( $options_item[ 'price' ] ) && isset( $addon_value[ 'price' ] ) ) {
                                $prepared_post->item_number[ 'options' ][ $addon_key ][ "price" ] = $addon_value[ 'price' ];
                            }

                            if ( !empty( $options_item[ 'quantity' ] ) && isset( $addon_value[ 'quantity' ] ) ) {
                                $prepared_post->item_number[ 'options' ][ $addon_key ][ "quantity" ] = $addon_value[ 'quantity' ];
                            }
                        }
                    }
                }
            }
            if ( !empty( $schema_properties[ 'item_price' ] ) && isset( $cart_details[ $cart_key ][ 'item_price' ] ) ) {
                $prepared_post->item_price = $cart_value[ 'item_price' ];
            }
            if ( !empty( $schema_properties[ 'quantity' ] ) && isset( $cart_details[ $cart_key ][ 'quantity' ] ) ) {
                $prepared_post->quantity = $cart_value[ 'quantity' ];
            }
            if ( !empty( $schema_properties[ 'discount' ] ) && isset( $cart_details[ $cart_key ][ 'discount' ] ) ) {
                $prepared_post->discount = $cart_value[ 'discount' ];
            }
            if ( !empty( $schema_properties[ 'subtotal' ] ) && isset( $cart_details[ $cart_key ][ 'subtotal' ] ) ) {
                $prepared_post->subtotal = $cart_value[ 'subtotal' ];
            }
            if ( !empty( $schema_properties[ 'tax' ] ) && isset( $cart_details[ $cart_key ][ 'tax' ] ) ) {
                $prepared_post->tax = $cart_value[ 'tax' ];
            }
            if ( !empty( $schema_properties[ 'price' ] ) && isset( $cart_details[ $cart_key ][ 'price' ] ) ) {
                $prepared_post->price = $cart_value[ 'price' ];
            }

            if ( !empty( $schema_properties[ 'addon_items' ][ 'properties' ] ) && isset( $cart_details[ $cart_key ][ 'addon_items' ] ) ) {
                $addon_item = $schema_properties[ 'addon_items' ][ 'properties' ];
                if ( !empty( $addon_item[ 'quantity' ] ) && isset( $cart_details[ $cart_key ][ 'addon_items' ][ "quantity" ] ) ) {
                    $prepared_post->addon_items[ 'quantity' ] = $cart_value[ 'addon_items' ][ 'quantity' ];
                }
                if ( !empty( $addon_item[ 'price_id' ] ) && isset( $cart_details[ $cart_key ][ 'addon_items' ][ "price_id" ] ) ) {
                    $prepared_post->addon_items[ 'price_id' ] = $cart_value[ 'addon_items' ][ 'price_id' ];
                }

                $options_item = $schema_properties[ 'addon_items' ][ 'items' ][ 'properties' ];
                if ( !empty( $options_item ) && isset( $cart_details[ $cart_key ][ 'addon_items' ] ) && is_array( $cart_details[ $cart_key ][ 'addon_items' ] ) ) {
                    $addon_data = $cart_details[ $cart_key ][ 'addon_items' ];
                    foreach ( $addon_data as $addon_key => $addon_value ) {
                        if ( is_array( $addon_value ) ) {
                            if ( !empty( $options_item[ 'addon_item_name' ] ) && isset( $addon_value[ 'addon_item_name' ] ) ) {
                                $prepared_post->addon_items[ $addon_key ][ "addon_item_name" ] = $addon_value[ 'addon_item_name' ];
                            }

                            if ( !empty( $options_item[ 'addon_id' ] ) && isset( $addon_value[ 'addon_id' ] ) ) {
                                $prepared_post->addon_items[ $addon_key ][ "addon_id" ] = $addon_value[ 'addon_id' ];
                            }

                            if ( !empty( $options_item[ 'price' ] ) && isset( $addon_value[ 'price' ] ) ) {
                                $prepared_post->addon_items[ $addon_key ][ "price" ] = $addon_value[ 'price' ];
                            }

                            if ( !empty( $options_item[ 'quantity' ] ) && isset( $addon_value[ 'quantity' ] ) ) {
                                $prepared_post->addon_items[ $addon_key ][ "quantity" ] = $addon_value[ 'quantity' ];
                            }
                        }
                    }
                }
            }
            $cart_object_array[] = $prepared_post;
        }
        return $cart_object_array;
    }

    /**
     * Overriding item for Response
     * @param WP_Request $request
     * @return  stdClass | WP_Error 
     * @since 3.0.0
     * * */
    public function prepare_item_for_response( $cart_data, $request ): WP_REST_Response {
        $schema = $this->get_item_schema();
        $cart_details_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ] );
        $data = array();
        if ( rest_is_field_included( 'name', $cart_details_field ) && !empty( $cart_data[ 'name' ] ) ) {
            $data[ 'name' ] = $cart_data[ 'name' ];
        }
        if ( rest_is_field_included( 'id', $cart_details_field ) && !empty( $cart_data[ 'id' ] ) ) {
            $data[ 'id' ] = $cart_data[ 'id' ];
        }
        if ( rest_is_field_included( 'instruction', $cart_details_field ) && !empty( $cart_data[ 'instruction' ] ) ) {
            $data[ 'instruction' ] = $cart_data[ 'instruction' ];
        }
        if ( rest_is_field_included( 'item_price', $cart_details_field ) && !empty( $cart_data[ 'item_price' ] ) ) {
            $data[ 'item_price' ] = $cart_data[ 'item_price' ];
        }
        if ( rest_is_field_included( 'quantity', $cart_details_field ) && !empty( $cart_data[ 'quantity' ] ) ) {
            $data[ 'quantity' ] = $cart_data[ 'quantity' ];
        }
        if ( rest_is_field_included( 'discount', $cart_details_field ) && !empty( $cart_data[ 'discount' ] ) ) {
            $data[ 'discount' ] = $cart_data[ 'discount' ];
        }
        if ( rest_is_field_included( 'subtotal', $cart_details_field ) && !empty( $cart_data[ 'subtotal' ] ) ) {
            $data[ 'subtotal' ] = $cart_data[ 'subtotal' ];
        }
        if ( rest_is_field_included( 'tax', $cart_details_field ) && !empty( $cart_data[ 'tax' ] ) ) {
            $data[ 'tax' ] = $cart_data[ 'tax' ];
        }
        if ( rest_is_field_included( 'price', $cart_details_field ) && !empty( $cart_data[ 'price' ] ) ) {
            $data[ 'price' ] = $cart_data[ 'price' ];
        }
        $cart_details_single_addon_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ][ 'addon_items' ][ 'properties' ] );
        if ( rest_is_field_included( 'quantity', $cart_details_single_addon_field ) && isset( $cart_data[ 'addon_items' ][ 'quantity' ] ) ) {
            $data[ 'addon_items' ][ 'quantity' ] = $cart_data[ 'addon_items' ][ 'quantity' ];
        }
        if ( rest_is_field_included( 'price_id', $cart_details_single_addon_field ) && isset( $cart_data[ 'addon_items' ][ 'price_id' ] ) ) {
            $data[ 'addon_items' ][ 'price_id' ] = $cart_data[ 'addon_items' ][ 'price_id' ];
        }
        $cart_details_multiple_addon_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ][ 'addon_items' ][ 'items' ][ 'properties' ] );
        if ( isset( $cart_data[ 'addon_items' ]) &&  is_array( $cart_data[ 'addon_items' ] ) && count( $cart_data[ 'addon_items' ] ) > 0 ) {
            foreach ( $cart_data[ 'addon_items' ] as $addon_key => $addon_value ) {
                if ( is_array( $addon_value ) ) {
                    if ( rest_is_field_included( "addon_item_name", $cart_details_multiple_addon_field ) && isset( $addon_value[ 'addon_item_name' ] ) ) {
                        $data[ 'addon_items' ][ $addon_key ][ 'addon_item_name' ] = $addon_value[ 'addon_item_name' ];
                    }
                    if ( rest_is_field_included( "addon_id", $cart_details_multiple_addon_field ) && isset( $addon_value[ 'addon_id' ] ) ) {
                        $data[ 'addon_items' ][ $addon_key ][ 'addon_id' ] = $addon_value[ 'addon_id' ];
                    }
                    if ( rest_is_field_included( "price", $cart_details_multiple_addon_field ) && isset( $addon_value[ 'price' ] ) ) {
                        $data[ 'addon_items' ][ $addon_key ][ 'price' ] = $addon_value[ 'price' ];
                    }
                    if ( rest_is_field_included( "quantity", $cart_details_multiple_addon_field ) && isset( $addon_value[ 'quantity' ] ) ) {
                        $data[ 'addon_items' ][ $addon_key ][ 'quantity' ] = $addon_value[ 'quantity' ];
                    }
                }
            }
        }
        $cart_details_item_numer_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ][ 'item_number' ][ 'properties' ] );
        if ( rest_is_field_included( 'id', $cart_details_item_numer_field ) && isset( $cart_data[ 'item_number' ][ 'id' ] ) ) {
            $data[ 'item_number' ][ 'id' ] = $cart_data[ 'item_number' ][ 'id' ];
        }
        if ( rest_is_field_included( 'quantity', $cart_details_item_numer_field ) && isset( $cart_data[ 'item_number' ][ 'quantity' ] ) ) {
            $data[ 'item_number' ][ 'quantity' ] = $cart_data[ 'item_number' ][ 'quantity' ];
        }
        $cart_details_item_numer_option_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ][ 'item_number' ][ 'properties' ][ 'options' ][ 'properties' ] );
        if ( rest_is_field_included( 'quantity', $cart_details_item_numer_option_field ) && isset( $cart_data[ 'item_number' ][ 'options' ][ 'quantity' ] ) ) {
            $data[ 'item_number' ][ 'options' ][ 'quantity' ] = $cart_data[ 'item_number' ][ 'options' ][ 'quantity' ];
        }
        if ( rest_is_field_included( 'price_id', $cart_details_item_numer_option_field ) && isset( $cart_data[ 'item_number' ][ 'options' ][ 'price_id' ] ) ) {
            $data[ 'item_number' ][ 'options' ][ 'price_id' ] = $cart_data[ 'item_number' ][ 'options' ][ 'price_id' ];
        }
        $cart_details_item_numer_option_multiple_field = array_keys( $schema[ 'properties' ][ 'cart_details' ][ 'items' ][ 'properties' ][ 'item_number' ][ 'properties' ][ 'options' ][ 'items' ][ 'properties' ] );
        if ( isset($cart_data[ 'item_number' ]) && is_array( $cart_data[ 'item_number' ][ 'options' ] ) && count( $cart_data[ 'item_number' ][ 'options' ] ) > 0 ) {
            foreach ( $cart_data[ 'item_number' ][ 'options' ] as $option_key => $option_value ) {
                if ( is_array( $option_value ) ) {
                    if ( rest_is_field_included( 'addon_item_name', $cart_details_item_numer_option_multiple_field ) && isset( $option_value[ 'addon_item_name' ] ) ) {
                        $data[ 'item_number' ][ 'options' ][ $option_key ][ 'addon_item_name' ] = $option_value[ 'addon_item_name' ];
                    }
                    if ( rest_is_field_included( 'addon_id', $cart_details_item_numer_option_multiple_field ) && isset( $option_value[ 'addon_id' ] ) ) {
                        $data[ 'item_number' ][ 'options' ][ $option_key ][ 'addon_id' ] = $option_value[ 'addon_id' ];
                    }
                    if ( rest_is_field_included( 'price', $cart_details_item_numer_option_multiple_field ) && isset( $option_value[ 'price' ] ) ) {
                        $data[ 'item_number' ][ 'options' ][ $option_key ][ 'price' ] = $option_value[ 'price' ];
                    }
                    if ( rest_is_field_included( 'quantity', $cart_details_item_numer_option_multiple_field ) && isset( $option_value[ 'quantity' ] ) ) {
                        $data[ 'item_number' ][ 'options' ][ $option_key ][ 'quantity' ] = $option_value[ 'quantity' ];
                    }
                }
            }
        }
        $response = new WP_REST_Response( $data );
        return $response;
    }

    /**
     * Empty cart 
     * @param WP_REST_Request $request
     * @return WP_REST_Response $response
     * @since 3.0.0
     * ** */
    public function delete_cart_content( WP_REST_Request $request ): WP_REST_Response {
        rpress_empty_cart();
        $response = new WP_REST_Response();
        $response->set_data( array( "message" => __( "Successfully cart emptied", "Restropress" ) ) );
        $response->set_status( 200 );
        return $response;
    }

    /**
     * Update cart 
     * @param WP_REST_Request $request
     * @return  WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function update_cart_content( WP_REST_Request $request ) {
        print_r( $request[ 'food_id' ] );
//        $cart_details = $request->get_param( 'cart_details' );
        $cart_data = $this->prepare_item_for_database( $request );
        $filteredArray = array_filter( $cart_data, function ( $item ) {
            //var_dump( $item->id == $request[ 'food_id' ] );
            print_r( $item->id == $request[ 'food_id' ] );
            return $item->id == $request[ 'food_id' ];
        } );
        print_r( $filteredArray );
//        if ( is_array( $cart_data ) && !empty( $cart_data ) ) {
//            $posts = array();
//            for ( $index = 0; $index < count( $cart_data ); $index++ ) {
//                rpress_set_cart_item_quantity( ( int ) $cart_data[ $index ]->id, ( int ) $cart_data[ $index ]->quantity, ( array ) $cart_data[ $index ] );
//                $data = $this->prepare_item_for_response( ( array ) $cart_data[ $index ], $request );
//                $posts[] = $this->prepare_response_for_collection( $data );
//            }
//            $response = rest_ensure_response( $posts );
//            return $response;
//        }
//        $response = new WP_REST_Response( [ "message" => apply_filters( "cart_empty_message", __( "Cart is empty", "restropress" ) ) ] );
//        $ensure_response = rest_ensure_response( $response );
//        return $ensure_response;
    }

    public function get_cart_content( WP_REST_Request $request ): WP_REST_Response {

        $contents = rpress_get_cart_contents();
        if ( is_array( $contents ) && count( $contents ) > 0 ) {
            $posts = array();
            foreach ( $contents as $contents_key => $contents_value ) {
                $data = $this->prepare_item_for_response( $contents_value, $request );
                $posts[] = $this->prepare_response_for_collection( $data );
            }
            $response = rest_ensure_response( $posts );
            return $response;
        }
        $response = new WP_REST_Response( [ "message" => apply_filters( "cart_empty_message", __( "Cart is empty", "restropress" ) ) ] );
        $ensure_response = rest_ensure_response( $response );
        return $ensure_response;
    }

    public function add_cart_content( WP_REST_Request $request ) {
        $cart_data = $this->prepare_item_for_database( $request );
        if ( is_array( $cart_data ) && !empty( $cart_data ) ) {
            $posts = array();
            for ( $index = 0; $index < count( $cart_data ); $index++ ) {
                if ( property_exists( $cart_data[ $index ], "id" ) ) {
                    $is_added = rpress_add_to_cart( $cart_data[ $index ]->id, ( array ) $cart_data[ $index ] );
                    if ( $is_added ) {
                        $data = $this->prepare_item_for_response( ( array ) $cart_data[ $index ], $request );
                        $posts[] = $this->prepare_response_for_collection( $data );
                    }
                }
            }
            $response = rest_ensure_response( $posts );
            return $response;
        }
    }

    public function add_cart_permissions_check( WP_REST_Request $request ) {
        $result = new RP_JWT_Verifier( $request );
        return $result;
    }

    public function get_cart_permissions_check( WP_REST_Request $request ) {
        $result = new RP_JWT_Verifier( $request );
        return $result;
    }

    public function delete_cart_permissions_check( WP_REST_Request $request ) {
        $result = new RP_JWT_Verifier( $request );
        return $result;
    }

    /**
     * Update cart permission 
     * @param WP_REST_Request $requst
     * @return boolean | WP_Error 
     * @since 3.0.0
     * * */
    public function update_cart_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        $result = new RP_JWT_Verifier( $request );
        return $result;
    }
}
