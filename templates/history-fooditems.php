<?php if( ! empty( $_GET['rpress-verify-success'] ) ) : ?>
	<p class="rpress-account-verified rpress_success">
	<?php _e( 'Your account has been successfully verified!', 'restropress' ); ?>
	</p>
<?php
endif;
/**
 * This template is used to display the fooditem history of the current user.
 */
$purchases = rpress_get_users_orders( get_current_user_id(), 20, true, 'any' );
if ( $purchases ) :
	do_action( 'rpress_before_fooditem_history' ); ?>
	<table id="rpress_user_history" class="rpress-table">
		<thead>
			<tr class="rpress_fooditem_history_row">
				<?php do_action( 'rpress_fooditem_history_header_start' ); ?>
				<th class="rpress_fooditem_fooditem_name"><?php _e( 'Food Item Name', 'restropress' ); ?></th>
				<?php if ( ! rpress_no_refooditem() ) : ?>
					<th class="rpress_fooditem_fooditem_files"><?php _e( 'Files', 'restropress' ); ?></th>
				<?php endif; //End if no refooditem?>
				<?php do_action( 'rpress_fooditem_history_header_end' ); ?>
			</tr>
		</thead>
		<?php foreach ( $purchases as $payment ) :
			$fooditems      = rpress_get_payment_meta_cart_details( $payment->ID, true );
			$purchase_data  = rpress_get_payment_meta( $payment->ID );
			$email          = rpress_get_payment_user_email( $payment->ID );

			if ( $fooditems ) :
				foreach ( $fooditems as $fooditem ) :

					// Skip over Bundles. Products included with a bundle will be displayed individually
					if ( rpress_is_bundled_product( $fooditem['id'] ) )
						continue; ?>

					<tr class="rpress_fooditem_history_row">
						<?php
						$price_id       = rpress_get_cart_item_price_id( $fooditem );
						$fooditem_files = rpress_get_fooditem_files( $fooditem['id'], $price_id );
						$name           = $fooditem['name'];

						// Retrieve and append the price option name
						if ( ! empty( $price_id ) && 0 !== $price_id ) {
							$name .= ' - ' . rpress_get_price_option_name( $fooditem['id'], $price_id, $payment->ID );
						}

						do_action( 'rpress_fooditem_history_row_start', $payment->ID, $fooditem['id'] );
						?>
						<td class="rpress_fooditem_fooditem_name"><?php echo esc_html( $name ); ?></td>

						<?php if ( ! rpress_no_refooditem() ) : ?>
							<td class="rpress_fooditem_fooditem_files">
								<?php

								if ( 'publish' == $payment->post_status ) :

									if ( $fooditem_files ) :

										foreach ( $fooditem_files as $filekey => $file ) :

											$fooditem_url = rpress_get_fooditem_file_url( $purchase_data['key'], $email, $filekey, $fooditem['id'], $price_id );
											?>

											<div class="rpress_fooditem_file">
												<a href="<?php echo esc_url( $fooditem_url ); ?>" class="rpress_fooditem_file_link">
													<?php echo rpress_get_file_name( $file ); ?>
												</a>
											</div>

											<?php do_action( 'rpress_fooditem_history_files', $filekey, $file, $fooditem['id'], $payment->ID, $purchase_data );
										endforeach;

									else :
										_e( 'No fooditemable files found.', 'restropress' );
									endif; // End if payment complete

								else : ?>
									<span class="rpress_fooditem_payment_status">
										<?php printf( __( 'Payment status is %s', 'restropress' ), rpress_get_payment_status( $payment, true) ); ?>
									</span>
									<?php
								endif; // End if $fooditem_files
								?>
							</td>
						<?php endif; // End if ! rpress_no_refooditem()

						do_action( 'rpress_fooditem_history_row_end', $payment->ID, $fooditem['id'] );
						?>
					</tr>
					<?php
				endforeach; // End foreach $fooditems
			endif; // End if $fooditems
		endforeach;
		?>
	</table>
	<div id="rpress_fooditem_history_pagination" class="rpress_pagination navigation">
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
	<?php do_action( 'rpress_after_fooditem_history' ); ?>
<?php else : ?>
	<p class="rpress-no-fooditems"><?php _e( 'You have not purchased any fooditems', 'restropress' ); ?></p>
<?php endif; ?>
