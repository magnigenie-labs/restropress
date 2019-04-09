<?php
/**
 * Class for logging events and errors
 *
 * @package     RPRESS
 * @subpackage  Logging
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 1.0.0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Logging Class
 *
 * A general use class for logging events and errors.
 *
 * @since  1.0.0
 */
class RPRESS_Logging {

	public $is_writable = true;
	private $filename   = '';
	private $file       = '';

	/**
	 * Set up the RPRESS Logging Class
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		// Create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), 1 );

		// Create types taxonomy and default types
		add_action( 'init', array( $this, 'register_taxonomy' ), 1 );

		add_action( 'plugins_loaded', array( $this, 'setup_log_file' ), 0 );

	}

	/**
	 * Sets up the log file if it is writable
	 *
	 * @since 1.0
	 * @return void
	 */
	public function setup_log_file() {

		$upload_dir       = wp_upload_dir();
		$this->filename   = wp_hash( home_url( '/' ) ) . '-rpress-debug.log';
		$this->file       = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

	}

	/**
	 * Registers the rpress_log Post Type
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function register_post_type() {
		/* Logs post type */
		$log_args = array(
			'labels'              => array( 'name' => __( 'Logs', 'restropress' ) ),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor' ),
			'can_export'          => true,
		);

		register_post_type( 'rpress_log', $log_args );
	}

	/**
	 * Registers the Type Taxonomy
	 *
	 * The "Type" taxonomy is used to determine the type of log entry
	 *
	 * @since  1.0.0
	 * @return void
	*/
	public function register_taxonomy() {
		register_taxonomy( 'rpress_log_type', 'rpress_log', array( 'public' => false ) );
	}

	/**
	 * Log types
	 *
	 * Sets up the default log types and allows for new ones to be created
	 *
	 * @since  1.0.0
	 * @return  array $terms
	 */
	public function log_types() {
		$terms = array(
			'sale', 'file_fooditem', 'gateway_error', 'api_request'
		);

		return apply_filters( 'rpress_log_types', $terms );
	}

	/**
	 * Check if a log type is valid
	 *
	 * Checks to see if the specified type is in the registered list of types
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Logging::log_types()
	 * @param string $type Log type
	 * @return bool Whether log type is valid
	 */
	function valid_type( $type ) {
		return in_array( $type, $this->log_types() );
	}

	/**
	 * Create new log entry
	 *
	 * This is just a simple and fast way to log something. Use $this->insert_log()
	 * if you need to store custom meta data
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Logging::insert_log()
	 * @param string $title Log entry title
	 * @param string $message Log entry message
	 * @param int $parent Log entry parent
	 * @param string $type Log type (default: null)
	 * @return int Log ID
	 */
	public function add( $title = '', $message = '', $parent = 0, $type = null ) {
		$log_data = array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type,
		);

		return $this->insert_log( $log_data );
	}

	/**
	 * Easily retrieves log items for a particular object ID
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Logging::get_connected_logs()
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param int $paged Page number (default: null)
	 * @return array Array of the connected logs
	*/
	public function get_logs( $object_id = 0, $type = null, $paged = null ) {
		return $this->get_connected_logs( array( 'post_parent' => $object_id, 'paged' => $paged, 'log_type' => $type ) );
	}

	/**
	 * Stores a log entry
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Logging::valid_type()
	 * @param array $log_data Log entry data
	 * @param array $log_meta Log entry meta
	 * @return int The ID of the newly created log item
	 */
	function insert_log( $log_data = array(), $log_meta = array() ) {
		$defaults = array(
			'post_type'    => 'rpress_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false,
		);

		$args = wp_parse_args( $log_data, $defaults );

		do_action( 'rpress_pre_insert_log', $log_data, $log_meta );

		// Store the log entry
		$log_id = wp_insert_post( $args );

		// Set the log type, if any
		if ( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) ) {
			wp_set_object_terms( $log_id, $log_data['log_type'], 'rpress_log_type', false );
		}

		// Set log meta, if any
		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				update_post_meta( $log_id, '_rpress_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'rpress_post_insert_log', $log_id, $log_data, $log_meta );

		return $log_id;
	}

	/**
	 * Update and existing log item
	 *
	 * @since  1.0.0
	 * @param array $log_data Log entry data
	 * @param array $log_meta Log entry meta
	 * @return bool True if successful, false otherwise
	 */
	public function update_log( $log_data = array(), $log_meta = array() ) {

		do_action( 'rpress_pre_update_log', $log_data, $log_meta );

		$defaults = array(
			'post_type'   => 'rpress_log',
			'post_status' => 'publish',
			'post_parent' => 0,
		);

		$args = wp_parse_args( $log_data, $defaults );

		// Store the log entry
		$log_id = wp_update_post( $args );

		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				if ( ! empty( $meta ) )
					update_post_meta( $log_id, '_rpress_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'rpress_post_update_log', $log_id, $log_data, $log_meta );
	}

	/**
	 * Retrieve all connected logs
	 *
	 * Used for retrieving logs related to particular items, such as a specific purchase.
	 *
	 * @access private
	 * @since  1.0.0
	 * @param array $args Query arguments
	 * @return mixed array if logs were found, false otherwise
	 */
	public function get_connected_logs( $args = array() ) {
		$defaults = array(
			'post_type'      => 'rpress_log',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'paged'          => get_query_var( 'paged' ),
			'log_type'       => false,
		);

		$query_args = wp_parse_args( $args, $defaults );

		if ( $query_args['log_type'] && $this->valid_type( $query_args['log_type'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'rpress_log_type',
					'field'     => 'slug',
					'terms'     => $query_args['log_type'],
				)
			);
		}

		$logs = get_posts( $query_args );

		if ( $logs )
			return $logs;

		// No logs found
		return false;
	}

	/**
	 * Retrieves number of log entries connected to particular object ID
	 *
	 * @since  1.0.0
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param array $meta_query Log meta query (default: null)
	 * @param array $date_query Log data query (default: null) (since 1.9)
	 * @return int Log count
	 */
	public function get_log_count( $object_id = 0, $type = null, $meta_query = null, $date_query = null ) {

		$query_args = array(
			'post_parent'      => $object_id,
			'post_type'        => 'rpress_log',
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'fields'           => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'rpress_log_type',
					'field'     => 'slug',
					'terms'     => $type,
				)
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		if ( ! empty( $date_query ) ) {
			$query_args['date_query'] = $date_query;
		}

		$logs = new WP_Query( $query_args );

		return (int) $logs->post_count;
	}

	/**
	 * Delete a log
	 *
	 * @since  1.0.0
	 * @uses RPRESS_Logging::valid_type
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param array $meta_query Log meta query (default: null)
	 * @return void
	 */
	public function delete_logs( $object_id = 0, $type = null, $meta_query = null  ) {
		$query_args = array(
			'post_parent'    => $object_id,
			'post_type'      => 'rpress_log',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'rpress_log_type',
					'field'     => 'slug',
					'terms'     => $type,
				)
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = get_posts( $query_args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				wp_delete_post( $log, true );
			}
		}
	}

	/**
	 * Retrieve the log data
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_file_contents() {
		return $this->get_file();
	}

	/**
	 * Log message to file
	 *
	 * @since 1.0
	 * @return void
	 */
	public function log_to_file( $message = '' ) {
		$message = date( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		$this->write_to_log( $message );

	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @since 1.0
	 * @return string
	 */
	protected function get_file() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );

		}

		return $file;
	}

	/**
	 * Write the log message
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function write_to_log( $message = '' ) {
		$file = $this->get_file();
		$file .= $message;
		@file_put_contents( $this->file, $file );
	}

	/**
	 * Delete the log file or removes all contents in the log file if we cannot delete it
	 *
	 * @since 1.0
	 * @return void
	 */
	public function clear_log_file() {
		@unlink( $this->file );

		if ( file_exists( $this->file ) ) {

			// it's still there, so maybe server doesn't have delete rights
			chmod( $this->file, 0664 ); // Try to give the server delete rights
			@unlink( $this->file );

			// See if it's still there
			if ( @file_exists( $this->file ) ) {

				/*
				 * Remove all contents of the log file if we cannot delete it
				 */
				if ( is_writeable( $this->file ) ) {

					file_put_contents( $this->file, '' );

				} else {

					return false;

				}

			}

		}

		$this->file = '';
		return true;

	}

	/**
	 * Return the location of the log file that RPRESS_Logging will use.
	 *
	 * Note: Do not use this file to write to the logs, please use the `rpress_debug_log` function to do so.
	 *
	 * @since 2.9.1
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		return $this->file;
	}

}

// Initiate the logging system
$GLOBALS['rpress_logs'] = new RPRESS_Logging();

/**
 * Record a log entry
 *
 * This is just a simple wrapper function for the log class add() function
 *
 * @since 1.0.0
 *
 * @param string $title
 * @param string $message
 * @param int    $parent
 * @param null   $type
 *
 * @global $rpress_logs RPRESS Logs Object
 *
 * @uses RPRESS_Logging::add()
 *
 * @return mixed ID of the new log entry
 */
function rpress_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	global $rpress_logs;
	$log = $rpress_logs->add( $title, $message, $parent, $type );
	return $log;
}


/**
 * Logs a message to the debug log file
 *
 * @since 1.0
 *
 * @param string $message
 * @global $rpress_logs RPRESS Logs Object
 * @return void
 */
function rpress_debug_log( $message = '' ) {
	global $rpress_logs;

	if( rpress_is_debug_mode() ) {

		$rpress_logs->log_to_file( $message );

	}
}
