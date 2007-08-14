if (console == undefined)
{
    var console = {};
    console.log = function(string)
    {
        //alert(string);
        return;
    }
}

function finishCalendarLoad(id) {
    //run_scripts(jQuery('#'+id)[0]);
    jQuery('#'+id).fadeIn("",function(){
        on_view_update();
        var bodyClass = calendar_config["types_classes"][calendar_config["type"]];  
        if (   bodyClass == 'week'
            || bodyClass == 'day')
        {
            //console.log("type == week/day start_hour_x: "+calendar_config["start_hour_x"]);
            jQuery('div.calendar-timeline-holder')[0].scrollTop = calendar_config["start_hour_x"];
        }       
    });
}

function run_scripts(e)
{
    //console.log("run_scripts in "+e);
    
    if (e.nodeType != 1)
    {
        return; //if it's not an element node, return
    }

    if (e.tagName.toLowerCase() == 'script')
    {
        //console.log("execute scripts in "+e);
        //console.log("execute scripts: "+e.text);
        eval(e.text); //run the script
    }
    else
    {
        var n = e.firstChild;
        while ( n )
        {
            if ( n.nodeType == 1 )
            {
                run_scripts( n ); //if it's an element node, recurse
            }
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
    
    var form = jQuery('#date-selection-form');
    var ajax_url = APPLICATION_PREFIX + url + calendar_config["timestamp"] + '/' + calendar_config["type"];
    //console.log("ajax_url: "+ajax_url);
    jQuery.ajaxSetup({global: true});
    jQuery.ajax({
        url: ajax_url,
        timeout: 12000,
        error: function(request, type, expobj) {
            alert("Failed to zoom request.statusText: "+request.statusText);
            jQuery('#calendar-holder').show();
        },
        success: function(r) {
            var content = unescape(r);
            // run_scripts(content);
            jQuery('#calendar-holder').html(content);

            var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
            jQuery('body').attr('class', bodyClass);

            setTimeout("finishCalendarLoad('calendar-holder')", 400);
        }
    });
    
    return false;   
}

function update_date_selection(day, month, year)
{
    var inputs = [];
    
    var form = jQuery('#date-selection-form');    
    jQuery(':input', form).each(function() {
        if (this.name == "month-select")
        {
            this.value = month;
        }
        if (this.name == "day-select")
        {
            this.value = day;
        }
        if (this.name == "year-select")
        {
            this.value = year;
        }
        inputs.push(this.name + '=' + escape(this.value));
    });
    
    return inputs;
}

function goto_prev()
{
    var currentDate = new Date();
    var day = currentDate.getDate();
    var month = currentDate.getMonth();
    var year = currentDate.getFullYear();

    var newDate = new Date(
        day, month, year
        );
        
    var form = jQuery('#date-selection-form');    
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
    });
    
    newDate.setDate(day);
    newDate.setMonth(month);
    newDate.setYear(year);
    
    //console.log("newDate before: "+newDate);

    if (calendar_config["type"] == 1)
    {
        newDate.setYear(newDate.getFullYear() - 1);        
    }
    if (calendar_config["type"] == 2)
    {
        newDate.setMonth((newDate.getMonth() - 1));
    }
    if (calendar_config["type"] == 3)
    {
        newDate.setDate((newDate.getDate() - 7));
    }
    if (calendar_config["type"] == 4)
    {
        newDate.setDate((newDate.getDate() - 1));
    }    
    
    var str_month = String((newDate.getMonth()+1));
    if (str_month.length < 2)
    {
        str_month = "0" + str_month;
    }
    
    var str_day = String((newDate.getDate()));
    if (str_day.length < 2)
    {
        str_day = "0" + str_day;
    }
    
    var inputs = update_date_selection(str_day, str_month, newDate.getFullYear());
    
    timestamp = newDate.getTime() / 1000.0;
    
    calendar_config["timestamp"] = timestamp;

    var ajax_url = APPLICATION_PREFIX + 'ajax/change/date/' + calendar_config["timestamp"] + '/' + calendar_config["type"];

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
}

