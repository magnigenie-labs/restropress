<?php
/**
 * Cron
 *
 * @package     RPRESS
 * @subpackage  Classes/Cron
 * @copyright   Copyright (c) 2018, Magnigenie
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since  1.0.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * RPRESS_Cron Class
 *
 * This class handles scheduled events
 *
 * @since  1.0.0
 */
class RPRESS_Cron {
	/**
	 * Get things going
	 *
	 * @since  1.0.0
	 * @see RPRESS_Cron::weekly_events()
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules'   ) );
		add_action( 'wp',             array( $this, 'schedule_events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since  1.0.0
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'restropress' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'rpress_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'weekly', 'rpress_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @access private
	 * @since  1.0.0
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'rpress_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp', true ), 'daily', 'rpress_daily_scheduled_events' );
		}
	}

}
$rpress_cron = new RPRESS_Cron;
