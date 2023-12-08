<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @since  1.0
 *
 * @param int $user User ID or email address
 * @param int $number Number of purchases to retrieve
 * @param bool $pagination Page number to retrieve
 * @param string|array $status Either an array of statuses, a single status as a string literal or a comma separated list of statues
 *
 * @return WP_Post[]|false List of all user purchases
 */
function rpress_get_users_orders( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user ) {
		return false;
	}

	if ( is_string( $status ) ) {
		if ( strpos( $status, ',' ) ) {
			$status = explode( ',', $status );
		} else {
			$status = $status === 'complete' ? 'publish' : $status;
			$status = array( $status );
		}

	}

	if ( is_array( $status ) ) {
		$status = array_unique( $status );
	}

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) )
			$paged = get_query_var('paged');
		else if ( get_query_var( 'page' ) )
			$paged = get_query_var( 'page' );
		else
			$paged = 1;
	}

	$args = array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date'
	);

	if ( $pagination ) {

		$args['page'] = $paged;

	} else {

		$args['nopaging'] = true;

	}

	$by_user_id = is_numeric( $user ) ? true : false;
	$customer   = new RPRESS_Customer( $user, $by_user_id );

	if( ! empty( $customer->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $customer->payment_ids ) );

	}

	$purchases = rpress_get_payments( apply_filters( 'rpress_get_users_orders_args', $args ) );

	// No purchases
	if ( ! $purchases )
		return false;

	return $purchases;
}

/**
 * Get Users Purchased Products
 *
 * Returns a list of unique products purchased by a specific user
 *
 * @since 1.0.0
 *
 * @param int    $user User ID or email address
 * @param string $status
 *
 * @return WP_Post[]|false List of unique products purchased by user
 */
function rpress_get_users_ordered_products( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( empty( $user ) ) {
		return false;
	}

	$by_user_id = is_numeric( $user ) ? true : false;

	$customer = new RPRESS_Customer( $user, $by_user_id );

	if ( empty( $customer->payment_ids ) ) {
		return false;
	}

	// Get all the items purchased
	$limit_payments = apply_filters( 'rpress_users_purchased_products_payments', 9999 );
	$payment_ids    = array_reverse( explode( ',', $customer->payment_ids ) );
	$payment_args   = array(
		'output'   => 'payments',
		'post__in' => $payment_ids,
		'status'   => $status,
		'number'   => $limit_payments,
	);
	$payments_query = new RPRESS_Payments_Query( $payment_args );
	$payments       = $payments_query->get_payments();

	$purchase_data  = array();

	foreach ( $payments as $payment ) {
		$purchase_data[] = $payment->fooditems;
	}

	if ( empty( $purchase_data ) ) {
		return false;
	}

	// Grab only the post ids of the products purchased on this order
	$purchase_product_ids = array();
	foreach ( $purchase_data as $purchase_meta ) {

		$purchase_ids = @wp_list_pluck( $purchase_meta, 'id' );

		if ( ! is_array( $purchase_ids ) || empty( $purchase_ids ) ) {
			continue;
		}

		$purchase_ids           = array_values( $purchase_ids );
		$purchase_product_ids[] = $purchase_ids;

	}

	// Ensure that grabbed products actually HAVE fooditems
	$purchase_product_ids = array_filter( $purchase_product_ids );

	if ( empty( $purchase_product_ids ) ) {
		return false;
	}

	// Merge all orders into a single array of all items purchased
	$purchased_products = array();
	foreach ( $purchase_product_ids as $product ) {
		$purchased_products = array_merge( $product, $purchased_products );
	}

	// Only include each product purchased once
	$product_ids = array_unique( $purchased_products );

	// Make sure we still have some products and a first item
	if ( empty ( $product_ids ) || ! isset( $product_ids[0] ) ) {
		return false;
	}

	$args = apply_filters( 'rpress_get_users_ordered_products_args', array(
		'include'        => $product_ids,
		'post_type'      => 'fooditem',
		'posts_per_page' => -1,
	) );

	return apply_filters( 'rpress_users_purchased_products_list', get_posts( $args ) );
}

