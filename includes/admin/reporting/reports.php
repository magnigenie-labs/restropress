<?php
/**
 * Admin Reports Page
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports Page
 *
 * Renders the reports page contents.
 *
 * @since 1.0
 * @return void
*/
function rpress_reports_page() {
	$current_page = admin_url( 'edit.php?post_type=fooditem&page=rpress-reports' );
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'reports';
	?>
	<div class="wrap">
		<h2><?php _e( 'RestroPress Reports', 'restropress' ); ?></h2>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo add_query_arg( array( 'tab' => 'reports', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'reports' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'restropress' ); ?></a>
			<?php if ( current_user_can( 'export_shop_reports' ) ) { ?>
				<a href="<?php echo add_query_arg( array( 'tab' => 'export', 'settings-updated' => false ), $current_page ); ?>" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'restropress' ); ?></a>
			<?php } ?>
			
			<?php do_action( 'rpress_reports_tabs' ); ?>
		</h2>

		<?php
		do_action( 'rpress_reports_page_top' );
		do_action( 'rpress_reports_tab_' . $active_tab );
		do_action( 'rpress_reports_page_bottom' );
		?>
	</div><!-- .wrap -->
	<?php
}

/**
 * Default Report Views
 *
 * @since  1.0.0
 * @return array $views Report Views
 */
function rpress_reports_default_views() {
	$views = array(
		'earnings'   => __( 'Earnings', 'restropress' ),
		'categories' => __( 'Earnings by Category', 'restropress' ),
		'fooditems'  => rpress_get_label_plural(),
		'gateways'   => __( 'Payment Methods', 'restropress' ),
		'taxes'      => __( 'Taxes', 'restropress' ),
	);

	$views = apply_filters( 'rpress_report_views', $views );

	return $views;
}

/**
 * Default Report Views
 *
 * Checks the $_GET['view'] parameter to ensure it exists within the default allowed views.
 *
 * @param string $default Default view to use.
 *
 * @since  1.0.0.6
 * @return string $view Report View
 *
 */
function rpress_get_reporting_view( $default = 'earnings' ) {

	if ( ! isset( $_GET['view'] ) || ! in_array( $_GET['view'], array_keys( rpress_reports_default_views() ) ) ) {
		$view = $default;
	} else {
		$view = $_GET['view'];
	}

	return apply_filters( 'rpress_get_reporting_view', $view );
}

/**
 * Renders the Reports page
 *
 * @since 1.0
 * @return void
 */
function rpress_reports_tab_reports() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		wp_die( __( 'You do not have permission to access this report', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	$current_view = 'earnings';
	$views        = rpress_reports_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $views ) )
		$current_view = $_GET['view'];

	do_action( 'rpress_reports_view_' . $current_view );

}
add_action( 'rpress_reports_tab_reports', 'rpress_reports_tab_reports' );

/**
 * Renders the Reports Page Views Drop Downs
 *
 * @since 1.0
 * @return void
 */
function rpress_report_views() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$views        = rpress_reports_default_views();
	$current_view = isset( $_GET['view'] ) ? $_GET['view'] : 'earnings';
	?>
	<form id="rpress-reports-filter" method="get">
		<select id="rpress-reports-view" name="view">
			<option value="-1"><?php _e( 'Report Type', 'restropress' ); ?></option>
			<?php foreach ( $views as $view_id => $label ) : ?>
				<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
			<?php endforeach; ?>
		</select>

		<?php do_action( 'rpress_report_view_actions' ); ?>

		<input type="hidden" name="post_type" value="fooditem"/>
		<input type="hidden" name="page" value="rpress-reports"/>
		<?php submit_button( __( 'Show', 'restropress' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
	do_action( 'rpress_report_view_actions_after' );
}

/**
 * Renders the Reports RestroPress Table
 *
 * @since 1.0
 * @uses RPRESS_Fooditem_Reports_Table::prepare_items()
 * @uses RPRESS_Fooditem_Reports_Table::display()
 * @return void
 */
function rpress_reports_fooditems_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( isset( $_GET['fooditem-id'] ) )
		return;

	include( dirname( __FILE__ ) . '/class-fooditem-reports-table.php' );

	$fooditems_table = new RPRESS_Fooditem_Reports_Table();
	$fooditems_table->prepare_items();
	$fooditems_table->display();
}
add_action( 'rpress_reports_view_fooditems', 'rpress_reports_fooditems_table' );

