<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SRE_Customizer_Admin {

	private static $screen_id = 'sre_customizer';
	private static $screen_title = 'SRE Customizer'; 

	// WooCommerce email classes.
	public static $email_types_class_names  = array(
		'new_order'                         => 'WC_Email_New_Order',
		'cancelled_order'                   => 'WC_Email_Cancelled_Order',
		'customer_processing_order'         => 'WC_Email_Customer_Processing_Order',
		'customer_completed_order'          => 'WC_Email_Customer_Completed_Order',
		'customer_refunded_order'           => 'WC_Email_Customer_Refunded_Order',
		'customer_on_hold_order'            => 'WC_Email_Customer_On_Hold_Order',
		'customer_invoice'                  => 'WC_Email_Customer_Invoice',
		'failed_order'                      => 'WC_Email_Failed_Order',
		'customer_new_account'              => 'WC_Email_Customer_New_Account',
		'customer_note'                     => 'WC_Email_Customer_Note',
		'customer_reset_password'           => 'WC_Email_Customer_Reset_Password',
	);
	
	public static $email_types_order_status = array(
		'new_order'                         => 'processing',
		'cancelled_order'                   => 'cancelled',
		'customer_processing_order'         => 'processing',
		'customer_completed_order'          => 'completed',
		'customer_refunded_order'           => 'refunded',
		'customer_on_hold_order'            => 'on-hold',
		'customer_invoice'                  => 'processing',
		'failed_order'                      => 'failed',
		'customer_new_account'              => null,
		'customer_note'                     => 'processing',
		'customer_reset_password'           => null,
	);
	
	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return SRE_Customizer_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0
	*/
	public function __construct() {
		$this->init();
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {

		//adding hooks
		add_action( 'admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );

		add_action('rest_api_init', array( $this, 'route_api_functions' ) );
				
		add_action('admin_enqueue_scripts', array( $this, 'customizer_enqueue_scripts' ) );

		add_action('admin_footer', array( $this, 'admin_footer_enqueue_scripts' ) );

		add_action( 'wp_ajax_' . self::$screen_id . '_email_preview', array( $this, 'get_preview_func' ) );
		add_action( 'wp_ajax_send_' . self::$screen_id . '_test_email', array( $this, 'send_test_email_func' ) );

		// Custom Hooks for everyone
		add_filter( 'sre_customizer_email_options', array( $this, 'sre_customizer_email_options' ), 10, 1);
		add_filter( 'sre_customizer_preview_content', array( $this, 'sre_customizer_preview_content' ), 10, 1);
		add_action( 'wp_ajax_save_sre_sortable_settings', array( $this, 'customizer_save_sort_options_settings' ) );
		
	}
	
	/*
	 * Admin Menu add function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_menu_page( __( self::$screen_title, 'sales-report-email-pro' ), __( self::$screen_title, 'sales-report-email-pro' ), 'manage_options', self::$screen_id, array( $this, 'react_settingsPage' ) );
	}

	/*
	 * Call Admin Menu data function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function react_settingsPage() {
		echo '<div id="root"></div>';
	}

	/*
	 * Add admin javascript
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function admin_footer_enqueue_scripts() {
		echo '<style type="text/css">#toplevel_page_' . wp_kses_post(self::$screen_id) . ' { display: none !important; }</style>';
	}
	
	/*
	* Add admin javascript
	*
	* @since 1.0
	*/	
	public function customizer_enqueue_scripts() {
		
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '' ;
		
		// Add condition for css & js include for admin page  
		if ( self::$screen_id == $page ) {

			// Add the WP Media 
			wp_enqueue_media();

			wp_enqueue_script( self::$screen_id, plugin_dir_url(__FILE__) . 'dist/main.js', ['jquery', 'wp-util', 'wp-color-picker'], time(), true);
			wp_localize_script( self::$screen_id, self::$screen_id, array(
				'main_title'	=> self::$screen_title,
				'admin_email' => get_option('admin_email'),
				'send_test_email_btn' => true,
				'translations' => array(
					esc_html__( 'Save', 'sales-report-email-pro' ),
					esc_html__( 'You are customizing', 'sales-report-email-pro' ),
					esc_html__( 'Customizing', 'sales-report-email-pro' ),
					esc_html__( 'Send Test Email', 'sales-report-email-pro' ),
					esc_html__( 'Send a test email', 'sales-report-email-pro' ),
					esc_html__( 'Enter Email addresses (comma separated)', 'sales-report-email-pro' ),
					esc_html__( 'Send', 'sales-report-email-pro' ),
					esc_html__( 'Settings Successfully Saved.', 'sales-report-email-pro' ),
					esc_html__( 'Please save the changes to send test email.', 'sales-report-email-pro' )
				),
				'back_to_wordpress_link' => admin_url('admin.php?page=woocommerce-advanced-sales-report-email'),
				'pro_link' => 'https://www.zorem.com/product/sales-report-email-pro/',
				'rest_nonce'	=> wp_create_nonce('wp_rest'),
				'rest_base'	=> esc_url_raw( rest_url() ),
			));

			// Add tiptip js and css file
			wp_enqueue_script( 'moment.min', plugin_dir_url(__FILE__) . 'assets/moment.min.js', array(), asre_pro()->version );
			wp_enqueue_style( 'daterangepicker', plugin_dir_url(__FILE__) . 'assets/daterangepicker.css', array(), asre_pro()->version );
			wp_enqueue_script( 'daterangepicker', plugin_dir_url(__FILE__) . 'assets/daterangepicker.js', array(), asre_pro()->version );

			wp_enqueue_style( self::$screen_id . '-custom', plugin_dir_url(__FILE__) . 'assets/custom.css', array(), time() );
			wp_enqueue_script( self::$screen_id . '-custom', plugin_dir_url(__FILE__) . 'assets/custom.js', ['jquery', 'wp-util', 'wp-color-picker'], time(), true );
		}
		
	}


	/*
	 * Customizer Routes API 
	*/
	public function route_api_functions() {

		register_rest_route( self::$screen_id, 'settings', array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_settings_route_api'],
			'permission_callback' => '__return_true',
		));

		/*register_rest_route( self::$screen_id, 'preview', array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_preview_route_api'],
			'permission_callback' => '__return_true',
		));*/

		register_rest_route( self::$screen_id, 'store/update', array(
			'methods'				=> 'POST',
			'callback'				=> [$this, 'update_store_settings'],
			'permission_callback'	=> '__return_true',
		));

		register_rest_route( self::$screen_id, 'send-test-email', array(
			'methods'				=> 'POST',
			'callback'				=> [$this, 'send_test_email_func'],
			'permission_callback'	=> '__return_true',
		));

	}

	/*
	 * Settings API 
	*/
	public function return_json_sucess_settings_route_api( $request ) {
		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';
		return wp_send_json_success($this->customize_setting_options_func( $preview ));

	}

	public function customize_setting_options_func( $preview) {

		$settings = apply_filters(  self::$screen_id . '_email_options' , $preview );
		
		return $settings; 

	}

	/*
	 * Preview API 
	*/
	/*public function return_json_sucess_preview_route_api($request) {
		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';
		return wp_send_json_success($this->get_preview_email($preview));
	}*/

	public function get_preview_func() {
		$preview = isset($_GET['preview']) ? sanitize_text_field($_GET['preview']) : '';
		echo wp_kses_post($this->get_preview_email($preview));
		die();
	}

	/**
	 * Get the email content
	 *
	 */
	public function get_preview_email( $preview ) { 

		$content = apply_filters( self::$screen_id . '_preview_content' , $preview );
		
		$content .= '<style type="text/css">body{margin: 0;}</style>';
		
		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_css_tags' ) );
		add_filter( 'safe_style_css', array( $this, 'safe_style_css' ), 10, 1 );

		return wp_kses_post($content);
	}

	/*
	* update a customizer settings
	*/
	public function update_store_settings( $request ) {
		
		$preview = !empty($request) ? $request->get_param('preview') : '';

		$data = $request->get_params() ? $request->get_params() : array();
		
		if ( ! empty( $data ) ) {
			
			//data to be saved
			
			$settings = $this->customize_setting_options_func( $preview );
			
			foreach ( $settings as $key => $val ) {

				if ( !isset($data[$key]) || ( isset($val['show']) && true != $val['show'] ) ) {
					continue;
				}

				//check column exist
				if ( isset( $val['option_type'] ) && 'key' == $val['option_type'] ) {
					$data[$key] = isset($data[$key]) ? wp_kses_post( wp_unslash( $data[$key] ) ) : '';
					update_option( $key, $data[$key] );
				} elseif ( isset( $val['option_type'] ) && 'array' == $val['option_type'] ) {
					if ( isset( $val['option_key'] ) && isset( $val['option_name'] ) ) {
						$option_data = get_option( $val['option_name'], array() );
						if ( 'enabled' == $val['option_key'] ) {
							$option_data[$val['option_key']] = isset($data[$key]) && 1 == $data[$key] ? wp_kses_post( wp_unslash( 'yes' ) ) : wp_kses_post( wp_unslash( 'no' ) );
						} else {
							$option_data[$val['option_key']] = isset($data[$key]) ? wp_kses_post( wp_unslash( $data[$key] ) ) : '';
						}
						update_option( $val['option_name'], $option_data );
					} elseif ( isset($val['option_name']) ) {
						$option_data = get_option( $val['option_name'], array() );
						$option_data[$key] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
						update_option( $val['option_name'], $option_data );
					}
				}
			}

			//// SRE Free Settings Save
			global $wpdb;
			$this->table = $wpdb->prefix . 'asre_sales_report';
			$report_data = (array) asre_pro()->admin->get_data_byid( $preview );
			$display_data = isset($report_data->display_data) && !empty($report_data->display_data) ? unserialize($report_data->display_data) : (object) array();

			foreach ( $settings as $key2 => $val2 ) {
				if ( !isset($data[$key2] ) && ( isset($val2['show']) && true != $val2['show'] ) ) {
					continue;
				}

				if ( isset( $val2['database_column'] ) ) {
					if ( isset( $val2['column_name'] ) && 'display_data' == $val2['column_name'] ) {
						$display_data->$key2 = isset($data[$key2]) ? sanitize_text_field($data[$key2]) : '';
						if ( isset( $val2['breakdown'] ) && true == $val2['breakdown'] ) {
							$row_key = $key2 . '_row';
							$display_data->$row_key = isset($data[$row_key]) ? sanitize_text_field($data[$row_key]) : '';
						}
					} else {
						$report_data[$key2] = isset($data[$key2]) ? $data[$key2] : '';
					}
				}
			}

			if (!empty($display_data)) {
				if ( 1 > $preview ) { 
					$report_data['report_status'] = 'publish';
					$report_data['daterange'] = isset($data['daterange']) ? serialize($data['daterange']) : '';
					$report_data['display_data'] = serialize($display_data);
					$wpdb->insert( $this->table, $report_data );
					$preview = $wpdb->insert_id;
				} else {
					$report_data['report_status'] = 'publish';
					$report_data['daterange'] = isset($data['daterange']) ? serialize($data['daterange']) : '';
					$report_data['display_data'] = serialize($display_data);
					$wpdb->update( $this->table, $report_data, array('id' => wc_clean($preview)) );
				}

				//cron reset/update
				asre_pro()->cron->reset_cron($preview);
			}
			/// end SRE Free ////

			echo json_encode( array('success' => true, 'preview' => $preview) );
			die();
	
		}

		echo json_encode( array('success' => false) );
		die();
	}

	/*
	* save settings function
	*/
	public function customizer_save_sort_options_settings() {
		
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';

		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
			die();
		}

		$reportId = isset( $_POST['id'] ) ? sanitize_text_field($_POST['id']) : '';
		$data = (array) asre_pro()->admin->get_data_byid( $reportId );
		$key = isset($_POST['key']) ?  sanitize_text_field($_POST['key']) : '';
		$column_name = !empty($key) ? $key . '_sort' : '';
		$column_data = isset($_POST['data']) ?  wc_clean($_POST['data']) : '';

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '%1s' AND COLUMN_NAME = '%2s' ", $this->table, $column_name ), ARRAY_A );
		if ( ! $row ) {
			$wpdb->query( $wpdb->prepare( 'ALTER TABLE %1s ADD %2s TEXT NULL DEFAULT NULL', $this->table, $column_name ) );
		}

		if ( !empty($column_name) && !empty($column_data) ) {
			$data[$column_name] = serialize($column_data);			
			$wpdb->update( $this->table, $data, array('id' => wc_clean($reportId)) );
		}
		
		echo json_encode( array('success' => 'true') );
		die();
	
	}

	/*
	* send a test email
	*/
	public function send_test_email_func( $request ) {

		$data = $request->get_params() ? $request->get_params() : array();

		$preview = !empty( $data['preview'] ) ? sanitize_text_field($data['preview']) : '';
		$recipients = !empty( $data['recipients'] ) ? sanitize_text_field($data['recipients']) : '';
		
		if ( ! empty( $preview ) && ! empty( $recipients ) ) {
			
			$report_data = asre_pro()->admin->get_data_byid( $preview );
			$message 		= apply_filters( self::$screen_id . '_preview_content' , $preview );
			$subject_email 	= !empty($report_data->email_subject) ? $report_data->email_subject : 'email';
			$subject = str_replace('{site_title}', get_bloginfo( 'name' ), '[TEST] ' . $subject_email );
			
			// create a new email
			$email 		= new WC_Email();
			add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

			$recipients = explode( ',', $recipients );
			if ($recipients) {
				foreach ( $recipients as $recipient) {
					wp_mail( $recipient, $subject, $message, $email->get_headers() );
				}
			}
			
			echo json_encode( array('success' => true) );
			die();
			
		}

		echo json_encode( array('success' => false) );
		die();
	}

	public function sre_customizer_email_options( $preview ) {

		$all_data = asre_pro()->admin->get_data();
		$email_types = array();
		foreach ( $all_data as $report ) {
			$email_types[$report->id] = $report->report_name; 
		}

		$report_data = asre_pro()->admin->get_data_byid( $preview );

		$display_data = isset($report_data->display_data) && !empty($report_data->display_data) ? unserialize($report_data->display_data) : array();

		$send_time_array = array();
				
		$WCS = false;
		if ( class_exists( 'WC_Subscriptions_Manager' ) ) {
			$WCS = true;
		}
		
		$send_time_array = array();
				
		for ( $hour = 0; $hour < 24; $hour++ ) {
			for ( $min = 0; $min < 60; $min = $min + 30 ) {	
				$this_time = gmdate( 'g:ia', strtotime( "2014-01-01 $hour:$min" ) );
				$This_time = gmdate( 'H:i', strtotime( "2014-01-01 $hour:$min" ) );
				$send_time_array[ $This_time ] = $this_time;
			}
			unset($send_time_array[ '00:00' ]);
		}

		$month = array();
		for ( $day = 1; $day <= 30; $day++ ) {
			$month[ $day ] = $day;
		}
		
		$week = array(
			esc_html__( 'Sunday', 'woocommerce' ),
			esc_html__( 'Monday', 'woocommerce' ),
			esc_html__( 'Tuesday', 'woocommerce' ),
			esc_html__( 'Wednesday', 'woocommerce' ),
			esc_html__( 'Thursday', 'woocommerce' ),
			esc_html__( 'Friday', 'woocommerce' ),
			esc_html__( 'Saturday', 'woocommerce' ),
		);
		$interval = array(
			'daily'   => esc_html__( 'Daily', 'sales-report-email-pro' ),
			'daily-overnight'   => esc_html__( 'Daily Flex Overnight', 'sales-report-email-pro' ),
			'weekly'  => esc_html__( 'Weekly', 'sales-report-email-pro' ),
			'monthly' => esc_html__( 'Monthly', 'sales-report-email-pro' ),
			'month-to-date' => esc_html__( 'Month to Date', 'sales-report-email-pro' ),
			'last-30-days' => esc_html__( 'Last 30 Days', 'sales-report-email-pro' ),
			'one-time' => esc_html__( 'One time', 'sales-report-email-pro' ),
		);		
		
		$settings = array(

			//panels
			'report_settings'	=> array(
				'title'	=> esc_html__( 'Report Settings', 'sales-report-email-pro' ),
				'type'	=> 'panel',
			),
			'report_design'	=> array(
				'title'	=> esc_html__( 'Report Design', 'sales-report-email-pro' ),
				'type'	=> 'panel',
			),
			'report_totals'	=> array(
				'title'	=> esc_html__( 'Report Totals', 'sales-report-email-pro' ),
				'type'	=> 'panel',
			),
			'report_details'	=> array(
				'title'	=> esc_html__( 'Report Details', 'sales-report-email-pro' ),
				'type'	=> 'panel',
			),

			//Header options
			'email_type' => array(
				//'title'    => esc_html__( 'Editing:', 'sales-report-email-pro' ),
				'type'     => 'select',
				'options'  => $email_types,
				'show'     => true,
				'previewType' => true,
				'nav' => 'header',
				'default'  => $preview ? $preview : '',
			),
			'email_enable' => array(
				'title'    => esc_html__( 'Enable email', 'sales-report-email-pro' ),
				'type'     => 'tgl-btn',
				'show'		=> true,
				'nav' => 'header',
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'default'	=> isset($report_data->email_enable) ? $report_data->email_enable : 1,
				'class'	=> 'align-right',
			),
			
			//settings
			'report_name' => array(
				'parent'=> 'report_settings',
				'type'		=> 'text',
				'title'		=> esc_html__( 'Report Name', 'sales-report-email-pro' ),
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'placeholder' => esc_html__( 'Sales Report', 'sales-report-email-pro' ),
				'default'	=> isset($report_data->report_name) ? $report_data->report_name : esc_html__( 'Sales Report', 'sales-report-email-pro' ),
				'class'	=> 'heading'
			),
			'email_interval' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Report Type', 'sales-report-email-pro' ),
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'class'     => 'email_interval',
				'options'	=> $interval,
				'default'	=> isset($report_data->email_interval) ? $report_data->email_interval : 'daily'
			),
			'email_select_week' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Day of Week', 'sales-report-email-pro' ),
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'options'   => $week,
				'class'     => 'half email_select_week ' . ( isset($report_data->email_interval) && 'weekly' != $report_data->email_interval ? 'hide' : '' ),
				'default'	=> isset($report_data->email_select_week) ? $report_data->email_select_week : ''
			),
			'email_select_month' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Day of Month', 'sales-report-email-pro' ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'options'   => $month,
				'class'     => 'half email_select_month ' . ( isset($report_data->email_interval) && 'monthly' != $report_data->email_interval ? 'hide' : '' ),
				'tooltip'     => esc_html__( 'the day on the month to send the report email.', 'sales-report-email-pro' ),
				'default'	=> isset($report_data->email_select_month) ? $report_data->email_select_month : ''
			),
			'day_hour_start' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'from', 'sales-report-email-pro' ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'class'     => 'half day_hour_start ' . ( isset($report_data->email_interval) && 'daily-overnight' != $report_data->email_interval ? 'hide' : '' ),
				'options'   => $send_time_array,
				'default'	=> isset($report_data->day_hour_start) ? $report_data->day_hour_start : '06:00'
			),
			'day_hour_end' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'to', 'sales-report-email-pro' ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'class'     => 'half day_hour_end ' . ( isset($report_data->email_interval) && 'daily-overnight' != $report_data->email_interval ? 'hide' : '' ),
				'options'   => $send_time_array,
				'default'	=> isset($report_data->day_hour_end) ? $report_data->day_hour_end : '06:00'
			),
			'daterange' => array(
				'parent'=> 'report_settings',
				'type'		=> 'daterange',
				'title'		=> esc_html__( 'Select a date range', 'sales-report-email-pro' ),				
				'show'		=> true,
				'refresh'	=> true,
				'sendButton'	=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'class'     => 'daterange ' . ( isset($report_data->email_interval) && 'one-time' != $report_data->email_interval ? 'hide' : '' ),
				'desc'     => esc_html__( 'After select date range you can send report manually by send test email button from bottom of side panel.', 'sales-report-email-pro' ),
				'default'	=> isset($report_data->daterange) ? unserialize($report_data->daterange) : array()
			),
			'email_send_time' => array(
				'parent'=> 'report_settings',
				'type'		=> 'select',
				'title'		=> esc_html__( 'Send Report At', 'sales-report-email-pro' ),				
				'show'		=> true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'class'	=> 'half email_send_time ' . ( isset($report_data->email_interval) && 'one-time' == $report_data->email_interval ? 'hide' : '' ),
				'options'   => $send_time_array,
				'default'	=> isset($report_data->email_send_time) ? $report_data->email_send_time : '08:00'
			),
			'email_recipients' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Email Recipients', 'sales-report-email-pro' ),
				'desc'  => esc_html__( 'add comma-separated email addresses', 'sales-report-email-pro' ),
				'type'     => 'tags-input',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'default'	=> isset($report_data->email_recipients) ? $report_data->email_recipients : get_option('admin_email')
			),
			'email_subject' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Email Subject', 'sales-report-email-pro' ),
				'desc'  => esc_html__( 'Available placeholder: {site_title} ', 'sales-report-email-pro' ),
				'type'     => 'text',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'refresh'	=> true,
				'placeholder' => esc_html__( 'Sales Report for {site_title}', 'sales-report-email-pro' ),
				'default'	=> isset($report_data->email_subject) ? $report_data->email_subject : '',
			),
			'email_content' => array(
				'parent'=> 'report_settings',
				'title'    => esc_html__( 'Additional content', 'sales-report-email-pro' ),
				'type'     => 'textarea',
				'show'     => true,
				'database_column' => 'report_options',
				'option_type' => 'array',
				'class'	=> 'additional_content',
				'placeholder'	=> '',
				'refresh'	=> true,
				'default'	=> isset($report_data->email_content) ? $report_data->email_content : '',
			),

			//email design
			'show_header_image' => array(
				'parent'=> 'report_design',
				'title'    => esc_html__( 'Display Header Image', 'sales-report-email-pro' ),
				'type'     => 'tgl-btn',
				'show'     => true,
				'database_column' => 'design_settings',
				'option_type' => 'array',
				'refresh'	=> true,
				'default'	=> isset($report_data->show_header_image) ? $report_data->show_header_image : 1
			),
			'branding_logo' => array(
				'parent'=> 'report_design',
				'title'    => esc_html__( 'Change header image', 'sales-report-email-pro' ),
				'type'     => 'media',
				'show'     => true,
				'refresh'	=> true,
				'database_column' => 'design_settings',
				'option_type' => 'array',
				'desc'     => esc_html__( 'image size requirements: 200px/40px.', 'sales-report-email-pro' ),
				'default'	=> isset($report_data->branding_logo) ? $report_data->branding_logo : '',
				'class' => 0 == $report_data->show_header_image ? 'branding_header_logo_hide header_logo' : 'branding_header_logo header_logo'
			),
			'display_edit_report_link' => array(
				'parent'=> 'report_design',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Hide Edit Report Link', 'sales-report-email-pro' ),	
				'show'		=> true,
				'database_column' => 'design_settings',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Enable this option to remove zorem branding in this email.', 'sales-report-email-pro' ),
				'default'	=> isset($display_data->display_edit_report_link) ? $display_data->display_edit_report_link : 0
			),
			'display_zorem_branding' => array(
				'parent'=> 'report_design',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Hide Powered by zorem Link', 'sales-report-email-pro' ),	
				'show'		=> true,
				'database_column' => 'design_settings',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Enable this option to remove zorem branding in this email.', 'sales-report-email-pro' ),
				'default'	=> isset($display_data->display_zorem_branding) ? $display_data->display_zorem_branding : 0
			),

		);

		$report_totals = array(
			//report totals
			'display_previous_period' => array(
				'parent'=> 'report_totals',
				'type'		=> 'checkbox',
				'title'    => esc_html__( 'Compare to the previous period', 'sales-report-email-pro' ),	
				'show'		=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Compare the report totals to previous period.', 'sales-report-email-pro' ),
				'default'	=> isset($display_data->display_previous_period) ? $display_data->display_previous_period : 1
			),
			'display_net_sales_this_month' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Net Sales This Month', 'sales-report-email-pro' ),				
				'show'		=> isset($report_data->email_interval) && 'monthly' == $report_data->email_interval || 'weekly' == $report_data->email_interval ? false : true,
				'refresh'	=> true,
				'sorting'	=> true,
				'previewType' => true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				// 'class'	=> 'display_net_sales_this_month ' . ( isset($report_data->email_interval) && 'one-time' == $report_data->email_interval ? 'hide' : '' ),
				'tooltip'     => esc_html__( 'sum of all orders not including shipping & taxes with refunds taken off.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_net_sales_this_month) ? $display_data->display_net_sales_this_month : 1
			),
			'display_gross_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Gross Sales', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'sum of all orders not including shipping & taxes with refunds taken off.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_gross_sales) ? $display_data->display_gross_sales : 1
			),
			'display_total_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Total Sales', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'sum of all orders including shipping & taxes with refunds taken off.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_sales) ? $display_data->display_total_sales : 1
			),
			'display_coupon_used' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Net Discount Amount', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total discounts with coupons.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_coupon_used) ? $display_data->display_coupon_used : 1
			),
			'display_coupon_count' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Discounted Orders', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total discounts orders with coupons.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_coupon_count) ? $display_data->display_coupon_count : ''
			),
			'display_total_refunds' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Refunds', 'woocommerce' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total Refunds during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_refunds) ? $display_data->display_total_refunds : 1
			),
			'display_total_refunds_number' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Refund number', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total Refunds during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_refunds_number) ? $display_data->display_total_refunds_number : 1
			),
			'display_total_tax' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Taxes', 'woocommerce' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total tax(Order Tax + Shipping Tax) charges during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_tax) ? $display_data->display_total_tax : ''
			),
			'display_total_shipping' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Shipping', 'woocommerce' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total shipping charges during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_shipping) ? $display_data->display_total_shipping : 1
			),
			'display_total_shipping_tax' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Shipping Tax', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total shipping tax charges during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_shipping_tax) ? $display_data->display_total_shipping_tax : ''
			),
			'display_net_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Net Sales', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'sum of all orders, with refunds, shipping & taxes taken off.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_net_revenue) ? $display_data->display_net_revenue : 1
			),
			'display_total_orders' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Orders', 'woocommerce' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total count of orders in status Processing/Complete.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_orders) ? $display_data->display_total_orders : 1
			),
			'display_total_items' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Items Sold', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total items sold during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_items) ? $display_data->display_total_items : 1
			),
			'display_signups' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'New Customers', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of new signups during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_signups) ? $display_data->display_signups : ''
			),
			'display_downloads' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Downloads', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total count of downloaded files during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_downloads) ? $display_data->display_downloads : ''
			),
			'display_average_order_value' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'AVG. Order Value', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Average Order Value during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_average_order_value) ? $display_data->display_average_order_value : ''
			),
			'display_average_daily_sales' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'AVG. Daily Sales', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Average Daily Sales during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_average_daily_sales) ? $display_data->display_average_daily_sales : ''
			),
			'display_average_daily_items' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'AVG. Order Items', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Average Items per order during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_average_daily_items) ? $display_data->display_average_daily_items : ''
			),
			'display_active_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Active Subscriptions', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of active subscriptions during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_active_subscriptions) ? $display_data->display_active_subscriptions : ''
			),
			'display_signup_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscriptions signups', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of subscriptions signups during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_signup_subscriptions) ? $display_data->display_signup_subscriptions : ''
			),
			'display_signup_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Signup Revenue', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',	
				'refresh'	=> true,			
				'tooltip'     => esc_html__( 'Total signup revenue during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_signup_revenue) ? $display_data->display_signup_revenue : ''
			),
			'display_renewal_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscription Renewal', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,				
				'tooltip'     => esc_html__( 'Total number of subscriptions renewal during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_renewal_subscriptions) ? $display_data->display_renewal_subscriptions : ''
			),
			'display_renewal_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Renewal Revenue', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total renewal revenue during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_renewal_revenue) ? $display_data->display_renewal_revenue : ''
			),
			'display_switch_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscription Switch', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of subscriptions switch during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_switch_subscriptions) ? $display_data->display_switch_subscriptions : ''
			),
			'display_switch_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Switch Revenue', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total switch revenue during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_switch_revenue) ? $display_data->display_switch_revenue : ''
			),
			'display_resubscribe_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscription Resubscribe', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of subscriptions resubscribe during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_resubscribe_subscriptions) ? $display_data->display_resubscribe_subscriptions : ''
			),
			'display_resubscribe_revenue' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Resubscribe Revenue', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total resubscribe revenue during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_resubscribe_revenue) ? $display_data->display_resubscribe_revenue : ''
			),
			'display_cancellation_subscriptions' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscription Cancellations', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'sorting'	=> true,
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'Total number of subscriptions cancellations during the report period.', 'sales-report-email-pro' ),
				'default'	=> isset($display_data->display_cancellation_subscriptions) ? $display_data->display_cancellation_subscriptions : ''
			),
			'display_net_subscription_gain' => array(
				'parent'=> 'report_totals',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Net Subscription Gain', 'woocommerce-subscriptions' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_totals',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'The net subscription gain during the report period.', 'sales-report-email-pro' ),
				'default'	=> isset($display_data->display_net_subscription_gain) ? $display_data->display_net_subscription_gain : ''
			),
		);

		$sort_report_totals = isset($report_data->report_totals_sort) ? unserialize($report_data->report_totals_sort) : array() ;
		
		if ( !empty($sort_report_totals)  ) {
			
			//new option add
			foreach ( $report_totals as $key => $value ) {
				if ( !in_array( $key, $sort_report_totals) ) {
					$sort_report_totals[] = $key;
				}
			}
			foreach ( $sort_report_totals as $key ) {
				$new_report_totals['display_previous_period'] = $report_totals['display_previous_period'];
				//$new_report_totals['display_net_sales_this_month'] = $report_totals['display_net_sales_this_month'];
				$new_report_totals[$key] = $report_totals[$key];
			}
			$report_totals = $new_report_totals;
		}

		$report_details = array(
			//report details
			'display_top_sellers' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Top Selling Products', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_top_sellers_row) ? $display_data->display_top_sellers_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'product name, quantity, amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_top_sellers) ? $display_data->display_top_sellers : 1
			),
			'display_top_variations' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Top Selling Variations', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_top_variations_row) ? $display_data->display_top_variations_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'product name, quantity, amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_top_variations) ? $display_data->display_top_variations : ''
			),
		   'display_top_categories' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Top Selling Categories', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_top_categories_row) ? $display_data->display_top_categories_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'category name, quantity, amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_top_categories) ? $display_data->display_top_categories : 1
			),
			'display_sales_by_coupons' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Coupons', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_coupons_row) ? $display_data->display_sales_by_coupons_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'coupon, quantity used and total discount amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_coupons) ? $display_data->display_sales_by_coupons : ''
			),
			'display_sales_by_billing_city' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Billing City', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_billing_city_row) ? $display_data->display_sales_by_billing_city_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'City, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_billing_city) ? $display_data->display_sales_by_billing_city : ''
			),
			'display_sales_by_shipping_city' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Shipping City', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_shipping_city_row) ? $display_data->display_sales_by_shipping_city_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'City, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_shipping_city) ? $display_data->display_sales_by_shipping_city : ''
			),
			'display_sales_by_billing_state' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Billing State', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_billing_state_row) ? $display_data->display_sales_by_billing_state_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'State, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_billing_state) ? $display_data->display_sales_by_billing_state : ''
			),
			'display_sales_by_shipping_state' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Shipping State', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_shipping_state_row) ? $display_data->display_sales_by_shipping_state_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'State, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_shipping_state) ? $display_data->display_sales_by_shipping_state : ''
			),
			'display_sales_by_billing_country' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Billing Country', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_billing_country_row) ? $display_data->display_sales_by_billing_country_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'country, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_billing_country) ? $display_data->display_sales_by_billing_country : ''
			),
			'display_sales_by_shipping_country' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Sales By Shipping Country', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'breakdown' => array(
					'default' 	=> isset($display_data->display_sales_by_shipping_country_row) ? $display_data->display_sales_by_shipping_country_row : 5,
					'option'	=> array( '5'=>'5','10'=>'10', '20'=>'20', '100'=>'All' ),
				),
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'country, orders count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_sales_by_shipping_country) ? $display_data->display_sales_by_shipping_country : ''
			),
			'display_order_status' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Orders By Status', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'orders Status, order count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_order_status) ? $display_data->display_order_status : ''
			),
			'display_order_details' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Report By Order details', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'orders id, customer name, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_order_details) ? $display_data->display_order_details : ''
			),
			'display_Refund_order_details' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Report By Refund details', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'orders id, customer name, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_Refund_order_details) ? $display_data->display_Refund_order_details : ''
			),
			'display_payment_method' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Orders By Payment Method', 'sales-report-email-pro' ),				
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'payment method, order count, total amount during the report period.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_payment_method) ? $display_data->display_payment_method : ''
			),
			'display_total_subscriber' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Subscriptions By Status (Total)', 'sales-report-email-pro' ),				
				'show'		=> $WCS,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( 'New, Cancelled, Pending Cancellations, etc.', 'sales-report-email-pro'),
				'default'	=> isset($display_data->display_total_subscriber) ? $display_data->display_total_subscriber : ''
			),
			'downloads_products_data_table' => array(
				'parent'=> 'report_details',
				'type'		=> 'tgl-btn',
				'title'		=> esc_html__( 'Downloads details', 'sales-report-email-pro' ),
				'show'		=> true,
				'sorting'	=> true,
				'database_column' => 'report_details',
				'column_name'	=> 'display_data',
				'option_type' => 'array',
				'refresh'	=> true,
				'tooltip'     => esc_html__( '', 'sales-report-email-pro'),
				'default'	=> isset($display_data->downloads_products_data_table) ? $display_data->downloads_products_data_table : ''
			),
		);

		$sort_report_details = isset($report_data->report_details_sort) ? unserialize($report_data->report_details_sort) : array() ;
		if ( !empty($sort_report_details)  ) {
			
			//new option add
			foreach ( $report_details as $key => $value ) {
				if ( !in_array( $key, $sort_report_details) ) {
					$sort_report_details[] = $key;
				}
			}
			
			foreach ( $sort_report_details as $key ) {
				$new_report_details[$key] = $report_details[$key];
			}
			$report_details = $new_report_details;
		}
				
		$settings = array_merge( $settings, $report_totals, $report_details );

		//trackship addon option
		$settings = apply_filters( 'sre_addon_trackship_option', $settings, $report_data ); 

		return $settings;
	}

	public function sre_customizer_preview_content( $preview ) {

		$content = asre_pro()->admin->email_content( $preview );

		return $content;
	}

	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		return sanitize_email( $from_address );
	}
	
	/**
	 * Get the email order status
	 *
	 * @param string $email_template the template string name.
	 */
	public function get_email_order_status( $email_template ) {
		
		$order_status = apply_filters( 'customizer_email_type_order_status_array', self::$email_types_order_status );
		
		$order_status = self::$email_types_order_status;
		
		if ( isset( $order_status[ $email_template ] ) ) {
			return $order_status[ $email_template ];
		} else {
			return 'processing';
		}
	}

	/**
	 * Get the email class name
	 *
	 * @param string $email_template the email template slug.
	 */
	public function get_email_class_name( $email_template ) {
		
		$class_names = apply_filters( 'customizer_email_type_class_name_array', self::$email_types_class_names );

		$class_names = self::$email_types_class_names;
		if ( isset( $class_names[ $email_template ] ) ) {
			return $class_names[ $email_template ];
		} else {
			return false;
		}
	}


	public function allowed_css_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	
	public function safe_style_css( $styles ) {
		 $styles[] = 'display';
		return $styles;
	}

}
