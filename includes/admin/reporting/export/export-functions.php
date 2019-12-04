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
require_once RP_PLUGIN_DIR . 'includes/admin/reporting/export/export-actions.php';

/**
 * Process batch exports via ajax
 *
 * @since 2.4
 * @return void
 */
function rpress_do_ajax_export() {

	require_once RP_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

	parse_str( $_POST['form'], $form );

	$_REQUEST = $form = (array) $form;


	if( ! wp_verify_nonce( $_REQUEST['rpress_ajax_export'], 'rpress_ajax_export' ) ) {
		die( '-2' );
	}

	do_action( 'rpress_batch_export_class_include', $form['rpress-export-class'] );

	$step     = absint( $_POST['step'] );
	$class    = sanitize_text_field( $form['rpress-export-class'] );
	$export   = new $class( $step );

	if( ! $export->can_export() ) {
		die( '-1' );
	}

	if ( ! $export->is_writable ) {
		echo json_encode( array( 'error' => true, 'message' => __( 'Export location or file not writable', 'restropress' ) ) ); exit;
	}

	$export->set_properties( $_REQUEST );

	// Added in 2.5 to allow a bulk processor to pre-fetch some data to speed up the remaining steps and cache data
	$export->pre_fetch();

	$ret = $export->process_step( $step );

	$percentage = $export->get_percentage_complete();

	if( $ret ) {

		$step += 1;
		echo json_encode( array( 'step' => $step, 'percentage' => $percentage ) ); exit;

	} elseif ( true === $export->is_empty ) {

		echo json_encode( array( 'error' => true, 'message' => __( 'No data found for export parameters', 'restropress' ) ) ); exit;

	} elseif ( true === $export->done && true === $export->is_void ) {

		$message = ! empty( $export->message ) ? $export->message : __( 'Batch Processing Complete', 'restropress' );
		echo json_encode( array( 'success' => true, 'message' => $message ) ); exit;

	} else {

		$args = array_merge( $_REQUEST, array(
			'step'       => $step,
			'class'      => $class,
			'nonce'      => wp_create_nonce( 'rpress-batch-export' ),
			'rpress_action' => 'fooditem_batch_export',
		) );

		$fooditem_url = add_query_arg( $args, admin_url() );

		echo json_encode( array( 'step' => 'done', 'url' => $fooditem_url ) ); exit;

	}
}
add_action( 'wp_ajax_rpress_do_ajax_export', 'rpress_do_ajax_export' );
