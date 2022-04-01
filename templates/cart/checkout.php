<?php

$cart_quantity = rpress_get_cart_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
?>

<li class="cart_item rpress-cart-meta rpress_subtotal"><?php esc_html_e( 'Subtotal:', 'restropress' ); ?> <span class='cart-subtotal'><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_subtotal() ) ); ?></span></li>

<?php if ( rpress_use_taxes() && !empty( ceil( rpress_get_cart_tax() ) ) ) : ?>
  <li class="cart_item rpress-cart-meta rpress_cart_tax"><?php echo esc_html( rpress_get_tax_name() ); ?> <span class="cart-tax"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_tax() ) ); ?></span></li>
<?php endif; ?>

<?php do_action( 'rpress_cart_line_item' ); ?>

<li class="cart_item rpress-cart-meta rpress_total"><?php esc_html_e( 'Total (', 'restropress' ); ?><span class="rpress-cart-quantity" <?php echo wp_kses_post( $display ); ?> ><?php echo esc_html( $cart_quantity ); ?></span><?php esc_html_e( ' Items)', 'restropress' ); ?><span class="cart-total"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span></li>

<!-- Service Type and Service Time -->
<?php if ( ( isset( $_COOKIE['service_type'] ) && !empty( $_COOKIE['service_type'] ) ) || ( isset( $_COOKIE['service_time'] ) && !empty( $_COOKIE['service_time'] ) ) ) : ?>
  <li class="delivery-items-options">
    <?php echo get_delivery_options( true ); ?>
  </li>
<?php endif; ?>

<?php if( apply_filters( 'rpress_show_checkout_button', true ) ) : ?>
<li class="cart_item rpress_checkout">
  	<a data-url="<?php echo esc_url( rpress_get_checkout_uri() ); ?>" href="#">
  		<span class="rp-ajax-toggle-text">
  			<?php
    		$confirm_order_text = apply_filters( 'rp_confirm_order_text', esc_html_e( 'Checkout', 'restropress' ) );
    		echo esc_html( $confirm_order_text ); ?>
    	</span> 
	</a>
</li>
<?php endif; ?>
<?php do_action( 'rpress_after_checkout_button' ); ?>
