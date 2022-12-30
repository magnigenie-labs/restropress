<div class="tab-pane fade delivery-settings-wrapper" id="nav-delivery" role="tabpanel" aria-labelledby="nav-delivery-tab">

  <!-- Delivery Time Wrap -->
  <div class="rpress-delivery-time-wrap rpress-time-wrap">

    <?php do_action( 'rpress_before_service_time', 'delivery' ); ?>

    <?php

    if ( rpress_is_service_enabled( 'delivery' ) ) :

      $store_times        = rp_get_store_timings( true, 'delivery' );
      $store_timings      = apply_filters( 'rpress_store_delivery_timings', $store_times );
      $store_time_format  = rpress_get_option( 'store_time_format' );
      $time_format        = ! empty( $store_time_format ) && $store_time_format == '24hrs' ? 'H:i' : 'h:ia';
      $time_format        = apply_filters( 'rpress_store_time_format', $time_format, $store_time_format );
      $selected_time = isset($_COOKIE['service_time']) ? $_COOKIE['service_time'] : '';
      $asap_option = rpress_get_option('enable_asap_option', '');
      $asap_option_only = rpress_get_option('enable_asap_option_only', '');
      
      ?>
      <div class="delivery-time-text">
        <?php echo apply_filters( 'rpress_delivery_time_string', esc_html_e( 'Select a delivery time', 'restropress' ) ); ?>
      </div>

      <?php
      if ( $asap_option_only == 1 ) {
        array_splice($store_timings, 1);
      }
      ?>

  		<select class="rpress-delivery rpress-allowed-delivery-hrs rpress-hrs rp-form-control" id="rpress-delivery-hours" name="rpress_allowed_hours">
  		  <?php
        if( is_array( $store_timings ) ) :
          foreach( $store_timings as $key => $time ) :
            $loop_time = date( $time_format, $time ); ?>
             <option value='<?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $loop_time; ?>'<?php selected( $selected_time,$loop_time,$asap_option, true ); ?>><?php echo ( $asap_option && $key == 0 ) ? __( 'ASAP' , 'restropress' ) : $loop_time; ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
  		</select>
    <?php endif; ?>

    <?php do_action( 'rpress_after_service_time', 'delivery' ); ?>

  </div>
	<!-- Delivery Time Wrap Ends -->

</div>