<?php
/**
 * Import Actions
 *
 * These are actions related to import data from RestroPress.
 *
 * @package     RPRESS
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a hook allowing extensions to register a hook on the batch export process
 *
 * @since  1.0.0
 * @return void
 */
function rpress_register_batch_importers() {
	if ( is_admin() ) {
		do_action( 'rpress_register_batch_importer' );
	}
}
add_action( 'plugins_loaded', 'rpress_register_batch_importers' );

/**
 * Register the payments batch importer
 *
 * @since  1.0.0
 */
function rpress_register_payments_batch_import() {
	add_action( 'rpress_batch_import_class_include', 'rpress_include_payments_batch_import_processer', 10 );
}
add_action( 'rpress_register_batch_importer', 'rpress_register_payments_batch_import', 10 );

/**
 * Loads the payments batch process if needed
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch import
 * @return void
 */
function rpress_include_payments_batch_import_processer( $class ) {

	if ( 'RPRESS_Batch_Payments_Import' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/import/class-batch-import-payments.php';
	}

}

/**
 * Register the fooditems batch importer
 *
 * @since  1.0.0
 */
function rpress_register_fooditems_batch_import() {
	add_action( 'rpress_batch_import_class_include', 'rpress_include_fooditems_batch_import_processer', 10 );
}
add_action( 'rpress_register_batch_importer', 'rpress_register_fooditems_batch_import', 10 );

/**
 * Loads the fooditems batch process if needed
 *
 * @since  1.0.0
 * @param  string $class The class being requested to run for the batch import
 * @return void
 */
function rpress_include_fooditems_batch_import_processer( $class ) {

	if ( 'RPRESS_Batch_RestroPress_Import' === $class ) {
		require_once RP_PLUGIN_DIR . 'includes/admin/import/class-batch-import-fooditems.php';
	}

}