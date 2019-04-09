<?php
/**
 * This template is used to display the registration form with [rpress_register]
 */
global $rpress_register_redirect;

do_action( 'rpress_print_errors' ); ?>

<?php 
if ( ! is_user_logged_in() ) : 
	$color = rpress_get_option( 'checkout_color', 'red' );
	$color = ( $color == 'inherit' ) ? '' : $color;
	$style = rpress_get_option( 'button_style', 'button' );
?>


<form id="rpress_register_form" class="rpress_form" action="" method="post">
	<?php do_action( 'rpress_register_form_fields_top' ); ?>

	<fieldset>
		<legend><?php _e( 'Register New User', 'restropress' ); ?></legend>

		<?php do_action( 'rpress_register_form_fields_before' ); ?>

		<p>
			<label for="rpress-user-login"><?php _e( 'Username', 'restropress' ); ?></label>
			<input id="rpress-user-login" class="required rpress-input" type="text" name="rpress_user_login" />
		</p>

		<p>
			<label for="rpress-user-email"><?php _e( 'Email', 'restropress' ); ?></label>
			<input id="rpress-user-email" class="required rpress-input" type="email" name="rpress_user_email" />
		</p>

		<p>
			<label for="rpress-user-pass"><?php _e( 'Password', 'restropress' ); ?></label>
			<input id="rpress-user-pass" class="password required rpress-input" type="password" name="rpress_user_pass" />
		</p>

		<p>
			<label for="rpress-user-pass2"><?php _e( 'Confirm Password', 'restropress' ); ?></label>
			<input id="rpress-user-pass2" class="password required rpress-input" type="password" name="rpress_user_pass2" />
		</p>


		<?php do_action( 'rpress_register_form_fields_before_submit' ); ?>

		<p>
			<input type="hidden" name="rpress_honeypot" value="" />
			<input type="hidden" name="rpress_action" value="user_register" />
			<input type="hidden" name="rpress_redirect" value="<?php echo esc_url( $rpress_register_redirect ); ?>"/>
			
			<input type="submit" class="rpress-submit <?php echo $color; ?> <?php echo $style; ?>" id="rpress-purchase-button" name="rpress_register_submit" value="<?php esc_attr_e( 'Register', 'restropress' ); ?>"/>
		</p>

		<?php do_action( 'rpress_register_form_fields_after' ); ?>
	</fieldset>

	<?php do_action( 'rpress_register_form_fields_bottom' ); ?>
</form>

<?php else : ?>

	<?php do_action( 'rpress_register_form_logged_in' ); ?>

<?php endif; ?>
