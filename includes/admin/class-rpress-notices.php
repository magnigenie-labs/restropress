<?php
/**
 * Admin Notices Class
 *
 * @package     RPRESS
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Notices Class
 *
 * @since 1.0.0
 */
class RPRESS_Notices {

	/**
	 * Get things started
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'rpress_dismiss_notices', array( $this, 'dismiss_notices' ) );
	}

	/**
	 * Show relevant notices
	 *
	 * @since 1.0.0
	 */
	public function show_notices() {
		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

		// Global (non-action-based) messages
		if ( ( rpress_get_option( 'purchase_page', '' ) == '' || 'trash' == get_post_status( rpress_get_option( 'purchase_page', '' ) ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_rpress_set_checkout_dismissed' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'restropress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-settings' ) ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'set_checkout' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'restropress' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if ( isset( $_GET['page'] ) && 'rpress-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && rpress_is_test_mode() ) {
			$notices['updated']['rpress-payment-history-test-mode'] = sprintf( __( 'Note: Test Mode is enabled. While in test mode no live transactions are processed. <a href="%s">Settings</a>.', 'restropress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-settings&tab=gateways' ) );
		}

		if( stristr( $_SERVER['SERVER_SOFTWARE'], 'nginx' ) && ! get_user_meta( get_current_user_id(), '_rpress_nginx_redirect_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'The fooditem files in %s are not currently protected due to your site running on NGINX.', 'restropress' ), '<strong>' . rpress_get_upload_dir() . '</strong>' ); ?></p>
				<p><?php _e( 'To protect them, you must add a redirect rule as explained in this guide</a>.', 'restropress' ); ?></p>
				<p><?php _e( 'If you have already added the redirect rule, you may safely dismiss this notice', 'restropress' ); ?></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'nginx_redirect' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'restropress' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}

		if( ! rpress_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_rpress_htaccess_missing_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			if( ! stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) ) {
				return; // Bail if we aren't using Apache... nginx doesn't use htaccess!
			}

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'The RestroPress .htaccess file is missing from %s!', 'restropress' ), '<strong>' . rpress_get_upload_dir() . '</strong>' ); ?></p>
				<p><?php printf( __( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the %s directory, and copy the following into it:', 'restropress' ), '<strong>' . rpress_get_upload_dir() . '</strong>' ); ?></p>
				<p><pre><?php echo rpress_get_htaccess_rules(); ?></pre></p>
				<p><a href="<?php echo esc_url( add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'htaccess_missing' ) ) ); ?>"><?php _e( 'Dismiss Notice', 'restropress' ); ?></a></p>
			</div>
			<?php
			echo ob_get_clean();
		}


		if ( class_exists( 'RPRESS_Recount_Earnings' ) && current_user_can( 'manage_shop_settings' ) ) {

			ob_start();
			?>
			<div class="error">
				<p><?php printf( __( 'RestroPress 2.5 contains a <a href="%s">built in recount tool</a>. Please <a href="%s">deactivate the RestroPress - Recount Earnings plugin</a>', 'restropress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-tools&tab=general' ), admin_url( 'plugins.php' ) ); ?></p>
			</div>
			<?php
			echo ob_get_clean();

		}

		/* Commented out per 
		if( ! rpress_test_ajax_works() && ! get_user_meta( get_current_user_id(), '_rpress_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
			echo '<div class="error">';
				echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may causes issues with your store.', 'restropress' ) . '</p>';
				echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'restropress' ), '' ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'admin_ajax_inaccessible' ) ) . '">' . __( 'Dismiss Notice', 'restropress' ) . '</a></p>';
			echo '</div>';
		}
		*/

		if ( isset( $_GET['rpress-message'] ) ) {
			// Shop discounts errors
			if( current_user_can( 'manage_shop_discounts' ) ) {
				switch( $_GET['rpress-message'] ) {
					case 'discount_added' :
						$notices['updated']['rpress-discount-added'] = __( 'Discount code added.', 'restropress' );
						break;
					case 'discount_add_failed' :
						$notices['error']['rpress-discount-add-fail'] = __( 'There was a problem adding your discount code, please try again.', 'restropress' );
						break;
					case 'discount_exists' :
						$notices['error']['rpress-discount-exists'] = __( 'A discount with that code already exists, please use a different code.', 'restropress' );
						break;
					case 'discount_updated' :
						$notices['updated']['rpress-discount-updated'] = __( 'Discount code updated.', 'restropress' );
						break;
					case 'discount_update_failed' :
						$notices['error']['rpress-discount-updated-fail'] = __( 'There was a problem updating your discount code, please try again.', 'restropress' );
						break;
					case 'discount_validation_failed' :
						$notices['error']['rpress-discount-validation-fail'] = __( 'The discount code could not be added because one or more of the required fields was empty, please try again.', 'restropress' );
						break;
					case 'discount_invalid_code':
						$notices['error']['rpress-discount-invalid-code'] = __( 'The discount code entered is invalid; only alphanumeric characters are allowed, please try again.', 'restropress' );
				}
			}

			// Shop reports errors
			if( current_user_can( 'view_shop_reports' ) ) {
				switch( $_GET['rpress-message'] ) {
					case 'payment_deleted' :
						$notices['updated']['rpress-payment-deleted'] = __( 'The payment has been deleted.', 'restropress' );
						break;
					case 'email_sent' :
						$notices['updated']['rpress-payment-sent'] = __( 'The order receipt has been resent.', 'restropress' );
						break;
					case 'refreshed-reports' :
						$notices['updated']['rpress-refreshed-reports'] = __( 'The reports have been refreshed.', 'restropress' );
						break;
					case 'payment-note-deleted' :
						$notices['updated']['rpress-payment-note-deleted'] = __( 'The payment note has been deleted.', 'restropress' );
						break;
				}
			}

			// Shop settings errors
			if( current_user_can( 'manage_shop_settings' ) ) {
				switch( $_GET['rpress-message'] ) {
					case 'settings-imported' :
						$notices['updated']['rpress-settings-imported'] = __( 'The settings have been imported.', 'restropress' );
						break;
					case 'api-key-generated' :
						$notices['updated']['rpress-api-key-generated'] = __( 'API keys successfully generated.', 'restropress' );
						break;
					case 'api-key-exists' :
						$notices['error']['rpress-api-key-exists'] = __( 'The specified user already has API keys.', 'restropress' );
						break;
					case 'api-key-regenerated' :
						$notices['updated']['rpress-api-key-regenerated'] = __( 'API keys successfully regenerated.', 'restropress' );
						break;
					case 'api-key-revoked' :
						$notices['updated']['rpress-api-key-revoked'] = __( 'API keys successfully revoked.', 'restropress' );
						break;
				}
			}

			// Shop payments errors
			if( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['rpress-message'] ) {
					case 'note-added' :
						$notices['updated']['rpress-note-added'] = __( 'The payment note has been added successfully.', 'restropress' );
						break;
					case 'payment-updated' :
						$notices['updated']['rpress-payment-updated'] = __( 'The order has been successfully updated.', 'restropress' );
						break;
				}
			}

			// Customer Notices
			if ( current_user_can( 'edit_shop_payments' ) ) {
				switch( $_GET['rpress-message'] ) {
					case 'customer-deleted' :
						$notices['updated']['rpress-customer-deleted'] = __( 'Customer successfully deleted', 'restropress' );
						break;
					case 'user-verified' :
						$notices['updated']['rpress-user-verified'] = __( 'User successfully verified', 'restropress' );
						break;
					case 'email-added' :
						$notices['updated']['rpress-customer-email-added'] = __( 'Customer email added', 'restropress' );
						break;
					case 'email-removed' :
						$notices['updated']['rpress-customer-email-removed'] = __( 'Customer email removed', 'restropress');
						break;
					case 'email-remove-failed' :
						$notices['error']['rpress-customer-email-remove-failed'] = __( 'Failed to remove customer email', 'restropress');
						break;
					case 'primary-email-updated' :
						$notices['updated']['rpress-customer-primary-email-updated'] = __( 'Primary email updated for customer', 'restropress');
						break;
					case 'primary-email-failed' :
						$notices['error']['rpress-customer-primary-email-failed'] = __( 'Failed to set primary email', 'restropress');
						break;
				}
			}

		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'rpress-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach( $notices['error'] as $notice => $message ) {
				add_settings_error( 'rpress-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'rpress-notices' );
	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @since 1.0.0
	 * @return void
	 */
	function dismiss_notices() {
		if( isset( $_GET['rpress_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_rpress_' . $_GET['rpress_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'rpress_action', 'rpress_notice' ) ) );
			exit;
		}
	}
}
new RPRESS_Notices;
