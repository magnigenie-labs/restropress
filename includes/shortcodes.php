<?php
/**
 * Shortcodes
 *
 * @package     RPRESS
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since  1.0.0
 * @author RestroPress
 * @param array $data Data sent from the profile editor
 * @return void
 */
function rpress_process_profile_editor_updates( $data ) {
  // Profile field change request
  if ( empty( $_POST['rpress_profile_editor_submit'] ) && !is_user_logged_in() ) {
    return false;
  }

  // Pending users can't edit their profile
  if ( rpress_user_pending_verification() ) {
    return false;
  }

  // Nonce security
  if ( ! wp_verify_nonce( $data['rpress_profile_editor_nonce'], 'rpress-profile-editor-nonce' ) ) {
    return false;
  }

  $user_id       = get_current_user_id();
  $old_user_data = get_userdata( $user_id );

  $display_name = isset( $data['rpress_display_name'] )    ? sanitize_text_field( $data['rpress_display_name'] )    : $old_user_data->display_name;
  $first_name   = isset( $data['rpress_first_name'] )      ? sanitize_text_field( $data['rpress_first_name'] )      : $old_user_data->first_name;
  $last_name    = isset( $data['rpress_last_name'] )       ? sanitize_text_field( $data['rpress_last_name'] )       : $old_user_data->last_name;
  $email        = isset( $data['rpress_email'] )           ? sanitize_email( $data['rpress_email'] )                : $old_user_data->user_email;
  $line1        = isset( $data['rpress_address_line1'] )   ? sanitize_text_field( $data['rpress_address_line1'] )   : '';
  $line2        = isset( $data['rpress_address_line2'] )   ? sanitize_text_field( $data['rpress_address_line2'] )   : '';
  $city         = isset( $data['rpress_address_city'] )    ? sanitize_text_field( $data['rpress_address_city'] )    : '';
  $state        = isset( $data['rpress_address_state'] )   ? sanitize_text_field( $data['rpress_address_state'] )   : '';
  $zip          = isset( $data['rpress_address_zip'] )     ? sanitize_text_field( $data['rpress_address_zip'] )     : '';
  $country      = isset( $data['rpress_address_country'] ) ? sanitize_text_field( $data['rpress_address_country'] ) : '';

  $userdata = array(
    'ID'           => $user_id,
    'first_name'   => $first_name,
    'last_name'    => $last_name,
    'display_name' => $display_name,
    'user_email'   => $email
  );


  $address = array(
    'line1'    => $line1,
    'line2'    => $line2,
    'city'     => $city,
    'state'    => $state,
    'zip'      => $zip,
    'country'  => $country
  );

  do_action( 'rpress_pre_update_user_profile', $user_id, $userdata );

  // New password
  if ( ! empty( $data['rpress_new_user_pass1'] ) ) {
    if ( $data['rpress_new_user_pass1'] !== $data['rpress_new_user_pass2'] ) {
      rpress_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'restropress' ) );
    } else {
      $userdata['user_pass'] = $data['rpress_new_user_pass1'];
    }
  }

  // Make sure the new email doesn't belong to another user
  if( $email != $old_user_data->user_email ) {
    // Make sure the new email is valid
    if( ! is_email( $email ) ) {
      rpress_set_error( 'email_invalid', __( 'The email you entered is invalid. Please enter a valid email.', 'restropress' ) );
    }

    // Make sure the new email doesn't belong to another user
    if( email_exists( $email ) ) {
      rpress_set_error( 'email_exists', __( 'The email you entered belongs to another user. Please use another.', 'restropress' ) );
    }
  }

  // Check for errors
  $errors = rpress_get_errors();

  if( $errors ) {
    // Send back to the profile editor if there are errors
    wp_redirect( $data['rpress_redirect'] );
    rpress_die();
  }

  // Update the user
  $meta    = update_user_meta( $user_id, '_rpress_user_address', $address );
  $updated = wp_update_user( $userdata );

  // Possibly update the customer
  $customer    = new RPRESS_Customer( $user_id, true );
  if ( $customer->email === $email || ( is_array( $customer->emails ) && in_array( $email, $customer->emails ) ) ) {
    $customer->set_primary_email( $email );
  };

  if ( $customer->id > 0 ) {
    $update_args = array(
      'name'  => $first_name . ' ' . $last_name,
    );

    $customer->update( $update_args );
  }

  if ( $updated ) {
    do_action( 'rpress_user_profile_updated', $user_id, $userdata );
    wp_redirect( add_query_arg( 'updated', 'true', $data['rpress_redirect'] ) );
    rpress_die();
  }
}
add_action( 'rpress_edit_user_profile', 'rpress_process_profile_editor_updates' );

/**
 * Process the 'remove' URL on the profile editor when customers wish to remove an email address
 *
 * @since  1.0.0
 * @return void
 */
function rpress_process_profile_editor_remove_email() {
  if ( ! is_user_logged_in() ) {
    return false;
  }

  // Pending users can't edit their profile
  if ( rpress_user_pending_verification() ) {
    return false;
  }

  // Nonce security
  if ( ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'rpress-remove-customer-email' ) ) {
    return false;
  }

  if ( empty( $_GET['email'] ) || ! is_email( $_GET['email'] ) ) {
    return false;
  }

  $customer = new RPRESS_Customer( get_current_user_id(), true );
  if ( $customer->remove_email( sanitize_email( $_GET['email'] ) ) ) {

    $url = add_query_arg( 'updated', true, esc_url( $_GET['redirect'] )  );

    $user          = wp_get_current_user();
    $user_login    = ! empty( $user->user_login ) ? $user->user_login : 'RPRESSBot';
    $customer_note = sprintf( __( 'Email address %s removed by %s', 'restropress' ), sanitize_email( $_GET['email'] ), $user_login );
    $customer->add_note( $customer_note );

  } else {
    rpress_set_error( 'profile-remove-email-failure', __( 'Error removing email address from profile. Please try again later.', 'restropress' ) );
    $url = esc_url( $_GET['redirect'] );
  }

  wp_safe_redirect( $url );
  exit;
}
add_action( 'rpress_profile-remove-email', 'rpress_process_profile_editor_remove_email' );
