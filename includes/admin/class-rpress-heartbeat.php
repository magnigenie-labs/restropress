<?php
/**
 * Admin / Heartbeat
 *
 * @package     RPRESS
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Heartbeart Class
 *
 * Hooks into the WP heartbeat API to update various parts of the dashboard as new sales are made
 *
 * Dashboard components that are effect:
 *	- Dashboard Summary Widget
 *
 * @since 1.0.0
 */
class RPRESS_Heartbeat {

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function init() {

		add_filter( 'heartbeat_received', array( 'RPRESS_Heartbeat', 'heartbeat_received' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( 'RPRESS_Heartbeat', 'enqueue_scripts' ) );
	}

	/**
	 * Tie into the heartbeat and append our stats
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function heartbeat_received( $response, $data ) {

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return $response; // Only modify heartbeat if current user can view show reports
		}

		// Make sure we only run our query if the rpress_heartbeat key is present
		if( ( isset( $data['rpress_heartbeat'] ) ) && ( $data['rpress_heartbeat'] == 'dashboard_summary' ) ) {

			// Instantiate the stats class
			$stats = new RPRESS_Payment_Stats;

			$earnings = rpress_get_total_earnings();

			// Send back the number of complete payments
			$response['rpress-total-payments'] = rpress_format_amount( rpress_get_total_sales(), false );
			$response['rpress-total-earnings'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $earnings ) ), ENT_COMPAT, 'UTF-8' );
			$response['rpress-payments-month'] = rpress_format_amount( $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ), false );
			$response['rpress-earnings-month'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ), ENT_COMPAT, 'UTF-8' );
			$response['rpress-payments-today'] = rpress_format_amount( $stats->get_sales( 0, 'today', false, array( 'publish', 'revoked' ) ), false );
			$response['rpress-earnings-today'] = html_entity_decode( rpress_currency_filter( rpress_format_amount( $stats->get_earnings( 0, 'today' ) ) ), ENT_COMPAT, 'UTF-8' );

		}

		return $response;

	}

	/**
	 * Load the heartbeat scripts
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function enqueue_scripts() {

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}

		// Make sure the JS part of the Heartbeat API is loaded.
		wp_enqueue_script( 'heartbeat' );
		add_action( 'admin_print_footer_scripts', array( 'RPRESS_Heartbeat', 'footer_js' ), 20 );
	}

	/**
	 * Inject our JS into the admin footer
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function footer_js() {
		global $pagenow;

		// Only proceed if on the dashboard
		if( 'index.php' != $pagenow ) {
			return;
		}

		if( ! current_user_can( 'view_shop_reports' ) ) {
			return; // Only load heartbeat if current user can view show reports
		}

		?>
		<script>
			(function($){
				// Hook into the heartbeat-send
				$(document).on('heartbeat-send', function(e, data) {
					data['rpress_heartbeat'] = 'dashboard_summary';
				});

				// Listen for the custom event "heartbeat-tick" on $(document).
				$(document).on( 'heartbeat-tick', function(e, data) {

					// Only proceed if our RPRESS data is present
					if ( ! data['rpress-total-payments'] )
						return;

					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
					console.log('tick');
					<?php endif; ?>

					// Update sale count and bold it to provide a highlight
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_totals .b.b-earnings', data['rpress-total-earnings'] );
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_totals .b.b-sales', data['rpress-total-payments'] );
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_today .b.b-earnings', data['rpress-earnings-today'] );
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_today .b.b-sales', data['rpress-payments-today'] );
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_current_month .b-earnings', data['rpress-earnings-month'] );
					rpress_dashboard_heartbeat_update( '.rpress_dashboard_widget .table_current_month .b-sales', data['rpress-payments-month'] );

					// Return font-weight to normal after 2 seconds
					setTimeout(function(){
						$('.rpress_dashboard_widget .b.b-sales,.rpress_dashboard_widget .b.b-earnings').css( 'font-weight', 'normal' );
						$('.rpress_dashboard_widget .table_current_month .b.b-earnings,.rpress_dashboard_widget .table_current_month .b.b-sales').css( 'font-weight', 'normal' );
					}, 2000);

				});

				function rpress_dashboard_heartbeat_update( selector, new_value ) {
					var current_value = $(selector).text();
					$(selector).text( new_value );
					if ( current_value !== new_value ) {
						$(selector).css( 'font-weight', 'bold' );
					}
				}
			}(jQuery));
		</script>
		<?php
	}
}
add_action( 'plugins_loaded', array( 'RPRESS_Heartbeat', 'init' ) );