function goto_next()
{
    var currentDate = new Date();
    var day = currentDate.getDate();
    var month = currentDate.getMonth();
    var year = currentDate.getFullYear();

    var newDate = new Date(
        day, month, year
        );
    
    var form = jQuery('#date-selection-form');
    jQuery(':input', form).each(function() {
        if (this.name == "day-select")
        {
            day = this.value;
        }
        if (this.name == "month-select")
        {
            month = this.value-1;
        }
        if (this.name == "year-select")
        {
            year = this.value;
        }
    });

    newDate.setDate(day);
    newDate.setMonth(month);
    newDate.setYear(year);
    
    //console.log("newDate before: "+newDate);

    if (calendar_config["type"] == 1)
    {
        newDate.setYear(newDate.getFullYear() + 1);        
    }
    if (calendar_config["type"] == 2)
    {
        newDate.setMonth((newDate.getMonth() + 1));
    }
    if (calendar_config["type"] == 3)
    {
        newDate.setDate((newDate.getDate() + 7));
    }
    if (calendar_config["type"] == 4)
    {
        newDate.setDate((newDate.getDate() + 1));
    }    
    
    var str_month = String((newDate.getMonth()+1));
    if (str_month.length < 2)
    {
        str_month = "0" + str_month;
    }
    
    var str_day = String((newDate.getDate()));
    if (str_day.length < 2)
    {
        str_day = "0" + str_day;
    }
    
    var inputs = update_date_selection(str_day, str_month, newDate.getFullYear());

    timestamp = newDate.getTime() / 1000.0;
    
    //console.log("newDate: "+newDate);
        
    calendar_config["timestamp"] = timestamp;

    var ajax_url = APPLICATION_PREFIX + 'ajax/change/date/' + calendar_config["timestamp"] + '/' + calendar_config["type"];

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
}

function goto_today()
{
    var form = jQuery('#date-selection-form');
    var currentDate = new Date();

    var day = currentDate.getDate();
    var month = currentDate.getMonth();
    var year = currentDate.getFullYear();

    var cur_month = String((month+1));
    if (cur_month.length < 2)
    {
        cur_month = "0" + cur_month;
    }

    var inputs = update_date_selection(day, cur_month, year);

    var newDate = new Date (
            year, month, day
        );
    timestamp = newDate.getTime() / 1000.0;
            
    calendar_config["timestamp"] = timestamp;

    var ajax_url = APPLICATION_PREFIX + 'ajax/change/date/' + calendar_config["timestamp"] + '/' + calendar_config["type"];

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
            // var content = unescape(r);
            // run_scripts(r);
            jQuery('#calendar-holder').html(unescape(r));

            //var bodyClass = calendar_config["types_classes"][calendar_config["type"]];
            
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
    //console.log("newDate day: "+newDate.getDate());
    //console.log("newDate month: "+newDate.getMonth());
    //console.log("newDate year: "+newDate.getFullYear());
            
    calendar_config["timestamp"] = timestamp;
    
    var ajax_url = APPLICATION_PREFIX + 'ajax/change/date/' + calendar_config["timestamp"] + '/' + calendar_config["type"];

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
    
    var ajax_url = APPLICATION_PREFIX + 'ajax/change/timezone/' + calendar_config["timestamp"] + '/' + calendar_config["type"];

    jQuery.ajaxSetup({global: true});
    jQuery.ajax({
        data: inputs.join('&'),
        url: ajax_url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            jQuery('#calendar-holder').show();
            alert("Failed to change timezone! type: "+type+" obj.statusText:"+obj.statusText);
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
    var url = "ajax/event/create/" + timestamp;

    if (active_shelf_item)
    {
        url = "ajax/event/move/" + active_shelf_item + "/" + timestamp;
    }
    
    load_modal_window(url);
}
function close_create_event()
{   
    close_modal_window();
    return;
}

function load_modal_window(url)
{
    var win = jQuery("div.calendar-modal-window");

    if (url.substr(0,7) != 'midcom-')
    {
        url = APPLICATION_PREFIX + url;
    }
    else
    {
        url = HOST_PREFIX + url;
    }
    
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: url,
        timeout: 12000,
        error: function(request, type, expobj) {
            //console.log("Failed to load modal window! type: "+type);
            //console.log("request.statusText: "+request.statusText);
            if (request.statusText == "Forbidden")
            {
                window.location = HOST_PREFIX + 'midcom-login-';
            }
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
function set_modal_window_contents(content)
{
    jQuery("div.calendar-modal-window").html(unescape(content));
}

function load_shelf_contents()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=load';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: HOST_PREFIX + url,
        timeout: 12000,
        dataType: 'json',
        error: function(request, type, expobj) {
            //console.log("Failed to load shelf content");
        },
        success: function(r) {
            shelf_contents = r;
            //console.log('Loaded Shelf content from the server: '+shelf_contents);            
            hide_shelf_events_from_view();
        }
    });    
}

function save_shelf_contents()
{
    //console.log('save_shelf_contents: '+shelf_contents);
    
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=save';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "POST",
        url: HOST_PREFIX + url,
        data: {data: protoToolkit.toJSON(shelf_contents)},
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to save shelf content! exception type: "+type);
        },
        success: function(msg) {
            update_shelf_panel_leaf();
            hide_shelf_events_from_view();
            //console.log('Saved Shelf content to the server: '+msg);
        }
    });    
}

