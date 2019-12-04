<?php
/**
 * This template is used to display the purchase summary with [rpress_receipt]
 */
global $rpress_receipt_args;

$payment   = get_post( $rpress_receipt_args['id'] );

if( empty( $payment ) ) : ?>

	<div class="rpress_errors rpress-alert rpress-alert-error">
		<?php _e( 'The specified receipt ID appears to be invalid', 'restropress' ); ?>
	</div>

<?php
return;
endif;

$meta      		= rpress_get_payment_meta( $payment->ID );
$cart      		= rpress_get_payment_meta_cart_details( $payment->ID, true );
$user      		= rpress_get_payment_meta_user_info( $payment->ID );
$email     		= rpress_get_payment_user_email( $payment->ID );
$status    		= rpress_get_payment_status( $payment, true );
$deliver_type = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_type' );
$deliver_time = rpress_get_payment_meta( $payment->ID, '_rpress_delivery_time' );
$time_text		= $deliver_type == 'pickup' ? __( 'Pickup Time', 'restropress' ) : __( 'Delivery Time', 'restropress' );
?>
<table id="rpress_purchase_receipt" class="rpress-table">
	<thead>
		<?php do_action( 'rpress_payment_receipt_before', $payment, $rpress_receipt_args ); ?>

		<?php if ( filter_var( $rpress_receipt_args['payment_id'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		<tr>
			<th><strong><?php _e( 'Order#', 'restropress' ); ?>:</strong></th>
			<th><?php echo rpress_get_payment_number( $payment->ID ); ?></th>
		</tr>
		<?php endif; ?>
	</thead>

	<tbody>

		<tr>
			<td class="rpress_receipt_payment_status"><strong><?php _e( 'Order Status', 'restropress' ); ?>:</strong></td>
			<td class="rpress_receipt_payment_status <?php echo strtolower( $status ); ?>"><?php echo $status; ?></td>
		</tr>

		<tr>
			<td class="rpress_receipt_delivery_type"><strong><?php _e( 'Order Type', 'restropress' ); ?>:</strong></td>
			<td class="rpress_receipt_delivery_type <?php echo strtolower( $deliver_type ); ?>"><?php echo ucfirst( $deliver_type ); ?></td>
		</tr>

		<tr>
			<td class="rpress_receipt_delivery_time"><strong><?php echo $time_text; ?>:</strong></td>
			<td class="rpress_receipt_delivery_time"><?php echo $deliver_time; ?></td>
		</tr>

		<?php if ( filter_var( $rpress_receipt_args['payment_key'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Payment Key', 'restropress' ); ?>:</strong></td>
				<td><?php echo rpress_get_payment_meta( $payment->ID, '_rpress_payment_purchase_key', true ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $rpress_receipt_args['date'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
		<tr>
			<td><strong><?php _e( 'Order Date', 'restropress' ); ?>:</strong></td>
			<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $meta['date'] ) ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( ( $fees = rpress_get_payment_fees( $payment->ID, 'fee' ) ) ) : ?>
		<tr>
			<td><strong><?php _e( 'Fees', 'restropress' ); ?>:</strong></td>
			<td>
				<ul class="rpress_receipt_fees">
				<?php foreach( $fees as $fee ) : ?>
					<li>
						<span class="rpress_fee_label"><?php echo esc_html( $fee['label'] ); ?></span>
						<span class="rpress_fee_sep">&nbsp;&ndash;&nbsp;</span>
						<span class="rpress_fee_amount"><?php echo rpress_currency_filter( rpress_format_amount( $fee['amount'] ) ); ?></span>
					</li>
				<?php endforeach; ?>
				</ul>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( filter_var( $rpress_receipt_args['discount'], FILTER_VALIDATE_BOOLEAN ) && isset( $user['discount'] ) && $user['discount'] != 'none' ) : ?>
			<tr>
				<td><strong><?php _e( 'Discount(s)', 'restropress' ); ?>:</strong></td>
				<td><?php echo $user['discount']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if( rpress_use_taxes() ) : ?>
			<tr>
				<td><strong><?php _e( 'Tax', 'restropress' ); ?>:</strong></td>
				<td><?php echo rpress_payment_tax( $payment->ID ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $rpress_receipt_args['payment_method'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Payment Method', 'restropress' ); ?>:</strong></td>
				<td><?php echo rpress_get_gateway_checkout_label( rpress_get_payment_gateway( $payment->ID ) ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( filter_var( $rpress_receipt_args['price'], FILTER_VALIDATE_BOOLEAN ) ) : ?>
			<tr>
				<td><strong><?php _e( 'Total Price', 'restropress' ); ?>:</strong></td>
				<td><?php echo rpress_payment_amount( $payment->ID ); ?></td>
			</tr>

		<?php endif; ?>

		<?php do_action( 'rpress_payment_receipt_after', $payment, $rpress_receipt_args ); ?>
	</tbody>
</table>

<?php do_action( 'rpress_payment_receipt_after_table', $payment, $rpress_receipt_args ); ?>

<?php if ( filter_var( $rpress_receipt_args['products'], FILTER_VALIDATE_BOOLEAN ) ) : ?>

	<h3><?php echo apply_filters( 'rpress_payment_receipt_products_title', __( 'Items Ordered', 'restropress' ) ); ?></h3>

	<table id="rpress_purchase_receipt_products" class="rpress-table">
		<thead>
			<th><?php _e( 'Name', 'restropress' ); ?></th>
			<th><?php _e( 'Price', 'restropress' ); ?></th>
		</thead>

		<tbody>
		<?php if( $cart ) : ?>
			<?php
			//print_r($cart);
			foreach ( $cart as $key => $item ) : ?>
				<?php
					$row_price = array();
 				?>
				<?php if( ! apply_filters( 'rpress_user_can_view_receipt_item', true, $item ) ) : ?>
					<?php continue; // Skip this item if can't view it ?>
				<?php endif; ?>

				<?php if( empty( $item['in_bundle'] ) ) : ?>
				<tr>
					<td>
						<?php
						$price_id       = rpress_get_cart_item_price_id( $item );
						?>

						<div class="rpress_purchase_receipt_product_name">
							<?php echo $item['quantity']; ?> X <?php echo esc_html( $item['name'] ); ?> (<?php echo rpress_price( $item['id'] ); ?>)
							<?php
								if( is_array($item['item_number']['options']) &&
									!empty($item['item_number']['options']) ) {
									foreach( $item['item_number']['options'] as $k => $v ) {
										array_push($row_price, $v['price']);
										if( !empty($v['addon_item_name']) ) {
											?>
											<br/>&nbsp;&nbsp;<small class="rpress-receipt-addon-item"><?php echo $v['addon_item_name']; ?> (<?php echo rpress_currency_filter(rpress_format_amount($v['price'])); ?>)</small>
											<?php
										}
									}
								}
							?>
						</div>


					</td>
					<td>
						<?php if( empty( $item['in_bundle'] ) ) : // Only show price when product is not part of a bundle ?>
							<?php
							$addon_price = array_sum($row_price);
							$addon_price = $addon_price + $item[ 'price' ];
							?>
							<?php echo rpress_currency_filter( rpress_format_amount( $addon_price ) ); ?>
						<?php endif; ?>
					</td>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>

		</tbody>

	</table>
<?php endif; ?>
