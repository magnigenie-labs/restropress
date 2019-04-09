<?php 
	$color = rpress_get_option( 'checkout_color', 'red' );
?>
<?php if ( rpress_use_taxes() ) : ?>
<li class="cart_item rpress-cart-meta rpress_subtotal"><?php echo __( 'Subtotal:', 'restropress' ). " <span class='subtotal'>" . rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>
<li class="cart_item rpress-cart-meta rpress_cart_tax"><?php _e( 'Estimated Tax:', 'restropress' ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>
<!-- Check Delivery Fee Starts Here -->
<?php if( apply_delivery_fee() ) : ?>
	<li class="cart_item rpress-cart-meta rpress_subtotal"><?php _e( 'SubTotal:', 'restropress' ); ?> <span class="cart-sub-total <?php echo $color; ?>"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span>
	</li>
<li class="cart_item rpress-cart-meta rpress-delivery-fee">
	<?php _e('Delivery Fee:', 'restropress' ); ?>
	<span class="cart-delivery-fee <?php echo $color; ?>"><?php echo rpress_get_delivery_price(); ?></span>
			
</li>
<?php endif; ?>
<!-- Check Delivery Fee Ends Here -->


<li class="cart_item rpress-cart-meta rpress_total"><?php _e( 'Total:', 'restropress' ); ?> <span class="cart-total <?php echo $color; ?>"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>
<li class="delivery-items-options">
	<?php echo get_delivery_options(true); ?>
</li>
<li class="cart_item rpress_checkout <?php echo $color; ?>"><a data-url="<?php echo rpress_get_checkout_uri(); ?>" href="#"><?php _e( 'Confirm Order', 'restropress' ); ?></a></li>