function update_shelf_panel_leaf()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=update_list';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: HOST_PREFIX + url,
        timeout: 12000,
        dataType: 'script',
        error: function(obj, type, expobj) {
            alert("Failed to update shelf list");
        },
        success: function(r) {
            //console.log('Updated Shelf list with content from the server: '+r);
        }
    });    
}

function hide_shelf_events_from_view()
{
    //console.log('hide_shelf_events_from_view');
    jQuery.each( shelf_contents, function(i,n){
        //console.log('hide event with id event-'+n.guid);
        jQuery('#event-'+n.guid).hide().attr({in_shelf: 'true'});
        jQuery('div.event-toolbar-holder div.event-toolbar:visible').hide();
    });
}
function unhide_shelf_events_from_view()
{
    //console.log('unhide_shelf_events_from_view');
    jQuery.each( shelf_contents, function(i,n){
        //console.log('unhide event with id event-'+n.guid);
        jQuery('#event-'+n.guid).show().attr({in_shelf: 'false'});
    });
}

function move_event_to_shelf(identifier, event_data)
{
    //console.log('move_event_to_shelf: '+identifier);
    
    var existing = jQuery.grep( shelf_contents, function(n,i){
       return n.guid == identifier;
    });
    
    //console.log('existing: '+existing);
    //console.log('existing.length: '+existing.length);
    
    if (existing.length == 0)
    {
        //console.log('Event isnt in the shelf yet. Add it now.');
        var next_idx = shelf_contents.push({guid: identifier, data: event_data});
        //console.log('shelf_contents['+(next_idx-1)+']: '+shelf_contents[next_idx-1]);
        //console.log('shelf_contents['+(next_idx-1)+'].data.title: '+shelf_contents[next_idx-1].data.title);
        
        save_shelf_contents();
    }
    else
    {
        //console.log('Event is in the shelf already. Do nothing');
    }
}

function activate_shelf_item(identifier)
{
    //console.log('activate_shelf_item: '+identifier);
    
    var existing = jQuery.grep( shelf_contents, function(n,i){
       return n.guid == identifier;
    });
    
    if (existing.length > 0)
    {
        //console.log('Event is in the shelf');
        
        if (   active_shelf_item
            && jQuery('#shelf-item-list li.active')[0].id == ('shelf-list-item-'+identifier))
        {
            //console.log('Already active, deactivating');
            active_shelf_item = false;
            jQuery('#shelf-item-list li.active').attr('class', '');
        }
        else
        {
            //console.log('activating');
            jQuery('#shelf-item-list li.active').attr('class', '');

            active_shelf_item = identifier;
            jQuery('#shelf-list-item-'+identifier).attr('class', 'active');            
        }
    }
    else
    {
        //console.log('Event isnt in the shelf!');
    }
}

function attach_active_shelf_item(timestamp, identifier)
{
    //console.log("attach_active_shelf_item timestamp: "+timestamp+" identifier: "+identifier);
}

