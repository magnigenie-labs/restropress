<?php
global $rpress_options;
$delivery_options = rpress_get_option('enable_delivery');
$pickup_options = rpress_get_option('enable_pickup');
?>
<div class="fancybox-main rpress-delivery-popup">
	<div class="fancybox-first">
		<div class="rpress-delivery-wrap">
			<div class="col-md-12">
				<h3 style="text-align: center;">
					<?php echo __('Your Order Settings', 'restro-press'); ?>		
				</h3>
				<div class="rpress-tabs-wrapper rpress-delivery-options">
					<ul class="nav nav-pills" id="rpressdeliveryTab">

						<!-- Delivery Option Starts Here -->
						<?php if( $delivery_options == 1 ) : ?>
						<li class="nav-item">
							<a class="nav-link" id="nav-delivery-tab" data-delivery-type="delivery" data-toggle="tab" href="#nav-delivery" role="tab" aria-controls="nav-delivery" aria-selected="false">
								<?php echo __('Delivery', 'restro-press'); ?>
							</a>
						</li>
						<?php endif; ?>

						<!-- Pickup Option Starts Here -->
						<?php if( $pickup_options == 1 ) : ?>
						<li class="nav-item">
							<a class="nav-link" id="nav-pickup-tab" data-delivery-type="pickup" data-toggle="tab" href="#nav-pickup" role="tab" aria-controls="nav-pickup" aria-selected="false">
								<?php echo __('Pickup', 'restro-press'); ?></a></li>
						<?php endif; ?>
						<!-- Pickup Option Ends Here -->
					</ul>
				</div>
				
				<div class="tab-content" id="rpress-tab-content">
					<?php if( $delivery_options == 1 ) : ?>
					<?php rpress_get_template_part( 'rpress', 'delivery' ); ?> 
					<?php endif; ?>

					<?php if( $pickup_options == 1 ) : ?>
					<?php rpress_get_template_part( 'rpress', 'pickup' ); ?>
					<?php endif; ?>
					<button type="button" data-food-id='{FoodID}' class="btn btn-primary btn-block rpress-delivery-opt-update">
					<?php echo __('Update','restro-press'); ?> </button>
				</div>
			</div>
		</div>
	</div>
</div>