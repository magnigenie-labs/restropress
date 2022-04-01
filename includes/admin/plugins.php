<?php
/**
 * Admin Plugins
 *
 * @package     RPRESS
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @author RestroPress
 * @since 1.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function rpress_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=rpress-settings' ) . '">' . esc_html__( 'General Settings', 'restropress' ) . '</a>';
	if ( $file == 'restro-press/restro-press.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'rpress_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author RestroPress
 * @since 1.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function rpress_plugin_row_meta( $input, $file ) {

	if ( $file != 'restropress/restro-press.php' )
		return $input;

	$extensions_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://restropress.com/extensions' )
	);

	$docs_link = esc_url( add_query_arg( array(
			'utm_source'   => 'plugins-page',
			'utm_medium'   => 'plugin-row',
			'utm_campaign' => 'admin',
		), 'https://docs.restropress.com' )
	);

	$links = array(
		'<a href="' . $extensions_link . '">' . __( 'Extensions', 'restropress' ) . '</a>',
		'<a href="' . $docs_link . '">' . __( 'Documentation', 'restropress' ) . '</a>',
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'rpress_plugin_row_meta', 10, 2 );