function empty_shelf()
{
    var url = 'midcom-exec-org.maemo.calendar/shelf.php?action=empty';
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: HOST_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to empty shelf list");
        },
        success: function(r) {
            unhide_shelf_events_from_view();
            shelf_contents = Array();
            jQuery('#shelf-item-list').html('');
            //console.log('Emptied Shelf list with response from the server: '+r);
        }
    });
}

function show_event_delete_form(identifier)
{
    //console.log('show_event_delete_form '+identifier);
    var url = 'ajax/event/delete/' + identifier;
    load_modal_window(url);
}

function enable_event_delete_form(identifier)
{
    //console.log('enable_event_delete_form '+identifier);
    var url = 'ajax/event/delete/' + identifier;
        
    jQuery.ajaxSetup({global: false});
    var options = { 
        //beforeSubmit:  show_processing,
        success:       show_results,
        url:       APPLICATION_PREFIX + url,
        type:      'post',
        timeout:   12000 
    }; 
     
    jQuery('#event-delete-form').ajaxForm(options);
}

function on_event_deleted(identifier)
{
    close_modal_window();
    jQuery('#event-'+identifier).remove();
    jQuery('#event-'+identifier+"-toolbox").remove();
}

function show_results(response, status)
{
    //console.log("show_results status: "+status);
    
    if (status == "success")
    {
        //run_scripts(response);
        set_modal_window_contents(response);        
    }
    if (status == "Forbidden")
    {
        window.location = HOST_PREFIX + 'midcom-login-';
    }  
}

function check_dm2_form_submit(formData, jqForm, options)
{
    //console.log("check_form_submit");
    
    var return_value = true;
    
    jQuery.each( formData, function(i,n){
        //console.log(i+": found form item "+n.name+" with value: "+n.value);
        
        if (n.name == 'midcom_helper_datamanager2_cancel')
        {
            if (typeof(options.oncancel) == 'function')
            {
                return_value = options.oncancel();
            }
            close_modal_window();
            return_value = false;
        }
    });
    
    return return_value;
}

function takeover_dm2_form(options)
{
    //console.log("takeover_dm2_form");
    
    var form = jQuery("#org_maemo_calendar");
    
    var url = form[0].action;

    if (options.url)
    {
        url = options.url;
    }
    
    if (url.substr(0,1) == '/')
    {
        // url = APPLICATION_PREFIX + url.substr(1);
        url = url.substr(1);
    }
    
    if (   url.substr(0,7) != 'http://'
        && url.substr(0,8) != 'https://'
        && url.substr(0,4) != 'www.')
    {
        final_url = APPLICATION_PREFIX + url;
    }
    else
    {
        final_url = url;
    }
    
    // console.log("final url: "+final_url);
    
    jQuery.ajaxSetup({global: false});
    options = jQuery.extend({
		beforeSubmit: check_dm2_form_submit,
		success: show_results,
		url: final_url,
		type: form[0].method,
		timeout: 120000,
		oncancel: null
	}, options);
	
    jQuery('#org_maemo_calendar').ajaxForm(options);

    // jQuery('#org_maemo_calendar').submit(function() { 
    //     jQuery(this).ajaxSubmit(options); 
    //     return false;
    // });    
}

function enable_buddylist_search()
{
    //console.log('enable_buddylist_search');

    var url = 'midcom-exec-org.maemo.calendar/buddylist.php?action=search';
        
    jQuery.ajaxSetup({global: false});
    var options = { 
        beforeSubmit:  show_searching,
        success:       render_buddylist_search_results,
        url:       HOST_PREFIX + url,
        type:      'post',
        dataType:  'json',
        timeout:   12000 
    }; 
     
    jQuery('#buddylist-search-form').ajaxForm(options);    
}

function show_searching()
{
    jQuery('#search-indicator').show();
    jQuery('#buddylist-search-result-count span').html("0");
    jQuery('#buddylist-search-result-count').hide();
    jQuery('#buddylist-search-results').html('');
}

