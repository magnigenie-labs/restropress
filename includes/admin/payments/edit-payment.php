<?php
/**
 * Edit Payment Template
 *
 * @package     RPRESS
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
$payment_id   = absint( $_GET['purchase_id'] );
$payment      = get_post( $payment_id );
$payment_data = rpress_get_payment_meta( $payment_id  );
?>
<div class="wrap">
	<h2><?php _e( 'Edit Payment', 'restropress' ); ?>: <?php echo get_the_title( $payment_id ) . ' - #' . $payment_id; ?> - <a href="<?php echo admin_url( 'edit.php?post_type=fooditem&page=rpress-payment-history' ); ?>" class="button-secondary"><?php _e( 'Go Back', 'restropress' ); ?></a></h2>
	<form id="rpress-edit-payment" action="" method="post">
		<table class="form-table">
			<tbody>
				<?php do_action( 'rpress_edit_payment_top', $payment->ID ); ?>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Buyer\'s Email', 'restropress' ); ?></span>
					</th>
					<td>
						<input class="regular-text" type="text" name="rpress-buyer-email" id="rpress-buyer-email" value="<?php echo rpress_get_payment_user_email( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the buyer\'s email here.', 'restropress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Buyer\'s User ID', 'restropress' ); ?></span>
					</th>
					<td>
						<input class="small-text" type="number" min="-1" step="1" name="rpress-buyer-user-id" id="rpress-buyer-user-id" value="<?php echo rpress_get_payment_user_id( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the buyer\'s WordPress user ID here.', 'restropress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php printf( __( 'Payment Amount in %s', 'restropress' ), rpress_get_currency() ); ?></span>
					</th>
					<td>
						<input class="small-text" type="number" min="0" step="0.01" name="rpress-payment-amount" id="rpress-payment-amount" value="<?php echo rpress_get_payment_amount( $payment_id ); ?>"/>
						<p class="description"><?php _e( 'If needed, you can update the purchase total here.', 'restropress' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'RestroPress Purchased', 'restropress' ); ?></span>
					</th>
					<td id="purchased-fooditems">
						<?php
							$fooditems = maybe_unserialize( $payment_data['fooditems'] );
							$cart_items = isset( $payment_meta['cart_details'] ) ? maybe_unserialize( $payment_meta['cart_details'] ) : false;
							if ( $fooditems ) {
								foreach ( $fooditems as $fooditem ) {
									$id = isset( $payment_data['cart_details'] ) ? $fooditem['id'] : $fooditem;

									if ( isset( $fooditem['options']['price_id'] ) ) {
										$variable_prices = '<input type="hidden" name="rpress-purchased-fooditems[' . $id . '][options][price_id]" value="'. $fooditem['options']['price_id'] .'" />';
										$variable_prices .= '(' . rpress_get_price_option_name( $id, $fooditem['options']['price_id'], $payment_id ) . ')';
									} else {
										$variable_prices = '';
									}

									echo '<div class="purchased_fooditem_' . $id . '">
											<input type="hidden" name="rpress-purchased-fooditems[' . $id . ']" value="' . $id . '"/>
											<strong>' . get_the_title( $id ) . ' ' . $variable_prices . '</strong> - <a href="#" class="rpress-remove-purchased-fooditem" data-action="remove_purchased_fooditem" data-id="' . $id . '">'. __( 'Remove', 'restropress' ) .'</a>
										  </div>';
								}
							}
						?>
						<p id="edit-fooditems"><a href="#TB_inline?width=640&amp;inlineId=available-fooditems" class="thickbox" title="<?php printf( __( 'Add %s to purchase', 'restropress' ), strtolower( rpress_get_label_plural() ) ); ?>"><?php printf( __( 'Add %s to purchase', 'restropress' ), strtolower( rpress_get_label_plural() ) ); ?></a></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Notes', 'restropress' ); ?></span>
					</th>
					<td>
						<?php
							$notes = rpress_get_payment_notes( $payment->ID );
							if ( ! empty( $notes ) ) {
								echo '<ul id="payment-notes">';
								foreach ( $notes as $note ) {
									if ( ! empty( $note->user_id ) ) {
										$user = get_userdata( $note->user_id );
										$user = $user->display_name;
									} else {
										$user = __( 'RPRESS Bot', 'restropress' );
									}
									$delete_note_url = wp_nonce_url( add_query_arg( array(
										'rpress-action' => 'delete_payment_note',
										'note_id'    => $note->comment_ID
									) ), 'rpress_delete_payment_note' );
									echo '<li>';
										echo '<strong>' . $user . '</strong>&nbsp;<em>' . $note->comment_date . '</em>&nbsp;&mdash;&nbsp;' . $note->comment_content;
										echo '&nbsp;&ndash;&nbsp;<a href="' . $delete_note_url . '" class="rpress-delete-payment-note" title="' . __( 'Delete this payment note', 'restropress' ) . '">' . __( 'Delete', 'restropress' ) . '</a>';
										echo '</li>';
								}
								echo '</ul>';
							} else {
								echo '<p>' . __( 'No payment notes', 'restropress' ) . '</p>';
							}
						?>
						<label for="rpress-payment-note"><?php _e( 'Add New Note', 'restropress' ); ?></label><br/>
						<textarea name="rpress-payment-note" id="rpress-payment-note" cols="30" rows="5"></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Payment Status', 'restropress' ); ?></span>
					</th>
					<td>
						<select name="rpress-payment-status" id="rpress_payment_status">
							<?php
							$status = $payment->post_status; // Current status
							$statuses = rpress_get_payment_statuses();
							foreach( $statuses as $status_id => $label ) {
								echo '<option value="' . $status_id	. '" ' . selected( $status, $status_id, false ) . '>' . $label . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<span><?php _e( 'Unlimited RestroPress', 'restropress' ); ?></span>
					</th>
					<td>
						<input type="checkbox" name="rpress-unlimited-fooditems" id="rpress_unlimited_fooditems" value="1"<?php checked( true, get_post_meta( $payment_id, '_unlimited_file_fooditems', true ) ); ?>/>
						<label class="description" for="rpress_unlimited_fooditems"><?php _e( 'Check this box to enable unlimited file fooditems for this purchase.', 'restropress' ); ?></label>
					</td>
				</tr>
				<tr id="rpress_payment_notification" style="display:none;">
					<th scope="row" valign="top">
						<span><?php _e( 'Send Purchase Receipt', 'restropress' ); ?></span>
					</th>
					<td>
						<input type="checkbox" name="rpress-payment-send-email" id="rpress_send_email" value="yes"/>
						<label class="description" for="rpress_send_email"><?php _e( 'Check this box to send the purchase receipt, including all fooditem links.', 'restropress' ); ?></label>
					</td>
				</tr>
				<?php do_action( 'rpress_edit_payment_bottom', $payment->ID ); ?>
			</tbody>
		</table>

		<input type="hidden" name="rpress_action" value="edit_payment"/>
		<input type="hidden" name="rpress-old-status" value="<?php echo $status; ?>"/>
		<input type="hidden" name="payment-id" value="<?php echo $payment_id; ?>"/>
		<?php wp_nonce_field( 'rpress_payment_nonce', 'rpress-payment-nonce' ); ?>
		<?php echo submit_button( __( 'Update Payment', 'restropress' ) ); ?>
	</form>
	<div id="available-fooditems" style="display:none;">
		<form id="rpress-add-fooditems-to-purchase">
			<p>
				<?php echo RPRESS()->html->product_dropdown( 'fooditems[0][id]' ); ?>
				&nbsp;<img src="<?php echo admin_url('/images/wpspin_light.gif'); ?>" class="hidden rpress_add_fooditem_to_purchase_waiting waiting" />
			</p>
			<p>
				<a href="#" class="button-secondary rpress-add-another-fooditem"><?php echo sprintf( __( 'Add Another %s', 'restropress' ), esc_html( rpress_get_label_singular() ) ); ?></a>
			</p>
			<p>
				<a id="rpress-add-fooditem" class="button-primary" title="<?php _e( 'Add Selected RestroPress', 'restropress' ); ?>"><?php _e( 'Add Selected RestroPress', 'restropress' ); ?></a>
				<a id="rpress-close-add-fooditem" class="button-secondary" onclick="tb_remove();" title="<?php _e( 'Close', 'restropress' ); ?>"><?php _e( 'Close', 'restropress' ); ?></a>
			</p>
			<?php wp_nonce_field( 'rpress_add_fooditems_to_purchase_nonce', 'rpress_add_fooditems_to_purchase_nonce' ); ?>
		</form>
	</div>
</div>