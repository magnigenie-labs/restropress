<?php
$cart_quantity = rpress_get_cart_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
?>
<li class="cart_item empty"><?php echo wp_kses_post( rpress_empty_cart_message() ) ; ?></li>

<?php if ( rpress_use_taxes() ) : ?>
<li class="cart_item rpress-cart-meta rpress_subtotal" style="display:none;"><?php esc_html_e( 'Subtotal:', 'restropress' ). " <span class='subtotal'>" . rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item rpress-cart-meta rpress_cart_tax" style="display:none;"><?php esc_html_e( 'Estimated Tax:', 'restropress' ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item rpress-cart-meta rpress_total" style="display:none;"><?php esc_html_e( 'Total (', 'restropress' ); ?><span class="rpress-cart-quantity" <?php echo wp_kses_post( $display ); ?>><?php echo esc_html( $cart_quantity ); ?></span> <?php esc_html_e( ' Items)', 'restropress' ); ?><span class="cart-total"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>
<li class="delivery-items-options" style="display:none">
	<?php echo get_delivery_options( true ); ?>
</li>
<li class="cart_item rpress_checkout" style="display:none;"><a href="<?php echo esc_url( rpress_get_checkout_uri() ); ?>"><?php esc_html_e( 'Checkout', 'restropress' ); ?></a></li>