function render_buddylist_search_results(results)
{
    //console.log('render_buddylist_search_results count: '+results.count);
    
    var search_result_holder = jQuery('#buddylist-search-results');

    var results_tpl = function() {
        return [
            'table', { width: "100%", border: 0, cellspacing: 0, cellpadding: 0 }, [
                'thead', {}, [
                    'tr', {}, this.header_items
                ],
                'tbody', {}, this.result_items
            ]
        ];
    };
    
    var message_tpl = function() {
        return [
            'div', { class: "search-message" }, this.message
        ];
    };
    
    if (results.count > 0)
    {
        var data = jQuery.extend({
            header_items: [],
            result_items: []
        }, results);
        
        //console.log('found results!');
        
        jQuery('#buddylist-search-result-count span').html(results.count);
        jQuery('#buddylist-search-result-count').show();
        
        jQuery(search_result_holder).tplAppend(data, results_tpl);
    }
    else
    {
        //console.log('No results found!');
        
        var data = jQuery.extend({
            message: ''
        }, results);

        jQuery(search_result_holder).tplAppend(data, message_tpl);
    }
    
    jQuery('#search-indicator').hide();
}

function remove_item_from_results(identifier)
{
    //console.log("remove_item_from_results: "+identifier);
    
    var current_count = jQuery('#buddylist-search-result-count span')[0].innerHTML;
    var new_count = current_count - 1;    
    
    //console.log("current_count: "+current_count);
    //console.log("new_count: "+new_count);
        
    jQuery('#buddylist-search-result-count span').html(' '+new_count);

    jQuery('#result-item-'+identifier).fadeOut("slow",function(){
        jQuery('#result-item-'+identifier).remove();
    });
}

function add_person_as_buddy(identifier)
{
    //console.log('add_person_as_buddy: '+identifier);
    
    var url = 'ajax/buddylist/add/' + identifier;
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            alert("Failed to add person as buddy! exception type: "+type);
        },
        success: function(msg) {
            //console.log('person added as buddy, with message: '+msg);
            
            remove_item_from_results(identifier);
            refresh_buddylist();
        }
    });    
}

function remove_person_from_buddylist(identifier)
{
    //console.log('remove_person_from_buddylist: '+identifier);
    
    var url = 'ajax/buddylist/remove/' + identifier;
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            //console.log("Failed to remove person from buddylist! exception type: "+type);
        },
        success: function(msg) {
            jQuery('#buddylist-item-'+identifier).fadeOut("slow",function(){
                jQuery('#buddylist-item-'+identifier).remove();
            });
            jQuery('#buddylist-item-list').Highlight(800, '#4c4c4c');
            clean_up_person(identifier);
            //console.log('person removed from buddylist, with message: '+msg);
        }
    });    
}

function refresh_buddylist(ask_for_reload)
{
    //console.log('refresh_buddylist');
    
    var url = 'midcom-exec-org.maemo.calendar/buddylist.php?action=refresh_list';
    var list = jQuery("#buddylist-item-list");

    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: HOST_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            //console.log("Failed to refresh buddylist. Exception type: "+type);
        },
        success: function(r) {
            list.html(unescape(r));
            jQuery('#buddylist-item-list').Highlight(800, '#4c4c4c');
            //console.log("Buddylist refreshed!");
            
            if (ask_for_reload)
            {
                var refresh = confirm("You have approved new buddy. We should refresh to get that persons events. Refresh now?");

                if (refresh)
                {
                    window.location.reload(true);
                }                
            }
        }
    });
}

function approve_buddy_request(identifier)
{
    //console.log('approve_buddy_request: '+identifier);
    
    var url = 'ajax/buddylist/action/approve/' + identifier;
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            //console.log("Failed to approve buddy request! exception type: "+type);
        },
        success: function(msg) {
            jQuery('#pending-list-item-'+identifier).fadeOut("slow",function(){
                jQuery('#pending-list-item-'+identifier).remove();
            });
            
            var ask_for_reload = false;
            if (msg == 'added_new')
            {
                ask_for_reload = true;
            }
            
            refresh_buddylist(ask_for_reload);
            //console.log('buddy request approved, with message: '+msg);
        }
    });    
}

