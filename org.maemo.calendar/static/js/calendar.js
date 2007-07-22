if (console == undefined)
{
    var console = {};
    console.log = function(string)
    {
        alert(string);
    }
}

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
        error: function(obj, type, expobj) {
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
    
    //APPLICATION_PREFIX + 
    var ajax_url = form[0].action + calendar_config["timestamp"] + '/' + calendar_config["type"];

    jQuery.ajaxSetup({global: true});
    jQuery.ajax({
        data: inputs.join('&'),
        url: ajax_url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to change date");
            jQuery('#calendar-holder').show();
        },
        success: function(r) {
            jQuery('#calendar-holder').html(unescape(r));

            //var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
            
            setTimeout("finishCalendarLoad('calendar-holder')", 400);
        }
    });
    
    return false;
};

function change_timezone() {
    var form = jQuery('#timezone-selection-form');

    var timezone = null;
    var inputs = [];
    
    jQuery(':input', form).each(function() {
        if (this.name == "timezone")
        {
            timezone = this.value-1;
        }
        inputs.push(this.name + '=' + escape(this.value));
    });
    console.log("new timezone: "+timezone);
    
    //APPLICATION_PREFIX + 
    var ajax_url = form[0].action + calendar_config["timestamp"] + '/' + calendar_config["type"];

    jQuery.ajaxSetup({global: true});
    jQuery.ajax({
        data: inputs.join('&'),
        url: ajax_url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to change timezone");
            jQuery('#calendar-holder').show();
        },
        success: function(r) {
            jQuery('#calendar-holder').html(unescape(r));            
            setTimeout("finishCalendarLoad('calendar-holder')", 400);
        }
    });
    
    return false;
};

function create_event(timestamp)
{
    var win = jQuery("div.calendar-modal-window");
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + "ajax/event/create/" + timestamp,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to load event creation window");
        },
        success: function(r) {
            win.html(unescape(r));
            //jQuery.blockUI(win, {width: "90%", top: "12%", left: "22%"});
            jQuery("div.calendar-modal-window").show();
            //setTimeout("finishCalendarLoad(\'calendar-modal-window\')", 400);
        }
    });
    // jQuery.get("/ajax/event/create/" + timestamp, function(data){
    //  win.html(unescape(data));
    //  //jQuery.blockUI(win, {width: "90%", top: "12%", left: "22%"});
    //  jQuery("div.calendar-modal-window").show();
    // });  
    
    return false;   
}
function close_create_event()
{   
    //jQuery("div.calendar-modal-window").html('');
    // jQuery("div.calendar-modal-window").hide();
//  var win = jQuery("div.calendar-modal-window");
    jQuery("div.calendar-modal-window").hide();
    //jQuery.unblockUI(win);
    return;
}

function load_modal_window(url)
{
    var win = jQuery("div.calendar-modal-window");
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to load modal window");
        },
        success: function(r) {
            win.html(unescape(r));
            jQuery("div.calendar-modal-window").show();
        }
    });    
}
function close_modal_window()
{   
    jQuery("div.calendar-modal-window").hide();
    return;
}

function load_shelf_contents()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=load';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        dataType: 'json',
        error: function(obj, type, expobj) {
            alert("Failed to load shelf content");
        },
        success: function(r) {
            shelf_contents = r;
            hide_shelf_events_from_view();
            console.log('Loaded Shelf content from the server: '+shelf_contents);
        }
    });    
}

function save_shelf_contents()
{
    console.log('save_shelf_contents: '+shelf_contents);
    
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=save';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "POST",
        url: APPLICATION_PREFIX + url,
        data: {data: protoToolkit.toJSON(shelf_contents)},
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to save shelf content! exception type: "+type);
        },
        success: function(msg) {
            update_shelf_panel_leaf();
            hide_shelf_events_from_view();
            console.log('Saved Shelf content to the server: '+msg);
        }
    });    
}

function update_shelf_panel_leaf()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=update_list';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        dataType: 'script',
        error: function(obj, type, expobj) {
            alert("Failed to update shelf list");
        },
        success: function(r) {
            console.log('Updated Shelf list with content from the server: '+r);
        }
    });    
}

function hide_shelf_events_from_view()
{
    console.log('hide_shelf_events_from_view');
    jQuery.each( shelf_contents, function(i,n){
        console.log('hide event with id event-'+n.guid);
        jQuery('#event-'+n.guid).hide().attr({in_shelf: 'true'});
        jQuery('div.event-toolbar-holder div.event-toolbar:visible').hide();
    });
}
function unhide_shelf_events_from_view()
{
    console.log('unhide_shelf_events_from_view');
    jQuery.each( shelf_contents, function(i,n){
        console.log('unhide event with id event-'+n.guid);
        jQuery('#event-'+n.guid).show().attr({in_shelf: 'false'});
    });
}

function move_event_to_shelf(identifier, event_data)
{
    console.log('move_event_to_shelf: '+identifier);
    
    var existing = jQuery.grep( shelf_contents, function(n,i){
       return n.guid == identifier;
    });
    
    console.log('existing: '+existing);
    console.log('existing.length: '+existing.length);
    
    if (existing.length == 0)
    {
        console.log('Event isnt in the shelf yet. Add it now.');
        var next_idx = shelf_contents.push({guid: identifier, data: event_data});
        console.log('shelf_contents['+(next_idx-1)+']: '+shelf_contents[next_idx-1]);
        console.log('shelf_contents['+(next_idx-1)+'].data.title: '+shelf_contents[next_idx-1].data.title);
        
        save_shelf_contents();
    }
    else
    {
        console.log('Event is in the shelf already. Do nothing');
    }
}

function empty_shelf()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=empty';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to empty shelf list");
        },
        success: function(r) {
            unhide_shelf_events_from_view();
            shelf_contents = Array();
            jQuery('#shelf-item-list').html('');
            console.log('Emptied Shelf list with response from the server: '+r);
        }
    });    
}

jQuery(document).ready(function() {
    //empty_shelf();    
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