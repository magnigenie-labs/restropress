<?php
/**
 * This template is used to display the RestroPress cart widget.
 */
$cart_items    	= rpress_get_cart_contents();
$cart_quantity 	= rpress_get_cart_quantity();
$display       	= $cart_quantity > 0 ? '' : 'style="display:none;"';

?>

<?php do_action( 'rpress_before_cart' ); ?>

<div class="rp-col-lg-4 rp-col-md-4 rp-col-sm-12 rp-col-xs-12 pull-right rpress-sidebar-cart item-cart sticky-sidebar">
	<div class="rpress-mobile-cart-icons" <?php echo  empty( rpress_get_cart_quantity() ) ? 'style="display:none"' :'' ?>>
		<div class="rp-cart-left-wrap">
			<span class="rp-cart-mb-icon"><i class='fa fa-shopping-cart' aria-hidden='true'></i></span>
			<span class='rpress-cart-badge rpress-cart-quantity'>
			  <?php echo rpress_get_cart_quantity(); ?> <span><?php esc_html_e('Items', 'restropress'); ?></span>
			</span>
			<span class="rp-separation">&nbsp;|&nbsp;</span>
			<span class="rp-mb-price"><?php echo rpress_currency_filter( rpress_format_amount( rpress_get_cart_total() ) ); ?></span>
		</div>
		<div class="rp-cart-right-wrap">
			<span class="rp-cart-mb-txt"><?php esc_html_e('Checkout', 'restropress'); ?></span>
			<span class="rp-cart-mb-icon"><i class="fa fa-caret-right" aria-hidden="true"></i></span>
		</div>
	</div>
	<div class="rpress-sidebar-main-wrap">
		<i class="fa fa-times close-cart-ic" aria-hidden="true"></i>
	    <div class="rpress-sidebar-cart-wrap">
	    	<div class="rpress item-order">
	    		<span><?php echo apply_filters('rpress_cart_title', __( 'Your Order', 'restropress' ) ); ?></span>
				<a class="rpress-clear-cart" href="#" <?php echo wp_kses_post( $display ) ?> >
					<span class="cart-clear-icon">&times;</span>
					<span class="cart-clear-text rp-ajax-toggle-text"><?php esc_html_e('Clear Order', 'restropress') ?></span>
				</a>
			</div>
			<ul class="rpress-cart">

				<?php if( $cart_items ) : ?>
					<?php foreach( $cart_items as $key => $item ) : ?>
						<?php echo rpress_get_cart_item_template( $key, $item, false, $data_key = '' ); ?>
					<?php endforeach; ?>
					<?php rpress_get_template_part( 'cart/checkout' ); ?>
				<?php else : ?>
					<?php rpress_get_template_part( 'cart/empty' ); ?>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php do_action( 'rpress_after_cart' ); ?>