/**
 * Has Purchases
 *
 * Checks to see if a user has purchased at least one item.
 *
 * @since       1.0
 * @param       int $user_id - the ID of the user to check
 * @return      bool - true if has purchased, false other wise.
 */
function rpress_has_purchases( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( rpress_get_users_orders( $user_id, 1 ) ) {
		return true; // User has at least one purchase
	}
	return false; // User has never purchased anything
}


/**
 * Get Purchase Status for User
 *
 * Retrieves the purchase count and the total amount spent for a specific user
 *
 * @since  1.0.0
 * @param       int|string $user - the ID or email of the customer to retrieve stats for
 * @param       string $mode - "test" or "live"
 * @return      array
 */
function rpress_get_purchase_stats_by_user( $user = '' ) {

	if ( is_email( $user ) ) {

		$field = 'email';

	} elseif ( is_numeric( $user ) ) {

		$field = 'user_id';

	}

	$stats    = array();
	$customer = RPRESS()->customers->get_customer_by( $field, $user );

	if( $customer ) {

		$customer = new RPRESS_Customer( $customer->id );

		$stats['purchases']   = absint( $customer->purchase_count );
		$stats['total_spent'] = rpress_sanitize_amount( $customer->purchase_value );

	}


	return (array) apply_filters( 'rpress_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a customer
 *
 * Returns total number of purchases a customer has made
 *
 * @since 1.0.0
 * @param       mixed $user - ID or email
 * @return      int - the total number of purchases
 */
function rpress_count_purchases_of_customer( $user = null ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = ! empty( $user ) ? rpress_get_purchase_stats_by_user( $user ) : false;

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user
 *
 * @since 1.0.0
 * @param       mixed $user - ID or email
 * @return      float - the total amount the user has spent
 */
function rpress_purchase_total_of_user( $user = null ) {

	$stats = rpress_get_purchase_stats_by_user( $user );

	return $stats['total_spent'];
}

/**
 * Counts the total number of files a user (or customer if an email address is given) has fooditemed
 *
 * @since 1.0.0
 * @param       mixed $user - ID or email
 * @return      int - The total number of files the user has fooditemed
 */
function rpress_count_file_fooditems_of_user( $user ) {
	global $rpress_logs;

	if ( is_email( $user ) ) {

		// If we got an email, look up the customer ID and call the direct query for customer fooditem counts.
		return rpress_count_file_fooditems_of_customer( $user );

	} else {
		$meta_query = array(
			array(
				'key'     => '_rpress_log_user_id',
				'value'   => $user
			)
		);
	}

	return $rpress_logs->get_log_count( null, 'file_fooditem', $meta_query );
}

/**
 * Counts the total number of files a customer has fooditemed.
 *
 * @param string|int $customer_id_or_email The email address or id of the customer.
 *
 * @return int The total number of files the customer has fooditemed.
 */
function rpress_count_file_fooditems_of_customer( $customer_id_or_email = '' ) {
	global $rpress_logs;

	$customer = new RPRESS_Customer( $customer_id_or_email );
	$meta_query = array(
		array(
			'key'   => '_rpress_log_customer_id',
			'value' => $customer->id,
		)
	);

	return $rpress_logs->get_log_count( null, 'file_fooditem', $meta_query );
}

/**
 * Validate a potential username
 *
 * @since 1.0.0
 * @param       string $username The username to validate
 * @return      bool
 */
function rpress_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid = ( $sanitized == $username );
	return (bool) apply_filters( 'rpress_validate_username', $valid, $username );
}

/**
 * Attach the customer to an existing user account when completing guest purchase
 *
 * This only runs when a user account already exists and a guest purchase is made
 * with the account's email address
 *
 * After attaching the customer to the user ID, the account is set to pending
 *
 * @since 1.0.0
 * @param  bool   $success     True if payment was added successfully, false otherwise
 * @param  int    $payment_id  The ID of the RPRESS_Payment that was added
 * @param  int    $customer_id The ID of the RPRESS_Customer object
 * @param  object $customer    The RPRESS_Customer object
 * @return void
 */
function rpress_connect_guest_customer_to_existing_user( $success, $payment_id, $customer_id, $customer ) {

	if( ! empty( $customer->user_id ) ) {
		return;
	}

	$user = get_user_by( 'email', $customer->email );

	if( ! $user ) {
		return;
	}

	$customer->update( array( 'user_id' => $user->ID ) );

	// Set a flag to force the account to be verified before purchase history can be accessed
	rpress_set_user_to_pending( $user->ID  );
	rpress_send_user_verification_email( $user->ID  );

}
add_action( 'rpress_customer_post_attach_payment', 'rpress_connect_guest_customer_to_existing_user', 10, 4 );

/**
 * Attach the newly created user_id to a customer, if one exists
 *
 * @since 1.0.0
 * @param  int $user_id The User ID that was created
 * @return void
 */
function rpress_connect_existing_customer_to_new_user( $user_id ) {
	$email = get_the_author_meta( 'user_email', $user_id );

	// Update the user ID on the customer
	$customer = new RPRESS_Customer( $email );

	if( $customer->id > 0 ) {
		$customer->update( array( 'user_id' => $user_id ) );
	}
}
add_action( 'user_register', 'rpress_connect_existing_customer_to_new_user', 10, 1 );

/**
 * Looks up purchases by email that match the registering user
 *
 * This is for users that purchased as a guest and then came
 * back and created an account.
 *
 * @since  1.0.0
 * @param       int $user_id - the new user's ID
 * @return      void
 */
function rpress_add_past_purchases_to_new_user( $user_id ) {

	$email    = get_the_author_meta( 'user_email', $user_id );

	$payments = rpress_get_payments( array( 's' => $email, 'output' => 'payments' ) );

	if( $payments ) {

		// Set a flag to force the account to be verified before purchase history can be accessed
		rpress_set_user_to_pending( $user_id );

		rpress_send_user_verification_email( $user_id );

		foreach( $payments as $payment ) {
			if ( is_object( $payment ) && $payment instanceof RPRESS_Payment ) {
				if ( intval( $payment->user_id ) > 0 ) {
					continue; // This payment already associated with an account
				}

				$payment->user_id = $user_id;
				$payment->save();
			}
		}
	}

}
add_action( 'user_register', 'rpress_add_past_purchases_to_new_user', 10, 1 );


/**
 * Counts the total number of customers.
 *
 * @since 1.0.0
 * @return 		int - The total number of customers.
 */
function rpress_count_total_customers( $args = array() ) {
	return RPRESS()->customers->count( $args );
}


/**
 * Returns the saved address for a customer
 *
 * @since 1.0.0
 * @return 		array - The customer's address, if any
 */
function rpress_get_customer_address( $user_id = 0 ) {
	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$address = get_user_meta( $user_id, '_rpress_user_address', true );

	if ( ! $address || ! is_array( $address ) || empty( $address ) ) {
		$address = array();
	}

	$address = wp_parse_args( $address, array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'zip'     => '',
		'country' => '',
		'state'   => '',
	) );

	return $address;
}

/**
 * Sends the new user notification email when a user registers during checkout
 *
 * @since 1.0.4
 * @param int   $user_id
 * @param array $user_data
 *
 * @return      void
 */
function rpress_new_user_notification( $user_id = 0, $user_data = array() ) {

	if( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	$emails     = RPRESS()->emails;
	$from_name  = rpress_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = rpress_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	// Setup and send the new user email for Admins.
	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );

	$admin_subject  = apply_filters( 'rpress_user_registration_admin_email_subject', sprintf( __('[%s] New User Registration', 'restropress' ), $from_name ), $user_data );
	$admin_heading  = apply_filters( 'rpress_user_registration_admin_email_heading', __( 'New user registration', 'restropress' ), $user_data );
	$admin_message  = sprintf( __( 'Username: %s', 'restropress'), $user_data['user_login'] ) . "\r\n\r\n";
	$admin_message .= sprintf( __( 'E-mail: %s', 'restropress'), $user_data['user_email'] ) . "\r\n";

	$admin_message = apply_filters( 'rpress_user_registration_admin_email_message', $admin_message, $user_data );

	$emails->__set( 'heading', $admin_heading );

	$emails->send( get_option( 'admin_email' ), $admin_subject, $admin_message );

	// Setup and send the new user email for the end user.
	$user_subject  = apply_filters( 'rpress_user_registration_email_subject', sprintf( __( '[%s] Your username and password', 'restropress' ), $from_name ), $user_data );
	$user_heading  = apply_filters( 'rpress_user_registration_email_heading', __( 'Your account info', 'restropress' ), $user_data );
	$user_message  = apply_filters( 'rpress_user_registration_email_username', sprintf( __( 'Username: %s', 'restropress' ), $user_data['user_login'] ) . "\r\n", $user_data );

	if ( did_action( 'rpress_pre_process_purchase' ) ) {
		$password_message = __( 'Password entered at checkout', 'restropress' );
	} else {
		$password_message = __( 'Password entered at registration', 'restropress' );
	}

	$user_message .= apply_filters( 'rpress_user_registration_email_password', sprintf( __( 'Password: %s', 'restropress' ), '[' . $password_message . ']' ) . "\r\n" );

	$login_url = apply_filters( 'rpress_user_registration_email_login_url', wp_login_url() );
	if( $emails->html ) {

		$user_message .= '<a href="' . $login_url . '"> ' . esc_attr__( 'Click here to log in', 'restropress' ) . ' &raquo;</a>' . "\r\n";

	} else {

		$user_message .= sprintf( __( 'To log in, visit: %s', 'restropress' ), $login_url ) . "\r\n";

	}

	$user_message = apply_filters( 'rpress_user_registration_email_message', $user_message, $user_data );

	$emails->__set( 'heading', $user_heading );

	$emails->send( $user_data['user_email'], $user_subject, $user_message );

}
add_action( 'rpress_insert_user', 'rpress_new_user_notification', 10, 2 );

/**
 * Set a user's status to pending
 *
 * @since  1.0.0
 * @param  integer $user_id The User ID to set to pending
 * @return bool             If the update was successful
 */
function rpress_set_user_to_pending( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return false;
	}

	do_action( 'rpress_pre_set_user_to_pending', $user_id );

	$update_successful = (bool) update_user_meta( $user_id, '_rpress_pending_verification', '1' );

	do_action( 'rpress_post_set_user_to_pending', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Set the user from pending to active
 *
 * @since  1.0.0
 * @param  integer $user_id The User ID to activate
 * @return bool             If the user was marked as active or not
 */
function rpress_set_user_to_verified( $user_id = 0 ) {

	if ( empty( $user_id ) ) {
		return false;
	}

	if ( ! rpress_user_pending_verification( $user_id ) ) {
		return false;
	}

	do_action( 'rpress_pre_set_user_to_active', $user_id );

	$update_successful = delete_user_meta( $user_id, '_rpress_pending_verification', '1' );

	do_action( 'rpress_post_set_user_to_active', $user_id, $update_successful );

	return $update_successful;
}

/**
 * Determines if the user account is pending verification. Pending accounts cannot view purchase history
 *
 * @since 1.0.0
 * @return  bool
 */
function rpress_user_pending_verification( $user_id = null ) {

	if( is_null( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	// No need to run a DB lookup on an empty user id
	if ( empty( $user_id ) ) {
		return false;
	}

	$pending = get_user_meta( $user_id, '_rpress_pending_verification', true );

	return (bool) apply_filters( 'rpress_user_pending_verification', ! empty( $pending ), $user_id );

}

/**
 * Gets the activation URL for the specified user
 *
 * @since 1.0.0
 * @return  string
 */
function rpress_get_user_verification_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return false;
	}

	$base_url = add_query_arg( array(
		'rpress_action' => 'verify_user',
		'user_id'    => $user_id,
		'ttl'        => strtotime( '+24 hours' )
	), untrailingslashit( rpress_get_user_verification_page() ) );

	$token = rpress_get_user_verification_token( $base_url );
	$url   = add_query_arg( 'token', $token, $base_url );

	return apply_filters( 'rpress_get_user_verification_url', $url, $user_id );

}

/**
 * Gets the URL that triggers a new verification email to be sent
 *
 * @since 1.0.0
 * @return  string
 */
function rpress_get_user_verification_request_url( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$url = esc_url( wp_nonce_url( add_query_arg( array(
		'rpress_action' => 'send_verification_email'
	) ), 'rpress-request-verification' ) );

	return apply_filters( 'rpress_get_user_verification_request_url', $url, $user_id );

}

/**
 * Sends an email to the specified user with a URL to verify their account
 *
 * @since 1.0.0
 * @param int $user_id
 */
function rpress_send_user_verification_email( $user_id = 0 ) {

	if( empty( $user_id ) ) {
		return;
	}

	if( ! rpress_user_pending_verification( $user_id ) ) {
		return;
	}

	$user_data  = get_userdata( $user_id );

	if( ! $user_data ) {
		return;
	}

	$name       = $user_data->display_name;
	$url        = rpress_get_user_verification_url( $user_id );
	$from_name  = rpress_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
	$from_email = rpress_get_option( 'from_email', get_bloginfo( 'admin_email' ) );
	$subject    = apply_filters( 'rpress_user_verification_email_subject', __( 'Verify your account', 'restropress' ), $user_id );
	$heading    = apply_filters( 'rpress_user_verification_email_heading', __( 'Verify your account', 'restropress' ), $user_id );
	$message    = sprintf(
		__( "Hello %s,\n\nYour account with %s needs to be verified before you can access your purchase history. <a href='%s'>Click here</a> to verify your account.\n\nLink missing? Visit the following URL: %s", 'restropress' ),
		$name,
		$from_name,
		$url,
		$url
	);

	$message    = apply_filters( 'rpress_user_verification_email_message', $message, $user_id );

	$emails     = new RPRESS_Emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', $heading );

	$emails->send( $user_data->user_email, $subject, $message );

}

/**
 * Generates a token for a user verification URL.
 *
 * An 'o' query parameter on a URL can include optional variables to test
 * against when verifying a token without passing those variables around in
 * the URL. For example, fooditems can be limited to the IP that the URL was
 * generated for by adding 'o=ip' to the query string.
 *
 * Or suppose when WordPress requested a URL for automatic updates, the user
 * agent could be tested to ensure the URL is only valid for requests from
 * that user agent.
 *
 * @since  1.0.0
 *
 * @param  string $url The URL to generate a token for.
 * @return string The token for the URL.
 */
function rpress_get_user_verification_token( $url = '' ) {

	$args    = array();
	$hash    = apply_filters( 'rpress_get_user_verification_token_algorithm', 'sha256' );
	$secret  = apply_filters( 'rpress_get_user_verification_token_secret', hash( $hash, wp_salt() ) );

	/*
	 * Add additional args to the URL for generating the token.
	 * Allows for restricting access to IP and/or user agent.
	 */
	$parts   = parse_url( $url );
	$options = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		// o = option checks (ip, user agent).
		if ( ! empty( $query_args['o'] ) ) {

			// Multiple options can be checked by separating them with a colon in the query parameter.
			$options = explode( ':', rawurldecode( $query_args['o'] ) );

			if ( in_array( 'ip', $options ) ) {

				$args['ip'] = rpress_get_ip();

			}

			if ( in_array( 'ua', $options ) ) {

				$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
				$args['user_agent'] = rawurlencode( $ua );

			}

		}

	}

	/*
	 * Filter to modify arguments and allow custom options to be tested.
	 * Be sure to rawurlencode any custom options for consistent results.
	 */
	$args = apply_filters( 'rpress_get_user_verification_token_args', $args, $url, $options );

	$args['secret'] = $secret;
	$args['token']  = false; // Removes a token if present.

	$url   = add_query_arg( $args, $url );
	$parts = parse_url( $url );

	// In the event there isn't a path, set an empty one so we can MD5 the token
	if ( ! isset( $parts['path'] ) ) {

		$parts['path'] = '';

	}

	$token = md5( $parts['path'] . '?' . $parts['query'] );

	return $token;

}

/**
 * Generate a token for a URL and match it against the existing token to make
 * sure the URL hasn't been tampered with.
 *
 * @since  1.0.0
 *
 * @param  string $url URL to test.
 * @return bool
 */
function rpress_validate_user_verification_token( $url = '' ) {

	$ret        = false;
	$parts      = parse_url( $url );
	$query_args = array();

	if ( isset( $parts['query'] ) ) {

		wp_parse_str( $parts['query'], $query_args );

		if ( isset( $query_args['ttl'] ) && current_time( 'timestamp' ) > $query_args['ttl'] ) {

			do_action( 'rpress_user_verification_token_expired' );

			$link_text = sprintf(
				__( 'Sorry but your account verification link has expired. <a href="%s">Click here</a> to request a new verification URL.', 'restropress' ),
				rpress_get_user_verification_request_url()
			);

			wp_die( apply_filters( 'rpress_verification_link_expired_text', $link_text ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );

		}

		if ( isset( $query_args['token'] ) && $query_args['token'] == rpress_get_user_verification_token( $url ) ) {

			$ret = true;

		}

	}

	return apply_filters( 'rpress_validate_user_verification_token', $ret, $url, $query_args );
}

/**
 * Processes an account verification email request
 *
 * @since  1.0.0
 *
 * @return void
 */
function rpress_process_user_verification_request() {

	if( ! wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'rpress-request-verification' ) ) {
		wp_die( __( 'Nonce verification failed.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	if( ! is_user_logged_in() ) {
		wp_die( __( 'You must be logged in to verify your account.', 'restropress' ), __( 'Notice', 'restropress' ), array( 'response' => 403 ) );
	}

	if( ! rpress_user_pending_verification( get_current_user_id() ) ) {
		wp_die( __( 'Your account has already been verified.', 'restropress' ), __( 'Notice', 'restropress' ), array( 'response' => 403 ) );
	}

	rpress_send_user_verification_email( get_current_user_id() );

	$redirect = apply_filters(
		'rpress_user_account_verification_request_redirect',
		add_query_arg( 'rpress-verify-request', '1', rpress_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'rpress_send_verification_email', 'rpress_process_user_verification_request' );

/**
 * Processes an account verification
 *
 * @since 1.0
 *
 * @return void
 */
function rpress_process_user_account_verification() {

	if( empty( $_GET['token'] ) ) {
		return false;
	}

	if( empty( $_GET['user_id'] ) ) {
		return false;
	}

	if( empty( $_GET['ttl'] ) ) {
		return false;
	}

	$parts = parse_url( add_query_arg( array() ) );
	wp_parse_str( $parts['query'], $query_args );
	$url = add_query_arg( $query_args, untrailingslashit( rpress_get_user_verification_page() ) );

	if( ! rpress_validate_user_verification_token( $url ) ) {

		do_action( 'rpress_invalid_user_verification_token' );

		wp_die( __( 'Invalid verification token provided.', 'restropress' ), __( 'Error', 'restropress' ), array( 'response' => 403 ) );
	}

	rpress_set_user_to_verified( absint( $_GET['user_id'] )  );

	do_action( 'rpress_user_verification_token_validated' );

	$redirect = apply_filters(
		'rpress_user_account_verified_redirect',
		add_query_arg( 'rpress-verify-success', '1', rpress_get_user_verification_page() )
	);

	wp_safe_redirect( $redirect );
	exit;

}
add_action( 'rpress_verify_user', 'rpress_process_user_account_verification' );

/**
 * Retrieves the purchase history page, or main URL for the account verification process
 *
 * @since 1.0.0
 * @return string The base URL to use for account verification
 */
function rpress_get_user_verification_page() {
	$url              = home_url();
	$order_history = rpress_get_option( 'order_history_page', 0 );

	if ( ! empty( $order_history ) ) {
		$url = get_permalink( $order_history );
	}

	return apply_filters( 'rpress_user_verification_base_url', $url );
}

/**
 * When a user is deleted, detach that user id from the customer record
 *
 * @since  1.0.0
 * @param  int $user_id The User ID being deleted
 * @return bool         If the detachment was successful
 */
function rpress_detach_deleted_user( $user_id ) {

	$customer = new RPRESS_Customer( $user_id, true );
	$detached = false;

	if ( $customer->id > 0 ) {
		$detached = $customer->update( array( 'user_id' => 0 ) );
	}

	do_action( 'rpress_detach_deleted_user', $user_id, $customer, $detached );

	return $detached;
}
add_action( 'delete_user', 'rpress_detach_deleted_user', 10, 1 );


/**

 * Adding keys
 * @since 3.0.0
 *  */
if ( "1" === rpress_get_option( "activate_api" ) ) {
    /**
     * Adding  API keys 
     * @since 3.0.0
     * * */
    add_action( "show_user_profile", "rpress_user_api_keys_display" );
    add_action( "edit_user_profile", "rpress_user_api_keys_display" );
}
if ( !function_exists( "rpress_user_api_keys_display" ) ) {

    /**
     * @param WP_User $profile | A  user object, Which is currently editing 
     * @return void
     * @since 3.0.0
     * * */
    function rpress_user_api_keys_display( WP_User $profile ): void {
        $keys_not_genereated = rpress_user_api_key_not_generated( $profile );
        $all_keys = rpress_get_user_all_api_keys( $profile );
        $table_data = $keys_not_genereated ?? $all_keys;
        echo $table_data;
    }

}

if ( !function_exists( "rpress_user_api_key_not_generated" ) ) {

    /**
     * Getting
     * @param  WP_User $profile
     * 
     * * */
    function rpress_user_api_key_not_generated( WP_User $profile ): string|null {
        if ( get_user_meta( $profile->ID, "_rp_api_keys_generated", true ) ) {
            return null;
        }
        ob_start();
        ?>
        <table class="form-table">
            <tr>
                <th><?php _e( "Restropress API keys", "restropress" ) ?> </th>
                <td>
                    <label for="rp-generate-api-key">
                        <input type="checkbox" name="rp-generate-api-key" id="rp-generate-api-key" value="true" />
                         <?php _e( "Generate", "restropress" ) ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
        return ob_get_clean();
    }

}

if ( !function_exists( "rpress_get_user_all_api_keys" ) ) {

    function rpress_get_user_all_api_keys( WP_User $profile ): null|string {
        if ( !get_user_meta( $profile->ID, "_rp_api_keys_generated", true ) ) {
            return null;
        }
        ob_start();
        ?>
        <h1>
            <?php _e( "Restropress API Keys Table", "restropress" ) ?>
        </h1>
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th> <?php _e( "Private Key", "restropress" ) ?> </th>
                    <th> <?php _e( "Public Key", "restropress" ) ?> </th>
                    <th> <?php _e( "Token", "restropress" ) ?> </th>
                    <th><?php _e( "Action", "restropress" ) ?></th>

                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p><?php echo get_user_meta( $profile->ID, "_rp_api_user_private_key", true ) ?></p>
                    </td>
                    <td>
                        <p><?php echo get_user_meta( $profile->ID, "_rp_api_user_public_key", true ) ?></p>
                    </td>
                    <td>
                        <p><?php echo get_user_meta( $profile->ID, "_rp_api_user_token_key", true ) ?></p>
                    </td>
                    <td>
                        <label for="rp-api-revoke-keys"><?php _e( "Revoke", "restropress" ) ?></label>
                        <input type="checkbox"  name="rp-api-revoke-keys" id="rp-api-revoke-keys" value="revoke"/>

                        <label for="rp-api-refresh-token-keys"><?php _e( "Refress Token", "restropress" ) ?></label>
                        <input type="checkbox"  name="rp-api-refresh-keys" id="rp-api-refresh-token-keys" value="refresh"/>
                    </td>
                </tr>
            </tbody>


        </table>
        <?php
        return ob_get_clean();
    }

}

/**
 * Updating API keys 
 * * */
add_action( 'personal_options_update', 'restropress_update_user_api_key' );
add_action( 'edit_user_profile_update', 'restropress_update_user_api_key' );

if ( !function_exists( "restropress_update_user_api_key" ) ) {

    /**
     * Adding API keys
     * @param int $user_ID | User ID of current editing User
     * @return void
     * @since 3.0.0
     * * */
    function restropress_update_user_api_key( int $user_ID ): void {
    
        rp_generate_api_keys( $user_ID );
        $data = filter_input( INPUT_POST, "rp-api-revoke-keys", FILTER_SANITIZE_STRING ) ?? filter_input( INPUT_POST, "rp-api-refresh-keys", FILTER_SANITIZE_STRING );
        if ( !is_null( $data ) ) {
            call_user_func( "rp_user_api_{$data}", $user_ID );
        }
    }

}
if ( !function_exists( "generate_api_keys" ) ) {

    /**
     * Generating API keys
     * @param int $user_ID 
     * @since 3.0.0
     * * */
    function rp_generate_api_keys( int $user_ID ): void {

        if ( true == filter_input( INPUT_POST, 'rp-generate-api-key' ) ) {
           $public_key = hash( 'sha512', time() );
            $private_key = password_hash( $user_ID, null );
           rp_user_api_get_token( $private_key, $public_key, $user_ID );
           
        }
    }

}
if ( !function_exists( "rp_user_api_get_token" ) ) {

    /**
     * Getting Token
     * @param string $private_key
     * @param string $public_key 
     * @param int $user_ID
     * @return void
     * @since 3.0.0
     * * */
    function rp_user_api_get_token( string $private_key, string $public_key, int $user_ID ): void {
        $url = get_site_url() . "/wp-json/rp/v1/auth";
        $arg = array(
            "headers" => array(
                'Authorization' => $public_key,
                'X-User-ID' => $user_ID,
            ),
        );
        $result = wp_safe_remote_get( $url, $arg );
        
         $response_data = json_decode( $result[ "body" ] ) ;
        if ( 200 === $result[ 'response' ][ 'code' ] ) {
            $clean_private_key = sanitize_meta( "_rp_api_user_private_key", $private_key, 'user' );
            update_user_meta( $user_ID, "_rp_api_user_private_key", $clean_private_key );
            $clean_public_key = sanitize_meta( "_rp_api_user_public_key", $public_key, 'user' );
            update_user_meta( $user_ID, "_rp_api_user_public_key", $clean_public_key );
            if ( property_exists( $response_data, 'token' ) ) {
                $clean_token_key = sanitize_meta( "_rp_api_user_token_key", $response_data->token, 'user' );
                update_user_meta( $user_ID, "_rp_api_user_token_key", $clean_token_key );
            }
            update_user_meta( $user_ID, "_rp_api_keys_generated", true );
        }
    }

}
if ( !function_exists( "rp_user_api_revoke" ) ) {

    /**
     * Revoking all API keys
     * @param int $user_ID
     * @since 3.0.0
     * @return void
     * * */
    function rp_user_api_revoke( int $user_ID ): void {
        delete_user_meta( $user_ID, "_rp_api_user_private_key" );
        delete_user_meta( $user_ID, "_rp_api_user_public_key" );
        delete_user_meta( $user_ID, "_rp_api_user_token_key" );
        delete_user_meta( $user_ID, "_rp_api_keys_generated" );
    }

}
if ( !function_exists( "rp_user_api_refresh" ) ) {

    /**
     * Refreshing Token 
     * @param int $user_ID 
     * @since 3.0.0
     * @return void
     * ** */
    function rp_user_api_refresh( int $user_ID ): void {
        $private_key = get_user_meta( $user_ID, "_rp_api_user_private_key", true );
        $public_key = hash( 'sha512', time() );
        rp_user_api_get_token( $private_key, $public_key, $user_ID );
    }

}
