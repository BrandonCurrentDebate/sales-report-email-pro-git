<?php
/**
 * Sales report email
 *
 * Class ASRE_Admin_Pro
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ASRE_Admin_Pro { 

	public $table;
	public $screen_id;
	public $data;
	public $email_data;
	public $interval;
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return ASRE_Admin_Pro
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	
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
		global $wpdb;
		$this->table = $wpdb->prefix . 'asre_sales_report';
		$this->screen_id = 'woocommerce-advanced-sales-report-email';
		
		//callback for admin menu register		
		add_action('admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );

		//load javascript in admin
		add_action('admin_enqueue_scripts', array( $this, 'wc_esrc_enqueue' ) );
		
		// Handle the enable/disable/delete actions.
		add_action( 'admin_init', array( $this, 'data_toggle_callback' ) );
		
		// enable toggle in report list hook
		add_action( 'wp_ajax_enable_toggle_data_update', array( $this, 'update_enable_toggle_callback' ) );
		
		// Hook for add admin body class in settings page
		add_filter( 'admin_body_class', array( $this, 'asre_post_admin_body_class' ), 100, 1 );
		
		// Cron hook
		//add_action( 'wc_asre_send', array( $this, 'cron_email_callback' ) );
		
		//callback add_action for email content branding logo html
		add_filter( 'asre_branding_logo_url', array( $this, 'change_asre_branding_logo' ), 10, 2 );
		
		// cron run date update in report setting hook
		add_action( 'wp_ajax_cron_run_date_update', array( $this, 'update_cron_run_date_callback' ) );
		
	}

	/*
	* add unique body class
	*/
	public function asre_post_admin_body_class( $body_class ) {
		
		if (!isset($_GET['page'])) {
			return $body_class;
		}
		if ( 'woocommerce-advanced-sales-report-email' == $_GET['page'] ) {
			$body_class .= ' asre-sales-report-email-setting ';
		}

		return $body_class;
	}
	
	/*
	* Admin Menu add function
	* WC sub menu
	*/
	public function register_woocommerce_menu() {
		add_submenu_page( 'woocommerce', 'Sales Report Email', 'Sales Report Email', 'manage_options', 'woocommerce-advanced-sales-report-email', array( $this, 'woocommerce_sales_report_page_callback' ) ); 
	}
	
	/*
	* callback for Sales Report Email page
	*/
	public function woocommerce_sales_report_page_callback() {	
		
		global $wpdb;

		// Check the user capabilities
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html( 'You do not have sufficient permissions to access this page.', 'woocommerce-cart-notices' ) );
		}

		$tab = isset( $_GET['tab'] ) ? sanitize_text_field($_GET['tab']) : 'list';

		if ( 'list' === $tab ) {
			
			$data = $this->get_data();

		}
		
		?>
			<div class="zorem-layout__header">
			<h1 class="tab_section_heading">
					<?php
					if ( 'license' === $tab ) {
						$tab_heading = esc_html( 'License', 'advanced-local-pickup-pro');
					} else {
						$tab_heading = esc_html( 'Report Emails', 'advanced-local-pickup-pro');
					}
					?>
					<a href="<?php echo esc_url(admin_url() . 'admin.php?page=woocommerce-advanced-sales-report-email'); ?>" class="link decoration"><?php esc_html_e( 'Sales Report Email', 'advanced-local-pickup-pro'); ?></a> > <?php esc_html_e( $tab_heading, 'advanced-local-pickup-pro'); ?>
				</h1>
				<div class="woocommerce-layout__activity-panel">
					<div class="woocommerce-layout__activity-panel-tabs">
						<button type="button" id="activity-panel-tab-help" class="components-button woocommerce-layout__activity-panel-tab">
							<span class="dashicons dashicons-editor-help"></span>
							Help 
						</button>
					</div>
					<div class="woocommerce-layout__activity-panel-wrapper">
						<div class="woocommerce-layout__activity-panel-content" id="activity-panel-true">
							<div class="woocommerce-layout__activity-panel-header">
								<div class="woocommerce-layout__inbox-title">
									<p class="css-activity-panel-Text">Documentation</p>            
								</div>								
							</div>
							<div>
								<ul class="woocommerce-list woocommerce-quick-links__list">
									<li class="woocommerce-list__item has-action">
										<?php
										$support_link = 'https://www.zorem.com/?support=1' ;
										?>
										<a href="<?php echo esc_url( $support_link ); ?>" class="woocommerce-list__item-inner" target="_blank" >
											<div class="woocommerce-list__item-before">
												<img src="<?php echo esc_url(asre_pro()->plugin_dir_url(__FILE__) . 'assets/images/get-support-icon.svg'); ?>">	
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Get Support</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>            
									<li class="woocommerce-list__item has-action">
										<a href="https://docs.zorem.com/docs/sales-report-email-pro/" class="woocommerce-list__item-inner" target="_blank">
											<div class="woocommerce-list__item-before">
												<img src="<?php echo esc_url(asre_pro()->plugin_dir_url(__FILE__) . 'assets/images/documentation-icon.svg'); ?>">
											</div>
											<div class="woocommerce-list__item-text">
												<span class="woocommerce-list__item-title">
													<div class="woocommerce-list-Text">Documentation</div>
												</span>
											</div>
											<div class="woocommerce-list__item-after">
												<span class="dashicons dashicons-arrow-right-alt2"></span>
											</div>
										</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>	
			</div>		
			<div class="woocommerce asre_admin_layout">
			<div class="asre_admin_content">
				<input id="asre_tab1" type="radio" name="tabs" class="asre_tab_input" data-tab="list" checked>
				<a for="asre_tab1" href="admin.php?page=<?php echo esc_html($this->screen_id); ?>&amp;tab=list" <?php echo 'edit' == $tab ? 'style="display:none;"' : ''; ?> class="asre_tab_label first_label <?php echo ( 'list' === $tab ) ? 'nav-tab-active' : ''; ?>"><?php esc_html_e('Report Emails', 'woocommerce'); ?></a>
				<div class="menu_devider"></div>
				<?php
				if (  'list' == $tab ) { 
					require_once( 'views/asre_reports_tab.php' ); 
				}
				?>
				</div>
			</div>
	<?php		
	}

	/*
	* Add admin javascript
	*/	
	public function wc_esrc_enqueue() {
		
		
		// Add condition for css & js include for admin page  
		if (!isset($_GET['page'])) {
				return;
		}
		if ( 'woocommerce-advanced-sales-report-email' != $_GET['page'] ) {
			return;
		}
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';	
		
		// Add the WP Media 
		wp_enqueue_media();
		
		// Add tiptip js and css file
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_styles' );
		
		wp_register_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
		wp_enqueue_script( 'jquery-tiptip' );
		wp_enqueue_script( 'jquery-blockui' );
		
		wp_enqueue_style( 'asrc-admin-css', asre_pro()->plugin_dir_url() . '/assets/css/admin.css', array(), asre_pro()->version );
		wp_enqueue_script( 'asrc-admin-js', asre_pro()->plugin_dir_url() . '/assets/js/admin.js', array('jquery','wp-color-picker'), asre_pro()->version );
		
		wp_localize_script( 'asrc-admin-js', 'asrc_object', 
			array( 
				'admin_url' => admin_url(),
				'nonce' => wp_create_nonce('asre-ajax-nonce')
			) 
		);
		
	}
	
	/*
	* get all data 
	*/
	public function get_data() {
		global $wpdb;

		// Avoid database table not found errors when plugin is first installed
		// by checking if the plugin option exists
		if ( empty( $this->data ) ) {
			$this->data = array();

			$wpdb->hide_errors();
			
			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %1s ORDER BY id DESC', $this->table ) ); //ORDER BY name ASC
			
			if ( ! empty( $results ) ) {
				
				foreach ( $results as $key => $result ) {
					$results[ $key ]->email_enable = maybe_unserialize( $results[ $key ]->email_enable );
					$results[ $key ]->report_name = maybe_unserialize( $results[ $key ]->report_name );
					$results[ $key ]->email_interval = maybe_unserialize( $results[ $key ]->email_interval );
					$results[ $key ]->email_recipients = maybe_unserialize( $results[ $key ]->email_recipients );
				}

				$this->data = $results;
			}
		}
		return $this->data;
	}
	
	
	/*
	* get data by id
	*/
	public function get_data_byid( $id ) {
		global $wpdb;
		$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %1s WHERE id = %d', $this->table, $id ) );
		if ( ! empty( $results ) ) {
			$results->email_enable = maybe_unserialize( $results->email_enable );
			$results->report_name = maybe_unserialize( $results->report_name );
			$results->email_interval = maybe_unserialize( $results->email_interval );
			$results->email_recipients = maybe_unserialize( $results->email_recipients );
		}
		return $results;
	}
	
	/*
	* get next cron run date in report list column
	*/	
	public function next_run_date( $data ) {

		$hrtime = $data->email_send_time;

		$week = array(
			esc_html( 'Sunday', 'woocommerce' ),
			esc_html( 'Monday', 'woocommerce' ),
			esc_html( 'Tuesday', 'woocommerce' ),
			esc_html( 'Wednesday', 'woocommerce' ),
			esc_html( 'Thursday', 'woocommerce' ),
			esc_html( 'Friday', 'woocommerce' ),
			esc_html( 'Saturday', 'woocommerce' ),
		);
		$run = $data->email_interval;
		
		if ( 'one-time' == $run ) {
			return esc_html( 'Manual', 'advanced-local-pickup-pro');
		}
		
		if ( 'monthly' == $run || 'last-30-days' == $run ) {
			$select_day_for_month = $data->email_select_month;
			if ( gmdate('j') == $select_day_for_month && current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				return gmdate('Y-m-d ' . $hrtime);
			} else {
				$next15th = mktime( 0, 0, 0, gmdate( 'n' ) + ( gmdate( 'j' ) >= (int) $select_day_for_month ), (int) $select_day_for_month );
				return gmdate('Y-m-d ' . $hrtime, $next15th);
			}
		}
		
		if ( 'weekly' == $run ) {
			$select_day_for_week = !empty($data->email_select_week) ? $data->email_select_week : 0;
			if ( gmdate('w') == $select_day_for_week && current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				return gmdate('Y-m-d ' . $hrtime);
			} else {
				return gmdate('Y-m-d ' . $hrtime, strtotime('next ' . $week[$select_day_for_week]));
			}
		}
		
		if ( 'daily' == $run || 'daily-overnight' == $run ) {
			if ( current_time( 'timestamp' ) < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				//today
				$datetime = new DateTime();
				return $datetime->format('Y-m-d ' . $hrtime);
			} else {
				$datetime = new DateTime('tomorrow');
				return $datetime->format('Y-m-d ' . $hrtime);
			}
		}

	}
	
	/*
	* update cron run date callback
	*/	
	public function update_cron_run_date_callback() {
		
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( ! wp_verify_nonce( $nonce, 'asre-ajax-nonce' ) ) {
			die();
		}
		
		$TIME = isset( $_POST['TIME'] ) ? sanitize_text_field($_POST['TIME']) : '';
		$hrtime = $TIME;

		$week = array(
			esc_html( 'Sunday', 'woocommerce' ),
			esc_html( 'Monday', 'woocommerce' ),
			esc_html( 'Tuesday', 'woocommerce' ),
			esc_html( 'Wednesday', 'woocommerce' ),
			esc_html( 'Thursday', 'woocommerce' ),
			esc_html( 'Friday', 'woocommerce' ),
			esc_html( 'Saturday', 'woocommerce' ),
		);

		$INTERVAL = isset( $_POST['INTERVAL'] ) ? sanitize_text_field($_POST['INTERVAL']) : '';
		$MONTH = isset( $_POST['MONTH'] ) ? sanitize_text_field($_POST['MONTH']) : '';
		$WEEK = isset( $_POST['WEEK'] ) ? sanitize_text_field($_POST['WEEK']) : '';
		$run = $INTERVAL;

		if ( 'monthly' == $run ) {
			$select_day_for_month = $MONTH;
			if ( gmdate('j') == $select_day_for_month && current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime);
			} else {
				$next15th = mktime( 0, 0, 0, gmdate( 'n' ) + ( gmdate( 'j' ) >= $select_day_for_month ), $select_day_for_month ); 
				$NextRunDate = gmdate('Y-m-d ' . $hrtime, $next15th);
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}

		if ( 'weekly' == $run || 'last-30-days' == $run ) {
			$select_day_for_week = $WEEK;
			if ( gmdate('w') == $select_day_for_week && current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime);
			} else {
				$NextRunDate = gmdate('Y-m-d ' . $hrtime, strtotime('next ' . $week[$select_day_for_week]));
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}

		if ( 'daily' == $run || 'daily-overnight' == $run ) {
			if ( current_time('timestamp') < strtotime(gmdate('Y-m-d ' . $hrtime)) ) {
				//today
				$datetime = new DateTime();
				$NextRunDate = $datetime->format('Y-m-d ' . $hrtime);
			} else {
				$datetime = new DateTime('tomorrow');
				$NextRunDate = $datetime->format('Y-m-d ' . $hrtime);
			}
			$newDate = gmdate('M d, Y g:iA', strtotime($NextRunDate));
		}
		
		

		$array = array(
			'NextRunDate'	=> isset($newDate) ? $newDate : '',
			'interval' => $INTERVAL,
			'sendTime' => $TIME,
			'week' => $WEEK,
			'month' => $MONTH,
		);

		echo json_encode($array);
		die();
	}
	
	/**
	 * Handle the enable/disable/delete actions.
	 *
	 * @since 1.0
	*/
	public function data_toggle_callback() {
		global $wpdb;

		// If on the WC Email reports screen & the current user can manage WooCommerce, continue.
		if ( isset( $_GET['page'] ) && $this->screen_id === $_GET['page'] && current_user_can( 'manage_woocommerce' ) ) {

			$action = isset( $_GET['action'] ) ? sanitize_text_field($_GET['action']) : false;

			// If no action or cart notice ID are set, bail.
			if ( ! $action || ! isset( $_GET['id'] ) ) {
				return;
			}

			$id = (int) $_GET['id'];

			if ( 'enable' === $action ) {

				$wpdb->query( $wpdb->prepare( 'UPDATE %1s SET email_enable=true WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'enabled' ), 'admin.php' ) ) );
				exit;

			} elseif ( 'disable' === $action ) {

				$wpdb->query( $wpdb->prepare( 'UPDATE %1s SET email_enable=false WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'disabled' ), 'admin.php' ) ) );
				exit;

			} elseif ( 'delete' === $action ) {

				$wpdb->query( $wpdb->prepare( 'DELETE FROM %1s WHERE id = %d', $this->table, $id ) );

				wp_redirect( esc_url_raw( add_query_arg( array( 'page' => $this->screen_id, 'result' => 'deleted' ), 'admin.php' ) ) );
				
				asre_pro()->cron->remove_cron($id);
				
				exit;
			}
		}
	}
	
	/*
	* update report enable toggle of existing entry
	*/	
	public function update_enable_toggle_callback() {
		
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field($_POST['nonce']) : '';
		if ( ! wp_verify_nonce( $nonce, 'asre-ajax-nonce' ) ) {
			die();
		}
		
		global $wpdb;
		$id = isset( $_POST['ID'] ) ? sanitize_text_field($_POST['ID']) : '';
				
		if ( isset($_POST['check']) && 'true' == $_POST['check'] ) {
			$check = 1;	
		} else {
			$check = 0;		
		}

		$array = array();
		$data = array(
			'email_enable' => $check,
		);
		$where = array(
			'id' => $id,
		);

		$result = $wpdb->update( $this->table, $data, $where );

		$array = array(
			'status' => 'success',
			'id' => $id,
		);

		echo json_encode($data);
		die();
	}
	
	/*
	* get email content data for sales report
	*/
	public function email_content( $id ) {
		
		if ( '' == $id ) {
			return;
		}
		
		if ( isset($_GET['action']) && 'sre_customizer_email_preview' == $_GET['action'] && 0 != $id ) {
			$data = $this->get_data_byid( $id );
			$display_data = unserialize($data->display_data);
			foreach ($_GET as $key => $value) {
				$data->$key = isset($_GET[$key]) ? sanitize_text_field($_GET[$key]) : $data->$key;
				$display_data->$key = isset($_GET[$key]) ? sanitize_text_field($_GET[$key]) : $display_data->$key;
			}
			$data->display_data = serialize($display_data);
		} elseif ( 0 == $id ) {
			$data = (object) array (
				'email_enable' => '1',
				'report_name' => 'Sales Report',
				'email_interval' => 'daily',
				'email_send_time' => '08:00',
				'email_recipients' => 'kuldip@zorem.com',
				'display_data' => serialize(
					(object) array(
					'display_gross_sales' => '1',
					'display_total_refunds_number' => '1',
					'display_total_sales' => '1',
					'display_coupon_used' => '1',
					'display_total_refunds' => '1',
					'display_total_shipping' => '1',
					'display_net_revenue' => '1',
					'display_total_orders' => '1',
					'display_total_items' => '1',
					'display_top_sellers' => '1',
					'display_top_categories' => '1',
					)
				),
			);
			if ( isset($_GET['action']) && 'sre_customizer_email_preview' == $_GET['action'] ) {
				$display_data = unserialize($data->display_data);
				foreach ($_GET as $key => $value) {
					$data->$key = isset($_GET[$key]) ? sanitize_text_field($_GET[$key]) : $data->$key;
					$display_data->$key = isset($_GET[$key]) ? sanitize_text_field($_GET[$key]) : $display_data->$key;
				}
				$data->display_data = serialize($display_data);
			}
		} else {
			$data = $this->get_data_byid( $id );
		}

		$this->set_email_data( $data );

		$display_data = unserialize($data->display_data);

		$interval = isset($data->email_interval) ? $data->email_interval : 'daily';

		$date_range = $this->get_current_date_range( $interval );
		$previous_date_range = $this->get_previous_date_range( $interval );		
		
		$data_array = array(
			'id' => $id,		
			'interval'  => $interval,
			'data' => $data,
			'display_data' => $display_data,
			'date_range' => $date_range,
			'previous_date_range' => $previous_date_range,
		);
		
		if ( 'daily' == $interval || 'daily-overnight' == $interval ) {
			//Net salses for this month
			$data_array['net_salse_this_month'] = asre_pro()->functions->get_net_salse_this_month_reports( $date_range, $interval );
			// $data_array['net_salse_this_month_sales'] = asre_pro()->functions->get_net_salse_last_month_reports( $date_range, $interval );
			// $data_array['net_salse_this_month_growth'] = round((int) $this->get_growth_count( $data_array['net_salse_this_month_sales'], $data_array['net_salse_this_month'] ));
		}

		if ( isset($display_data->display_gross_sales) && '1' == $display_data->display_gross_sales ) {
			//gross salses
			$data_array['gross_sales'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->gross_sales;
			$data_array['previous_gross_sales'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->gross_sales;
			$data_array['gross_growth'] = round((int) $this->get_growth_count( $data_array['previous_gross_sales'], $data_array['gross_sales'] ));
		}
		
		if ( isset($display_data->display_total_sales) && '1' == $display_data->display_total_sales ) {
			//total salses
			$data_array['total_sales'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->total_sales;
			$data_array['previous_total_sales'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->total_sales;
			$data_array['sales_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_sales'], $data_array['total_sales'] ));
		}
		
		if ( isset($display_data->display_coupon_used) && '1' == $display_data->display_coupon_used ) {
			//total coupons
			$data_array['coupon_used'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->coupons;
			$data_array['previous_coupon_used'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->coupons;
			$data_array['coupon_growth'] = round((int) $this->get_growth_count( $data_array['previous_coupon_used'], $data_array['coupon_used'] ));
		}
		
		if ( isset($display_data->display_coupon_count) && '1' == $display_data->display_coupon_count ) {
			//total coupons count
			$data_array['coupon_count'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->coupons_count;
			$data_array['previous_coupon_count'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->coupons_count;
			$data_array['coupon_count_growth'] = round((int) $this->get_growth_count( $data_array['previous_coupon_count'], $data_array['coupon_count'] ));
		}
		
		if ( isset($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ) {
			//total refunds
			$data_array['total_refunds'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->refunds;
			$data_array['previous_total_refunds'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->refunds;
			$data_array['refund_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_refunds'], $data_array['total_refunds'] ));
		}

		if ( isset($display_data->display_total_refunds_number) && '1' == $display_data->display_total_refunds_number ) {
			//total refunds number
			 $data_array['refund_number'] = asre_pro()->functions->get_total_refunds_number( $date_range );
			 $data_array['previous_refund_number'] = asre_pro()->functions->get_total_refunds_number( $previous_date_range );
			 $data_array['refund_number_growth'] = round( (int) $this->get_growth_count( $data_array['previous_refund_number'], $data_array['refund_number'] ) );
		}
		
		if ( wc_tax_enabled() ) {	
			if ( isset($display_data->display_total_tax) && '1' == $display_data->display_total_tax ) {
				//total taxes
				$data_array['total_taxes'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->taxes;
				$data_array['previous_total_taxes'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->taxes;
				$data_array['taxes_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_taxes'], $data_array['total_taxes'] ));
			}
			
			if ( isset($display_data->display_total_shipping_tax) && '1' == $display_data->display_total_shipping_tax ) {
				//total shipping tax
				$data_array['total_shipping_tax'] = asre_pro()->functions->get_total_shipping_tax( $date_range );
				$data_array['previous_total_shipping_tax'] = asre_pro()->functions->get_total_shipping_tax( $previous_date_range );
				$data_array['shipping_tax_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_shipping_tax'], $data_array['total_shipping_tax'] ));
			}
			
		}
		
		if ( isset($display_data->display_total_shipping) && '1' == $display_data->display_total_shipping ) {
			//total shipping
			$data_array['total_shipping'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->shipping;
			$data_array['previous_total_shipping'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->shipping;
			$data_array['shipping_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_shipping'], $data_array['total_shipping'] ));
		}
		
		if ( isset($display_data->display_net_revenue) && '1' == $display_data->display_net_revenue ) {
			//net revenue
			$data_array['net_revenue'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->net_revenue;
			$data_array['previous_net_revenue'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->net_revenue;
			$data_array['net_revenue_growth'] = round((int) $this->get_growth_count( $data_array['previous_net_revenue'], $data_array['net_revenue'] ));
		}
		
		if ( isset($display_data->display_total_orders) && '1' == $display_data->display_total_orders ) {
			//total orders
			$data_array['total_orders'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->orders_count;
			$data_array['previous_total_orders'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->orders_count;
			$data_array['orders_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_orders'], $data_array['total_orders'] ));
		}
		
		if ( isset($display_data->display_total_items) && '1' == $display_data->display_total_items ) {
			//total items
			$data_array['total_items'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->num_items_sold;
			$data_array['previous_total_items'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->num_items_sold;
			$data_array['items_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_items'], $data_array['total_items'] ));
		}
		
		if ( isset($display_data->display_signups) && '1' == $display_data->display_signups ) {
			//total new customer
			$data_array['total_signups'] = asre_pro()->functions->get_total_new_customer( $date_range );		
			$data_array['previous_total_signups'] = asre_pro()->functions->get_total_new_customer( $previous_date_range );
			$data_array['signup_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_signups'], $data_array['total_signups'] ));
		}
		
		if ( isset($display_data->display_downloads) && '1' == $display_data->display_downloads ) {
			//total downlaods
			$data_array['total_downloads'] = asre_pro()->functions->get_total_downloads( $date_range );		
			$data_array['previous_total_downloads'] = asre_pro()->functions->get_total_downloads( $previous_date_range );
			$data_array['downloads_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_downloads'], $data_array['total_downloads'] ));
		}
		
		if ( isset($display_data->display_average_order_value) && '1' == $display_data->display_average_order_value ) {
			//avg order value
			if ( !isset($display_data->display_total_orders) || '1' != $display_data->display_total_orders ) {
				$data_array['total_orders'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->orders_count;
				$data_array['previous_total_orders'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->orders_count;
			}
			if ( !isset($display_data->display_net_revenue) || '1' != $display_data->display_net_revenue ) {
				$data_array['net_revenue'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->net_revenue;
				$data_array['previous_net_revenue'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->net_revenue;
			}

			$data_array['avg_order_value'] = 0 != $data_array['total_orders'] ?  $data_array['net_revenue'] / $data_array['total_orders'] : 0;
			$data_array['prev_avg_order_value'] = 0 != $data_array['previous_total_orders'] ?  $data_array['previous_net_revenue'] / $data_array['previous_total_orders'] : 0;
			$data_array['avg_order_growth'] = round((int) $this->get_growth_count( $data_array['prev_avg_order_value'], $data_array['avg_order_value'] ));
		}
		
		if ( isset($display_data->display_average_daily_items) && '1' == $display_data->display_average_daily_items ) {
			//avg order items
			if ( !isset($display_data->display_total_orders) || '1' != $display_data->display_total_orders ) {
				$data_array['total_orders'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->orders_count;
				$data_array['previous_total_orders'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->orders_count;
			}
			if ( !isset($display_data->display_total_items) || '1' != $display_data->display_total_items ) {
				$data_array['total_items'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->num_items_sold;
				$data_array['previous_total_items'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->num_items_sold;
			}

			$data_array['avg_order_item'] = 0 != $data_array['total_orders'] ?  $data_array['total_items'] / $data_array['total_orders'] : 0;
			$data_array['prev_avg_order_item'] = 0 != $data_array['previous_total_orders'] ?  $data_array['previous_total_items'] / $data_array['previous_total_orders'] : 0;
			$data_array['avg_item_growth'] = round((int) $this->get_growth_count( $data_array['prev_avg_order_item'], $data_array['avg_order_item'] ));
		}
		
		if ( ( isset($display_data->display_average_daily_sales) && '1' == $display_data->display_average_daily_sales ) && 'daily' != $data->email_interval && 'daily-overnight' != $data->email_interval ) {
			//day count for avg daily salse
			$start_date = strtotime( $date_range->start_date->format( 'Y-m-d H:i:s' ) );
			$end_date = strtotime( $date_range->end_date->format( 'Y-m-d H:i:s' ) );
			$datediff = $end_date - $start_date;
			$day_count = !empty( round( $datediff / ( 60 * 60 * 24 ) ) ) ? round( $datediff / ( 60 * 60 * 24 ) ) : 1;

			if ( !isset($display_data->display_total_sales) || '1' != $display_data->display_total_sales ) {
				$data_array['total_sales'] = asre_pro()->functions->get_total_reports( $date_range, $interval )->total_sales;
				$data_array['previous_total_sales'] = asre_pro()->functions->get_total_reports( $previous_date_range, $interval )->total_sales;
			}

			//avg daily salse
			$data_array['avg_daily_sales'] = $data_array['total_sales'] / $day_count;
			$data_array['prev_avg_daily_sales'] = $data_array['previous_total_sales'] / $day_count;
			$data_array['avg_sales_growth'] = round((int) $this->get_growth_count( $data_array['prev_avg_daily_sales'], $data_array['avg_daily_sales'] ));
		}
		
		if ( isset($display_data->display_top_sellers) && '1' == $display_data->display_top_sellers ) {
			//top seller
			$data_array['top_sellers'] = asre_pro()->functions->get_top_selling_reports( $date_range, $interval, $display_data );
		}
		
		if ( isset($display_data->display_top_variations) && '1' == $display_data->display_top_variations ) {
			//top variations products
			$data_array['top_variations_sellers'] = asre_pro()->functions->get_top_variations_selling_reports( $date_range, $display_data );
		}

		if ( isset($display_data->display_top_categories) && '1' == $display_data->display_top_categories ) {
			//top categories
			$data_array['top_categories'] = asre_pro()->functions->get_top_categories_reports( $date_range, $display_data );
		}

		if ( isset($display_data->downloads_products_data_table) && '1' == $display_data->downloads_products_data_table ) {
			//top categories
			$data_array['products_data_table'] = asre_pro()->functions->get_product_data_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_coupons) && '1' == $display_data->display_sales_by_coupons ) {
			//salse by coupons
			$data_array['salse_by_coupons'] = asre_pro()->functions->get_coupons_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_billing_country) && '1' == $display_data->display_sales_by_billing_country ) {
			//billing country
			$data_array['sales_by_billing_country'] = asre_pro()->functions->get_billing_country_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_shipping_country) && '1' == $display_data->display_sales_by_shipping_country ) {
			//shipping country
			$data_array['sales_by_shipping_country'] = asre_pro()->functions->get_shipping_country_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_billing_state) && '1' == $display_data->display_sales_by_billing_state ) {
			//billing state
			$data_array['sales_by_billing_state'] = asre_pro()->functions->get_billing_state_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_shipping_state) && '1' == $display_data->display_sales_by_shipping_state ) {
			//shipping state
			$data_array['sales_by_shipping_state'] = asre_pro()->functions->get_shipping_state_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_billing_city) && '1' == $display_data->display_sales_by_billing_city ) {
			//billing city
			$data_array['sales_by_billing_city'] = asre_pro()->functions->get_billing_city_reports( $date_range, $display_data );
		}
		
		if ( isset($display_data->display_sales_by_shipping_city) && '1' == $display_data->display_sales_by_shipping_city ) {
			//shipping city
			$data_array['sales_by_shipping_city'] = asre_pro()->functions->get_shipping_city_reports( $date_range, $display_data );
		}

		if ( isset($display_data->display_order_status) && '1' == $display_data->display_order_status ) {
			//order status
			$data_array['sales_by_order_status'] = asre_pro()->functions->get_order_status_reports( $date_range  );
		}

		if ( isset($display_data->display_order_details) && '1' == $display_data->display_order_details ) {
			//order details
			$data_array['reoprt_by_order_details'] = asre_pro()->functions->get_order_details_reports( $date_range  );
		}

		if ( isset($display_data->display_Refund_order_details) && '1' == $display_data->display_Refund_order_details ) {
			//order details
			$data_array['reoprt_by_refund_order_details'] = asre_pro()->functions->get_refund_order_details( $date_range  );
		}
		
		if ( isset($display_data->display_payment_method) && '1' == $display_data->display_payment_method ) {
			//payment methods
			$data_array['sales_by_payment_method'] = asre_pro()->functions->get_payment_methods_reports( $date_range  );
		}
		
		/*** WC Subcription reports data ***/
		if ( class_exists('WC_Subscriptions_Manager') ) {
			
			if ( isset($display_data->display_active_subscriptions) && '1' == $display_data->display_active_subscriptions ) {
				//total active subscribers
				$data_array['total_active_subscriber'] = asre_pro()->functions->get_total_active_subscribers( $date_range ); 			
				$data_array['previous_total_active_subscriber'] = asre_pro()->functions->get_total_active_subscribers( $previous_date_range );
				$data_array['active_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_active_subscriber'], $data_array['total_active_subscriber'] ));
			}
			
			if ( isset($display_data->display_signup_subscriptions) && '1' == $display_data->display_signup_subscriptions ) {
				//total signup subscribers
				$data_array['total_signup_subscriber'] = asre_pro()->functions->get_total_signup_subscribers( $date_range ); 			
				$data_array['previous_total_signup_subscriber'] = asre_pro()->functions->get_total_signup_subscribers( $previous_date_range );
				$data_array['signup_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_signup_subscriber'], $data_array['total_signup_subscriber'] ));
			}
			
			if ( isset($display_data->display_signup_revenue) && '1' == $display_data->display_signup_revenue ) {
				//total signup revenue
				$data_array['total_signup_revenue'] = asre_pro()->functions->get_total_signup_revenue( $date_range ); 			
				$data_array['previous_total_signup_revenue'] = asre_pro()->functions->get_total_signup_revenue( $previous_date_range );
				$data_array['signup_revenue_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_signup_revenue'], $data_array['total_signup_revenue'] ));
			}
			
			if ( isset($display_data->display_renewal_subscriptions) && '1' == $display_data->display_renewal_subscriptions ) {
				//total renewal subscribers
				$data_array['total_renewal_subscriber'] = asre_pro()->functions->get_total_renewal_subscribers( $date_range ); 			
				$data_array['previous_total_renewal_subscriber'] = asre_pro()->functions->get_total_renewal_subscribers( $previous_date_range );
				$data_array['renewal_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_renewal_subscriber'], $data_array['total_renewal_subscriber'] ));
			}
			
			if ( isset($display_data->display_renewal_revenue) && '1' == $display_data->display_renewal_revenue ) {
				//total renewal revenue
				$data_array['total_renewal_revenue'] = asre_pro()->functions->get_total_renewal_revenue( $date_range ); 			
				$data_array['previous_total_renewal_revenue'] = asre_pro()->functions->get_total_renewal_revenue( $previous_date_range );
				$data_array['renewal_revenue_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_renewal_revenue'], $data_array['total_renewal_revenue'] ));
			}
			
			if ( isset($display_data->display_switch_subscriptions) && '1' == $display_data->display_switch_subscriptions ) {
				//total switch subscribers
				$data_array['total_switch_subscriber'] = asre_pro()->functions->get_total_switch_subscribers( $date_range ); 			
				$data_array['previous_total_switch_subscriber'] = asre_pro()->functions->get_total_switch_subscribers( $previous_date_range );
				$data_array['switch_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_switch_subscriber'], $data_array['total_switch_subscriber'] ));
			}
			
			if ( isset($display_data->display_switch_revenue) && '1' == $display_data->display_switch_revenue ) {
				//total switch revenue
				$data_array['total_switch_revenue'] = asre_pro()->functions->get_total_switch_revenue( $date_range ); 			
				$data_array['previous_total_switch_revenue'] = asre_pro()->functions->get_total_switch_revenue( $previous_date_range );
				$data_array['switch_revenue_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_switch_revenue'], $data_array['total_switch_revenue'] ));
			}
			
			if ( isset($display_data->display_resubscribe_subscriptions) && '1' == $display_data->display_resubscribe_subscriptions ) {
				//total resubscribe subscribers
				$data_array['total_resubscribe_subscriber'] = asre_pro()->functions->get_total_resubscribe_subscribers( $date_range ); 			
				$data_array['previous_total_resubscribe_subscriber'] = asre_pro()->functions->get_total_resubscribe_subscribers( $previous_date_range );
				$data_array['resubscribe_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_resubscribe_subscriber'], $data_array['total_resubscribe_subscriber'] ));
			}
			
			if ( isset($display_data->display_resubscribe_revenue) && '1' == $display_data->display_resubscribe_revenue ) {
				//total resubscribe revenue
				$data_array['total_resubscribe_revenue'] = asre_pro()->functions->get_total_resubscribe_revenue( $date_range ); 			
				$data_array['previous_total_resubscribe_revenue'] = asre_pro()->functions->get_total_resubscribe_revenue( $previous_date_range );
				$data_array['resubscribe_revenue_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_resubscribe_revenue'], $data_array['total_resubscribe_revenue'] ));
			}
			
			if ( isset($display_data->display_cancellation_subscriptions) && '1' == $display_data->display_cancellation_subscriptions ) {
				//total cancellation subscribers
				$data_array['total_cancellation_subscriber'] = asre_pro()->functions->get_total_cancellation_subscribers( $date_range ); 			
				$data_array['previous_total_cancellation_subscriber'] = asre_pro()->functions->get_total_cancellation_subscribers( $previous_date_range );
				$data_array['cancellation_subscriber_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_cancellation_subscriber'], $data_array['total_cancellation_subscriber'] ));
			}
			
			if ( isset($display_data->display_net_subscription_gain) && '1' == $display_data->display_net_subscription_gain ) {
				//total net subscription gain
				$data_array['total_net_subscription_gain'] = asre_pro()->functions->get_total_net_subscribers_gain( $date_range ); 			
				$data_array['previous_total_net_subscription_gain'] = asre_pro()->functions->get_total_net_subscribers_gain( $previous_date_range );
				$data_array['net_subscription_gain_growth'] = round((int) $this->get_growth_count( $data_array['previous_total_net_subscription_gain'], $data_array['total_net_subscription_gain'] ));
			}
			
			if ( isset($display_data->display_total_subscriber) && '1' == $display_data->display_total_subscriber ) {
				//Subscriptions By Status (Total)
				$data_array['subscription_by_status'] = asre_pro()->functions->get_subscription_by_status_reports( $date_range  );
			}

		}

		ob_start();
		wc_get_template(
			'email-report-content.php',
			$data_array,
			'sales-report-email-pro/', 
			asre_pro()->get_plugin_path() . '/include/preview/'
		);
		
		$message = ob_get_clean();
		
		return $message;
		
	}
	
	/**
	 * Method triggered on Cron run.
	 * This method will create a WC_SRE_Sales_Report_Email object and call trigger method.
	 *
	 * @since  1.0.0
	*/
	public function cron_email_callback( $id ) {
		
		if ( empty( $id ) ) {
			return;
		}
		
		$data = $this->get_data_byid( $id );
		if (isset($data)) {
			// Check if extension is active
			$enabled = $data->email_enable;
			$report_status = $data->report_status;
			if ( '0' == $enabled || 'draft' == $report_status ) {
				return;
			}
			
		}

		// Check if an email should be send
		$interval = isset($data->email_interval) ? $data->email_interval : '';
		$selected_w_day = isset($data->email_select_week) ? $data->email_select_week : '';
		$selected_m_day = isset($data->email_select_month) ? $data->email_select_month : '';
		$now = new DateTime( gmdate('Y-m-d H:i:s'), new DateTimeZone( wc_timezone_string() ) );
		
		$send_today = false;

		switch ( $interval ) {
			case 'last-30-days':
				// Send monthly reports on the selected day of the month
				if ( $selected_m_day == (int) $now->format( 'j' ) ) {
					$send_today = true;
				}
				break;
			case 'monthly':
				// Send monthly reports on the selected day of the month
				if ( $selected_m_day == (int) $now->format( 'j' ) ) {
					$send_today = true;
				}
				break;
			case 'weekly':
				// Send weekly reports on selected day of week
				if ( $selected_w_day == (int) $now->format( 'w' ) ) {
					$send_today = true;
				}
				break;
			case 'daily':
				// Send everyday if the interval is daily
				$send_today = true;
				break;
			case 'daily-overnight':
				// Send everyday if the interval is daily overnight
				$send_today = true;
				break;
			case 'month-to-date':
				// Send everyday if the interval is month-to-date
				$send_today = true;
				break;
		}

		// Check if we need to send an email today
		if ( true !== $send_today ) {
			return;
		}

		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();	
		$mailer = WC()->mailer();
		$sent_to_admin = false;
		$plain_text = false;
		$email = '';
		
		$message = $this->email_content( $id );
		
		$email_heading = $data->email_subject;
		$subject_email = $data->email_subject;
		
		if (empty($subject_email)) {
			$subject_email = 'Sales Report for {site_title}';	
		}
		
		$subject = str_replace('{site_title}', get_bloginfo( 'name' ), $subject_email );
		
		// create a new email
		$email = new WC_Email();
		$headers = "Content-Type: text/html\r\n";
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );

		$recipients = $data->email_recipients;
		$recipients = explode(',', $recipients);
		
		$logger = wc_get_logger();
		if ($recipients) {
			foreach ($recipients as $recipient) {
				$bool = wp_mail( $recipient, $subject, $message, $email->get_headers() );
				if ('1' == $bool) { 
					$bool = 'Success';
				} else {
					$bool = 'Fail';
				}
				$logger->info( 'Report: ' . $data->report_name, array( 'source' => 'sre-log' ) );
				$logger->info( 'Email: ' . $recipient, array( 'source' => 'sre-log' ) );
				$logger->info( 'Status: ' . $bool, array( 'source' => 'sre-log' ) );
			}
		}		
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
	
	/*
	* set email data for sales report
	*/
	public function set_email_data( $data ) {
		$this->email_data = $data;
	}
	
	/*
	* retune email data for sales report
	*/
	public function get_email_data() {
		return $this->email_data;
	}
	
	/*
	* retune email data for sales report totals
	*/
	public function get_total_report_content( $widget_title, $current_data, $previous_data, $growth, $display_data ) {
		ob_start();
		?>
		<div class="report-widget">
			<p class="col-heading"><strong><?php esc_html_e( $widget_title, 'sales-report-email-pro' ); ?></strong></p>
			<p class="report-summary__item-data">
				<span class="widget-value">
				<?php 
					echo !empty($current_data) ? wp_kses_post($current_data) : '0';
				?>
				</span>
				<?php
				if ( isset($growth) && isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) {
					$gross_growth_value = asre_pro()->admin->growth_html( $growth );
					echo wp_kses_post($gross_growth_value);
				}
				?>
			</p>
			<?php
			if ( 'Active Subscriptions' == $widget_title && isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) {
				?>
				<span class="report-summary__item-prev-label"><?php esc_html_e('total active subscribers', 'sales-report-email-pro'); ?></span>
				<?php
			} else {
				if ( isset($previous_data) && isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) { 
					?>
					<span class="report-summary__item-prev-label"><?php esc_html_e('Previous Period', 'sales-report-email-pro'); ?>: <span class="report-summary__item-prev-value">
					<?php echo !empty($previous_data) ? wp_kses_post($previous_data) : '0'; ?></span></span>
					<?php
				}
			}
			?>
		</div>
		<?php		
		$message = ob_get_clean();
		echo wp_kses_post($message);
	}
	
	/*
	* Return email data for sales report details
	*/
	public function get_details_report_content( $reports_data ) {
		ob_start();

		// Ensure $reports_data is an array before accessing its elements
		if (!is_array($reports_data)) {
			//return; // or handle the error as needed
		}

		?>
		<h3 class="report-table-title">
			<?php
			// Ensure 'title' key exists and is not a float
			echo isset($reports_data['title']) && !is_float($reports_data['title']) 
				? esc_html_e($reports_data['title'], 'sales-report-email-pro') 
				: esc_html__('Unknown Title', 'sales-report-email-pro');
			
			// Check if 'td' is an array, has content, and 'wc_analytics_link' is set and not empty
			if (is_array($reports_data['td']) && !empty($reports_data['td']) && isset($reports_data['wc_analytics_link']) && !empty($reports_data['wc_analytics_link'])) {
				echo '<a href="' . esc_url($reports_data['wc_analytics_link']) . '" target="_blank">' . __('View all', 'sales-report-email-pro') . '</a>';
			}
			?>
		</h3>
		<table class="report-table-widget" cellspacing="0" cellpadding="6" style="width: 100%;vertical-align:top;">
			<tbody class="report-table-list">
				<tr>
					<?php
					// Check if 'th' is an array before looping
					if ( isset( $reports_data['th'] ) && is_array( $reports_data['th'] ) ) {
						foreach ( $reports_data['th'] as $key => $th ) {
							// Only output <th> tags if 'td' is an array and not empty
							if ( is_array( $reports_data['td'] ) && !empty( $reports_data['td'] ) ) {
								echo '<th ' . ( 0 != $key ? 'style="width:20%"' : '' ) . '>';
								echo esc_html($th);
								echo '</th>';
							}
						}
					}
					?>
				</tr>
				<?php
				// Check if 'td' is an array and contains data
				if ( !isset( $reports_data['td'] ) || !is_array( $reports_data['td'] ) || empty( $reports_data['td'] ) ) {
					echo '<tr><td colspan="' . ( isset( $reports_data['th'] ) && is_array( $reports_data['th'] ) ? count( $reports_data['th'] ) : 1 ) . '">' . esc_html__('Data not available.', 'sales-report-email-pro') . '</td></tr>';
				} else {
					foreach ( $reports_data['td'] as $td ) {
						// Ensure each $td is an array before processing
						if (!is_array($td)) {
							continue;
						}
						
						// Ensure $td[0] is set and not empty, otherwise set to 'Unknown'
						if ( isset($td[0] ) && empty( $td[0] ) ) {
							$td[0] = esc_html__('Unknown', 'sales-report-email-pro');
						}

						echo '<tr>';
						foreach ( $td as $column ) {
							echo isset($column) ? '<td>' . wp_kses_post($column) . '</td>' : '';
						}
						echo '</tr>';
					}
				}
				?>
			</tbody>
		</table>
		<?php

		$message = ob_get_clean();
		echo wp_kses_post($message);
	}
	
	/*
	* Growth count function
	*/
	public static function get_growth_count( $previous_count, $current_count ) { 

		if ( 0 != (float) $previous_count ) {
			$percentChange = round( ( ( (float) $current_count - (float) $previous_count ) / (float) $previous_count ) * 100 );
		} else if ( 0 != (float) $current_count && 0 == (float) $previous_count ) {
			$percentChange = 100;
		} else {
			$percentChange = null;
		}
		
		return $percentChange;
	}
	
	/*
	* get value of sales growth
	*/
	public static function growth_html( $growth_count ) {
		ob_start();
		?>
		<span class="growth-span 
		<?php 
		if ($growth_count>0) { 
			echo'arrow-up'; 
		}
		if ( 0 == $growth_count ) { 
			echo'arrow-equal'; 
		} 
		if ( $growth_count<0 ) {
			echo'arrow-down'; 
		} 
		?>
		">
		<?php echo number_format( (float) $growth_count); ?>%
		</span> 
		<?php
		$message = ob_get_clean();
		return wp_kses_post($message);
	}
	
	/*
	* callback add_action for email content branding logo html
	*
	*/
	public function change_asre_branding_logo( $branding_logo_url, $data ) {
		if ( !empty( $data->branding_logo ) ) {
			return $data->branding_logo;
		}
		
		return $branding_logo_url;
	}	
	
	/**
	 * Get the current date range
	 *
	 * @since  1.0.0
	 * @return DateTime
	 */
	public function get_current_date_range( $interval ) {
		
		$this->interval = $interval;
		
		// Subtract a second from end date.
		$data = asre_pro()->admin->get_email_data();
		
		if ( 'one-time' == $this->interval ) {
			$daterange = isset($data->daterange) ? unserialize($data->daterange) : array( gmdate('Y/m/d'), gmdate('Y/m/d'));
			$start_date = new DateTime( date_i18n( $daterange[0] . ' 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n($daterange[1] . ' 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
		} else {
			$start_date = new DateTime( date_i18n( 'Y-m-d 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n('Y-m-d 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
		}
		
		// Modify start date based on interval
		switch ( $this->interval ) {
			case 'one-time':
				$start_date;
				$end_date;
				break;
			case 'last-30-days':
				$start_date->modify( '-30 days' );
				$end_date->modify( '-1 day' );
				break;
			case 'monthly':
				$start_date->modify( 'first day of previous month' );
				$end_date->modify( 'last day of previous month' );		
				break;
			case 'previous-month-to-date':
				$start_date->modify( 'first day of previous month' );					
				
				$today = new DateTime(date_i18n('Y-m-d 00:00:00'), new DateTimeZone(wc_timezone_string()));
				$target_day = $today->format('d') - 1;

				// Adjust to the first day of the previous month
				// $end_date = (clone $today)->modify('first day of last month');
				$end_date = clone $today;
				$end_date_get = $end_date->modify('first day of last month');

				// Check if the target day exists in the previous month
				// $days_in_previous_month = (clone $end_date)->format('t');
				$end_date_clone = clone $end_date_get;
				$days_in_previous_month = $end_date_clone->format('t');
				if ($target_day > $days_in_previous_month) {
					// If the target day exceeds the number of days in the previous month, adjust to the last day of the previous month
					$end_date->modify('last day of this month');
				} else {
					// Otherwise, set to the target day of the previous month
					$end_date->setDate($end_date->format('Y'), $end_date->format('m'), $target_day);						
				}

				break;
			case 'weekly':
				$start_date->modify( '-1 week' );
				$end_date->modify( '-1 day' );
				break;	
			case 'daily':
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				break;
			case 'daily-overnight':
				$start_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_start ), new DateTimeZone( wc_timezone_string() ) );
				$end_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_end ), new DateTimeZone( wc_timezone_string() ) );
				if ($data->day_hour_start >= $data->day_hour_end) {
					$start_date->modify( '-1 day' );
				}
				break;
			default:
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				break;				
		}
		
		//date convert to gmt datetime
		$start_date_gmt = $this->convert_local_datetime_to_gmt($start_date->format('Y-m-d H:i:s'));
		$end_date_gmt = $this->convert_local_datetime_to_gmt($end_date->format('Y-m-d H:i:s'));		
		
		$date_range = (object) array(
			'start_date' => $start_date,
			'end_date' => $end_date,
			'start_date_gmt' => $start_date_gmt,
			'end_date_gmt' => $end_date_gmt,
		);
				
		return $date_range;
	}
	
	/**
	 * Get the previous date range
	 *
	 * @since  1.0.0
	 * @return DateTime
	 */
	public function get_previous_date_range( $interval ) {
		
		$privous_intervals = array(
			'daily' => 'previous_day',
			'weekly' => 'previous_week',
			'monthly' => 'previous_month',
			'last-30-days' => 'previous-last-30-days',
			'daily-overnight' => 'previous_overnight',
			'one-time'	=> 'previous-one-time',
		);
		
		$this->interval = $privous_intervals[$interval];
		
		// Subtract a second from end date.
		$data = asre_pro()->admin->get_email_data();
		
		if ( 'previous-one-time' == $this->interval ) {
			$daterange = isset($data->daterange) ? unserialize($data->daterange) : array( gmdate('Y/m/d'), gmdate('Y/m/d'));
			$start_date = new DateTime( date_i18n( $daterange[0] . ' 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n($daterange[1] . ' 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
			$datediff = strtotime($daterange[1]) - strtotime($daterange[0]);
			$day_count = round( $datediff / ( 60 * 60 * 24 ) ) != '' ? round( $datediff / ( 60 * 60 * 24 ) ) + 1 : 1;
		} else {
			$start_date = new DateTime( date_i18n( 'Y-m-d 00:00:00' ), new DateTimeZone( wc_timezone_string() ) );
			$end_date = new DateTime( date_i18n('Y-m-d 23:59:59'), new DateTimeZone( wc_timezone_string() ) );
		}
		
		// Modify start date based on interval
		switch ( $this->interval ) {
			case 'previous-one-time':
				$start_date->modify( '-' . $day_count . ' day' );
				$end_date->modify( '-' . $day_count . ' day' );
				break;
			case 'previous-last-30-days':
				$start_date->modify( '-60 days' );
				$end_date->modify( '-31 days' );
				break;
			case 'previous_month':
				// last month
				$start_date->modify( 'first day of previous month' );			
				$end_date->modify( 'last day of previous month' );
				
				// second last month
				$start_date->modify( 'first day of previous month' );			
				$end_date->modify( 'last day of previous month' );
				break;
			case 'previous_week':
				$start_date->modify( '-2 week' );
				$end_date->modify( '-1 week' );
				$end_date->modify( '-1 day' );
				break;
			case 'previous_day':
				$start_date->modify( '-2 day' );				
				$end_date->modify( '-2 day' );
				break;
			case 'previous_overnight':
				$start_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_start ), new DateTimeZone( wc_timezone_string() ) );
				$end_date = new DateTime( date_i18n( gmdate( 'Y-m-d' ) . $data->day_hour_end ), new DateTimeZone( wc_timezone_string() ) );
				
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				if ($data->day_hour_start >= $data->day_hour_end) {
					$start_date->modify( '-1 day' );
				}
				break;
			default:
				$start_date->modify( '-1 day' );
				$end_date->modify( '-1 day' );
				break;				
		}
		
		//date convert to gmt datetime
		$start_date_gmt = $this->convert_local_datetime_to_gmt($start_date->format('Y-m-d H:i:s'));
		$end_date_gmt = $this->convert_local_datetime_to_gmt($end_date->format('Y-m-d H:i:s'));		
		
		$previous_date_range = (object) array(
			'start_date' => $start_date,
			'end_date' => $end_date,
			'start_date_gmt' => $start_date_gmt,
			'end_date_gmt' => $end_date_gmt,
		);
		
		return $previous_date_range;
	}
	
	/**
	 * Get the convert datetime to gmt
	 *
	 * @param DateTime $datetime_string
	 *
	 * @since  1.0.0
	 */
	public static function convert_local_datetime_to_gmt( $datetime_string ) {
		$datetime = new \DateTime( $datetime_string, new \DateTimeZone( wc_timezone_string() ) );
		$datetime->setTimezone( new \DateTimeZone( 'GMT' ) );
		return $datetime;
	}
	
}
