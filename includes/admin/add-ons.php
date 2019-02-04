<?php
/**
 * Admin Add-ons
 *
 * @package     RPRESS
 * @subpackage  Admin/Add-ons
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0.0
 * @return void
 */
function rpress_add_ons_page() {
	$add_ons_tabs = apply_filters( 'rpress_add_ons_tabs', array( 'popular' => __( 'Popular', 'restro-press' ), 'new' => __( 'New', 'restro-press' ), 'all' => __( 'View all Integrations', 'restro-press' ) ) );
	$active_tab   = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $add_ons_tabs ) ? $_GET['tab'] : 'popular';

	// Set a new campaign for tracking purposes
	$campaign = isset( $_GET['view'] ) && strtolower( $_GET['view'] ) === 'integrations' ? 'RPRESSIntegrationsPage' : 'RPRESSAddonsPage';

	ob_start(); ?>
	<div class="wrap" id="rpress-add-ons">
		<h1 class="wp-heading-inline"><?php echo rpress_get_label_plural(); ?></h1>
		<a href="<?php echo admin_url( 'post-new.php?post_type=fooditem' ); ?>" class="page-title-action">Add New</a>
		<hr class="wp-header-end">
		<?php rpress_display_product_tabs(); ?>
	</div>
	<?php
	echo ob_get_clean();
}