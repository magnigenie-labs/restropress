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
	wp_die( __( 'Payment ID not supplied. Please try again', 'restropress' ), __( 'Error', 'restropress' ) );
}

// Setup the variables
$payment_id   = absint( $_GET['id'] );
$payment      = new RPRESS_Payment( $payment_id );

// Sanity check... fail if purchase ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( __( 'The specified ID does not belong to a payment. Please try again', 'restropress' ), __( 'Error', 'restropress' ) );
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
$order_status  	= rpress_get_order_status( $payment_id );
$phone			= !empty( $payment_meta['phone'] ) ? $payment_meta['phone'] : ( !empty( $address_info['phone'] ) ? $address_info['phone'] : '' );

$address_info	= get_post_meta( $payment_id, '_rpress_delivery_address', true );

$user_address 	= !empty( $address_info['address'] ) ? $address_info['address'] . ', ' : '';
$user_address 	.= !empty( $address_info['flat'] ) ? $address_info['flat'] . ', ' : '';
$user_address 	.= !empty( $address_info['city'] ) ? $address_info['city'] . ', ' : '';
$user_address 	.= !empty( $address_info['postcode'] ) ? $address_info['postcode']  : '';

$service_type 	= $payment->get_meta( '_rpress_delivery_type' );
$service_time 	= $payment->get_meta( '_rpress_delivery_time' );
$service_date 	= $payment->get_meta( '_rpress_delivery_date' );
$order_note		= $payment->get_meta( '_rpress_order_note' );
$discount		= rpress_get_discount_price_by_payment_id( $payment_id );

$customer_name  = is_array( $payment_meta['user_info'] ) ? $payment_meta['user_info']['first_name'] . ' ' . $payment_meta['user_info']['last_name'] : $customer->name;
$customer_email = is_array( $payment_meta['user_info'] ) ? $payment_meta['user_info']['email'] : $customer->email;

?>

