/*header script*/
jQuery( document ).on( "click", "#activity-panel-tab-help", function() {
	"use strict";
	jQuery(this).addClass( 'is-active' );
	jQuery( '.woocommerce-layout__activity-panel-wrapper' ).addClass( 'is-open is-switching' );
});

jQuery(document).click(function(){
	"use strict";
	var $trigger = jQuery(".woocommerce-layout__activity-panel");
    if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery('#activity-panel-tab-help').removeClass( 'is-active' );
		jQuery( '.woocommerce-layout__activity-panel-wrapper' ).removeClass( 'is-open is-switching' );
    }   
});
/*header script end*/

/* sre_snackbar jquery */
(function( $ ){
	$.fn.sre_snackbar = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var sre_snackbar = $("<article></article>").addClass('snackbar-log snackbar-log-success snackbar-log-show').text( msg );
		$(".snackbar-logs").append(sre_snackbar);
		setTimeout(function(){ sre_snackbar.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

/* sre_snackbar_warning jquery */
(function( $ ){
	$.fn.sre_snackbar_warning = function(msg) {
		if ( jQuery('.snackbar-logs').length === 0 ){
			$("body").append("<section class=snackbar-logs></section>");
		}
		var sre_snackbar_warning = $("<article></article>").addClass( 'snackbar-log snackbar-log-error snackbar-log-show' ).html( msg );
		$(".snackbar-logs").append(sre_snackbar_warning);
		setTimeout(function(){ sre_snackbar_warning.remove(); }, 3000);
		return this;
	}; 
})( jQuery );

jQuery(document).on("click", "#toggle_box .handlediv", function(){
	var exist = jQuery(this).parent().hasClass('closed');
	if(exist == false){
		jQuery(this).parent().addClass('closed');
	}
	if(exist == true){
	   	jQuery(this).parent().removeClass('closed');
	}
});

jQuery(document).on("click", ".checkbox-slide", function(){
	var status = jQuery(this).prop('checked');
	var id = jQuery(this).attr('id');
	if(status == false){
		jQuery('#'+id).closest('.total-item').removeClass('item-bg');
	}
	if(status == true){
	   	jQuery('#'+id).closest('.total-item').addClass('item-bg');
	}
});
	
jQuery(document).on("click", ".enable_status_list", function(){
	var value = jQuery(this).prop('checked');
	var id = jQuery(this).data('id');
	var row = jQuery(this).parents('#report-row');
	var data = {
				action: 'enable_toggle_data_update',
				check: value,
				ID : id,
				nonce: asrc_object.nonce, 
			};
	jQuery.ajax({
		url : ajaxurl,//csv_workflow_update
		type : 'POST',
		data : data,
		dataType:"json",
		success:function(result){
			if( result.email_enable == 1 ){
				//alert(result.check);
				jQuery(value).val('1');
				jQuery(row).removeClass().addClass('active');
				jQuery(document).sre_snackbar( "Report enabled successfully." );
			} else {
				jQuery(value).val('0');
				jQuery(row).removeClass().addClass('inactive');;
				jQuery(document).sre_snackbar( "Report disabled successfully." );
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			jQuery(".workflow_spinner").removeClass("active");
			jQuery(".workflow_error").show();
    	}
	});	
});

jQuery(document).on("change", "#email_interval, #email_select_month, #email_select_week, #email_send_time", function($){
	var interval = jQuery('#email_interval').val();
	var sendTime = jQuery('#email_send_time').val();
	var Week = jQuery('#email_select_week').val();
	var Month = jQuery('#email_select_month').val();
	
	var data = {
				action: 'cron_run_date_update',
				INTERVAL: interval,
				TIME : sendTime,
				WEEK : Week,
				MONTH : Month,
				nonce: asrc_object.nonce,
			};
	jQuery.ajax({
		url : ajaxurl,
		type : 'POST',
		data : data,
		dataType:"json",
		success:function(result){
			if( result.interval == 'daily' || result.interval == 'daily-overnight' ){
				jQuery(".cron_run_date b").replaceWith('<b>'+result.NextRunDate+'</b>');	
			} 
			if (result.interval == 'weekly' ) {
				jQuery(".cron_run_date b").replaceWith('<b>'+result.NextRunDate+'</b>');
			}
			if(result.interval == 'monthly' || result.interval == 'last-30-days' ) {
				jQuery(".cron_run_date b").replaceWith('<b>'+result.NextRunDate+'</b>');
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
			//error code
    	}
	});	
});

jQuery(document).on("submit", "#workflow_form", function(){
	jQuery(".workflow_success").hide();
	
	var error;
	
	if(error == true){
		return false;
	}
	
	jQuery(".workflow_spinner").addClass("active");
	jQuery.ajax({
		url : ajaxurl+"?action=report_data_update",//csv_workflow_update
		type : 'POST',
		data : jQuery(this).serialize(),
		dataType:"json",
		success:function(result){
			if( result.status == "success" ){
				jQuery(".workflow_spinner").removeClass("active");
				jQuery(document).sre_snackbar( "Report settings saved successfully." );
				jQuery('#workflow_form #id').val(result.id);
				jQuery('#asre_nonce_verify').val(result.asre_nonce_verify);
				jQuery(".submit.asre-btn .button-primary").replaceWith('<button name="save" class="button-primary woocommerce-save-button" type="submit" value="Save changes">Update</button>');
				jQuery(".cbr-show-modal, #asre_sales_report_test_mail").attr('style', '').attr('disabled', false);
				jQuery(".header-breadcrumbs-last").replaceWith('<span class="header-breadcrumbs-last"> Edit Report</span>');
				window.history.pushState("object or string", asrc_object.admin_url, "admin.php?page=woocommerce-advanced-sales-report-email&tab=edit&id="+result.id);
				jQuery("#iframe").attr("src", asrc_object.admin_url+"admin-ajax.php?action=preview_asre_page&id="+result.id );
			} else {
				jQuery(".workflow_spinner").removeClass("active");
				jQuery(".invalid_license").show();
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) { 
		jQuery(".workflow_spinner").removeClass("active");
		jQuery(".workflow_error").show();
    }  
		
	});
	return false;
});

function showerror(element){
	element.css("border-color","red");
}
function hideerror(element){
	element.css("border-color","");
}
function validatePhone(value) {
    var filter = /^[0-9-+]+$/;
    if (filter.test(value)) {
		if(value.length > 6 && value.length < 11) {	return true;} else {	return false;}
    } else {
        return false;
	}
}

function validatePincode(value) {
    var filter = /^[0-9-+]+$/;
    if (filter.test(value)) {
		if(value.length == 6) {	return true;} else {	return false;}
    } else {
        return false;
	}
}
function validateEmail(value){
	var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
	if (reg.test(value) == false) {
		return false;
	}
	return true;
}
function validateRadio ( ElementsByName ) {
    var radios = document.getElementsByName( ElementsByName );
    var formValid = false;

    var i = 0;
    while (!formValid && i < radios.length) {
        if (radios[i].checked){ formValid = true; }
        i++;        
    }
    return formValid;
}

function tConvert (time) {
  // Check correct time format and split into components
  time = time.toString().match (/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];

  if (time.length > 1) { // If time format correct
	time = time.slice (1);  // Remove full string match value
	time[1] = time[2] == 00 ? '' : ':'; // Set AM/PM
	time[2] = time[2] == 00 ? '' : '30'; // Set AM/PM
    time[5] = +time[0] < 12 ? 'am' : 'pm'; // Set AM/PM
    time[0] = +time[0] % 12 || 12; // Adjust hours
  }
  return time.join (''); // return adjusted time or original string
}

jQuery(document).ready(function($){
	
	var test_button = $("#asre_sales_report_test_mail");

	test_button.click( function(e){
		var ID = $('#id').val();
		var recipients = $('#test_email_recipients').val();
		test_button.after('<div class="spinner" style="float:none"></div>');
		$("#preview_test_table .spinner").addClass("active");
		$.ajax({
			url : ajaxurl+"?action=send_test_sales_email&id="+ID,
			type : 'POST',
			success:function(result){
				$("#preview_test_table .spinner").removeClass("active");
				jQuery('#preview_test_table .spinner').hide();
				jQuery(document).sre_snackbar( "Test email report sent to "+recipients+"." );
			}
		});
	});
});

jQuery(document).on("change", "#email_interval", function($){

	var val = jQuery(this).val();
	var send_time = tConvert(jQuery("#email_send_time").val());
	var time_start = tConvert(jQuery("#day_hour_start").val());
	var time_end = tConvert(jQuery("#day_hour_end").val());
	
	jQuery(".email_select_week, .email_select_month, .daily-overnight, .interval_desc").hide();
	if( val == 'daily' ){
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertBefore( ".form-table.display_previous_period" );
	}
	if( val == 'weekly' ){
		jQuery(".email_select_week").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertBefore( ".form-table.display_previous_period" );
	}

	if ( val == 'weekly' || val == 'monthly') {
		jQuery(".display_net_sales_this_month").addClass('hide');
	} else {
		jQuery(".display_net_sales_this_month.hide").removeClass('hide');
	}
	
	if( val == 'monthly' || val == 'last-30-days' ){
		jQuery(".email_select_month").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertBefore( ".form-table.display_previous_period" );
	}
	if( val == 'daily-overnight' ){
		jQuery(".daily-overnight").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent daily at "+send_time+" and will show results from "+time_start+" -  "+time_end+" (overnight).</div>").insertBefore( ".form-table.display_previous_period" );
	}
});

jQuery(document).on("change", "#email_send_time, #day_hour_start, #day_hour_end", function(){

	var email_interval = jQuery("#email_interval").val();
	var send_time = tConvert(jQuery("#email_send_time").val());
	var time_start = tConvert(jQuery("#day_hour_start").val());
	var time_end = tConvert(jQuery("#day_hour_end").val());
	
	jQuery(".interval_desc").hide();
	if( email_interval == 'daily' ){
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent daily at "+send_time+" and will show results for the previous day from 00:00  to 24:00.</div>").insertBefore( ".form-table.display_previous_period" );
	}
	if( email_interval == 'weekly' ){
		jQuery(".email_select_week").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent Weekly on Monday at "+send_time+" and will show results for the previous 7 days.</div>").insertBefore( ".form-table.display_previous_period" );
	}
	if( email_interval == 'monthly' || email_interval == 'last-30-days' ){
		jQuery(".email_select_month").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent Monthly on the 1st (5th, 10th, etc) of the month at "+send_time+" and will show results for the previous month.</div>").insertBefore( ".form-table.display_previous_period" );
	}
	if( email_interval == 'daily-overnight' ){
		jQuery(".daily-overnight").show();
		jQuery("<div class='interval_desc' style='padding-top:10px;'>Report will be sent daily at "+send_time+" and will show results from "+time_start+" -  "+time_end+" (overnight).</div>").insertBefore( ".form-table.display_previous_period" );
	}
	
});

jQuery(document).ready(function(event){
	var val = jQuery("#email_send_time").val();
	jQuery('#day_hour_end > option').each(function() {
		jQuery(this).css('display','');
		if ( jQuery(this).val() > val ) {
			jQuery(this).css('display','none');                                 
		}
	});
});

jQuery(document).on("change", "#email_send_time", function(event){
	var val = jQuery(this).val();
	var end_time = jQuery("#day_hour_end").val();
	jQuery('#day_hour_end > option').each(function() {
		jQuery(this).css('display','');
		if ( jQuery(this).val() > val ) {
			jQuery(this).css('display','none');
			if( end_time > val){
				jQuery("#day_hour_end").val(val).trigger('change');
			}
		}
	});
});


jQuery(document).on("click", ".send_test_report_mail", function($){
	jQuery.ajax({
		url : ajaxurl+"?action=send_test_sales_email",
		type : 'POST',
		success:function(result){			
		}
	});
});


/*WP media field js*/
var file_frame;
	jQuery(document).on("click", "#asre_upload_image_button", function(product) {
		product.preventDefault();
		var asre_image_id = jQuery(this).siblings(".asre_image_id");
		var branding_logo = jQuery(this).siblings(".branding_logo");
		var asre_thumbnail = jQuery("#asre_thumbnail");
		
		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}
	
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: 'Upload Media',
			button: {
				text: 'Add',
			},
			multiple: false // Set to true to allow multiple files to be selected
		});
	
		// When a file is selected, run a callback.
		file_frame.on('select', function(){     
			attachment = file_frame.state().get('selection').first().toJSON();       
			var id = attachment.id;        
			var url = attachment.url;     
			branding_logo.attr('value', url);
			asre_thumbnail.attr('src', url);
			asre_image_id.attr('value', id);
			jQuery('.asre-thumbnail-image').show();
			jQuery('.asre-image-placeholder').hide();
	
		});
		// Finally, open the modal
		file_frame.open();
	});

/*ajex call for general tab form save*/	 
jQuery(document).on("submit", "#asre_branding_tab_form", function(){
	"use strict";
	jQuery("#asre_branding_tab_form .spinner").addClass("active");
	var form = jQuery('#asre_branding_tab_form');
	jQuery.ajax({
		url: ajaxurl+"?action=asre_branding_form_update",//csv_workflow_update,		
		data: form.serialize(),
		type: 'POST',
		dataType:"json",	
		success: function(response) {
			if( response.success === "true" ){
				jQuery("#asre_branding_tab_form .spinner").removeClass("active");
				jQuery(document).sre_snackbar( "Setting saved successfully." );
			} else {
				//show error on front
			}
		},
		error: function(response) {
			console.log(response);			
		}
	});
	return false;
});

jQuery(document).on("submit",'#esre-licence-form', function(e){
	e.preventDefault();
	var licence_form = jQuery(this);
	var action = licence_form.find(".licence_action").val();
	var data = licence_form.serialize();
	var licence_button = licence_form.find(".licence_submit");
	
	jQuery("#wclp-licence-form").block({
		message: null,
		overlayCSS: {
			background: "#fff",
			opacity: .6
		}	
    });
	
	jQuery.ajax({
		type: "POST",
		url : ajaxurl,
		data: data + '&nonce=' + asrc_object.nonce,
		beforeSend: function(){
			licence_button.prop('disabled', true).val('Please wait..');
		},
		success : function(data){
			var btn_value = 'Activate';
			if( data.success === true ){
				if( action === 'wc_esre_license_activate' ){
					btn_value = 'Deactivate';
					licence_form.find(".licence_action").val( 'wc_esre_license_deactivate' );
					jQuery("#esre-licence-form").unblock();
					location.reload();
				} else {
					licence_form.find(".licence_action").val( 'wc_esre_license_activate' );
					licence_form.find("#license_key").val( '' );
					jQuery("#esre-licence-form").unblock();
					location.reload();
				}
			} else if( data.success === false ){
				jQuery('.pesan').html('<span style="color:red;">' + data.error  + '</span>');
			} else {
				jQuery('.pesan').html('<span style="color:red;">'+data.message+'</span>');//'+data.message+'
			}
			
			licence_button.prop('disabled', false).val(btn_value);
		},
		error: function(data){
			console.log(data);
		}
	});
	
});

jQuery(document).ready(function($){
	
	var val = jQuery("#email_interval").val();
	if( val == 'daily' || val == 'daily-overnight'){
		if(jQuery("#display_average_daily_sales").prop('checked')){
			jQuery("#display_average_daily_sales").trigger("click");
		}
		jQuery("#display_average_daily_sales").prop("disabled", true);
	}
	if( val == 'weekly' || val == 'monthly'){
		jQuery("#display_average_daily_sales").prop("disabled", false);
	}

});

jQuery(document).on("change", "#email_interval", function($){
	var val = jQuery(this).val();
	if( val == 'daily' || val == 'daily-overnight'){
		if(jQuery("#display_average_daily_sales").prop('checked')){
			jQuery("#display_average_daily_sales").trigger("click");
		}
		jQuery("#display_average_daily_sales").prop("disabled", true);
	} 
	if( val == 'weekly' || val == 'monthly'){
		jQuery("#display_average_daily_sales").prop("disabled", false);
	}
});

/*remove preview image on click*/
jQuery(document).on("click", "#remove_btn", function(){		
	jQuery('img').parent(".asre-thumbnail-image").hide();
	jQuery('.button').parent(".asre-thumbnail-image").hide();
	jQuery('#branding_logo').val('');
	jQuery('.asre-image-placeholder').show();
 });
 
 jQuery(document).on("click", ".asre_tab_input", function(){
	var tab = jQuery(this).data('tab');
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname+"?page=woocommerce-advanced-sales-report-email&tab="+tab;
	window.history.pushState({path:url},'',url);	
});

jQuery(document).on("click", ".asre_edit_inner_container #toggle_box h2", function(){
	var triggerClass = jQuery(this).parents('#toggle_box').find('.handlediv').trigger('click');
});