/**
 * Renders the detailed report for a specific product
 *
 * @since  1.0.0
 * @return void
 */
function rpress_reports_fooditem_details() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	if( ! isset( $_GET['fooditem-id'] ) )
		return;
?>
	<div class="tablenav top">
		<div class="actions bulkactions">
			<div class="alignleft">
				<?php rpress_report_views(); ?>
			</div>&nbsp;
			<button onclick="history.go(-1);" class="button-secondary"><?php _e( 'Go Back', 'restropress' ); ?></button>
		</div>
	</div>
<?php
	rpress_reports_graph_of_fooditem( absint( $_GET['fooditem-id'] ) );
}
add_action( 'rpress_reports_view_fooditems', 'rpress_reports_fooditem_details' );


/**
 * Renders the Gateways Table
 *
 * @since 1.0
 * @uses RPRESS_Gateawy_Reports_Table::prepare_items()
 * @uses RPRESS_Gateawy_Reports_Table::display()
 * @return void
 */
function rpress_reports_gateways_table() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-gateways-reports-table.php' );

	$fooditems_table = new RPRESS_Gateawy_Reports_Table();
	$fooditems_table->prepare_items();
	$fooditems_table->display();
}
add_action( 'rpress_reports_view_gateways', 'rpress_reports_gateways_table' );


/**
 * Renders the Reports Earnings Graphs
 *
 * @since 1.0
 * @return void
 */
function rpress_reports_earnings() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php rpress_report_views(); ?></div>
	</div>
	<?php
	rpress_reports_graph();
}
add_action( 'rpress_reports_view_earnings', 'rpress_reports_earnings' );


/**
 * Renders the Reports Earnings By Category Table & Graphs
 *
 * @since 1.0
 */
function rpress_reports_categories() {
	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	include( dirname( __FILE__ ) . '/class-categories-reports-table.php' );
	?>
			<div class="inside">
				<?php

				$categories_table = new RPRESS_Categories_Reports_Table();
				$categories_table->prepare_items();
				$categories_table->display();
				?>

				<?php echo $categories_table->load_scripts(); ?>

				<div class="rpress-mix-totals">
					<div class="rpress-mix-chart">
						<strong><?php _e( 'Category Sales Mix: ', 'restropress' ); ?></strong>
						<?php $categories_table->output_sales_graph(); ?>
					</div>
					<div class="rpress-mix-chart">
						<strong><?php _e( 'Category Earnings Mix: ', 'restropress' ); ?></strong>
						<?php $categories_table->output_earnings_graph(); ?>
					</div>
				</div>

				<?php do_action( 'rpress_reports_graph_additional_stats' ); ?>

				<p class="rpress-graph-notes">
					<span>
						<em><sup>&dagger;</sup> <?php _e( 'All Parent categories include sales and earnings stats from child categories.', 'restropress' ); ?></em>
					</span>
					<span>
						<em><?php _e( 'Stats include all sales and earnings for the lifetime of the store.', 'restropress' ); ?></em>
					</span>
				</p>

			</div>
	<?php
}
add_action( 'rpress_reports_view_categories', 'rpress_reports_categories' );

/**
 * Renders the Tax Reports
 *
 * @since 1.0.0
 * @return void
 */