function deny_buddy_request(identifier)
{
    //console.log('deny_buddy_request: '+identifier);
    
    var url = 'ajax/buddylist/action/deny/' + identifier;
    jQuery.ajaxSetup({global: false});
    jQuery.ajax({
        type: "GET",
        url: APPLICATION_PREFIX + url,
        timeout: 12000,
        error: function(obj, type, expobj) {
            //console.log("Failed to deny buddy request! exception type: "+type);
        },
        success: function(msg) {
            jQuery('#pending-list-item-'+identifier).fadeOut("slow",function(){
                jQuery('#pending-list-item-'+identifier).remove();
            });
            //console.log('buddy request denied, with message: '+msg);
        }
    });    
}

function clean_up_person(identifier)
{
    //Remove calendar from list
    var search_string = "#calendar-list-item-" + identifier;
    jQuery(search_string).remove();
    //Remove calendar tags from list
    var search_string = "#calendar-list-item-" + identifier + "-tags";
    jQuery(search_string).remove();
    //Remove calendar layer
    var search_string = "#calendar-layer-" + identifier;
    jQuery(search_string).fadeOut("slow",function(){
        jQuery(search_string).remove();
    });
}

function edit_calendar_layer_properties(layer_id)
{
    //console.log('edit_calendar_layer_properties layer_id: '+layer_id);

    var url = 'midcom-exec-org.maemo.calendar/layers.php?action=show_update_layer&layer_id='+layer_id;
    load_modal_window(url);
}
function edit_calendar_layer_tag_properties(layer_id, tag_id)
{
    //console.log('edit_calendar_layer_tag_properties layer_id: '+layer_id+' tag_id: '+tag_id);

    var url = 'midcom-exec-org.maemo.calendar/layers.php?action=show_update_tag&layer_id='+layer_id+'&tag_id='+tag_id;
    load_modal_window(url);
}

function enable_layer_update_form(layer_id, tag_id)
{
    //console.log('enable_color_change_form layer_id: '+layer_id+' tag_id: '+tag_id);
    
    var type = 'layer';
    var url = 'midcom-exec-org.maemo.calendar/layers.php?action=update_layer&layer_id='+layer_id;
    if (tag_id != undefined)
    {
        type = 'layer_tag';
        url = 'midcom-exec-org.maemo.calendar/layers.php?action=update_tag&layer_id='+layer_id+'&tag_id='+tag_id;
    }
        
    jQuery.ajaxSetup({global: false});
    var options = { 
        beforeSubmit:  show_processing,
        success:       processing_successfull,
        url:       HOST_PREFIX + url,
        type:      'post',
        //dataType:  'json',
        timeout:   12000
    }; 
    
    var form_id = '#update-' + type + '-form';    
    jQuery(form_id).ajaxForm(options);
}

function enable_tag_create_form(layer_id)
{
    url = 'midcom-exec-org.maemo.calendar/layers.php?action=create_tag&layer_id='+layer_id;
    jQuery.ajaxSetup({global: false});
    var options = { 
        beforeSubmit:  show_processing,
        success:       processing_successfull,
        url:       HOST_PREFIX + url,
        type:      'post',
        timeout:   12000
    }; 
    
    var form_id = '#create-new_tag-form';    
    jQuery(form_id).ajaxForm(options);
}

function show_processing(formData, jqForm, options)
{
    //console.log('show_processing');
}
function processing_successfull(responseText, statusText)
{
    //console.log('processing_successfull responseText:'+responseText);
    close_modal_window();
    
    if (responseText > 0)
    {        
        window.location = window.location;
        //window.location.reload(true);
    }
}

function on_view_update()
{
    hide_shelf_events_from_view();
}

function show_layout()
{
    jQuery('#calendar-loading').hide();
    jQuery('#calendar-holder').show();
    jQuery('div.application div.header').show();
    jQuery('#main-panel').show();
}

function modify_foreground_color(search_string)
{
    //console.log("modify_foreground_color search_string: "+search_string);
    //console.log("found "+jQuery(search_string).length+" matches.");
    jQuery.each( jQuery(search_string), function(i,n){
        execute_modify_foreground_color(n);
    });
}

