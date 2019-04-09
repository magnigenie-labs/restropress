<?php if( ! empty( $_GET['rpress-verify-success'] ) ) : ?>
<p class="rpress-account-verified rpress_success">
	<?php _e( 'Your account has been successfully verified!', 'restropress' ); ?>
</p>
<?php
endif;
/**
 * This template is used to display the order history of the current user.
 */
if ( is_user_logged_in() ):
	$payments = rpress_get_users_orders( get_current_user_id(), 20, true, 'any' );
	if ( $payments ) :
		do_action( 'rpress_before_order_history', $payments ); ?>
		<table id="rpress_user_history" class="rpress-table">
			<thead>
				<tr class="rpress_purchase_row">
					<?php do_action('rpress_order_history_header_before'); ?>
					<th class="rpress_purchase_id"><?php _e('ID','restropress' ); ?></th>
					<th class="rpress_purchase_date"><?php _e('Date','restropress' ); ?></th>
					<th class="rpress_purchase_amount"><?php _e('Amount','restropress' ); ?></th>
					<th class="rpress_purchase_details"><?php _e('Details','restropress' ); ?></th>
					<?php do_action('rpress_order_history_header_after'); ?>
				</tr>
			</thead>
			<?php foreach ( $payments as $payment ) : ?>
				<?php $payment = new RPRESS_Payment( $payment->ID );
				?>
				<tr class="rpress_purchase_row">
					<?php do_action( 'rpress_order_history_row_start', $payment->ID, $payment->payment_meta ); ?>
					<td class="rpress_purchase_id">#<?php echo $payment->number ?></td>
					<td class="rpress_purchase_date"><?php echo date_i18n( get_option('date_format'), strtotime( $payment->date ) ); ?></td>
					<td class="rpress_purchase_amount">
						<span class="rpress_purchase_amount"><?php echo rpress_currency_filter( rpress_format_amount( $payment->total ) ); ?></span>
					</td>
					<td class="rpress_purchase_details">
						<?php if( $payment->status != 'publish' ) : ?>
							<span class="rpress_purchase_status <?php echo $payment->status; ?>"><?php echo $payment->status_nicename; ?></span>
							<?php if ( $payment->is_recoverable() ) : ?>
								&mdash; <a href="<?php echo $payment->get_recovery_url(); ?>"><?php _e( 'Complete Purchase', 'restropress' ); ?></a>
							<?php endif; ?>
						<?php else: ?>
							<a href="<?php echo esc_url( add_query_arg( 'payment_key', $payment->key, rpress_get_success_page_uri() ) ); ?>"><?php _e( 'View Details', 'restropress' ); ?></a>
						<?php endif; ?>
					</td>
					<?php do_action( 'rpress_order_history_row_end', $payment->ID, $payment->payment_meta ); ?>
				</tr>
			<?php endforeach; ?>
		</table>
		<div id="rpress_order_history_pagination" class="rpress_pagination navigation">
			<?php
			$big = 999999;
			echo paginate_links( array(
				'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => ceil( rpress_count_purchases_of_customer() / 20 ) // 20 items per page
			) );
			?>
		</div>
		<?php do_action( 'rpress_after_order_history', $payments ); ?>
		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<p class="rpress-no-purchases"><?php _e('You have not made any orders','restropress' ); ?></p>
	<?php endif;
endif;
