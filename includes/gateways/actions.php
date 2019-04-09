<?php
/**
 * Gateway Actions
 *
 * @package     RPRESS
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes gateway select on checkout. Only for users without ajax / javascript
 *
 * @since  1.0.0
 *
 * @param $data
 */
function rpress_process_gateway_select( $data ) {
	if( isset( $_POST['gateway_submit'] ) ) {
		wp_redirect( add_query_arg( 'payment-mode', $_POST['payment-mode'] ) ); exit;
	}
}
add_action( 'rpress_gateway_select', 'rpress_process_gateway_select' );

/**
 * Loads a payment gateway via AJAX
 *
 * @since  1.0.0
 * @return void
 */
function rpress_load_ajax_gateway() {
	if ( isset( $_POST['rpress_payment_mode'] ) ) {
		do_action( 'rpress_purchase_form' );
		exit();
	}
}
add_action( 'wp_ajax_rpress_load_gateway', 'rpress_load_ajax_gateway' );
add_action( 'wp_ajax_nopriv_rpress_load_gateway', 'rpress_load_ajax_gateway' );

/**
 * Sets an error on checkout if no gateways are enabled
 *
 * @since  1.0.0
 * @return void
 */
function rpress_no_gateway_error() {
	$gateways = rpress_get_enabled_payment_gateways();

	if ( empty( $gateways ) && rpress_get_cart_total() > 0 ) {
		remove_action( 'rpress_after_cc_fields', 'rpress_default_cc_address_fields' );
		remove_action( 'rpress_cc_form', 'rpress_get_cc_form' );
		rpress_set_error( 'no_gateways', __( 'You must enable a payment gateway to use RestroPress', 'restropress' ) );
	} else {
		rpress_unset_error( 'no_gateways' );
	}
}
add_action( 'init', 'rpress_no_gateway_error' );
