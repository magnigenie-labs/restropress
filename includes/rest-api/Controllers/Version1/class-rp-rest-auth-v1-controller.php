<?php

use Firebase\JWT\JWT;
use WP_REST_Response as response;

/**
 * Description of RP_REST_Auth_V1_Controller
 *
 * @author Magnigeeks <info@magnigeeks.com>
 */
class RP_REST_Auth_V1_Controller {

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
    protected $rest_base = 'auth';
    
    
    /**
     * Response.
     *
     * @var string
     */
    protected $response;
    
    /**
     * API key
     * @var string
     * **/
   protected  $api_key = "";
    public function __construct() {
        $this->response = new response();
    }

    /**
     * Register the routes for foods.
     */
    public function register_routes() {
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array( $this, 'get_token' ),
                        'permission_callback' => array( $this, 'get_auth_permissions_check' ),
                        'args' => [],
                    ),
                )
        );
    }

    /**
     * 
     * * */

    /**
     * Callback of generating token
     * 
     * @access public
     * @return WP_REST_Response  | Response object
     * @throws InvalidArgumentException
     * @throws DomainException
     * @throws BeforeValidException
     * @throws UnexpectedValueException 
     * @throws Exception
     * @since 3.0.0
     * * */
    public function get_token( WP_REST_Request $request ): WP_REST_Response {
        
        $api_key = $request->get_header( 'x-api-key' );
        //Initialize Expire 
        $expire = null;
        //Generating unique token id
        $token_id = base64_encode( random_bytes( 16 ) );
        //Get DateTimeImmutable Object for further use at Issuer time and not before 
        $obj = new DateTimeImmutable();
        //Set expire time limit if it set at admin 
        if ( !empty( rpress_get_option( 'api_expire' ) ) ) {
            $exp = rpress_get_option( 'api_expire' );
            $expire = $obj->modify( '+' . $exp )->getTimestamp();      // Add expire time limit 
        }
        //Initialize server name furether use for Issuer 
        $server_name = $_SERVER[ 'SERVER_NAME' ];
        // Create the token as an array
        $data = [
            'iat' => $obj->getTimestamp(), // Issued at: time when the token was generated
            'jti' => $token_id, // Json Token Id: an unique identifier for the token
            'iss' => $server_name, // Issuer
            'aud' => $server_name, // Audience
            'nbf' => $obj->getTimestamp(), // Not before
            'data' => [
                'api_key' => $this->api_key, // API key
                "user_id" => $request->get_header( 'x-user-id')
            ]
        ];

        // Adding Expire time limit 
        if ( !is_null( $expire ) ) {
            $data[ 'exp' ] = $expire;
        }

        $user_id = null;
        if ( !is_null( $request->get_param( 'user_id' ) ) ) {
            $user_id = $request->get_param( 'user_id' );
            $data[ 'data' ][ 'user_id' ] = $user_id;
        }

        //Applying a filter for future adding data to token create array
        $data = apply_filters( 'rp_api_token_generate_data', $data );
        //Adding Links 
        $this->response->add_links( $this->prepare_links() );

        //Checking data is array type
        if ( !is_array( $data ) ) {
            $this->response->set_status( 401 );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( 'Data should be an array', 'restropress' ) ) ] );
            return $this->response;
        }

        //Get token on try catch block 
        try {
            // Encode the array to a JWT string.
            $token = JWT::encode( $data, $this->api_key, 'HS512' );
            $this->response->set_status( 200 );
            $this->response->set_data( [ 'token' => $token ] );
        } catch ( InvalidArgumentException $exc ) {
            $error = $exc->getMessage();
            $this->response->set_status( 401 );
            $this->response->add_headers( [ 'X-WP-RP-error' => $error ] );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( $error, 'restropress' ) ) ] );
        } catch ( DomainException $exc ) {
            $error = $exc->getMessage();
            $this->response->set_status( 401 );
            $this->response->add_headers( [ 'X-WP-RP-error' => $error ] );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( $error, 'restropress' ) ) ] );
        } catch ( BeforeValidException $exc ) {
            $error = $exc->getMessage();
            $this->response->set_status( 401 );
            $this->response->add_headers( [ 'X-WP-RP-error' => $error ] );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( $error, 'restropress' ) ) ] );
        } catch ( UnexpectedValueException $exc ) {
            $error = $exc->getMessage();
            $this->response->set_status( 401 );
            $this->response->add_headers( [ 'X-WP-RP-error' => $error ] );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( $error, 'restropress' ) ) ] );
        } catch ( Exception $exc ) {
            $error = $exc->getMessage();
            $this->response->set_status( 401 );
            $this->response->add_headers( [ 'X-WP-RP-error' => $error ] );
            $this->response->set_data( [ 'message' => apply_filters( 'rp_api_token_generate_error_message', __( $error, 'restropress' ) ) ] );
        }

        //Return response;
        return $this->response;
    }

    /**
     * protected method for preparing Links
     * @return array | array of Links
     * @since 3.0.0
     * @access protected
     * * */
    protected function prepare_links(): array {
        $links = array(
            'self' => array(
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
            ),
            'collection' => array(
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
            ),
        );

        return $links;
    }

    /**
     * Checking Permission before generating Token
     * 
     * @param WP_REST_Request $request  Rest request Object
     * @return bool|WP_Error permission return can be Boolean or WP_Error
     * @access public
     * @since 3.0.0
     * * */
    public function get_auth_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        
        //Initialize Api_key 
        $api_key = null;
        //Initialize enable status
        $is_api_enabled = null;

        //Getting API key from  header
        $api_key = $request->get_header( 'authorization' );
        //Get API enable status
        $is_api_enabled = rpress_get_option( 'activate_api' );

        //Check if API not enabled | in true case return appropriate error message
        if ( !$is_api_enabled ) {
            return new WP_Error(
                    'rest_forbidden',
                    apply_filters( 'rp_api_not_enabled_error_message', __( 'API has not enabled!!!.', 'restropress' ), $request ),
                    array( 'status' => rest_authorization_required_code() )
            );
        }else{
            $this->api_key = $api_key;
            return true ;
        }

        //Check for valid API key | in true case return true | in false case return appropriate error message
//        if ( !empty( $api_key ) && $api_key === $this->api_key ) {
//            return true;
//        } else {
//            return new WP_Error(
//                    'rest_forbidden',
//                    apply_filters( 'rp_api_key_not_valid_error_message', __( 'Not a valid API key !!!.', 'restropress' ), $request ),
//                    array( 'status' => rest_authorization_required_code() )
//            );
//        }

        return false;
    }

}
