function finishCalendarLoad(id) {
	//run_scripts($j('#'+id)[0]);
	$j('#'+id).fadeIn("",function(){
		var bodyClass = calendar_config["types_classes"][calendar_config["type"]];	
		if (   bodyClass == 'week'
			|| bodyClass == 'day')
		{
			//console.log("type == week/day start_hour_x: "+calendar_config["start_hour_x"]);
			$j('div.calendar-timeline-holder')[0].scrollTop = calendar_config["start_hour_x"];
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
	
	var ajax_url = url + calendar_config["timestamp"] + '/' + calendar_config["type"];
	
	jQuery.ajaxSetup({global: true});
	jQuery.ajax({
		url: ajax_url,
		timeout: 12000,
		error: function() {
			alert("Failed to zoom");
			$j('#calendar-holder').show();
		},
		success: function(r) {
			$j('#calendar-holder').html(unescape(r));

			var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
			$j('body').attr('class', bodyClass);

			setTimeout("finishCalendarLoad('calendar-holder')", 400);
		}
	});
	
	return false;	
}

function change_date() {
	var form = $j('#date-selection-form');
	var currentDate = new Date();

	var inputs = [];
	var day = currentDate.getDate();
	var month = currentDate.getMonth();
	var year = currentDate.getFullYear();
	calendar_timestamp = currentDate.getTime()/1000.0;
	
	$j(':input', form).each(function() {
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
	
	var ajax_url = form[0].action + calendar_config["timestamp"] + '/' + calendar_config["type"];

	jQuery.ajaxSetup({global: true});
	jQuery.ajax({
		data: inputs.join('&'),
		url: ajax_url,
		timeout: 12000,
		error: function() {
			alert("Failed to change date");
			$j('#calendar-holder').show();
		},
		success: function(r) {
			$j('#calendar-holder').html(unescape(r));

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
		url: "/ajax/event/create/" + timestamp,
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
	// $j("#calendar-modal-window").hide();
//	var win = jQuery("#calendar-modal-window");
	jQuery("#calendar-modal-window").hide();
	//jQuery.unblockUI(win);
	return;
}

$j(document).ready(function() {
	$j.extend($j.blockUI.defaults.overlayCSS, { backgroundColor: '#b39169' });
	$j('#calendar-loading').ajaxStart(function() {
		$j('#calendar-holder').hide();
		//$j(this).fadeIn();
		var indicator_url = MIDCOM_STATIC_URL + "/org.maemo.calendar/images/indicator.gif";
		$j.blockUI('<img src="' + indicator_url + '" alt="Loading..." /> Please wait');
		//$j(this).show();
	}).ajaxStop(function() {
		//$j('#calendar-loading').fadeOut(); 
		$j.unblockUI();
		//$j(this).hide();
		//$j('#calendar-holder').show();
	});

});