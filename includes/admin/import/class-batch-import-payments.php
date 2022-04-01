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
class RPRESS_Batch_Payments_Import extends RPRESS_Batch_Import {

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
			'total'             => '',
			'subtotal'          => '',
			'tax'               => 'draft',
			'number'            => '',
			'mode'              => '',
			'gateway'           => '',
			'date'              => '',
			'status'            => '',
			'email'             => '',
			'first_name'        => '',
			'last_name'         => '',
			'customer_id'       => '',
			'user_id'           => '',
			'discounts'         => '',
			'key'               => '',
			'transaction_id'    => '',
			'ip'                => '',
			'currency'          => '',
			'parent_payment_id' => '',
			'fooditems'         => '',
			'line1'             => '',
			'line2'             => '',
			'city'              => '',
			'state'             => '',
			'zip'               => '',
			'country'           => '',
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

		// Remove certain actions to ensure they don't fire when creating the payments
		remove_action( 'rpress_complete_purchase', 'rpress_trigger_purchase_receipt', 999 );
		remove_action( 'rpress_admin_sale_notice', 'rpress_admin_email_notice', 10 );

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * ( $this->step - 1 ) ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;

			// Clean up the temporary records in the payment import process
			global $wpdb;
			$sql = "DELETE FROM {$wpdb->prefix}rpress_customermeta WHERE meta_key = '_canonical_import_id'";
			$wpdb->query( $sql );
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

				// Import payment
				$this->create_payment( $row );

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
	public function create_payment( $row = array() ) {

		$payment = new RPRESS_Payment;
		$payment->status = 'pending';

		if( ! empty( $this->field_mapping['number'] ) && ! empty( $row[ $this->field_mapping['number'] ] ) ) {

			$payment->number = sanitize_text_field( $row[ $this->field_mapping['number'] ] );

		}

		if( ! empty( $this->field_mapping['mode'] ) && ! empty( $row[ $this->field_mapping['mode'] ] ) ) {

			$mode = strtolower( sanitize_text_field( $row[ $this->field_mapping['mode'] ] ) );
			$mode = 'test' != $mode && 'live' != $mode ? false : $mode;
			if( ! $mode ) {
				$mode = rpress_is_test_mode() ? 'test' : 'live';
			}

			$payment->mode = $mode;

		}

		if( ! empty( $this->field_mapping['date'] ) && ! empty( $row[ $this->field_mapping['date'] ] ) ) {

			$date = sanitize_text_field( $row[ $this->field_mapping['date'] ] );

			if( ! strtotime( $date ) ) {

				$date = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );

			} else {

				$date = date( 'Y-m-d H:i:s', strtotime( $date ) );

			}

			$payment->date = $date;

		}

