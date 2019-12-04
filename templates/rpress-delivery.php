<div class="tab-pane fade delivery-settings-wrapper" id="nav-delivery" role="tabpanel" aria-labelledby="nav-delivery-tab">
  <?php do_action( 'rpress_pre_order_dates' ); ?>
  
	<!-- Delivery Time Wrap -->
  <div class="rpress-delivery-time-wrap rpress-time-wrap">
    <div class="delivery-time-text">
      <?php echo apply_filters( 'rpress_delivery_time_string', esc_html_e( 'Select A Delivery Time', 'restropress' ) ); ?>
    </div>
		
		<select class="rpress-delivery rpress-allowed-delivery-hrs rpress-hrs rp-form-control" id="rpress-delivery-hours" name="rpress_allowed_hours">
		<?php
      $current_time = !empty( rp_get_current_time() ) ? rp_get_current_time() : date("h:i A");
      
      $store_timings = apply_filters( 'rpress_store_delivery_timings', $store_times );

      $store_timings_for_today = apply_filters( 'rpress_timing_for_today', true );


      if( is_array( $store_timings ) ) :
        foreach( $store_timings as $time ) :
          $store_time = date( "h:ia", $time );

          if( $store_timings_for_today ) :
            if ( strtotime( $store_time ) > strtotime( $current_time ) ) :
            ?>
            <option value='<?php echo $store_time; ?>'><?php echo $store_time; ?>
            </option>
          <?php
            endif;
          else :
            ?>
            <option value='<?php echo $store_time; ?>'><?php echo $store_time; ?>
          <?php
          endif;
        endforeach;
      endif;
			?>
		</select>
	</div>
	<!-- Delivery Time Wrap Ends Here -->
</div>