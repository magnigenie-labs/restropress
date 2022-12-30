<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from RestroPress.
 *
 * @package     RPRESS
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

/**
 * Exports earnings for a specified time period
 * RPRESS_Earnings_Export class.
 *
 * @since  1.0.0
 * @return void
 */
function rpress_export_earnings() {
	require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-export-earnings.php';

	$earnings_export = new RPRESS_Earnings_Export();

	$earnings_export->export();
}
add_action( 'rpress_earnings_export', 'rpress_export_earnings' );

/**
 * Exports all the payments stored in Order History to a CSV file using the
 * RPRESS_Export class.
 *
 * @since  1.0.0
 * @return void
 */
function rpress_export_payment_history() {
	require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-export-payments.php';

	$payments_export = new RPRESS_Payments_Export();

	$payments_export->export();
}
add_action( 'rpress_payment_export', 'rpress_export_payment_history' );

/**
 * Export all the customers to a CSV file.
 *
 * Note: The WordPress Database API is being used directly for performance
 * reasons (workaround of calling all posts and fetch data respectively)
 *
 * @since  1.0.0
 * @return void
 */
function rpress_export_all_customers() {
	require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';

	$customer_export = new RPRESS_Customers_Export();

	$customer_export->export();
}
add_action( 'rpress_email_export', 'rpress_export_all_customers' );

/**
 * Exports all the fooditems to a CSV file using the RPRESS_Export class.
 *
 * @since  1.0.0
 * @return void
 */
function rpress_export_all_fooditems_history() {
	require_once RP_PLUGIN_DIR . 'includes/admin/reporting/class-export-fooditem-history.php';

	$file_fooditem_export = new RPRESS_Fooditem_History_Export();

	$file_fooditem_export->export();
}
add_action( 'rpress_fooditems_history_export', 'rpress_export_all_fooditems_history' );