		$payment->customer_id = $this->set_customer( $row );

		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$payment->email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		if( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

			$payment->first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );

		}

		if( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

			$payment->last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );

		}

		if( ! empty( $this->field_mapping['user_id'] ) && ! empty( $row[ $this->field_mapping['user_id'] ] ) ) {

			$user_id = sanitize_text_field( $row[ $this->field_mapping['user_id'] ] );

			if( is_numeric( $user_id ) ) {

				$user_id = absint( $row[ $this->field_mapping['user_id'] ] );
				$user    = get_userdata( $user_id );

			} elseif( is_email( $user_id ) ) {

				$user = get_user_by( 'email', $user_id );

			} else {

				$user = get_user_by( 'login', $user_id );

			}

			if( $user ) {

				$payment->user_id = $user->ID;

				$customer = new RPRESS_Customer( $payment->customer_id );

				if( empty( $customer->user_id ) ) {
					$customer->update( array( 'user_id' => $user->ID ) );
				}

			}

		}

		if( ! empty( $this->field_mapping['discounts'] ) && ! empty( $row[ $this->field_mapping['discounts'] ] ) ) {

			$payment->discounts = sanitize_text_field( $row[ $this->field_mapping['discounts'] ] );

		}

		if( ! empty( $this->field_mapping['transaction_id'] ) && ! empty( $row[ $this->field_mapping['transaction_id'] ] ) ) {

			$payment->transaction_id = sanitize_text_field( $row[ $this->field_mapping['transaction_id'] ] );

		}

		if( ! empty( $this->field_mapping['ip'] ) && ! empty( $row[ $this->field_mapping['ip'] ] ) ) {

			$payment->ip = sanitize_text_field( $row[ $this->field_mapping['ip'] ] );

		}

		if( ! empty( $this->field_mapping['gateway'] ) && ! empty( $row[ $this->field_mapping['gateway'] ] ) ) {

			$gateways = rpress_get_payment_gateways();
			$gateway  = strtolower( sanitize_text_field( $row[ $this->field_mapping['gateway'] ] ) );

			if( ! array_key_exists( $gateway, $gateways ) ) {

				foreach( $gateways as $key => $enabled_gateway ) {

					if( $enabled_gateway['checkout_label'] == $gateway ) {

						$gateway = $key;
						break;

					}

				}

			}

			$payment->gateway = $gateway;

		}

		if( ! empty( $this->field_mapping['currency'] ) && ! empty( $row[ $this->field_mapping['currency'] ] ) ) {

			$payment->currency = strtoupper( sanitize_text_field( $row[ $this->field_mapping['currency'] ] ) );

		}

		if( ! empty( $this->field_mapping['key'] ) && ! empty( $row[ $this->field_mapping['key'] ] ) ) {

			$payment->key = sanitize_text_field( $row[ $this->field_mapping['key'] ] );

		}

		if( ! empty( $this->field_mapping['parent_payment_id'] ) && ! empty( $row[ $this->field_mapping['parent_payment_id'] ] ) ) {

			$payment->parent_payment_id = absint( $row[ $this->field_mapping['parent_payment_id'] ] );

		}

		if( ! empty( $this->field_mapping['fooditems'] ) && ! empty( $row[ $this->field_mapping['fooditems'] ] ) ) {

			if( __( 'Products (Raw)', 'restropress' ) == $this->field_mapping['fooditems'] ) {

				// This is an RPRESS export so we can extract prices
				$fooditems = $this->get_fooditems_from_rpress( $row[ $this->field_mapping['fooditems'] ] );

			} else {

				$fooditems = $this->str_to_array( $row[ $this->field_mapping['fooditems'] ] );

			}

			if( is_array( $fooditems ) ) {

				$fooditem_count = count( $fooditems );

				foreach( $fooditems as $fooditem ) {

					if( is_array( $fooditem ) ) {
						$fooditem_name = $fooditem['fooditem'];
						$price         = $fooditem['price'];
						$tax           = $fooditem['tax'];
						$price_id      = $fooditem['price_id'];
					} else {
						$fooditem_name = $fooditem;
					}

					$fooditem_id = $this->maybe_create_fooditem( $fooditem_name );

					if( ! $fooditem_id ) {
						continue;
					}

					$item_price = ! isset( $price ) ? rpress_get_fooditem_price( $fooditem_id ) : $price;
					$item_tax   = ! isset( $tax ) ? ( $fooditem_count > 1 ? 0.00 : $payment->tax ) : $tax;
					$price_id   = ! isset( $price_id ) ? false : $price_id;

					$args = array(
						'item_price' => $item_price,
						'tax'        => $item_tax,
						'price_id'   => $price_id,
					);

					$payment->add_fooditem( $fooditem_id, $args );

				}

			}

		}

		if( ! empty( $this->field_mapping['total'] ) && ! empty( $row[ $this->field_mapping['total'] ] ) ) {

			$payment->total = rpress_sanitize_amount( $row[ $this->field_mapping['total'] ] );

		}

		if( ! empty( $this->field_mapping['tax'] ) && ! empty( $row[ $this->field_mapping['tax'] ] ) ) {

			$payment->tax = rpress_sanitize_amount( $row[ $this->field_mapping['tax'] ] );

		}

		if( ! empty( $this->field_mapping['subtotal'] ) && ! empty( $row[ $this->field_mapping['subtotal'] ] ) ) {

			$payment->subtotal = rpress_sanitize_amount( $row[ $this->field_mapping['subtotal'] ] );

		} else {

			$payment->subtotal = $payment->total - $payment->tax;

		}

		$address = array( 'line1' => '', 'line2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => '' );

		foreach( $address as $key => $address_field ) {

			if( ! empty( $this->field_mapping[ $key ] ) && ! empty( $row[ $this->field_mapping[ $key ] ] ) ) {

				$address[ $key ] = sanitize_text_field( $row[ $this->field_mapping[ $key ] ] );

			}

		}

		$payment->address = $address;

		$payment->save();


		// The status has to be set after payment is created to ensure status update properly
		if( ! empty( $this->field_mapping['status'] ) && ! empty( $row[ $this->field_mapping['status'] ] ) ) {

			$payment->status = strtolower( sanitize_text_field( $row[ $this->field_mapping['status'] ] ) );

		} else {

			$payment->status = 'complete';

		}

		// Save a second time to update stats
		$payment->save();

	}

	private function set_customer( $row ) {

		global $wpdb;

		if( ! empty( $this->field_mapping['email'] ) && ! empty( $row[ $this->field_mapping['email'] ] ) ) {

			$email = sanitize_text_field( $row[ $this->field_mapping['email'] ] );

		}

		// Look for a customer from the canonical source, if any
		if( ! empty( $this->field_mapping['customer_id'] ) && ! empty( $row[ $this->field_mapping['customer_id'] ] ) ) {

			$canonical_id = absint( $row[ $this->field_mapping['customer_id'] ] );
			$mapped_id    = $wpdb->get_var( $wpdb->prepare( "SELECT customer_id FROM $wpdb->customermeta WHERE meta_key = '_canonical_import_id' AND meta_value = %d LIMIT 1", $canonical_id ) );

		}

		if( ! empty( $mapped_id ) ) {

			$customer = new RPRESS_Customer( $mapped_id );

		}

		if( empty( $mapped_id ) || ! $customer->id > 0 ) {

			// Look for a customer based on provided ID, if any

			if( ! empty( $this->field_mapping['customer_id'] ) && ! empty( $row[ $this->field_mapping['customer_id'] ] ) ) {

				$customer_id = absint( $row[ $this->field_mapping['customer_id'] ] );

				$customer_by_id = new RPRESS_Customer( $customer_id );

			}

			// Now look for a customer based on provided email

			if( ! empty( $email ) ) {

				$customer_by_email = new RPRESS_Customer( $email );

			}

			// Now compare customer records. If they don't match, customer_id will be stored in meta and we will use the customer that matches the email

			if( ( empty( $customer_by_id ) || $customer_by_id->id !== $customer_by_email->id ) && ! empty( $customer_by_email ) )  {

				$customer = $customer_by_email;

			} else if ( ! empty( $customer_by_id ) ) {

				$customer = $customer_by_id;

				if( ! empty( $email ) ) {
					$customer->add_email( $email );
				}

			}

			// Make sure we found a customer. Create one if not.
			if( empty( $customer->id ) ) {

				if ( ! $customer instanceof RPRESS_Customer ) {
					$customer = new RPRESS_Customer;
				}

				$first_name = '';
				$last_name  = '';

				if( ! empty( $this->field_mapping['first_name'] ) && ! empty( $row[ $this->field_mapping['first_name'] ] ) ) {

					$first_name = sanitize_text_field( $row[ $this->field_mapping['first_name'] ] );

				}

				if( ! empty( $this->field_mapping['last_name'] ) && ! empty( $row[ $this->field_mapping['last_name'] ] ) ) {

					$last_name = sanitize_text_field( $row[ $this->field_mapping['last_name'] ] );

				}

				$customer->create( array(
					'name'  => $first_name . ' ' . $last_name,
					'email' => $email
				) );

				if( ! empty( $canonical_id ) && (int) $canonical_id !== (int) $customer->id ) {
					$customer->update_meta( '_canonical_import_id', $canonical_id );
				}

			}


		}

		if( $email && $email != $customer->email ) {
			$customer->add_email( $email );
		}

		return $customer->id;

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
	 * Return the calculated completion percentage
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_fooditems_from_rpress( $data_str ) {

		// Break string into separate products

		$d_array   = array();
		$fooditems = (array) explode( '/', $data_str );

		if( $fooditems ) {

			foreach( $fooditems as $key => $fooditem ) {

				$d   = (array) explode( '|', $fooditem );
				preg_match_all( '/\{(\d|(\d+(\.\d+|\d+)))\}/', $d[1], $matches );
				$price = trim( substr( $d[1], 0, strpos( $d[1], '{' ) ) );
				$tax   = isset( $matches[1][0] ) ? trim( $matches[1][0] ) : 0;
				$price_id = isset( $matches[1][1] ) ? trim( $matches[1][1] ) : false;

				$d_array[] = array(
					'fooditem' => trim( $d[0] ),
					'price'    => $price - $tax,
					'tax'      => $tax,
					'price_id' => $price_id,
				);

			}

		}

		return $d_array;

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
	 * Retrieve the URL to the payments list table
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_list_table_url() {
		return admin_url( 'admin.php?page=rpress-payment-history' );
	}

	/**
	 * Retrieve the payments labels
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_import_type_label() {
		return __( 'payments', 'restropress' );
	}
}