function rpress_reports_taxes() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	$year = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : date( 'Y' );
	?>
	<div class="tablenav top">
		<div class="alignleft actions"><?php rpress_report_views(); ?></div>
	</div>

	<div class="metabox-holder" style="padding-top: 0;">
		<div class="postbox">
			<h3><span><?php _e('Tax Report','restropress' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'This report shows the total amount collected in sales tax for the given year.', 'restropress' ); ?></p>
				<form method="get" action="<?php echo admin_url( 'edit.php' ); ?>">
					<span><?php echo $year; ?></span>: <strong><?php rpress_sales_tax_for_year( $year ); ?></strong>&nbsp;&mdash;&nbsp;
					<select name="year">
						<?php for ( $i = 2009; $i <= date( 'Y' ); $i++ ) : ?>
						<option value="<?php echo $i; ?>"<?php selected( $year, $i ); ?>><?php echo $i; ?></option>
						<?php endfor; ?>
					</select>
					<input type="hidden" name="view" value="taxes" />
					<input type="hidden" name="post_type" value="fooditem" />
					<input type="hidden" name="page" value="rpress-reports" />
					<?php submit_button( __( 'Submit', 'restropress' ), 'secondary', 'submit', false ); ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .metabox-holder -->
	<?php
}
add_action( 'rpress_reports_view_taxes', 'rpress_reports_taxes' );

/**
 * Renders the 'Export' tab on the Reports Page
 *
 * @since 1.0
 * @return void
 */
function rpress_reports_tab_export() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}
	?>
	<div id="rpress-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div id="post-body">
				<div id="post-body-content">

					<?php do_action( 'rpress_reports_tab_export_content_top' ); ?>

					<div class="postbox rpress-export-earnings-report">
						<h3><span><?php _e( 'Export Earnings Report', 'restropress' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV giving a detailed look into earnings over time.', 'restropress' ); ?></p>
							<form id="rpress-export-earnings" class="rpress-export-form rpress-import-export-form" method="post">
								<?php echo RPRESS()->html->month_dropdown( 'start_month' ); ?>
								<?php echo RPRESS()->html->year_dropdown( 'start_year' ); ?>
								<?php echo _x( 'to', 'Date one to date two', 'restropress' ); ?>
								<?php echo RPRESS()->html->month_dropdown( 'end_month' ); ?>
								<?php echo RPRESS()->html->year_dropdown( 'end_year' ); ?>
								<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>
								<input type="hidden" name="rpress-export-class" value="RPRESS_Batch_Earnings_Report_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'restropress' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox rpress-export-payment-history">
						<h3><span><?php _e('Export Payment History','restropress' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all payments recorded.', 'restropress' ); ?></p>

							<form id="rpress-export-payments" class="rpress-export-form rpress-import-export-form" method="post">
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-payment-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'restropress' ) )); ?>
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-payment-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'restropress' ) )); ?>
								<select name="status">
									<option value="any"><?php _e( 'All Statuses', 'restropress' ); ?></option>
									<?php
									$statuses = rpress_get_payment_statuses();
									foreach( $statuses as $status => $label ) {
										echo '<option value="' . $status . '">' . $label . '</option>';
									}
									?>
								</select>
								<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>
								<input type="hidden" name="rpress-export-class" value="RPRESS_Batch_Payments_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'restropress' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					

					<div class="postbox rpress-export-fooditems">
						<h3><span><?php _e('Export FoodItems in CSV','restropress' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of fooditem products.', 'restropress' ); ?></p>
							<form id="rpress-export-file-fooditems" class="rpress-export-form rpress-import-export-form" method="post">
								<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>
								<input type="hidden" name="rpress-export-class" value="RPRESS_Batch_RestroPress_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'restropress' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox rpress-export-fooditem-history">
						<h3><span><?php _e('Export Order History in CSV','restropress' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of file fooditems. To fooditem a CSV for all file fooditems, leave "Choose a Download" as it is.', 'restropress' ); ?></p>
							<form id="rpress-export-file-fooditems" class="rpress-export-form rpress-import-export-form" method="post">
								<?php echo RPRESS()->html->product_dropdown( array( 'name' => 'fooditem_id', 'id' => 'rpress_file_fooditem_export_fooditem', 'chosen' => true ) ); ?>
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-file-fooditem-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'restropress' ) )); ?>
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-file-fooditem-export-end', 'name' => 'end', 'placeholder' => __( 'Choose end date', 'restropress' ) )); ?>
								<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>
								<input type="hidden" name="rpress-export-class" value="RPRESS_Batch_File_RestroPress_Export"/>
								<input type="submit" value="<?php _e( 'Generate CSV', 'restropress' ); ?>" class="button-secondary"/>
							</form>
						</div><!-- .inside -->
					</div><!-- .postbox -->

					<div class="postbox rpress-export-payment-history">
						<h3><span><?php _e('Export Sales', 'restropress' ); ?></span></h3>
						<div class="inside">
							<p><?php _e( 'Download a CSV of all sales.', 'restropress' ); ?></p>

							<form id="rpress-export-sales" class="rpress-export-form rpress-import-export-form" method="post">
								<?php echo RPRESS()->html->product_dropdown( array( 'name' => 'fooditem_id', 'id' => 'rpress_sales_export_fooditem', 'chosen' => true ) ); ?>
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-sales-export-start', 'name' => 'start', 'placeholder' => __( 'Choose start date', 'restropress' ) )); ?>
								<?php echo RPRESS()->html->date_field( array( 'id' => 'rpress-sales-export-end','name' => 'end', 'placeholder' => __( 'Choose end date', 'restropress' ) )); ?>
								<?php wp_nonce_field( 'rpress_ajax_export', 'rpress_ajax_export' ); ?>
								<input type="hidden" name="rpress-export-class" value="RPRESS_Batch_Sales_Export"/>
								<span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'restropress' ); ?>" class="button-secondary"/>
									<span class="spinner"></span>
								</span>
							</form>

						</div><!-- .inside -->
					</div><!-- .postbox -->

					<?php do_action( 'rpress_reports_tab_export_content_bottom' ); ?>

				</div><!-- .post-body-content -->
			</div><!-- .post-body -->
		</div><!-- .metabox-holder -->
	</div><!-- #rpress-dashboard-widgets-wrap -->
	<?php
}
add_action( 'rpress_reports_tab_export', 'rpress_reports_tab_export' );

