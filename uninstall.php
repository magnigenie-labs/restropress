<?php
/**
 * Uninstall RestroPress
 *
 * Deletes all the plugin data i.e.
 * 		1. Custom Post types.
 * 		2. Terms & Taxonomies.
 * 		3. Plugin pages.
 * 		4. Plugin options.
 * 		5. Capabilities.
 * 		6. Roles.
 * 		7. Database tables.
 * 		8. Cron events.
 *
 * @package     RPRESS
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load RPRESS file.
include_once( 'restro-press.php' );

global $wpdb, $wp_roles;

if( rpress_get_option( 'uninstall_on_delete' ) ) {

	/** Delete All the Custom Post Types */
	$rpress_taxonomies = array( 'addon_category', 'fooditem_tag', 'rpress_log_type', );
	$rpress_post_types = array( 'fooditem', 'rpress_payment', 'rpress_discount', 'rpress_log' );
	foreach ( $rpress_post_types as $post_type ) {

		$rpress_taxonomies = array_merge( $rpress_taxonomies, get_object_taxonomies( $post_type ) );
		$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );

		if ( $items ) {
			foreach ( $items as $item ) {
				wp_delete_post( $item, true);
			}
		}
	}

	/** Delete All the Terms & Taxonomies */
	foreach ( array_unique( array_filter( $rpress_taxonomies ) ) as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms.
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies.
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}

	/** Delete the Plugin Pages */
	$rpress_created_pages = array( 'purchase_page', 'success_page', 'failure_page', 'order_history_page' );
	foreach ( $rpress_created_pages as $p ) {
		$page = rpress_get_option( $p, false );
		if ( $page ) {
			wp_delete_post( $page, true );
		}
	}

	/** Delete all the Plugin Options */
	delete_option( 'rpress_settings' );
	delete_option( 'rpress_version' );
	delete_option( 'rpress_use_php_sessions' );
	delete_option( 'wp_rpress_customers_db_version' );
	delete_option( 'wp_rpress_customermeta_db_version' );
	delete_option( 'rpress_completed_upgrades' );
	delete_option( 'widget_rpress_categories_tags_widget' );
	delete_option( 'widget_rpress_product_details' );
	delete_option( '_rpress_table_check' );
	delete_option( 'rpress_tracking_notice' );
	delete_option( 'rpress_earnings_total' );
	delete_option( 'rpress_tax_rates' );
	delete_option( 'rpress_version_upgraded_from' );

	/** Delete Capabilities */
	RPRESS()->roles->remove_caps();

	/** Delete the Roles */
	$rpress_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
	foreach ( $rpress_roles as $role ) {
		remove_role( $role );
	}

	// Remove all database tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rpress_customers" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "rpress_customermeta" );

	/** Cleanup Cron Events */
	wp_clear_scheduled_hook( 'rpress_daily_scheduled_events' );
	wp_clear_scheduled_hook( 'rpress_daily_cron' );
	wp_clear_scheduled_hook( 'rpress_weekly_cron' );

	// Remove any transients we've left behind
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_rpress\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_rpress\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_rpress\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_rpress\_%'" );
}
