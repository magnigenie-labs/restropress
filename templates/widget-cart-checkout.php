<?php if ( rpress_use_taxes() ) : ?>
<li class="cart_item rpress-cart-meta rpress_subtotal"><?php echo __( 'Subtotal:', 'restro-press' ). " <span class='subtotal'>" . rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item rpress-cart-meta rpress_cart_tax"><?php _e( 'Estimated Tax:', 'restro-press' ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<li class="cart_item rpress-cart-meta rpress_total"><?php _e( 'Total:', 'restro-press' ); ?> <span class="cart-total"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>

<li class="delivery-items-options">
	<?php echo get_delivery_options(); ?>
</li>
<li class="cart_item rpress_checkout"><a data-url="<?php echo rpress_get_checkout_uri(); ?>" href="#"><?php _e( 'Confirm Order', 'restro-press' ); ?></a></li>