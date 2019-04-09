<?php
/**
 * Import / Export Settings
 *
 * @package     RPRESS
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2013, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



function rpress_export_import() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Export / Import Settings', 'restropress' ); ?></h2>
		<div class="metabox-holder">
			<?php do_action( 'rpress_export_import_top' ); ?>
			<div class="postbox">
				<h3><span><?php _e( 'Export Settings', 'restropress' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export the RestroPress settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'restropress' ); ?></p>
					<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'restropress' ), admin_url( 'edit.php?post_type=fooditem&page=rpress-reports&tab=export' ) ); ?>
					<form method="post" action="<?php echo admin_url( 'tools.php?page=rpress-settings-export-import' ); ?>">
						<p>
							<input type="hidden" name="rpress_action" value="export_settings" />
						</p>
						<p>
							<?php wp_nonce_field( 'rpress_export_nonce', 'rpress_export_nonce' ); ?>
							<?php submit_button( __( 'Export', 'restropress' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<div class="postbox">
				<h3><span><?php _e( 'Import Settings', 'restropress' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import the RestroPress settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'restropress' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'tools.php?page=rpress-settings-export-import' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="rpress_action" value="import_settings" />
							<?php wp_nonce_field( 'rpress_import_nonce', 'rpress_import_nonce' ); ?>
							<?php submit_button( __( 'Import', 'restropress' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<?php do_action( 'rpress_export_import_bottom' ); ?>
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
	<?php

}


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since  1.0.0
 * @return void
 */
function rpress_process_settings_export() {

	if( empty( $_POST['rpress_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['rpress_export_nonce'], 'rpress_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$settings = array();
	$settings['general']    = get_option( 'rpress_general' );
	$settings['gateways']   = get_option( 'rpress_gateways' );
	$settings['emails']     = get_option( 'rpress_emails' );
	$settings['styles']     = get_option( 'rpress_styles' );
	$settings['taxes']      = get_option( 'rpress_taxes' );
	$settings['extensions'] = get_option( 'rpress_extensions' );
	$settings['misc']       = get_option( 'rpress_misc' );

	ignore_user_abort( true );

	if ( ! rpress_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=rpress-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;

}
add_action( 'rpress_export_settings', 'rpress_process_settings_export' );

/**
 * Process a settings import from a json file
 *
 * @since  1.0.0
 * @return void
 */
function rpress_process_settings_import() {

	if( empty( $_POST['rpress_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['rpress_import_nonce'], 'rpress_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_shop_settings' ) )
		return;

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'restropress' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = rpress_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'rpress_general'   , $settings['general']    );
	update_option( 'rpress_gateways'  , $settings['gateways']   );
	update_option( 'rpress_emails'    , $settings['emails']     );
	update_option( 'rpress_styles'    , $settings['styles']     );
	update_option( 'rpress_taxes'     , $settings['taxes']      );
	update_option( 'rpress_extensions', $settings['extensions'] );
	update_option( 'rpress_misc'      , $settings['misc']       );

	wp_safe_redirect( admin_url( 'tools.php?page=rpress-settings-export-import&rpress-message=settings-imported' ) ); exit;

}
add_action( 'rpress_import_settings', 'rpress_process_settings_import' );