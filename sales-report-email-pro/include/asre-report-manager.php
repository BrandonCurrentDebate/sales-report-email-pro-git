<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

// WC_Admin_Report is not autoloaded we manually need to load
include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );

class ASRE_Report_Manager extends WC_Admin_Report {

	/**
	 * The constructor creates a WC_Admin_Reports object sets the start and end date
	 *
	 * @param WC_ESRE_Date_Range $date_range
	 *
	 * @since  1.0.0
	 */
	public function __construct( $date_range ) {
		$this->start_date = (int) strtotime($date_range->start_date->format( 'Y-m-d H:i:s' ));
		$this->end_date   = (int) strtotime($date_range->end_date->format( 'Y-m-d H:i:s' ));
	}
	
}
