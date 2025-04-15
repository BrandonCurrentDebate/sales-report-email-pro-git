<?php
/*
 * @wordpress-plugin
 * Plugin Name: Email Reports for WooCommerce
 * Plugin URI: https://www.zorem.com/product/email-reports-for-woocommerce/ 
 * Description: Get sales reports from your WooCommerce store directly to your inbox on a flexible schedule. Schedule multiple reports and use advanced report builder to customize the report schedule, data and display, compare to the previous period, show subscriptions data and more..
 * Version: 3.5
 * Author: zorem
 * Author URI: https://zorem.com 
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: sales-report-email-pro
 * Domain Path: /lang/
 * Woo: 18734001092482:5e8dbd5dd1a47fdabf75e247ea7700a0
 * WC tested up to: 9.5.1
 * Requires Plugins: woocommerce
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package zorem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Sales_Report_Email_PRO {
	
	public $email_data;
	public $plugin_path;
	public $admin;
	public $install;
	public $cron;
	public $functions;
	public $customizer;
	public $asre_admin_notice;

	/**
	 * Sales Report Email PRO
	 *
	 * @var string
	 */
	public $version = '3.5';
	
	/**
	 * Constructor
	 *
	 * @since  1.0.0
	*/
	public function __construct() {
		
		// Check if Wocoomerce is activated
		if ( $this->is_wc_active()  ) {
			
			add_action( 'admin_notices', array( $this, 'asre_pro_admin_notice' ) );

			$this->includes_for_all();

			//callback on activate plugin
			register_activation_hook( __FILE__, array( $this, 'on_activation' ) );
			register_activation_hook( __FILE__, array( $this->install, 'asre_insert_table_columns' ) );
			add_action( 'init', array( $this->install, 'asre_update_install_callback' ) );

			$this->includes();
			$this->init();
		}
	}
	
	/**
	 * Check if WooCommerce is active
	 *
	 * @since  1.0.0
	 * @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}
		

		// Do the WC active check
		if ( false === $is_active ) {
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	 * Display WC active notice
	 *
	 * @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p><?php printf( esc_html( 'Please install and activate %1$sWooCommerce%2$s for WC Sales Report Email to work!', 'sales-report-email-pro' ), '<a href="' . esc_url(admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' )) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Initialize plugin
	 *
	 * @since  1.0.0
	*/
	private function init() {
		
		// Cron hook
		add_action( 'wc_asre_send', array( $this->admin, 'cron_email_callback' ) );
		
		// Load plugin textdomain
		add_action('plugins_loaded', array($this, 'load_textdomain'));
		
		//callback for add action link for plugin page	
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'my_plugin_action_links' ));
		
	}
	
	/*
	* call on plugin activation
	* 
	* @since 2.4
	*/
	public function on_activation() {
		
		// SRE free deactivate
		deactivate_plugins('woo-advanced-sales-report-email/woocommerce-advanced-sales-report-email.php' );
		set_transient( 'free_sre_plugin', 'notice', 3 );

	}
	
	/**
	 * SRE pro admin notice
	 *
	 * @since 1.0.0
	 */
	public function asre_pro_admin_notice() {
		
		//Display SRE Free notice
		if ( 'notice' == get_transient( 'free_sre_plugin' ) ) {
			?>
			<div id="message" class="updated notice is-dismissible">
				<p><?php printf( esc_html( 'The Salse Report Email free plugin was deactivated since you use the PRO version. You can now remove the Free version.', 'sales-report-email-pro' ), '<a href="' . esc_url(admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' )) . '">', '</a>' ); ?></p>
			</div>
			<?php
			delete_transient( 'free_sre_plugin' );
		}
	}

	public function includes_for_all() {
		
		require_once $this->get_plugin_path() . '/include/asre-admin.php';
		$this->admin = ASRE_Admin_Pro::get_instance();

		require_once $this->get_plugin_path() . '/include/asre-installation.php';
		$this->install = ASRE_Installation::get_instance();

	}

	/**
	 * Include plugin file.
	 *
	 * @since 1.0.0
	 *
	 */	
	public function includes() {
		
		require_once $this->get_plugin_path() . '/include/asre-cron-manager.php';
		$this->cron = ASRE_Cron_Manager::get_instance();
		
		require_once $this->get_plugin_path() . '/include/asre-data-functions.php';
		$this->functions = ASRE_Data_Functions::get_instance();
		
		// customizer
		require_once $this->get_plugin_path() . '/include/customizer/customizer-admin.php';	
		$this->customizer = SRE_Customizer_Admin::get_instance();

		require_once $this->get_plugin_path() . '/include/asre-admin-notices.php';
		$this->asre_admin_notice = ASRE_Admin_Notices::get_instance();
		
	}
	
	/**
	 * Add plugin action links.
	 *
	 * Add a link to the settings page on the plugins.php page.
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $links List of existing plugin action links.
	 * @return array         List of modified plugin action links.
	 */
	public function my_plugin_action_links( $links ) {
		$links = 	array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=woocommerce-advanced-sales-report-email' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>'
		), array(
			'<a href="' . esc_url( 'https://www.zorem.com/docs/sales-report-email-for-woocommerce/?utm_source=wp-admin&utm_medium=SRE&utm_campaign=docs' ) . '" target="_blank">' . esc_html( 'Docs', 'woocommerce' ) . '</a>'
		), array(
			'<a href="' . esc_url( 'https://www.zorem.com/my-account/contact-support/?utm_source=wp-admin&utm_medium=contact&utm_campaign=support' ) . '" target="_blank">' . esc_html( 'Support', 'woocommerce' ) . '</a>'
		), array(
			'<a href="' . esc_url( 'https://wordpress.org/support/plugin/woo-advanced-sales-report-email/reviews/#new-post' ) . '" target="_blank">' . esc_html( 'Review', 'woocommerce' ) . '</a>'
		),
		$links );
		return $links;
	}
			
	/*
	* load text domain
	*/
	public function load_textdomain() {
		load_plugin_textdomain( 'sales-report-email-pro', false, plugin_dir_path( plugin_basename(__FILE__) ) . 'lang/' );
		
		require_once $this->get_plugin_path() . '/include/asre-report-manager.php';
	}
	

	/**
	 * Gets the absolute plugin path without a trailing slash, e.g.
	 * /path/to/wp-content/plugins/plugin-directory.
	 *
	 * @return string plugin path
	 */
	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}
	
	/*
	* @return __FILE__.
	*/
	public static function get_plugin_domain() {
		return __FILE__;
	}

	
	/*
	* plugin file directory function
	*/	
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}
	
	
}
/**
 * Returns an instance of Sales_Report_Email_PRO.
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 * @return Sales_Report_Email_PRO
*/
function asre_pro() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new Sales_Report_Email_PRO();
	}

	return $instance;
}

/**
 * Register this class globally.
 *
 * Backward compatibility.
*/
asre_pro();

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
