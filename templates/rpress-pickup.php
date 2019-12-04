<div class="tab-pane fade delivery-settings-wrapper" id="nav-pickup" role="tabpanel" aria-labelledby="nav-pickup-tab">

  <?php do_action( 'rpress_pre_order_dates' ); ?>

	<!-- Pickup Time Starts Here -->
	<div class="rpress-pickup-time-wrap rpress-time-wrap  <?php echo $preorder_class; ?>">
		<div class="pickup-time-text"><?php echo __('Select a pickup time', 'restropress'); ?></div>
		
		<select class="<?php echo $preorder_class; ?> rpress-pickup rpress-allowed-pickup-hrs rpress-hrs rp-form-control" id="rpress-pickup-hours" name="rpress_allowed_hours">
			<?php
        $current_time = !empty( rp_get_current_time() ) ? rp_get_current_time() : date("h:i A");

				$store_times = rp_get_store_timings();
        $store_timings = apply_filters( 'rpress_store_pickup_timings', $store_times );
        $store_timings_for_today = apply_filters( 'rpress_timing_for_today', true );

        if ( is_array( $store_timings ) ) :
          foreach( $store_timings as $time ) :
            $loop_time = date( "h:ia", $time );
            
            if ( $store_timings_for_today ) :
              if ( strtotime( $loop_time ) > strtotime( $current_time ) ) :
              ?>
                <option value='<?php echo $loop_time; ?>'><?php echo $loop_time; ?></option>
              <?php
              endif;
            else:
              ?>
              <option value='<?php echo $loop_time; ?>'><?php echo $loop_time; ?></option>
              <?php
            endif;
            
          endforeach;
        endif;
			?>
		</select>
	</div>
	<!-- Pickup Time Ends Here -->
</div>