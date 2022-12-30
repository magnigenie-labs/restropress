<?php
/**
 * Customer Reports Table Class
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * RPRESS_Customer_Reports_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since 1.0
 */
class RPRESS_Customer_Reports_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * Number of customers found
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $count = 0;

	/**
	 * Total customers
	 *
	 * @var int
	 * @since  1.0.05
	 */
	public $total = 0;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Customer', 'restropress' ),     // Singular name of the listed records
			'plural'    => __( 'Customers', 'restropress' ),    // Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

	}

	/**
	 * Show the search field
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( sanitize_text_field( $_REQUEST['orderby'] ) ). '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr(  sanitize_text_field( $_REQUEST['order'] ) ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'num_purchases' :
				$value = '<a href="' .
					admin_url( 'admin.php?page=rpress-payment-history&user=' . urlencode( $item['email'] )
				) . '">' . esc_html( $item['num_purchases'] ) . '</a>';
				break;

			case 'amount_spent' :
				$value = rpress_currency_filter( rpress_format_amount( $item[ $column_name ] ) );
				break;

			case 'fooditems' :
				$user = ! empty( $item['user_id'] ) ? $item['user_id'] : $item['email'];
				$value = '<a href="' . esc_url( admin_url( 'admin.php?page=rpress-reports&tab=logs&user=' . urlencode( $user ) ) ) . '">' . __( 'View fooditem log', 'restropress' ) . '</a>';
				break;
			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;
		}
		return apply_filters( 'rpress_report_column_' . $column_name, $value, $item['id'] );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'     		=> __( 'Name', 'restropress' ),
			'id'     		=> __( 'ID', 'restropress' ),
			'email'     	=> __( 'Email', 'restropress' ),
			'num_purchases' => __( 'Purchases', 'restropress' ),
			'amount_spent'  => __( 'Total Spent', 'restropress' ),
			'fooditems'=> __( 'Food Items', 'restropress' )
		);

		return apply_filters( 'rpress_report_customer_columns', $columns );

	}

	/**
	 * Get the sortable columns
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'id' 	        => array( 'id', true ),
			'name' 	        => array( 'name', true ),
			'num_purchases' => array( 'purchase_count', false ),
			'amount_spent' 	=> array( 'purchase_value', false ),
		);
	}

	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		rpress_report_views();
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 1.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since  1.0.0
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( sanitize_text_field( $_GET['s'] ) ) ): false;
	}

	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since 1.0
	  * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {
		global $wpdb;

		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] )   ? sanitize_text_field( $_GET['order'] )   : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args    = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby
		);

		if( is_email( $search ) ) {
			$args['email'] = $search;
		} elseif( is_numeric( $search ) ) {
			$args['id']    = $search;
		}

		$customers = RPRESS()->customers->get_customers( $args );

		if ( $customers ) {

			$this->count = count( $customers );

			foreach ( $customers as $customer ) {

				$user_id = ! empty( $customer->user_id ) ? absint( $customer->user_id ) : 0;

				$data[] = array(
					'id'            => $customer->id,
					'user_id'       => $user_id,
					'name'          => $customer->name,
					'email'         => $customer->email,
					'num_purchases'	=> $customer->purchase_count,
					'amount_spent'	=> $customer->purchase_value
				);
			}
		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.0
	 * @uses RPRESS_Customer_Reports_Table::get_columns()
	 * @uses WP_List_Table::get_sortable_columns()
	 * @uses RPRESS_Customer_Reports_Table::get_pagenum()
	 * @uses RPRESS_Customer_Reports_Table::get_total_customers()
	 * @return void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reports_data();

		$this->total = rpress_count_total_customers();

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page )
		) );
	}
}