<?php
/**
 * Upload Functions
 *
 * @package     RPRESS
 * @subpackage  Admin/Upload
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Change RestroPress Upload Directory
 *
 * Hooks the rpress_set_upload_dir filter when appropriate. This function works by
 * hooking on the WordPress Media Uploader and moving the uploading files that
 * are used for RPRESS to an rpress directory under wp-content/uploads/ therefore,
 * the new directory is wp-content/uploads/rpress/{year}/{month}. This directory is
 * provides protection to anything uploaded to it.
 *
 * @since 1.0
 * @global $pagenow
 * @return void
 */
function rpress_change_fooditems_upload_dir() {
	global $pagenow;

	if ( ! empty( $_REQUEST['post_id'] ) && ( 'async-upload.php' == $pagenow || 'media-upload.php' == $pagenow ) ) {
		if ( 'fooditem' == get_post_type( $_REQUEST['post_id'] ) ) {
			rpress_create_protection_files( true );
			add_filter( 'upload_dir', 'rpress_set_upload_dir' );
		}
	}
}
add_action( 'admin_init', 'rpress_change_fooditems_upload_dir', 999 );


/**
 * Creates blank index.php and .htaccess files
 *
 * This function runs approximately once per month in order to ensure all folders
 * have their necessary protection files
 *
 * @since 1.0.0
 *
 * @param bool $force
 * @param bool $method
 */

function rpress_create_protection_files( $force = false, $method = false ) {
	if ( false === get_transient( 'rpress_check_protection_files' ) || $force ) {

		$upload_path = rpress_get_upload_dir();

		// Make sure the /rpress folder is created
		wp_mkdir_p( $upload_path );

		// Top level .htaccess file
		$rules = rpress_get_htaccess_rules( $method );
		if ( rpress_htaccess_exists() ) {
			$contents = @file_get_contents( $upload_path . '/.htaccess' );
			if ( $contents !== $rules || ! $contents ) {
				// Update the .htaccess rules if they don't match
				@file_put_contents( $upload_path . '/.htaccess', $rules );
			}
		} elseif( wp_is_writable( $upload_path ) ) {
			// Create the file if it doesn't exist
			@file_put_contents( $upload_path . '/.htaccess', $rules );
		}

		// Top level blank index.php
		if ( ! file_exists( $upload_path . '/index.php' ) && wp_is_writable( $upload_path ) ) {
			@file_put_contents( $upload_path . '/index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
		}

		// Now place index.php files in all sub folders
		$folders = rpress_scan_folders( $upload_path );
		foreach ( $folders as $folder ) {
			// Create index.php, if it doesn't exist
			if ( ! file_exists( $folder . 'index.php' ) && wp_is_writable( $folder ) ) {
				@file_put_contents( $folder . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}
		}
		// Check for the files once per day
		set_transient( 'rpress_check_protection_files', true, 3600 * 24 );
	}
}
add_action( 'admin_init', 'rpress_create_protection_files' );

/**
 * Checks if the .htaccess file exists in wp-content/uploads/rpress
 *
 * @since 1.0
 * @return bool
 */
function rpress_htaccess_exists() {
	$upload_path = rpress_get_upload_dir();

	return file_exists( $upload_path . '/.htaccess' );
}

/**
 * Scans all folders inside of /uploads/rpress
 *
 * @since 1.0.0
 * @return array $return List of files inside directory
 */
function rpress_scan_folders( $path = '', $return = array() ) {
	$path = $path == ''? dirname( __FILE__ ) : $path;
	$lists = @scandir( $path );

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $f ) {
			if ( is_dir( $path . DIRECTORY_SEPARATOR . $f ) && $f != "." && $f != ".." ) {
				if ( ! in_array( $path . DIRECTORY_SEPARATOR . $f, $return ) )
					$return[] = trailingslashit( $path . DIRECTORY_SEPARATOR . $f );

				rpress_scan_folders( $path . DIRECTORY_SEPARATOR . $f, $return);
			}
		}
	}

	return $return;
}

/**
 * Retrieve the .htaccess rules to wp-content/uploads/rpress/
 *
 * @since  1.0.0
 *
 * @param bool $method
 * @return mixed|void The htaccess rules
 */
function rpress_get_htaccess_rules( $method = false ) {

	if( empty( $method ) )
		$method = rpress_get_file_fooditem_method();

	switch( $method ) :

		case 'redirect' :
			// Prevent directory browsing
			$rules = "Options -Indexes";
			break;

		case 'direct' :
		default :
			// Prevent directory browsing and direct access to all files, except images (they must be allowed for featured images / thumbnails)
			$allowed_filetypes = apply_filters( 'rpress_protected_directory_allowed_filetypes', array( 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'ogg' ) );
			$rules = "Options -Indexes\n";
			$rules .= "deny from all\n";
			$rules .= "<FilesMatch '\.(" . implode( '|', $allowed_filetypes ) . ")$'>\n";
			    $rules .= "Order Allow,Deny\n";
			    $rules .= "Allow from all\n";
			$rules .= "</FilesMatch>\n";
			break;

	endswitch;
	$rules = apply_filters( 'rpress_protected_directory_htaccess_rules', $rules, $method );
	return $rules;
}


// For installs on pre WP 3.6
if( ! function_exists( 'wp_is_writable' ) ) {

	/**
	 * Determine if a directory is writable.
	 *
	 * This function is used to work around certain ACL issues
	 * in PHP primarily affecting Windows Servers.
	 *
	 * @see win_is_writable()
	 *
	 * @since 1.0
	 *
	 * @param string $path
	 * @return bool
	 */
	function wp_is_writable( $path ) {
	        if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) )
	                return win_is_writable( $path );
	        else
	                return @is_writable( $path );
	}
}