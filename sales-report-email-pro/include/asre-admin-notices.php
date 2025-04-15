<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ASRE_Admin_Notices {

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();	
	}
	
	/**
	 * Get the class instance
	 *
	 * @return ASRE_Admin_Notices
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {

		add_action( 'admin_notices', array( $this, 'asre_return_for_woocommerce_notice' ) );
		add_action( 'admin_init', array( $this, 'asre_return_for_woocommerce_notice_ignore' ) );
	}

	/*
	* Dismiss admin notice for return
	*/
	public function asre_return_for_woocommerce_notice_ignore() {
		if ( isset( $_GET['asre-return-for-woocommerce-notice'] ) ) {
			
			if (isset($_GET['nonce'])) {
				$nonce = sanitize_text_field($_GET['nonce']);
				if (wp_verify_nonce($nonce, 'asre_return_for_woocommerce_dismiss_notice')) {
					update_option('asre_return_for_woocommerce_notice_ignore', 'true');
				}
			}
			
		}
	}

	/*
	* Display admin notice on plugin install or update
	*/
	public function asre_return_for_woocommerce_notice() { 		
		
		$return_installed = ( function_exists( 'zorem_returns_exchanges' ) ) ? true : false;
		if ( $return_installed ) {
			return;
		}
		
		if ( get_option('asre_return_for_woocommerce_notice_ignore') ) {
			return;
		}	
		
		$nonce = wp_create_nonce('asre_return_for_woocommerce_dismiss_notice');
		$dismissable_url = esc_url(add_query_arg(['asre-return-for-woocommerce-notice' => 'true', 'nonce' => $nonce]));

		?>
		<style>		
		.wp-core-ui .notice.asre-dismissable-notice{
			position: relative;
			padding-right: 38px;
			border-left-color: #005B9A;
		}
		.wp-core-ui .notice.asre-dismissable-notice h3{
			margin-bottom: 5px;
		} 
		.wp-core-ui .notice.asre-dismissable-notice a.notice-dismiss{
			padding: 9px;
			text-decoration: none;
		} 
		.wp-core-ui .button-primary.asre_notice_btn {
			background: #005B9A;
			color: #fff;
			border-color: #005B9A;
			text-transform: uppercase;
			padding: 0 11px;
			font-size: 12px;
			height: 30px;
			line-height: 28px;
			margin: 5px 0 15px;
		}
		.asre-dismissable-notice strong{
			font-weight: bold;
		}
		</style>
		<div class="notice updated notice-success asre-dismissable-notice">			
			<a href="<?php esc_html_e( $dismissable_url ); ?>" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></a>			
			<h3>Launching Zorem Returns!</h3>
			<p>We’re thrilled to announce the launch of our new <a href="https://www.zorem.com/product/zorem-returns/"><strong>Zorem Returns Plugin!</strong></a> This powerful tool is designed to streamline and automate your returns and exchanges management process, freeing up your time to focus on what truly matters—growing your business.</p>				
			<p><strong>Act fast!</strong> For a limited time, you can enjoy an exclusive <strong>40% discount</strong> on the plugin with the coupon code <strong>RETURNS40.</strong> Don’t miss out—this offer expires on <strong>October 31st, 2024.</strong></p>
			<a class="button-primary asre_notice_btn" target="blank" href="<?php echo esc_url( 'https://www.zorem.com/product/zorem-returns/' ); ?>">Unlock 40% Off</a>
			<a class="button-primary asre_notice_btn" href="<?php esc_html_e( $dismissable_url ); ?>">Dismiss</a>				
		</div>	
		<?php 				
	}

}
