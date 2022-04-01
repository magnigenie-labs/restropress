<?php
/**
 * Admin Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Processes all RPRESS actions sent via POST and GET by looking for the 'rpress-action'
 * request and running do_action() to call the function
 *
 * @since 1.0.0
 * @return void
 */
function rpress_process_actions() {
	if ( isset( $_POST['rpress-action'] ) ) {
		do_action( 'rpress_' . sanitize_text_field( $_POST['rpress-action'] ), rpress_sanitize_array( $_POST ) );
	}

	if ( isset( $_GET['rpress-action'] ) ) {
		do_action( 'rpress_' . sanitize_text_field( $_GET['rpress-action'] ), rpress_sanitize_array( $_GET ) );
	}

}
add_action( 'admin_init', 'rpress_process_actions' );

/**
 * Display notices to admins
 *
 * @since 2.6
 */
function rp_addon_activation_notice() {

  $items = get_transient( 'restropress_add_ons_feed' );
  if( ! $items ) {
    $items = rpress_fetch_items();
  }

  $statuses = array();

  if( is_array( $items ) && !empty( $items ) ) {

    foreach( $items as $key => $item ) {

      $class_name = trim( $item->class_name );

      if( class_exists( $class_name ) ) {

        if( !get_option( $item->text_domain . '_license_status' ) ) {
          array_push( $statuses, 'empty' );
        } else {
          $status = get_option( $item->text_domain . '_license_status' );
          array_push( $statuses, $status );
        }
      }
    }
  }

  if( !empty( $statuses ) && ( in_array( 'empty', $statuses) || in_array( 'invalid', $statuses) ) ) {

    $class = 'notice notice-error';
    $message = __( 'You have invalid or expired license keys for one or more addons of RestroPress. Please go to the <a href="%2$s">Extensions</a> page to update your licenses.', 'restropress' );
    $url = admin_url( 'admin.php?page=rpress-extensions' );

    printf( '<div class="%1$s"><p>' . $message . '</p></div>', esc_attr( $class ), $url );
  }
}
add_action( 'admin_notices', 'rp_addon_activation_notice' );


/**
 * Check all extensions for updates
 * @since 2.7.2
 */
add_action( 'init', 'check_extensions_update', 10, 1 );
function check_extensions_update() {

  if ( !is_admin() || wp_doing_ajax() ) return;

  if ( ! function_exists( 'get_plugins' ) ) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }

  $all_plugins = get_plugins();

  $ext_data = [];

  foreach ( $all_plugins as $key => $plugin ) {

    if ( strtolower( $plugin['Author'] ) == 'magnigenie' ) {
      $ext_data[$plugin['TextDomain']] = array(
        'path'    => $key,
        'version' => $plugin['Version'],
      );
    }

  }

  if ( !empty( $ext_data ) ) {

    foreach ( $ext_data as $key => $ext ) {

      if ( $ext['path'] == plugin_basename( RP_PLUGIN_FILE ) ) continue ;

      $text_domain = str_replace( '-', '_', $key );

      new RestroPress_License( $ext['path'] , '' , $ext['version'], 'MagniGenie', $text_domain . '_license' );

    }

  }

}
