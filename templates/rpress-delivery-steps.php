<?php
global $rpress_options;
$delivery_options = rpress_get_option('enable_delivery');
$pickup_options = rpress_get_option('enable_pickup');
$store_timings = get_option('rpress_store_timing');
$color = rpress_get_option( 'checkout_color', 'red' );

$pre_order_class = '';
if( is_array($store_timings) && isset($store_timings['enable']) ) {
	if( isset($store_timings['pre_order']) ) {
		$pre_order_class = 'pre-order-enable';
	}
}
?>

<div class="rpress-delivery-wrap <?php echo $color; ?> ">
	<div class="rpress-row">
  			
  	<!-- Error Message Starts Here -->
  	<div class="alert alert-warning rpress-errors-wrap disabled"></div>
  	<!-- Error Message Ends Here -->

		<div class="rpress-tabs-wrapper rpress-delivery-options text-center">
			<ul class="nav nav-pills" id="rpressdeliveryTab">
				<!-- Delivery Option Starts Here -->
				<?php if( $delivery_options == 1 ) : ?>
					<li class="nav-item">
						<a class="nav-link <?php echo $color; ?>" id="nav-delivery-tab" data-delivery-type="delivery" data-toggle="tab" href="#nav-delivery" role="tab" aria-controls="nav-delivery" aria-selected="false">
							<?php echo __('Delivery', 'restro-press'); ?>
						</a>
					</li>
				<?php endif; ?>

				<!-- Pickup Option Starts Here -->
				<?php if( $pickup_options == 1 ) : ?>
					<li class="nav-item">
						<a class="nav-link <?php echo $color; ?>" id="nav-pickup-tab" data-delivery-type="pickup" data-toggle="tab" href="#nav-pickup" role="tab" aria-controls="nav-pickup" aria-selected="false">
							<?php echo __('Pickup', 'restro-press'); ?>	
						</a>
					</li>
				<?php endif; ?>
				<!-- Pickup Option Ends Here -->
			</ul>
				
			<div class="tab-content" id="rpress-tab-content">
				<?php if( $delivery_options == 1 ) : ?>
					<?php rpress_get_template_part( 'rpress', 'delivery' ); ?> 
				<?php endif; ?>

				<?php if( $pickup_options == 1 ) : ?>
					<?php rpress_get_template_part( 'rpress', 'pickup' ); ?>
				<?php endif; ?>
				<button type="button" data-food-id='{FoodID}' class="btn btn-primary btn-block rpress-delivery-opt-update <?php echo $color;?> ">
					<?php echo __('Update','restro-press'); ?> 
				</button>
			</div>
		</div>
	</div>
</div>