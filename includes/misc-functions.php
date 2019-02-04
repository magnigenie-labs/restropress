<?php
/**
 * Misc Functions
 *
 * @package     RPRESS
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Is Test Mode
 *
 * @since 1.0
 * @return bool $ret True if test mode is enabled, false otherwise
 */
function rpress_is_test_mode() {
	$ret = rpress_get_option( 'test_mode', false );
	return (bool) apply_filters( 'rpress_is_test_mode', $ret );
}

/**
 * Is Debug Mode
 *
 * @since 1.0
 * @return bool $ret True if debug mode is enabled, false otherwise
 */
function rpress_is_debug_mode() {
	$ret = rpress_get_option( 'debug_mode', false );
	if( defined( 'RPRESS_DEBUG_MODE' ) && RPRESS_DEBUG_MODE ) {
		$ret = true;
	}
	return (bool) apply_filters( 'rpress_is_debug_mode', $ret );
}

/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function rpress_no_guest_checkout() {
	$ret = rpress_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'rpress_no_guest_checkout', $ret );
}

/**
 * Checks if users can only purchase fooditems when logged in
 *
 * @since 1.0
 * @return bool $ret Whether or not the logged_in_only setting is set
 */
function rpress_logged_in_only() {
	$ret = rpress_get_option( 'logged_in_only', false );
	return (bool) apply_filters( 'rpress_logged_in_only', $ret );
}

/**
 * Redirect to checkout immediately after adding items to the cart?
 *
 * @since 1.0.0
 * @return bool $ret True is redirect is enabled, false otherwise
 */
function rpress_straight_to_checkout() {
	$ret = rpress_get_option( 'redirect_on_add', false );
	return (bool) apply_filters( 'rpress_straight_to_checkout', $ret );
}

/**
 * Disable Refooditem
 *
 * @since 1.0
 * @return bool True if refooditeming of files is disabled, false otherwise
 */
function rpress_no_refooditem() {
	$ret = rpress_get_option( 'disable_refooditem', false );
	return (bool) apply_filters( 'rpress_no_refooditem', $ret );
}

/**
 * Verify credit card numbers live?
 *
 * @since  1.0.0
 * @return bool $ret True is verify credit cards is live
 */
function rpress_is_cc_verify_enabled() {
	$ret = true;

	/*
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
	 * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
	 */

	$gateways = rpress_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} else if ( count( $gateways ) == 1 ) {
		$ret = false;
	} else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	return (bool) apply_filters( 'rpress_verify_credit_cards', $ret );
}

/**
 * Is Odd
 *
 * Checks whether an integer is odd.
 *
 * @since 1.0
 * @param int     $int The integer to check
 * @return bool Is the integer odd?
 */
function rpress_is_odd( $int ) {
	return (bool) ( $int & 1 );
}

/**
 * Get File Extension
 *
 * Returns the file extension of a filename.
 *
 * @since 1.0
 *
 * @param unknown $str File name
 *
 * @return mixed File extension
 */
function rpress_get_file_extension( $str ) {
	$parts = explode( '.', $str );
	return end( $parts );
}

/**
 * Checks if the string (filename) provided is an image URL
 *
 * @since 1.0
 * @param string  $str Filename
 * @return bool Whether or not the filename is an image
 */
function rpress_string_is_image_url( $str ) {
	$ext = rpress_get_file_extension( $str );

	switch ( strtolower( $ext ) ) {
		case 'jpg';
			$return = true;
			break;
		case 'png';
			$return = true;
			break;
		case 'gif';
			$return = true;
			break;
		default:
			$return = false;
			break;
	}

	return (bool) apply_filters( 'rpress_string_is_image', $return, $str );
}

