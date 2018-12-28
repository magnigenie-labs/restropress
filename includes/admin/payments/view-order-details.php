<?php
/**
 * View Order Details
 *
 * @package     RPRESS
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * View Order Details Page
 *
 * @since  1.0.0
 * @return void
*/
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Payment ID not supplied. Please try again', 'restro-press' ), __( 'Error', 'restro-press' ) );
}

// Setup the variables
$payment_id   = absint( $_GET['id'] );
$payment      = new RPRESS_Payment( $payment_id );


// Sanity check... fail if purchase ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( __( 'The specified ID does not belong to a payment. Please try again', 'restro-press' ), __( 'Error', 'restro-press' ) );
}

$number         = $payment->number;
$payment_meta   = $payment->get_meta();
$transaction_id = esc_attr( $payment->transaction_id );
$cart_items     = $payment->cart_details;
$user_id        = $payment->user_id;
$payment_date   = strtotime( $payment->date );
$unlimited      = $payment->has_unlimited_fooditems;
$user_info      = rpress_get_payment_meta_user_info( $payment_id );
$address        = $payment->address;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$customer       = new RPRESS_Customer( $payment->customer_id );


?>
<div class="wrap rpress-wrap">
	<h2><?php printf( __( 'Payment %s', 'restro-press' ), $number ); ?></h2>
	<?php do_action( 'rpress_view_order_details_before', $payment_id ); ?>
	<form id="rpress-edit-order-form" method="post">
		<?php do_action( 'rpress_view_order_details_form_top', $payment_id ); ?>
		<div id="poststuff">
			<div id="rpress-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'rpress_view_order_details_sidebar_before', $payment_id ); ?>


							<div id="rpress-order-update" class="postbox rpress-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Update Payment', 'restro-press' ); ?></span>
								</h3>
								<div class="inside">
									<div class="rpress-admin-box">

										<?php do_action( 'rpress_view_order_details_totals_before', $payment_id ); ?>

										<div class="rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Status:', 'restro-press' ); ?></span>&nbsp;
												<select name="rpress-payment-status" class="medium-text">
													<?php foreach( rpress_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>

												<?php
												$status_help  = '<ul>';
												$status_help .= '<li>' . __( '<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'restro-press' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'restro-press' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'restro-press' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'restro-press' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'restro-press' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'restro-press' ) . '</li>';
												$status_help .= '</ul>';
												?>
												<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php echo $status_help; ?>"></span>
											</p>

											<?php if ( $payment->is_recoverable() ) : ?>
											<p>
												<span class="label"><?php _e( 'Recovery URL', 'restro-press' ); ?>:</span>
												<?php $recover_help = __( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'restro-press' ); ?>
												<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php echo $recover_help; ?>"></span>

												<input type="text" class="large-text" readonly="readonly" value="<?php echo $payment->get_recovery_url(); ?>" />
											</p>
											<?php endif; ?>
										</div>

										<div class="rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Date:', 'restro-press' ); ?></span>&nbsp;
												<input type="text" name="rpress-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text rpress_datepicker"/>
											</p>
										</div>

										<div class="rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Time:', 'restro-press' ); ?></span>&nbsp;
												<input type="text" maxlength="2" name="rpress-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text rpress-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="text" maxlength="2" name="rpress-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text rpress-payment-time-min"/>
											</p>
										</div>

										<?php do_action( 'rpress_view_order_details_update_inner', $payment_id ); ?>

										

										<?php
										$fees = $payment->fees;
										if ( ! empty( $fees ) ) : ?>
										<div class="rpress-order-fees rpress-admin-box-inside">
											<p class="strong"><?php _e( 'Fees', 'restro-press' ); ?>:</p>
											<ul class="rpress-payment-fees">
												<?php foreach( $fees as $fee ) : ?>
												<li data-fee-id="<?php echo $fee['id']; ?>"><span class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . rpress_currency_filter( $fee['amount'], $currency_code ); ?></span></li>
												<?php endforeach; ?>
											</ul>
										</div>
										<?php endif; ?>

										<?php if ( rpress_use_taxes() ) : ?>
										<div class="rpress-order-taxes rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Tax', 'restro-press' ); ?>:</span>&nbsp;
												<input name="rpress-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( rpress_format_amount( $payment->tax ) ); ?>"/>
												<?php if ( ! empty( $payment->tax_rate ) ) : ?>
													<span class="rpress-tax-rate">
														&nbsp;<?php echo $payment->tax_rate * 100; ?>%
													</span>
												<?php endif; ?>
											</p>
										</div>
										<?php endif; ?>

										<div class="rpress-order-payment rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Total Price', 'restro-press' ); ?>:</span>&nbsp;
												<?php echo rpress_currency_symbol( $payment->currency ); ?>&nbsp;<input name="rpress-payment-total" type="text" class="med-text" value="<?php echo esc_attr( rpress_format_amount( $payment->total ) ); ?>"/>
											</p>
										</div>

										<div class="rpress-order-payment-recalc-totals rpress-admin-box-inside" style="display:none">
											<p>
												<span class="label"><?php _e( 'Recalculate Totals', 'restro-press' ); ?>:</span>&nbsp;
												<a href="" id="rpress-order-recalc-total" class="button button-secondary right"><?php _e( 'Recalculate', 'restro-press' ); ?></a>
											</p>
										</div>

										<?php do_action( 'rpress_view_order_details_totals_after', $payment_id ); ?>

									</div><!-- /.rpress-admin-box -->

								</div><!-- /.inside -->

								<div class="rpress-order-update-box rpress-admin-box">
									<?php do_action( 'rpress_view_order_details_update_before', $payment_id ); ?>
									<div id="major-publishing-actions">
										<div id="delete-action">
											<a href="<?php echo wp_nonce_url( add_query_arg( array( 'rpress-action' => 'delete_payment', 'purchase_id' => $payment_id ), admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' ) ), 'rpress_payment_nonce' ) ?>" class="rpress-delete-payment rpress-delete"><?php _e( 'Delete Payment', 'restro-press' ); ?></a>
										</div>
										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'restro-press' ); ?>"/>
										<div class="clear"></div>
									</div>
									<?php do_action( 'rpress_view_order_details_update_after', $payment_id ); ?>
								</div><!-- /.rpress-order-update-box -->

							</div><!-- /#rpress-order-data -->

							<?php if( rpress_is_payment_complete( $payment_id ) ) : ?>
							<div id="rpress-order-resend-receipt" class="postbox rpress-order-data">
								<div class="inside">
									<div class="rpress-order-resend-receipt-box rpress-admin-box">
										<?php do_action( 'rpress_view_order_details_resend_receipt_before', $payment_id ); ?>
										<a href="<?php echo esc_url( add_query_arg( array( 'rpress-action' => 'email_links', 'purchase_id' => $payment_id ) ) ); ?>" id="<?php if( count( $customer->emails ) > 1 ) { echo 'rpress-select-receipt-email'; } else { echo 'rpress-resend-receipt'; } ?>" class="button-secondary alignleft"><?php _e( 'Resend Receipt', 'restro-press' ); ?></a>
										<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If fooditem URLs are included in the receipt, new file fooditem URLs will also be included with the receipt.', 'restro-press' ); ?>"></span>
										<?php if( count( $customer->emails ) > 1 ) : ?>
											<div class="clear"></div>
											<div class="rpress-order-resend-receipt-addresses" style="display:none;">
												<select class="rpress-order-resend-receipt-email">
													<option value=""><?php _e( ' -- select email --', 'restro-press' ); ?></option>
													<?php foreach( $customer->emails as $email ) : ?>
														<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo $email; ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										<?php endif; ?>
										<div class="clear"></div>
										<?php do_action( 'rpress_view_order_details_resend_receipt_after', $payment_id ); ?>
									</div><!-- /.rpress-order-resend-receipt-box -->
								</div>
							</div>
							<?php endif; ?>


							<?php 
								if( !empty($payment_id) ) :

									$delivery_type =  $payment->get_meta('_rpress_delivery_type');
									$delivery_time = $payment->get_meta( '_rpress_delivery_time');

									if( !empty($delivery_type) ) : 
							?>
							<div id="rpress-delivery-details" class="postbox rpress-order-data">
								<h3 class="hndle">
									<span><?php _e( 'Delivery Details', 'restro-press' ); ?></span>
								</h3>
								<div class="inside">
									<div class="rpress-admin-box">
										<div class="rpress-delivery-details rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Delivery Type:', 'restro-press' ); ?></span>&nbsp;
												<?php  echo ucfirst($delivery_type); ?>
												</p>
											</div>
											<div class="rpress-delivery-details rpress-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Delivery Time:', 'restro-press' ); ?></span>&nbsp;
													<?php  
														if( !empty($delivery_time) ) :
															echo $delivery_time;
														endif;
													?>
												</p>
											</div>
										</div><!-- /.column-container -->
									</div><!-- /.inside -->
								</div><!-- /#rpress-order-data -->
							<?php 
							endif;
							endif; 
							?>

							<div id="rpress-order-details" class="postbox rpress-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Payment Meta', 'restro-press' ); ?></span>
								</h3>
								<div class="inside">
									<div class="rpress-admin-box">

										<?php do_action( 'rpress_view_order_details_payment_meta_before', $payment_id ); ?>

										<?php
										if ( $gateway ) : ?>
											<div class="rpress-order-gateway rpress-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Gateway:', 'restro-press' ); ?></span>&nbsp;
													<?php echo rpress_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="rpress-order-payment-key rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Key:', 'restro-press' ); ?></span>&nbsp;
												<span><?php echo $payment->key; ?></span>
											</p>
										</div>

										<div class="rpress-order-ip rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'IP:', 'restro-press' ); ?></span>&nbsp;
												<span><?php echo rpress_payment_get_ip_address_url( $payment_id ); ?></span>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
										<div class="rpress-order-tx-id rpress-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Transaction ID:', 'restro-press' ); ?></span>&nbsp;
												<span><?php echo apply_filters( 'rpress_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id ); ?></span>
											</p>
										</div>
										<?php endif; ?>

										

										<?php do_action( 'rpress_view_order_details_payment_meta_after', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->

							</div><!-- /#rpress-order-data -->



							<div id="rpress-order-logs" class="postbox rpress-order-logs">

								<h3 class="hndle">
									<span><?php _e( 'Logs', 'restro-press' ); ?></span>
								</h3>
								<div class="inside">
									<div class="rpress-admin-box">

										<div class="rpress-admin-box-inside">

											
											<p>
												<?php $purchase_url = admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history&user=' . esc_attr( rpress_get_payment_user_email( $payment_id ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all orders for this customer', 'restro-press' ); ?></a>
											</p>
										</div>

										<?php do_action( 'rpress_view_order_details_logs_inner', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->

							</div><!-- /#rpress-order-logs -->

							<?php do_action( 'rpress_view_order_details_sidebar_after', $payment_id ); ?>

						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'rpress_view_order_details_main_before', $payment_id ); ?>

							<?php $column_count = rpress_use_taxes() ? 'columns-5' : 'columns-4'; ?>

							<?php if ( is_array( $cart_items ) ) :
								$is_qty_enabled = rpress_item_quantities_enabled() ? ' item_quantity' : '' ;
								?>
								<div id="rpress-purchased-files" class="postbox rpress-edit-purchase-element <?php echo $column_count; ?>">
									<h3 class="hndle rpress-payment-details-label-mobile">
										<span><?php printf( __( 'Purchased %s', 'restro-press' ), rpress_get_label_plural() ); ?></span>
									</h3>
									<div class="rpress-purchased-files-header row header">
										<ul class="rpress-purchased-files-list-wrapper">
											<li class="fooditem 123">
												<?php printf( _x( '%s Purchased', 'payment details purchased item title - full screen', 'restro-press' ), rpress_get_label_singular() ); ?>
											</li>

											<li class="item_price">
												<?php
													 _ex( 'Price', 'payment details purchased item price - full screen', 'restro-press' );
													//if( rpress_item_quantities_enabled() ) :
														_ex( ' & Quantity', 'payment details purchased item quantity - full screen', 'restro-press' );
													//endif;
												?>
											</li>

											<?php if ( rpress_use_taxes() ) : ?>
												<li class="item_tax">
													<?php _ex( 'Tax', 'payment details purchased item tax - full screen', 'restro-press' ); ?>
												</li>
											<?php endif; ?>

											<li class="price">
												<?php printf( _x( '%s Total', 'payment details purchased item total - full screen', 'restro-press' ), rpress_get_label_singular() ); ?>
											</li>
										</ul>
									</div>
									<?php
									$i = 0;
									foreach ( $cart_items as $key => $cart_item ) :
									 ?>
										<div class="row">
											<ul class="rpress-purchased-files-list-wrapper">
												<?php

												// Item ID is checked if isset due to the near-1.0 cart data
												$item_id    = isset( $cart_item['id']    )                                  ? $cart_item['id']                                 : $cart_item;
												$price      = isset( $cart_item['price'] )                                  ? $cart_item['price']                              : false;
												$item_price = isset( $cart_item['item_price'] )                             ? $cart_item['item_price']                         : $price;
												$subtotal   = isset( $cart_item['subtotal'] )                               ? $cart_item['subtotal']                           : $price;
												$item_tax   = isset( $cart_item['tax'] )                                    ? $cart_item['tax']                                : 0;
												$price_id   = isset( $cart_item['item_number']['options']['price_id'] )     ? $cart_item['item_number']['options']['price_id'] : null;
												$quantity   = isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity']                           : 1;
												$fooditem   = new RPRESS_Fooditem( $item_id );

												if( false === $price ) {

													// This function is only used on payments with near 1.0 cart data structure
													$price = rpress_get_fooditem_final_price( $item_id, $user_info, null );
												}
												?>

												<li class="fooditem">
													<span class="rpress-purchased-fooditem-title">
														<?php if ( ! empty( $fooditem->ID ) ) : ?>
															<a href="<?php echo admin_url( 'post.php?post=' . $item_id . '&action=edit' ); ?>">
																<?php echo $fooditem->get_name();
																if ( isset( $cart_items[ $key ]['item_number'] ) && isset( $cart_items[ $key ]['item_number']['options'] ) ) {
																	$price_options = $cart_items[ $key ]['item_number']['options'];
																	if ( rpress_has_variable_prices( $item_id ) && isset( $price_id ) ) {
																		echo ' - ' . rpress_get_price_option_name( $item_id, $price_id, $payment_id );
																	}
																}
																?>
															</a>
														<?php else: ?>
															<span class="deleted">
																<?php if ( ! empty( $cart_item['name'] ) ) : ?>
																	<?php echo $cart_item['name']; ?>&nbsp;-&nbsp;
																	<em>(<?php _e( 'Deleted', 'restro-press' ); ?>)</em>
																<?php else: ?>
																	<em><?php printf( __( '%s deleted', 'restro-press' ), rpress_get_label_singular() ); ?></em>
																<?php endif; ?>
															</span>
														<?php endif; ?>
													</span>
													<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][id]" class="rpress-payment-details-fooditem-id" value="<?php echo esc_attr( $item_id ); ?>"/>
													<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][price_id]" class="rpress-payment-details-fooditem-price-id" value="<?php echo esc_attr( $price_id ); ?>"/>

													<?php// if( ! rpress_item_quantities_enabled() ) : ?>

														<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][quantity]" class="rpress-payment-details-fooditem-quantity" value="<?php echo esc_attr( $quantity ); ?>"/>
													<?php //endif; ?>

													<?php if ( ! rpress_use_taxes() ): ?>
														<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_tax]" class="rpress-payment-details-fooditem-item-tax" value="<?php echo $item_tax; ?>" />
													<?php endif; ?>

													<?php if ( ! empty( $cart_items[ $key ]['fees'] ) ) : ?>
														<?php $fees = array_keys( $cart_items[ $key ]['fees'] ); ?>
														<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][fees]" class="rpress-payment-details-fooditem-fees" value="<?php echo esc_attr( json_encode( $fees ) ); ?>"/>
													<?php endif; ?>

												</li>

												<li class="item_price ">
													<span class="rpress-payment-details-label-mobile">
														<?php
															_ex( 'Price', 'payment details purchased item price - mobile', 'restro-press' );
																_ex( ' & Quantity', 'payment details purchased item quantity - mobile', 'restro-press' );
														?>
													</span>
													<?php echo rpress_currency_symbol( $currency_code ); ?>
													<input type="text" class="medium-text rpress-price-field rpress-payment-details-fooditem-item-price rpress-payment-item-input" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_price]" value="<?php echo rpress_format_amount( $item_price ); ?>" />
													
														&nbsp;&times;&nbsp;
														<input type="number" name="rpress-payment-details-fooditems[<?php echo $key; ?>][quantity]" class="small-text rpress-payment-details-fooditem-quantity rpress-payment-item-input" min="1" step="1" value="<?php echo $quantity; ?>" />
												</li>

												<?php if ( rpress_use_taxes() ) : ?>
												<li class="item_tax">
													<span class="rpress-payment-details-label-mobile">
														<?php _ex( 'Tax', 'payment details purchased item tax - mobile', 'restro-press' ); ?>
													</span>
													<?php echo rpress_currency_symbol( $currency_code ); ?>
													<input type="text" class="small-text rpress-price-field rpress-payment-details-fooditem-item-tax rpress-payment-item-input" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_tax]" value="<?php echo rpress_format_amount( $item_tax ); ?>" />
												</li>
												<?php endif; ?>

												<li class="price">
													<span class="rpress-payment-details-label-mobile">
														<?php printf( _x( '%s Total', 'payment details purchased item total - mobile', 'restro-press' ), rpress_get_label_singular() ); ?>
													</span>
													<span class="rpress-price-currency"><?php echo rpress_currency_symbol( $currency_code ); ?></span><span class="price-text rpress-payment-details-fooditem-amount"><?php echo rpress_format_amount( $price ); ?></span>
													<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][amount]" class="rpress-payment-details-fooditem-amount" value="<?php echo esc_attr( $price ); ?>"/>
												</li>
											</ul>

											<div class="rpress-purchased-fooditem-actions actions">
												<input type="hidden" class="rpress-payment-details-fooditem-has-log" name="rpress-payment-details-fooditems[<?php echo $key; ?>][has_log]" value="1" />
												<?php if( rpress_get_fooditem_files( $item_id, $price_id ) && rpress_is_payment_complete( $payment_id ) ) : ?>
													<span class="rpress-copy-fooditem-link-wrapper">
														<a href="" class="rpress-copy-fooditem-link" data-fooditem-id="<?php echo esc_attr( $item_id ); ?>" data-price-id="<?php echo esc_attr( $price_id ); ?>"><?php _e( 'Copy Download Link(s)', 'restro-press' ); ?></a> |
													</span>
												<?php endif; ?>
												<a href="" class="rpress-order-remove-fooditem rpress-delete" data-key="<?php echo esc_attr( $key ); ?>"><?php _e( 'Remove', 'restro-press' ); ?></a>
											</div>

											<div class="rpress-addon-items">
												<?php 
												if( !empty($fooditem->ID) ) :

													$terms = getFooditemCategoryById($fooditem->ID);
													$get_formatted_cats = getFormattedCatsList($terms);
													if( !empty($get_formatted_cats)  ) :
													?>
													<span class="order-addon-items"><?php _e( 'Addon Items', 'restro-press' ); ?></span>
													<div class="food-item-list">
													<select multiple class="addon-items-list" name="rpress-payment-details-fooditems[<?php echo $key; ?>][addon_items][]">
													<?php
													if( is_array($get_formatted_cats) ) :

														if( is_array($cart_item['addon_items']) 
															&& !empty($cart_item['addon_items']) ) :

															foreach( $cart_item['addon_items'] as $data => $addon_rows ) {
																if( is_array($addon_rows) && 
																!empty($addon_rows) ) {
																	$addons_array[] = $addon_rows['addon_id'];
																	?>
																	<option data-price="<?php echo $addon_rows['price']?>" value="<?php echo $addon_rows['addon_item_name']; ?>|<?php echo $addon_rows['addon_id']; ?>|<?php echo $addon_rows['price']?>|1" selected><?php echo $addon_rows['addon_item_name'].' ( '.html_entity_decode( rpress_currency_filter( rpress_format_amount( $addon_rows['price'] ) ), ENT_COMPAT, 'UTF-8' ).' ) '; ?></option>
																<?php
																}
															}

														endif;

														foreach( $get_formatted_cats as  $get_formatted_cat ) {

															if( !in_array($get_formatted_cat['id'], $addons_array) ) {
															?>
															<option data-price="<?php echo $get_formatted_cat['price']; ?>" 
																value="<?php echo $get_formatted_cat['name'];?>|<?php echo $get_formatted_cat['id']; ?>|<?php echo $get_formatted_cat['price'];?>|1" ><?php echo $get_formatted_cat['name'].' ( '.html_entity_decode( rpress_currency_filter( rpress_format_amount( $get_formatted_cat['price'] ) ), ENT_COMPAT, 'UTF-8' ).' ) '; ?></option>
															<?php
															}
														}
													endif;
													?>
													</select>
													</div>
													<?php
													endif;
												endif;
												?>
											</div>
											<?php
												if( isset($cart_items[$key]['instruction'] ) && !empty($cart_items[$key]['instruction']) ) :
											?>
											<span class="order-addon-items special-instructions">
													<?php _e( 'Special Instruction:', 'restro-press' ); ?>
													<?php
														echo $cart_items[$key]['instruction'];
													 ?>
												</span>
											<?php endif; ?>
											
										</div>
									<?php
									$i++;
									
									endforeach; ?>
								</div>
							<?php else : $key = 0; ?>
								<div class="row">
									<p><?php printf( __( 'No %s included with this purchase', 'restro-press' ), rpress_get_label_plural() ); ?></p>
								</div>
							<?php endif; ?>

							<div class="postbox rpress-edit-purchase-element <?php echo $column_count; ?>">

								<div class="rpress-add-fooditem-to-purchase-header row header">
									<ul class="rpress-purchased-files-list-wrapper">
										<li class="fooditem"><?php printf( __( 'Add New %s', 'restro-press' ), rpress_get_label_singular() ); ?></li>

										<li class="item_price<?php echo $is_qty_enabled; ?>">
											<?php _e( 'Price', 'restro-press' ); ?>
											<?php //if( rpress_item_quantities_enabled() ) : ?>
												<?php _e( ' & Quantity', 'restro-press' ); ?>
											<?php //endif; ?>
										</li>

										<?php if ( rpress_use_taxes() ) : ?>
											<li class="item_tax">
												<?php _e( 'Tax', 'restro-press' ); ?>
											</li>
										<?php endif; ?>

										<li class="price"><?php _e( 'Actions', 'restro-press' ); ?></li>
									</ul>
								</div>
								<div class="rpress-add-fooditem-to-purchase inside">

									<ul>
										<li class="fooditem">
											<span class="rpress-payment-details-label-mobile">
												<?php printf( _x( 'Select New %s To Add', 'payment details select item to add - mobile', 'restro-press' ), rpress_get_label_singular() ); ?>
											</span>
											<?php echo RPRESS()->html->product_dropdown( array(
												'name'   => 'rpress-order-fooditem-select',
												'id'     => 'rpress-order-fooditem-select',
												'chosen' => true
											) ); ?>
										</li>

										<li class="item_price<?php echo $is_qty_enabled; ?>">
											<span class="rpress-payment-details-label-mobile">
												<?php
												_ex( 'Price', 'payment details add item price - mobile', 'restro-press' );
												//if( rpress_item_quantities_enabled() ) :
													_ex( ' & Quantity', 'payment details add item quantity - mobile', 'restro-press' );
												//endif;
												?>
											</span>
											<?php
											echo rpress_currency_symbol( $currency_code ) . '&nbsp;';
											echo RPRESS()->html->text(
												array(
													'name'  => 'rpress-order-fooditem-price',
													'id'    => 'rpress-order-fooditem-price',
													'class' => 'medium-text rpress-price-field rpress-order-fooditem-price rpress-add-fooditem-field'
												)
											);
											?>

											<?php //if( rpress_item_quantities_enabled() ) : ?>
												&nbsp;&times;&nbsp;
												<input type="number" id="rpress-order-fooditem-quantity" name="rpress-order-fooditem-quantity" class="small-text rpress-add-fooditem-field" min="1" step="1" value="1" />
											<?php //endif; ?>
										</li>

										<?php if ( rpress_use_taxes() ) : ?>
											<li class="item_tax">
												<span class="rpress-payment-details-label-mobile">
													<?php _ex( 'Tax', 'payment details add item tax - mobile', 'restro-press' ); ?>
												</span>
												<?php
												echo rpress_currency_symbol( $currency_code ) . '&nbsp;';
												echo RPRESS()->html->text(
													array(
														'name'  => 'rpress-order-fooditem-tax',
														'id'    => 'rpress-order-fooditem-tax',
														'class' => 'small-text rpress-order-fooditem-tax rpress-add-fooditem-field'
													)
												);
												?>
											</li>
										<?php endif; ?>

										<li class="rpress-add-fooditem-to-purchase-actions actions">
											<span class="rpress-payment-details-label-mobile">
												<?php _e( 'Actions', 'restro-press' ); ?>
											</span>
											<a href="" id="rpress-order-add-fooditem" class="button button-secondary"><?php printf( __( 'Add New %s', 'restro-press' ), rpress_get_label_singular() ); ?></a>
										</li>

									</ul>

									<input type="hidden" name="rpress-payment-fooditems-changed" id="rpress-payment-fooditems-changed" value="" />
									<input type="hidden" name="rpress-payment-removed" id="rpress-payment-removed" value="{}" />

									<?php //if ( ! rpress_item_quantities_enabled() ) : ?>
										<input type="hidden" id="rpress-order-fooditem-quantity" name="rpress-order-fooditem-quantity" value="1" />
									<?php // endif; ?>

									<?php if ( ! rpress_use_taxes() ) : ?>
										<input type="hidden" id="rpress-order-fooditem-tax" name="rpress-order-fooditem-tax" value="0" />
									<?php endif; ?>

								</div><!-- /.inside -->

							</div>

							<?php do_action( 'rpress_view_order_details_files_after', $payment_id ); ?>

							<?php do_action( 'rpress_view_order_details_billing_before', $payment_id ); ?>

							<div id="rpress-customer-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Customer Details', 'restro-press' ); ?></span>
								</h3>
								<div class="inside rpress-clearfix">

									<div class="column-container customer-info">
										<div class="column">
											<?php if( ! empty( $customer->id ) ) : ?>
												<?php $customer_url = admin_url( 'edit.php?post_type=fooditem&page=rpress-customers&view=overview&id=' . $customer->id ); ?>
												<a href="<?php echo $customer_url; ?>"><?php echo $customer->name; ?> - <?php echo $customer->email; ?></a>
											<?php endif; ?>
											<input type="hidden" name="rpress-current-customer" value="<?php echo $customer->id; ?>" />
										</div>
										<div class="column">
											<a href="#change" class="rpress-payment-change-customer"><?php _e( 'Assign to another customer', 'restro-press' ); ?></a>
											&nbsp;|&nbsp;
											<a href="#new" class="rpress-payment-new-customer"><?php _e( 'New Customer', 'restro-press' ); ?></a>
										</div>
									</div>

									<div class="column-container change-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Select a customer', 'restro-press' ); ?>:</strong>
											<?php
												$args = array(
													'class'       => 'rpress-payment-change-customer-input',
													'selected'    => $customer->id,
													'name'        => 'customer-id',
													'placeholder' => __( 'Type to search all Customers', 'restro-press' ),
												);

												echo RPRESS()->html->customer_dropdown( $args );
											?>
										</div>
										<div class="column"></div>
										<div class="column">
											<strong><?php _e( 'Actions', 'restro-press' ); ?>:</strong>
											<br />
											<input type="hidden" id="rpress-change-customer" name="rpress-change-customer" value="0" />
											<a href="#cancel" class="rpress-payment-change-customer-cancel rpress-delete"><?php _e( 'Cancel', 'restro-press' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to change the customer', 'restro-press' ); ?></em></small>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Name', 'restro-press' ); ?>:</strong>&nbsp;
											<input type="text" name="rpress-new-customer-name" value="" class="medium-text"/>
										</div>
										<div class="column">
											<strong><?php _e( 'Email', 'restro-press' ); ?>:</strong>&nbsp;
											<input type="email" name="rpress-new-customer-email" value="" class="medium-text"/>
										</div>
										<div class="column">
											<strong><?php _e( 'Actions', 'restro-press' ); ?>:</strong>
											<br />
											<input type="hidden" id="rpress-new-customer" name="rpress-new-customer" value="0" />
											<a href="#cancel" class="rpress-payment-new-customer-cancel rpress-delete"><?php _e( 'Cancel', 'restro-press' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to create new customer', 'restro-press' ); ?></em></small>
										</div>
									</div>

									<?php
									// The rpress_payment_personal_details_list hook is left here for backwards compatibility
									do_action( 'rpress_payment_personal_details_list', $payment_meta, $user_info );
									do_action( 'rpress_payment_view_details', $payment_id );
									?>

								</div><!-- /.inside -->
							</div><!-- /#rpress-customer-details -->

							<div id="rpress-billing-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Billing Address', 'restro-press' ); ?></span>
								</h3>
								<div class="inside rpress-clearfix">

									<div id="rpress-order-address">

										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'restro-press' ); ?></strong><br/>
														<input type="text" name="rpress-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="large-text" />
													</p>
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'restro-press' ); ?></strong><br/>
														<input type="text" name="rpress-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="large-text" />
													</p>

												</div>
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'restro-press' ); ?></strong><br/>
														<input type="text" name="rpress-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="large-text"/>

													</p>
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'restro-press' ); ?></strong><br/>
														<input type="text" name="rpress-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="large-text"/>

													</p>
												</div>
												<div class="column">
													<p id="rpress-order-address-country-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'restro-press' ); ?></strong><br/>
														<?php
														echo RPRESS()->html->select( array(
															'options'          => rpress_get_country_list(),
															'name'             => 'rpress-payment-address[0][country]',
															'id'               => 'rpress-payment-address-country',
															'selected'         => $address[ 'country' ],
															'show_option_all'  => false,
															'show_option_none' => false,
															'chosen'           => true,
															'placeholder'      => __( 'Select a country', 'restro-press' ),
															'data'             => array(
																'search-type'        => 'no_ajax',
																'search-placeholder' => __( 'Type to search all Countries', 'restro-press' ),
															),
														) );
														?>
													</p>
													<p id="rpress-order-address-state-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'restro-press' ); ?></strong><br/>
														<?php
														$states = rpress_get_shop_states( $address['country'] );
														if( ! empty( $states ) ) {
															echo RPRESS()->html->select( array(
																'options'          => $states,
																'name'             => 'rpress-payment-address[0][state]',
																'id'               => 'rpress-payment-address-state',
																'selected'         => $address[ 'state' ],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => __( 'Select a state', 'restro-press' ),
																'data'             => array(
																	'search-type'        => 'no_ajax',
																	'search-placeholder' => __( 'Type to search all States/Provinces', 'restro-press' ),
																),
															) );
														} else { ?>
															<input type="text" name="rpress-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="large-text"/>
															<?php
														} ?>
													</p>
												</div>
											</div>
										</div>
									</div><!-- /#rpress-order-address -->

									<?php do_action( 'rpress_payment_billing_details', $payment_id ); ?>

								</div><!-- /.inside -->
							</div><!-- /#rpress-billing-details -->

							<?php do_action( 'rpress_view_order_details_billing_after', $payment_id ); ?>

							<div id="rpress-payment-notes" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Payment Notes', 'restro-press' ); ?></span></h3>
								<div class="inside">
									<div id="rpress-payment-notes-inner">
										<?php
										$notes = rpress_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) :
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo rpress_get_payment_note_html( $note, $payment_id );

											endforeach;
										else :
											$no_notes_display = '';
										endif;
										echo '<p class="rpress-no-payment-notes"' . $no_notes_display . '>'. __( 'No payment notes', 'restro-press' ) . '</p>';
										?>
									</div>
									<textarea name="rpress-payment-note" id="rpress-payment-note" class="large-text"></textarea>

									<p>
										<button id="rpress-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'restro-press' ); ?></button>
									</p>
									<div class="clear"></div>
								</div><!-- /.inside -->
							</div><!-- /#rpress-payment-notes -->

							<?php do_action( 'rpress_view_order_details_main_after', $payment_id ); ?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #rpress-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'rpress_view_order_details_form_bottom', $payment_id ); ?>
		<?php wp_nonce_field( 'rpress_update_payment_details_nonce' ); ?>
		<input type="hidden" name="rpress_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="rpress_action" value="update_payment_details"/>
	</form>
	<?php do_action( 'rpress_view_order_details_after', $payment_id ); ?>
</div><!-- /.wrap -->

<div id="rpress-fooditem-link"></div>
