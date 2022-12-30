<?php
/**
 * Payment Import Class
 *
 * This class handles importing payments with the batch processing API
 *
 * @package     RPRESS
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Batch_Import Class
 *
 * @since 1.0.0
 */
class RPRESS_Batch_Customers_Import extends RPRESS_Batch_Import {

	/**
	 * Set up our import config.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		$this->per_step = 5;

		// Set up default field map values
		$this->field_mapping = array(
			'id' 	 		 => '',
			'user_id'   	 => '',
			'user_login'	 => '',
			'user_pass' 	 => '',
			'payment_ids'	 => '',
			'date_created'	 => '',
			'name'   		 => '',
			'email'  		 => '',
			'purchase_count' => '',
			'purchase_value' => '',
			'line1'     	 => '',
			'line2'     	 => '',
			'city'      	 => '',
			'state'     	 => '',
			'zip'       	 => '',
			'country'   	 => ''
		);

	}

	/**
	 * Process a step
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function process_step() {

		$more = false;

		if ( ! $this->can_import() ) {
			wp_die( __( 'You do not have permission to import data.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		// Remove certain actions to ensure they don't fire when creating the 

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * ( $this->step - 1 ) ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;

		}

		if( ! $this->done && $this->csv->data ) {

			$more = true;

			foreach( $this->csv->data as $key => $row ) {

				// Skip all rows until we pass our offset
				if( $key + 1 <= $offset ) {
					continue;
				}

				// Done with this batch
				if( $i > $this->per_step ) {
					break;
				}

				// Import customer
				$this->create_customer( $row );

				$i++;
			}

		}

		return $more;
	}

	/**
	 * Set up and store a payment record from a CSV row
	 *
	 * @since 1.0.0
	 * @return void
	 */
	

	public function create_customer(  $row = array() ) {

		$customer = new RPRESS_Customer();
		

		// Look for a customer from the canonical source, if any
		if( ! empty( $this->field_mapping['id'] ) && ! empty( $row[ $this->field_mapping['id'] ] ) ) {

			$customer->id = $row[ $this->field_mapping['id'] ] ;
			
		}
		
		if( ! empty( $this->field_mapping['name'] ) && ! empty( $row[ $this->field_mapping['name'] ] ) ) {

			$customer->name = $row[ $this->field_mapping['name'] ] ;
			
		}
		if( ! empty( $this->field_mapping['user_login'] ) && ! empty( $row[ $this->field_mapping['user_login'] ] ) ) {

			$customer->user_login = $row[ $this->field_mapping['user_login'] ] ;
			
		}
		if( ! empty( $this->field_mapping['user_pass'] ) && ! empty( $row[ $this->field_mapping['user_pass'] ] ) ) {

			$customer->user_pass = $row[ $this->field_mapping['user_pass'] ];
			
		}
		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$customer->email = $row[ $this->field_mapping['email'] ];
			
		}	
		
		if( ! empty( $this->field_mapping['purchase_count'] ) && ! empty( $row[ $this->field_mapping['purchase_count'] ] ) ) {

			$customer->purchase_count = $row[ $this->field_mapping['purchase_count'] ];
			
		}
		if( ! empty( $this->field_mapping['purchase_value'] ) && ! empty( $row[ $this->field_mapping['purchase_value'] ] ) ) {

			$customer->purchase_value = $row[ $this->field_mapping['purchase_value'] ];
			
		}

		if( ! empty( $this->field_mapping['payment_ids'] ) && ! empty( $row[ $this->field_mapping['payment_ids'] ] ) ) {

			$customer->payment_ids = $row[ $this->field_mapping['payment_ids'] ];
			
		}

		if( ! empty( $this->field_mapping['date_created'] ) && ! empty( $row[ $this->field_mapping['date_created'] ] ) ) {

			$customer->date_created = $row[ $this->field_mapping['date_created'] ];
			
		}
		
		$address = array( 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );

		foreach( $address as $key => $address_field ) {

			if( ! empty( $this->field_mapping[ $key ] ) && ! empty( $row[ $this->field_mapping[ $key ] ] ) ) {

				$address[ $key ] =  $row[ $this->field_mapping[ $key ] ];

			}
			$customer->address = $address;
		}
		 if( ! empty( $this->field_mapping['user_id'] ) && ! empty( $row[ $this->field_mapping['user_id'] ] ) ) {

			$customer->user_id  =  $row[ $this->field_mapping['user_id'] ] ;
		}			 


		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {
			$email = $row[ $this->field_mapping['email'] ];
			
			$customer_test = new RPRESS_Customer($email);
			if($customer_test){
				
				$customer->id = $customer_test->id;
			}
			
			$customer->email =  $row[ $this->field_mapping['email'] ] ;
			
			$data=array();
				foreach ( $customer as $key => $value ) {
					$data[$key] = $value;
				}
				$id =$customer_test->id;
			if( $id !==0 ){

				$customer->update($data);
			}
			else{
				
				$customer->create($data);
			}

		}

	}
	
	
	/**
	 * Return the calculated completion percentage
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$total = count( $this->csv->data );

		if( $total > 0 ) {
			$percentage = ( $this->step * $this->per_step / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}
	/**
	 * Look up Food Items by title and create one if none is found
	 *
	 * @since 1.0.0
	 * @return int Food Item ID
	 */
	private function maybe_create_fooditem( $title = '' ) {

		if( ! is_string( $title ) ) {
			return false;
		}

		$fooditem = get_page_by_title( $title, OBJECT, 'fooditem' );

		if( $fooditem ) {

			$fooditem_id = $fooditem->ID;

		} else {

			$args = array(
				'post_type'   => 'fooditem',
				'post_title'  => $title,
				'post_author' => get_current_user_id()
			);

			$fooditem_id = wp_insert_post( $args );

		}

		return $fooditem_id;
	}

	/**
	 * Retrieve the URL to the payments list table
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_list_table_url() {
		return admin_url( 'admin.php?page=rpress-customers' );
	}

	/**
	 * Retrieve the payments labels
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_import_type_label() {
		return __( 'customers', 'restropress' );
	}
}