/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function rpress_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		// can include more than 1 ip, first is the public one
		$ip = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip = trim($ip[0]);
	} elseif( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	// Fix potential CSV returned from $_SERVER variables
	$ip_array = explode( ',', $ip );
	$ip_array = array_map( 'trim', $ip_array );

	return apply_filters( 'rpress_get_ip', $ip_array[0] );
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since  1.0.0
 * @return mixed string $host if detected, false otherwise
 */
function rpress_get_host() {
	$host = false;

	if( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
	} elseif( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}


/**
 * Check site host
 *
 * @since  1.0.0
 * @param $host The host to check
 * @return bool true if host matches, false if not
 */
function rpress_is_host( $host = false ) {

	$return = false;

	if( $host ) {
		$host = str_replace( ' ', '', strtolower( $host ) );

		switch( $host ) {
			case 'wpengine':
				if( defined( 'WPE_APIKEY' ) )
					$return = true;
				break;
			case 'pagely':
				if( defined( 'PAGELYBIN' ) )
					$return = true;
				break;
			case 'icdsoft':
				if( DB_HOST == 'localhost:/tmp/mysql5.sock' )
					$return = true;
				break;
			case 'networksolutions':
				if( DB_HOST == 'mysqlv5' )
					$return = true;
				break;
			case 'ipage':
				if( strpos( DB_HOST, 'ipagemysql.com' ) !== false )
					$return = true;
				break;
			case 'ipower':
				if( strpos( DB_HOST, 'ipowermysql.com' ) !== false )
					$return = true;
				break;
			case 'mediatemplegrid':
				if( strpos( DB_HOST, '.gridserver.com' ) !== false )
					$return = true;
				break;
			case 'pairnetworks':
				if( strpos( DB_HOST, '.pair.com' ) !== false )
					$return = true;
				break;
			case 'rackspacecloud':
				if( strpos( DB_HOST, '.stabletransit.com' ) !== false )
					$return = true;
				break;
			case 'sysfix.eu':
			case 'sysfix.eupowerhosting':
				if( strpos( DB_HOST, '.sysfix.eu' ) !== false )
					$return = true;
				break;
			case 'flywheel':
				if( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false )
					$return = true;
				break;
			default:
				$return = false;
		}
	}

	return $return;
}


/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function rpress_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'restro-press' ),
		'EUR'  => __( 'Euros (&euro;)', 'restro-press' ),
		'GBP'  => __( 'Pound Sterling (&pound;)', 'restro-press' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'restro-press' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'restro-press' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'restro-press' ),
		'CZK'  => __( 'Czech Koruna', 'restro-press' ),
		'DKK'  => __( 'Danish Krone', 'restro-press' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'restro-press' ),
		'HUF'  => __( 'Hungarian Forint', 'restro-press' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'restro-press' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'restro-press' ),
		'MYR'  => __( 'Malaysian Ringgits', 'restro-press' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'restro-press' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'restro-press' ),
		'NOK'  => __( 'Norwegian Krone', 'restro-press' ),
		'PHP'  => __( 'Philippine Pesos', 'restro-press' ),
		'PLN'  => __( 'Polish Zloty', 'restro-press' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'restro-press' ),
		'SEK'  => __( 'Swedish Krona', 'restro-press' ),
		'CHF'  => __( 'Swiss Franc', 'restro-press' ),
		'TWD'  => __( 'Taiwan New Dollars', 'restro-press' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'restro-press' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'restro-press' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'restro-press' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'restro-press' ),
		'RUB'  => __( 'Russian Rubles', 'restro-press' ),
		'AOA'  => __( 'Angolan Kwanza', 'restro-press' ),
	);

	return apply_filters( 'rpress_currencies', $currencies );
}

/**
 * Get the store's set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function rpress_get_currency() {
	$currency = rpress_get_option( 'currency', 'USD' );
	return apply_filters( 'rpress_currency', $currency );
}

/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since 1.0
 * @param  string $currency The currency string
 * @return string           The symbol to use for the currency
 */
function rpress_currency_symbol( $currency = '' ) {
	if ( empty( $currency ) ) {
		$currency = rpress_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "NZD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		case "AOA" :
			$symbol = 'Kz';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'rpress_currency_symbol', $symbol, $currency );
}

/**
 * Get the name of a currency
 *
 * @since  1.0.0
 * @param  string $code The currency code
 * @return string The currency's name
 */
function rpress_get_currency_name( $code = 'USD' ) {
	$currencies = rpress_get_currencies();
	$name       = isset( $currencies[ $code ] ) ? $currencies[ $code ] : $code;
	return apply_filters( 'rpress_currency_name', $name );
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param integer $n
 * @return string Short month name
 */
function rpress_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}

/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function rpress_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}

