<?php
/**
 * Sales report email
 *
 * Class ASRE_Data_Functions
 * 
 * @version       1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class ASRE_Data_Functions { 

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	
	/**
	 * Get the class instance
	 *
	 * @return ASRE_Data_Functions
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get the total reports
	 *
	 * @return data
	*/
	public function get_net_salse_this_month_reports( $date_range, $interval ) {

		if ( 'daily' == $interval || 'daily-overnight' == $interval ) {

			$start_of_month = clone $date_range->start_date;
			$start_date = $start_of_month->modify('first day of this month 00:00:00');			
			
			$args = array(
				'before' 	=> $date_range->end_date->format( 'Y-m-d H:i:s' ),
				'after'  	=> $start_date->format( 'Y-m-d H:i:s' ),
			);

			$reports = new \Automattic\WooCommerce\Admin\API\Reports\Revenue\Query( $args );
			$data = $reports->get_data();
			return $data->totals->net_revenue;
		} else {
			return '';
		}
		
	}

	/**
	 * Get the total reports
	 *
	 * @return data
	*/
	// public function get_net_salse_last_month_reports( $date_range, $interval ) {
	// 	echo '<pre>';print_r($date_range);echo '</pre>';
	// 	$start_date = $date_range->start_date->modify('-1 month'); // Go back one month
	
	// 	// Check the interval
	// 	if ( 'daily' == $interval || 'daily-overnight' == $interval ) {	
	// 		$args = array(
	// 			'before' => $date_range->end_date->format( 'Y-m-d H:i:s' ),
	// 			'after'  => $start_date->format( 'Y-m-d H:i:s' ),
	// 		);
	
	// 		$reports = new \Automattic\WooCommerce\Admin\API\Reports\Revenue\Query( $args );
	// 		$data = $reports->get_data();
	// 		return $data->totals->net_revenue;
	// 	} else {
	// 		return '';
	// 	}
	// }
	
	
	/**
	 * Get the total reports
	 *
	 * @return data
	*/
	public function get_total_reports( $date_range, $interval ) {

		$Interval = array(
			'daily' => 'day',
			'previous_day' => 'day',
			'daily-overnight' => 'day',
			'previous_overnight' => 'day',
			'weekly' => 'week',
			'previous_week' => 'week',
			'monthly' => 'month',
			'month-to-date' => 'day',
			'previous_month' => 'month',
			'last-30-days' => 'month',
			'previous-last-30-days' => 'month',
			'one-time' => 'month',
			'previous-one-time' => 'month',
		);
		
		$args = array(
			'before' 	=> $date_range->end_date->format( 'Y-m-d H:i:s' ),
			'after'  	=> $date_range->start_date->format( 'Y-m-d H:i:s' ),
			'interval' 	=> $Interval[$interval],
		);
		
		$reports = new \Automattic\WooCommerce\Admin\API\Reports\Revenue\Query( $args );
		$data = $reports->get_data();
		return $data->totals;
		
	}
	
	/**
	 * Get the shipping tax
	 *
	 * @return data
	*/
	public function get_total_shipping_tax( $date_range ) {
		
		$args = array(
			'before' 	=> $date_range->end_date->format( 'Y-m-d H:i:s' ),
			'after'  	=> $date_range->start_date->format( 'Y-m-d H:i:s' ),
		);
		
		$report = new \Automattic\WooCommerce\Admin\API\Reports\Taxes\Query( $args );
		$data = $report->get_data();
		
		$shipping_tax = '';
		if (isset($data->data[0])) {
			$shipping_tax = $data->data[0]['shipping_tax'];
		}
		
		return $shipping_tax;
		
	}
	
	/**
	 * Get the new customer
	 *
	 * @return data
	*/
	public function get_total_new_customer( $date_range ) {
		
		$users_query = new WP_User_Query(
			array(
				'role__in	' => array( 'customer', 'subscriber' ),
				'number' => -1,
				'date_query' => array(
					array(
						'after' => $date_range->start_date->format( 'Y-m-d H:i:s' ),
						'before' => $date_range->end_date->format( 'Y-m-d H:i:s' ),
					)
				)
			)
		);
		
		return $users_query->total_users;
		
	}
 
	/**
	 * Get the download count
	 *
	 * @return data
	*/
	public function get_total_downloads( $date_range ) {
		
		$args = array(
			'before' 	=> $date_range->end_date->format( 'Y-m-d H:i:s' ),
			'after'  	=> $date_range->start_date->format( 'Y-m-d H:i:s' ),
		);
		
		$report = new \Automattic\WooCommerce\Admin\API\Reports\Downloads\Query( $args );
		$data = $report->get_data();
		
		$dowamloads_total = '';
		if (isset($data->total)) {
			$dowamloads_total = $data->total;
		}
		
		return $dowamloads_total;
		
	}	
	
	/**
	 * Get the top selling data
	 *
	 * @return data
	*/
	public function get_top_selling_reports( $date_range, $interval, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		$limit_row = !empty($display_data->display_top_sellers_row) ? $display_data->display_top_sellers_row : 5;
		
		$products_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Products\DataStore();
		$top_sellers       = $limit_row > 0 ? $products_data_store->get_data(
			apply_filters(
				'woocommerce_analytics_products_query_args',
				array(
					'orderby'       => 'items_sold',
					'order'         => 'desc',
					'before'       => $end_date,
					'after'        => $start_date,
					'per_page'      => $limit_row,
					'extended_info' => true,
				)
			)
		)->data : array();
		
		$total = array_sum( array_column( $top_sellers, 'items_sold' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $top_sellers as $top_seller ) {
			$name = $top_seller['extended_info']['name'] ? wp_kses_post($top_seller['extended_info']['name'] ) : '';
			$sku = $top_seller['extended_info']['sku'] && !empty($top_seller['extended_info']['sku'])  ? ' (' . wp_kses_post($top_seller['extended_info']['sku'] ) . ')' : '';
			$array = array( 
				$name . $sku,
				// wp_kses_post($top_seller['items_sold']) . ' (' . round( ( $top_seller['items_sold']*100 )/$total ) . '%)',
				// wp_kses_post( $total != 0 ? $top_seller['items_sold'] : '' ) . ' (' . round( ( $total != 0 ? ( $top_seller['items_sold'] * 100 ) / $total : $total ) ) . '%)',
				wp_kses_post( 0 != $total ? $top_seller['items_sold'] : '' ) . ' (' . round( ( 0 != $total ? ( $top_seller['items_sold'] * 100 ) / $total : $total ) ) . '%)',

				wp_kses_post(wc_price($top_seller['net_revenue'])),
			);

			$array = apply_filters( 'sre_top_selling_products_details_table_content', $array, $top_seller, $top_seller['product_id'] );
			array_push($td_array, $array);
		}

		$th_array = array(
			__('Product Name', 'sales-report-email-pro'),
			__('Quantity', 'sales-report-email-pro'),
			__('Net Sales', 'sales-report-email-pro')
		);

		$th_array = apply_filters( 'sre_top_selling_products_details_table_header', $th_array );
		
		$top_sellers = array( 
			'title' => __('Top Selling Products', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fproducts&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => $th_array,
			'td' => $td_array
		);
		
		return $top_sellers;
		
	}
	
	/**
	 * Get the top variations selling data
	 *
	 * @return data
	*/
	public function get_top_variations_selling_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		$limit_row = !empty($display_data->display_top_variations_row) ? $display_data->display_top_variations_row : 5;

		$args = array(			
			'orderby'       => 'items_sold',
			'order'         => 'desc',
			'before'       => $end_date,
			'after'        => $start_date,
			'per_page'      => $limit_row,
			'extended_info' => true,
		);		
		
		$products_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Variations\Query( $args );
		$top_variations_sellers       = $limit_row > 0 ? $products_data_store->get_data()->data : array();
		
		$total = array_sum( array_column( $top_variations_sellers, 'items_sold' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $top_variations_sellers as $top_variations_seller ) {
			$name = isset($top_variations_seller['extended_info']['name']) && !empty($top_variations_seller['extended_info']['name']) ? wp_kses_post($top_variations_seller['extended_info']['name'] ) : '';
			$sku = isset($top_variations_seller['extended_info']['sku']) && !empty($top_variations_seller['extended_info']['sku']) ? ' (' . wp_kses_post($top_variations_seller['extended_info']['sku'] ) . ')' : '';
			$array = array( 
				$name . $sku,
				// wp_kses_post($top_variations_seller['items_sold']) . ' (' . round( ( $top_variations_seller['items_sold']*100 )/$total ) . '%)',
				// wp_kses_post( $total != 0 ? $top_variations_seller['items_sold'] : '' ) . ' (' . round( ( $total != 0 ? ( $top_variations_seller['items_sold'] * 100 ) / $total : $total ) ) . '%)',
				wp_kses_post( 0 != $total ? $top_variations_seller['items_sold'] : '' ) . ' (' . round( ( 0 != $total ? ( $top_variations_seller['items_sold'] * 100 ) / $total : $total ) ) . '%)',

				wp_kses_post(wc_price($top_variations_seller['net_revenue'])),
			);
			array_push($td_array, $array);
		}
		
		$top_variations_sellers = array(
			'title' => __('Top Selling Product Variations', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fvariations&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => array(
				__('Product Name', 'sales-report-email-pro'),
				__('Quantity', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $top_variations_sellers;
		
	}
	
	/**
	 * Get the top categories data
	 *
	 * @return data
	*/
	public function get_top_categories_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_top_categories_row) ? $display_data->display_top_categories_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		$categories_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Categories\DataStore();
		$top_categories       = $limit_row > 0 ? $categories_data_store->get_data(
			apply_filters(
				'woocommerce_analytics_categories_query_args',
				array(
					'orderby'       => 'items_sold',
					'order'         => 'desc',
					'before'       	=> $end_date,
					'after'        	=> $start_date,
					'per_page'      => $limit_row,
					'extended_info' => true,
				)
			)
		)->data : array();
		
		$total = array_sum( array_column( $top_categories, 'items_sold' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $top_categories as $top_category ) {
			$category_name = isset( $top_category['extended_info'] ) && isset( $top_category['extended_info']['name'] ) ? $top_category['extended_info']['name'] : '';
			$array = array( 
				$category_name,
				// wp_kses_post($top_category['items_sold']) . ' (' . round( ( $top_category['items_sold']*100 )/$total ) . '%)',
				// wp_kses_post( $total != 0 ? $top_category['items_sold'] : '' ) . ' (' . round(($total != 0 ? ($top_category['items_sold'] * 100) / $total : $total)) . '%)',
				wp_kses_post( 0 != $total ? $top_category['items_sold'] : '' ) . ' (' . round( ( 0 != $total ? ( $top_category['items_sold'] * 100 ) / $total : $total ) ) . '%)',

				wp_kses_post(wc_price($top_category['net_revenue'])),
			);
			array_push($td_array, $array);
		}
		
		$top_categories = array(
			'title' => __('Top Selling Categories', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fcategories&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => array(
				__('Category Name', 'sales-report-email-pro'),
				__('Quantity', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $top_categories;
		
	}

	/**
	 * Get the top categories data
	 *
	 * @return data
	*/
	public function get_product_data_reports( $date_range, $display_data ) {

		// Prepare array to hold product download data
		$td_array = array();
	
		// Convert the date range to the correct format
		$start_date = $date_range->start_date->format('Y-m-d H:i:s');
		$end_date = $date_range->end_date->format('Y-m-d H:i:s');
	
		// Define query arguments with the correct date filter
		$args = array(
			'limit' => -1,  // Fetch all orders in the range
			'status' => array('completed', 'processing'),  // Adjust based on the required statuses
			'date_query' => array(
				array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,  // Include the start and end date in the query
				),
			),
		);
	
		// Get the orders
		$orders = wc_get_orders( $args );
	
		foreach ( $orders as $order ) {
	
			// Ensure the order is not an OrderRefund object
			if ( $order instanceof \Automattic\WooCommerce\Admin\Overrides\OrderRefund ) {
				continue; // Skip refunds
			}
	
			$order_id = $order->get_id(); // Order ID
			$user = $order->get_user(); // Get the user
	
			// Get downloadable items
			$downloads = $order->get_downloadable_items();
	
			if ( ! empty( $downloads ) ) {
				foreach ( $downloads as $download ) {
					$product_id = $download['product_id'];
					$file_name = $download['file']['name']; // Downloadable file name
	
					// Get product title
					$product_title = get_the_title( $product_id );
	
					// Check for user login or 'Guest'
					$username = $user ? $user->user_login : 'Guest';
	
					// Populate the table data
					$td_array[] = array(
						'product_title' => $product_title,
						'file_name' => $file_name,
						'order_id' => $order_id,
						'username' => $username,
					);
				}
			}
		}
	
		// Build the final report data
		$downloads_products_data = array( 
			'title' => __('Downloads details', 'sales-report-email-pro'),
			'wc_analytics_link' => admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fdownloads&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => array(
				__('Product title', 'sales-report-email-pro'),
				__('File name', 'sales-report-email-pro'),
				__('Order #', 'sales-report-email-pro'),
				__('Username', 'sales-report-email-pro')
			),
			'td' => $td_array,
		);
	
		return $downloads_products_data;
	}
	
	
	
	/**
	 * Get the top coupons data
	 *
	 * @return data
	*/
	public function get_coupons_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_coupons_row) ? $display_data->display_sales_by_coupons_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		$args = array(
			'before'       => $end_date,
			'after'        => $start_date,
			'per_page'      => $limit_row,
		);
		$coupons_data_store = new \Automattic\WooCommerce\Admin\API\Reports\Coupons\Query( $args );
		$salse_by_coupons       = $limit_row > 0 ? $coupons_data_store->get_data()->data : array();
		
		$total = array_sum( array_column( $salse_by_coupons, 'orders_count' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $salse_by_coupons as $salse_by_coupon ) {
			$coupon_code = new WC_Coupon($salse_by_coupon['coupon_id']);
			$array = array( 
				wp_kses_post($coupon_code->get_code()),
				// wp_kses_post($salse_by_coupon['orders_count']) . ' (' . round( ( $salse_by_coupon['orders_count']*100 )/$total ) . '%)',
				// wp_kses_post( $total != 0 ? $salse_by_coupon['orders_count'] : '' ) . ' (' . round( ( $total != 0 ? ($salse_by_coupon['orders_count'] * 100) / $total : $total ) ) . '%)',
				wp_kses_post( 0 != $total ? $salse_by_coupon['orders_count'] : '' ) . ' (' . round( ( 0 != $total ? ( $salse_by_coupon['orders_count'] * 100 ) / $total : $total ) ) . '%)',

				wp_kses_post(wc_price($salse_by_coupon['amount'])),
			);
			array_push($td_array, $array);
		}
		
		$salse_by_coupons = array( 
			'title' => __('Sales By Coupons', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fcoupons&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => array(
				__('Coupon Code', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Amount discounted', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $salse_by_coupons;
		
	}
	
	/**
	 * Get the billing country data
	 *
	 * @return data
	*/
	public function get_billing_country_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_billing_country_row) ? $display_data->display_sales_by_billing_country_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;
		
		$sales_by_billing_country_orders = $wpdb->get_results( $wpdb->prepare( 
			"SELECT
				country_meta.meta_value as country,					
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,					
				COUNT(DISTINCT order_stats.order_id) AS total_orders,
				( SUM(order_stats.total_sales) + COALESCE( SUM(order_coupon_lookup.discount_amount), 0 ) - SUM(order_stats.tax_total) - SUM(order_stats.shipping_total) + ABS( SUM( CASE WHEN net_total < 0 THEN net_total ELSE 0 END ) ) ) as gross_sales	
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS country_meta ON(order_stats.order_id = country_meta.post_id)
			LEFT JOIN
				{$wpdb->prefix}wc_order_coupon_lookup as order_coupon_lookup ON order_coupon_lookup.order_id = order_stats.order_id		
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash', 'wc-failed' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND country_meta.meta_key IN ( '_billing_country')
			GROUP BY country
			ORDER BY total_sales DESC", $end_date, $start_date 
		) );
		
		$total = array_sum( array_column( $sales_by_billing_country_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_billing_country_orders, 0, $limit_row) as $country_sales ) {
			$array = array( 
				wp_kses_post(WC()->countries->countries[ $country_sales->country ]),
				wp_kses_post($country_sales->total_orders) . ' (' . round( ( $country_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($country_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_billing_country_orders = array( 
			'title' => __('Sales By Billing Country', 'sales-report-email-pro'),
			'wc_analytics_link'	=> class_exists('Sales_Report_By_Country') ? admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fsales-by-country&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date . '&country_type=_billing_country' ) : '',
			'th' => array(
				__('Country', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_billing_country_orders;
		
	}
	
	/**
	 * Get the shipping country data
	 *
	 * @return data
	*/
	public function get_shipping_country_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_shipping_country_row) ? $display_data->display_sales_by_shipping_country_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;		

		$sales_by_shipping_country_orders = $wpdb->get_results( $wpdb->prepare( 
			"SELECT
				country_meta.meta_value as country,					
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,					
				COUNT(DISTINCT order_stats.order_id) AS total_orders,
				( SUM(order_stats.total_sales) + COALESCE( SUM(order_coupon_lookup.discount_amount), 0 ) - SUM(order_stats.tax_total) - SUM(order_stats.shipping_total) + ABS( SUM( CASE WHEN net_total < 0 THEN net_total ELSE 0 END ) ) ) as gross_sales	
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS country_meta ON(order_stats.order_id = country_meta.post_id)
			LEFT JOIN
				{$wpdb->prefix}wc_order_coupon_lookup as order_coupon_lookup ON order_coupon_lookup.order_id = order_stats.order_id		
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND country_meta.meta_key IN ( '_shipping_country')
			GROUP BY country
			ORDER BY total_sales DESC", $end_date, $start_date 
		) );
		
		$total = array_sum( array_column( $sales_by_shipping_country_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_shipping_country_orders, 0, $limit_row) as $country_sales ) {
			$array = array( 
				wp_kses_post(WC()->countries->countries[ $country_sales->country ]),
				wp_kses_post($country_sales->total_orders) . ' (' . round( ( $country_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($country_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_shipping_country_orders = array( 
			'title' => __('Sales By Shipping Country', 'sales-report-email-pro'),
			'wc_analytics_link'	=> class_exists('Sales_Report_By_Country') ? admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Fsales-by-country&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ) : '',
			'th' => array(
				__('Country', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_shipping_country_orders;
		
	}
	
	/**
	 * Get the billing state data
	 *
	 * @return data
	*/
	public function get_billing_state_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_billing_state_row) ? $display_data->display_sales_by_billing_state_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;		

		$sales_by_billing_state_orders = $wpdb->get_results( $wpdb->prepare( 
			"SELECT
				country_meta.meta_value as country,
				state_meta.meta_value as state,					
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS country_meta ON(order_stats.order_id = country_meta.post_id)
			LEFT JOIN
				{$wpdb->postmeta} AS state_meta ON(order_stats.order_id = state_meta.post_id)
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND country_meta.meta_key IN ( '_billing_country')
				AND state_meta.meta_key IN ( '_billing_state')
			GROUP BY state
			ORDER BY total_sales DESC", $end_date, $start_date 
		) );
		
		$total = array_sum( array_column( $sales_by_billing_state_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_billing_state_orders, 0, $limit_row) as $state_sales ) {
			$array = array( 
				wp_kses_post(WC()->countries->get_states( $state_sales->country )[$state_sales->state]),
				wp_kses_post($state_sales->total_orders) . ' (' . round( ( $state_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($state_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_billing_state_orders = array( 
			'title' => __('Sales By Billing State', 'sales-report-email-pro'),
			'th' => array(
				__('State', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_billing_state_orders;
		
	}
	
	/**
	 * Get the shipping state data
	 *
	 * @return data
	*/
	public function get_shipping_state_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_shipping_state_row) ? $display_data->display_sales_by_shipping_state_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;		

		$sales_by_shipping_state_orders = $wpdb->get_results( $wpdb->prepare( 
			"SELECT
				country_meta.meta_value as country,
				state_meta.meta_value as state,					
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS country_meta ON(order_stats.order_id = country_meta.post_id)
			LEFT JOIN
				{$wpdb->postmeta} AS state_meta ON(order_stats.order_id = state_meta.post_id)
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND country_meta.meta_key IN ( '_shipping_country')
				AND state_meta.meta_key IN ( '_shipping_state')
			GROUP BY state
			ORDER BY total_sales DESC", $end_date, $start_date
		) );
		
		$total = array_sum( array_column( $sales_by_shipping_state_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_shipping_state_orders, 0, $limit_row) as $state_sales ) {
			$array = array( 
				wp_kses_post(WC()->countries->get_states( $state_sales->country )[$state_sales->state]),
				wp_kses_post($state_sales->total_orders) . ' (' . round( ( $state_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($state_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_shipping_state_orders = array( 
			'title' => __('Sales By Shipping State', 'sales-report-email-pro'),
			'th' => array(
				__('State', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_shipping_state_orders;
		
	}
	
	/**
	 * Get the billing city data
	 *
	 * @return data
	*/
	public function get_billing_city_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_billing_city_row) ? $display_data->display_sales_by_billing_city_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;		

		$sales_by_city_orders = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				city_meta.meta_value as city,
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS city_meta ON(order_stats.order_id = city_meta.post_id)
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND city_meta.meta_key IN ( '_billing_city')
			GROUP BY city
			ORDER BY total_sales DESC", $end_date, $start_date
		) );
		
		$total = array_sum( array_column( $sales_by_city_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_city_orders, 0, $limit_row) as $city_sales ) {
			$array = array( 
				wp_kses_post(ucfirst($city_sales->city)),
				wp_kses_post($city_sales->total_orders) . ' (' . round( ( $city_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($city_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_city_orders = array( 
			'title' => __('Sales By Billing City', 'sales-report-email-pro'),
			'th' => array(
				__('City', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_city_orders;
		
	}
	
	/**
	 * Get the shipping city data
	 *
	 * @return data
	*/
	public function get_shipping_city_reports( $date_range, $display_data ) {
		
		// Get setting data
		$data = asre_pro()->admin->get_email_data();
		$limit_row = !empty($display_data->display_sales_by_shipping_city_row) ? $display_data->display_sales_by_shipping_city_row : 5;
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;

		$sales_by_city_orders = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				city_meta.meta_value as city,
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS city_meta ON(order_stats.order_id = city_meta.post_id)
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND city_meta.meta_key IN ( '_shipping_city')
			GROUP BY city
			ORDER BY total_sales DESC", $end_date, $start_date 
		) );
		
		$total = array_sum( array_column( $sales_by_city_orders, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( array_slice($sales_by_city_orders, 0, $limit_row) as $city_sales ) {
			$array = array( 
				wp_kses_post(ucfirst($city_sales->city)),
				wp_kses_post($city_sales->total_orders) . ' (' . round( ( $city_sales->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($city_sales->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$sales_by_city_orders = array( 
			'title' => __('Sales By Shipping City', 'sales-report-email-pro'),
			'th' => array(
				__('City', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $sales_by_city_orders;
		
	}
	
	/**
	 * Get the order status data
	 *
	 * @return data
	*/
	public function get_order_status_reports( $date_range ) {
		
		// Get setting data
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;

		$orders_byStatus = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				order_stats.status as status,					
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
			GROUP BY status
			ORDER BY total_sales DESC", $end_date, $start_date
		) );
		
		$total = array_sum( array_column( $orders_byStatus, 'total_orders' ) );
		
		//new array create for html
		$td_array = array();
		foreach ( $orders_byStatus as $Status ) {
			$order_statuses = wc_get_order_statuses();
			if ( empty($order_statuses[$Status->status]) ) {
				continue;
			}
			$array = array( 
				wp_kses_post('<a href=' . admin_url('edit.php?post_status=' . $Status->status . '&post_type=shop_order') . ' target="_blank">' . $order_statuses[$Status->status] . '</a>'),
				wp_kses_post($Status->total_orders) . ' (' . round( ( $Status->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($Status->net_total)),
			);
			array_push($td_array, $array);
		}
		
		$orders_byStatus = array( 
			'title' => __('Sales By Order Status', 'sales-report-email-pro'),
			'th' => array(
				__('Order Status', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		
		return $orders_byStatus;
		
	}

	/**
	 * Get the order status data
	 *
	 * @return data
	*/
	public function get_order_details_reports( $date_range ) {
		
		// Get setting data
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		global $wpdb;
			
		$orders_data = $wpdb->get_results( $wpdb->prepare( 
			"SELECT
				order_stats.order_id as order_id,
				order_stats.total_sales as total_sales,
				order_stats.net_total as net_total
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			WHERE
				order_stats.date_created <= %s 
				AND order_stats.date_created >= %s
			ORDER BY total_sales DESC", $end_date, $start_date 
		) );
		
		//new array create for html
		$td_array = array();
		foreach ( $orders_data as $order ) {

			$order_id = $order->order_id;
			$Order = wc_get_order( $order_id );

			if ( is_a( $Order, 'WC_Order_Refund' ) ) {
				$Order = wc_get_order( $Order->get_parent_id() );
			}
			
			if ( empty($Order) || 'trash' == $Order->get_status() ) {
				continue;
			}

			$customerName = isset($Order) ? $Order->get_formatted_billing_full_name() : '';

			$array = array( 
				wp_kses_post($Order->get_order_number()),
				wp_kses_post($customerName),
				wp_kses_post(wc_price($order->net_total)),
				wp_kses_post(wc_price($order->total_sales)),
			);

			$array = apply_filters( 'sre_order_details_table_content', $array, $Order );

			array_push($td_array, $array);
		}
		
		$th_array = array(
			__('Order id', 'sales-report-email-pro'),
			__('Customer Name', 'sales-report-email-pro'),
			__('Net Sales', 'sales-report-email-pro'),
			__('Total Sales', 'sales-report-email-pro')
		);

		$th_array = apply_filters( 'sre_order_details_table_header', $th_array );
		
		$orders_data = array(
			'title' => __('Report by Order details', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Forders&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => $th_array,
			'td' => $td_array
		);

		return $orders_data;
		
	}

	/**
	 * Get the report data for refunded orders within a date range.
	 *
	 * @param stdClass $date_range Object containing start_date and end_date DateTime properties
	 * @return array Report data for refunded order details
	 */
	public function get_refund_order_details( $date_range ) {
		// Validate date range
		if ( $date_range->start_date > $date_range->end_date ) {
			// Handle invalid date range error (optional)
			return;
		}
	
		global $wpdb;
	
		// Get formatted date strings
		$start_date = $date_range->start_date->format('Y-m-d H:i:s');
		$end_date = $date_range->end_date->format('Y-m-d H:i:s');
	
		// SQL query to retrieve refunded order statistics within date range
		$query = $wpdb->prepare(
			"SELECT
				order_stats.order_id as order_id,
				order_stats.total_sales as total_sales,
				order_stats.net_total as net_total
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			WHERE
				order_stats.date_created <= %s 
				AND order_stats.date_created >= %s
				AND order_stats.net_total < 0  -- Add condition for net_total < 0
			ORDER BY total_sales DESC", $end_date, $start_date 
		);
	
		// Execute SQL query and retrieve results
		$orders_data = $wpdb->get_results($query);
	
		//new array create for html
		$td_array = array();
		foreach ( $orders_data as $order ) {
			$order_id = $order->order_id;
			$Order = wc_get_order( $order_id );

			if ( is_a( $Order, 'WC_Order_Refund' ) ) {
				$Order = wc_get_order( $Order->get_parent_id() );
			}
			
			if ( empty($Order) || 'trash' == $Order->get_status() ) {
				continue;
			}

			$customerName = isset($Order) ? $Order->get_formatted_billing_full_name() : '';

			$array = array( 
				wp_kses_post($Order->get_order_number()),
				wp_kses_post($customerName),
				wp_kses_post(wc_price($order->net_total)),
				// wp_kses_post(wc_price($order->total_sales)),
			);

			$array = apply_filters( 'sre_order_details_table_content', $array, $Order );

			array_push($td_array, $array);
		}

		$th_array = array(
			__('Order id', 'sales-report-email-pro' ),
			__('Customer Name', 'sales-report-email-pro' ),
			__('Total Refund', 'sales-report-email-pro' )
		);
		
		$th_array = apply_filters( 'sre_refunded_order_details_table_header', $th_array );
		
		$orders_data = array( 
			'title' => __('Report by Refund Order details', 'sales-report-email-pro'),
			'wc_analytics_link'	=> admin_url('admin.php?page=wc-admin&path=%2Fanalytics%2Forders&period=custom&compare=previous_period&after=' . $start_date . '&before=' . $end_date ),
			'th' => $th_array,
			'td' => $td_array
		);

		return $orders_data;
	}
	
	
	
	/**
	 * Get the total number of refunds within a date range.
	 *
	 * @param stdClass $date_range Object containing start_date and end_date DateTime properties
	 * @return int Total number of refunds
	 */
	public function get_total_refunds_number( $date_range ) {
		// Ensure that $date_range is a valid object with expected properties
		if ( ! is_object( $date_range ) || ! isset( $date_range->start_date, $date_range->end_date ) ) {
			return 0; // Return 0 or handle error if date range is invalid
		}

		// Retrieve start and end dates from $date_range object
		$start_date_formatted = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date_formatted = $date_range->end_date->format( 'Y-m-d H:i:s' );

		global $wpdb;

		// Query to count refunds within the specified date range
		$refund_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(ID)
			FROM {$wpdb->prefix}posts
			WHERE post_type = 'shop_order_refund'
			AND post_status = 'wc-completed'
			AND post_date >= %s
			AND post_date <= %s",
			$start_date_formatted,
			$end_date_formatted
		) );

		return intval( $refund_count ); // Return the total number of refunds as an integer
	}

	/**
	 * Get the payment methods data
	 *
	 * @return data
	*/
	public function get_payment_methods_reports( $date_range ) {
		// Get setting data
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		global $wpdb;
		$salse_by_payment_method = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				payment_method_meta.meta_value as payment_method,
				SUM( order_stats.total_sales ) as total_sales,
				SUM( order_stats.net_total ) as net_total,
				COUNT(DISTINCT order_stats.order_id) AS total_orders
			FROM
				{$wpdb->prefix}wc_order_stats as order_stats
			LEFT JOIN
				{$wpdb->postmeta} AS payment_method_meta ON(order_stats.order_id = payment_method_meta.post_id)
			WHERE
				1=1
				AND order_stats.status NOT IN ( 'wc-trash', 'wc-failed', 'wc-cancelled' )
				AND order_stats.date_created <= %s
				AND order_stats.date_created >= %s
				AND payment_method_meta.meta_key IN ( '_payment_method')
			GROUP BY payment_method
			ORDER BY total_sales DESC", $end_date, $start_date
		) );
		$total = array_sum( array_column( $salse_by_payment_method, 'total_orders' ) );
		//new array create for html
		$td_array = array();
		foreach ( $salse_by_payment_method as $payment_method ) {
			$payment_method_title = WC()->payment_gateways()->payment_gateways()[ $payment_method->payment_method ];
			$array = array(
				wp_kses_post($payment_method_title->title . ' (' . $payment_method->payment_method) . ')',
				wp_kses_post($payment_method->total_orders) . ' (' . round( ( $payment_method->total_orders*100 )/$total ) . '%)',
				wp_kses_post(wc_price($payment_method->net_total)),
				wp_kses_post(wc_price($payment_method->total_sales)),
			);
			array_push($td_array, $array);
		}
		$salse_by_payment_method = array(
			'title' => __('Sales By Payment Method', 'sales-report-email-pro'),
			'th' => array(
				__('Payment methods', 'sales-report-email-pro'),
				__('Orders', 'sales-report-email-pro'),
				__('Net Sales', 'sales-report-email-pro'),
				__('Total Sales', 'sales-report-email-pro')
			),
			'td' => $td_array
		);
		return $salse_by_payment_method;
	}
	
	public function normalize_order_status( $status ) {
		$status = trim( $status );
		return 'wc-' . $status;
	}

	
	
	/*** WC Subcription reports data ***/
	
	/**
	 * Get the total active subscribers
	 *
	 * @return data
	*/
	public function get_total_active_subscribers( $date_range ) {
		
		global $wpdb;
		$subscriptions = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				post_status, COUNT( * ) as num_posts
			FROM
				{$wpdb->posts}
			WHERE
			post_type = %s AND post_status = %s GROUP BY post_status", 'shop_subscription', 'wc-active'
		) );
		
		return $subscriptions[0]->num_posts;

	}
	
	/**
	 * Get the total signup subscribers
	 *
	 * @return data
	*/
	public function get_total_signup_subscribers( $date_range ) {
		
		global $wpdb;
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );

		// Subscription signups data
		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT wcsubs.ID) AS count
				FROM {$wpdb->posts} AS wcsubs
				INNER JOIN {$wpdb->posts} AS wcorder
					ON wcsubs.post_parent = wcorder.ID
				WHERE wcorder.post_type IN ( 'shop_order' )
					AND wcsubs.post_type IN ( 'shop_subscription' )
					AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' )
					AND wcorder.post_date >= %s
					AND wcorder.post_date < %s",
			$start_date,
			$end_date
		);

		$query_hash = md5( $query );
		$cached_results = array();

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'woocommerce_subscription_dashboard_status_widget_signup_query', $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(DISTINCT wcsubs.ID) AS count
					FROM {$wpdb->posts} AS wcsubs
					INNER JOIN {$wpdb->posts} AS wcorder
						ON wcsubs.post_parent = wcorder.ID
					WHERE wcorder.post_type IN ( 'shop_order' )
						AND wcsubs.post_type IN ( 'shop_subscription' )
						AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' )
						AND wcorder.post_date >= %s
						AND wcorder.post_date < %s",
				$start_date,
				$end_date
			) ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, WEEK_IN_SECONDS );
		}

		$signup_count = $cached_results[ $query_hash ];
		
		return $signup_count;
		
	}
	
	/**
	 * Get the total signup revenue
	 *
	 * @return data
	*/
	public function get_total_signup_revenue( $date_range ) {
		
		global $wpdb;
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		// Signup revenue this month
		$query = $wpdb->prepare(
			"SELECT SUM(order_total_meta.meta_value)
				FROM {$wpdb->postmeta} AS order_total_meta
					RIGHT JOIN
					(
						SELECT DISTINCT wcorder.ID
						FROM {$wpdb->posts} AS wcsubs
						INNER JOIN {$wpdb->posts} AS wcorder
							ON wcsubs.post_parent = wcorder.ID
						WHERE wcorder.post_type IN ( 'shop_order' )
							AND wcsubs.post_type IN ( 'shop_subscription' )
							AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' )
							AND wcorder.post_date >= %s
							AND wcorder.post_date < %s
					) AS orders ON orders.ID = order_total_meta.post_id
				WHERE order_total_meta.meta_key = '_order_total'",
			$start_date,
			$end_date
		);

		$query_hash = md5( $query );
		$cached_results = array();

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'woocommerce_subscription_dashboard_status_widget_signup_revenue_query', $wpdb->get_var( $wpdb->prepare(
				"SELECT SUM(order_total_meta.meta_value)
					FROM {$wpdb->postmeta} AS order_total_meta
						RIGHT JOIN
						(
							SELECT DISTINCT wcorder.ID
							FROM {$wpdb->posts} AS wcsubs
							INNER JOIN {$wpdb->posts} AS wcorder
								ON wcsubs.post_parent = wcorder.ID
							WHERE wcorder.post_type IN ( 'shop_order' )
								AND wcsubs.post_type IN ( 'shop_subscription' )
								AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded' )
								AND wcorder.post_date >= %s
								AND wcorder.post_date < %s
						) AS orders ON orders.ID = order_total_meta.post_id
					WHERE order_total_meta.meta_key = '_order_total'",
				$start_date,
				$end_date
			) ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, HOUR_IN_SECONDS );
		}

		$signup_revenue = $cached_results[ $query_hash ];
		
		return $signup_revenue;
		
	}
	
	/**
	 * Get the total renewal subscribers
	 *
	 * @return data
	*/
	public function get_total_renewal_subscribers( $date_range ) {
		
		global $wpdb;
		$default_args = array(
		'no_cache'     => false,
		'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );

		// Subscription renewal data
		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT wcorder.ID) AS count
				FROM {$wpdb->posts} AS wcorder
				INNER JOIN {$wpdb->postmeta} AS meta__subscription_renewal
					ON (
						wcorder.id = meta__subscription_renewal.post_id
						AND
						meta__subscription_renewal.meta_key = '_subscription_renewal'
					)
				WHERE wcorder.post_type IN ( 'shop_order' )
					AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
					AND wcorder.post_date >= %s
					AND wcorder.post_date < %s",
			$start_date,
			$end_date
		);

		$query_hash = md5( $query );
		$cached_results = array();

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'woocommerce_subscription_dashboard_status_widget_renewal_query', $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(DISTINCT wcorder.ID) AS count
					FROM {$wpdb->posts} AS wcorder
					INNER JOIN {$wpdb->postmeta} AS meta__subscription_renewal
						ON (
							wcorder.id = meta__subscription_renewal.post_id
							AND
							meta__subscription_renewal.meta_key = '_subscription_renewal'
						)
					WHERE wcorder.post_type IN ( 'shop_order' )
						AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
						AND wcorder.post_date >= %s
						AND wcorder.post_date < %s",
				$start_date,
				$end_date
			) ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, HOUR_IN_SECONDS );
		}

		$renewal_count = $cached_results[ $query_hash ];
		
		return $renewal_count;
		
	}
	
	/**
	 * Get the total renewal revenue
	 *
	 * @return data
	*/
	public function get_total_renewal_revenue( $date_range ) {
		
		global $wpdb;
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );
		
		// Renewal revenue data
			$query = $wpdb->prepare(
			"SELECT SUM(order_total_meta.meta_value)
				FROM {$wpdb->postmeta} as order_total_meta
				RIGHT JOIN
				(
					SELECT DISTINCT wcorder.ID
					FROM {$wpdb->posts} AS wcorder
					INNER JOIN {$wpdb->postmeta} AS meta__subscription_renewal
						ON (
							wcorder.id = meta__subscription_renewal.post_id
							AND
							meta__subscription_renewal.meta_key = '_subscription_renewal'
						)
					WHERE wcorder.post_type IN ( 'shop_order' )
						AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
						AND wcorder.post_date >= %s
						AND wcorder.post_date < %s
				) AS orders ON orders.ID = order_total_meta.post_id
				WHERE order_total_meta.meta_key = '_order_total'",
			$start_date,
			$end_date
		);

		$query_hash = md5( $query );
		$cached_results = array();

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'woocommerce_subscription_dashboard_status_widget_renewal_revenue_query', $wpdb->get_var( $wpdb->prepare(
				"SELECT SUM(order_total_meta.meta_value)
					FROM {$wpdb->postmeta} as order_total_meta
					RIGHT JOIN
					(
						SELECT DISTINCT wcorder.ID
						FROM {$wpdb->posts} AS wcorder
						INNER JOIN {$wpdb->postmeta} AS meta__subscription_renewal
							ON (
								wcorder.id = meta__subscription_renewal.post_id
								AND
								meta__subscription_renewal.meta_key = '_subscription_renewal'
							)
						WHERE wcorder.post_type IN ( 'shop_order' )
							AND wcorder.post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
							AND wcorder.post_date >= %s
							AND wcorder.post_date < %s
					) AS orders ON orders.ID = order_total_meta.post_id
					WHERE order_total_meta.meta_key = '_order_total'",
				$start_date,
				$end_date
			) ) );
			set_transient( strtolower( __CLASS__ ), $cached_results, HOUR_IN_SECONDS );
		}

		$renewal_revenue = $cached_results[ $query_hash ];
		
		return $renewal_revenue;
		
	}
	
	/**
	 * Get the total switch subscribers
	 *
	 * @return data
	*/
	public function get_total_switch_subscribers( $date_range ) {
		
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		// Create a Report Manager object
		$report_manager = new ASRE_Report_Manager( $date_range );

		/*
		* Switch orders
		*/	
		$SwitchOrders = $report_manager->get_order_report_data(
			array(
				'data' => array(
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'_subscription_switch' => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'switch_orders',
					),
					'_order_total' => array(
						'type'      => 'meta',
						'function'  => 'SUM',
						'name'      => 'switch_totals',
						'join_type' => 'LEFT',   // To avoid issues if there is no switch_total meta
					),
				),
				'where' => array(
					'post_status' => array(
						'key'      => 'post_status',
						'operator' => 'NOT IN',
						'value'    => array( 'trash', 'auto-draft' ),
					),
				),
				'order_status'        => $args['order_status'],
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => wc_get_order_types( 'order-count' ),
				'nocache'             => $args['no_cache'],
			)
		);
		foreach ($SwitchOrders as $switch_order) {
			return $switch_order->count;
		}
		
	}
	
	/**
	 * Get the total switch revenue
	 *
	 * @return data
	*/
	public function get_total_switch_revenue( $date_range ) {
		
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
				
		// Create a Report Manager object
		$report_manager = new ASRE_Report_Manager( $date_range );

		/*
		* Switch orders
		*/	
		$SwitchOrders = $report_manager->get_order_report_data(
			array(
				'data' => array(
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'_subscription_switch' => array(
						'type'     => 'meta',
						'function' => '',
						'name'     => 'switch_orders',
					),
					'_order_total' => array(
						'type'      => 'meta',
						'function'  => 'SUM',
						'name'      => 'switch_totals',
						'join_type' => 'LEFT',   // To avoid issues if there is no switch_total meta
					),
				),
				'where' => array(
					'post_status' => array(
						'key'      => 'post_status',
						'operator' => 'NOT IN',
						'value'    => array( 'trash', 'auto-draft' ),
					),
				),
				'order_status'        => $args['order_status'],
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => wc_get_order_types( 'order-count' ),
				'nocache'             => $args['no_cache'],
				//'debug'             => true,
			)
		);

		foreach ($SwitchOrders as $switch_order) {
			return $switch_order->switch_totals;
		}
		
	}
	
	/**
	 * Get the total resubscribe subscribers
	 *
	 * @return data
	*/
	public function get_total_resubscribe_subscribers( $date_range ) {
		
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		// Create a Report Manager object
		$report_manager = new ASRE_Report_Manager( $date_range );
		
		/*
		* resubscribe orders
		*/	
		$ResubscribeOrders = $report_manager->get_order_report_data(
			array(
				'data' => array(
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'id' => array(
						'type'     => 'post_data',
						'function' => 'GROUP_CONCAT',
						'name'     => 'order_ids',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'_subscription_resubscribe' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'resubscribe_orders',
					),
					'_order_total' => array(
						'type'      => 'meta',
						'function'  => 'SUM',
						'name'      => 'resubscribe_totals',
						'join_type' => 'LEFT',   // To avoid issues if there is no resubscribe_total meta
						),
				),
				'where' => array(
					'post_status' => array(
						'key'      => 'post_status',
						'operator' => 'NOT IN',
						'value'    => array( 'trash', 'auto-draft' ),
					),
				),
				'order_status'        => $args['order_status'],
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => wc_get_order_types( 'order-count' ),
				'nocache'             => $args['no_cache'],
			)
		);
	
		foreach ($ResubscribeOrders as $Resubscribe_orders) {
			return $Resubscribe_orders->count;
		}
		
	}
	
	/**
	 * Get the total resubscribe revenue
	 *
	 * @return data
	*/
	public function get_total_resubscribe_revenue( $date_range ) {
		
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		
		// Create a Report Manager object
		$report_manager = new ASRE_Report_Manager( $date_range );
		
		/*
		* resubscribe orders
		*/	
		$ResubscribeOrders = $report_manager->get_order_report_data(
			array(
				'data' => array(
					'ID' => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'id' => array(
						'type'     => 'post_data',
						'function' => 'GROUP_CONCAT',
						'name'     => 'order_ids',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
					'_subscription_resubscribe' => array(
						'type'      => 'meta',
						'function'  => '',
						'name'      => 'resubscribe_orders',
					),
					'_order_total' => array(
						'type'      => 'meta',
						'function'  => 'SUM',
						'name'      => 'resubscribe_totals',
						'join_type' => 'LEFT',   // To avoid issues if there is no resubscribe_total meta
						),
				),
				'where' => array(
					'post_status' => array(
						'key'      => 'post_status',
						'operator' => 'NOT IN',
						'value'    => array( 'trash', 'auto-draft' ),
					),
				),
				'order_status'        => $args['order_status'],
				'order_by'            => 'post_date ASC',
				'query_type'          => 'get_results',
				'filter_range'        => true,
				'order_types'         => wc_get_order_types( 'order-count' ),
				'nocache'             => $args['no_cache'],
			)
		);

		foreach ($ResubscribeOrders as $Resubscribe_orders) {
			return $Resubscribe_orders->resubscribe_totals;
		}
		
	}
	
	/**
	 * Get the total cancellation subscribers
	 *
	 * @return data
	*/
	public function get_total_cancellation_subscribers( $date_range ) {
		
		global $wpdb;
		$update_cache = false;
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		$offset         = get_option( 'gmt_offset' );
		// Convert from Decimal format(eg. 11.5) to a suitable format(eg. +11:30) for CONVERT_TZ() of SQL query.
		$site_timezone = sprintf( '%+02d:%02d', (int) $offset, ( $offset - floor( $offset ) ) * 60 );
		
		$cached_results = get_transient( strtolower( get_class( $this ) ) );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );

		/*
		 * Subscription cancellations
		 */
		$query = $wpdb->prepare(
			"SELECT COUNT( DISTINCT wcsubs.ID ) as count, CONVERT_TZ( wcsmeta_cancel.meta_value, '+00:00', %s ) as cancel_date, GROUP_CONCAT( DISTINCT wcsubs.ID ) as subscription_ids
				FROM {$wpdb->posts} as wcsubs
				JOIN {$wpdb->postmeta} AS wcsmeta_cancel
					ON wcsubs.ID = wcsmeta_cancel.post_id
					AND wcsmeta_cancel.meta_key = %s
					AND wcsubs.post_status NOT IN ( 'trash', 'auto-draft' )
				GROUP BY YEAR( cancel_date ), MONTH( cancel_date ), DAY( cancel_date )
				HAVING cancel_date BETWEEN %s AND %s
				ORDER BY wcsmeta_cancel.meta_value ASC",
			$site_timezone,
			wcs_get_date_meta_key( 'cancelled' ),
			$start_date,
			$end_date
		);
		
		$query_hash = md5( $query );

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'wcs_reports_subscription_events_cancel_count_data', (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT COUNT( DISTINCT wcsubs.ID ) as count, CONVERT_TZ( wcsmeta_cancel.meta_value, '+00:00', %s ) as cancel_date, GROUP_CONCAT( DISTINCT wcsubs.ID ) as subscription_ids
					FROM {$wpdb->posts} as wcsubs
					JOIN {$wpdb->postmeta} AS wcsmeta_cancel
						ON wcsubs.ID = wcsmeta_cancel.post_id
						AND wcsmeta_cancel.meta_key = %s
						AND wcsubs.post_status NOT IN ( 'trash', 'auto-draft' )
					GROUP BY YEAR( cancel_date ), MONTH( cancel_date ), DAY( cancel_date )
					HAVING cancel_date BETWEEN %s AND %s
					ORDER BY wcsmeta_cancel.meta_value ASC",
				$site_timezone,
				wcs_get_date_meta_key( 'cancelled' ),
				$start_date,
				$end_date
			) ), $args );
			$update_cache = true;
			
		}

		$cancellation_count = $cached_results[ $query_hash ];
		
		$cancellation_count = absint( array_sum( wp_list_pluck( $cancellation_count, 'count' ) ) );
		
		return $cancellation_count;
		
	}
	
	/**
	 * Get the total net subscribers gain
	 *
	 * @return data
	*/
	public function get_total_net_subscribers_gain( $date_range ) {
		
		global $wpdb;
		$update_cache = false;
		$default_args = array(
			'no_cache'     => false,
			'order_status' => apply_filters( 'woocommerce_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ),
		);
		$args = array();
		$args = apply_filters( 'wcs_reports_subscription_events_args', $args );
		$args = wp_parse_args( $args, $default_args );
		$offset         = get_option( 'gmt_offset' );
		// Convert from Decimal format(eg. 11.5) to a suitable format(eg. +11:30) for CONVERT_TZ() of SQL query.
		$site_timezone = sprintf( '%+02d:%02d', (int) $offset, ( $offset - floor( $offset ) ) * 60 );
		
		$cached_results = get_transient( strtolower( get_class( $this ) ) );
		
		$start_date = $date_range->start_date->format( 'Y-m-d H:i:s' );
		$end_date = $date_range->end_date->format( 'Y-m-d H:i:s' );

		/*
		 * Subscribers by date
		 */
		$query = $wpdb->prepare(
			"SELECT searchdate.Date as date, COUNT( DISTINCT wcsubs.ID) as count, GROUP_CONCAT( DISTINCT wcsubs.ID ) as subscription_ids
				FROM (
					SELECT DATE(last_thousand_days.Date) as Date
					FROM (
						SELECT DATE(%s) - INTERVAL(units.digit + (10 * tens.digit) + (100 * hundreds.digit)) DAY as Date
						FROM (
							SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
							UNION ALL SELECT 3 UNION ALL SELECT 4
							UNION ALL SELECT 5 UNION ALL SELECT 6
							UNION ALL SELECT 7 UNION ALL SELECT 8
							UNION ALL SELECT 9
						) as units
						CROSS JOIN (
							SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
							UNION ALL SELECT 3 UNION ALL SELECT 4
							UNION ALL SELECT 5 UNION ALL SELECT 6
							UNION ALL SELECT 7 UNION ALL SELECT 8
							UNION ALL SELECT 9
						) as tens
						CROSS JOIN (
							SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
							UNION ALL SELECT 3 UNION ALL SELECT 4
							UNION ALL SELECT 5 UNION ALL SELECT 6
							UNION ALL SELECT 7 UNION ALL SELECT 8
							UNION ALL SELECT 9
						) AS hundreds
					) last_thousand_days
					WHERE last_thousand_days.Date >= %s AND last_thousand_days.Date <= %s
				) searchdate,
					{$wpdb->posts} AS wcsubs,
					{$wpdb->postmeta} AS wcsmeta
					WHERE wcsubs.ID = wcsmeta.post_id AND wcsmeta.meta_key = %s
						AND DATE( wcsubs.post_date ) <= searchdate.Date
						AND wcsubs.post_type IN ( 'shop_subscription' )
						AND wcsubs.post_status NOT IN( 'auto-draft' )
						AND (
							DATE( CONVERT_TZ( wcsmeta.meta_value , '+00:00', %s ) ) >= searchdate.Date
							OR wcsmeta.meta_value = 0
							OR wcsmeta.meta_value IS NULL
						)
					GROUP BY searchdate.Date
					ORDER BY searchdate.Date ASC",
			$end_date,
			$start_date,
			$end_date,
			wcs_get_date_meta_key( 'end' ),
			$site_timezone
		);

		$query_hash = md5( $query );

		if ( $args['no_cache'] || false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
			$wpdb->query( 'SET SESSION SQL_BIG_SELECTS=1' );
			$cached_results[ $query_hash ] = apply_filters( 'wcs_reports_subscription_events_subscriber_count_data', (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT searchdate.Date as date, COUNT( DISTINCT wcsubs.ID) as count, GROUP_CONCAT( DISTINCT wcsubs.ID ) as subscription_ids
					FROM (
						SELECT DATE(last_thousand_days.Date) as Date
						FROM (
							SELECT DATE(%s) - INTERVAL(units.digit + (10 * tens.digit) + (100 * hundreds.digit)) DAY as Date
							FROM (
								SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
								UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6
								UNION ALL SELECT 7 UNION ALL SELECT 8
								UNION ALL SELECT 9
							) as units
							CROSS JOIN (
								SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
								UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6
								UNION ALL SELECT 7 UNION ALL SELECT 8
								UNION ALL SELECT 9
							) as tens
							CROSS JOIN (
								SELECT 0 AS digit UNION ALL SELECT 1 UNION ALL SELECT 2
								UNION ALL SELECT 3 UNION ALL SELECT 4
								UNION ALL SELECT 5 UNION ALL SELECT 6
								UNION ALL SELECT 7 UNION ALL SELECT 8
								UNION ALL SELECT 9
							) AS hundreds
						) last_thousand_days
						WHERE last_thousand_days.Date >= %s AND last_thousand_days.Date <= %s
					) searchdate,
						{$wpdb->posts} AS wcsubs,
						{$wpdb->postmeta} AS wcsmeta
						WHERE wcsubs.ID = wcsmeta.post_id AND wcsmeta.meta_key = %s
							AND DATE( wcsubs.post_date ) <= searchdate.Date
							AND wcsubs.post_type IN ( 'shop_subscription' )
							AND wcsubs.post_status NOT IN( 'auto-draft' )
							AND (
								DATE( CONVERT_TZ( wcsmeta.meta_value , '+00:00', %s ) ) >= searchdate.Date
								OR wcsmeta.meta_value = 0
								OR wcsmeta.meta_value IS NULL
							)
						GROUP BY searchdate.Date
						ORDER BY searchdate.Date ASC",
				$end_date,
				$start_date,
				$end_date,
				wcs_get_date_meta_key( 'end' ),
				$site_timezone
			) ), $args );
			$update_cache = true;
		}
		
		$net_count = $cached_results[ $query_hash ];
		$total_subscriptions_at_period_end   = $net_count ? absint( end( $net_count )->count ) : 0;
		$total_subscriptions_at_period_start = isset( $net_count[0]->count ) ? absint( $net_count[0]->count ) : 0;
		
		$subscription_change_count = ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start > 0 ) ? '+' . ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start ) : ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start );
		
		if ( 0 === $total_subscriptions_at_period_start ) {
			$subscription_change_percent = '&#x221e;%'; // infinite percentage increase if the starting subs is 0
		} elseif ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start >= 0 ) {
			$subscription_change_percent = '+' . number_format( ( ( ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start ) / $total_subscriptions_at_period_start ) * 100 ), 2 ) . '%';
		} else {
			$subscription_change_percent = number_format( ( ( ( $total_subscriptions_at_period_end - $total_subscriptions_at_period_start ) / $total_subscriptions_at_period_start ) * 100 ), 2 ) . '%';
		}
		
		$net_gain = $subscription_change_count;
		
		return $net_gain;
		
	}
	
	/**
	 * Get the Subscriptions By Status (Total) data
	 *
	 * @return data
	*/
	public function get_subscription_by_status_reports( $date_range ) {
		
		global $wpdb;
		$subscriptions = $wpdb->get_results( $wpdb->prepare(
			"SELECT
				post_status, COUNT( * ) as num_posts
			FROM
				{$wpdb->posts}
			WHERE
			post_type = %s GROUP BY post_status", 'shop_subscription'
		) );
		
		//new array create for html
		$td_array = array();
		foreach ( $subscriptions as $key => $value ) {
			if ( 'trash' == $value->post_status ) {
				continue;
			}
			$order_statuses = wc_get_order_statuses();
			$statusName = !empty($order_statuses[$value->post_status]) ? $order_statuses[$value->post_status] : str_replace('-', ' ', substr( $value->post_status, 3 ) );
			$array = array( 
				wp_kses_post(ucfirst($statusName)),
				wp_kses_post($value->num_posts)
			);
			array_push($td_array, $array);
		}
		
		$subscription_by_status = array( 
			'title' => 'Subscriptions By Status (Total)',
			'th' => array( 'Status', 'Total' ),
			'td' => $td_array
		);
		
		return $subscription_by_status;
		
	}
	
}
