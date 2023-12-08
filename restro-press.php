<?php
/**
 * Plugin Name: RestroPress
 * Plugin URI: https://www.restropress.com
 * Description: RestroPress is an online ordering system for WordPress.
 * Version: 2.9.8
 * Author: MagniGenie
 * Author URI: https://magnigenie.com
 * Text Domain: restropress
 * Domain Path: languages
 *
 * @package RPRESS
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'RP_PLUGIN_FILE' ) ) {
	define( 'RP_PLUGIN_FILE', __FILE__ );
}

// Include the main RestroPress class.
if ( ! class_exists( 'RestroPress', false ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-rpress.php';
}

/**
 * Returns the main instance of RestroPress.
 *
 * @return RestroPress
 */
function RPRESS() {
	return RestroPress::instance();
}

//Get RestroPress Running.
RPRESS();