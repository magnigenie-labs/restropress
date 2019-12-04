<?php
/**
 * Install Function
 *
 * @package     RPRESS
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'fooditems' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the RPRESS Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $rpress_options
 * @param  bool $network_side If the plugin is being network-activated
 * @return void
 */
function rpress_install( $network_wide = false ) {
	global $wpdb;

	if ( is_multisite() && $network_wide ) {

		foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs LIMIT 100" ) as $blog_id ) {
			switch_to_blog( $blog_id );
			rpress_run_install();
			restore_current_blog();
		}
	} else {
		rpress_run_install();
	}

}
register_activation_hook( RP_PLUGIN_FILE, 'rpress_install' );

/**
 * Run the RPRESS Install process
 *
 * @since  1.0.0
 * @return void
 */
function rpress_run_install() {
	global $wpdb, $rpress_options;

	if( ! function_exists( 'rpress_create_protection_files' ) ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/upload-functions.php';
	}

	// Setup the RestroPress Custom Post Type
	rpress_setup_rpress_post_types();

	// Setup the Taxonomies
	rpress_setup_fooditem_taxonomies();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'rpress_version' );
	if ( $current_version ) {
		update_option( 'rpress_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Pull options from WP, not RPRESS's global
	$current_options = get_option( 'rpress_settings', array() );

	// Checks if the purchase page option exists
	$purchase_page = array_key_exists( 'purchase_page', $current_options ) ? get_post( $current_options['purchase_page'] ) : false;
	if ( empty( $purchase_page ) ) {
		// Checkout Page
		$checkout = wp_insert_post(
			array(
				'post_title'     => __( 'Checkout', 'restropress' ),
				'post_content'   => '[fooditem_checkout]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['purchase_page'] = $checkout;
	}

	$checkout = isset( $checkout ) ? $checkout : $current_options['purchase_page'];

	$success_page = array_key_exists( 'success_page', $current_options ) ? get_post( $current_options['success_page'] ) : false;
	if ( empty( $success_page ) ) {
		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __( 'Order Confirmation', 'restropress' ),
				'post_content'   => __( 'Thanks! Your order has been received. [rpress_receipt]', 'restropress' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_parent'    => $checkout,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['success_page'] = $success;
	}

	$failure_page = array_key_exists( 'failure_page', $current_options ) ? get_post( $current_options['failure_page'] ) : false;
	if ( empty( $failure_page ) ) {
		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'restropress' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'restropress' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		$options['failure_page'] = $failed;
	}

	$history_page = array_key_exists( 'order_history_page', $current_options ) ? get_post( $current_options['order_history_page'] ) : false;
	if ( empty( $history_page ) ) {
		// Order History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __( 'Orders', 'restropress' ),
				'post_content'   => '[order_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $checkout,
				'comment_status' => 'closed'
			)
		);

		$options['order_history_page'] = $history;
	}

	$fooditems = array_key_exists( 'fooditems_page', $current_options ) ? get_post( $current_options['fooditems_page'] ) : false;
	if ( empty( $fooditems ) ) {
		// Food Item (Food Item) Page
		$fooditem = wp_insert_post(
			array(
				'post_title'     => __( 'Food Items', 'restropress' ),
				'post_content'   => '[fooditems]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['fooditems_page'] = $fooditem;
	}

	// Populate some default values
	foreach( rpress_get_registered_settings() as $tab => $sections ) {
		foreach( $sections as $section => $settings) {

			//Check for backwards compatibility
			$tab_sections = rpress_get_settings_tab_sections( $tab );
			if( ! is_array( $tab_sections ) || ! array_key_exists( $section, $tab_sections ) ) {
				$section = 'main';
				$settings = $sections;
			}

			foreach ( $settings as $option ) {

				if( ! empty( $option['type'] ) && 'checkbox' == $option['type'] && ! empty( $option['std'] ) ) {
					$options[ $option['id'] ] = '1';
				}

			}
		}

	}

	$merged_options = array_merge( $rpress_options, $options );
	$rpress_options    = $merged_options;

	update_option( 'rpress_settings', $merged_options );
	update_option( 'rpress_version', RP_VERSION );

	// Create wp-content/uploads/rpress/ folder and the .htaccess file
	// rpress_create_protection_files( true );

	// Create RPRESS shop roles
	$roles = new RPRESS_Roles;
	$roles->add_roles();
	$roles->add_caps();

	// // Create the customer databases
	@RPRESS()->customers->create_table();
	@RPRESS()->customer_meta->create_table();

	// // Check for PHP Session support, and enable if available
	RPRESS()->session->use_php_sessions();

	// // Add a temporary option to note that RPRESS pages have been created
	set_transient( '_rpress_installed', $merged_options, 30 );
}

/**
 * When a new Blog is created in multisite, see if RPRESS is network activated, and run the installer
 *
 * @since  1.0.0
 * @param  int    $blog_id The Blog ID created
 * @param  int    $user_id The User ID set as the admin
 * @param  string $domain  The URL
 * @param  string $path    Site Path
 * @param  int    $site_id The Site ID
 * @param  array  $meta    Blog Meta
 * @return void
 */
function rpress_new_blog_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

	if ( is_plugin_active_for_network( plugin_basename( RP_PLUGIN_FILE ) ) ) {

		switch_to_blog( $blog_id );
		rpress_install();
		restore_current_blog();

	}

}
add_action( 'wpmu_new_blog', 'rpress_new_blog_created', 10, 6 );


/**
 * Drop our custom tables when a mu site is deleted
 *
 * @since  1.0.0
 * @param  array $tables  The tables to drop
 * @param  int   $blog_id The Blog ID being deleted
 * @return array          The tables to drop
 */
function rpress_wpmu_drop_tables( $tables, $blog_id ) {

	switch_to_blog( $blog_id );
	$customers_db     = new RPRESS_DB_Customers();
	$customer_meta_db = new RPRESS_DB_Customer_Meta();
	if ( $customers_db->installed() ) {
		$tables[] = $customers_db->table_name;
		$tables[] = $customer_meta_db->table_name;
	}
	restore_current_blog();

	return $tables;

}
add_filter( 'wpmu_drop_tables', 'rpress_wpmu_drop_tables', 10, 2 );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * rpress_after_install hook.
 *
 * @since  1.0.0
 * @return void
 */
function rpress_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$rpress_options     = get_transient( '_rpress_installed' );
	$rpress_table_check = get_option( '_rpress_table_check', false );

	if ( false === $rpress_table_check || current_time( 'timestamp' ) > $rpress_table_check ) {

		if ( ! @RPRESS()->customer_meta->installed() ) {

			// Create the customer meta database (this ensures it creates it on multisite instances where it is network activated)
			@RPRESS()->customer_meta->create_table();

		}

		if ( ! @RPRESS()->customers->installed() ) {
			// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
			@RPRESS()->customers->create_table();
			@RPRESS()->customer_meta->create_table();

			do_action( 'rpress_after_install', $rpress_options );
		}

		update_option( '_rpress_table_check', ( current_time( 'timestamp' ) + WEEK_IN_SECONDS ) );

	}

	if ( false !== $rpress_options ) {
		// Delete the transient
		delete_transient( '_rpress_installed' );
	}


}
add_action( 'admin_init', 'rpress_after_install' );

/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when RPRESS is network activation so we need to create them during admin_init
 *
 * @since  1.0.0
 * @return void
 */
function rpress_install_roles_on_network() {

	global $wp_roles;

	if( ! is_object( $wp_roles ) ) {
		return;
	}


	if( empty( $wp_roles->roles ) || ! array_key_exists( 'shop_manager', $wp_roles->roles ) ) {

		// Create RPRESS shop roles
		$roles = new RPRESS_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}
add_action( 'admin_init', 'rpress_install_roles_on_network' );
