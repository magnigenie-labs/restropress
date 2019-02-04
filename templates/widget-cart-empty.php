<li class="cart_item empty"><?php echo rpress_empty_cart_message(); ?></li>
<?php if ( rpress_use_taxes() ) : ?>
<li class="cart_item rpress-cart-meta rpress_subtotal" style="display:none;"><?php echo __( 'Subtotal:', 'restro-press' ). " <span class='subtotal'>" . rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item rpress-cart-meta rpress_cart_tax" style="display:none;"><?php _e( 'Estimated Tax:', 'restro-press' ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item rpress-cart-meta rpress_total" style="display:none;"><?php _e( 'Total:', 'restro-press' ); ?> <span class="cart-total"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>
<li class="delivery-items-options" style="display:none">
	<?php echo get_delivery_options(); ?>
</li>
<li class="cart_item rpress_checkout" style="display:none;"><a href="<?php echo rpress_get_checkout_uri(); ?>"><?php _e( 'Checkout', 'restro-press' ); ?></a></li>
