<?php
/**
 * File RestroPress Log View Class
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * RPRESS_File_RestroPress_Log_Table Class
 *
 * Renders the file fooditems log view
 *
 * @since  1.0.0
 */
class RPRESS_File_RestroPress_Log_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $per_page = 15;

	/**
	 * Are we searching for files?
	 *
	 * @var bool
	 * @since  1.0.0
	 */
	public $file_search = false;

	/**
	 * Store each unique product's files so they only need to be queried once
	 *
	 * @var array
	 * @since  1.0.0
	 */
	private $queried_files = array();

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => rpress_get_label_singular(),
			'plural'   => rpress_get_label_plural(),
			'ajax'     => false,
		) );

		add_action( 'rpress_log_view_actions', array( $this, 'fooditems_filter' ) );
	}

	/**
	 * Show the search field
	 *
	 * @since  1.0.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since  1.0.0
	 *
	 * @param array $item Contains all the data of the log item
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$base_url = remove_query_arg( 'paged' );
		switch ( $column_name ) {
			case 'fooditem' :
				$fooditem      = new RPRESS_RestroPress( $item[ $column_name ] );
				$column_value  = $fooditem->get_name();

				if ( false !== $item['price_id'] ) {
					$column_value .= ' &mdash; ' . rpress_get_price_option_name( $fooditem->ID, $item['price_id'] );
				}

				return '<a href="' . add_query_arg( 'fooditem', $fooditem->ID, $base_url ) . '" >' . $column_value . '</a>';
			case 'customer' :
				return '<a href="' . add_query_arg( 'customer', $item[ 'customer' ]->id, $base_url ) . '">' . $item['customer']->name . '</a>';
			case 'payment_id' :
				return $item['payment_id'] !== false ? '<a href="' . admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details&id=' . $item['payment_id'] ) . '">' . rpress_get_payment_number( $item['payment_id'] ) . '</a>' : '';
			case 'ip' :
				return '<a href="https://ipinfo.io/' . $item['ip']  . '" target="_blank" rel="noopener noreferrer">' . $item['ip']  . '</a>';
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since  1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => __( 'Log ID', 'restropress' ),
			'fooditem'   => rpress_get_label_singular(),
			'customer'   => __( 'Customer', 'restropress' ),
			'payment_id' => __( 'Payment ID', 'restropress' ),
			'file'       => __( 'File', 'restropress' ),
			'ip'         => __( 'IP Address', 'restropress' ),
			'date'       => __( 'Date', 'restropress' ),
		);
		return $columns;
	}

	/**
	 * Retrieves the customer we are filtering logs by, if any
	 *
	 * @since  1.0.0
	 * @return mixed int If customer ID, string If Email, false if not present
	 */
	public function get_filtered_customer() {
		$ret = false;

		if( isset( $_GET['customer'] ) ) {
			$customer = new RPRESS_Customer( sanitize_text_field( $_GET['customer'] ) );
			if ( ! empty( $customer->id ) ) {
				$ret = $customer->id;
			}
		}

		return $ret;
	}

	/**
	 * Retrieves the ID of the fooditem we're filtering logs by
	 *
	 * @since  1.0.0
	 * @return int RestroPress ID
	 */
	public function get_filtered_fooditem() {
		return ! empty( $_GET['fooditem'] ) ? absint( $_GET['fooditem'] ) : false;
	}

	/**
	 * Retrieves the ID of the payment we're filtering logs by
	 *
	 * @since  1.0.0
	 * @return int Payment ID
	 */
	public function get_filtered_payment() {
		return ! empty( $_GET['payment'] ) ? absint( $_GET['payment'] ) : false;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @since  1.0.0
	 * @return String The search string
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : '';
	}

	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query, user query, or fooditem query
	 *
	 * @since  1.0.0
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		$customer_id = $this->get_filtered_customer();
		$payment     = $this->get_filtered_payment();
		$meta_query  = array();

		if ( $payment ) {
			// Show only logs from a specific payment
			$meta_query[] = array(
				'key'   => '_rpress_log_payment_id',
				'value' => $payment,
			);
		}

		if ( ! empty( $customer_id ) ) {
			$search = $customer_id;
		} else {
			$search = $this->get_search();
		}

		if ( ! empty( $search ) ) {
			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {
				// This is an IP address search
				$key     = '_rpress_log_ip';
				$compare = '=';
			} else if ( is_email( $search ) ) {
				$customer = new RPRESS_Customer( $search );
				if ( ! empty( $customer->id ) ) {
					$key     = '_rpress_log_customer_id';
					$search  = $customer->id;
					$compare = '=';
				}
			} else {
				if ( is_numeric( $search ) ) {
					$customer = new RPRESS_Customer( $search );

					if ( ! empty( $customer->id ) ) {
						$key     = '_rpress_log_customer_id';
						$search  = $customer->id;
						$compare = '=';
					} else {
						$this->file_search = true;
					}

				}

			}

			if ( ! $this->file_search ) {
				// Meta query only works for non file name searche
				$meta_query[] = array(
					'key'     => $key,
					'value'   => $search,
					'compare' => $compare,
				);
			}
		}

		return $meta_query;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since  1.0.0
	 * @return int Current page number
	 */
	function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		rpress_log_views();
	}

	/**
	 * Sets up the fooditems filter
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function fooditems_filter() {
		$fooditems = get_posts( array(
			'post_type'              => 'fooditem',
			'post_status'            => 'any',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		if ( $fooditems ) {
			echo '<select name="fooditem" id="rpress-log-fooditem-filter">';
				echo '<option value="0">' . __( 'All', 'restropress' ) . '</option>';
				foreach ( $fooditems as $fooditem ) {
					echo '<option value="' . $fooditem . '"' . selected( $fooditem, $this->get_filtered_fooditem() ) . '>' . esc_html( get_the_title( $fooditem ) ) . '</option>';
				}
			echo '</select>';
		}
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since  1.0.0
	 * @global object $rpress_logs RPRESS Logs Object
	 * @return array $logs_data Array of all the Log entires
	 */
	function get_logs() {
		global $rpress_logs, $wpdb;

		// Prevent the queries from getting cached. Without this there are occasional memory issues for some installs
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$fooditem  = empty( $_GET['s'] ) ? $this->get_filtered_fooditem() : null;
		$log_query = array(
			'post_parent'            => $fooditem,
			'log_type'               => 'file_fooditem',
			'paged'                  => $paged,
			'meta_query'             => $this->get_meta_query(),
			'posts_per_page'         => $this->per_page,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		);

		$logs = $rpress_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$meta        = get_post_custom( $log->ID );
				$payment_id  = isset( $meta['_rpress_log_payment_id'] ) ? $meta['_rpress_log_payment_id'][0] : false;
				$ip          = $meta['_rpress_log_ip'][0];
				$user_id     = isset( $meta['_rpress_log_user_id'] ) ? (int) $meta['_rpress_log_user_id'][0] : null;
				$customer_id = isset( $meta['_rpress_log_customer_id'] ) ? (int) $meta['_rpress_log_customer_id'][0] : null;
				$price_id    = rpress_has_variable_prices( $log->post_parent ) ? get_post_meta( $log->ID, '_rpress_log_price_id', true ) : false;

				if( ! array_key_exists( $log->post_parent, $this->queried_files ) ) {
					$files   = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT meta_value from $wpdb->postmeta WHERE post_id = %d and meta_key = 'rpress_fooditem_files'", $log->post_parent ) ) );
					$this->queried_files[ $log->post_parent ] = $files;
				} else {
					$files   = $this->queried_files[ $log->post_parent ];
				}

				// Filter the fooditem files
				$files = apply_filters( 'rpress_log_file_fooditem_fooditem_files', $files, $log, $meta );

				$file_id   = (int) $meta['_rpress_log_file_id'][0];
				$file_id   = $file_id !== false ? $file_id : 0;

				// Filter the $file_id
				$file_id = apply_filters( 'rpress_log_file_fooditem_file_id', $file_id, $log );

				$file_name = isset( $files[ $file_id ]['name'] ) ? $files[ $file_id ]['name'] : null;

				if ( ( $this->file_search && strpos( strtolower( $file_name ), strtolower( $this->get_search() ) ) !== false ) || ! $this->file_search ) {
					$logs_data[] = array(
						'ID'         => $log->ID,
						'fooditem'   => $log->post_parent,
						'price_id'   => $price_id,
						'customer'   => new RPRESS_Customer( $customer_id ),
						'payment_id' => $payment_id,
						'file'       => $file_name,
						'ip'         => $ip,
						'date'       => $log->post_date,
					);
				}
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.0
	 * @global object $rpress_logs RPRESS Logs Object
	 * @uses RPRESS_File_RestroPress_Log_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses RPRESS_File_RestroPress_Log_Table::get_pagenum()
	 * @uses RPRESS_File_RestroPress_Log_Table::get_logs()
	 * @uses RPRESS_File_RestroPress_Log_Table::get_log_count()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	function prepare_items() {
		global $rpress_logs;

		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = $rpress_logs->get_log_count( $this->get_filtered_fooditem(), 'file_fooditem', $this->get_meta_query() );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}

	/**
	 * Since our "bulk actions" are navigational, we want them to always show, not just when there's items
	 *
	 * @since  1.0.0
	 * @return bool
	 */
	public function has_items() {
		return true;
	}
}
