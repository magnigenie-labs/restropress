<?php
/**
 * Batch Sales Logs Export Class
 *
 * This class handles Sales logs export
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, MagniGenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Batch_Sales_Export Class
 *
 * @since 1.0
 */
class RPRESS_Batch_Sales_Export extends RPRESS_Batch_Export {
	/**
	 * Our export type. Used for export-type specific filters/actions
	 *
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'sales';

	/**
	 * Set the CSV columns
	 *
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'ID'          => __( 'Log ID', 'restropress' ),
			'user_id'     => __( 'User', 'restropress' ),
			'customer_id' => __( 'Customer ID', 'restropress' ),
			'email'       => __( 'Email', 'restropress' ),
			'first_name'  => __( 'First Name', 'restropress' ),
			'last_name'   => __( 'Last Name', 'restropress' ),
			'fooditem'    => rpress_get_label_singular(),
			'amount'      => __( 'Item Amount', 'restropress' ),
			'payment_id'  => __( 'Payment ID', 'restropress' ),
			'price_id'    => __( 'Price ID', 'restropress' ),
			'date'        => __( 'Date', 'restropress' ),
		);

		return $cols;
	}

	/**
	 * Get the Export Data
	 *
	 * @since 1.0
 	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $rpress_logs;

		$data = array();

		$args = array(
			'log_type'       => 'sale',
			'posts_per_page' => 30,
			'paged'          => $this->step,
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		if ( ! empty( $this->start ) || ! empty( $this->end ) ) {
			$args['date_query'] = array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			);
		}

		if ( 0 !== $this->fooditem_id ) {
			$args['post_parent'] = $this->fooditem_id;
		}

		$logs = $rpress_logs->get_connected_logs( $args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$payment_id = get_post_meta( $log->ID, '_rpress_log_payment_id', true );
				$payment    = new RPRESS_Payment( $payment_id );
				$fooditem    = new RPRESS_Fooditem( $log->post_parent );

				if ( ! empty( $payment->ID ) ) {
					$customer   = new RPRESS_Customer( $payment->customer_id );
					$cart_items = $payment->cart_details;
					$amount     = 0;

					if ( is_array( $cart_items ) ) {
						foreach ( $cart_items as $item ) {
							$log_price_id = null;
							if ( $item['id'] == $log->post_parent ) {
								if ( isset( $item['item_number']['options']['price_id'] ) ) {
									$log_price_id = get_post_meta( $log->ID, '_rpress_log_price_id', true );

									if ( (int) $item['item_number']['options']['price_id'] !== (int) $log_price_id ) {
										continue;
									}
								}

								$amount = isset( $item['price'] ) ? $item['price'] : $item['item_price'];
								break;
							}
						}
					}
				}
				$data[] = array(
					'ID'          => $log->ID,
					'user_id'     => $customer->user_id,
					'customer_id' => $customer->id,
					'email'       => $payment->email,
					'first_name'  => $payment->first_name,
					'last_name'   => $payment->last_name,
					'fooditem'    => $fooditem->post_title,
					'amount'      => $amount,
					'payment_id'  => $payment->ID,
					'price_id'    => $log_price_id,
					'date'        => get_post_field( 'post_date', $payment_id ),
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
	 * @since 1.0
	 * @return int
	 */
	public function get_percentage_complete() {
		global $rpress_logs;

		$args = array(
			'post_type'		   => 'rpress_log',
			'posts_per_page'   => -1,
			'post_status'	   => 'publish',
			'fields'           => 'ids',
			'post_parent'      => $this->fooditem_id,
			'tax_query'        => array(
				array(
					'taxonomy' 	=> 'rpress_log_type',
					'field'		=> 'slug',
					'terms'		=> 'sale'
				)
			),
			'date_query'        => array(
				array(
					'after'     => date( 'Y-n-d H:i:s', strtotime( $this->start ) ),
					'before'    => date( 'Y-n-d H:i:s', strtotime( $this->end ) ),
					'inclusive' => true
				)
			)
		);

		$logs       = new WP_Query( $args );
		$total      = (int) $logs->post_count;
		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( 30 * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	public function set_properties( $request ) {
		$this->start       = isset( $request['start'] ) ? sanitize_text_field( $request['start'] ) : '';
		$this->end         = isset( $request['end'] )   ? sanitize_text_field( $request['end'] ) . ' 23:59:59'  : '';
		$this->fooditem_id = isset( $request['fooditem_id'] )   ? absint( $request['fooditem_id'] )        : 0;
	}
}
