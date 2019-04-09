<?php
/**
 * This template is used to display the login form with [rpress_login]
 */
global $rpress_login_redirect;
if ( ! is_user_logged_in() ) :
	$color = rpress_get_option( 'checkout_color', 'red' );
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = rpress_get_option( 'button_style', 'button' );

	// Show any error messages after form submission
	rpress_print_errors(); ?>
	<form id="rpress_login_form" class="rpress_form" action="" method="post">
		<fieldset>
			<legend><?php _e( 'Log into Your Account', 'restropress' ); ?></legend>
			<?php do_action( 'rpress_login_fields_before' ); ?>
			<p class="rpress-login-username">
				<label for="rpress_user_login"><?php _e( 'Username or Email', 'restropress' ); ?></label>
				<input name="rpress_user_login" id="rpress_user_login" class="rpress-required rpress-input" type="text"/>
			</p>
			<p class="rpress-login-password">
				<label for="rpress_user_pass"><?php _e( 'Password', 'restropress' ); ?></label>
				<input name="rpress_user_pass" id="rpress_user_pass" class="rpress-password rpress-required rpress-input" type="password"/>
			</p>
			<p class="rpress-login-remember">
				<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'restropress' ); ?></label>
			</p>
			<p class="rpress-login-submit">
				<input type="hidden" name="rpress_redirect" value="<?php echo esc_url( $rpress_login_redirect ); ?>"/>
				<input type="hidden" name="rpress_login_nonce" value="<?php echo wp_create_nonce( 'rpress-login-nonce' ); ?>"/>
				<input type="hidden" name="rpress_action" value="user_login"/>
				

				<input type="submit" class="rpress-submit <?php echo $color; ?> <?php echo $style; ?>" id="rpress_login_submit"  value="<?php _e( 'Log In', 'restropress' ); ?>"/>
			</p>
			<p class="rpress-lost-password">
				<a href="<?php echo wp_lostpassword_url(); ?>">
					<?php _e( 'Lost Password?', 'restropress' ); ?>
				</a>
			</p>
			<?php do_action( 'rpress_login_fields_after' ); ?>
		</fieldset>
	</form>
<?php else : ?>

	<?php do_action( 'rpress_login_form_logged_in' ); ?>

<?php endif; ?>
