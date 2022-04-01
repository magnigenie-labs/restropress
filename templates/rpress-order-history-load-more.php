<?php 
	$paged = ( isset( $_GET['paged'] ) AND !empty( $_GET['paged'] ) ) ? sanitize_text_field( $_GET['paged'] ): 1 ;
    $args = array(  
        'post_type'         => 'rpress_payment',
        'post_status'       => 'any',
        'posts_per_page'    => 10, 
        'paged'             => $paged, 
        'order'             => 'DSC', 
        'author'            => get_current_user_id()
    );

    $loop = new WP_Query( $args );
   ?> 
    <div class="rp-col-md-12 rp-col-sm-6">
        <?php
            $count = 1 ;
            if ( $loop->have_posts() ) :
                while ( $loop->have_posts() ) : $loop->the_post(); 
                	$payment = new RPRESS_Payment( get_the_ID() );
                	$billing_name = array();
                	if ( ! empty( $payment->user_info['first_name'] ) ) {
                		$billing_name[] = $payment->user_info['first_name'];
                	}

                	if ( ! empty( $payment->user_info['last_name'] ) ) {
                		$billing_name[] = $payment->user_info['last_name'];
                	}
                	$billing_name = implode( ' ', array_values( $billing_name ) );

                	$address_info   = get_post_meta( $payment->ID, '_rpress_delivery_address', true );
                	$address        = !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
                	$address	    .= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
                	$address	    .= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
                	$address	    .= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';

                	$service_type = get_post_meta( $payment->ID, '_rpress_delivery_type', true );
                	$order_status = get_post_meta( $payment->ID, '_order_status', true );

                	$order_items = array();
                	foreach ( $payment->fooditems as $cart_item ) {
                		$fooditem = new RPRESS_Fooditem( $cart_item['id'] );
                		$name     = $fooditem->get_name();

                		if ( $fooditem->has_variable_prices() && isset( $cart_item['options']['price_id'] ) ) {
                			$variation_name = rpress_get_price_option_name( $fooditem->ID, $cart_item['options']['price_id'] );
                			if ( ! empty( $variation_name ) ) {
                				$name .= ' - ' . $variation_name;
                			}
                		}

                		$order_items[] = $name . ' &times; ' . $cart_item['quantity'];
                	}

                	$items_purchased = implode( ', ', $order_items );
                	?>
                    	<div class="rp-col-lg-6 rp-col-md-6 rp-col-sm-12 rp-col-xs-12 rpress_purchase_row">
                    		<div class="rpress-history-card">
                    			<div class="rp-col-md-9 rpress-his-col">
                    				<div class="rpress-order-id rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Order :','restropress' ); ?></span>#<?php echo esc_html( $payment->number ); ?></div>
                    				<div class="rpress-od-date rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Placed on :','restropress' ); ?></span><?php echo date_i18n( get_option('date_format'), strtotime( $payment->date ) ); ?></div>

                    				<!-- Address  -->
                    				<?php if( $address ) : ?>
                    					<div class="rpress-adds rpress-lable-txt">
                    						<span class="rp-bold-hs"><?php esc_html_e('Address :','restropress' ); ?></span><?php echo esc_html( $address ); ?>
                    					</div>
                    				<?php endif ;?>

                    				<div class="rpress-order-type rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Order Type :','restropress' ); ?></span> <?php echo esc_html( $service_type ); ?></div>
                    				<div class="rpess-view-details">
                    					<a href="#" class="rpress-view-order-btn" data-order-id="<?php echo esc_attr__( $payment->ID ); ?>">
                                            <span class="rp-ajax-toggle-text">
                                                <?php esc_html_e('View Details', 'restropress') ?>
                                            </span> 
                                        </a>
                    				</div>
                    			</div>
                    			<div class="rp-col-md-3 rpress-his-col">
                    				<div class="rpress-order-status-wrap">
                    					<span class="button rpress-status"><?php echo esc_html( $order_status ); ?></span>
                    				</div>
                    			</div>	
                    			<hr class="rp-line" style="border-style: dashed;">
                    			<div class="rpress-order-cart-ft">
                    				<div class="rpesss-foods">
                    					<span><?php echo wp_kses_post( $items_purchased ); ?></span>
                    				</div>
                    				<div class="rpress-total-am">
                    					<span><?php esc_html_e( 'Total Paid:', 'restropress' ); ?> </span><?php echo wp_kses_post( rpress_currency_filter( rpress_format_amount( $payment->total ) ) ) ; ?>
                    				</div>
                    			</div>
                    		</div>
                    	</div>
                	<?php 
                        if( 2 === $count  ) :
                            ?>
                		      </div><div class="rp-col-md-12 rp-col-sm-6 " <?php echo $key === ( count( $payments ) - 1 ) ? esc_attr( 'id= "rp-order-history-last"') : '' ?>>
                            <?php 
                            $count = 0 ;
                        endif;
                    $count++;
                endwhile;
            endif;
            $found_post = count( $loop->posts );
            wp_reset_postdata(); 
        ?>
        
    </div>