<?php
/**
 * Food Item Addons data panel.
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;

$addon_categories 	= rpress_get_addons();
$addon_types		= rpress_get_addon_types();
$post_id  			= get_the_ID();

?>

<div id="addons_fooditem_data" class="panel rp-metaboxes-wrapper restropress_options_panel hidden">
	<div class="rp-metabox-container">
		<div class="toolbar toolbar-top">
			<span class="rp-toolbar-title">
				<?php esc_html_e( 'Addons', 'restropress' ); ?>
			</span>
			<button type="button" class="button create-addon alignright">
        		<span class="dashicons dashicons-plus-alt"></span>
				<?php esc_html_e( 'Create New Add-on', 'restropress' ); ?>
			</button>
		</div>

		<div class="rp-addons rp-metaboxes">
			<?php include 'html-fooditem-addon.php'; ?>
		</div>

		<div class="toolbar toolbar-bottom">
			<button type="button" data-item-id="<?php echo esc_attr( $post_id ) ; ?>" class="button button-primary add-new-addon alignright">
        		<span class="dashicons dashicons-plus"></span>
				<?php esc_html_e( 'Add New', 'restropress' ); ?>
			</button>
		</div>
	</div>
	<?php do_action( 'rpress_fooditem_options_addons_data' ); ?>
</div>