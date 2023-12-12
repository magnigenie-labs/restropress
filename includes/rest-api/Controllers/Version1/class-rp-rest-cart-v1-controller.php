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
                        'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
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
                '/' . $this->rest_base,
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
     
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => "cart",
            'type' => 'object',
            'readonly' => true,
            "properties" => array(
                "cart_details" => array(
                    'description' => __("Cart Item", "restropress"),
                    'type' => "array",
                    'context' => array('view', 'edit', 'embed'),
                    "items" => array(
                        'description' => __("Cart", "restropress"),
                        'type' => "object",
                        'context' => array('view', 'edit', 'embed'),
                        "properties" => array(
                            "id" => array(
                                'description' => __("ID of food", "restropress"),
                                'type' => "integer",
                                'context' => array('view', 'edit', 'embed'),
                                'required'=>true,
                            ),
                            "price_id" => array(
                                'description' => __("ID of Variation Item", "restropress"),
                                'type' => "integer",
                                'context' => array('view', 'edit', 'embed'),
                            ),
                            "price" => array(
                                'description' => __("Price of food", "restropress"),
                                'type' => "number",
                                'context' => array('view', 'edit', 'embed'),
                            ),
                            "quantity" => array(
                                'description' => __("Quantity of food", "restropress"),
                                'type' => "integer",
                                'context' => array('view', 'edit', 'embed'),
                            ),
                            "instruction" => array(
                                'description' => __("Instruction of food", "restropress"),
                                'type' => "string",
                                'context' => array('view', 'edit', 'embed'),
                            ),
                            "addon_items" => array(
                                'description' => __("Addon Items", "restropress"),
                                'type' => "array",
                                'context' => array('view', 'edit', 'embed'),
                                "items" => array(
                                    'type' => "object",
                                    'properties' => array(
                                        "addon_item_name" => array(
                                            'description' => __("Addon Item Name", "restropress"),
                                            'type' => "string",
                                            'context' => array('view', 'edit', 'embed'),
                                        ),
                                        "addon_id" => array(
                                            'description' => __("Addon Item ID", "restropress"),
                                            'type' => "integer",
                                            'context' => array('view', 'edit', 'embed'),
                                        ),
                                        "price" => array(
                                            'description' => __("Price of Addon Item", "restropress"),
                                            'type' => "number|null",
                                            'context' => array('view', 'edit', 'embed'),
                                        ),
                                        "quantity" => array(
                                            'description' => __("Quantity of Addon Item", "restropress"),
                                            'type' => "integer",
                                            'context' => array('view', 'edit', 'embed'),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),            
            )
        );
        // $schema = apply_filters( "rest_rp_cart_item_schema", $schema );

    
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
        $data = $request->get_json_params();
        $cart_details = $data[ 'cart_details' ];
        $schema = $this->get_item_schema();
        $schema_properties = $schema[ "properties" ][ "cart_details" ][ "items" ][ "properties" ];
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
            if (!empty($schema_properties['price_id']) && isset($cart_details[$cart_key]['price_id'])) {
                $prepared_post->price_id = $cart_value['price_id'];
            }
            
            if (!empty($schema_properties['price']) && isset($cart_details[$cart_key]['price'])) {
                $prepared_post->price = $cart_value['price'];
            }
            
            if (!empty($schema_properties['quantity']) && isset($cart_details[$cart_key]['quantity'])) {
                $prepared_post->quantity = $cart_value['quantity'];
            }
            
            if (!empty($schema_properties['addon_items']) && isset($cart_details[$cart_key]['addon_items'])) {
                $prepared_post->addon_items = array();
            
                
                foreach ($cart_value['addon_items'] as $addon_key => $addon_value) {
                    $addon_item = array();
            
                    // Adding Addon Item Name
                    if (!empty($schema_properties['addon_items']['items']['properties']['addon_item_name']) && isset($addon_value['addon_item_name'])) {
                        $addon_item['addon_item_name'] = $addon_value['addon_item_name'];
                        
                    }
            
                    // Adding Addon Item ID
                    if (!empty($schema_properties['addon_items']['items']['properties']['addon_id']) && isset($addon_value['addon_id'])) {
                        $addon_item['addon_id']= $addon_value['addon_id'];
                    }
            
                    // Adding Addon Item Price
                    $addon_item['price'] = $addon_value['price'];
            
                    // Adding Addon Item Quantity
                    if (!empty($schema_properties['addon_items']['items']['properties']['quantity']) && isset($addon_value['quantity'])) {
                        $addon_item['quantity'] = $addon_value['quantity'];
                    }
                    $prepared_post->addon_items[] = $addon_item;
                }
                
            }
            $cart_object_array[]= $prepared_post;
            
        }
        return $cart_object_array;
    }

    /**
     * Overriding item for Response
     * @param WP_Request $request
     * @return  stdClass | WP_Error 
     * @since 3.0.0
     * * */
    public function prepare_item_for_response( $cart_data, $request ): array{
    

        $cart_data['instruction']=$cart_data['item_number']['instruction'];
        $cart_data['addon_items']=$cart_data['item_number']['addon_items'];

        unset($cart_data['item_number']);

      

        return $cart_data;
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
        $cart_data = $this->prepare_item_for_database( $request );
    
       if ( is_array( $cart_data ) && !empty( $cart_data ) ) {
           $posts = array();
           for ( $index = 0; $index < count( $cart_data ); $index++ ) {
               rpress_set_cart_item_quantity( ( int ) $cart_data[ $index ]->id, ( int ) $cart_data[ $index ]->quantity, ( array ) $cart_data[ $index ] );
            //    $data = $this->prepare_item_for_response( ( array ) $cart_data[ $index ], $request );
            //    $posts[] = $this->prepare_response_for_collection( $data );
           }
           return $this->get_cart_content($request);
       }
       $response = new WP_REST_Response( [ "message" => apply_filters( "cart_empty_message", __( "Cart is empty", "restropress" ) ) ] );
       $ensure_response = rest_ensure_response( $response );
       return $ensure_response;
    }

    public function get_cart_content( WP_REST_Request $request ): WP_REST_Response {
        $contents = rpress_get_cart_content_details();
        
        if ( is_array( $contents ) && count( $contents ) > 0 ) {
            $posts = array();
            foreach ( $contents as $contents_key => $contents_value ) {
                $data = $this->prepare_item_for_response( $contents_value, $request );
                $posts[] = $this->prepare_response_for_collection( $data );
            }
              // Setup purchase information
	$cart_data = array(
		'fees'         => rpress_get_cart_fees(),        // Any arbitrary fees that have been added to the cart
		'subtotal'     => rpress_get_cart_subtotal(),    // Amount before taxes and discounts
		'discount'     => rpress_get_cart_discounted_amount(), // Discounted amount
		'tax'          => rpress_get_cart_tax(),               // Taxed amount
		'price'        => rpress_get_cart_total(),    // Amount after taxes
		'cart_details' =>  $data,
	);

            $response = rest_ensure_response( $cart_data );
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
                }
            }
        
            return $this->get_cart_content($request);
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
    public function update_cart_permissions_check( WP_REST_Request $request ){
        $result = new RP_JWT_Verifier( $request );
        return $result;
    }
}
