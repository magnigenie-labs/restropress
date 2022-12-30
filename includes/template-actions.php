<?php
/**
 * Manage actions and callbacks related to templates.
 *
 * @package     RPRESS
 * @subpackage  Templates
 * @copyright   Copyright (c) 2017, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

/**
 * Output a message and login form on the profile editor when the
 * current visitor is not logged in.
 *
 * @since 1.0.0
 */
function rpress_profile_editor_logged_out() {
	echo '<p class="rpress-logged-out">' . esc_html__( 'You need to log in to edit your profile.', 'restropress' ) . '</p>';
	echo rpress_login_form(); // WPCS: XSS ok.
}
add_action( 'rpress_profile_editor_logged_out', 'rpress_profile_editor_logged_out' );

/**
 * Output a message on the login form when a user is already logged in.
 *
 * This remains mainly for backwards compatibility.
 *
 * @since 1.0.0
 */
function rpress_login_form_logged_in() {
	echo '<p class="rpress-logged-in">' . esc_html__( 'You are already logged in', 'restropress' ) . '</p>';
}
add_action( 'rpress_login_form_logged_in', 'rpress_login_form_logged_in' );