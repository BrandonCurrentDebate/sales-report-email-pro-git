<?php
/**
 * Sales report email
 *
 * Class WC_ASRE_Cron_Manager
 * 
 * @version       1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ASRE_Cron_Manager {
	
	const CRON_HOOK = 'wc_asre_send';
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	
	/**
	 * Get the class instance
	 *
	 * @return ASRE_Cron_Manager
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Remove the Cron
	 *
	 * @since  1.0.0
	 */
	public function remove_cron( $id ) {
		
		$arg = array((int) $id);
		wp_clear_scheduled_hook( self::CRON_HOOK, $arg );
	}

	/**
	 * Setup the Cron
	 *
	 * @since  1.0.0
	 */
	public function setup_cron( $id ) {

		// Add the count words cronjob
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {

			$data = asre_pro()->admin->get_data_byid( $id );

			$send_time = !empty($data->email_send_time) ? $data->email_send_time : '08:00';

			// Create a Date Time object when the cron should run for the first time
			$first_cron = new DateTime( gmdate( 'Y-m-d' ) . ' ' . $send_time . ':00', new DateTimeZone( wc_timezone_string() ) );	

			$first_cron->setTimeZone(new DateTimeZone('GMT'));

			$time = new DateTime( gmdate( 'Y-m-d H:i:s' ), new DateTimeZone( 'GMT' ) );
			
			if ( $time->getTimestamp() >  $first_cron->getTimestamp() ) {
				$first_cron->modify( '+1 day' );
			}
			$arg = array((int) $id);
			wp_schedule_event( $first_cron->format( 'U' ) + $first_cron->getOffset(), 'daily', self::CRON_HOOK , $arg );
		}
	}
	
	/**
	 *	Method triggered on saving admin sales report email settings
	 *	This is to make sure the time_sent parameter gets changed in the sheduled event.
	 *
	 * @since  1.1.0
	*/
	public function reset_cron( $id ) {
		$this->remove_cron($id);
		$this->setup_cron($id);
	}
}