/**
 * Renders the Reports page
 *
 * @since 1.0
 * @return void
 */
function rpress_reports_tab_logs() {

	if( ! current_user_can( 'view_shop_reports' ) ) {
		return;
	}

	require( RP_PLUGIN_DIR . 'includes/admin/reporting/logs.php' );

	$current_view = 'file_fooditems';
	$log_views    = rpress_log_default_views();

	if ( isset( $_GET['view'] ) && array_key_exists( $_GET['view'], $log_views ) )
		$current_view = $_GET['view'];

	do_action( 'rpress_logs_view_' . $current_view );
}
add_action( 'rpress_reports_tab_logs', 'rpress_reports_tab_logs' );

/**
 * Retrieves estimated monthly earnings and sales
 *
 * @since 1.0
 *
 * @param bool  $include_taxes If the estimated earnings should include taxes
 * @return array
 */
function rpress_estimated_monthly_stats( $include_taxes = true ) {

	$estimated = get_transient( 'rpress_estimated_monthly_stats' . $include_taxes );

	if ( false === $estimated ) {

		$estimated = array(
			'earnings' => 0,
			'sales'    => 0
		);

		$stats = new RPRESS_Payment_Stats;

		$to_date_earnings = $stats->get_earnings( 0, 'this_month', null, $include_taxes );
		$to_date_sales    = $stats->get_sales( 0, 'this_month' );

		$current_day      = date( 'd', current_time( 'timestamp' ) );
		$current_month    = date( 'n', current_time( 'timestamp' ) );
		$current_year     = date( 'Y', current_time( 'timestamp' ) );
		$days_in_month    = cal_days_in_month( CAL_GREGORIAN, $current_month, $current_year );

		$estimated['earnings'] = ( $to_date_earnings / $current_day ) * $days_in_month;
		$estimated['sales']    = ( $to_date_sales / $current_day ) * $days_in_month;

		// Cache for one day
		set_transient( 'rpress_estimated_monthly_stats' . $include_taxes, $estimated, 86400 );
	}

	return maybe_unserialize( $estimated );
}
