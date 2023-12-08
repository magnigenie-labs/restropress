<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Restropress\RestApi\Utilities;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use WP_REST_Request;
use WP_Error;
use stdClass;
use WP_User;

/**
 * Description of RP_JWT_Verifier
 *
 * @author dibya
 */
class RP_JWT_Verifier {

    public $result;

    public function __construct( WP_REST_Request $request ) {
        $this->result = $this->incoming_token_verify( $request );
    }

    /**
     * JSON token verifying 
     * To overcome from complexity of nesting condition and repetition  
     * Basically this method for verifying incoming token
     * @return boolean or WP_Error 
     * @since 3.0.0
     * * */
    public function incoming_token_verify( WP_REST_Request $request ): bool|WP_Error {
        $token = $request->get_header( 'authorization' );
        if ( !is_null( $token ) && !empty( $token ) ) {
            if ( is_string( $token ) ) {
                return $this->jwt_verify( $request );
            } else {
                return new WP_Error(
                        'rest_forbidden',
                        apply_filters( 'rp_api_token_not_string_error_message', __( "Token is not a string!!!", 'restropress' ) ),
                        array( 'status' => rest_authorization_required_code() )
                );
            }
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_token_is_null_error_message', __( "Token is null!!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }
    }

    /**
     * Private method to check and authentication JSON token 
     * Basically this method for checking token with JWT Library 
     * @param string  $token | Incoming token 
     * @throws DomainException
     * @return boolean| WP_error
     * * */
    public function jwt_verify( WP_REST_Request $request ): bool|WP_Error {
        $token = $request->get_header( 'authorization' );
        $api_key = $request->get_header( 'x-api-key' );
        if ( !is_null( $token ) && preg_match( '/Bearer\s(\S+)/', $token, $matches ) && is_string( $api_key ) ) {

            try {
                $decoded = JWT::decode( $matches[ 1 ], new Key( $api_key, 'HS512' ) );
                return $this->checking_decoded_data( $decoded, $api_key );
            } catch ( Exception $exc ) {
                $error = $exc->getMessage();
                return new WP_Error(
                        'rest_forbidden',
                        apply_filters( 'rp_api_not_valid_error_message', __( $error, 'restropress' ) ),
                        array( 'status' => rest_authorization_required_code() )
                );
            }
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_token_not_found_error_message', __( "Token  OR API key not found in request!!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }
    }

    /**
     * Checking decoded data
     * @param stdClass $decoded
     * @return  boolean| WP_error
     * @since 3.0.0 
     * * */
    public function checking_decoded_data( stdClass $decoded, string $api_key ): bool|WP_Error {
        if ( is_object( $decoded ) && !empty( ( array ) $decoded ) && isset( $decoded->data->user_id ) && !empty( $decoded->data->user_id ) ) {
            return $this->checking_user_id( ( int ) $decoded->data->user_id, $api_key );
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_not_valid_error_message', __( "Decoded data is not valid !!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }
    }

    /**
     * Checking user id and private data
     * @param int $user_id | Description
     * @return boolean| WP_error
     * @since 3.0.0
     * * */
    public function checking_user_id( int $user_ID, string $api_key ): bool|WP_Error {
        $private_key = get_user_meta( $user_ID, "_rp_api_user_private_key", true );
        $public_key = get_user_meta( $user_ID, "_rp_api_user_public_key", true );
        if ( hash_equals( $api_key, $public_key ) ) {
            if ( password_verify( $user_ID, $private_key ) ) {
                if ( !empty( get_current_user_id() ) && get_current_user_id() == $user_ID ) {
                    return true;
                } else {
                    return $this->checking_user( $user_ID );
                }
            } else {
                return new WP_Error(
                        'rest_forbidden',
                        apply_filters( 'rp_api_not_valid_error_message', __( "Private key is not valid !!!", 'restropress' ) ),
                        array( 'status' => rest_authorization_required_code() )
                );
            }
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_not_valid_error_message', __( "May be token or public key got expired !!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }
    }

    /**
     * Checking User 
     * @param int $user_ID 
     * @return bool| WP_Error | Description
     * @since 3.0.0
     * * */
    public function checking_user( int $user_ID ): bool|WP_Error {
        $user = get_user_by( 'id', $user_ID );
        if ( $user instanceof WP_User ) {
            wp_set_current_user( $user->ID, $user->user_login );
            wp_set_auth_cookie( $user->ID );
            return true;
        } else {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_not_valid_error_message', __( "User is not valid !!!", 'restropress' ) ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }
    }

}
