<?php
/**
 * This template is used to display the RestroPress cart widget.
 */
$cart_items    = rpress_get_cart_contents();
$cart_quantity = rpress_get_cart_quantity();
$display       = $cart_quantity > 0 ? '' : ' style="display:none;"';
$color = rpress_get_option( 'checkout_color', 'red' );
?>
<div class="rpress item-order">
	<h6><?php echo apply_filters('rpress_cart_title', __('Your Order', 'restropress')); ?></h6>
	<a class="rpress-clear-cart <?php echo $color; ?>" href="#" <?php echo $display ?> > <i class="fa fa-ban"></i> <?php echo __('Clear Order', 'restropress') ?> </a>
</div>
<ul class="rpress-cart">
<?php if( $cart_items ) : 
	?>

	<?php foreach( $cart_items as $key => $item ) : ?>

		<?php echo rpress_get_cart_item_template( $key, $item, false, $data_key = '' ); ?>

	<?php endforeach; ?>

	<?php rpress_get_template_part( 'widget', 'cart-checkout' ); ?>

<?php else : ?>

	<?php rpress_get_template_part( 'widget', 'cart-empty' ); ?>

<?php endif; ?>
</ul>
