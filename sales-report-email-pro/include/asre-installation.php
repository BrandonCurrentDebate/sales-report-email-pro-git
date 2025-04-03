<?php
/**
 * Sales report email
 *
 * Class ASRE_Installation
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ASRE_Installation { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	public $table;
	
	/**
	 * Get the class instance
	 *
	 * @return ASRE_Installation
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	* Function callback for add not existing key in database.
	*
	*/
	public function asre_update_install_callback() {
		
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';
		$columns = array( 'id', 'email_enable', 'report_name', 'report_status', 'email_recipients', 'email_subject', 'email_content', 'date_created', 'email_interval', 'email_select_week', 'email_select_month', 'day_hour_start', 'day_hour_end', 'email_send_time', 'daterange', 'branding_logo', 'show_header_image', 'display_data' );
		
		foreach ( $columns as $column ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = '%2s'", $this->table, $column ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD %2s TEXT NULL DEFAULT NULL', $this->table, $column ) );
			}
		}

		if (version_compare(get_option( 'sales_report_email_pro' ), '1.0', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';

			//Drop columns
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'attch_pdf_report' ", $this->table ), ARRAY_A );
			if ( $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s DROP COLUMN attch_pdf_report', $this->table) );
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'test_email_recipients' ", $this->table ), ARRAY_A );
			if ( $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s DROP COLUMN test_email_recipients', $this->table) );
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'report_status' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD report_status text NOT NULL', $this->table) );
				$wpdb->query( $wpdb->prepare( "UPDATE %1s SET report_status = 'publish'", $this->table) );
			}
			update_option('sales_report_email_pro', '1.0');
		}

		if (version_compare(get_option( 'sales_report_email_pro' ), '1.1.1', '<') ) {

			//database functions
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';

			//ADD columns 
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'report_status' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD report_status TEXT NOT NULL AFTER report_name', $this->table) );
				$wpdb->query( $wpdb->prepare( "UPDATE %1s SET report_status = 'publish'", $this->table) );
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'daterange' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD daterange TEXT NULL DEFAULT NULL AFTER email_send_time', $this->table ) );
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'show_header_image' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD show_header_image TEXT NULL DEFAULT NULL AFTER daterange', $this->table ) );
				$wpdb->query( $wpdb->prepare( "UPDATE %1s SET show_header_image = '1'", $this->table) );
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'branding_logo' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD branding_logo TEXT NULL DEFAULT NULL AFTER daterange', $this->table ) );
			}
			
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = 'display_data' ", $this->table ), ARRAY_A );
			if ( ! $row ) {
				$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD display_data TEXT NULL DEFAULT NULL AFTER branding_logo', $this->table ) );

				//Migration columns
				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY id DESC', $this->table ) );
				if ( empty($results) ) {
					return;
				}
				
				foreach ( $results as $report  ) {

					$data = (array) asre_pro()->admin->get_data_byid( $report->id );

					$display_data = (object) array(
						'display_edit_report_link' => isset($report->display_edit_report_link) ? $report->display_edit_report_link : '',
						'display_zorem_branding' =>isset($report->display_zorem_branding) ? $report->display_zorem_branding : '',
						'display_previous_period' => isset($report->display_previous_period) ? $report->display_previous_period : '',
						'display_gross_sales' => isset($report->display_gross_sales) ? $report->display_gross_sales : '',
						'display_total_sales' => isset($report->display_total_sales) ? $report->display_total_sales : '',
						'display_coupon_used' => isset($report->display_coupon_used) ? $report->display_coupon_used : '',
						'display_coupon_count' => isset($report->display_coupon_count) ? $report->display_coupon_count : '',
						'display_total_refunds' => isset($report->display_total_refunds) ? $report->display_total_refunds : '',
						'display_total_tax' => isset($report->display_total_tax) ? $report->display_total_tax : '',
						'display_total_shipping' => isset($report->display_total_shipping) ? $report->display_total_shipping : '',
						'display_total_shipping_tax' => isset($report->display_total_shipping_tax) ? $report->display_total_shipping_tax : '',
						'display_net_revenue' => isset($report->display_net_revenue) ? $report->display_net_revenue : '',
						'display_total_orders' => isset($report->display_total_orders) ? $report->display_total_orders : '',
						'display_total_items' => isset($report->display_total_items) ? $report->display_total_items : '',
						'display_signups' => isset($report->display_signups) ? $report->display_signups : '',
						'display_downloads' => isset($report->display_downloads) ? $report->display_downloads : '',
						'display_average_order_value' => isset($report->display_average_order_value) ? $report->display_average_order_value : '',
						'display_average_daily_sales' => isset($report->display_average_daily_sales) ? $report->display_average_daily_sales : '',
						'display_average_daily_items' => isset($report->display_average_daily_items) ? $report->display_average_daily_items : '',
						'display_active_subscriptions' => isset($report->display_active_subscriptions) ? $report->display_active_subscriptions : '',
						'display_signup_subscriptions' => isset($report->display_signup_subscriptions) ? $report->display_signup_subscriptions : '',
						'display_signup_revenue' => isset($report->display_signup_revenue) ? $report->display_signup_revenue : '',
						'display_renewal_subscriptions' => isset($report->display_renewal_subscriptions) ? $report->display_renewal_subscriptions : '',
						'display_renewal_revenue' => isset($report->display_renewal_revenue) ? $report->display_renewal_revenue : '',
						'display_switch_subscriptions' => isset($report->display_switch_subscriptions) ? $report->display_switch_subscriptions : '',
						'display_switch_revenue' => isset($report->display_switch_revenue) ? $report->display_switch_revenue : '',
						'display_resubscribe_subscriptions' => isset($report->display_resubscribe_subscriptions) ? $report->display_resubscribe_subscriptions : '',
						'display_resubscribe_revenue' => isset($report->display_resubscribe_revenue) ? $report->display_resubscribe_revenue : '',
						'display_cancellation_subscriptions' => isset($report->display_cancellation_subscriptions) ? $report->display_cancellation_subscriptions : '',
						'display_cancellation_revenue' => isset($report->display_cancellation_revenue) ? $report->display_cancellation_revenue : '',
						'display_net_subscription_gain' => isset($report->display_net_subscription_gain) ? $report->display_net_subscription_gain : '',
						'display_top_sellers' => isset($report->display_top_sellers) ? $report->display_top_sellers : '',
						'display_top_variations' => isset($report->display_top_variations) ? $report->display_top_variations : '',
						'display_top_categories' => isset($report->display_top_categories) ? $report->display_top_categories : '',
						'display_sales_by_coupons' => isset($report->display_sales_by_coupons) ? $report->display_sales_by_coupons : '',
						'display_sales_by_billing_city' => isset($report->display_sales_by_billing_city) ? $report->display_sales_by_billing_city : '',
						'display_sales_by_shipping_city' => isset($report->display_sales_by_shipping_city) ? $report->display_sales_by_shipping_city : '',
						'display_sales_by_billing_state' => isset($report->display_sales_by_billing_state) ? $report->display_sales_by_billing_state : '',
						'display_sales_by_shipping_state' => isset($report->display_sales_by_shipping_state) ? $report->display_sales_by_shipping_state : '',
						'display_sales_by_billing_country' => isset($report->display_sales_by_billing_country) ? $report->display_sales_by_billing_country : '',
						'display_sales_by_shipping_country' => isset($report->display_sales_by_shipping_country) ? $report->display_sales_by_shipping_country : '',
						'display_order_status' => isset($report->display_order_status) ? $report->display_order_status : '',
						'display_order_details' => isset($report->display_order_details) ? $report->display_order_details : '',
						'display_payment_method' => isset($report->display_payment_method) ? $report->display_payment_method : '',
						'display_total_subscriber' => isset($report->display_total_subscriber) ? $report->display_total_subscriber : '',
						'display_top_sellers_row' => isset($report->display_top_sellers_row) ? $report->display_top_sellers_row : '',
						'display_top_variations_row' => isset($report->display_top_variations_row) ? $report->display_top_variations_row : '',
						'display_top_categories_row' => isset($report->display_top_categories_row) ? $report->display_top_categories_row : '',
						'display_sales_by_coupons_row' => isset($report->display_sales_by_coupons_row) ? $report->display_sales_by_coupons_row : '',
						'display_sales_by_billing_city_row' => isset($report->display_sales_by_billing_city_row) ? $report->display_sales_by_billing_city_row : '',
						'display_sales_by_shipping_city_row' => isset($report->display_sales_by_shipping_city_row) ? $report->display_sales_by_shipping_city_row : '',
						'display_sales_by_billing_state_row' => isset($report->display_sales_by_billing_state_row) ? $report->display_sales_by_billing_state_row : '',
						'display_sales_by_shipping_state_row' => isset($report->display_sales_by_shipping_state_row) ? $report->display_sales_by_shipping_state_row : '',
						'display_sales_by_billing_country_row' => isset($report->display_sales_by_billing_country_row) ? $report->display_sales_by_billing_country_row : '',
						'display_sales_by_shipping_country_row' => isset($report->display_sales_by_shipping_country_row) ? $report->display_sales_by_shipping_country_row : '',
					);
					
					$data['display_data'] =  serialize( $display_data );
					$wpdb->update( $this->table, $data, array('id' => wc_clean($report->id)) );

				}
				
				//DROP old columns
				$tabledata = $wpdb->get_row( $wpdb->prepare('SELECT display_data FROM %1s LIMIT 1', $this->table) );

				$old_columns = unserialize($tabledata->display_data);
				if ( !empty($old_columns) ) {
					foreach ( $old_columns as $column_name => $value ) {
						$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = '%2s' ", $this->table, $column_name ), ARRAY_A );
						if ( $row ) {
							//Drop columns
							$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s DROP COLUMN %2s', $this->table, $column_name ) );
						}
					}
				}
			}

			//MODIFY columns
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY report_status TEXT AFTER report_name', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_recipients TEXT AFTER report_status', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_subject TEXT AFTER email_recipients', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_content TEXT AFTER email_subject', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY date_created TEXT AFTER email_content', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_interval TEXT AFTER date_created', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_select_week TEXT AFTER email_interval', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_select_month TEXT AFTER email_select_week', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY day_hour_start TEXT AFTER email_select_month', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY day_hour_end TEXT AFTER day_hour_start', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY email_send_time TEXT AFTER day_hour_end', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY daterange TEXT AFTER email_send_time', $this->table) );
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s MODIFY branding_logo TEXT AFTER daterange', $this->table) );

			update_option('sales_report_email_pro', '1.1.1');
		}

	}

	/**
	* Insert database table and columns
	*
	*/
	public function asre_insert_table_columns() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';

		if ( !$wpdb->query( $wpdb->prepare( 'show tables like %s', $this->table ) ) ) {			
			$this->create_advanced_sales_report_table();	
		}
	}

	/*
	* function for create salse report email table
	*/
	public function create_advanced_sales_report_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();	
		$this->table = $wpdb->prefix . 'asre_sales_report';		
		$sql = "CREATE TABLE $this->table (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			email_enable varchar(500) DEFAULT '' NOT NULL,
			report_name TEXT NULL DEFAULT NULL,
			report_status TEXT NULL DEFAULT NULL,
			email_recipients TEXT NULL DEFAULT NULL,
			email_subject TEXT NULL DEFAULT NULL,
			email_content TEXT NULL DEFAULT NULL,
			date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			email_interval TEXT NULL DEFAULT NULL,
			email_select_week TEXT NULL DEFAULT NULL,
			email_select_month TEXT NULL DEFAULT NULL,
			day_hour_start TEXT NULL DEFAULT NULL,
			day_hour_end TEXT NULL DEFAULT NULL,
			email_send_time TEXT NULL DEFAULT NULL,
			daterange TEXT NULL DEFAULT NULL,
			branding_logo TEXT NULL DEFAULT NULL,
			display_data TEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";			
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}
