<div class="tab-pane fade delivery-settings-wrapper" id="nav-pickup" role="tabpanel" aria-labelledby="nav-pickup-tab">

  <!-- Pickup Time Wrap -->
  <div class="rpress-pickup-time-wrap rpress-time-wrap">

    <?php do_action( 'rpress_before_service_time', 'pickup' ); ?>

    <?php

    if ( rpress_is_service_enabled( 'pickup' ) ) :

      $store_times        = rp_get_store_timings( true, 'pickup' );
      $store_timings      = apply_filters( 'rpress_store_pickup_timings', $store_times );
      $store_time_format  = rpress_get_option( 'store_time_format' );
      $time_format        = ! empty( $store_time_format ) && $store_time_format == '24hrs' ? 'H:i' : 'h:ia';
      $time_format        = apply_filters( 'rpress_store_time_format', $time_format, $store_time_format );
      $selected_time = isset($_COOKIE['service_time']) ? $_COOKIE['service_time'] : '';
      $asap_option = rpress_get_option('enable_asap_option', '');
      $asap_option_only = rpress_get_option('enable_asap_option_only', '');
      
      ?>
      <div class="pickup-time-text">
        <?php echo apply_filters( 'rpress_pickup_time_string', esc_html_e( 'Select a pickup time', 'restropress' ) ); ?>
      </div>

      <?php
      if ( $asap_option_only == 1 ) {
        array_splice($store_timings, 1);
      }
      ?>

      <select class="rpress-pickup rpress-allowed-pickup-hrs rpress-hrs rp-form-control" id="rpress-pickup-hours" name="rpress_allowed_hours">
       <?php if( is_array( $store_timings ) ) :
         foreach( $store_timings as $key => $time ) :
           $loop_time = date( $time_format, $time ); ?>
           <option value='<?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $loop_time; ?>'<?php selected( $selected_time,$loop_time, $asap_option, true ); ?>><?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $loop_time; ?></option>
         <?php endforeach; ?>
       <?php endif; ?>
    	</select>
    <?php endif; ?>

    <?php do_action( 'rpress_after_service_time', 'pickup' ); ?>

	</div>
	<!-- Pickup Time Wrap Ends -->

</div>