/**
 * Get the current page URL
 *
 * @since 1.0
 * @param  bool   $nocache  If we should bust cache on the returned URL
 * @return string $page_url Current page URL
 */
function rpress_get_current_page_url( $nocache = false ) {

	global $wp;

	if( get_option( 'permalink_structure' ) ) {

		$base = trailingslashit( home_url( $wp->request ) );

	} else {

		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( array( 'post_type', 'name' ), $base );

	}

	$scheme = is_ssl() ? 'https' : 'http';
	$uri    = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$uri = home_url( '/' );
	} elseif ( rpress_is_checkout() ) {
		$uri = rpress_get_checkout_uri();
	}

	$uri = apply_filters( 'rpress_get_current_page_url', $uri );

	if ( $nocache ) {
		$uri = rpress_add_cache_busting( $uri );
	}

	return $uri;
}

/**
 * Adds the 'nocache' parameter to the provided URL
 *
 * @since  1.0.0
 * @param  string $url The URL being requested
 * @return string      The URL with cache busting added or not
 */
function rpress_add_cache_busting( $url = '' ) {

	$no_cache_checkout = rpress_get_option( 'no_cache_checkout', false );

	if ( rpress_is_caching_plugin_active() || ( rpress_is_checkout() && $no_cache_checkout ) ) {
		$url = add_query_arg( 'nocache', 'true', $url );
	}

	return $url;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook rpress_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @uses do_action() Calls 'rpress_deprecated_function_run' and passes the function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'rpress_deprecated_function_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $function    The function that was called
 * @param string  $version     The version of RestroPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _rpress_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {
	do_action( 'rpress_deprecated_function_run', $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'rpress_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since RestroPress version %2$s! Use %3$s instead.', 'restro-press' ), $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since RestroPress version %2$s with no alternative available.', 'restro-press' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Marks an argument in a function deprecated and informs when it's been used
 *
 * There is a hook rpress_deprecated_argument_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that has an argument being deprecated.
 *
 * @uses do_action() Calls 'rpress_deprecated_argument_run' and passes the argument, function name, what to use instead,
 *   and the version the function was deprecated in.
 * @uses apply_filters() Calls 'rpress_deprecated_argument_trigger_error' and expects boolean value of true to do
 *   trigger or false to not trigger error.
 *
 * @param string  $argument    The arguemnt that is being deprecated
 * @param string  $function    The function that was called
 * @param string  $version     The version of WordPress that deprecated the function
 * @param string  $replacement Optional. The function that should have been called
 * @param array   $backtrace   Optional. Contains stack backtrace of deprecated function
 */
function _rpress_deprected_argument( $argument, $function, $version, $replacement = null, $backtrace = null ) {
	do_action( 'rpress_deprecated_argument_run', $argument, $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger
	if ( WP_DEBUG && apply_filters( 'rpress_deprecated_argument_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since RestroPress version %3$s! Please use %4$s instead.', 'restro-press' ), $argument, $function, $version, $replacement ) );
			trigger_error(  print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( 'The %1$s argument of %2$s is <strong>deprecated</strong> since RestroPress version %3$s with no alternative available.', 'restro-press' ), $argument, $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}


/**
 * Checks whether function is disabled.
 *
 * @since 1.0.5
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function rpress_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}

/**
 * RPRESS Let To Num
 *
 * Does Size Conversions
 *
 * @since  1.0.0
 * @usedby rpress_settings()
 * @author Chris Christoff
 *
 * @param unknown $v
 * @return int
 */
function rpress_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P': // fall-through
		case 'T': // fall-through
		case 'G': // fall-through
		case 'M': // fall-through
		case 'K': // fall-through
			$ret *= 1024;
			break;
		default:
			break;
	}

	return (int) $ret;
}

/**
 * Retrieve the URL of the symlink directory
 *
 * @since 1.0
 * @return string $url URL of the symlink directory
 */
function rpress_get_symlink_url() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress/symlinks' );
	$url = $wp_upload_dir['baseurl'] . '/rpress/symlinks';

	return apply_filters( 'rpress_get_symlink_url', $url );
}

/**
 * Retrieve the absolute path to the symlink directory
 *
 * @since 1.0
 * @return string $path Absolute path to the symlink directory
 */