<div class="wrap rpress-wrap">
	<h2>
		<?php printf( __( 'Order #%s', 'restropress' ), $number ); ?>
		<?php do_action( 'rpress_after_order_title', $payment_id ); ?>
	</h2>
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
									<span><?php esc_html_e( 'Update Order', 'restropress' ); ?></span>
								</h3>
								<div class="inside">
			<div class="rpress-admin-box">

				<?php do_action( 'rpress_view_order_details_totals_before', $payment_id ); ?>

				<div class="rpress-admin-box-inside">

					<p>
						<span class="label"><?php esc_html_e( 'Order Status:', 'restropress' ); ?></span>
						<select name="rpress_order_status" class="medium-text">
						<?php foreach( rpress_get_order_statuses() as $key => $status ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $order_status, $key, true ); ?> >
							<?php echo esc_html( $status ); ?>
							</option>
						<?php endforeach; ?>
						</select>
						<?php
						$order_status_help = '<ul>';
						$order_status_help .= '<li>' . __( '<strong>Pending</strong>: When the order is initially received by the restaurant.', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>Accepted</strong>: When the restaurant accepts the order.', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>Processing</strong>: When the restaurant starts preparing the food.', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>Ready</strong>: When the order has been prepared by the restaurant.', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>In Transit</strong>: When the order is out for delivery', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>Cancelled</strong>: Order has been cancelled', 'restropress' ) . '</li>';
						$order_status_help .= '<li>' . __( '<strong>Completed</strong>: Payment has been done and the order has been completed.', 'restropress' ) . '</li>';
						$order_status_help .= '</ul>';
						?>
						<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( $order_status_help ); ?>"></span>
					</p>
				</div>

				<div class="rpress-admin-box-inside">

					<p>
						<span class="label"><?php esc_html_e( 'Payment:', 'restropress' ); ?></span>
						<select name="rpress-payment-status" class="medium-text rpress-payment-status">
							<?php foreach( rpress_get_payment_statuses() as $key => $status ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
							<?php endforeach; ?>
						</select>

						<?php
						$status_help  = '<ul>';
						$status_help .= '<li>' . __( '<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'restropress' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'restropress' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'restropress' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'restropress' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'restropress' ) . '</li>';
						$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'restropress' ) . '</li>';
						$status_help .= '</ul>';
						?>
						<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( $status_help ); ?>"></span>
					</p>
				</div>


					<?php if ( $payment->is_recoverable() ) : ?>
						<div class="rpress-admin-box-inside">
					<p>
						<span class="label"><?php esc_html_e( 'Recovery URL', 'restropress' ); ?>:</span>
						<?php $recover_help = esc_html__( 'Pending and abandoned payments can be resumed by the customer, using this custom URL. Payments can be resumed only when they do not have a transaction ID from the gateway.', 'restropress' ); ?>
						<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( $recover_help ); ?>"></span>
						<input type="text" class="large-text" readonly="readonly" value="<?php echo esc_url( $payment->get_recovery_url() ) ; ?>" />
					</p>
				</div>
					<?php endif; ?>

					<div class="rpress-admin-box-inside">
						<p>
							<span class="label"><?php esc_html_e( 'Date:', 'restropress' ); ?></span>
							<input type="text" name="rpress-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text rpress_datepicker"/>
						</p>
					</div>

					<div class="rpress-admin-box-inside">
						<p>
							<span class="label"><?php esc_html_e( 'Time:', 'restropress' ); ?></span>
							<input type="text" maxlength="2" name="rpress-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text rpress-payment-time-hour"/>
							<input type="text" maxlength="2" name="rpress-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text rpress-payment-time-min"/>
						</p>
					</div>

					<?php
					$fees = $payment->fees;
					if ( ! empty( $fees ) ) : ?>
					<div class="rpress-admin-box-inside">
						<p class="rpress-order-fees strong">
							<span class="label"><?php esc_html_e( 'Fees:', 'restropress' ); ?></span>
							<ul class="rpress-payment-fees">
								<?php foreach( $fees as $fee ) : ?>
									<li data-fee-id="<?php echo esc_attr( $fee['id'] ); ?>"><span class="fee-label"><?php echo esc_html( $fee['label'] ) . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . rpress_currency_filter( $fee['amount'], $currency_code ); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</p>
					</div>
					<?php endif; ?>

					<?php if ( rpress_use_taxes() ) : ?>
					<div class="rpress-admin-box-inside">
							<p class="rpress-order-taxes">
								<span class="label"><?php echo esc_html( rpress_get_tax_name() ); ?>:</span>
								<input name="rpress-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( rpress_format_amount( $payment->tax ) ); ?>"/>
								<?php if ( ! empty( $payment->tax_rate ) ) : ?>
									<span class="rpress-tax-rate">
										<?php echo  floatval($payment->tax_rate * 100); ?>%
									</span>
								<?php endif; ?>
							</p>
					</div>
					<?php endif; ?>

					<?php if ( !empty( $discount ) ) : ?>
						<div class="rpress-admin-box-inside">
							<p class="rpress-order-discount">
								<span class="label"><?php esc_html_e( 'Coupon', 'restropress' ); ?>:</span>&nbsp;
								<?php echo  esc_html( $discount ); ?>
							</p>
						</div>
					<?php endif; ?>

					<div class="rpress-admin-box-inside">
						<p class="rpress-order-payment">
							<span class="label"><?php esc_html_e( 'Total Price', 'restropress' ); ?>:</span>&nbsp;
							<?php echo rpress_currency_symbol( $payment->currency ); ?>&nbsp;<input name="rpress-payment-total" type="text" class="med-text" value="<?php echo esc_attr( rpress_format_amount( $payment->total ) ); ?>"/>
						</p>
					</div>

				<div class="rpress-order-payment-recalc-totals rpress-admin-box-inside" style="display:none">
					<p>
						<span class="label"><?php esc_html_e( 'Recalculate Totals', 'restropress' ); ?>:</span>&nbsp;
						<a href="" id="rpress-order-recalc-total" class="button button-secondary right"><?php esc_html_e( 'Recalculate', 'restropress' ); ?></a>
					</p>
				</div>

				<?php do_action( 'rpress_view_order_details_totals_after', $payment_id ); ?>

			</div><!-- /.rpress-admin-box -->
		</div><!-- /.inside -->

								<div class="rpress-order-update-box rpress-admin-box">
			<?php do_action( 'rpress_view_order_details_update_before', $payment_id ); ?>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'rpress-action' => 'delete_payment', 'purchase_id' => $payment_id ), admin_url( 'admin.php?page=rpress-payment-history' ) ), 'rpress_payment_nonce' ) )?>" class="rpress-delete-payment rpress-delete"><?php esc_html_e( 'Delete Order', 'restropress' ); ?></a>
				</div>
				<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Order', 'restropress' ); ?>" />
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
				<a href="<?php echo esc_url( add_query_arg( array( 'rpress-action' => 'email_links', 'purchase_id' => $payment_id ) ) ); ?>" id="<?php if( count( $customer->emails ) > 1 ) { echo 'rpress-select-receipt-email'; } else { echo 'rpress-resend-receipt'; } ?>" class="button-secondary alignleft"><?php esc_html_e( 'Resend Receipt', 'restropress' ); ?></a>
				<span alt="f223" class="rpress-help-tip dashicons dashicons-editor-help" title="<?php esc_html_e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If fooditem URLs are included in the receipt, new file fooditem URLs will also be included with the receipt.', 'restropress' ); ?>"></span>
				<?php if( count( $customer->emails ) > 1 ) : ?>
					<div class="clear"></div>
					<div class="rpress-order-resend-receipt-addresses" style="display:none;">
						<select class="rpress-order-resend-receipt-email">
							<option value=""><?php esc_html_e( ' -- select email --', 'restropress' ); ?></option>
							<?php foreach( $customer->emails as $email ) : ?>
							<option value="<?php echo urlencode( sanitize_email( $email ) ); ?>"><?php echo esc_html( $email ); ?></option>
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

	<div id="rpress-order-details" class="postbox rpress-order-data rpress-payment-info-wrap">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Payment Info', 'restropress' ); ?></span>
		</h3>
		<div class="inside">
			<div class="rpress-admin-box order-payment-info">
				<?php do_action( 'rpress_view_order_details_payment_meta_before', $payment_id ); ?>
					<?php if ( $gateway ) : ?>
					<div class="rpress-admin-box-inside">
						<p class="rpress-order-gateway">
							<span class="label"><?php esc_html_e( 'Gateway:', 'restropress' ); ?></span>
							<?php echo esc_html( rpress_get_gateway_admin_label( $gateway ) ); ?>
						</p>
					</div>
					<?php endif; ?>

					<div class="rpress-admin-box-inside">
						<p class="rpress-order-payment-key">
							<span class="label"><?php esc_html_e( 'Key:', 'restropress' ); ?></span><?php echo esc_html( $payment->key ) ; ?>
						</p>
					</div>

					<div class="rpress-admin-box-inside">
						<p class="rpress-order-ip">
							<span class="label"><?php esc_html_e( 'IP:', 'restropress' ); ?></span>
							<span><?php echo rpress_payment_get_ip_address_url( $payment_id ); ?></span>
						</p>
					</div>

					<?php if ( $transaction_id ) : ?>
						<div class="rpress-admin-box-inside">
							<p class="rpress-order-tx-id">
							<span class="label"><?php esc_html_e( 'Transaction ID:', 'restropress' ); ?></span>
							<span><?php echo apply_filters( 'rpress_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id ); ?></span>
							</p>
						</div>
					<?php endif; ?>

				<?php do_action( 'rpress_view_order_details_payment_meta_after', $payment_id ); ?>

			</div><!-- /.column-container -->
		</div><!-- /.inside -->
	</div><!-- /#rpress-order-data -->

	<div id="rpress-payment-notes" class="postbox">
		<h3 class="hndle">
			<span><?php esc_html_e( 'Payment Notes', 'restropress' ); ?></span>
		</h3>
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
				echo '<p class="rpress-no-payment-notes"' . esc_attr( $no_notes_display ). '>'. __( 'No payment notes', 'restropress' ) . '</p>'; ?>
			</div>

			<textarea name="rpress-payment-note" id="rpress-payment-note" class="large-text"></textarea>

			<p>
				<button id="rpress-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php esc_html_e( 'Add Note', 'restropress' ); ?></button>
			</p>
			<div class="clear"></div>
		</div><!-- /.inside -->
	</div><!-- /#rpress-payment-notes -->

	<div id="rpress-order-logs" class="postbox rpress-order-logs">

		<h3 class="hndle">
			<span><?php esc_html_e( 'Logs', 'restropress' ); ?></span>
		</h3>

		<div class="inside">
			<div class="rpress-admin-box">
				<div class="rpress-admin-box-inside">
					<p>
						<?php $purchase_url = admin_url( 'admin.php?page=rpress-payment-history&user=' . esc_attr( rpress_get_payment_user_email( $payment_id ) ) ); ?>
						<a class="customer-order-logs" href="<?php echo esc_url( $purchase_url ); ?>"><?php esc_html_e( 'View all orders for this customer', 'restropress' ); ?></a>
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


    	<div id="rpress-customer-details" class="postbox">
			<h3 class="hndle">
				<span><?php esc_html_e( 'Order Details', 'restropress' ); ?></span>
			</h3>
			<div class="inside rpress-clearfix">

				<div class="column-container customer-info">
					<div class="column">
						<?php if( ! empty( $customer->id ) ) : ?>
							<?php $customer_url = admin_url( 'admin.php?page=rpress-customers&view=overview&id=' . $customer->id ); ?>
							<a href="<?php echo esc_url( $customer_url ); ?>"><?php echo esc_html( $customer_name ); ?> - <?php echo sanitize_email( $customer_email ); ?></a>
						<?php endif; ?>
						<input type="hidden" name="rpress-current-customer" value="<?php echo esc_attr( $customer->id ); ?>" />
						<div style="margin-top:10px; margin-bottom:10px;">
							<strong><?php esc_html_e('Phone:', 'restropress'); ?> </strong>
							<?php echo esc_html( $phone ); ?>
						</div>
					</div>
					<div class="column">
						<a href="#change" class="rpress-payment-change-customer"><?php esc_html_e( 'Assign to another customer', 'restropress' ); ?></a>
						&nbsp;|&nbsp;
						<a href="#new" class="rpress-payment-new-customer"><?php esc_html_e( 'New Customer', 'restropress' ); ?></a>
					</div>
				</div>

				<div class="column-container change-customer" style="display: none">
					<div class="column">
						<strong><?php esc_html_e( 'Select a customer', 'restropress' ); ?>:</strong>
						<?php
							$args = array(
								'class'       => 'rpress-payment-change-customer-input',
								'selected'    => $customer->id,
								'name'        => 'customer-id',
								'placeholder' => __( 'Type to search all Customers', 'restropress' ),
							);

							echo RPRESS()->html->customer_dropdown( $args );
						?>
					</div>
					<div class="column"></div>
					<div class="column">
						<strong><?php esc_html_e( 'Actions', 'restropress' ); ?>:</strong>
						<br />
						<input type="hidden" id="rpress-change-customer" name="rpress-change-customer" value="0" />
						<a href="#cancel" class="rpress-payment-change-customer-cancel rpress-delete"><?php esc_html_e( 'Cancel', 'restropress' ); ?></a>
					</div>
					<div class="column">
						<small><em>*<?php esc_html_e( 'Click "Save Order" to change the customer', 'restropress' ); ?></em></small>
					</div>
				</div>

				<div class="column-container new-customer" style="display: none">
					<div class="column">
						<strong><?php esc_html_e( 'Name', 'restropress' ); ?>:</strong>&nbsp;
						<input type="text" name="rpress-new-customer-name" value="" class="medium-text"/>
					</div>
					<div class="column">
						<strong><?php esc_html_e( 'Email', 'restropress' ); ?>:</strong>&nbsp;
						<input type="email" name="rpress-new-customer-email" value="" class="medium-text"/>
					</div>
					<div class="column">
						<strong><?php esc_html_e( 'Actions', 'restropress' ); ?>:</strong>
						<br />
						<input type="hidden" id="rpress-new-customer" name="rpress-new-customer" value="0" />
						<a href="#cancel" class="rpress-payment-new-customer-cancel rpress-delete"><?php esc_html_e( 'Cancel', 'restropress' ); ?></a>
					</div>
					<div class="column">
						<small><em>*<?php esc_html_e( 'Click "Save Order" to create new customer', 'restropress' ); ?></em></small>
					</div>
				</div>

				<div class="column-container order-info">

					<div class="column">

						<?php apply_filters( 'rpress_view_service_details_before', $payment_id ); ?>

						<div class="rpress-delivery-details">
							<p>
								<strong><?php esc_html_e( 'Service date: ', 'restropress' ); ?></strong>
								<?php if( !empty( $service_date ) ) :
									$service_date = rpress_local_date( $service_date );
									echo apply_filters( 'rpress_service_date_view', $service_date );
								endif; ?>
							</p>
						</div>

						<div class="rpress-delivery-details">
							<p class="rp-service-details">
								<strong><?php esc_html_e( 'Service type: ', 'restropress' ); ?></strong><?php //echo rpress_service_label( $service_type ); ?>
								<select class="medium-text" name="rp_service_type">
			                        <?php
			                        $service_types = rpress_get_service_types();
			                        foreach( $service_types as $service_id => $service_label ) { ?>
			                            <option value="<?php echo esc_attr( $service_id ); ?>" <?php echo selected( $service_type, $service_id, true ) ?>><?php echo esc_html( $service_label ); ?></option>
			                        <?php } ?>
			                    </select>
							</p>
						</div>

						<?php if( !empty( $service_time ) ) : ?>
							<div class="rpress-delivery-details">
								<p class="rp-service-time">
									<strong><?php esc_html_e( 'Service time: ', 'restropress' ); ?></strong>
									<select name="rp_service_time" class="medium-text">
				                        <?php echo rp_get_store_service_hours( $service_type, false, $service_time ); ?>
				                    </select>
								</p>
							</div>
						<?php endif; ?>

						<?php apply_filters( 'rpress_view_service_details_after', $payment_id ); ?>

					</div>

					<?php if( $service_type == 'delivery' ) : ?>
						<div class="column">
							<div class="rpress-delivery-address">
								<h3><?php echo sprintf( __( '%s address:' ), rpress_service_label( $service_type ) );?></h3>
								<p><?php echo apply_filters( 'rpress_admin_receipt_delivery_address', $user_address, $address_info ); ?></p>
							</div>
						</div>
					<?php endif; ?>

				</div>

				<?php if( !empty( $order_note ) ) : ?>
					<div class="column-container customer-instructions">
						<h3><?php echo sprintf( __( '%s instructions:' ), rpress_service_label( $service_type ) );?></h3>
						<?php echo esc_html( $order_note ); ?>
					</div>
				<?php endif;

				// The rpress_payment_personal_details_list hook is left here for backwards compatibility
				do_action( 'rpress_payment_personal_details_list', $payment_id, $payment_meta, $user_info );
				do_action( 'rpress_payment_view_details', $payment_id );
				?>

			</div><!-- /.inside -->
		</div><!-- /#rpress-customer-details -->

    	<?php do_action( 'rpress_view_order_details_main_before', $payment_id ); ?>
    	<?php $column_count = rpress_use_taxes() ? 'columns-5' : 'columns-4'; ?>

    	<?php
    	if ( is_array( $cart_items ) ) :
    		$is_qty_enabled = rpress_item_quantities_enabled() ? ' item_quantity' : '' ; ?>
    		<div id="rpress-purchased-items" class="postbox rpress-edit-purchase-element <?php echo esc_attr( $column_count ); ?>">
    			<div class="rpress-purchased-items-header row header">
    				<ul class="rpress-purchased-items-list-header">
    					<li class="fooditem"><?php printf( _x( '%s Purchased', 'payment details purchased item title - full screen', 'restropress' ), rp_get_label_singular() ); ?></li>
    					<li class="item_price">
    						<?php _ex( 'Price', 'payment details purchased item price - full screen', 'restropress' ); ?>
    						<?php _ex( ' & Quantity', 'payment details purchased item quantity - full screen', 'restropress' ); ?>
    					</li>
    					<?php if ( rpress_use_taxes() ) : ?>
                        <li class="item_tax"><?php _ex( 'Tax', 'payment details purchased item tax - full screen', 'restropress' ); ?></li>
                    	<?php endif; ?>
                    	<li class="price"><?php printf( _x( '%s Total', 'payment details purchased item total - full screen', 'restropress' ), rp_get_label_singular() ); ?>
                    	</li>
                    </ul>
                </div>

                <?php
                $i = 0;
                foreach ( $cart_items as $key => $cart_item ) :
                	$item_id = isset( $cart_item['id'] ) ? $cart_item['id'] : $cart_item;
					$fooditem = new RPRESS_Fooditem( $item_id );
					$fooditem_name = ! empty( $fooditem->ID ) ? $fooditem->get_name() : '';
					$price = isset( $cart_item['price'] ) ? $cart_item['price'] : false;
					$item_price = isset( $cart_item['item_price'] ) ? $cart_item['item_price'] : $price;
					$subtotal = isset( $cart_item['subtotal'] ) ? $cart_item['subtotal'] : $price;
					$item_tax = isset( $cart_item['tax'] ) ? $cart_item['tax'] : 0;
					$price_id   = isset( $cart_item['item_number']['options']['price_id'] ) ? $cart_item['item_number']['options']['price_id'] : null;
					$quantity   = isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity'] : 1;

                    if( false === $price ) {
                    	// This function is only used on payments with near 1.0 cart data structure
                    	$price = rpress_get_fooditem_final_price( $item_id, $user_info, null );
                    } ?>

                    <div class="row rpress-purchased-row">

                      	<div class="rpress-order-items-wrapper">
                      		<ul class="rpress-purchased-items-list-wrapper <?php echo esc_attr( $key ); ?>">
                      			<li class="fooditem">
                      				<span class="rpress-purchased-fooditem-actions actions">
		                                <input type="hidden" class="rpress-payment-details-fooditem-has-log" name="rpress-payment-details-fooditems[<?php echo $key; ?>][has_log]" value="1" />
		                                <a href="" class="rpress-order-remove-fooditem rpress-delete" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( '&times;', 'restropress' ); ?></a>
		                            </span>
                      				<span class="rpress-purchased-fooditem-title">
                      					<?php if ( ! empty( $fooditem->ID ) ) : ?>
                      						<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $item_id . '&action=edit' ) ); ?>">
                      							<?php echo sanitize_title( $fooditem->get_name() );
                      							if ( isset( $cart_items[ $key ]['item_number'] ) && isset( $cart_items[ $key ]['item_number']['options'] ) ) {
                      								$price_options = $cart_items[ $key ]['item_number']['options'];
                      								if ( rpress_has_variable_prices( $item_id ) && isset( $price_id ) ) {
                      									echo ' - ' . rpress_get_price_option_name( $item_id, $price_id, $payment_id );
                      								}
                      							} ?>
                      						</a>
                      					<?php else: ?>
                  							<span class="deleted">
                  								<?php if ( ! empty( $cart_item['name'] ) ) : ?>
                  									<?php echo esc_html( $cart_item['name'] ); ?>&nbsp;-&nbsp;
                  									<em>(<?php esc_html_e( 'Deleted', 'restropress' ); ?>)</em>
                  								<?php else: ?>
                  									<em><?php printf( __( '%s deleted', 'restropress' ), rpress_get_label_singular() ); ?></em>
                  								<?php endif; ?>
                  							</span>
                  						<?php endif; ?>
                  					</span>

                  					<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][id]" class="rpress-payment-details-fooditem-id" value="<?php echo esc_attr( $item_id ); ?>"/>

                  					<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][price_id]" class="rpress-payment-details-fooditem-price-id" value="<?php echo esc_attr( $price_id ); ?>"/>

                  					<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][quantity]" class="rpress-payment-details-fooditem-quantity" value="<?php echo esc_attr( $quantity ); ?>" />

                  					<?php if ( ! rpress_use_taxes() ): ?>
                  						<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_tax]" class="rpress-payment-details-fooditem-item-tax" value="<?php echo esc_attr( $item_tax ); ?>" />
                  					<?php endif; ?>

                  					<?php if ( ! empty( $cart_items[ $key ]['fees'] ) ) :
                  						$fees = array_keys( $cart_items[ $key ]['fees'] ); ?>
                  						<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][fees]" class="rpress-payment-details-fooditem-fees" value="<?php echo esc_attr( json_encode( $fees ) ); ?>"/>
                  					<?php endif; ?>
                  				</li>

                  				<li class="item_price">
                  					<span class="rpres-order-price-wrap">
                  						<span class="rpress-payment-details-label-mobile">
                  							<?php _ex( 'Price', 'payment details purchased item price - mobile', 'restropress' ); ?>
                  						</span>
                  						<?php echo rpress_currency_symbol( $currency_code ); ?>
                  						<input type="text" class="rpress-order-input medium-text rpress-price-field rpress-payment-details-fooditem-item-price rpress-payment-item-input" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_price]" value="<?php echo rpress_format_amount( $item_price ); ?>" />
                  					</span>

                  					<span class="rpres-order-quantity-wrap">
                  						<span class="rpress-payment-details-label-mobile">
                  							<?php _ex( 'Quantity', 'payment details purchased item quantity - mobile', 'restropress' ); ?>
                  						</span>
                  						<input type="number" name="rpress-payment-details-fooditems[<?php echo $key; ?>][quantity]" class="small-text rpress-payment-details-fooditem-quantity rpress-payment-item-input rpress-order-input" min="1" step="1" value="<?php echo esc_attr( $quantity ); ?>" />
                  					</span>
                  				</li>

                  				<?php if ( rpress_use_taxes() ) : ?>
                  				<li class="item_tax">
                  					<span class="rpress-payment-details-label-mobile"><?php echo rpress_get_tax_name(); ?></span>
                  					<?php echo rpress_currency_symbol( $currency_code ); ?>
                  					<input type="text" class="small-text rpress-price-field rpress-payment-details-fooditem-item-tax rpress-payment-item-input rpress-order-input" name="rpress-payment-details-fooditems[<?php echo $key; ?>][item_tax]" value="<?php echo rpress_format_amount( $item_tax ); ?>" />
                  				</li>
                  				<?php endif; ?>

                  				<li class="price">
                  					<span class="rpress-payment-details-label-mobile">
                  						<?php printf( _x( '%s Total Price', 'payment details purchased item total - mobile', 'restropress' ), rpress_get_label_singular() ); ?>
                  					</span>
                  					<span class="rpress-price-currency"><?php echo rpress_currency_symbol( $currency_code ); ?></span>
                  					<span class="price-text rpress-payment-details-fooditem-amount"><?php echo rpress_format_amount( $price ); ?></span>
                  					<input type="hidden" name="rpress-payment-details-fooditems[<?php echo $key; ?>][amount]" class="rpress-payment-details-fooditem-amount" value="<?php echo esc_attr( $price ); ?>"/>
                  				</li>
                  			</ul>

                  			<!-- Addon Items Starts Here -->
                  			<div class="rpress-addon-items">
                  				<?php if( !empty( $fooditem->ID ) ) : ?>
                  					<span class="order-addon-items">
                  						<?php esc_html_e( 'Addon Items', 'restropress' ); ?>
                  					</span>

	<div class="food-item-list">
		<select multiple class="addon-items-list" name="rpress-payment-details-fooditems[<?php echo $key; ?>][addon_items][]">
			<?php
			$addons = get_post_meta( $fooditem->ID, '_addon_items', array() );
			if ( is_array( $addons ) && !empty( $addons ) ) :
				foreach( $addons as $addon_items ) :
					if ( is_array( $addon_items ) ) :
						foreach( $addon_items as $addon_key => $addon_item ) :
							$addon_id = isset( $addon_item['category'] ) ? $addon_item['category'] : '';
							$get_addons = rpress_get_addons( $addon_id );
							if ( is_array( $get_addons ) && !empty( $get_addons ) ) :
								foreach( $get_addons as $get_addon ) :
									$addon_item_id = $get_addon->term_id;
									$addon_item_name = $get_addon->name;
									$addon_slug = $get_addon->slug;
									$addon_raw_price = rpress_get_addon_data( $addon_item_id, '_price' );
									$addon_price = !empty( $addon_raw_price ) ? rpress_currency_filter( rpress_format_amount( $addon_raw_price )) : '';
									$selected_addon_items = isset( $cart_item['addon_items'] ) ? $cart_item['addon_items'] : array();
									if ( !empty( $selected_addon_items ) ) {
										foreach( $selected_addon_items as $selected_addon_item ) {
											$selected_addon_id = !empty( $selected_addon_item['addon_id'] ) ? $selected_addon_item['addon_id'] : '';
											if ( $selected_addon_id == $addon_item_id ) { ?>
												<option selected data-price="<?php echo esc_attr( $addon_price ); ?>" data-id="<?php echo esc_attr( $addon_item_id ); ?>" value="<?php echo esc_attr( $addon_item_name ) . '|' . esc_attr( $addon_item_id ) . '|' . esc_attr( $addon_raw_price ) .'|'. '1' ; ?>">
													<?php
														echo esc_html( $addon_item_name );
														if( !empty( $addon_price ) ) echo ' (' . rpress_sanitize_amount( $addon_price ) . ') ';
													?>
												</option> <?php
											}
										}
									} ?>

                                    <option data-price="<?php echo esc_attr( $addon_price ); ?>" data-id="<?php echo esc_attr( $addon_item_id ); ?>" value="<?php echo esc_attr( $addon_item_name ) . '|' . esc_attr( $addon_item_id ). '|' . esc_attr( $addon_raw_price ) .'|'. '1' ; ?>">
                                        <?php echo esc_html( $addon_item_name ) . ' (' . rpress_sanitize_amount( $addon_price ) . ') '; ?>
                                    </option>
                                <?php endforeach;
                            endif;
                        endforeach;
                    endif;
                endforeach;
            endif; ?>
        </select>
    </div>

								<?php endif; ?>
							</div> <!-- end of addon items-->

							<!-- Addon Items Ends Here -->

							<div class="clear"></div>

							<?php
							if( isset($cart_items[$key]['instruction'] ) && !empty($cart_items[$key]['instruction']) ) : ?>
								<div class="rpress-special-instruction">
									<span class="special-instruction-label">
										<?php esc_html_e( 'Special Instruction:', 'restropress' ); ?>
									</span>
									<?php echo esc_html( $cart_items[$key]['instruction'] ) ; ?>
								</div> <!-- //end of special instruction-->
							<?php endif; ?>
						</div>
					</div>

					<?php $i++;
				endforeach; ?>
			</div>

		<?php else : $key = 0; ?>

			<div class="row">
				<p><?php printf( __( 'No %s included with this purchase', 'restropress' ), rp_get_label_plural() ); ?></p>
			</div>

        <?php endif; ?>

        <div class="postbox rpress-edit-purchase-element rp-add-update-elements <?php echo esc_attr( $column_count ); ?>">

        	<div class="rpress-add-fooditem-to-purchase-header row header">
        		<ul class="rpress-purchased-items-list-wrapper">
        			<li class="fooditem"><?php printf( __( 'Add New %s', 'restropress' ), rpress_get_label_singular() ); ?></li>
        			<li class="item_price<?php echo esc_attr( $is_qty_enabled ) ; ?>">
        				<?php esc_html_e( 'Price', 'restropress' ); ?>
        				<?php esc_html_e( ' & Quantity', 'restropress' );?>
        			</li>
        			<?php if ( rpress_use_taxes() ) : ?>
        			<li class="item_tax">
        				<?php echo sanitize_title( rpress_get_tax_name() ) ; ?>
        			</li>
        			<?php endif; ?>
        			<li class="price"><?php esc_html_e( 'Actions', 'restropress' ); ?></li>
        		</ul>
        	</div>

        	<div class="rpress-add-fooditem-to-purchase aa inside">
        		<ul>
        			<li class="fooditem">
        				<span class="rpress-payment-details-label-mobile">
        					<?php printf( _x( 'Select %s To Add', 'payment details select item to add - mobile', 'restropress' ), rpress_get_label_singular() ); ?>
        				</span>
        				<?php echo RPRESS()->html->product_dropdown( array(
							'name'   => 'rpress-order-fooditem-select',
							'id'     => 'rpress-order-fooditem-select',
							'chosen' => true
						) ); ?>
					</li>

					<li class="item_price<?php echo esc_attr( $is_qty_enabled ); ?>">
						<span class="rpress-payment-details-label-mobile">
							<?php
							_ex( 'Price', 'payment details add item price - mobile', 'restropress' );
							_ex( ' & Quantity', 'payment details add item quantity - mobile', 'restropress' ); ?>
						</span>

						<span class="rpress-fooditem-to-purchase-wrapper">
	                        <span class="rpress-fooditem-variations"></span>
	                        <span class="rpress-fooditem-price"></span>
	                    </span>
	                    <span>&nbsp;&times;&nbsp;</span>
	                    <input type="number" id="rpress-order-fooditem-quantity" name="rpress-order-fooditem-quantity" class="small-text rpress-add-fooditem-field rpress-order-input" min="1" step="1" value="1" />
	                </li>

	                <?php if ( rpress_use_taxes() ) : ?>
	                <li class="item_tax">
	                	<span class="rpress-payment-details-label-mobile">
	                		<?php _ex( 'Tax', 'payment details add item tax - mobile', 'restropress' ); ?>
	                	</span>
	                	<?php
	                	echo rpress_currency_symbol( $currency_code ) . '&nbsp;';
	                	echo RPRESS()->html->text(
							array(
								'name'  => 'rpress-order-fooditem-tax',
								'id'    => 'rpress-order-fooditem-tax',
								'class' => 'small-text rpress-order-fooditem-tax rpress-add-fooditem-field rpress-order-input'
							)
						); ?>
					</li>
					<?php endif; ?>

					<li class="rpress-add-fooditem-to-purchase-actions actions">
						<span class="rpress-payment-details-label-mobile">
							<?php esc_html_e( 'Actions', 'restropress' ); ?>
						</span>
						<a href="" id="rpress-order-add-fooditem" class="button button-secondary"><?php printf( __( 'Add New %s', 'restropress' ), rpress_get_label_singular() ); ?></a>
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

		<?php if ( rpress_show_billing_fields() ) : ?>
		<div id="rpress-billing-details" class="postbox">
			<h3 class="hndle">
				<span><?php esc_html_e( 'Billing Address', 'restropress' ); ?></span>
			</h3>
			<div class="inside rpress-clearfix">

				<div id="rpress-order-address">

					<div class="order-data-address">
						<div class="data column-container">
							<div class="column">
								<p>
									<?php
									$line1_address = !empty( $address['line1'] ) ? $address['line1'] : '';
									?>
									<strong class="order-data-address-line"><?php esc_html_e( 'Street Address Line 1:', 'restropress' ); ?></strong><br/>
									<input type="text" name="rpress-payment-address[0][line1]" value="<?php echo esc_attr($line1_address); ?>" class="large-text" />
								</p>
								<p>

									<strong class="order-data-address-line"><?php esc_html_e( 'Street Address Line 2:', 'restropress' ); ?></strong><br/>
									<input type="text" name="rpress-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="large-text" />
								</p>

							</div>
							<div class="column">
								<p>
									<?php
									$city = !empty( $address['city'] ) ? $address['city'] : '';
									?>
									<strong class="order-data-address-line"><?php echo esc_html__( 'City:', 'Address City', 'restropress' ); ?></strong><br/>
									<input type="text" name="rpress-payment-address[0][city]" value="<?php echo esc_attr( $city ); ?>" class="large-text"/>

								</p>
								<p>
									<?php $zip = !empty( $address['zip'] ) ? $address['zip'] : ''; ?>
									<strong class="order-data-address-line"><?php echo esc_html__( 'Zip / Postal Code:', 'Zip / Postal code of address', 'restropress' ); ?></strong><br/>
									<input type="text" name="rpress-payment-address[0][zip]" value="<?php echo esc_attr( $zip ); ?>" class="large-text"/>

								</p>
							</div>
							<div class="column">
								<?php

								$country = !empty( $address[ 'country' ] ) ? $address[ 'country' ] : '';

								 ?>
								<p id="rpress-order-address-country-wrap">
									<strong class="order-data-address-line"><?php echo esc_html__( 'Country:', 'Address country', 'restropress' ); ?></strong><br/>
									<?php
									echo RPRESS()->html->select( array(
										'options'          => rpress_get_country_list(),
										'name'             => 'rpress-payment-address[0][country]',
										'id'               => 'rpress-payment-address-country',
										'selected'         => $country,
										'show_option_all'  => false,
										'show_option_none' => false,
										'chosen'           => true,
										'placeholder'      => __( 'Select a country', 'restropress' ),
										'data'             => array(
											'search-type'        => 'no_ajax',
											'search-placeholder' => __( 'Type to search all Countries', 'restropress' ),
										),
									) );
									?>
								</p>
								<p id="rpress-order-address-state-wrap">
									<strong class="order-data-address-line"><?php echo esc_html__( 'State / Province:', 'State / province of address', 'restropress' ); ?></strong><br/>
									<?php
									$state = !empty( $address[ 'state' ] ) ? $address[ 'state' ] : '';
								 ?>
									<?php
									$states = rpress_get_states( $address['country'] );
									if( ! empty( $states ) ) {
										echo RPRESS()->html->select( array(
											'options'          => $states,
											'name'             => 'rpress-payment-address[0][state]',
											'id'               => 'rpress-payment-address-state',
											'selected'         => $state,
											'show_option_all'  => false,
											'show_option_none' => false,
											'chosen'           => true,
											'placeholder'      => __( 'Select a state', 'restropress' ),
											'data'             => array(
												'search-type'        => 'no_ajax',
												'search-placeholder' => __( 'Type to search all States/Provinces', 'restropress' ),
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
		<?php endif; ?>

		<?php do_action( 'rpress_view_order_details_billing_after', $payment_id ); ?>
		<?php do_action( 'rpress_view_order_details_main_after', $payment_id ); ?>


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
