<?php if( ! empty( $_GET['rpress-verify-success'] ) ) : ?>
<p class="rpress-account-verified rpress_success">
	<?php esc_html_e( 'Your account has been successfully verified!', 'restropress' ); ?>
</p>
<?php
endif;
/**
 * This template is used to display the order history of the current user.
 */
if ( is_user_logged_in() ):
	$payments = rpress_get_users_orders( get_current_user_id(), 10, true, 'any' );
	if ( $payments ) :
		do_action( 'rpress_before_order_history', $payments ); ?>
		<div id="rpress_user_history" class="rpress-table">
			<div class="repress-history-inner">
				<div class="rp-col-md-12 rp-col-sm-6">
					<?php $count = 1 ; ?>
					<?php foreach ( $payments as $key => $payment ) : ?>
						<?php 
							$payment = new RPRESS_Payment( $payment->ID ); 

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

							do_action( 'rpress_order_history_row_start', $payment->ID, $payment->payment_meta );
						?>
						<div class="rp-col-lg-6 rp-col-md-6 rp-col-sm-12 rp-col-xs-12 rpress_purchase_row">
							<div class="rpress-history-card">
								<div class="rp-col-md-9 rpress-his-col">
									<div class="rpress-order-id rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Order :','restropress' ); ?></span>#<?php echo esc_html( $payment->number ); ?></div>
									<div class="rpress-od-date rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Placed on :','restropress' ); ?></span><?php echo date_i18n( get_option('date_format'), strtotime( $payment->date ) ); ?></div>

									<!-- Address  -->
									<?php if( $address ) : ?>
										<div class="rpress-adds rpress-lable-txt">
											<span class="rp-bold-hs"><?php esc_html_e('Address :','restropress' ); ?></span><?php echo wp_kses_post( $address ); ?>
										</div>
									<?php endif ;?>

									<div class="rpress-order-type rpress-lable-txt"><span class="rp-bold-hs"><?php esc_html_e('Order Type :','restropress' ); ?></span> <?php echo esc_html( $service_type ) ; ?></div>
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
										<span><?php esc_html_e( 'Total Paid:', 'restropress' ); ?> </span><?php echo rpress_currency_filter( rpress_format_amount( $payment->total ) ); ?>
									</div>
								</div>
							</div>
						</div>
						<?php if(2 === $count):?>
							</div><div class="rp-col-md-12 rp-col-sm-6 " <?php echo $key === (count($payments) - 1) ? 'id= "rp-order-history-last"' : '' ?>>
							<?php $count = 0 ;?>
						<?php endif;?>
						<?php $count++ ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="rp-col-md-12 rp-infinite-load-main">
				<div class="rp-infinite-load" id="rp-order-history-infi-load-container">
				</div>
			</div>
		</div>	
		<?php do_action( 'rpress_after_order_history', $payments ); ?>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<p class="rpress-no-purchases"><?php esc_html_e('You have not made any orders','restropress' ); ?></p>
	<?php endif;
endif;