function rpress_get_symlink_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress/symlinks' );
	$path = $wp_upload_dir['basedir'] . '/rpress/symlinks';

	return apply_filters( 'rpress_get_symlink_dir', $path );
}

/**
 * Retrieve the absolute path to the file upload directory without the trailing slash
 *
 * @since 1.0
 * @return string $path Absolute path to the RPRESS upload directory
 */
function rpress_get_upload_dir() {
	$wp_upload_dir = wp_upload_dir();
	wp_mkdir_p( $wp_upload_dir['basedir'] . '/rpress' );
	$path = $wp_upload_dir['basedir'] . '/rpress';

	return apply_filters( 'rpress_get_upload_dir', $path );
}

/**
 * Delete symbolic links after they have been used
 *
 * This function is only intended to be used by WordPress cron.
 *
 * @since 1.0
 * @return void
 */
function rpress_cleanup_file_symlinks() {

	// Bail if not in WordPress cron
	if ( ! rpress_doing_cron() ) {
		return;
	}

	$path = rpress_get_symlink_dir();
	$dir = opendir( $path );

	while ( ( $file = readdir( $dir ) ) !== false ) {
		if ( $file == '.' || $file == '..' )
			continue;

		$transient = get_transient( md5( $file ) );
		if ( $transient === false )
			@unlink( $path . '/' . $file );
	}
}
add_action( 'rpress_cleanup_file_symlinks', 'rpress_cleanup_file_symlinks' );

/**
 * Checks if SKUs are enabled
 *
 * @since  1.0.0
 * @author Daniel J Griffiths
 * @return bool $ret True if SKUs are enabled, false otherwise
 */
function rpress_use_skus() {
	$ret = rpress_get_option( 'enable_skus', false );
	return (bool) apply_filters( 'rpress_use_skus', $ret );
}

/**
 * Retrieve timezone
 *
 * @since  1.0.0
 * @return string $timezone The timezone ID
 */
function rpress_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) )
		return $timezone;

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) )
		return 'UTC';

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst &&  $city['offset'] == $utc_offset )
					return $city['timezone_id'];
			}
		}
	}

	// fallback
	return 'UTC';
}

/**
 * Given an object or array of objects, convert them to arrays
 *
 * @since 1.0
 * @internal Updated in 2.6
 * @param    object|array $object An object or an array of objects
 * @return   array                An array or array of arrays, converted from the provided object(s)
 */
function rpress_object_to_array( $object = array() ) {

	if ( empty( $object ) || ( ! is_object( $object ) && ! is_array( $object ) ) ) {
		return $object;
	}

	if ( is_array( $object ) ) {
		$return = array();
		foreach ( $object as $item ) {
			if ( $object instanceof RPRESS_Payment ) {
				$return[] = $object->array_convert();
			} else {
				$return[] = rpress_object_to_array( $item );
			}

		}
	} else {
		if ( $object instanceof RPRESS_Payment ) {
			$return = $object->array_convert();
		} else {
			$return = get_object_vars( $object );

			// Now look at the items that came back and convert any nested objects to arrays
			foreach ( $return as $key => $value ) {
				$value = ( is_array( $value ) || is_object( $value ) ) ? rpress_object_to_array( $value ) : $value;
				$return[ $key ] = $value;
			}
		}
	}

	return $return;

}

/**
 * Set Upload Directory
 *
 * Sets the upload dir to rpress. This function is called from
 * rpress_change_fooditems_upload_dir()
 *
 * @since 1.0
 * @return array Upload directory information
 */
function rpress_set_upload_dir( $upload ) {

	// Override the year / month being based on the post publication date, if year/month organization is enabled
	if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
		// Generate the yearly and monthly dirs
		$time = current_time( 'mysql' );
		$y = substr( $time, 0, 4 );
		$m = substr( $time, 5, 2 );
		$upload['subdir'] = "/$y/$m";
	}

	$upload['subdir'] = '/rpress' . $upload['subdir'];
	$upload['path']   = $upload['basedir'] . $upload['subdir'];
	$upload['url']    = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  1.0.0
 * @param  string $upgrade_action The upgrade action to check completion for
 * @return bool                   If the action has been added to the copmleted actions array
 */
