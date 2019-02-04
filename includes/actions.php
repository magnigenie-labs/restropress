<?php
/**
 * Front-end Actions
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Hooks RPRESS actions, when present in the $_GET superglobal. Every rpress_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.0
 * @return void
*/
function rpress_get_actions() {
	$key = ! empty( $_GET['rpress_action'] ) ? sanitize_key( $_GET['rpress_action'] ) : false;
	if ( ! empty( $key ) ) {
		do_action( "rpress_{$key}" , $_GET );
	}
}
add_action( 'init', 'rpress_get_actions' );

/**
 * Hooks RPRESS actions, when present in the $_POST superglobal. Every rpress_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0.0
 * @return void
*/
function rpress_post_actions() {
	$key = ! empty( $_POST['rpress_action'] ) ? sanitize_key( $_POST['rpress_action'] ) : false;
	if ( ! empty( $key ) ) {
		do_action( "rpress_{$key}", $_POST );
	}
}
add_action( 'init', 'rpress_post_actions' );
