<?php
/**
 * Batch Customers Export Class
 *
 * This class handles customer export
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Batch_Customers_Export Class
 *
 * @since 2.4
 */
class RPRESS_Batch_Customers_Export extends RPRESS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'customers';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {

		$cols = array(
			'id'        	 => __( 'ID',   'restropress' ),
			'user_id'		 => __( 'User ID',   'restropress' ),
			'user_login'	 => __( 'User Name', 'restropress' ),
			'user_pass' 	 => __( 'Password','restropress' ),
			'payment_ids'	 => __('Payment IDS','restropress' ),
			'date_created'	 => __( 'Date Created','restropress' ),
			'name'      	 => __( 'Name',   'restropress' ),
			'email'     	 => __( 'Email', 'restropress' ),
			'purchase_count' => __( 'Number of Purchases', 'restropress' ),
			'purchase_value' => __( 'Customer Value', 'restropress' ),
			'line1'			 => __( 'Address Line1', 'restropress' ),
			'line2'			 => __( 'Address Line2', 'restropress' ),
			'country'   	 => __( 'Country/Region', 'restropress' ),
			'city'			 => __( 'City', 'restropress' ),
			'state'			 => __( 'State', 'restropress' ),
			'zip'			 => __( 'Postal Code', 'restropress' )
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
	 *   Database API
	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {

		$data = array();

		if ( ! empty( $this->fooditem ) ) {

			// Export customers of a specific product
			global $rpress_logs;

			$args = array(
				'post_parent'    => absint( $this->fooditem ),
				'log_type'       => 'sale',
				'posts_per_page' => 30,
				'paged'          => $this->step
			);

			if( null !== $this->price_id ) {
				$args['meta_query'] = array(
					array(
						'key'   => '_rpress_log_price_id',
						'value' => (int) $this->price_id
					)
				);
			}

			$logs = $rpress_logs->get_connected_logs( $args );

			if ( $logs ) {
				foreach ( $logs as $log ) {

					$payment_id  = get_post_meta( $log->ID, '_rpress_log_payment_id', true );
					$customer_id = rpress_get_payment_customer_id( $payment_id );
					$customer    = new RPRESS_Customer( $customer_id );
					$user_id	 =	$customer->user_id; 	
					$customer_address = get_user_meta( $user_id, '_rpress_user_address', true );
					$line1 = $customer_address['line1'];
					$line2 = $customer_address['line2'];
					$country = $customer_address['country'];
					$city = $customer_address['city'];
					$state = $customer_address['state'];
					$zip = $customer_address['zip'];
					$user = get_user_by( 'id', $user_id );

					$data[] = array(
						'id'          	 => $customer->id,
						'user_id'	  	 => $customer->user_id,
						'user_login'  	 =>$user->user_login,
						'user_pass'	  	 =>$user->user_pass,
						'payment_ids'	 =>$customer->payment_ids,
						'date_created'	 =>$customer->date_created,
						'name'        	 => $customer->name,
						'email'       	 => $customer->email,
						'purchase_count' => $customer->purchase_count,
						'purchase_value' => rpress_format_amount( $customer->purchase_value ),
						'line1'		     => $line1,
						'line2'		     => $line2,
						'country'        => $country,
						'city'		     => $city,
						'state'		     => $state,
						'zip'		     => $zip
					);
				}
			}

		} else {

			// Export all customers
			$offset    = 30 * ( $this->step - 1 );
			$customers = RPRESS()->customers->get_customers( array( 'number' => 30, 'offset' => $offset ) );
			
			$i = 0;

			foreach ( $customers as $customer ) {
				$user_id	 =	$customer->user_id;
				$customer_address = get_user_meta( $user_id, '_rpress_user_address', true );
				$user 	  = get_user_by( 'id', $user_id );
				$line1    =	$customer_address['line1'];
				$line2    =	$customer_address['line2'];
				$country  =	$customer_address['country'];
				$city     =	$customer_address['city'];
				$state    =	$customer_address['state'];
				$zip      =	$customer_address['zip'];
				
				$data[$i]['id']          	= $customer->id;
				$data[$i]['user_id']     	= $customer->user_id;
				$data[$i]['user_login']  	= $user->user_login;
				$data[$i]['user_pass']   	= $user->user_pass;
				$data[$i]['payment_ids'] 	= $customer->payment_ids;
				$data[$i]['date_created'] 	= $customer->date_created;
				$data[$i]['name']        	= $customer->name;
				$data[$i]['email']       	= $customer->email;
				$data[$i]['purchase_count'] = $customer->purchase_count;
				$data[$i]['purchase_value'] = rpress_format_amount( $customer->purchase_value );
				$data[$i]['line1']    		= $line1;
				$data[$i]['line2']     	    = $line2;
				$data[$i]['country']        = $country;
				$data[$i]['city']           = $city;
				$data[$i]['state']          = $state;
				$data[$i]['zip']   	        = $zip;

				$i++;
			}
		}

		$data = apply_filters( 'rpress_export_get_data', $data );
		$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );

		return $data;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {

		$percentage = 0;

		// We can't count the number when getting them for a specific fooditem
		if( empty( $this->fooditem ) ) {

			$total = RPRESS()->customers->count();

			if( $total > 0 ) {

				$percentage = ( ( 30 * $this->step ) / $total ) * 100;

			}

		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the Customers export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->start    = isset( $request['start'] )            ? sanitize_text_field( $request['start'] ) : '';
		$this->end      = isset( $request['end']  )             ? sanitize_text_field( $request['end']  )  : '';
		$this->fooditem = isset( $request['fooditem']         ) ? absint( $request['fooditem']         )   : null;
		$this->price_id = ! empty( $request['rpress_price_option'] ) && 0 !== $request['rpress_price_option'] ? absint( $request['rpress_price_option'] )   : null;
	}
}
