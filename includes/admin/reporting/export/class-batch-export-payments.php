<?php
/**
 * Payments Export Class
 *
 * This class handles payment export in batches
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
 * RPRESS_Batch_Payments_Export Class
 *
 * @since 2.4
 */
class RPRESS_Batch_Payments_Export extends RPRESS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.4
	 */
	public $export_type = 'payments';

	/**
	 * Set the CSV columns
	 *
	 * @since 2.4
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'id'           => __( 'Payment ID',   'restropress' ), // unaltered payment ID (use for querying)
			'seq_id'       => __( 'Payment Number',   'restropress' ), // sequential payment ID
			'email'        => __( 'Email', 'restropress' ),
			'customer_id'  => __( 'Customer ID', 'restropress' ),
			'first'        => __( 'First Name', 'restropress' ),
			'last'         => __( 'Last Name', 'restropress' ),
			'address1'     => __( 'Address', 'restropress' ),
			'address2'     => __( 'Address (Line 2)', 'restropress' ),
			'city'         => __( 'City', 'restropress' ),
			'state'        => __( 'State', 'restropress' ),
			'country'      => __( 'Country', 'restropress' ),
			'zip'          => __( 'Zip / Postal Code', 'restropress' ),
	   'delivery_address'  =>__('Delivery Address', 'restropress'),
			'products'     => __( 'Products (Verbose)', 'restropress' ),
			'instruction'  => __( 'Special Instruction','restropress' ),
   			'service_type' => __( 'Service Type','restropress' ),
			'service_time' => __( 'Service Time','restropress' ),

			'products_raw' => __( 'Products (Raw)', 'restropress' ),
			'skus'         => __( 'SKUs', 'restropress' ),
			'amount'       => __( 'Amount', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')',
			'tips'         => __( 'Tips', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')',
			'tax'          => __( 'Tax', 'restropress' ) . ' (' . html_entity_decode( rpress_currency_filter( '' ) ) . ')',
			'discount'     => __( 'Discount Code', 'restropress' ),
			'gateway'      => __( 'Payment Method', 'restropress' ),
			'trans_id'     => __( 'Transaction ID', 'restropress' ),
			'key'          => __( 'Purchase Key', 'restropress' ),
			'date'         => __( 'Date', 'restropress' ),
			'user'         => __( 'User', 'restropress' ),
			'currency'     => __( 'Currency', 'restropress' ),
			'ip'           => __( 'IP Address', 'restropress' ),
			'mode'         => __( 'Mode (Live|Test)', 'restropress' ),
			'order_status' => __( 'Status', 'restropress' ),
			'country_name' => __( 'Country Name', 'restropress' ),

		);

		if( ! rpress_use_skus() ){
			unset( $cols['skus'] );
		}
		if ( ! rpress_get_option( 'enable_sequential' ) ) {
			unset( $cols['seq_id'] );
		}

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 2.4
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;
		
		$data = array();
		$args = array(
			'number'   => 30,
			'page'     => $this->step,
	'order_status'     => $this->status,
			'order'    => 'ASC',
			'orderby'  => 'date'
		);
		 
		if( ! empty( $this->start ) || ! empty( $this->end ) ) {

			$args['start_date'] = date( 'Y-n-d 00:00:00', strtotime( $this->start ) ) ;
			$args['end_date']   = date( 'Y-n-d 23:59:59', strtotime( $this->end ) ) ;

		}

		if( ! is_null( $this->status) ) {
			if( $this->status ){
				$args['meta_query'] = array( 
					array(
						'key' => '_order_status',
						'value' => $this->status,
						'compare' => '='
					)
				);	

			}
		
		}

		$payments = rpress_get_payments( $args );
		if( $payments ) {
			foreach ( $payments as $payment ) {
				$payment = new RPRESS_Payment( $payment->ID );

				$address_info	 = get_post_meta( $payment->ID , '_rpress_delivery_address', true );

				$user_address 	 = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
				$user_address 	.= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
				$user_address 	.= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
				$user_address 	.= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';
				
				$order_statuses = rpress_get_order_statuses();	
	      		$current_order_status = rpress_get_order_status( $payment->ID );
	      		$payment_meta = rpress_get_payment_meta( $payment->ID );
				$user_info      = $payment->user_info;
				$fooditems      = $payment->cart_details;
				$tips 			= $payment_meta['fees']['tip'];
				$tips_amount	= $tips['amount'];
				
				$total          = $payment->total;
				$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
				$products       = '';
				$service_type   = get_post_meta( $payment->ID, '_rpress_delivery_type', true );
				$service_time   = get_post_meta( $payment->ID, '_rpress_delivery_time', true );

				$products_raw   = '';
				$skus           = ''; 

				if ( $fooditems ) {
					foreach ( $fooditems as $key => $fooditem ) {
							
						$id  = isset( $payment_meta['cart_details'] ) ? $fooditem['id'] : $fooditem;

							$title = $id['name'];
							$add_items = $id['item_number'];
							$item_name = array();

							foreach( $add_items['options'] as $key => $add_item ){
								
								$item_name[] = isset( $add_item['addon_item_name'] ) ? $add_item['addon_item_name'] : false;
							}
							
							$addon_list = implode( " ", $item_name );

						$qty = isset( $fooditem['quantity'] ) ? $fooditem['quantity'] : 1;

						if ( isset( $fooditem['price'] ) ) {
							$price = $fooditem['price'];
						} else {
							// If the fooditem has variable prices, override the default price
							$price_override = isset( $payment_meta['cart_details'] ) ? $fooditem['price'] : null;
							$price = rpress_get_fooditem_final_price( $id, $user_info, $price_override );
						}

						$fooditem_tax      = isset( $fooditem['tax'] ) ? $fooditem['tax'] : 0;

						if ( isset( $fooditem['instruction'] ) ) {
							
							$instruction = $fooditem['instruction'];
							if ( ! empty( $instruction ) ) {
								$instruction .= $instruction;
							}
							$instruction .= '  ';
						}
						
						$fooditem_price_id = isset( $fooditem['item_number']['options']['price_id'] ) ? absint( $fooditem['item_number']['options']['price_id'] ) : false;

						/* Set up verbose product column */

						$products .= html_entity_decode( $title ) .'|'. $addon_list;

						if ( $qty > 1 ) {
							$products .= html_entity_decode( ' (' . $qty . ')' );
						}

						$products .= ' - ';

						if ( rpress_use_skus() ) {
							$sku = rpress_get_fooditem_sku( $fooditem['id']);

							if ( ! empty( $sku ) ) {
								$skus .= $sku;
							}
							$skus .= '  ';
						}

						if ( isset( $fooditems[ $key ]['item_number'] ) && isset( $fooditems[ $key ]['item_number']['options'] ) ) {
							$price_options = $fooditems[ $key ]['item_number']['options'];

							if ( isset( $price_options['price_id'] ) && ! is_null( $price_options['price_id'] ) ) {
								$products .= html_entity_decode( rpress_get_price_option_name( $id, $price_options['price_id'], $payment->ID ) ) . ' - ';
							}
						}

						$products .= html_entity_decode( rpress_currency_filter( rpress_format_amount( $price ) ) );

						if ( $key != ( count( $fooditems ) -1 ) ) {

							$products .= ' / ';
						}
						

						/* Set up raw products column - Nothing but product names */
						$products_raw .= html_entity_decode( $title ) .'|'. $addon_list . '|' . $price . '{' . $fooditem_tax . '}';

						// if we have a Price ID, include it.
						if ( false !== $fooditem_price_id ) {
							$products_raw .= '{' . $fooditem_price_id . '}';
						}

						if ( $key != ( count( $fooditems ) -1 ) ) {

							$products_raw .= ' / ';

						}
					}
				}

				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				} else {
					$user = false;
				}

				$data[] = array(
					'id'           => $payment->ID,
					'seq_id'       => $payment->number,
					'email'        => $user_info['email'],
					'customer_id'  => $payment->customer_id,
					'first'        => $user_info['first_name'],
					'last'         => $user_info['last_name'],
					'address1'     => isset( $user_info['address']['line1'] )   ? $user_info['address']['line1']   : '',
					'address2'     => isset( $user_info['address']['line2'] )   ? $user_info['address']['line2']   : '',
					'city'         => isset( $user_info['address']['city'] )    ? $user_info['address']['city']    : '',
					'state'        => isset( $user_info['address']['state'] )   ? $user_info['address']['state']   : '',
					'country'      => isset( $user_info['address']['country'] ) ? $user_info['address']['country'] : '',
					'zip'          => isset( $user_info['address']['zip'] )     ? $user_info['address']['zip']     : '',
				'delivery_address' => $user_address,
					'products'     => $products,
					'instruction'  => $instruction,
					'service_type' => $service_type,
					'service_time' => $service_time,
					'products_raw' => $products_raw,
					'skus'         => $skus,
					'amount'       => html_entity_decode( rpress_format_amount( $total ) ), // The non-discounted item price
					'tips'		   => $tips_amount,	
					'tax'          => html_entity_decode( rpress_format_amount( rpress_get_payment_tax( $payment->ID, $payment_meta ) ) ),
					'discount'     => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'restropress' ),
					'gateway'      => rpress_get_gateway_admin_label( rpress_get_payment_meta( $payment->ID, '_rpress_payment_gateway', true ) ),
					'trans_id'     => $payment->transaction_id,
					'key'          => $payment_meta['key'],
					'date'         => $payment->date,
					'user'         => $user ? $user->display_name : __( 'guest', 'restropress' ),
					'currency'     => $payment->currency,
					'ip'           => $payment->ip,
					'mode'         => $payment->get_meta( '_rpress_payment_mode', true ),
					'order_status' => $current_order_status,
					'country_name' => isset( $user_info['address']['country'] ) ? rpress_get_country_name( $user_info['address']['country'] ) : '',
				);
			}

			$data = apply_filters( 'rpress_export_get_data', $data );
			$data = apply_filters( 'rpress_export_get_data_' . $this->export_type, $data );
			return $data;

		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.4
	 * @return int
	 */
	public function get_percentage_complete() {

		$status = $this->status;
		$args   = array(
			'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
			'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
		);

		if( 'any' == $status ) {

			$total = array_sum( (array) rpress_count_payments( $args ) );

		} else {

			$total = rpress_count_payments( $args )->$status;

		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.4.2
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {

		$this->start  = isset( $request['start'] )  ? sanitize_text_field( $request['start'] )  : '';
		$this->end    = isset( $request['end']  )   ? sanitize_text_field( $request['end']  )   : '';
		$this->status = isset( $request['order_status'] ) ? sanitize_text_field( $request['order_status'] ) : 'complete';
	}
}