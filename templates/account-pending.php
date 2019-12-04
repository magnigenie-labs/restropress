<?php if( ! empty( $_GET['rpress-verify-request'] ) ) : ?>
  <p class="rpress-account-pending rpress_success">
	 <?php _e( 'An email with an activation link has been sent.', 'restropress' ); ?>
  </p>
<?php endif; ?>
  <p class="rpress-account-pending">
		<?php $url = esc_url( rpress_get_user_verification_request_url() ); ?>
		<?php printf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'restropress' ), $url ); ?>
	</p>