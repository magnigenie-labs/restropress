<?php
/**
 * Discount Codes
 *
 * @package     RPRESS
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the Discount Pages Admin Page
 *
 * @since 1.4
 * @author Magnigenie
 * @return void
*/
function rpress_discounts_page() {
	if ( isset( $_GET['rpress-action'] ) && $_GET['rpress-action'] == 'edit_discount' ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php';
	} elseif ( isset( $_GET['rpress-action'] ) && $_GET['rpress-action'] == 'add_discount' ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php';
	} else {
		require_once RP_PLUGIN_DIR . 'includes/admin/discounts/class-discount-codes-table.php';
		$discount_codes_table = new RPRESS_Discount_Codes_Table();
		$discount_codes_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php _e( 'Discount Codes', 'restropress' ); ?><a href="<?php echo esc_url( add_query_arg( array( 'rpress-action' => 'add_discount' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'restropress' ); ?></a></h1>
		<?php do_action( 'rpress_discounts_page_top' ); ?>
		<form id="rpress-discounts-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-discounts' ); ?>">
			<?php $discount_codes_table->search_box( __( 'Search', 'restr-press' ), 'rpress-discounts' ); ?>

			<input type="hidden" name="post_type" value="fooditem" />
			<input type="hidden" name="page" value="rpress-discounts" />

			<?php $discount_codes_table->views() ?>
			<?php $discount_codes_table->display() ?>
		</form>
		<?php do_action( 'rpress_discounts_page_bottom' ); ?>
	</div>
<?php
	}
}
