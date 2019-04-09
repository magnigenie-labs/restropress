<?php
/**
 * Admin Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all RPRESS actions sent via POST and GET by looking for the 'rpress-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function rpress_process_actions() {
	if ( isset( $_POST['rpress-action'] ) ) {
		do_action( 'rpress_' . $_POST['rpress-action'], $_POST );
	}

	if ( isset( $_GET['rpress-action'] ) ) {
		do_action( 'rpress_' . $_GET['rpress-action'], $_GET );
	}
}
add_action( 'admin_init', 'rpress_process_actions' );

/**
 *
 * @since 1.0.0
 * @param $views
 *
 * @return mixed
 */
function rpress_products_tabs( $views ) {
	rpress_display_product_tabs();

	return $views;
}
add_filter( 'views_edit-fooditem', 'rpress_products_tabs', 10, 1 );

/**
 * Displays the product tabs for 'Products' 
 *
 * @since 1.0.0
 */
function rpress_display_product_tabs() {
	?>
	<h2 class="nav-tab-wrapper">
		<?php
		$tabs = array(
			'products' => array(
				'name' => rpress_get_label_plural(),
				'url'  => admin_url( 'edit.php?post_type=fooditem' ),
			),
		);

		$tabs       = apply_filters( 'rpress_add_ons_tabs', $tabs );
		$active_tab = isset( $_GET['page'] ) && $_GET['page'] === 'rpress-addons' ? 'integrations' : 'products';
		foreach( $tabs as $tab_id => $tab ) {

			$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

			echo '<a href="' . esc_url( $tab['url'] ) . '" class="nav-tab' . $active . '">';
			echo esc_html( $tab['name'] );
			echo '</a>';
		}
		?>

		<a href="<?php echo admin_url( 'post-new.php?post_type=fooditem' ); ?>" class="page-title-action">
			<?php _e( 'Add New', 'restropress' ); // No text domain so it just follows what WP Core does ?>
		</a>
	</h2>
	<br />
	<?php
}