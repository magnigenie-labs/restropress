<?php
/**
 * Food Item data meta box.
 *
 * @package RestroPress/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="panel-wrap fooditem_data">

	<ul class="fooditem_data_tabs rp-tabs">
		<?php foreach ( self::get_fooditem_data_tabs() as $key => $tab ) : ?>
			<li class="<?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab['class'] ) ? implode( ' ', ( array ) $tab['class'] ) : '' ); ?>">
				<a href="#<?php echo esc_attr( $tab['target'] ); ?>">
					<?php if( !empty( $tab['icon'] ) ) : ?>
						<i class="<?php echo sanitize_html_class( $tab['icon'] ) ; ?>"></i>
					<?php endif; ?>
					<span><?php echo esc_html( $tab['label'] ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
		<?php do_action( 'rpress_fooditem_write_panel_tabs' ); ?>
	</ul>

	<?php
		self::output_tabs();
		do_action( 'rpress_fooditem_data_panels' );
	?>
	<div class="clear"></div>
</div>
