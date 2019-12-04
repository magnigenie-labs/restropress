<?php 
	$cart_quantity = rpress_get_cart_quantity();
	$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
	$color = rpress_get_option( 'checkout_color', 'red' );
?>
<li class="cart_item empty"><?php echo rpress_empty_cart_message(); ?></li>
<?php if ( rpress_use_taxes() ) : ?>
<li class="cart_item rpress-cart-meta rpress_subtotal" style="display:none;"><?php echo __( 'Subtotal:', 'restropress' ). " <span class='subtotal'>" . rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item rpress-cart-meta rpress_cart_tax" style="display:none;"><?php _e( 'Estimated Tax:', 'restropress' ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item rpress-cart-meta rpress_total" style="display:none;"><?php _e( 'Total (', 'restropress' ); ?><span class="rpress-cart-quantity" <?php echo $display; ?>><?php echo $cart_quantity; ?></span> <?php _e( ' Items)', 'restropress' ); ?><span class="cart-total <?php echo $color; ?>"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>
<li class="delivery-items-options" style="display:none">
	<?php echo get_delivery_options( true ); ?>
</li>
<li class="cart_item rpress_checkout" style="display:none;"><a href="<?php echo rpress_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'restropress' ); ?></a></li>