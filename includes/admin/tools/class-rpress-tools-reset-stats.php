<?php
/**
 * Recount store earnings and stats
 *
 * This class handles batch processing of resetting store and fooditem sales and earnings stats
 *
 * @subpackage  Admin/Tools/RPRESS_Tools_Reset_Stats
 * @copyright   Copyright (c) 2018, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Tools_Reset_Stats Class
 *
 * @since  1.0.0
 */
class RPRESS_Tools_Reset_Stats extends RPRESS_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since  1.0.0
	 */
	public $export_type = '';

	/**
	 * Allows for a non-fooditem batch processing to be run.
	 * @since  1.0.0
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  1.0.0
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * Get the Export Data
	 *
	 * @since  1.0.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $data The data for the CSV file
	 */
	public function get_data() {
		global $wpdb;

		$items = $this->get_stored_data( 'rpress_temp_reset_ids' );

		if ( ! is_array( $items ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $items, $offset, $this->per_step );

		if ( $step_items ) {

			$step_ids = array(
				'customers' => array(),
				'fooditems' => array(),
				'other'     => array(),
			);

			foreach ( $step_items as $item ) {

				switch( $item['type'] ) {
					case 'customer':
						$step_ids['customers'][] = $item['id'];
						break;
					case 'fooditem':
						$step_ids['fooditems'][] = $item['id'];
						break;
					default:
						$item_type = apply_filters( 'rpress_reset_item_type', 'other', $item );
						$step_ids[ $item_type ][] = $item['id'];
						break;
				}

			}

			$sql = array();

			foreach ( $step_ids as $type => $ids ) {

				if ( empty( $ids ) ) {
					continue;
				}

				$ids = implode( ',', $ids );

				switch( $type ) {
					case 'customers':
						$table_name = $wpdb->prefix . 'rpress_customers';
						$sql[] = "DELETE FROM $table_name WHERE id IN ($ids)";
						break;
					case 'fooditems':
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0 WHERE meta_key = '_rpress_fooditem_sales' AND post_id IN ($ids)";
						$sql[] = "UPDATE $wpdb->postmeta SET meta_value = 0.00 WHERE meta_key = '_rpress_fooditem_earnings' AND post_id IN ($ids)";
						break;
					case 'other':
						$sql[] = "DELETE FROM $wpdb->posts WHERE id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->postmeta WHERE post_id IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->comments WHERE comment_post_ID IN ($ids)";
						$sql[] = "DELETE FROM $wpdb->commentmeta WHERE comment_id NOT IN (SELECT comment_ID FROM $wpdb->comments)";
						break;
				}

				if ( ! in_array( $type, array( 'customers', 'fooditems', 'other' ) ) ) {
					// Allows other types of custom post types to filter on their own post_type
					// and add items to the query list, for the IDs found in their post type.
					$sql = apply_filters( 'rpress_reset_add_queries_' . $type, $sql, $ids );
				}

			}

			if ( ! empty( $sql ) ) {
				foreach ( $sql as $query ) {
					$wpdb->query( $query );
				}
			}

			return true;

		}

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since  1.0.0
	 * @return int
	 */
	public function get_percentage_complete() {

		$items = $this->get_stored_data( 'rpress_temp_reset_ids', false );
		$total = count( $items );

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since  1.0.0
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			update_option( 'rpress_earnings_total', 0 );
			delete_transient( 'rpress_earnings_total' );
			delete_transient( 'rpress_estimated_monthly_stats' . true );
			delete_transient( 'rpress_estimated_monthly_stats' . false );
			$this->delete_data( 'rpress_temp_reset_ids' );

			// Reset the sequential order numbers
			if ( rpress_get_option( 'enable_sequential' ) ) {
				delete_option( 'rpress_last_payment_number' );
			}

			$this->done    = true;
			$this->message = __( 'Customers, earnings, sales, discounts and logs successfully reset.', 'restropress' );
			return false;
		}
	}

	public function headers() {
		ignore_user_abort( true );

		if ( ! rpress_is_func_disabled( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}
	}

	/**
	 * Perform the export
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		rpress_die();
	}

	public function pre_fetch() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'rpress_temp_reset_ids' );
		}

		$items = get_option( 'rpress_temp_reset_ids', false );

		if ( false === $items ) {
			$items = array();

			$rpress_types_for_reset = array( 'fooditem', 'rpress_log', 'rpress_payment', 'rpress_discount' );
			$rpress_types_for_reset = apply_filters( 'rpress_reset_store_post_types', $rpress_types_for_reset );

			$args = apply_filters( 'rpress_tools_reset_stats_total_args', array(
				'post_type'      => $rpress_types_for_reset,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			) );

			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$items[] = array(
					'id'   => (int) $post->ID,
					'type' => $post->post_type,
				);
			}

			$customer_args = array( 'number' => -1 );
			$customers     = RPRESS()->customers->get_customers( $customer_args );
			foreach ( $customers as $customer ) {
				$items[] = array(
					'id'   => (int) $customer->id,
					'type' => 'customer',
				);
			}

			// Allow filtering of items to remove with an unassociative array for each item
			// The array contains the unique ID of the item, and a 'type' for you to use in the execution of the get_data method
			$items = apply_filters( 'rpress_reset_store_items', $items );

			$this->store_data( 'rpress_temp_reset_ids', $items );
		}

	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  1.0.0
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  1.0.0
	 * @param  string $key   The option_name
	 * @param  mixed  $value  The value to store
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s', '%s', '%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  1.0.0
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}
