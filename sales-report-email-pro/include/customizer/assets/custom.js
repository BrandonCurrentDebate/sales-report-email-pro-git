jQuery(document).on("change", "#email_interval", function($){
	var send_time = jQuery("#email_send_time").val();
	var email_interval = jQuery("#email_interval").val();

    jQuery(".interval_desc").hide();
    jQuery(".email_send_time, .email_select_week, .email_select_month, .day_hour_start, .day_hour_end, .daterange").addClass('hide');
	if( email_interval == 'daily' ){
		jQuery(".email_send_time").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'month-to-date' ){
		jQuery(".email_send_time").removeClass('hide');		
	}
	if( email_interval == 'weekly' ){
		jQuery(".email_select_week, .email_send_time").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'monthly' || email_interval == 'last-30-days' ){
		jQuery(".email_select_month, .email_send_time").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'daily-overnight' ){
		jQuery(".day_hour_start, .day_hour_end, .email_send_time").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}
	if( email_interval == 'one-time' ){
		jQuery(" .daterange").removeClass('hide');
		//jQuery("<div class='zoremmail-menu zoremmail-menu-inline interval_desc'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertAfter( ".zoremmail-menu-sub.email_send_time" );
	}

});

// jQuery(document).on("click", "#report_details", function($){
// 	var email_interval = jQuery("#email_interval").val();
// 	console.log('email_interval', email_interval)
// 	if ( email_interval == 'weekly' || email_interval == 'monthly') {
// 		console.log('email_interval1', email_interval)
// 		jQuery(".display_net_sales_this_month").addClass('hide');
// 	} else {
// 		console.log('email_interval2', email_interval)
// 		jQuery(".display_net_sales_this_month.hide").removeClass('hide');
// 	}
// });

jQuery(document).on("keyup", ".heading .zoremmail-input", function(event){
	if(event.target.value){
		var str = event.target.value;
	} else {
		var str = event.target.placeholder;
	}
	
	var res = str.replace("{site_title}", sre_customizer.site_title);
	var res = res.replace("{order_number}", sre_customizer.order_number);
	var res = res.replace("{customer_first_name}", sre_customizer.customer_first_name);
	var res = res.replace("{customer_last_name}", sre_customizer.customer_last_name);
	var res = res.replace("{customer_company_name}", sre_customizer.customer_company_name);
	var res = res.replace("{customer_username}", sre_customizer.customer_username);
	var res = res.replace("{customer_email}", sre_customizer.customer_email);
	var res = res.replace("{est_delivery_date}", sre_customizer.est_delivery_date);
	
	if( str ){	
		jQuery("#content-preview-iframe").contents().find( '.report-name ' ).text(res);
	} else{
		jQuery("#content-preview-iframe").contents().find( '.report-name' ).text(event.target.placeholder);
	}
});

jQuery(document).on("keyup", ".additional_content .zoremmail-input", function(event){
	if(event.target.value){
		var str = event.target.value;
	} else {
		var str = event.target.placeholder;
	}
	
	var res = str.replace("{site_title}", sre_customizer.site_title);
	var res = res.replace("{order_number}", sre_customizer.order_number);
	var res = res.replace("{customer_first_name}", sre_customizer.customer_first_name);
	var res = res.replace("{customer_last_name}", sre_customizer.customer_last_name);
	var res = res.replace("{customer_company_name}", sre_customizer.customer_company_name);
	var res = res.replace("{customer_username}", sre_customizer.customer_username);
	var res = res.replace("{customer_email}", sre_customizer.customer_email);
	var res = res.replace("{est_delivery_date}", sre_customizer.est_delivery_date);
	
	jQuery("#content-preview-iframe").contents().find( '.main-title ~ .additonal-content' ).remove();
	if( str ){				
		jQuery("#content-preview-iframe").contents().find( '.main-title' ).after('<span class="additonal-content">'+res+'</span>');
	}
});

function set_params(key, val) {
	jQuery(".zoremmail-layout-content-preview").addClass(
		"customizer-unloading"
	  );
	var url = new URL(jQuery("iframe").attr('src'));
	var search_params = url.searchParams;
	search_params.set(key, val);
	url.search = search_params.toString();
	var new_url = url.toString();
	jQuery("iframe").attr("src", new_url);
	jQuery(".zoremmail-layout-content-preview").removeClass(
		"customizer-unloading"
	  );
}

jQuery(document).on("click", ".tgl-flat, .checkbox", function(){
	var key = jQuery(this).attr('id');
	var val = jQuery(this).val();
	
	if ( key == null || key === 'email_enable' || key === 'email_type' ) { 
		return;
	}
	set_params(key, val);
});

jQuery(document).on("change", ".select", function(){
	var key = jQuery(this).attr('id');
	var val = jQuery(this).val();

	if ( key == null || key === 'email_select_week' || key === 'email_select_month' || key === 'email_send_time' || key === 'email_type' ) { 
		return;
	}
	set_params(key, val);
});

jQuery(document).ready(function(){

	jQuery('input[name="daterange"]').daterangepicker({
		opens: 'right',
		autoApply: true,
	}, function(start, end, label) {
		jQuery("#daterange").val(start.format('mm/dd/YYYY') + ' - ' + end.format('mm/dd/YYYY'));
	});

	jQuery('.zoremmail-menu-contain').sortable({
		handle: '.options-sort',
		axis: 'y',
		helper: function (e, ui) { 
			ui.children().each(function () {
				jQuery(this).width(jQuery(this).width()).parent().css('background-color', '#fff').css('border-top', '1px solid #e0e0e0').css('padding','10px 0 10px 0');
			});
			return ui;
		},
		stop: function ( e, ui) {
			jQuery(".zoremmail-layout-content-preview").addClass(
				"customizer-unloading"
			  );
			jQuery('.zoremmail-menu-item').parent().css('background-color', '').css('border-top', '').css('padding','');
			var column_key = ui.item.find('.sortable').attr('name');
			var obj = {};
			jQuery(this).children().each(function( index ) {
				obj[index] = jQuery(this).find('.sortable').val();
			});
			var preview = jQuery('#preview').val();
			var ajax_data = {
				data: obj,
				id: preview,
				key: column_key,
				nonce: sre_customizer.rest_nonce,
				action: 'save_sre_sortable_settings',
			};
			
			jQuery.ajax({
				url: ajaxurl,	
				data: ajax_data,		
				type: 'POST',
				dataType:"json",
				success: function(response) {
					if( response.success === "true" ){
						jQuery("iframe").attr("src", jQuery("iframe").attr("src"));
						jQuery(".zoremmail-layout-content-preview").removeClass(
							"customizer-unloading"
						  );
					} else {
						//show error on front
					}
				},
				error: function(response) {
					console.log(response);			
				}
			});
			return;
		},
	});

});

jQuery( document ).on( "change", ".tgl-btn-parent", function() {
	var isChecked = jQuery("#show_header_image").is(":checked");
	if ( isChecked == false ) {
		jQuery(".header_logo").hide();
	} else {
		jQuery(".header_logo").show();
	}
});
