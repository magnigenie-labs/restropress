<?php
/**
 * Food Item variable price html
 *
 * @package RestroPress/Admin
 */

defined( 'ABSPATH' ) || exit;
$count 	= !empty( $current ) ? $current : 0;
$name  	= !empty( $price ) && is_array( $price )  ? $price['name'] : '';
$amount = !empty( $price ) ? $price['amount'] : '';
?>
<div class="rp-metabox variable-price">
	<h3>
		<a href="#" class="remove_row delete">
			<?php esc_html_e( 'Remove', 'restropress' ); ?>
		</a>
		<div class="tips sort" data-tip="<?php esc_html_e( 'Drag Drop to reorder the addon categories.', 'restropress' );?>"></div>
		<strong class="price_name">
			<?php echo $name == '' ? __( 'Option Name', 'restropress' ) : esc_html( $name ); ?>
		</strong>
	</h3>
	<div class="rp-metabox-content">
		<div class="rp-col-6 price-name">
			<input type="text" value="<?php echo esc_attr( $name ); ?>" name="rpress_variable_prices[<?php echo absint( $count ); ?>][name]" class="rp-input rp-input-variable-name" placeholder="<?php esc_html_e( 'Option Name', 'restropress' ); ?>">
		</div>
		<div class="rp-col-6 price-value">
			<?php esc_html_e( 'Price:', 'restropress' ); ?>
			<?php echo rpress_currency_symbol(); ?>
			<input type="text" value="<?php echo rpress_sanitize_amount( $amount ); ?>" name="rpress_variable_prices[<?php echo absint( $count ); ?>][amount]" class="rp-input" placeholder="9.99">
		</div>
	</div>
</div>