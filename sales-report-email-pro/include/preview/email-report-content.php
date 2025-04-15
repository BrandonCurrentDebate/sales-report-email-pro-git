<?php
/**
 * Sales report email preview
 *
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
$additional_content = isset($data->email_content) ? $data->email_content : '';
$email_content = str_replace('{site_title}', get_bloginfo( 'name' ), $additional_content );
$general_format = get_option('date_format');
$time_format = get_option('time_format');
if ( 'F j, Y' == $general_format) {
	$date_format = 'M j, Y';
} else { 
	$date_format = $general_format;
}

?>    
<html 
<?php 
if (is_rtl()) {
	echo 'dir="rtl"';
}
?>
>
	<head>
		<meta media="all" name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
			<?php 
			if ( empty($data->email_subject)) {
				echo esc_html(str_replace('{site_title}', get_bloginfo( 'name' ), 'Sales Report for {site_title}' ));
			} else {
				echo esc_html($data->email_subject);
			} 
			?>
		</title>
		<?php if (is_rtl()) { ?>
		<style type="text/css">
		.report-table-widget th {
			text-align: right !important;
		}
		.growth-span {
			float:left !important;
		}
		.sales-report-email-template {
			text-align: right !important;
		}
		.report-table-widget tr td {
			text-align: right !important;
		}
		@media screen and (max-width: 500px) {
			.report-dates td {
				float: left;
				width: 100%;
				font-size: 13px !important;
				text-align: right !important;
				padding-left: 0;
			}
		}
		</style>
		<?php } else { ?>
		<style type="text/css">
		.report-table-widget th {
			text-align: left;
		}

		@media screen and (max-width: 500px) {
			.report-dates td {
				float: left;
				width: 100%;
				font-size: 13px !important;
				text-align: left !important;
				padding-left: 0;
			}
		}
		</style>
		<?php } ?>
		<style type="text/css">
		.sales-report-email-template {
			max-width: 900px;
			margin: 20px auto;
			padding: 70px 0;
			font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
			background-color: #ffffff;
			border: 1px solid #e0e0e0;
			border-radius: 3px;
			padding: 20px;
		}

		.report-heading {
			border-bottom: 1px solid #e0e0e0;
			margin-bottom: 20px;
			display: inline-block;
			width: 100%;
		}

		.report-summary {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			display: table;
			width: 100%;
		}

		.report-widget * {
			line-height: 1.26em;
		}

		.report-widget {
			width: 33.33%;
			min-height: 102px;
			display: inline-block;
			padding: 14px 16px;
			margin-left: 0px;
			background-color: #fafafa;
			border: 1px solid #e0e0e0;
			text-decoration: none;
			box-sizing: border-box;
			float: left;
		}

		.column4 .report-widget {
			width: calc(25% - 0px);
		}

		.report-widget:hover {
			background-color: #f3f4f5;
		}

		.report-summary__item-data {
			margin: 0;
			margin-bottom: 5px;
			float: left;
			width: 100%;
		}

		.report-summary__item-prev-label {
			font-size: 11px;
			color: #555d66;
			display: inline-block;
			width: 100%;
		}

		.report-summary__item-prev-value {
			font-size: 11px;
			color: #555d66;
			display: inline-block;
		}

		.col-6 {
			width: 49%;
			box-sizing: border-box;
			display: inline-block;
			margin-right: 5px;
			vertical-align: top;
		}

		.col-heading {
			margin: 0 0 10px !important;
			display: block;
			margin-bottom: 16px;
			font-size: 11px;
			text-transform: uppercase;
			color: #6c7781;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
		}

		.widget-value {
			margin-bottom: 4px;
			font-size: 18px;
			font-size: 1.125rem;
			font-weight: 600;
			color: #191e23;
			flex: 1 0 auto;
		}

		.report-table-widget tr td img {
			width: 50px;
			height: auto;
			vertical-align: middle;
			margin-right: 10px;
		}

		.report-table-widget {
			border-radius: 0;
			border: 1px solid #e0e0e0;
			box-shadow: none;
			font-size: 14px;
		}

		.report-table-widget tr td {
			border-top: 1px solid #e0e0e0;
			padding: 7px 10px;
			display: table-cell;
			line-height: 1.5;
			color: #333;
		}

		.report-table-widget thead tr th {
			text-align: left;
			padding: 15px 10px;
			border-radius: 0;
		}

		.report-table-widget tbody tr th, .report-table-widget tbody tr td {
			background-color: #fff;
			padding: 7px 10px;
			border-top: 1px solid #e0e0e0;
		}
		
		.report-table-widget tbody tr > td, .report-table-widget tbody tr > th {
			border-left: 0;
		}

		.report-table-widget tbody tr > td ~ td, .report-table-widget tbody tr > th ~ th {
			border-left: 1px solid #e0e0e0;
		}
		
		.report-table-widget tbody tr th {
			background-color: #fff;
			border-top: none;
			font-weight: 600;
			font-size: 14px;
			color: #333;
		}

		.growth-span {
			font-size: 80%;
			margin-left: 10px;
			line-height: 1.7;
		}
		
		.report-table-title {
			margin: 0;
			padding: 10px;
			font-size: 16px;
			font-weight: 600;
			color: #333;
			border: 1px solid #e0e0e0;
			background: #f5f5f5;
			border-bottom: 0;
			margin-top: 20px;
		}

		.report-table-title a {
			float: right;
			color: #2271b1;
			text-decoration: none;
			font-size: 13px;
			line-height: 1.5;
		}

		.report-summary__item-data .woocommerce-summary__item-delta-icon {
			float: right;
			vertical-align: middle;
			margin-right: 3px;
			fill: currentColor;
		}
		
		.growth-span.arrow-down {
			background: #e05b49;
		}

		.growth-span.arrow-up {
			background: #4caf50;
		}
		.growth-span.arrow-equal {
			background: #dddddd;
		}
		.growth-span {
			color: #fff;
			padding: 5px;
			float: right;
			border-radius: 3px;
			line-height: 1;
			background: #4caf50;
		}

		.asre-plugin-logo {
			margin-bottom: 10px;
			max-height: 40px;
		}

		.report-heading {
			display: inline-block;
			width: 100%;
		}
		
		.main-title .report-name {
			font-size: 20px;
			margin: 0;
			float: left;
			margin-top: 10px;
			margin-bottom: 10px;
		}
		.current-date {
			font-size: 14px;
			display: block;
			float: right;
			padding: 13px 0;
		}
		.additonal-content {
			padding-bottom: 20px;
			font-size: 14px;
			display: block;
		}
		
		a {
			color: #2271b1;
		}
		
		@media only screen and (max-width: 1149px) {
			.report-dates td {
				font-size: 15px;
			}
			.report-widget {
				width: 33.33% !important;
			}
		}

		@media only screen and (max-width: 768px) {
			.report-dates td {
				font-size: 15px;
			}
			.col-heading {
				margin: 5px 0 20px !important;
				font-size: 1em !important;
			}
			.report-widget {
				width: 50% !important;
			}
			.report-table-widget {
				width: 100% !important;
			}
			.widget-value {
				font-size: 1.8em !important;
			}
			.report-summary__item-data {
				margin-bottom: 20px !important;
			}
			.growth-span {
				font-size: 100% !important;
			}
			.growth-span img {
				width: 18px !important
			}
			.report-summary__item-prev-label,
			.report-summary__item-prev-value {
				font-size: 1em !important;
				margin-bottom: 5px !important;
			}
		}

		@media only screen and (max-width: 650px) {
			.report-widget {
				width: 50% !important;
			}
			td.current-date {
				float: left;
			}
			.current-date {
				float: left;
				line-height: 1.5;
				padding-top: 0;
			}
			td.previous-date {
				float: left !important;
			}
			p.col-heading {
				font-size: 0.8em !important;
			}
		}

		@media only screen and (max-width: 550px) {
			.sales-report-email-template {
				padding: 10px 10px 20px !important;
			}
			
			.report-widget {
				width: 100% !important;
			}
			.report-table-widget {
				width: 100% !important;
			}
			.col-heading {
				margin: 10px 0 15px !important;
				font-size: 0.7em !important;
			}
			.widget-value {
				font-size: 1.5em !important;
			}
			.report-summary__item-data {
				margin-bottom: 10px !important;
			}
			.growth-span {
				font-size: 80% !important;
			}
			.growth-span img {
				width: 15px !important
			}
			.report-summary__item-prev-label,
			.report-summary__item-prev-value {
				font-size: 1em !important;
			}
			.asre-plugin-logo {
				max-height:40px;
			}
			
			.edit-report-link, .zorem-branding-link {
				display: block;
				text-align: center;
				padding: 5px;
				float: none !important;
			}

		}
		</style>
	</head>
	<body>
		<div class="sales-report-email-template">
			<div class="main-title">
				<?php if ( isset($data->show_header_image) && '1' == $data->show_header_image ) { ?>
					<img src="<?php echo esc_url(apply_filters( 'asre_branding_logo_url', asre_pro()->plugin_dir_url(__FILE__) . 'assets/images/sre-logo.png', $data )); ?>" class="asre-plugin-logo" style="display: block;" alt="" >
				<?php } ?>
				
				<div class="report-heading">
					<span class="report-name" style=""><?php echo esc_html($data->report_name); ?></span>
					<span class="current-date"> 
						<?php esc_html_e('Report dates', 'sales-report-email-pro'); ?>: 
						<?php
						$startday = $date_range->start_date->format('j');
						$endday = $date_range->end_date->format('j');
						$startmonth = $date_range->start_date->format('m');
						$endmonth = $date_range->end_date->format('m');
						$startyear = $date_range->start_date->format('y');
						$endyear = $date_range->end_date->format('y');
						$starttime = $date_range->start_date->format('H:i:s');
						$endtime = $date_range->end_date->format('H:i:s');
						
						if ( $startday != $endday && $startmonth == $endmonth && $startyear == $endyear && '00:00:00' == $starttime &&  '23:59:59' == $endtime  ) {
							echo '<strong>' . esc_html(date_i18n('F j', strtotime($date_range->start_date->format('Y-m-d H:i:s')))) . ' - ' . esc_html(date_i18n('j', strtotime($date_range->end_date->format('Y-m-d H:i:s')))) . ', ' . esc_html(date_i18n('Y', strtotime($date_range->end_date->format('Y-m-d H:i:s')))) . '</strong>';
						} else if ( $startday == $endday && $startmonth == $endmonth && $startyear == $endyear && '00:00:00' == $starttime &&  '23:59:59' == $endtime ) { 
							echo '<strong>' . esc_html(date_i18n('F j', strtotime($date_range->start_date->format('Y-m-d H:i:s')))) . ', ' . esc_html(date_i18n('Y', strtotime($date_range->start_date->format('Y-m-d H:i:s')))) . '</strong>';
						} else {
							?>
							<strong>
							<?php if ( 'daily-overnight' == $data->email_interval ) { ?>
								<?php echo esc_html(date_i18n( 'M j', $date_range->start_date->getTimestamp() )); ?>
							<?php } else { ?>
								<?php echo esc_html(date_i18n( 'M j, Y', $date_range->start_date->getTimestamp() )); ?>
							<?php } ?>
							<?php 
							if ( 'daily-overnight' == $data->email_interval ) {
								echo esc_html(date_i18n( 'H:i:s', $date_range->start_date->getTimestamp() ));
							} 
							?>
							- 
							<?php if ( 'daily-overnight' == $data->email_interval ) { ?>
								<?php echo esc_html(date_i18n( 'M j', $date_range->end_date->getTimestamp() )); ?>
							<?php } else { ?>
								<?php echo esc_html(date_i18n( 'M j, Y', $date_range->end_date->getTimestamp() )); ?>
							<?php } ?>
							<?php 
							if ( 'daily-overnight' == $data->email_interval ) {
								echo esc_html(date_i18n( 'H:i:s', $date_range->end_date->getTimestamp() ));
							} 
							?>
							</strong>
							<?php
						}
						?>
						<?php 
						$previous_startday = $previous_date_range->start_date->format('j');
						$previous_endday = $previous_date_range->end_date->format('j');
						$previous_startmonth = $previous_date_range->start_date->format('m');
						$previous_endmonth = $previous_date_range->end_date->format('m');
						$previous_startyear = $previous_date_range->start_date->format('y');
						$previous_endyear = $previous_date_range->end_date->format('y');
						$previous_starttime = $previous_date_range->start_date->format('H:i:s');
						$previous_endtime = $previous_date_range->end_date->format('H:i:s');
						
						if ( $previous_startday != $previous_endday && $previous_startmonth == $previous_endmonth && $previous_startyear == $previous_endyear && '00:00:00' == $previous_starttime &&  '23:59:59' == $previous_endtime ) {
							if ( isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) {
								echo '('; 
								esc_html_e('vs.', 'woocommerce'); 
								echo ' ' . esc_html(date_i18n( 'F j', $previous_date_range->start_date->getTimestamp() )) . '-' . esc_html(date_i18n( 'j', $previous_date_range->end_date->getTimestamp() )) . ', ' . esc_html(date_i18n( 'Y', $previous_date_range->end_date->getTimestamp() )) . ')'; 
							}
						} else if ( $previous_startday == $previous_endday && $previous_startmonth == $previous_endmonth && $previous_startyear == $previous_endyear && '00:00:00' == $previous_starttime &&  '23:59:59' == $previous_endtime ) {
							if ( isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) {
								echo '('; 
								esc_html_e('vs.', 'woocommerce'); 
								echo ' ' . esc_html(date_i18n( 'F j', $previous_date_range->start_date->getTimestamp() )) . ', ' . esc_html(date_i18n( 'Y', $previous_date_range->end_date->getTimestamp() )) . ')'; 
							}
						} else {
							if ( isset($display_data->display_previous_period) && '1' == $display_data->display_previous_period ) { 
								?>
							<span style="font-size:90%;">
							<?php 
								echo '('; 
								esc_html_e('vs.', 'woocommerce'); 
								?>
							<?php if ( 'daily-overnight' == $data->email_interval ) { ?>
								<?php echo esc_html(date_i18n( 'M j', $previous_date_range->start_date->getTimestamp() )); ?>
							<?php } else { ?>
								<?php echo esc_html(date_i18n( 'M j, Y', $previous_date_range->start_date->getTimestamp() )); ?>
							<?php } ?>
							<?php 
								if ( 'daily-overnight' == $data->email_interval ) { 
									echo esc_html(date_i18n( $time_format, $previous_date_range->start_date->getTimestamp() )); 
								} 
								?>
								- 
								<?php 
								if ('daily-overnight' == $data->email_interval) { 
									echo esc_html(date_i18n( 'M j', $previous_date_range->end_date->getTimestamp() )); 
								} else { 
									echo esc_html(date_i18n( 'M j, Y', $previous_date_range->end_date->getTimestamp() )); 
								} 
								?>
								<?php 
								if ( 'daily-overnight' == $data->email_interval ) { 
									echo esc_html(date_i18n( $time_format, $previous_date_range->end_date->getTimestamp() )); 
								} 
								?>
							)</span>
							<?php } ?>
						<?php } ?>
					</span>
				</div>
			</div>
			<?php if ($email_content) { ?>
				<span class="additonal-content"><?php echo wp_kses_post($email_content); ?></span>
			<?php 
			}
			// action hook
			do_action( 'before_email_report', $date_range );
			
			$report_totals_data = array();
			
			if ( isset($display_data->display_net_sales_this_month) && '1' == $display_data->display_net_sales_this_month && ( 'daily' == $data->email_interval || 'daily-overnight' == $data->email_interval ) ) {
				$report_totals_data['display_net_sales_this_month'] = array ( __('Net Sales This Month', 'sales-report-email-pro'), wc_price($net_salse_this_month), null, null, $data );
			}
			
			if ( isset($display_data->display_gross_sales) && '1' == $display_data->display_gross_sales ) {				
				$report_totals_data['display_gross_sales'] = array ( __('Gross Sales', 'sales-report-email-pro'), wc_price($gross_sales), wc_price($previous_gross_sales), $gross_growth, $data );
			}
			
			if ( isset($display_data->display_total_sales) && '1' == $display_data->display_total_sales ) {
				$report_totals_data['display_total_sales'] = array ( __('Total Sales', 'sales-report-email-pro'), wc_price($total_sales), wc_price($previous_total_sales), $sales_growth, $data );
			}
			
			if ( isset($display_data->display_coupon_used) && '1' == $display_data->display_coupon_used ) {
				$report_totals_data['display_coupon_used'] = array ( __('Net Discount Amount', 'sales-report-email-pro'), wc_price($coupon_used), wc_price($previous_coupon_used), $coupon_growth, $data );
			}

			if ( isset($display_data->display_coupon_count) && '1' == $display_data->display_coupon_count ) {
				$report_totals_data['display_coupon_count'] = array( __('Discounted Orders', 'sales-report-email-pro'), $coupon_count, $previous_coupon_count, $coupon_count_growth, $data );
				
			}
			
			if ( isset($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ) {
				$report_totals_data['display_total_refunds'] = array ( __('Refunds', 'sales-report-email-pro'), wc_price($total_refunds), wc_price($previous_total_refunds), $refund_growth, $data );
			}

			if ( isset( $display_data->display_total_refunds_number ) && '1' == $display_data->display_total_refunds_number ) {
				$report_totals_data['display_total_refunds_number'] = array( __('Refund number', 'sales-report-email-pro'), $refund_number, $previous_refund_number, $refund_number_growth, $data );
			}

			if (wc_tax_enabled()) {
				if ( isset($display_data->display_total_tax) && '1' == $display_data->display_total_tax ) {
					$report_totals_data['display_total_tax'] = array ( __('Taxes', 'sales-report-email-pro'), wc_price($total_taxes), wc_price($previous_total_taxes), $taxes_growth, $data );
				}
				if ( isset($display_data->display_total_shipping_tax) && '1' == $display_data->display_total_shipping_tax ) {
					$report_totals_data['display_total_shipping_tax'] = array ( __('Shipping Tax', 'sales-report-email-pro'), wc_price($total_shipping_tax), wc_price($previous_total_shipping_tax), $shipping_tax_growth, $data );
				}
			}

			if ( isset($display_data->display_total_shipping) && '1' == $display_data->display_total_shipping ) {
				$report_totals_data['display_total_shipping'] = array ( __('Shipping', 'sales-report-email-pro'), wc_price($total_shipping), wc_price($previous_total_shipping), $shipping_growth, $data );
			}

			if ( isset($display_data->display_net_revenue) && '1' == $display_data->display_net_revenue ) {
				$report_totals_data['display_net_revenue'] = array ( __('Net Sales', 'sales-report-email-pro'), wc_price($net_revenue), wc_price($previous_net_revenue), $net_revenue_growth, $data );
			}

			if ( isset($display_data->display_total_orders) && '1' == $display_data->display_total_orders ) {
				$report_totals_data['display_total_orders'] = array ( __('Orders', 'sales-report-email-pro'), $total_orders, $previous_total_orders, $orders_growth, $data );
			}

			if ( isset($display_data->display_total_items) && '1' == $display_data->display_total_items ) {
				$report_totals_data['display_total_items'] = array ( __('Items Sold', 'sales-report-email-pro'), $total_items, $previous_total_items, $items_growth, $data );
			}

			if ( isset($display_data->display_signups) && '1' == $display_data->display_signups ) {
				$report_totals_data['display_signups'] = array ( __('New Customers', 'sales-report-email-pro'), $total_signups, $previous_total_signups, $signup_growth, $data );
			}

			if ( isset($display_data->display_downloads) && '1' == $display_data->display_downloads ) {
				$report_totals_data['display_downloads'] = array ( __('Downloads', 'sales-report-email-pro'), $total_downloads, $previous_total_downloads, $downloads_growth, $data );
			}

			if ( isset($display_data->display_average_order_value) && '1' == $display_data->display_average_order_value ) {
				$report_totals_data['display_average_order_value'] = array ( __('AVG. Order Value', 'sales-report-email-pro'), wc_price($avg_order_value), wc_price($prev_avg_order_value), $avg_order_growth, $data );
			}

			if ( ( isset($display_data->display_average_daily_sales) && '1' == $display_data->display_average_daily_sales ) && 'daily' != $data->email_interval && 'daily-overnight' != $data->email_interval ) { 
				$report_totals_data['display_average_daily_sales'] = array ( __('AVG. Daily Sales', 'sales-report-email-pro'), wc_price($avg_daily_sales), wc_price($prev_avg_daily_sales), $avg_sales_growth, $data );
			}

			if ( isset($display_data->display_average_daily_items) && '1' == $display_data->display_average_daily_items ) {
				$report_totals_data['display_average_daily_items'] = array ( __('AVG. Order Items', 'sales-report-email-pro'), round($avg_order_item, 2), round($prev_avg_order_item, 2), $avg_item_growth, $data );
			}
			
			if ( class_exists( 'WC_Subscriptions_Manager' ) ) {
				if ( isset($display_data->display_active_subscriptions) && '1' == $display_data->display_active_subscriptions ) {
					$report_totals_data['display_active_subscriptions'] = array ( __('Active Subscriptions', 'sales-report-email-pro'), $total_active_subscriber, $previous_total_active_subscriber, $active_subscriber_growth, $data );
				}
	
				if ( isset($display_data->display_signup_subscriptions) && '1' == $display_data->display_signup_subscriptions ) {
					$report_totals_data['display_signup_subscriptions'] = array ( __('Subscriptions signups', 'sales-report-email-pro'), $total_signup_subscriber, $previous_total_signup_subscriber, $signup_subscriber_growth, $data );
				}
	
				if ( isset($display_data->display_signup_revenue) && '1' == $display_data->display_signup_revenue ) {
					$report_totals_data['display_signup_revenue'] = array ( __('Signup Revenue', 'sales-report-email-pro'), wc_price($total_signup_revenue), wc_price($previous_total_signup_revenue), $signup_revenue_growth, $data );
				}
	
				if ( isset($display_data->display_renewal_subscriptions) && '1' == $display_data->display_renewal_subscriptions ) {
					$report_totals_data['display_signup_revenue'] = array ( __('Subscription Renewal', 'sales-report-email-pro'), $total_renewal_subscriber, $previous_total_renewal_subscriber, $renewal_subscriber_growth, $data );
				}
	
				if ( isset($display_data->display_renewal_revenue) && '1' == $display_data->display_renewal_revenue ) {
					$report_totals_data['display_renewal_revenue'] = array ( __('Renewal Revenue', 'sales-report-email-pro'), wc_price($total_renewal_revenue), wc_price($previous_total_renewal_revenue), $renewal_revenue_growth, $data );
				}
	
				if ( isset($display_data->display_switch_subscriptions) && '1' == $display_data->display_switch_subscriptions ) {
					$report_totals_data['display_switch_subscriptions'] = array ( __('Subscription Switch', 'sales-report-email-pro'), round($total_switch_subscriber), round($previous_total_switch_subscriber), $switch_subscriber_growth, $data );
				}
	
				if ( isset($display_data->display_switch_revenue) && '1' == $display_data->display_switch_revenue ) {
					$report_totals_data['display_switch_revenue'] = array ( __('Switch Revenue', 'sales-report-email-pro'), wc_price($total_switch_revenue), wc_price($previous_total_switch_revenue), $switch_revenue_growth, $data );
				}
	
				if ( isset($display_data->display_resubscribe_subscriptions) && '1' == $display_data->display_resubscribe_subscriptions ) {
					$report_totals_data['display_resubscribe_subscriptions'] = array ( __('Subscription Resubscribe', 'sales-report-email-pro'), round($total_resubscribe_subscriber), round($previous_total_resubscribe_subscriber), $resubscribe_subscriber_growth, $data );
				}
	
				if ( isset($display_data->display_resubscribe_revenue) && '1' == $display_data->display_resubscribe_revenue ) {
					$report_totals_data['display_resubscribe_revenue'] = array ( __('Resubscribe Revenue', 'sales-report-email-pro'), wc_price($total_resubscribe_revenue), wc_price($previous_total_resubscribe_revenue), $resubscribe_revenue_growth, $data );
				}
	
				if ( isset($display_data->display_cancellation_subscriptions) && '1' == $display_data->display_cancellation_subscriptions ) {
					$report_totals_data['display_cancellation_subscriptions'] = array ( __('Subscription Cancellation', 'sales-report-email-pro'), $total_cancellation_subscriber, $previous_total_cancellation_subscriber, $cancellation_subscriber_growth, $data );
				}
				
				if ( isset($display_data->display_net_subscription_gain) && '1' == $display_data->display_net_subscription_gain ) {
					$report_totals_data['display_net_subscription_gain'] = array ( __('Net Subscription Gain', 'sales-report-email-pro'), round($total_net_subscription_gain), round($previous_total_net_subscription_gain), $net_subscription_gain_growth, $data );
				}
			}



			$report_totals_data = apply_filters( 'email_reports_widget_data', $report_totals_data, $data, $date_range, $previous_date_range );
			$count_widget = count($report_totals_data);
			$column4 = array( 3, 6, 9 );
			?>
			<div class="report-summary has-6-items 
				<?php 
				if ( !in_array( $count_widget, $column4) ) { 
					echo 'column4'; 
				} 
				?>
				" style="list-style-type:none;text-decoration: none;" >
				<?php				
				$sort_report_totals = !empty($data->report_totals_sort) ? unserialize($data->report_totals_sort) : array_keys((array) $report_totals_data);
				if ( !empty($sort_report_totals)  ) {
					foreach ( $sort_report_totals as $key ) {
						if ( isset($display_data->$key) && '1' == $display_data->$key ) { 
							if (!empty($report_totals_data[$key])) {
								asre_pro()->admin->get_total_report_content( $report_totals_data[$key][0], $report_totals_data[$key][1], $report_totals_data[$key][2], $report_totals_data[$key][3], $display_data );
							}
						}
					}
				}
				
				//trackship data
				do_action( 'sre_view_after_report_total', $data, $date_range, $previous_date_range ); 
				
				?>
			</div>
			<?php 
			
			$report_details_data = array(
				'display_top_sellers' => isset ($display_data->display_top_sellers) && '1' == $display_data->display_top_sellers ? $top_sellers : array(),
				// 'display_total_refunds' => isset ($display_data->display_total_refunds) && '1' == $display_data->display_total_refunds ? $total_refunds : array(),
				'display_top_variations' => isset ($display_data->display_top_variations) && '1' == $display_data->display_top_variations ? $top_variations_sellers : array(),
				'display_top_categories' => isset ($display_data->display_top_categories) && '1' == $display_data->display_top_categories ? $top_categories : array(),
				'display_sales_by_coupons' => isset ($display_data->display_sales_by_coupons) && '1' == $display_data->display_sales_by_coupons ? $salse_by_coupons : array(),
				'display_sales_by_billing_city' => isset ($display_data->display_sales_by_billing_city) && '1' == $display_data->display_sales_by_billing_city ? $sales_by_billing_city : array(),
				'display_sales_by_shipping_city' => isset ($display_data->display_sales_by_shipping_city) && '1' == $display_data->display_sales_by_shipping_city ? $sales_by_shipping_city : array(),
				'display_sales_by_billing_state' => isset ($display_data->display_sales_by_billing_state) && '1' == $display_data->display_sales_by_billing_state ? $sales_by_billing_state : array(),
				'display_sales_by_shipping_state' => isset ($display_data->display_sales_by_shipping_state) && '1' == $display_data->display_sales_by_shipping_state ? $sales_by_shipping_state : array(),
				'display_sales_by_billing_country' => isset ($display_data->display_sales_by_billing_country) && '1' == $display_data->display_sales_by_billing_country ? $sales_by_billing_country : array(),
				'display_sales_by_shipping_country' => isset ($display_data->display_sales_by_shipping_country) && '1' == $display_data->display_sales_by_shipping_country ? $sales_by_shipping_country : array(),
				'display_order_status' => isset ($display_data->display_order_status) && '1' == $display_data->display_order_status ? $sales_by_order_status : array(),
				'display_order_details' => isset ($display_data->display_order_details) && '1' == $display_data->display_order_details ? $reoprt_by_order_details : array(),
				'display_Refund_order_details' => isset ($display_data->display_Refund_order_details) && '1' == $display_data->display_Refund_order_details ? $reoprt_by_refund_order_details : array(),
				'display_payment_method' => isset ($display_data->display_payment_method) && '1' == $display_data->display_payment_method ? $sales_by_payment_method : array(),
				'downloads_products_data_table' => isset ($display_data->downloads_products_data_table) && '1' == $display_data->downloads_products_data_table ? $products_data_table : array(),
			);
			
			// Only add the 'display_total_subscriber' if WC_Subscriptions_Manager exists
			if (class_exists('WC_Subscriptions_Manager') && isset($display_data->display_total_subscriber) && '1' == $display_data->display_total_subscriber) {
				$report_details_data['display_total_subscriber'] = $subscription_by_status;
			}
			 
			$sort_report_details = !empty($data->report_details_sort) ? unserialize($data->report_details_sort) : array_keys((array) $report_details_data);
			if ( !empty($sort_report_details)  ) { 
				foreach ( $sort_report_details as $key ) {
					if ( isset($display_data->$key) && '1' == $display_data->$key ) { 
						if (!empty($report_details_data[$key])) { 
							asre_pro()->admin->get_details_report_content( $report_details_data[$key] );
						}
					}
				}
			}
			
			//trackship deatils
			do_action( 'sre_view_after_report_details', $data, $date_range, $previous_date_range );

			// action hook
			do_action( 'after_email_report', $date_range );
			echo '<div style="display: flow-root;">';
			if ( isset($display_data->display_edit_report_link) && '1' != $display_data->display_edit_report_link ) { 
				echo '<span class="edit-report-link" style="float: left;padding: 20px 0 0;"><a target="_blank" href="' . esc_url(admin_url()) . 'admin.php?page=sre_customizer&type=email_options&id=' . esc_html($data->id) . '">edit report</a></span>';
			}
			if ( isset($display_data->display_zorem_branding) && '1' != $display_data->display_zorem_branding ) {
				echo '<span class="zorem-branding-link" style="float: right;padding: 20px 0 0;"><a target="_blank" href="https://zorem.com">Powered by zorem</a></span>';	
			}
			echo '</div>';
			?>
		</div>
	</body>
</html>
