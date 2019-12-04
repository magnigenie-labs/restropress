<?php
/**
 * Admin Payment History
 *
 * @package     RPRESS
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Payment History Page
 *
 * Renders the payment history page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/
function rpress_payment_history_page() {
	$rpress_payment = get_post_type_object( 'rpress_payment' );

	if ( isset( $_GET['view'] ) && 'view-order-details' == $_GET['view'] ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
	} else {
		require_once RP_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
		$payments_table = new RPRESS_Payment_History_Table();
		$payments_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php echo $rpress_payment->labels->menu_name ?></h1>
		<?php do_action( 'rpress_payments_page_top' ); ?>
		<form id="rpress-payments-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' ); ?>">
			<input type="hidden" name="post_type" value="fooditem" />
			<input type="hidden" name="page" value="rpress-payment-history" />

			<?php $payments_table->views() ?>

			<?php $payments_table->advanced_filters(); ?>

			<?php $payments_table->display() ?>
		</form>
		<?php do_action( 'rpress_payments_page_bottom' ); ?>
	</div>
<?php
	}
}


/**
 * Payment History admin titles
 *
 * @since  1.0.0
 *
 * @param $admin_title
 * @param $title
 * @return string
 */
function rpress_view_order_details_title( $admin_title, $title ) {
	if ( 'fooditem_page_rpress-payment-history' != get_current_screen()->base )
		return $admin_title;

	if( ! isset( $_GET['rpress-action'] ) )
		return $admin_title;

	switch( $_GET['rpress-action'] ) :

		case 'view-order-details' :
			$title = __( 'View Order Details', 'restropress' ) . ' - ' . $admin_title;
			break;
		case 'edit-payment' :
			$title = __( 'Edit Payment', 'restropress' ) . ' - ' . $admin_title;
			break;
		default:
			$title = $admin_title;
			break;
	endswitch;

	return $title;
}
add_filter( 'admin_title', 'rpress_view_order_details_title', 10, 2 );

/**
 * Intercept default Edit post links for RPRESS payments and rewrite them to the View Order Details screen
 *
 * @since 1.0.4
 *
 * @param $url
 * @param $post_id
 * @param $context
 * @return string
 */
function rpress_override_edit_post_for_payment_link( $url, $post_id = 0, $context ) {

	$post = get_post( $post_id );
	if( ! $post )
		return $url;

	if( 'rpress_payment' != $post->post_type )
		return $url;

	$url = admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&view=view-order-details&id=' . $post_id );

	return $url;
}
add_filter( 'get_edit_post_link', 'rpress_override_edit_post_for_payment_link', 10, 3 );
