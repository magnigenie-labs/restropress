
<div class="tab-pane fade delivery-settings-wrapper" id="nav-delivery" role="tabpanel" aria-labelledby="nav-delivery-tab">
	<div><?php echo __('Select a delivery time', 'restro-press'); ?></div>
	<input type="text" class="rpress-delivery rpress-allowed-delivery-hrs" id="rpress-allowed-hours" name="rpress_allowed_hours">
	<?php 
		if( class_exists('RestroPress_Store_Timing') ) :
			$store_timings = get_option('rpress_store_timing');

			//Check store timing is enabled or not
			if( isset($store_timings['enable']) 
				&& isset($store_timings['pre_order']) ) :
		?>
		<input type="text" name="" class="rpress_get_delivery_dates">
		<?php
			endif;
		endif;
	?>
</div>