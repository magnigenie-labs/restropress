<?php
/**
 * Admin Notices
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
 * Admin Messages
 *
 * @since 1.0.0
 * @global $rpress_options Array of all the RPRESS Options
 * @return void
 */
function rpress_admin_messages() {
	global $rpress_options;

	if ( isset( $_GET['rpress-message'] ) && 'discount_added' == $_GET['rpress-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		 add_settings_error( 'rpress-notices', 'rpress-discount-added', __( 'Discount code added.', 'rpress' ), 'updated' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'discount_add_failed' == $_GET['rpress-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-discount-add-fail', __( 'There was a problem adding your discount code, please try again.', 'rpress' ), 'error' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'discount_exists' == $_GET['rpress-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-discount-exists', __( 'A discount with that code already exists, please use a different code.', 'rpress' ), 'error' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'discount_updated' == $_GET['rpress-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		 add_settings_error( 'rpress-notices', 'rpress-discount-updated', __( 'Discount code updated.', 'rpress' ), 'updated' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'discount_update_failed' == $_GET['rpress-message'] && current_user_can( 'manage_shop_discounts' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-discount-updated-fail', __( 'There was a problem updating your discount code, please try again.', 'rpress' ), 'error' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'payment_deleted' == $_GET['rpress-message'] && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-payment-deleted', __( 'The order has been deleted.', 'rpress' ), 'updated' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'email_sent' == $_GET['rpress-message'] && current_user_can( 'view_shop_reports' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-payment-sent', __( 'The order receipt has been resent.', 'rpress' ), 'updated' );
    }

    if ( isset( $_GET['rpress-message'] ) && 'payment-note-deleted' == $_GET['rpress-message'] && current_user_can( 'view_shop_reports' ) ) {
        add_settings_error( 'rpress-notices', 'rpress-payment-note-deleted', __( 'The order note has been deleted.', 'rpress' ), 'updated' );
    }

	if ( isset( $_GET['page'] ) && 'rpress-payment-history' == $_GET['page'] && current_user_can( 'view_shop_reports' ) && rpress_is_test_mode() ) {
		add_settings_error( 'rpress-notices', 'rpress-payment-sent', sprintf( __( 'Note: Test Mode is enabled, only test payments are shown below. <a href="%s">Settings</a>.', 'rpress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-settings' ) ), 'updated' );
	}

	if ( ( empty( $rpress_options['purchase_page'] ) || 'trash' == get_post_status( $rpress_options['purchase_page'] ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_rpress_set_checkout_dismissed' ) ) {
		echo '<div class="error">';
			echo '<p>' . sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'rpress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-settings' ) ) . '</p>';
			echo '<p><a href="' . add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'set_checkout' ) ) . '">' . __( 'Dismiss Notice', 'rpress' ) . '</a></p>';
		echo '</div>';
	}

	if ( isset( $_GET['rpress-message'] ) && 'settings-imported' == $_GET['rpress-message'] && current_user_can( 'manage_shop_settings' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-settings-imported', __( 'The settings have been imported.', 'rpress' ), 'updated' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'note-added' == $_GET['rpress-message'] && current_user_can( 'edit_shop_payments' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-note-added', __( 'The order note has been added successfully.', 'rpress' ), 'updated' );
	}

	if ( isset( $_GET['rpress-message'] ) && 'payment-updated' == $_GET['rpress-message'] && current_user_can( 'edit_shop_payments' ) ) {
		add_settings_error( 'rpress-notices', 'rpress-payment-updated', __( 'The order has been successfully updated.', 'rpress' ), 'updated' );
	}

    if( ! rpress_htaccess_exists() && ! get_user_meta( get_current_user_id(), '_rpress_htaccess_missing_dismissed', true ) && current_user_can( 'manage_shop_settings' ) ) {
        if( ! stristr( $_SERVER['SERVER_SOFTWARE'], 'apache' ) )
            return; // Bail if we aren't using Apache... nginx doesn't use htaccess!

		echo '<div class="error">';
			echo '<p>' . sprintf( __( 'The RestroPress .htaccess file is missing from <strong>%s</strong>!', 'rpress' ), rpress_get_upload_dir() ) . '</p>';
			echo '<p>' . sprintf( __( 'First, please resave the Misc settings tab a few times. If this warning continues to appear, create a file called ".htaccess" in the <strong>%s</strong> directory, and copy the following into it:', 'rpress' ), rpress_get_upload_dir() ) . '</p>';
			echo '<p><pre>' . rpress_get_htaccess_rules() . '</pre>';
			echo '<p><a href="' . add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'htaccess_missing' ) ) . '">' . __( 'Dismiss Notice', 'rpress' ) . '</a></p>';
		echo '</div>';
	}

	if( ! get_user_meta( get_current_user_id(), '_rpress_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_shop_settings' ) && false !== get_transient( '_rpress_ajax_works' ) ) {
		
		if( ! rpress_test_ajax_works() ) {

			echo '<div class="error">';
				echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may causes issues with your store.', 'rpress' ) . '</p>';
				echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'rpress' ), '' ) . '</p>';
				echo '<p><a href="' . add_query_arg( array( 'rpress_action' => 'dismiss_notices', 'rpress_notice' => 'admin_ajax_inaccessible' ) ) . '">' . __( 'Dismiss Notice', 'rpress' ) . '</a></p>';
			echo '</div>';

		}
	}

	settings_errors( 'rpress-notices' );
}
add_action( 'admin_notices', 'rpress_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since 1.0.0
 * @return void
*/
function rpress_admin_addons_notices() {
	add_settings_error( 'rpress-notices', 'rpress-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'rpress' ), 'error' );
	settings_errors( 'rpress-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.0.0
 * @return void
*/
function rpress_dismiss_notices() {

	if( ! is_user_logged_in() ) {
		return;
	}

	$notice = isset( $_GET['rpress_notice'] ) ? $_GET['rpress_notice'] : false;

	if( ! $notice )
		return; // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_rpress_' . $notice . '_dismissed', 1 );

	wp_redirect( remove_query_arg( array( 'rpress_action', 'rpress_notice' ) ) ); exit;

}
add_action( 'rpress_dismiss_notices', 'rpress_dismiss_notices' );
