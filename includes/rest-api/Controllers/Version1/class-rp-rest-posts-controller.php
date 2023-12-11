<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Abstract Rest Posts Controller Class
 *
 * @class RP_REST_Posts_Controller
 * @package Restropress\RestApi
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WC_REST_Posts_Controller
 *
 * @package Restropress\RestApi
 * @version  2.6.0
 */
class RP_REST_Posts_Controller extends WP_REST_Posts_Controller {

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
    protected $rest_base = '';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_type = '';

    /**
     * Controls visibility on frontend.
     *
     * @var string
     */
    protected $public = false;

    public function __construct( $post_type = '', $instance = '' ) {
        $this->post_type = $post_type;
        add_filter( "rest_pre_insert_{$this->post_type}", [ $instance, "{$this->post_type}_pre_insert" ], 10, 2 );
    }

    /**
     * Check if a given request has access to read items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check( $request ){
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    public function get_item_permissions_check( $request ){
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    public function update_item_permissions_check( $request ){
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * Overriding of default permission check 
     * @param WP_REST_Request $request 
     * @return boolean || WP_Error
     * @since 3.0.0
     * * */
    public function create_item_permissions_check( $request ){
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * Verifying JSON token with parent permission 
     * Basically this function pass the parent permission Only after JSON token verified 
     * @param WP_REST_Request $request | REst request
     * @param string  $method name | Method name
     * @return boolean or WP_Error
     * @since 3.0.0
     * * */
    private function check_with_parent_permission( WP_REST_Request $request, string $function_name ){
        $token = $request->get_header( 'authorization' );
        $verification_result = false;
        $bool = false;
        if ( !is_null( $token ) ) {
            $verification_result = new RP_JWT_Verifier( $request );
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_token_is_null_error_message', __( "Token is null!!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }

        if ( (true === $verification_result) xor (!is_wp_error( $verification_result->result ) ) ) {
            $bool = parent::$function_name( $request );
        } else {
            return $verification_result->result;
        }

        return $bool;
    }

    public function dump_data( $param ) {
        echo "<pre>";
        print_r( $param );
        echo "</pre>";
    }

}
