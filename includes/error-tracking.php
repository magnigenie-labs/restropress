<?php
/**
 * Error Tracking
 *
 * @package     RPRESS
 * @subpackage  Functions/Errors
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Print Errors
 *
 * Prints all stored errors. For use during checkout.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses rpress_get_errors()
 * @uses rpress_clear_errors()
 * @return void
 */
function rpress_print_errors() {
	$errors = rpress_get_errors();
	if ( $errors ) {

		$classes = apply_filters( 'rpress_error_class', array(
			'rpress_errors', 'rpress-alert', 'rpress-alert-error'
		) );

		if ( ! empty( $errors ) ) {
			echo '<div class="' . implode( ' ', $classes ) . '">';
				// Loop error codes and display errors
				foreach ( $errors as $error_id => $error ) {

					echo '<p class="rpress_error" id="rpress_error_' . $error_id . '"><strong>' . __( 'Error', 'restropress' ) . '</strong>: ' . $error . '</p>';

				}

			echo '</div>';
		}

		rpress_clear_errors();

	}
}
add_action( 'rpress_purchase_form_before_submit', 'rpress_print_errors' );
add_action( 'rpress_ajax_checkout_errors', 'rpress_print_errors' );
add_action( 'rpress_print_errors', 'rpress_print_errors' );

/**
 * Get Errors
 *
 * Retrieves all error messages stored during the checkout process.
 * If errors exist, they are returned.
 *
 * @since 1.0
 * @uses RPRESS_Session::get()
 * @return mixed array if errors are present, false if none found
 */
function rpress_get_errors() {
	$errors = RPRESS()->session->get( 'rpress_errors' );
	$errors = apply_filters( 'rpress_errors', $errors );
	return $errors;
}

/**
 * Set Error
 *
 * Stores an error in a session var.
 *
 * @since 1.0
 * @uses RPRESS_Session::get()
 * @param int $error_id ID of the error being set
 * @param string $error_message Message to store with the error
 * @return void
 */
function rpress_set_error( $error_id, $error_message ) {
	$errors = rpress_get_errors();
	if ( ! $errors ) {
		$errors = array();
	}
	$errors[ $error_id ] = $error_message;
	RPRESS()->session->set( 'rpress_errors', $errors );
}

/**
 * Clears all stored errors.
 *
 * @since 1.0
 * @uses RPRESS_Session::set()
 * @return void
 */
function rpress_clear_errors() {
	RPRESS()->session->set( 'rpress_errors', null );
}

/**
 * Removes (unsets) a stored error
 *
 * @since  1.0.0
 * @uses RPRESS_Session::set()
 * @param int $error_id ID of the error being set
 * @return string
 */
function rpress_unset_error( $error_id ) {
	$errors = rpress_get_errors();
	if ( $errors ) {
		unset( $errors[ $error_id ] );
		RPRESS()->session->set( 'rpress_errors', $errors );
	}
}

/**
 * Register die handler for rpress_die()
 *
 * @author RestroPress
 * @since  1.0.0
 * @return void
 */
function _rpress_die_handler() {
	if ( defined( 'RPRESS_UNIT_TESTS' ) )
		return '_rpress_die_handler';
	else
		die();
}

/**
 * Wrapper function for wp_die(). This function adds filters for wp_die() which
 * kills execution of the script using wp_die(). This allows us to then to work
 * with functions using rpress_die() in the unit tests.
 *
 * @author RestroPress
 * @since  1.0.0
 * @return void
 */
function rpress_die( $message = '', $title = '', $status = 400 ) {
	add_filter( 'wp_die_ajax_handler', '_rpress_die_handler', 10, 3 );
	add_filter( 'wp_die_handler', '_rpress_die_handler', 10, 3 );
	wp_die( $message, $title, array( 'response' => $status ));
}
