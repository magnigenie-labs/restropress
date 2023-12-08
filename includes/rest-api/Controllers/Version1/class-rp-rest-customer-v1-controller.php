<?php

use Restropress\RestApi\Utilities\RP_JWT_Verifier;

/**
 * Description of class-rp-rest-customer-v1-controller
 *
 * @author magnigeeks <info@magnigeeks.com>
 */
class RP_REST_Customer_v1_Controller extends WP_REST_Controller {

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
    protected $rest_base = 'customers';

    public function __construct() {
        
    }

    /**
     * Registering 
     * * */
    public function register_routes() {
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base,
                array(
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array( $this, 'get_customers' ),
                        'permission_callback' => array( $this, 'get_customer_permissions_check' ),
                        'args' => $this->get_collection_params(),
                    ),
                    array(
                        'methods' => WP_REST_Server::CREATABLE,
                        'callback' => array( $this, 'add_customer' ),
                        'permission_callback' => array( $this, 'add_customer_permissions_check' ),
                        'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)',
                array(
                    'args' => array(
                        'id' => array(
                            'description' => __( 'Unique identifier for the post.' ),
                            'type' => 'integer',
                        ),
                    ),
                    array(
                        'methods' => WP_REST_Server::READABLE,
                        'callback' => array( $this, 'get_customer' ),
                        'permission_callback' => array( $this, 'get_customer_permissions_check' ),
                        'args' => $this->get_collection_params(),
                    ),
                    array(
                        'methods' => WP_REST_Server::EDITABLE,
                        'callback' => array( $this, 'update_customer' ),
                        'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                        'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                    ),
                    array(
                        'methods' => WP_REST_Server::DELETABLE,
                        'callback' => array( $this, 'delete_customer' ),
                        'permission_callback' => array( $this, 'delete_customer_permissions_check' ),
                        'args' => array(
                            'force' => array(
                                'type' => 'boolean',
                                'default' => false,
                                'description' => __( 'Whether to bypass Trash and force deletion.' ),
                            ),
                        ),
                    ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/add-emails',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'add_emails' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/remove-emails',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'remove_emails' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/set-primary-email',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'set_primary_email' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/attach-payment',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'attach_payment' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/remove-payment',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'remove_payment' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/add-note',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'add_note' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/add-meta-data',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'add_meta' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/update-meta-data',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'update_meta' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );

        register_rest_route(
                $this->namespace,
                '/' . $this->rest_base . '/(?P<id>[\d]+)/delete-meta-data',
                array(
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => array( $this, 'delete_meta' ),
                    'permission_callback' => array( $this, 'update_customer_permissions_check' ),
                    'args' => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                )
        );
    }

    /**
     * Remove metadata matching criteria from a customer.
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function delete_meta( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "meta_key" ) && property_exists( $prepare_customer_data, "meta_value" ) ) {
                if ( is_string( $prepare_customer_data->meta_key ) ) {
                    $is_updated = $custmer_object->delete_meta( $prepare_customer_data->meta_key, $prepare_customer_data->meta_value );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Update customer meta field based on customer ID.
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function update_meta( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "meta_key" ) && property_exists( $prepare_customer_data, "meta_value" ) ) {
                if ( is_string( $prepare_customer_data->meta_key ) ) {
                    $is_updated = $custmer_object->update_meta( $prepare_customer_data->meta_key, $prepare_customer_data->meta_value );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Add meta data field to a customer.
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function add_meta( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "meta_key" ) && property_exists( $prepare_customer_data, "meta_value" ) ) {
                if ( is_string( $prepare_customer_data->meta_key ) ) {
                    $is_updated = $custmer_object->add_meta( $prepare_customer_data->meta_key, $prepare_customer_data->meta_value );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Add a note for the customer
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function add_note( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "notes" ) ) {
                if ( is_string( $prepare_customer_data->notes ) ) {
                    $is_updated = $custmer_object->add_note( $prepare_customer_data->notes );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Attach payment to the customer
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function remove_payment( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "payment_id" ) ) {
                if ( is_integer( $prepare_customer_data->payment_id ) ) {
                    $is_updated = $custmer_object->remove_payment( $prepare_customer_data->payment_id );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Attach payment to the customer
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function attach_payment( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "payment_id" ) ) {
                if ( is_integer( $prepare_customer_data->payment_id ) ) {
                    $is_updated = $custmer_object->attach_payment( $prepare_customer_data->payment_id );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Set an email address as the customer's primary email
     * This will move the customer's previous primary email to an additional email
     * @param WP_REST_Request $request
     * @return WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function set_primary_email( WP_REST_Request $request ): WP_REST_Response {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "email" ) ) {
                if ( is_email( $prepare_customer_data->email ) ) {
                    $is_updated = $custmer_object->set_primary_email( $prepare_customer_data->email );
                    if ( $is_updated ) {
                        return $this->get_customer( $request );
                    }
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Removing Email/Emails from customer
     * @param WP_REST_Request $request
     * @param WP_REST_Response $response 
     * * */
    public function remove_emails( WP_REST_Request $request ) {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( $prepare_customer_data->ID ) {
            $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
            if ( property_exists( $prepare_customer_data, "emails" ) ) {
                if ( is_array( $prepare_customer_data->emails ) ) {
                    for ( $i = 0; $i < count( $prepare_customer_data->emails ); $i++ ) {
                        $custmer_object->add_email( $prepare_customer_data->emails[ $i ] );
                    }
                    return $this->get_customer( $request );
                }
            }
        }
        return $this->get_customer( $request );
    }

    /**
     * Adding Email/Emails to customer 
     * @param WP_REST_Request $request 
     * @return  WP_REST_Response  $response
     * @since 3.0.0
     * ** */
    public function add_emails( WP_REST_Request $request ): WP_REST_Response {

        $prepare_customer_data = $this->prepare_item_for_database( $request );
        $customer_table = new RPRESS_DB_Customers();
        if ( property_exists( $prepare_customer_data, "ID" ) ) {
            $is_exist = $customer_table->exists( $prepare_customer_data->ID, "id" );
            if ( $is_exist ) {
                $custmer_object = new RPRESS_Customer( $prepare_customer_data->ID );
                if ( property_exists( $prepare_customer_data, "emails" ) ) {
                    if ( is_array( $prepare_customer_data->emails ) ) {
                        for ( $i = 0; $i < count( $prepare_customer_data->emails ); $i++ ) {
                            $custmer_object->add_email( $prepare_customer_data->emails[ $i ] );
                        }
                        return $this->get_customer( $request );
                    }
                } else {
                    $response = new WP_REST_Response();
                    $response->set_data( array( "message" => __( "Please check emails you are providing", "Restropress" ) ) );
                    $response->set_status( 401 );
                    return $response;
                }
            } else {
                $response = new WP_REST_Response();
                $response->set_data( array( "message" => __( "Please check ID you are providing", "Restropress" ) ) );
                $response->set_status( 401 );
                return $response;
            }
        }
        $response = new WP_REST_Response();
        $response->set_data( array( "message" => __( "Please check ID you are providing", "Restropress" ) ) );
        $response->set_status( 401 );
        return $response;
    }

    /**
     * Deleting Customer By ID
     * @param WP_REST_Request $request
     * @since 3.0.0
     * * */
    public function delete_customer( WP_REST_Request $request ): WP_REST_Response {
        $cutomer_id = $request[ 'id' ];
        $custmer_object = new RPRESS_Customer( $cutomer_id );
        $response = new WP_REST_Response();
        if ( !empty( $custmer_object->id ) ) {
            $data = $this->prepare_item_for_response( $custmer_object, $request );
            $response_collection_data = $this->prepare_response_for_collection( $data );
            $customer_db = new RPRESS_DB_Customers();
            $is_deleted = $customer_db->delete( $cutomer_id );

            $response->set_data( array( "deleted" => $is_deleted, "previous" => $response_collection_data ) );
            return $response;
        }
        $response->set_data( array( "deleted" => false, "message" => __( "Please check ID you are providing", "Restropress" ) ) );
        $response->set_status( 401 );
        return $response;
    }

    /**
     * Update Customer By ID
     * @param WP_REST_Request $request
     * @return  WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function update_customer( WP_REST_Request $request ) {
        $prepare_customer_data = $this->prepare_item_for_database( $request );
        if ( property_exists( $prepare_customer_data, "ID" ) ) {
            $customer_table = new RPRESS_DB_Customers();
            $is_exist = $customer_table->exists( $prepare_customer_data->ID, "id" );
            if ( $is_exist ) {
                $to_update_data = [];
                if ( property_exists( $prepare_customer_data, "name" ) ) {
                    $to_update_data[ 'name' ] = $prepare_customer_data->name;
                }
                if ( property_exists( $prepare_customer_data, "email" ) ) {
                    $to_update_data[ 'email' ] = $prepare_customer_data->email;
                }
                if ( property_exists( $prepare_customer_data, "user_id" ) ) {
                    $to_update_data[ 'user_id' ] = $prepare_customer_data->user_id;
                }
                if ( property_exists( $prepare_customer_data, "payment_ids" ) ) {
                    $to_update_data[ 'payment_ids' ] = $prepare_customer_data->payment_ids;
                }
                if ( property_exists( $prepare_customer_data, "purchase_count" ) ) {
                    $to_update_data[ 'purchase_count' ] = $prepare_customer_data->purchase_count;
                }
                if ( property_exists( $prepare_customer_data, "purchase_value" ) ) {
                    $to_update_data[ 'purchase_value' ] = $prepare_customer_data->purchase_value;
                }
                $custmer_object = new RPRESS_Customer();
                $is_updated = $custmer_object->update( $data_to_update );
                if ( $is_updated ) {
                    return $this->get_customer( $request );
                } else {
                    return $this->get_customer( $request );
                }
            }
            $response = new WP_REST_Response();
            $response->set_data( array( "message" => __( "Please check ID you are providing", "Restropress" ) ) );
            $response->set_status( 401 );
            return $response;
        }
        $response = new WP_REST_Response();
        $response->set_data( array( "message" => __( "Please check ID you are providing", "Restropress" ) ) );
        $response->set_status( 401 );
        return $response;
    }

    /**
     * Get Customer by ID
     * @param WP_REST_Request $request
     * @return WP_REST_Response $response
     * @since 3.0.0
     * * */
    public function get_customer( WP_REST_Request $request ): WP_REST_Response {
        $cutomer_id = $request[ 'id' ];
        $customer_table = new RPRESS_DB_Customers();
        $is_exist = $customer_table->exists( $cutomer_id, "id" );
        if ( $is_exist ) {
            $customer = $customer_table->get_customer_by( "id", $cutomer_id );
            $data = $this->prepare_item_for_response( $customer, $request );
            $response_collection_data = $this->prepare_response_for_collection( $data );
            $response = rest_ensure_response( $response_collection_data );
            return $response;
        }
        $response = new WP_REST_Response();
        $response->set_data( array( "message" => __( "Please check ID you are providing", "Restropress" ) ) );
        $response->set_status( 401 );
        return $response;
    }

    /**
     * Adding customers
     * @param WP_REST_Request $request
     * @since 3.0.0
     * @return WP_REST_Response 
     * * */
    public function add_customer( WP_REST_Request $request ): WP_REST_Response {

        $prepare_customer_data = $this->prepare_item_for_database( $request );
        $to_add_data = [];
        if ( property_exists( $prepare_customer_data, "name" ) ) {
            $to_add_data[ 'name' ] = $prepare_customer_data->name;
        }
        if ( property_exists( $prepare_customer_data, "email" ) ) {
            $to_add_data[ 'email' ] = $prepare_customer_data->email;
        }
        if ( property_exists( $prepare_customer_data, "user_id" ) ) {
            $to_add_data[ 'user_id' ] = $prepare_customer_data->user_id;
        }
        if ( property_exists( $prepare_customer_data, "payment_ids" ) ) {
            $to_add_data[ 'payment_ids' ] = $prepare_customer_data->payment_ids;
        }
        if ( property_exists( $prepare_customer_data, "purchase_count" ) ) {
            $to_add_data[ 'purchase_count' ] = $prepare_customer_data->purchase_count;
        }
        if ( property_exists( $prepare_customer_data, "purchase_value" ) ) {
            $to_add_data[ 'purchase_value' ] = $prepare_customer_data->purchase_value;
        }
        $custmer_object = new RPRESS_Customer();
        $created_id = $custmer_object->create( $to_add_data );
        if ( $created_id ) {
            $request->set_body_params( [ "id" => $created_id ] );
            return $this->get_customer( $request );
        }
        $response = new WP_REST_Response();
        $response->set_data( array( "message" => __( "Something wrong happen please try again", "Restropress" ) ) );
        $response->set_status( 401 );
        return $response;
    }

    /**
     * Query for Customer (Search, Meta Query etc.)
     * @return array
     * @since 3.0.0
     * * */
    public function get_collection_params(): array {
        $query_params = parent::get_collection_params();
        $query_params[ 'order' ] = array(
            'description' => __( 'Order of the collection.' ),
            'type' => 'string',
            'default' => "DESC",
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'orderby' ] = array(
            'description' => __( 'Orderby of the collection.' ),
            'type' => 'string',
            'default' => "id",
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'email' ] = array(
            'description' => __( 'Search by email of the cutomer table.' ),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'id' ] = array(
            'description' => __( 'Search by id of the cutomer table.' ),
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'user_id' ] = array(
            'description' => __( 'Search by user_id of the cutomer table.' ),
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'name' ] = array(
            'description' => __( 'Search by name of the cutomer table.' ),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'meta_key' ] = array(
            'description' => __( 'Search by meta_key of the cutomer table.' ),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'meta_value' ] = array(
            'description' => __( 'Search by meta_key of the cutomer table.' ),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'include' ] = array(
            'description' => __( 'Limit result set to specific IDs.' ),
            'type' => 'array',
            'items' => array(
                'type' => 'integer',
            ),
            'default' => array(),
        );
        $query_params[ 'exclude' ] = array(
            'description' => __( 'Ensure result set excludes specific IDs.' ),
            'type' => 'array',
            'items' => array(
                'type' => 'integer',
            ),
            'default' => array(),
        );
        $query_params[ 'users_include' ] = array(
            'description' => __( 'Limit result set to specific user IDs.' ),
            'type' => 'array',
            'items' => array(
                'type' => 'integer',
            ),
            'default' => array(),
        );
        $query_params[ 'users_exclude' ] = array(
            'description' => __( 'Ensure result set excludes specific User IDs.' ),
            'type' => 'array',
            'items' => array(
                'type' => 'integer',
            ),
            'default' => array(),
        );
        $query_params[ 'meta_query' ] = array(
            'description' => __( 'Search by meta_value of the cutomer table.' ),
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
        );
        $query_params[ 'date' ] = array(
            'description' => __( 'Search by meta_value of the cutomer table.' ),
            'type' => [ 'string', 'ojcet' ],
            'properties' => array(
                "start" => array(
                    'description' => __( 'Start date' ),
                    'type' => 'string',
                ),
                "end" => array(
                    'description' => __( 'end date', "restropress" ),
                    'type' => 'string',
                )
            )
        );
        return $query_params;
    }

    /**
     * Customer Schema
     * @return array 
     * @since 3.0.0
     * * */
    public function get_item_schema(): array {
        if ( $this->schema ) {
            return $this->add_additional_fields_schema( $this->schema );
        }
        parent::get_item_schema();
        $schema = array(
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => "customer",
            'type' => 'object',
            "properties" => array(
                'id' => array(
                    'description' => __( "Unique id of customer table", "restropress" ),
                    'type' => "integer",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "user_id" => array(
                    'description' => __( "user id", "restropress" ),
                    'type' => "integer",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "email" => array(
                    'description' => __( "Email of customer", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "emails" => array(
                    'description' => __( "Email of customer", "restropress" ),
                    'type' => "array",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                    "items" => array(
                        'description' => __( "Email of customer", "restropress" ),
                        'type' => "string",
                        'context' => array( 'view', 'edit', 'embed' ),
                        'readonly' => true,
                    )
                ),
                "name" => array(
                    'description' => __( "Name of customer", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "notes" => array(
                    'description' => __( "Name of customer", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "purchase_value" => array(
                    'description' => __( "Total purchase of food item ", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "purchase_count" => array(
                    'description' => __( "Total purchase  count of food item", "restropress" ),
                    'type' => "integer",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "payment_ids" => array(
                    'description' => __( "Order Idies ", "restropress" ),
                    'type' => "array",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                    "items" => array(
                        'description' => __( "Order ID ", "restropress" ),
                        'type' => "string",
                        'context' => array( 'view', 'edit', 'embed' ),
                        'readonly' => true,
                    )
                ),
                "payment_id" => array(
                    'description' => __( "Order ID", "restropress" ),
                    'type' => "integer",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "meta_key" => array(
                    'description' => __( "Order ID", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "meta_value" => array(
                    'description' => __( "Order ID", "restropress" ),
                    'type' => [ "string", "array", "integer" ],
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "meta_data" => array(
                    'description' => __( "All metadata", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "date_created" => array(
                    'description' => __( "Created date of customer", "restropress" ),
                    'type' => "string",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                ),
                "payments" => array(
                    'description' => __( "Order Idies ", "restropress" ),
                    'type' => "array",
                    'context' => array( 'view', 'edit', 'embed' ),
                    'readonly' => true,
                    "items" => array(
                        'description' => __( "Order ID ", "restropress" ),
                        'type' => "object",
                        'context' => array( 'view', 'edit', 'embed' ),
                        'readonly' => true,
                        "properties" => array(
                            "id" => array(
                                'description' => __( "Order ID ", "restropress" ),
                                'type' => "integer",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "payment_meta" => array(
                                'description' => __( "Payment meta", "restropress" ),
                                'type' => "object",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                                "properties" => array(
                                    "phone" => array(
                                        'description' => __( "Phone number ", "restropress" ),
                                        'type' => "string",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "key" => array(
                                        'description' => __( "Purchase Key ", "restropress" ),
                                        'type' => "string",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "email" => array(
                                        'description' => __( "Email at purchasing ", "restropress" ),
                                        'type' => "string",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                    ),
                                    "date" => array(
                                        'description' => __( "date of purchasing ", "restropress" ),
                                        'type' => "string",
                                        'context' => array( 'view', 'edit', 'embed' ),
                                        'readonly' => true,
                                        "format" => "date"
                                    ),
                                )
                            ),
                            "total" => array(
                                'description' => __( "Total ", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "subtotal" => array(
                                'description' => __( "Sub Total ", "restropress" ),
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
                            "discounted_amount" => array(
                                'description' => __( "Discount Amount", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "tax_rate" => array(
                                'description' => __( "Tax Rate", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "fees" => array(
                                'description' => __( "Fees", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "fees_total" => array(
                                'description' => __( "Fees Total", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            ),
                            "discounts" => array(
                                'description' => __( "Discounts", "restropress" ),
                                'type' => "string",
                                'context' => array( 'view', 'edit', 'embed' ),
                                'readonly' => true,
                            )
                        )
                    )
                ),
            )
        );
        $schema = apply_filters( "rest_rp_customer_item_schema", $schema );

        $this->schema = $schema;

        return $this->add_additional_fields_schema( $this->schema );
    }

    /**
     * Getting customers
     * @param WP_REST_Request $request
     * @since 3.0.0
     * @return WP_REST_Response 
     * * */
    public function get_customers( WP_REST_Request $request ): WP_REST_Response {

        $query_args = $this->prepare_customer_query( $request );
        $customer_table = new RPRESS_DB_Customers();
        $customers = $customer_table->get_customers( $query_args );
        $posts = array();
        if ( is_array( $customers ) && count( $customers ) > 0 ) {
            for ( $i = 0; $i < count( $customers ); $i++ ) {
                $data = $this->prepare_item_for_response( $customers[ $i ], $request );
                $posts[] = $this->prepare_response_for_collection( $data );
            }
        }
        $response = rest_ensure_response( $posts );
        return $response;
    }

    /**
     * Preparing for Customer Query 
     * @param  WP_REST_Request $request
     * @since  3.0.0
     * @return arrya $query 
     * * */
    public function prepare_customer_query( WP_REST_Request $request ): array {
        $registarted = $this->get_collection_params();
        $params = $request->get_params();
        $parameter_mappings = array(
            'per_page' => 'number',
            'page' => 'page',
            'order' => 'order',
            'orderby' => 'orderby',
            "email" => "email",
            "id" => "id",
            "user_id" => "user_id",
            "name" => "name",
            "meta_key" => "meta_key",
            "meta_value" => "meta_value",
            "date" => "date",
            "users_exclude" => "users_exclude",
            "users_include" => "users_include",
            "exclude" => "exclude",
            "include" => "include",
            "search" => "search"
        );
        $arg = [];
        foreach ( $parameter_mappings as $api_param => $customer_param ) {
            if ( isset( $registarted[ $api_param ], $params[ $api_param ] ) ) {
                $arg[ $customer_param ] = $params[ $api_param ];
            }
        }
        $arg[ "offset" ] = $arg[ "number" ] * ($arg[ "page" ] - 1);
        unset( $arg[ "page" ] );
        return $arg;
    }

    /**
     * Permission checking for get request
     * @param WP_REST_Request $request 
     * @since 3.0.0
     * @return bool | WP_Error 
     * * */
    public function get_customer_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        $object = new RP_JWT_Verifier( $request );
        return $object->result;
    }

    /**
     * Permission Checking for POST request 
     * @param  WP_REST_Request $request Description
     * @since 3.0.0
     * @return bool | WP_Error 
     * * */
    public function add_customer_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        $varifier_object = new RP_JWT_Verifier( $request );
        return $varifier_object->result;
    }

    /**
     * Permission Checking for PUT Request
     * @param WP_REST_Request $request 
     * @return bool | WP_Error 
     * @since 3.0.0
     * * */
    public function update_customer_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        $varifier_object = new RP_JWT_Verifier( $request );
        return $varifier_object->result;
    }

    /**
     * Permission Checking for DELETE Request
     * @param WP_REST_Request $request 
     * @return bool | WP_Error 
     * @since 3.0.0
     * * */
    public function delete_customer_permissions_check( WP_REST_Request $request ): bool|WP_Error {
        $varifier_object = new RP_JWT_Verifier( $request );
        return $varifier_object->result;
    }

    /**
     * Over riding prepare_item_for_response
     * @param RPRESS_Customer $customer 
     * @param WP_REST_Request $request 
     * @return WP_REST_Response 
     * @since 3.0.0
     * * */
    public function prepare_item_for_response( $customer_table, $request ): WP_REST_Response {
        $fields = $this->get_fields_for_response( $request );
        $customer = new RPRESS_Customer( $customer_table->id );
        $data = array();
        if ( rest_is_field_included( 'id', $fields ) ) {
            $data[ "ID" ] = $customer->id;
        }
        if ( rest_is_field_included( 'user_id', $fields ) ) {
            $data[ "user_id" ] = $customer->user_id;
        }



        if ( rest_is_field_included( 'payment_ids', $fields ) ) {
            $data[ "payment_ids" ] = $customer->get_payment_ids();
        }

        if ( rest_is_field_included( 'emails', $fields ) ) {
            $data[ "emails" ] = $customer->emails;
        }

        if ( rest_is_field_included( 'email', $fields ) ) {
            $data[ "email" ] = $customer->email;
        }

        if ( rest_is_field_included( 'name', $fields ) ) {
            $data[ "name" ] = $customer->name;
        }

        if ( rest_is_field_included( 'purchase_value', $fields ) ) {
            $data[ "purchase_value" ] = $customer->purchase_value;
        }

        if ( rest_is_field_included( 'purchase_count', $fields ) ) {
            $data[ "purchase_count" ] = $customer->purchase_count;
        }

        if ( rest_is_field_included( 'notes', $fields ) ) {
            $data[ "notes" ] = $customer->notes;
        }

        if ( rest_is_field_included( 'meta_data', $fields ) ) {
            $data[ "meta_data" ] = $customer->get_meta();
        }

        if ( rest_is_field_included( 'date_created', $fields ) ) {
            $data[ "date_created" ] = $customer_table->date_created;
        }


        // if ( rest_is_field_included( 'payments', $fields ) ) {
        $payments = $customer->get_payments();
        $schema = $this->get_item_schema();
        $payments_field = array_keys( $schema[ 'properties' ][ 'payments' ][ 'items' ][ 'properties' ] );
        $count = 0;
        foreach ( $payments as $payment_key => $payment_obj ) {

            if ( rest_is_field_included( "id", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'id' ] = $payment_obj->ID;
            }
            if ( rest_is_field_included( "total", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'total' ] = $payment_obj->total;
            }
            if ( rest_is_field_included( "subtotal", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'subtotal' ] = $payment_obj->subtotal;
            }
            if ( rest_is_field_included( "tax", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'tax' ] = $payment_obj->tax;
            }
            if ( rest_is_field_included( "discounted_amount", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'discounted_amount' ] = $payment_obj->discounted_amount;
            }
            if ( rest_is_field_included( "tax_rate", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'tax_rate' ] = $payment_obj->tax_rate;
            }
            if ( rest_is_field_included( "fees", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'fees' ] = $payment_obj->fees;
            }
            if ( rest_is_field_included( "fees_total", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'fees_total' ] = $payment_obj->fees_total;
            }
            if ( rest_is_field_included( "discounts", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'discounts' ] = $payment_obj->discounts;
            }
            if ( rest_is_field_included( "payment_meta", $payments_field ) ) {
                $data[ "payments" ][ $count ][ 'payment_meta' ] = $payment_obj->payment_meta;
            }
            $count++;
        }
        // }
        $response = new WP_REST_Response( $data );
        return $response;
    }

    /**
     * Prepares a single term for create or update.
     *
     * @since 3.0.0
     *
     * @param WP_REST_Request $request Request object.
     * @return object Term object.
     */
    protected function prepare_item_for_database( $request ): stdClass {
        $prepared_customer = new stdClass();
        $schema = $this->get_item_schema();

        // Customer ID.
        if ( isset( $request[ 'id' ] ) ) {
            $custmer_object = new RPRESS_Customer( $request[ 'id' ] );
            if ( is_wp_error( $custmer_object ) ) {
                return $custmer_object;
            }
            $prepared_customer->ID = $custmer_object->id;
        }

        // Customer email.
        if ( !empty( $schema[ 'properties' ][ 'email' ] ) && isset( $request[ 'email' ] ) ) {
            if ( is_string( $request[ 'email' ] ) ) {
                $prepared_customer->email = $request[ 'email' ];
            }
        }

        // Customer emails.
        if ( !empty( $schema[ 'properties' ][ 'emails' ] ) && isset( $request[ 'emails' ] ) ) {
            if ( is_array( $request[ 'emails' ] ) ) {
                for ( $i = 0; $i < count( $request[ 'emails' ] ); $i++ ) {
                    $prepared_customer->emails[] = $request[ 'emails' ][ $i ];
                }
            }
        }

        // Customer name.
        if ( !empty( $schema[ 'properties' ][ 'name' ] ) && isset( $request[ 'name' ] ) ) {
            if ( is_string( $request[ 'name' ] ) ) {
                $prepared_customer->name = $request[ 'name' ];
            }
        }

        // Customer purchase_value.
        if ( !empty( $schema[ 'properties' ][ 'purchase_value' ] ) && isset( $request[ 'purchase_value' ] ) ) {
            if ( is_float( $request[ 'purchase_value' ] ) ) {
                $prepared_customer->purchase_value = $request[ 'purchase_value' ];
            }
        }

        // Customer purchase_value.
        if ( !empty( $schema[ 'properties' ][ 'purchase_value' ] ) && isset( $request[ 'purchase_value' ] ) ) {
            if ( is_float( $request[ 'purchase_value' ] ) ) {
                $prepared_customer->purchase_value = $request[ 'purchase_value' ];
            }
        }

        // Customer purchase_count.
        if ( !empty( $schema[ 'properties' ][ 'purchase_count' ] ) && isset( $request[ 'purchase_count' ] ) ) {
            if ( is_integer( $request[ 'purchase_count' ] ) ) {
                $prepared_customer->purchase_count = $request[ 'purchase_count' ];
            }
        }

        // Customer payment_ids.
        if ( !empty( $schema[ 'properties' ][ 'payment_ids' ] ) && isset( $request[ 'payment_ids' ] ) ) {
            if ( is_array( $request[ 'payment_ids' ] ) ) {
                for ( $i = 0; $i < count( $request[ 'payment_ids' ] ); $i++ ) {
                    $prepared_customer->payment_ids[] = $request[ 'payment_ids' ][ $i ];
                }
            }
        }

        // Customer payment_id.
        if ( !empty( $schema[ 'properties' ][ 'payment_id' ] ) && isset( $request[ 'payment_id' ] ) ) {
            if ( is_integer( $request[ 'payment_id' ] ) ) {
                $prepared_customer->payment_id = $request[ 'payment_id' ];
            }
        }

        // Customer note.
        if ( !empty( $schema[ 'properties' ][ 'notes' ] ) && isset( $request[ 'notes' ] ) ) {
            if ( is_string( $request[ 'notes' ] ) ) {
                $prepared_customer->notes = $request[ 'notes' ];
            }
        }

        // Customer Meta Key.
        if ( !empty( $schema[ 'properties' ][ 'meta_key' ] ) && isset( $request[ 'meta_key' ] ) ) {
            if ( is_string( $request[ 'meta_key' ] ) ) {
                $prepared_customer->meta_key = $request[ 'meta_key' ];
            }
        }

        // Customer Meta Value.
        if ( !empty( $schema[ 'properties' ][ 'meta_value' ] ) && isset( $request[ 'meta_value' ] ) ) {
            if ( is_string( $request[ 'meta_value' ] ) ) {
                $prepared_customer->meta_value = $request[ 'meta_value' ];
            } elseif ( is_array( $request[ 'meta_value' ] ) ) {
                for ( $i = 0; $i < count( $request[ 'meta_value' ] ); $i++ ) {
                    $prepared_customer->meta_value[] = $request[ 'meta_value' ][ $i ];
                }
            }
        }

        return $prepared_customer;
    }
}
