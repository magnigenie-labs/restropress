<?php
/**
 * This template is used to display the profile editor with [rpress_profile_editor]
 */
global $current_user;

if ( is_user_logged_in() ):
	$user_id      = get_current_user_id();
	$first_name   = get_user_meta( $user_id, 'first_name', true );
	$last_name    = get_user_meta( $user_id, 'last_name', true );
	$display_name = $current_user->display_name;
	$address      = rpress_get_customer_address( $user_id );
	$states       = rpress_get_shop_states( $address['country'] );
	$state 		  = $address['state'];

	if ( rpress_is_cart_saved() ): ?>
		<?php $restore_url = add_query_arg( array( 'rpress_action' => 'restore_cart', 'rpress_cart_token' => rpress_get_cart_token() ), rpress_get_checkout_uri() ); ?>
		<div class="rpress_success rpress-alert rpress-alert-success"><strong><?php _e( 'Saved cart','restropress' ); ?>:</strong> <?php printf( __( 'You have a saved cart, <a href="%s">click here</a> to restore it.', 'restropress' ), esc_url( $restore_url ) ); ?></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['updated'] ) && $_GET['updated'] == true && ! rpress_get_errors() ): ?>
		<div class="rpress_success rpress-alert rpress-alert-success"><strong><?php _e( 'Success','restropress' ); ?>:</strong> <?php _e( 'Your profile has been edited successfully.', 'restropress' ); ?></div>
	<?php endif; ?>

	<?php rpress_print_errors(); ?>

	<?php do_action( 'rpress_profile_editor_before' ); ?>

	<form id="rpress_profile_editor_form" class="rpress_form" action="<?php echo rpress_get_current_page_url(); ?>" method="post">

		<?php do_action( 'rpress_profile_editor_fields_top' ); ?>

		<fieldset id="rpress_profile_personal_fieldset">

			<legend id="rpress_profile_name_label"><?php _e( 'Change your Name', 'restropress' ); ?></legend>

			<p id="rpress_profile_first_name_wrap">
				<label for="rpress_first_name"><?php _e( 'First Name', 'restropress' ); ?></label>
				<input name="rpress_first_name" id="rpress_first_name" class="text rpress-input" type="text" value="<?php echo esc_attr( $first_name ); ?>" />
			</p>

			<p id="rpress_profile_last_name_wrap">
				<label for="rpress_last_name"><?php _e( 'Last Name', 'restropress' ); ?></label>
				<input name="rpress_last_name" id="rpress_last_name" class="text rpress-input" type="text" value="<?php echo esc_attr( $last_name ); ?>" />
			</p>

			<p id="rpress_profile_display_name_wrap">
				<label for="rpress_display_name"><?php _e( 'Display Name', 'restropress' ); ?></label>
				<select name="rpress_display_name" id="rpress_display_name" class="select rpress-select">
					<?php if ( ! empty( $current_user->first_name ) ): ?>
					<option <?php selected( $display_name, $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->first_name ); ?>"><?php echo esc_html( $current_user->first_name ); ?></option>
					<?php endif; ?>
					<option <?php selected( $display_name, $current_user->user_nicename ); ?> value="<?php echo esc_attr( $current_user->user_nicename ); ?>"><?php echo esc_html( $current_user->user_nicename ); ?></option>
					<?php if ( ! empty( $current_user->last_name ) ): ?>
					<option <?php selected( $display_name, $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->last_name ); ?>"><?php echo esc_html( $current_user->last_name ); ?></option>
					<?php endif; ?>
					<?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ): ?>
					<option <?php selected( $display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?> value="<?php echo esc_attr( $current_user->first_name . ' ' . $current_user->last_name ); ?>"><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></option>
					<option <?php selected( $display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?> value="<?php echo esc_attr( $current_user->last_name . ' ' . $current_user->first_name ); ?>"><?php echo esc_html( $current_user->last_name . ' ' . $current_user->first_name ); ?></option>
					<?php endif; ?>
				</select>
				<?php do_action( 'rpress_profile_editor_name' ); ?>
			</p>

			<?php do_action( 'rpress_profile_editor_after_name' ); ?>

			<p id="rpress_profile_primary_email_wrap">
				<label for="rpress_email"><?php _e( 'Primary Email Address', 'restropress' ); ?></label>
				<?php $customer = new RPRESS_Customer( $user_id, true ); ?>
				<?php if ( $customer->id > 0 ) : ?>

					<?php if ( 1 === count( $customer->emails ) ) : ?>
						<input name="rpress_email" id="rpress_email" class="text rpress-input required" type="email" value="<?php echo esc_attr( $customer->email ); ?>" />
					<?php else: ?>
						<?php
							$emails           = array();
							$customer->emails = array_reverse( $customer->emails, true );

							foreach ( $customer->emails as $email ) {
								$emails[ $email ] = $email;
							}

							$email_select_args = array(
								'options'          => $emails,
								'name'             => 'rpress_email',
								'id'               => 'rpress_email',
								'selected'         => $customer->email,
								'show_option_none' => false,
								'show_option_all'  => false,
							);

							echo RPRESS()->html->select( $email_select_args );
						?>
					<?php endif; ?>
				<?php else: ?>
					<input name="rpress_email" id="rpress_email" class="text rpress-input required" type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" />
				<?php endif; ?>

				<?php do_action( 'rpress_profile_editor_email' ); ?>
			</p>

			<?php if ( $customer->id > 0 && count( $customer->emails ) > 1 ) : ?>
				<p id="rpress_profile_emails_wrap">
					<label for="rpress_emails"><?php _e( 'Additional Email Addresses', 'restropress' ); ?></label>
					<ul class="rpress-profile-emails">
					<?php foreach ( $customer->emails as $email ) : ?>
						<?php if ( $email === $customer->email ) { continue; } ?>
						<li class="rpress-profile-email">
							<?php echo $email; ?>
							<span class="actions">
								<?php
									$remove_url = wp_nonce_url(
										add_query_arg(
											array(
												'email'      => rawurlencode( $email ),
												'rpress_action' => 'profile-remove-email',
												'redirect'   => esc_url( rpress_get_current_page_url() ),
											)
										),
										'rpress-remove-customer-email'
									);
								?>
								<a href="<?php echo $remove_url ?>" class="delete"><?php _e( 'Remove', 'restropress' ); ?></a>
							</span>
						</li>
					<?php endforeach; ?>
					</ul>
				</p>
			<?php endif; ?>

			<?php do_action( 'rpress_profile_editor_after_email' ); ?>

		</fieldset>

		<?php do_action( 'rpress_profile_editor_after_personal_fields' ); ?>

		<fieldset id="rpress_profile_address_fieldset">

			<legend id="rpress_profile_billing_address_label"><?php _e( 'Change your Billing Address', 'restropress' ); ?></legend>

			<p id="rpress_profile_billing_address_line_1_wrap">
				<label for="rpress_address_line1"><?php _e( 'Line 1', 'restropress' ); ?></label>
				<input name="rpress_address_line1" id="rpress_address_line1" class="text rpress-input" type="text" value="<?php echo esc_attr( $address['line1'] ); ?>" />
			</p>

			<p id="rpress_profile_billing_address_line_2_wrap">
				<label for="rpress_address_line2"><?php _e( 'Line 2', 'restropress' ); ?></label>
				<input name="rpress_address_line2" id="rpress_address_line2" class="text rpress-input" type="text" value="<?php echo esc_attr( $address['line2'] ); ?>" />
			</p>

			<p id="rpress_profile_billing_address_city_wrap">
				<label for="rpress_address_city"><?php _e( 'City', 'restropress' ); ?></label>
				<input name="rpress_address_city" id="rpress_address_city" class="text rpress-input" type="text" value="<?php echo esc_attr( $address['city'] ); ?>" />
			</p>

			<p id="rpress_profile_billing_address_postal_wrap">
				<label for="rpress_address_zip"><?php _e( 'Zip / Postal Code', 'restropress' ); ?></label>
				<input name="rpress_address_zip" id="rpress_address_zip" class="text rpress-input" type="text" value="<?php echo esc_attr( $address['zip'] ); ?>" />
			</p>

			<p id="rpress_profile_billing_address_country_wrap">
				<label for="rpress_address_country"><?php _e( 'Country', 'restropress' ); ?></label>
				<select name="rpress_address_country" id="rpress_address_country" class="select rpress-select">
					<?php foreach( rpress_get_country_list() as $key => $country ) : ?>
					<option value="<?php echo $key; ?>"<?php selected( $address['country'], $key ); ?>><?php echo esc_html( $country ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p id="rpress_profile_billing_address_state_wrap">
				<label for="rpress_address_state"><?php _e( 'State / Province', 'restropress' ); ?></label>
				<?php if( ! empty( $states ) ) : ?>
					<select name="rpress_address_state" id="rpress_address_state" class="select rpress-select">
						<?php
							foreach( $states as $state_code => $state_name ) {
								echo '<option value="' . $state_code . '"' . selected( $state_code, $state, false ) . '>' . $state_name . '</option>';
							}
						?>
					</select>
				<?php else : ?>
					<input name="rpress_address_state" id="rpress_address_state" class="text rpress-input" type="text" value="<?php echo esc_attr( $state ); ?>" />
				<?php endif; ?>

				<?php do_action( 'rpress_profile_editor_address' ); ?>
			</p>

			<?php do_action( 'rpress_profile_editor_after_address' ); ?>

		</fieldset>

		<?php do_action( 'rpress_profile_editor_after_address_fields' ); ?>

		<fieldset id="rpress_profile_password_fieldset">

			<legend id="rpress_profile_password_label"><?php _e( 'Change your Password', 'restropress' ); ?></legend>

			<p id="rpress_profile_password_wrap">
				<label for="rpress_user_pass"><?php _e( 'New Password', 'restropress' ); ?></label>
				<input name="rpress_new_user_pass1" id="rpress_new_user_pass1" class="password rpress-input" type="password"/>
			</p>

			<p id="rpress_profile_confirm_password_wrap">
				<label for="rpress_user_pass"><?php _e( 'Re-enter Password', 'restropress' ); ?></label>
				<input name="rpress_new_user_pass2" id="rpress_new_user_pass2" class="password rpress-input" type="password"/>
				<?php do_action( 'rpress_profile_editor_password' ); ?>
			</p>

			<?php do_action( 'rpress_profile_editor_after_password' ); ?>

		</fieldset>

		<?php do_action( 'rpress_profile_editor_after_password_fields' ); ?>

		<fieldset id="rpress_profile_submit_fieldset">

			<p id="rpress_profile_submit_wrap">
				<input type="hidden" name="rpress_profile_editor_nonce" value="<?php echo wp_create_nonce( 'rpress-profile-editor-nonce' ); ?>"/>
				<input type="hidden" name="rpress_action" value="edit_user_profile" />
				<input type="hidden" name="rpress_redirect" value="<?php echo esc_url( rpress_get_current_page_url() ); ?>" />
				<input name="rpress_profile_editor_submit" id="rpress_profile_editor_submit" type="submit" class="rpress_submit rpress-submit" value="<?php _e( 'Save Changes', 'restropress' ); ?>"/>
			</p>

		</fieldset>

		<?php do_action( 'rpress_profile_editor_fields_bottom' ); ?>

	</form><!-- #rpress_profile_editor_form -->

	<?php do_action( 'rpress_profile_editor_after' ); ?>

	<?php
else:
	do_action( 'rpress_profile_editor_logged_out' );
endif;