function rpress_has_upgrade_completed( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades = rpress_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  1.0.0
 * @return array The array of completed upgrades
 */
function rpress_get_completed_upgrades() {

	$completed_upgrades = get_option( 'rpress_completed_upgrades' );

	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}

	return $completed_upgrades;

}


if ( ! function_exists( 'cal_days_in_month' ) ) {
	// Fallback in case the calendar extension is not loaded in PHP
	// Only supports Gregorian calendar
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
	}
}


if ( ! function_exists( 'hash_equals' ) ) :
/**
 * Compare two strings in constant time.
 *
 * This function was added in PHP 5.6.
 * It can leak the length of a string.
 *
 * @since 1.0
 *
 * @param string $a Expected string.
 * @param string $b Actual string.
 * @return bool Whether strings are equal.
 */
function hash_equals( $a, $b ) {
	$a_length = strlen( $a );
	if ( $a_length !== strlen( $b ) ) {
		return false;
	}
	$result = 0;

	// Do not attempt to "optimize" this.
	for ( $i = 0; $i < $a_length; $i++ ) {
		$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
	}

	return $result === 0;
}
endif;

if ( ! function_exists( 'getallheaders' ) ) :

	/**
	 * Retrieve all headers
	 *
	 * Ensure getallheaders function exists in the case we're using nginx
	 *
	 * @since 1.0
	 * @return array
	 */
	function getallheaders() {
		$headers = array();
		foreach ( $_SERVER as $name => $value ) {
			if ( substr( $name, 0, 5 ) == 'HTTP_' ) {
				$headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
			}
		}
		return $headers;
	}

endif;

/**
 * Determines the receipt visibility status
 *
 * @return bool Whether the receipt is visible or not.
 */
function rpress_can_view_receipt( $payment_key = '' ) {

	$return = false;

	if ( empty( $payment_key ) ) {
		return $return;
	}

	global $rpress_receipt_args;

	$rpress_receipt_args['id'] = rpress_get_purchase_id_by_key( $payment_key );

	$user_id = (int) rpress_get_payment_user_id( $rpress_receipt_args['id'] );

	$payment_meta = rpress_get_payment_meta( $rpress_receipt_args['id'] );

	if ( is_user_logged_in() ) {
		if ( $user_id === (int) get_current_user_id() ) {
			$return = true;
		} elseif ( wp_get_current_user()->user_email === rpress_get_payment_user_email( $rpress_receipt_args['id'] ) ) {
			$return = true;
		} elseif ( current_user_can( 'view_shop_sensitive_data' ) ) {
			$return = true;
		}
	}

	$session = rpress_get_purchase_session();
	if ( ! empty( $session ) && ! is_user_logged_in() ) {
		if ( $session['purchase_key'] === $payment_meta['key'] ) {
			$return = true;
		}
	}

	return (bool) apply_filters( 'rpress_can_view_receipt', $return, $payment_key );
}

/**
 * Given a Payment ID, generate a link to IP address provider (ipinfo.io)
 *
 * @since 1.0
 * @param  int		$payment_id The Payment ID
 * @return string	A link to the IP details provider
 */
function rpress_payment_get_ip_address_url( $payment_id ) {

	$payment = new RPRESS_Payment( $payment_id );

	$base_url = 'https://ipinfo.io/';
	$provider_url = '<a href="' . esc_url( $base_url ) . esc_attr( $payment->ip ) . '" target="_blank">' . esc_attr( $payment->ip ) . '</a>';

	return apply_filters( 'rpress_payment_get_ip_address_url', $provider_url, $payment->ip, $payment_id );

}

/**
 * Abstraction for WordPress cron checking, to avoid code duplication.
 *
 * In future versions of RPRESS, this function will be changed to only refer to
 * RPRESS specific cron related jobs. You probably won't want to use it until then.
 *
 * @since 1.0
 *
 * @return boolean
 */
function rpress_doing_cron() {

	// Bail if not doing WordPress cron (>4.8.0)
	if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
		return true;

	// Bail if not doing WordPress cron (<4.8.0)
	} elseif ( defined( 'DOING_CRON' ) && ( true === DOING_CRON ) ) {
		return true;
	}

	// Default to false
	return false;
}
