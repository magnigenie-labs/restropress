<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register a view for the single customer view
 *
 * @since  1.0.0
 * @param  array $views An array of existing views
 * @return array        The altered list of views
 */
function rpress_register_default_customer_views( $views ) {

	$default_views = array(
		'overview'  => 'rpress_customers_view',
		'delete'    => 'rpress_customers_delete_view',
		'notes'     => 'rpress_customer_notes_view',
		'tools'      => 'rpress_customer_tools_view',
	);

	return array_merge( $views, $default_views );

}
add_filter( 'rpress_customer_views', 'rpress_register_default_customer_views', 1, 1 );

/**
 * Register a tab for the single customer view
 *
 * @since  1.0.0
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs
 */
function rpress_register_default_customer_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array( 'dashicon' => 'dashicons-admin-users', 'title' => _x( 'Profile', 'Customer Details tab title', 'restropress' ) ),
		'notes'    => array( 'dashicon' => 'dashicons-admin-comments', 'title' => _x( 'Notes', 'Customer Notes tab title', 'restropress' ) ),
		'tools'    => array( 'dashicon' => 'dashicons-admin-tools', 'title' => _x( 'Tools', 'Customer Tools tab title', 'restropress' ) ),
	);

	return array_merge( $tabs, $default_tabs );
}
add_filter( 'rpress_customer_tabs', 'rpress_register_default_customer_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since 1.0
 * @param  array $tabs An array of existing tabs
 * @return array       The altered list of tabs, with 'delete' at the bottom
 */
function rpress_register_delete_customer_tab( $tabs ) {

	$tabs['delete'] = array( 'dashicon' => 'dashicons-trash', 'title' => _x( 'Delete', 'Delete Customer tab title', 'restropress' ) );

	return $tabs;
}
add_filter( 'rpress_customer_tabs', 'rpress_register_delete_customer_tab', PHP_INT_MAX, 1 );

/**
 * Remove the admin bar edit profile link when the user is not verified
 *
 * @since  1.0.0
 * @return void
 */
function rpress_maybe_remove_adminbar_profile_link() {

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( rpress_user_pending_verification() ) {

		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('edit-profile', 'user-actions');

	}

}
add_action( 'wp_before_admin_bar_render', 'rpress_maybe_remove_adminbar_profile_link' );

/**
 * Remove the admin menus and disable profile access for non-verified users
 *
 * @since  1.0.0
 * @return void
 */
function rpress_maybe_remove_menu_profile_links() {

	if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}

	if ( current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( rpress_user_pending_verification() ) {

		if( defined( 'IS_PROFILE_PAGE' ) && true === IS_PROFILE_PAGE ) {
			$url     = esc_url( rpress_get_user_verification_request_url() );
			$message = sprintf( __( 'Your account is pending verification. Please click the link in your email to activate your account. No email? <a href="%s">Click here</a> to send a new activation code.', 'restropress' ), $url );
			$title   = __( 'Account Pending Verification', 'restropress' );
			$args    = array(
				'response' => 403,
			);
			wp_die( $message, $title, $args );
		}

		remove_menu_page( 'profile.php' );
		remove_submenu_page( 'users.php', 'profile.php' );

	}

}
add_action( 'admin_init', 'rpress_maybe_remove_menu_profile_links' );
