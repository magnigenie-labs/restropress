<?php
/**
 * Dashboard Widgets
 *
 * @package     RPRESS
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the dashboard widgets
 *
 * @author RestroPress
 * @since  1.0.0
 * @return void
 */
function rpress_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'rpress_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		wp_add_dashboard_widget( 'rpress_dashboard_sales', __('RestroPress Sales Summary','restropress' ), 'rpress_dashboard_sales_widget' );
	}
}
add_action('wp_dashboard_setup', 'rpress_register_dashboard_widgets', 10 );

/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the Sales Summary dashboard widget. This widget displays
 * the current month's sales and earnings, total sales and earnings best selling
 * fooditems as well as recent purchases made on your RPRESS Store.
 *
 * @author RestroPress
 * @since  1.0.0
 * @return void
 */
function rpress_dashboard_sales_widget( ) {
	echo '<p><img src=" ' . esc_attr( set_url_scheme( RP_PLUGIN_URL . 'assets/images/loading.gif', 'relative' ) ) . '"/></p>';
}

/**
 * Loads the dashboard sales widget via ajax
 *
 * @since  1.0.0
 * @return void
 */
function rpress_load_dashboard_sales_widget( ) {

	if ( ! current_user_can( apply_filters( 'rpress_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		die();
	}

	$stats = new RPRESS_Payment_Stats; ?>
	<div class="rpress_dashboard_widget">
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Current Month', 'restropress' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t monthly_earnings"><?php _e( 'Earnings', 'restropress' ); ?></td>
						<td class="b b-earnings"><?php echo rpress_currency_filter( rpress_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ); ?>
						<td class="first t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'restropress' ); ?></td>
						<td class="b b-sales"><?php echo rpress_format_amount( $monthly_sales, false ); ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Last Month', 'restropress' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t earnings"><?php echo __( 'Earnings', 'restropress' ); ?></td>
						<td class="b b-last-month-earnings"><?php echo rpress_currency_filter( rpress_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ); ?></td>
					</tr>
					<tr>
						<td class="first t sales">
							<?php $last_month_sales = $stats->get_sales( 0, 'last_month', false, array( 'publish', 'revoked' ) ); ?>
							<?php echo _n( 'Sale', 'Sales', rpress_format_amount( $last_month_sales, false ), 'restropress' ); ?>
						</td>
						<td class="b b-last-month-sales">
							<?php echo $last_month_sales; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Today', 'restropress' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t sales"><?php _e( 'Earnings', 'restropress' ); ?></td>
						<td class="last b b-earnings">
							<?php $earnings_today = $stats->get_earnings( 0, 'today', false ); ?>
							<?php echo rpress_currency_filter( rpress_format_amount( $earnings_today ) ); ?>
						</td>
					</tr>
					<tr>
						<td class="t sales">
							<?php _e( 'Sales', 'restropress' ); ?>
						</td>
						<td class="last b b-sales">
							<?php $sales_today = $stats->get_sales( 0, 'today', false, array( 'publish', 'revoked' ) ); ?>
							<?php echo rpress_format_amount( $sales_today, false ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Totals', 'restropress' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t earnings"><?php _e( 'Total Earnings', 'restropress' ); ?></td>
						<td class="last b b-earnings"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_total_earnings() ) ); ?></td>
					</tr>
					<tr>
						<td class="t sales"><?php _e( 'Total Sales', 'restropress' ); ?></td>
						<td class="last b b-sales"><?php echo rpress_format_amount( rpress_get_total_sales(), false ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php do_action( 'rpress_sales_summary_widget_after_stats', $stats ); ?>
		<?php
		$p_query = new RPRESS_Payments_Query( array(
			'number'   => 5,
			'status'   => 'publish'
		) );

		$payments = $p_query->get_payments();

		if ( $payments ) { ?>
		<div class="table recent_purchases">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Recent Purchases', 'restropress' ); ?>
							<a href="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' ); ?>">&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'restropress' ); ?></a>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $payments as $payment ) { ?>
						<tr>
							<td class="rpress_order_label">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details' ) ); ?>">
									<?php echo get_the_title( $payment->ID ) ?>
									&mdash; <?php echo $payment->email ?>
								</a>
								<?php if ( ! empty( $payment->user_id ) && ( $payment->user_id > 0 ) ) {
									$user = get_user_by( 'id', $payment->user_id );
									if ( $user ) {
										echo "(" . $user->data->user_login . ")";
									}
								} ?>
							</td>
							<td class="rpress_order_price">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details' ) ); ?>">
									<span class="rpress_price_label"><?php echo rpress_currency_filter( rpress_format_amount( $payment->total ), rpress_get_payment_currency_code( $payment->ID ) ); ?></span>
								</a>
							</td>
						</tr>
						<?php
					} // End foreach ?>
				</tbody>
			</table>
		</div>
		<?php } // End if ?>
		<?php do_action( 'rpress_sales_summary_widget_after_purchases', $payments ); ?>
	</div>
	<?php
	die();
}
add_action( 'wp_ajax_rpress_load_dashboard_widget', 'rpress_load_dashboard_sales_widget' );

/**
 * Add fooditem count to At a glance widget
 *
 * @author RestroPress
 * @since  1.0.0
 * @return void
 */
function rpress_dashboard_at_a_glance_widget( $items ) {
	$num_posts = wp_count_posts( 'fooditem' );

	if ( $num_posts && $num_posts->publish ) {
		$text = _n( '%s ' . rpress_get_label_singular(), '%s ' . rpress_get_label_plural(), $num_posts->publish, 'restropress' );

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_products' ) ) {
			$text = sprintf( '<a class="fooditem-count" href="edit.php?post_type=fooditem">%1$s</a>', $text );
		} else {
			$text = sprintf( '<span class="fooditem-count">%1$s</span>', $text );
		}

		$items[] = $text;
	}

	return $items;
}
add_filter( 'dashboard_glance_items', 'rpress_dashboard_at_a_glance_widget', 1 );