function execute_modify_foreground_color(element)
{
    function bg_to_bits(bgcolor)
    {
        bgcolor = String(bgcolor);
        bgcolor = bgcolor.replace(/ /g,'');
        bgcolor = bgcolor.toLowerCase();

        var rgb_bits = [];
        var color_defs = [
            {
                re: /^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$/,
                process: function (bits){
                    return [
                        parseInt(bits[1]),
                        parseInt(bits[2]),
                        parseInt(bits[3])
                    ];
                }
            },
            {
                re: /^(\w{2})(\w{2})(\w{2})$/,
                process: function (bits){
                    return [
                        parseInt(bits[1], 16),
                        parseInt(bits[2], 16),
                        parseInt(bits[3], 16)
                    ];
                }
            }
        ];
        
        for (var i = 0; i < color_defs.length; i++) {
            var re = color_defs[i].re;
            var processor = color_defs[i].process;
            var bits = re.exec(bgcolor);
            if (bits) {
                rgb_bits = processor(bits);
            }
        }
        
        if (   rgb_bits[0] < 0
            || isNaN(rgb_bits[0]))
        {
            rgb_bits[0] = 0;
        }
        else if (rgb_bits[0] > 255)
        {
            rgb_bits[0] = 255;
        }
        
        if (   rgb_bits[1] < 0
            || isNaN(rgb_bits[1]))
        {
            rgb_bits[1] = 0;
        }
        else if (rgb_bits[1] > 255)
        {
            rgb_bits[1] = 255;
        }

        if (   rgb_bits[2] < 0
            || isNaN(rgb_bits[2]))
        {
            rgb_bits[2] = 0;
        }
        else if (rgb_bits[2] > 255)
        {
            rgb_bits[2] = 255;
        }

        // rgb_bits[0] = (rgb_bits[0] < 0 || isNaN(rgb_bits[0])) ? 0 : ((rgb_bits[0] > 255) ? 255 : rgb_bits[0]);
        // rgb_bits[1] = (rgb_bits[1] < 0 || isNaN(rgb_bits[1])) ? 0 : ((rgb_bits[1] > 255) ? 255 : rgb_bits[1]);
        // rgb_bits[2] = (rgb_bits[2] < 0 || isNaN(rgb_bits[2])) ? 0 : ((rgb_bits[2] > 255) ? 255 : rgb_bits[2]);
                
        return rgb_bits;
    }
    
    function RGBToHSL(rgb)
    {
        var min, max, delta, h, s, l;
        var r = rgb[0], g = rgb[1], b = rgb[2];
        min = Math.min(r, Math.min(g, b));
        max = Math.max(r, Math.max(g, b));
        delta = max - min;
        l = (min + max) / 2;
        s = 0;
        if (l > 0 && l < 1)
        {
            x = (2 - 2 * l);
            if (l < 0.5)
            {
                (2 * l)
            }
            
            s = delta / x;
            // s = delta / (l < 0.5 ? (2 * l) : (2 - 2 * l));
        }
        h = 0;
        if (delta > 0)
        {
            if (max == r && max != g)
            {
                h += (g - b) / delta;
            }
            if (max == g && max != b)
            {
                h += (2 + (b - r) / delta);
            }
            if (max == b && max != r)
            {
                h += (4 + (r - g) / delta);
            }
            
            h /= 6;
        }
        
        return [h, s, l];
    }
    
    bg = jQuery(element).css('background');
    if (   bg == undefined
        || bg == "")
    {
        bg = jQuery(element).css('background-color');
    }

    if (   bg == undefined
        || bg == "")
    {
        bg = jQuery(element).attr('bgcolor');
    }
    
    if (   bg == undefined
        || bg == "")
    {
        jQuery(element).css({color: "#3c3c3c"});
        return false;
    }

    if (bg.charAt(0) == '#')
    {
        bg = bg.substr(1,6);
    }
    
    var hsl = RGBToHSL(bg_to_bits(bg));
        
    var fg_color = '#ffffff';
    if (hsl[0] < 0.5)
    {
        fg_color = '#3c3c3c';
    }
    
    jQuery(element).css({
      color: fg_color
    });    
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