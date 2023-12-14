<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;

class RP_REST_Terms_Controller extends WP_REST_Terms_Controller {

    public function __construct( $taxonomy ) {
        parent::__construct( $taxonomy );
    }

    /**
     * get terms permission | Get 10 terms at a time
     * @param WP_REST_Request $request
     * @return WP_Error | bool
     * @since 3.0.0
     * * */
    public function get_items_permissions_check( $request ) {
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * get term permission | Get single terms at a time
     * @param WP_REST_Request $request
     * @return WP_Error | bool
     * @since 3.0.0
     * * */
    public function get_item_permissions_check( $request ) {
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * Create term permission 
     * @param WP_REST_Request $request
     * @since 3.0.0
     * @return bool| WP_Error
     * * */
    public function create_item_permissions_check( $request ) {
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * Update term permission 
     * @param WP_REST_Request $request
     * @since 3.0.0
     * @return bool| WP_Error
     * * */
    public function update_item_permissions_check( $request ) {
        return $this->check_with_parent_permission( $request, __FUNCTION__ );
    }

    /**
     * Delete term permission 
     * @param WP_REST_Request $request
     * @since 3.0.0
     * @return bool| WP_Error
     * * */
    public function delete_item_permissions_check( $request ) {
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

}
