<?php
/**
 * Contextual Help
 *
 * @package     RPRESS
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports contextual help.
 *
 * @access      private
 * @since  1.0.0
 * @return      void
 */
function rpress_reporting_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'restropress_page_rpress-reports' )
		return;

	do_action( 'rpress_reports_contextual_help', $screen );
}
add_action( 'load-restropress_page_rpress-reports', 'rpress_reporting_contextual_help' );
