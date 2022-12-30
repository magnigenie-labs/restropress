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
	$key = ! empty( $_GET['rpress_action'] ) ? sanitize_text_field( $_GET['rpress_action'] ) : false;
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
	$key = ! empty( $_POST['rpress_action'] ) ? sanitize_text_field( $_POST['rpress_action'] ) : false;
	if ( ! empty( $key ) ) {
		do_action( "rpress_{$key}", rpress_sanitize_array( $_POST ) );
	}
}
add_action( 'init', 'rpress_post_actions' );

/**
 * This sets the tax rate to fallback tax rate
 *
 * @since 2.6
 * @return mixed
 */

function rpress_upgrade_data( $upgrader_object, $options ) {

  $rpress_plugin_path_name = plugin_basename( __FILE__ );

  if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {

    if( is_array( $options['plugins'] ) ) {

      foreach ( $options['plugins'] as $plugin ) {

        if ( $plugin == $rpress_plugin_path_name ) {

          $default_tax  = '';
          $tax_rates    = get_option( 'rpress_tax_rates', array() );

          if ( is_array( $tax_rates ) && ! empty( $tax_rates ) ) {
            $default_tax = isset( $tax_rates[0]['rate'] ) ? $tax_rates[0]['rate'] : '';
          }

          if ( ! empty( $default_tax ) ) {
            rpress_update_option( 'tax_rate', $default_tax );
          }
        }
      }
    }
  }
}
add_action( 'upgrader_process_complete', 'rpress_upgrade_data', 10, 2 );