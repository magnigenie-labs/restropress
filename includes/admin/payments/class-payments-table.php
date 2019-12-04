<?php
/**
 * Order History Table Class
 *
 * @package     RPRESS
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * RPRESS_Payment_History_Table Class
 *
 * Renders the Order History table on the Order History page
 *
 * @since  1.0.0
 */
class RPRESS_Payment_History_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since  1.0.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $total_count;

	/**
	 * Total number of delivered payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $delivered_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $pending_count;

	/**
	 * Total number of paid payments
	 *
	 * @var int
	 * @since 1.0.0
	 */
	public $paid_count;

	/**
	 * Total number of out for deliver payments
	 *
	 * @var int
	 * @since  1.0.0
	 */
	public $out_for_deliver_count;

	/**
	 * Get things started
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Payment_History_Table::get_payment_counts()
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

		$this->get_payment_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' );
	}

	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : null;
		$status     = isset( $_GET['status'] )      ? $_GET['status'] : '';

		$all_gateways     = rpress_get_payment_gateways();
		$gateways         = array();
		$selected_gateway = isset( $_GET['gateway'] ) ? sanitize_text_field( $_GET['gateway'] ) : 'all';

		if ( ! empty( $all_gateways ) ) {
			$gateways['all'] = __( 'All Gateways', 'restropress' );

			foreach( $all_gateways as $slug => $admin_label ) {
				$gateways[ $slug ] = $admin_label['admin_label'];
			}
		}

		/**
		 * Allow gateways that aren't registered the standard way to be displayed in the dropdown.
		 *
		 * @since  1.0.0
		 */
		$gateways = apply_filters( 'rpress_payments_table_gateways', $gateways );
		?>
		<div id="rpress-payment-filters">
			<span id="rpress-payment-date-filters">
				<span>
					<label for="start-date"><?php _e( 'Start Date:', 'restropress' ); ?></label>
					<input type="text" id="start-date" name="start-date" class="rpress_datepicker" value="<?php echo $start_date; ?>" placeholder="mm/dd/yyyy"/>
				</span>
				<span>
					<label for="end-date"><?php _e( 'End Date:', 'restropress' ); ?></label>
					<input type="text" id="end-date" name="end-date" class="rpress_datepicker" value="<?php echo $end_date; ?>" placeholder="mm/dd/yyyy"/>
				</span>
			</span>
			<span id="rpress-payment-gateway-filter">
				<?php
				if ( ! empty( $gateways ) ) {
					echo RPRESS()->html->select( array(
						'options'          => $gateways,
						'name'             => 'gateway',
						'id'               => 'gateway',
						'selected'         => $selected_gateway,
						'show_option_all'  => false,
						'show_option_none' => false
					) );
				}
				?>
			</span>
			<span id="rpress-payment-after-core-filters">
				<?php do_action( 'rpress_payment_advanced_filters_after_fields' ); ?>
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'restropress' ); ?>"/>
			</span>
			<?php if( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
			<?php endif; ?>
			<?php if( ! empty( $start_date ) || ! empty( $end_date ) || 'all' !== $selected_gateway ) : ?>
				<a href="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'restropress' ); ?></a>
			<?php endif; ?>
			<?php do_action( 'rpress_payment_advanced_filters_row' ); ?>
			<?php $this->search_box( __( 'Search', 'restropress' ), 'rpress-payments' ); ?>
		</div>

<?php
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
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
		<p class="search-box">
			<?php do_action( 'rpress_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?><br/>
		</p>
<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @since  1.0.0
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current          = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count      = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$delivered_count   = '&nbsp;<span class="count">(' . $this->delivered_count . ')</span>';
		$pending_count    = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$paid_count = '&nbsp;<span class="count">(' . $this->paid_count  . ')</span>';
		$out_for_deliver_count = '&nbsp;<span class="count">(' . $this->out_for_deliver_count . ')</span>';
		$views = array(
			'all'        => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All','restropress' ) . $total_count ),
			'publish'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'publish', 'paged' => FALSE ) ), $current === 'deleverd' ? ' class="current"' : '', __('Delivered','restropress' ) . $delivered_count ),
			'pending'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __('Pending','restropress' ) . $pending_count ),
			'paid' => sprintf('<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'paid', 'paged' => FALSE ) ), $current === 'paid' ? ' class="current"' : '', __('Paid','restropress' ) . $paid_count ),
			'processing' => sprintf('<a href="%s"%s>%s</a>', add_query_arg( array( 'status' => 'processing', 'paged' => FALSE ) ), $current === 'processing' ? ' class="current"' : '', __('Processing','restropress' ) . $out_for_deliver_count)
		);

		return apply_filters( 'rpress_payments_table_views', $views );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since  1.0.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />', //Render a checkbox instead of text
			'ID'       => __( 'ID', 'restropress' ),
			'email'    => __( 'Email', 'restropress' ),
			'amount'   => __( 'Amount', 'restropress' ),
			'date'     => __( 'Date', 'restropress' ),
			'customer' => __( 'Customer', 'restropress' ),
			'delivery' => __( 'Order Type', 'restropress' ),
			'status'   => __( 'Status', 'restropress' ),
		);

		return apply_filters( 'rpress_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @since  1.0.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID'     => array( 'ID', true ),
			'amount' => array( 'amount', false ),
			'date'   => array( 'date', false ),
		);
		return apply_filters( 'rpress_payments_table_sortable_columns', $columns );
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
	 * @param array $payment Contains all the data of the payment
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $payment, $column_name ) {
		switch ( $column_name ) {
			case 'amount' :
				$amount  = $payment->total;
				$amount  = ! empty( $amount ) ? $amount : 0;
				$value   = rpress_currency_filter( rpress_format_amount( $amount ), rpress_get_payment_currency_code( $payment->ID ) );
				break;
			case 'date' :
				$date    = strtotime( $payment->date );
				$value   = date_i18n( get_option( 'date_format' ), $date );
				break;
			case 'status' :
				$payment = get_post( $payment->ID );
				$value   = rpress_get_payment_status( $payment, true );
				break;
			case 'delivery' :
				$value = rpress_get_delivery_type( $payment->ID );
				break;
			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;

		}
		return apply_filters( 'rpress_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Render the Email Column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the Email column
	 */
	public function column_email( $payment ) {

		$row_actions = array();

		$email = rpress_get_payment_user_email( $payment->ID );

		// Add search term string back to base URL
		$search_terms = ( isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( rpress_is_payment_complete( $payment->ID ) && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg( array( 'rpress-action' => 'email_links', 'purchase_id' => $payment->ID ), $this->base_url ) . '">' . __( 'Resend Purchase Receipt', 'restropress' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'rpress-action' => 'delete_payment', 'purchase_id' => $payment->ID ), $this->base_url ), 'rpress_payment_nonce') . '">' . __( 'Delete', 'restropress' ) . '</a>';

		$row_actions = apply_filters( 'rpress_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'restropress' );
		}

		$value = $email . $this->row_actions( $row_actions );

		return apply_filters( 'rpress_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_cb( $payment ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$payment->ID
		);
	}

	/**
	 * Render the ID column
	 *
	 * @since  1.0.0
	 * @param array $payment Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_ID( $payment ) {
		return '<a href="' . add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details' ) ) . '">' . $payment->ID . '</a>';
	}

	/**
	 * Render the Customer Column
	 *
	 * @since 2.4.3
	 * @param array $payment Contains all the data of the payment
	 * @return string Data shown in the User column
	 */
	public function column_customer( $payment ) {

		$customer_id = rpress_get_payment_customer_id( $payment->ID );

		if( ! empty( $customer_id ) ) {
			$customer    = new RPRESS_Customer( $customer_id );
			$value = '<a href="' . esc_url( admin_url( "edit.php?post_type=fooditem&page=rpress-customers&view=overview&id=$customer_id" ) ) . '">' . $customer->name . '</a>';
		} else {
			$email = rpress_get_payment_user_email( $payment->ID );
			$value = '<a href="' . esc_url( admin_url( "edit.php?post_type=fooditem&page=rpress-payment-history&s=$email" ) ) . '">' . __( '(customer missing)', 'restropress' ) . '</a>';
		}
		return apply_filters( 'rpress_payments_table_column', $value, $payment->ID, 'user' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since  1.0.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete',                'restropress' ),
			'set-status-publish'     => __( 'Set To Completed',      'restropress' ),
			'set-status-pending'     => __( 'Set To Pending',        'restropress' ),
			'set-status-processing'  => __( 'Set To Processing',     'restropress' ),
			'set-status-refunded'    => __( 'Set To Refunded',       'restropress' ),
			'set-status-paid'     	 => __( 'Set To Paid',        'restropress' ),
			'set-status-failed'      => __( 'Set To Delivered',         'restropress' ),
			'set-status-preapproval' => __( 'Set To Preapproval',    'restropress' ),
			'set-status-cancelled'   => __( 'Set To Cancelled',      'restropress' ),
			'resend-receipt'         => __( 'Resend Email Receipts', 'restropress' )
		);

		return apply_filters( 'rpress_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['payment'] ) ? $_GET['payment'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) )
			$ids = array( $ids );


		if( empty( $action ) )
			return;

		foreach ( $ids as $id ) {
			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				rpress_delete_purchase( $id );
			}

			if ( 'set-status-publish' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'publish' );
			}

			if ( 'set-status-pending' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'pending' );
			}

			if ( 'set-status-processing' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'processing' );
			}

			if ( 'set-status-refunded' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'refunded' );
			}

			if ( 'set-status-paid' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'paid' );
			}

			if ( 'set-status-failed' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'failed' );
			}

			if ( 'set-status-abandoned' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'abandoned' );
			}

			if ( 'set-status-preapproval' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'preapproval' );
			}

			if ( 'set-status-cancelled' === $this->current_action() ) {
				rpress_update_payment_status( $id, 'cancelled' );
			}

			if( 'resend-receipt' === $this->current_action() ) {
				rpress_email_purchase_receipt( $id, false );
			}

			do_action( 'rpress_payments_table_do_bulk_action', $id, $this->current_action() );
		}

	}

	/**
	 * Retrieve the payment counts
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function get_payment_counts() {

		global $wp_query;

		$args = array();

		if( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif( isset( $_GET['customer'] ) ) {
			$args['customer'] = absint( $_GET['customer'] );
		} elseif( isset( $_GET['s'] ) ) {

			$is_user  = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;

			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = sanitize_text_field( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		if ( ! empty( $_GET['gateway'] ) && $_GET['gateway'] !== 'all' ) {
			$args['gateway'] = $_GET['gateway'];
		}

		$payment_count          	= rpress_count_payments( $args );
		$this->delivered_count   	= (isset($payment_count->delivered))? $payment_count->delivered : 0;
		$this->pending_count    	=  (isset($payment_count->pending)) ? $payment_count->pending : 0 ;
		$this->paid_count 			=  (isset($payment_count->paid)) ? $payment_count->paid : 0 ;
		$this->out_for_deliver_count   	=  (isset($payment_count->processing)) ? $payment_count->processing : 0 ;
		foreach( $payment_count as $count ) {
			$this->total_count += $count;
		}
	}

	/**
	 * Retrieve all the data for all the payments
	 *
	 * @since  1.0.0
	 * @return array $payment_data Array of all the data for the payments
	 */
	public function payments_data() {

		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] )     ? urldecode( $_GET['orderby'] )              : 'ID';
		$order      = isset( $_GET['order'] )       ? $_GET['order']                             : 'DESC';
		$user       = isset( $_GET['user'] )        ? $_GET['user']                              : null;
		$customer   = isset( $_GET['customer'] )    ? $_GET['customer']                          : null;
		$status     = isset( $_GET['status'] )      ? $_GET['status']                            : rpress_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] )    ? $_GET['meta_key']                          : null;
		$year       = isset( $_GET['year'] )        ? $_GET['year']                              : null;
		$month      = isset( $_GET['m'] )           ? $_GET['m']                                 : null;
		$day        = isset( $_GET['day'] )         ? $_GET['day']                               : null;
		$search     = isset( $_GET['s'] )           ? sanitize_text_field( $_GET['s'] )          : null;
		$start_date = isset( $_GET['start-date'] )  ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] )    ? sanitize_text_field( $_GET['end-date'] )   : $start_date;
		$gateway    = isset( $_GET['gateway'] )     ? sanitize_text_field( $_GET['gateway'] )    : null;

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @since  1.0.0
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'rpress_payments_table_search_gateway', $gateway );

		if( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching
		}

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'customer'   => $customer,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'gateway'    => $gateway
		);

		if( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {

			$args['search_in_notes'] = true;
			$args['s'] = trim( str_replace( 'txn:', '', $args['s'] ) );

		}

		$p_query  = new RPRESS_Payments_Query( $args );

		return $p_query->get_payments();

	}

	/**
	 * Setup the final data for the table
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Payment_History_Table::get_columns()
	 * @uses RPRESS_Payment_History_Table::get_sortable_columns()
	 * @uses RPRESS_Payment_History_Table::payments_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->payments_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'deleverd':
				$total_items = $this->delivered_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'out_for_deliver':
				$total_items = $this->out_for_deliver_count;
			break;
			case 'paid':
				$total_items = $this->paid_count;
			break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-RPRESS status
				$count       = wp_count_posts( 'rpress_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
