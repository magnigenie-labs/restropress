<?php
/**
 * Logs UI
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Sales Log View
 *
 * @since  1.0.0
 * @uses RPRESS_Sales_Log_Table::prepare_items()
 * @uses RPRESS_Sales_Log_Table::display()
 * @return void
 */
function rpress_logs_view_sales() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-sales-logs-list-table.php' );

	$logs_table = new RPRESS_Sales_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}
add_action( 'rpress_logs_view_sales', 'rpress_logs_view_sales' );

/**
 *  Logs
 *
 * @since  1.0.0
 * @uses RPRESS_File_RestroPress_Log_Table::prepare_items()
 * @uses RPRESS_File_RestroPress_Log_Table::search_box()
 * @uses RPRESS_File_RestroPress_Log_Table::display()
 * @return void
 */
function rpress_logs_view_file_fooditems() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-file-fooditems-logs-list-table.php' );

	$logs_table = new RPRESS_File_RestroPress_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="wrap">
		<?php do_action( 'rpress_logs_file_fooditems_top' ); ?>
		<form id="rpress-logs-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-reports&tab=logs' ); ?>">
			<?php
			$logs_table->search_box( __( 'Search', 'restropress' ), 'rpress-payments' );
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="fooditem" />
			<input type="hidden" name="page" value="rpress-reports" />
			<input type="hidden" name="tab" value="logs" />
		</form>
		<?php do_action( 'rpress_logs_file_fooditems_bottom' ); ?>
	</div>
<?php
}
add_action( 'rpress_logs_view_file_fooditems', 'rpress_logs_view_file_fooditems' );

/**
 * Gateway Error Logs
 *
 * @since  1.0.0
 * @uses RPRESS_File_RestroPress_Log_Table::prepare_items()
 * @uses RPRESS_File_RestroPress_Log_Table::display()
 * @return void
 */
function rpress_logs_view_gateway_errors() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateway-error-logs-list-table.php' );

	$logs_table = new RPRESS_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();
}
add_action( 'rpress_logs_view_gateway_errors', 'rpress_logs_view_gateway_errors' );




/**
 * Default Log Views
 *
 * @since  1.0.0
 * @return array $views Log Views
 */
function rpress_log_default_views() {
	$views = array(
		'file_fooditems'  => __( 'File RestroPress', 'restropress' ),
		'sales' 		  => __( 'Sales', 'restropress' ),
		'gateway_errors'  => __( 'Payment Errors', 'restropress' ),
	);

	$views = apply_filters( 'rpress_log_views', $views );

	return $views;
}

/**
 * Renders the Reports page views drop down
 *
 * @since 1.0
 * @return void
*/
function rpress_log_views() {
	$views        = rpress_log_default_views();
	$current_view = isset( $_GET['view'] ) && array_key_exists( $_GET['view'], rpress_log_default_views() ) ? sanitize_text_field( $_GET['view'] ) : 'file_fooditems';
	?>
	<form id="rpress-logs-filter" method="get" action="edit.php">
		<select id="rpress-logs-view" name="view">
			<option value="-1"><?php _e( 'Log Type', 'restropress' ); ?></option>
			<?php foreach ( $views as $view_id => $label ): ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'rpress_log_view_actions' ); ?>

		<input type="hidden" name="post_type" value="fooditem"/>
		<input type="hidden" name="page" value="rpress-reports"/>
		<input type="hidden" name="tab" value="logs"/>

		<?php submit_button( __( 'Apply', 'restropress' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
}