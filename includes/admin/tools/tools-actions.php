<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tools Actions
 *
 * @package     RPRESS
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */

/**
 * Register the recount batch processor
 * @since  1.0.0
 */
function rpress_register_batch_recount_store_earnings_tool() {
	add_action( 'rpress_batch_export_class_include', 'rpress_include_recount_store_earnings_tool_batch_processer', 10, 1 );
}
add_action( 'rpress_register_batch_exporter', 'rpress_register_batch_recount_store_earnings_tool', 10 );

/**
 * Loads the tools batch processing class for recounting store earnings
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function rpress_include_recount_store_earnings_tool_batch_processer( $class ) {

	if ( 'RPRESS_Tools_Recount_Store_Earnings' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/tools/class-rpress-tools-recount-store-earnings.php';
	}

}

/**
 * Register the recount fooditem batch processor
 * @since  1.0.0
 */
function rpress_register_batch_recount_fooditem_tool() {
	add_action( 'rpress_batch_export_class_include', 'rpress_include_recount_fooditem_tool_batch_processer', 10, 1 );
}
add_action( 'rpress_register_batch_exporter', 'rpress_register_batch_recount_fooditem_tool', 10 );

/**
 * Loads the tools batch processing class for recounting fooditem stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function rpress_include_recount_fooditem_tool_batch_processer( $class ) {

	if ( 'RPRESS_Tools_Recount_Download_Stats' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/tools/class-rpress-tools-recount-fooditem-stats.php';
	}

}

/**
 * Register the recount all stats batch processor
 * @since  1.0.0
 */
function rpress_register_batch_recount_all_tool() {
	add_action( 'rpress_batch_export_class_include', 'rpress_include_recount_all_tool_batch_processer', 10, 1 );
}
add_action( 'rpress_register_batch_exporter', 'rpress_register_batch_recount_all_tool', 10 );

/**
 * Loads the tools batch processing class for recounting all stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function rpress_include_recount_all_tool_batch_processer( $class ) {

	if ( 'RPRESS_Tools_Recount_All_Stats' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/tools/class-rpress-tools-recount-all-stats.php';
	}

}

/**
 * Register the reset stats batch processor
 * @since  1.0.0
 */
function rpress_register_batch_reset_tool() {
	add_action( 'rpress_batch_export_class_include', 'rpress_include_reset_tool_batch_processer', 10, 1 );
}
add_action( 'rpress_register_batch_exporter', 'rpress_register_batch_reset_tool', 10 );

/**
 * Loads the tools batch processing class for resetting store and product earnings
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function rpress_include_reset_tool_batch_processer( $class ) {

	if ( 'RPRESS_Tools_Reset_Stats' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/tools/class-rpress-tools-reset-stats.php';
	}

}

/**
 * Register the reset customer stats batch processor
 * @since  1.0.0
 */
function rpress_register_batch_customer_recount_tool() {
	add_action( 'rpress_batch_export_class_include', 'rpress_include_customer_recount_tool_batch_processer', 10, 1 );
}
add_action( 'rpress_register_batch_exporter', 'rpress_register_batch_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all customer stats
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function rpress_include_customer_recount_tool_batch_processer( $class ) {

	if ( 'RPRESS_Tools_Recount_Customer_Stats' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/tools/class-rpress-tools-recount-customer-stats.php';
	}

}
