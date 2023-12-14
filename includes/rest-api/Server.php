<?php

/**
 * Initialize this version of the REST API.
 *
 * @package Restropress\RestApi
 */

namespace Restropress\RestApi;

include RP_PLUGIN_DIR . 'includes/rest-api/Utilities/SingletonTrait.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Utilities/RP_JWT_Verifier.php';
//include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/class-rp-rest-system-status-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-posts-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-terms-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-foods-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-auth-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-orders-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-cart-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-customer-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-food-categories-v1-controller.php';
include RP_PLUGIN_DIR . 'includes/rest-api/Controllers/Version1/class-rp-rest-food-addons-v1-controller.php';


defined( 'ABSPATH' ) || exit;

use Restropress\RestApi\Utilities\SingletonTrait;

/**
 * Class responsible for loading the REST API and all REST API namespaces.
 */
class Server {

    use SingletonTrait;

    /**
     * REST API namespaces and endpoints.
     *
     * @var array
     */
    protected $controllers = array();

    /**
     * Hook into WordPress ready to init the REST API as needed.
     */
    public function init() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
        add_action( 'rest_api_init', array( $this, 'register_user_meta_for_rest' ),10 );
    }

/**
     * Register User meta for getting token and key from rest api.
     */
    function register_user_meta_for_rest() {
		register_meta(
			'user',
			'_rp_api_user_private_key',
			array(
				'show_in_rest'      => true,
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
               
			)
		);
		register_meta(
			'user',
			'_rp_api_user_public_key',
			array(
				'show_in_rest'      => true,
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_meta(
			'user',
			'_rp_api_user_token_key',
			array(
				'show_in_rest'      => true,
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
	}
    /**
     * Register REST API routes.
     */
    public function register_rest_routes() {
        foreach ( $this->get_rest_namespaces() as $namespace => $controllers ) {
            foreach ( $controllers as $controller_name => $controller_class ) {
                $this->controllers[ $namespace ][ $controller_name ] = new $controller_class();
                $this->controllers[ $namespace ][ $controller_name ]->register_routes();
            }
        }
    }

    /**
     * Get API namespaces - new namespaces should be registered here.
     *
     * @return array List of Namespaces and Main controller classes.
     */
    protected function get_rest_namespaces() {
        return apply_filters(
                'restropress_rest_api_get_rest_namespaces',
                array(
                    'rp/v1' => $this->get_v1_controllers(),
                )
        );
    }

    /**
     * List of controllers in the wc/v1 namespace.
     *
     * @return array
     */
    protected function get_v1_controllers() {
        return array(
            'auth' => 'RP_REST_Auth_V1_Controller',
            'foods' => 'RP_REST_Foods_V1_Controller',
            'orders' => 'RP_REST_Orders_V1_Controller',
            'cart' => 'RP_REST_Cart_V1_Controller',
            'customer' => 'RP_REST_Customer_V1_Controller',
            "fooditem/categories" => "RP_REST_Food_Categories_V1_Controller",
            "fooditem/addons" => "RP_REST_Food_Addons_V1_Controller"
        );
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_path() {
        return dirname( __DIR__ );
    }

}
