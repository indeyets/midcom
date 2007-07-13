function finishCalendarLoad(id) {
	//run_scripts(jQuery('#'+id)[0]);
	jQuery('#'+id).fadeIn("",function(){
		var bodyClass = calendar_config["types_classes"][calendar_config["type"]];	
		if (   bodyClass == 'week'
			|| bodyClass == 'day')
		{
			//console.log("type == week/day start_hour_x: "+calendar_config["start_hour_x"]);
			jQuery('div.calendar-timeline-holder')[0].scrollTop = calendar_config["start_hour_x"];
		}		
	});
}

function run_scripts(e) {
	console.log("run_scripts in "+e);
	
	if (e.nodeType != 1) return; //if it's not an element node, return

	if (e.tagName.toLowerCase() == 'script') {
		console.log("execute scripts in "+e);
		console.log("execute scripts: "+e.text);
		eval(e.text); //run the script
	}
	else {
		var n = e.firstChild;
		while ( n ) {
			if ( n.nodeType == 1 ) run_scripts( n ); //if it's an element node, recurse
			n = n.nextSibling;
		}
	}
}

function zoom_view(zoom_in, url)
{	
	if(zoom_in)
	{
		if(calendar_config["type"] == 4)
		{
			return false;
		}	
		calendar_config["type"] += 1;
	}
	else
	{
		if(calendar_config["type"] == 1)
		{
			return false;
		}
		calendar_config["type"] -= 1;		
	}
	
	var ajax_url = APPLICATION_PREFIX + url + calendar_config["timestamp"] + '/' + calendar_config["type"];
	
	jQuery.ajaxSetup({global: true});
	jQuery.ajax({
		url: ajax_url,
		timeout: 12000,
		error: function() {
			alert("Failed to zoom");
			jQuery('#calendar-holder').show();
		},
		success: function(r) {
			jQuery('#calendar-holder').html(unescape(r));

			var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
			jQuery('body').attr('class', bodyClass);

			setTimeout("finishCalendarLoad('calendar-holder')", 400);
		}
	});
	
	return false;	
}

function change_date() {
	var form = jQuery('#date-selection-form');
	var currentDate = new Date();

	var inputs = [];
	var day = currentDate.getDate();
	var month = currentDate.getMonth();
	var year = currentDate.getFullYear();
	calendar_timestamp = currentDate.getTime()/1000.0;
	
	jQuery(':input', form).each(function() {
		if (this.name == "month-select")
		{
			month = this.value-1;
		}
		if (this.name == "day-select")
		{
			day = this.value;
		}
		if (this.name == "year-select")
		{
			year = this.value;
		}
		inputs.push(this.name + '=' + escape(this.value));
	});
	var newDate = new Date(
			year, month, day
		);
	timestamp = newDate.getTime() / 1000.0;
	// console.log("newDate day: "+newDate.getDate());
	// console.log("newDate month: "+newDate.getMonth());
	// console.log("newDate year: "+newDate.getFullYear());
			
	calendar_config["timestamp"] = timestamp;
	
	var ajax_url = APPLICATION_PREFIX + form[0].action + calendar_config["timestamp"] + '/' + calendar_config["type"];

	jQuery.ajaxSetup({global: true});
	jQuery.ajax({
		data: inputs.join('&'),
		url: ajax_url,
		timeout: 12000,
		error: function() {
			alert("Failed to change date");
			jQuery('#calendar-holder').show();
		},
		success: function(r) {
			jQuery('#calendar-holder').html(unescape(r));

			var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
			
			setTimeout("finishCalendarLoad('calendar-holder')", 400);
		}
	});
	
	return false;
};

function create_event(timestamp)
{
	var win = jQuery("#calendar-modal-window");
	jQuery.ajaxSetup({global: false});
	jQuery.ajax({
		type: "GET",
		url: APPLICATION_PREFIX + "ajax/event/create/" + timestamp,
		timeout: 12000,
		error: function() {
			alert("Failed to load event creation window");
		},
		success: function(r) {
			win.html(unescape(r));
			//jQuery.blockUI(win, {width: "90%", top: "12%", left: "22%"});
			jQuery("#calendar-modal-window").show();
			//setTimeout("finishCalendarLoad(\'calendar-modal-window\')", 400);
		}
	});
	// jQuery.get("/ajax/event/create/" + timestamp, function(data){
	// 	win.html(unescape(data));
	// 	//jQuery.blockUI(win, {width: "90%", top: "12%", left: "22%"});
	// 	jQuery("#calendar-modal-window").show();
	// });	
	
	return false;	
}
function close_create_event()
{	
	//jQuery("#calendar-modal-window").html('');
	// jQuery("#calendar-modal-window").hide();
//	var win = jQuery("#calendar-modal-window");
	jQuery("#calendar-modal-window").hide();
	//jQuery.unblockUI(win);
	return;
}

jQuery(document).ready(function() {
	jQuery.extend(jQuery.blockUI.defaults.overlayCSS, { backgroundColor: '#b39169' });
	jQuery('#calendar-loading').ajaxStart(function() {
		jQuery('#calendar-holder').hide();
		//jQuery(this).fadeIn();
		var indicator_url = MIDCOM_STATIC_URL + "/org.maemo.calendar/images/indicator.gif";
		jQuery.blockUI('<img src="' + indicator_url + '" alt="Loading..." /> Please wait');
		//jQuery(this).show();
	}).ajaxStop(function() {
		//jQuery('#calendar-loading').fadeOut(); 
		jQuery.unblockUI();
		//jQuery(this).hide();
		//jQuery('#calendar-holder').show();
	});

});