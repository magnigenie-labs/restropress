<?php
/**
 * RestroPress License Functions
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_activate_addon_license', 'restropress_activate_addon_license' );

function restropress_activate_addon_license() {
	// listen for our activate button to be clicked
	if( isset($_POST['license_key']) ) {

		$api_url = 'https://www.restropress.com';
		// Get the license from the user
		$license = isset( $_POST['license'] ) ? trim( $_POST['license'] ) : '';
		$name = isset( $_POST['product_name'] ) ? $_POST['product_name'] : '';
		$license_key = isset( $_POST['license_key'] ) ? $_POST['license_key'] : '';

		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license,
			'item_name'  => urlencode( $name ), // the name of our product
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) 
			|| 200 !== wp_remote_retrieve_response_code( $response ) ) {
			
			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} 
			else {
				$message = __( 'An error occurred, please try again.' );
			}

		} 
		else {
			
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( false === $license_data->success ) {
				
				switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), $name );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default :

							$message = __( 'An error occurred, please try again.' );
							break;
				}
			}
		}

		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) )
			$return = array( 'status' => 'error', 'message' => $message );
		else {
			//Save the license key in database
			update_option( $license_key, $license );

			// $license_data->license will be either "valid" or "invalid"
			update_option( $license_key . '_status', $license_data->license );
			$return = array( 'status' => 'updated', 'message' => 'Your license is successfully activated.' );
		}
		echo json_encode( $return );
		exit();
	}
}


/***********************************************
	* Deactivate a license key.
	* This will decrease the site count
	***********************************************/
	add_action( 'wp_ajax_deactivate_addon_license', 'restropress_deactivate_addon_license' );
	function restropress_deactivate_addon_license() {
		
		if( isset($_POST['license_key']) ) {

			$license_key = isset( $_POST['license_key'] ) ? $_POST['license_key'] : '';
			// retrieve the license from the database
			$license = trim( get_option( $license_key ) );

			$item_name = isset( $_POST['product_name'] ) ? $_POST['product_name'] : '';

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( $item_name ), // the name of our product in EDD
				'url'        => home_url()
			);

			$api_url = 'https://www.restropress.com';

			// Call the custom API.
			$response = wp_remote_post( $api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}
				$return = array( 'status' => 'error', 'message' => $message );
			}
			else{
				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"
				if( $license_data->license == 'deactivated' ) {
					delete_option( $license_key . '_status' );
					delete_option( $license_key );
				}
				$return = array( 'status' => 'updated', 'message' => 'License successfully deactivated.' );
			}
			echo json_encode( $return );
			exit();
		}
	}