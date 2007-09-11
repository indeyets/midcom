/*
 * midcom_helper_datamanager2_widget_chooser_widget - jQuery plugin 1.0
 *
 * Jerry Jalava <jerry.jalava@gmail.com>
 *
 */

/*
 * @name midcom_helper_datamanager2_widget_chooser_widget
 * @cat Plugins/Midcom
 * @type jQuery
 * @param String|Object url an URL to data backend
 * @param Map options Optional settings
 * @option String widget_id The id of the widget instance. Default: chooser_widget
 * @option Number result_limit Limit the number of items in the results box. Is also send as a "limit" parameter to backend on request. Default: 10
 * @option Boolean renderer_callback If this is set to true, then when user presses tab it creates a tag from current input value if we don't have any results. Default: false
 * @option String id_field Name of the objects field to be used as identifier. Default: guid
 * @option String creation_handler Url which to load when creating new object. Default: null
 * @option String creation_default_key key which will be used when sending the query value to the creation handler. Default: 'title'
 * @option Boolean creation_mode If this is set to true, a create new -button is showed. Default: false
 */
 


jQuery.fn.extend({
	midcom_helper_datamanager2_widget_chooser_widget: function(url, options) {
		options = jQuery.extend({}, jQuery.midcom_helper_datamanager2_widget_chooser.defaults, {
			url: url || null
		}, options);
        // return this.each(function() {
		return new jQuery.midcom_helper_datamanager2_widget_chooser(this, options);
        // });
	},
	midcom_helper_datamanager2_widget_chooser_add_result_item: function(item) {
		return this.trigger("add_result_item",[item]);
	},
	midcom_helper_datamanager2_widget_chooser_remove_result_item: function(item_id) {
		return this.trigger("remove_result_item",[item_id]);
	}
});

jQuery.midcom_helper_datamanager2_widget_chooser = function(input, options) {
	var KEY = {
		UP: 38,
		DOWN: 40,
		DEL: 46,
		TAB: 9,
		RETURN: 13,
		ESC: 27,
		COMMA: 188
	};

	var CLASSES = {
		LOADING: "chooser_widget_search_loading",
		IDLE: "chooser_widget_search_idle",
		FAILED: "chooser_widget_search_failed"
	};

	var timeout;
	var previousValue = "";
	var hasFocus = 0;
	var lastKeyPressCode;
	var last_term;
		
	var create_button = null;
	var creation_dialog = null;
	var input_element = jQuery(input).attr("autocomplete", "off").addClass(CLASSES.IDLE);
	var insert_after = input_element;
	    
    if (options.creation_mode)
    {
        enable_creation_mode();
        insert_after = creation_dialog;
    }
    
    var results_holder = jQuery.midcom_helper_datamanager2_widget_chooser.ResultsHolder(options, input, insert_after, selectCurrent);    
    
	hideResultsNow();
	
	function selectCurrent()
	{
		var selected = results_holder.selected();
		if (! selected)
		{
			return false;
		}
		
		input_element.val('');
		input.focus();
		input_element.trigger("activate", [selected.data]);
		return true;
	}

	function onChange(crap, skipPrevCheck)
	{
		if (lastKeyPressCode == KEY.DEL)
		{
			return;
		}
		
		var currentValue = input_element.val();			    		
		if (currentValue == previousValue)
		{
			return;
		}
		
		previousValue = currentValue;
		
		if (currentValue.length >= options.min_chars)
		{
			currentValue = currentValue.toLowerCase();
			request(currentValue, receiveData, stopLoading);
		}
		else
		{
			stopLoading();
		}
	};
	
	function hideResults()
	{
		clearTimeout(timeout);
		timeout = setTimeout(hideResultsNow, 200);
	};

	function hideResultsNow()
	{
		results_holder.hide();
		clearTimeout(timeout);
	};

	function receiveData(q, data) {
		if ( data && data.length && hasFocus )
		{
			stopLoading();
			results_holder.display(data, q);
			results_holder.show();
		}
		
		input_element.removeClass(CLASSES.LOADING);
		input_element.removeClass(CLASSES.FAILED);
		input_element.addClass(CLASSES.IDLE);
	};
	
	function request(term, success, failure)
	{
        last_term = term;
        
		input_element.removeClass(CLASSES.IDLE);
		input_element.removeClass(CLASSES.FAILED);	    
	    input_element.addClass(CLASSES.LOADING);
	    
		var data = false;
		
		term = term.toLowerCase();
        
		jQuery.ajax({
		    type: "POST",
			url: options.url,
			dataType: 'xml',
			data: jQuery.extend({
    			query: term,
    			limit: options.result_limit
    		}, {extra_params: options.extra_params}),
            error: function(obj, type, expobj) {
                failure(type, expobj);
            },
			success: function(data) {
				var parsed = parse(data);
				success(term, parsed);
			}
		});
	}
	
	function stopLoading(type, expobj)
	{	    
		input_element.removeClass(CLASSES.IDLE);
		input_element.removeClass(CLASSES.FAILED);
		input_element.removeClass(CLASSES.LOADING);
		if (type == "undefined")
		{
		    input_element.addClass(CLASSES.IDLE);
		}
		else
		{
		    input_element.addClass(CLASSES.FAILED);
		}
	}
	
	function parse(data)
	{
        var results = [];
        jQuery('result',data).each(function(idx) {            
            var rel_this = jQuery(this);
    	    
            results[idx] = {         	    
                id:rel_this.find("id").text(), 
                guid:rel_this.find("guid").text()
            };
            
            jQuery.each(options.result_headers,function(i,h){
                results[idx][h.name] = rel_this.find(h.name).text();
            });
        });

    	var parsed = [];
    	jQuery(results).each(function(idx){
    		var item = results[idx];
    		if (item) {
    			parsed[parsed.length] = {
    				id: item.id,
    				data: item
    			};
    		}    	    
    	});

    	return parsed;
	}

	input_element.keydown(function(event) {
		// track last key pressed
		lastKeyPressCode = event.keyCode;
		switch(event.keyCode) {
		
			case KEY.UP:
				event.preventDefault();
				if ( results_holder.visible() ) {
					results_holder.prev();
				} else {
					onChange(0, true);
				}
				break;
				
			case KEY.DOWN:
				event.preventDefault();
				if ( results_holder.visible() ) {
					results_holder.next();
				} else {
					onChange(0, true);
				}
				break;
			
			case KEY.TAB:
			case KEY.RETURN:
				if( selectCurrent() ){
					input_element.focus();
					event.preventDefault();
				}
				break;
				
			case KEY.ESC:
				break;
				
			default:
                clearTimeout(timeout);
                timeout = setTimeout(onChange, options.delay);
				break;
		}
	}).keypress(function(event) {
		// having fun with opera - remove this binding and Opera submits the form when we select an entry via return
		switch(event.keyCode) {
		    case KEY.TAB:
		    case KEY:RETURN:
		        event.preventDefault();
		        break;
	    }
	}).focus(function(){
		// track whether the field has focus, we shouldn't process any
		// results if the field no longer has focus
		hasFocus++;
	}).blur(function() {
		hasFocus = 0;
		input_element.removeClass(CLASSES.LOADING);
		input_element.removeClass(CLASSES.FAILED);
		input_element.addClass(CLASSES.IDLE);
	}).click(function() {
		// show results when clicking in a focused field
		if ( hasFocus++ > 1 && !results_holder.visible() ) {
			onChange(0, true);
		}
	}).bind("activate", function(event, data){
	    input_element.focus();
	    results_holder.activate_item(data);
	}).bind("add_result_item", function(event, item){
	    results_holder.add_item(item);
	}).bind("remove_result_item", function(event, item_id){
	    results_holder.del_item();
	});
	
	function enable_creation_mode()
	{
        input.css({float: 'left'});
        
	    creation_dialog = jQuery('#' + options.widget_id + '_creation_dialog');
	    create_button = jQuery('#' + options.widget_id + '_create_button');
	    create_button.bind('click', function(){
	        var creation_url = options.creation_handler;
	        
	        if (last_term != "undefined")
	        {
	            creation_url += '?defaults[' + options.creation_default_key + ']=' + last_term;
	        }
            
            var iframe = ['<iframe src="' + creation_url + '"'];
            iframe.push('id="' + options.widget_id + 'chooser_widget_creation_dialog_content"');
            iframe.push('class="chooser_widget_creation_dialog_content"');
            iframe.push('frameborder="0"');
            iframe.push('marginwidth="0"');
            iframe.push('marginheight="0"');
            iframe.push('width="600"');
            iframe.push('height="450"');
            iframe.push('scrolling="auto"');
            iframe.push('/>');

            var iframe_html = iframe.join(' ');
	        jQuery('.chooser_widget_creation_dialog_content_holder', creation_dialog).html(iframe_html);
	        
            jQuery('#' + options.widget_id + '_creation_dialog').jqmShow();
	    });
	}
};

jQuery.midcom_helper_datamanager2_widget_chooser.defaults = {
    widget_id: 'chooser_widget',
	delay: 400,
	result_limit: 10,
	renderer_callback: false,
	allow_multiple: true,
	id_field: 'guid',
	creation_mode: false,
	creation_handler: null,
	creation_default_key: null
};

jQuery.midcom_helper_datamanager2_widget_chooser.ResultsHolder = function(options, input, insert_after, select_function)
{
	var CLASSES = {
		HOVER: "chooser_widget_result_item_hover",
		ACTIVE: "chooser_widget_result_item_active",
		INACTIVE: "chooser_widget_result_item_inactive",
		DELETED: "chooser_widget_result_item_deleted"
	};

	// Create results element
	var element = jQuery('<div id="' + options.widget_id + '_results"></div>')
		.hide()
		.addClass('chooser_widget_results_holder');
	jQuery(insert_after).after( element );

	var headers = jQuery('<ul class="chooser_widget_headers"></ul>').appendTo(element);
	var list = jQuery('<ul class="chooser_widget_results"></ul>').appendTo(element);

    var has_content = false,
        list_items = [],
        selected_items = [],
        list_jq_items,
		active = -1,
		data;

	list.mouseover( function(event) {
        var jq_elem = jQuery(target(event)).addClass(CLASSES.HOVER);
	}).mouseout( function(event) {
		jQuery(target(event)).removeClass(CLASSES.HOVER);
	}).click(function(event) {
		select_function();
		return false;
	});

	function target(event)
	{
		var element = event.target;
		if (element)
		{
		    if (element.tagName == "UL")
		    {
		        element = jQuery(element).find('li').eq(0);
		        return element;
		    }
		    
    		while(element.tagName != "LI")
    		{
    			element = element.parentNode;
    		}
		}
		return element;
	}
	
	create_headers();
    
    function create_headers()
    {
        jQuery.each( options.result_headers, function(i,n) {
            var li_elem = jQuery("<li>")
                .addClass('chooser_widget_header_item')
                .attr({ id: 'chooser_widget_header_item_'+n.name });
            var item_content = jQuery("<div>")
                .html( n.title )
                .appendTo(li_elem);
            li_elem.appendTo(headers);
        });
    }
    
	function dataToDom()
	{
		for (var i=0; i < data.length; i++) {
			if (!data[i])
				continue;
			
			add(data[i].data);
		}
		list_jq_items = list.find("li");
		if ( options.select_first ) {
			list_jq_items.eq(0).addClass(CLASSES.ACTIVE);
			active = 0;
			var active_id = list_items[0];

    	    jQuery('#'+options.widget_id + '_result_item_'+active_id+'_input', list).attr({ value: active_id });
    	    jQuery('#'+options.widget_id + '_result_item_'+active_id).attr("keep_on_list","true");
    	    selected_items.push(active_id);
		}
	}
	
	function can_add(id)
	{
	    //console.log("can_add id: "+id);
	    
	    var existing = false;
        existing = jQuery.grep( list_items, function(n,i){
           return n == id;
        });
        if (existing == id)
        {
            // jQuery('#'+options.widget_id+'_result_item_'+id,list).hide("fast",function(){
            //    jQuery('#'+options.widget_id+'_result_item_'+id,list).show('fast');
            //  });
            return false;
        }
	    
	    return true;
	}
	
	function add(data)
	{
	    //console.log('ResultsHolder add');
	    //console.log('data.id: '+data.id);
	    //console.log('data.guid: '+data.guid);
	    
	    var n=null;
        jQuery.each( options.result_headers, function(i,n) {
            //console.log("data."+n.name+": "+data[n.name]);
        });
	    
	    var item_id = data[options.id_field];
	    //console.log('options.id_field: '+options.id_field);
        //console.log('item_id: '+item_id);
        if (! can_add(item_id))
        {
            //console.log("Can't add!");
            return false;
        }
        
        //console.log("Can add!");
        
        if (!has_content)
        {
            has_content = true;
            element.show();
        }
	    
	    if (options.allow_multiple)
	    {
    	    var input_elem_name = options.widget_id + "_selections[" + item_id + "]";
	    }
        else
        {
            var input_elem_name = options.widget_id + "_selections";
        }
        
	    var li_elem = jQuery("<li>")
	    .attr({ id: options.widget_id + '_result_item_'+item_id })
	    .attr("deleted","false")
	    .attr("keep_on_list","false")
	    .attr("pre_selected","false")
	    .addClass("chooser_widget_result_item")
    	.click(function(event) {
    	    var li_element = target(event);

    	    var current_keep_status = jQuery(li_element).attr("keep_on_list");
            var current_delete_status = jQuery(li_element).attr("deleted");
            var current_presel_status = jQuery(li_element).attr("pre_selected");
    	    if (current_keep_status == "true")
    	    {
    	        if (current_delete_status == "false")
    	        {
            		jQuery(li_element).removeClass(CLASSES.ACTIVE);
            		if (current_presel_status == "true")
            		{
                		remove(item_id);
            		}
            		else
            		{
            		    inactivate(item_id);
            		}
    	        }
    	        else
    	        {
            		restore(item_id);    	            
    	        }
    	    }
    	    else
    	    {
        		activate(item_id);
    	    }

    		return false;
    	});
    	
	    if (data['pre_selected'])
	    {
    	    li_elem.attr("pre_selected","true");
	    }    	
    	
    	if (options.renderer_callback)
    	{
    	    //console.log("use renderer");
    	    //TODO: Implement
    	    // PONDER:  How should we handle the renderer_callback rendering?
    	    //          We could use custom javascript function, or require the data
    	    //          object to contain a content field which is allready formatted html...
            var item_content = jQuery("<div>")
            // .attr({ id: options.widget_id + '_result_item_'+data.id })
            // .html( midcom_helper_datamanager2_widget_chooser_format_item(data, options) )
            .appendTo(li_elem);
    	}
    	else
    	{
    	    var item_content = midcom_helper_datamanager2_widget_chooser_format_item(data,options)
    	    .appendTo(li_elem);
            var input_elem = jQuery("<input>")
            .attr({ type: 'hidden', name: input_elem_name, value: 0, id: options.widget_id + '_result_item_'+item_id+'_input' })
            .hide()
            .appendTo(li_elem);
    	}
	    
	    li_elem.appendTo(list);
	    
	    list_items.push(item_id);
	}

	function moveSelect(step) {
		active += step;
		wrapSelection();
		list_jq_items.removeClass(CLASSES.HOVER);

        var jq_elem = list_jq_items.eq(active);
		if (jq_elem.attr('class') != CLASSES.ACTIVE)
		{
    		jq_elem.addClass(CLASSES.HOVER);		    
		}
	};
	
	function wrapSelection() {
		if (active < 0) {
			active = list_jq_items.size() - 1;
		} else if (active >= list_jq_items.size()) {
			active = 0;
		}
	}
    
	function remove(id)
	{
	    jQuery('#'+options.widget_id + '_result_item_'+id+'_input', list).attr({ value: 0 });
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.ACTIVE);
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.INACTIVE);
        jQuery('#'+options.widget_id + '_result_item_'+id).addClass(CLASSES.DELETED);
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("deleted","true");
	    selected_items = jQuery.grep( selected_items, function(n,i){
            return n != id;
        });
	}
	
	function restore(id)
    {
	    jQuery('#'+options.widget_id + '_result_item_'+id+'_input', list).attr({ value: id });
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.DELETED);
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.INACTIVE);
	    jQuery('#'+options.widget_id + '_result_item_'+id).addClass(CLASSES.ACTIVE);
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("deleted","false");
	    selected_items.push(id);
    }

	function activate(id)
    {
	    jQuery('#'+options.widget_id + '_result_item_'+id+'_input', list).attr({ value: id });
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.DELETED);
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.INACTIVE);
        jQuery('#'+options.widget_id + '_result_item_'+id).addClass(CLASSES.ACTIVE);
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("keep_on_list","true");
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("deleted","false");
	    selected_items.push(id);
    }
    
	function inactivate(id)
    {
	    jQuery('#'+options.widget_id + '_result_item_'+id+'_input', list).attr({ value: id });
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.DELETED);
	    jQuery('#'+options.widget_id + '_result_item_'+id).removeClass(CLASSES.ACTIVE);
        jQuery('#'+options.widget_id + '_result_item_'+id).addClass(CLASSES.INACTIVE);
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("keep_on_list","false");
	    jQuery('#'+options.widget_id + '_result_item_'+id).attr("deleted","false");
	    selected_items.push(id);
    }
    
    function delete_unseleted_from_list()
    {
        list_jq_items = list.find("li");
        var removed_items = [];
        jQuery.each( list_items, function(i,n){
            if (n != undefined)
            {
                if (jQuery('#'+options.widget_id + '_result_item_'+n).attr("keep_on_list") == "false")
                {
                    jQuery('#'+options.widget_id + '_result_item_'+n).remove();
            	    removed_items.push(n);
                }      
            }
        });
	    jQuery.each( removed_items, function(i,n){
	        list_items = unset(list_items, n, false, true);
        });
    }
    
    function unset(array, valueToUnset, valueOrIndex, isHash)
    {
        var output = new Array(0);
        for (var i in array)
        {
            if (! valueOrIndex)
            {
                if (array[i] == valueToUnset)
                {
                    continue;
                };
                if (! isHash)
                {
                    output[++output.length-1]=array[i];
                }
                else
                {
                    output[i]=array[i];
                }
            }
            else
            {
                if (i == valueToUnset)
                {
                    continue;
                };
                if (! isHash)
                {
                    output[++output.length-1]=array[i];
                }
                else
                {
                    output[i]=array[i];
                }
            }
        }

        return output;
    }

    function clear_unselected()
    {
        delete_unseleted_from_list();
    }
	
	return {
		display: function(d) {
			clear_unselected();
			data = d;
			dataToDom();
		},
	    add_item: function(item)
	    {
	        add(item);
	        var item_id = item[options.id_field];
	        activate(item_id);
	    },
	    del_item: function(item)
	    {
	        var item_id = item[options.id_field];
	        remove(item_id);
	    },
	    activate_item: function(item)
	    {
	        var item_id = item[options.id_field];
	        activate(item_id);
	    },
		visible : function() {
			return element.is(":visible");
		},
		next: function() {
			moveSelect(1);
		},
		prev: function() {
			moveSelect(-1);
		},
		hide: function() {
			element.hide();
			active = -1;
		},
		current: function() {
			return this.visible() && (list_jq_items.filter("." + CLASSES.ACTIVE)[0] || options.select_first && list_jq_items[0]);
		},
		show: function() {
			element.show();
		},
		selected: function() {
			return data && data[active];
		}
	};
};

jQuery.midcom_helper_datamanager2_widget_chooser.MoveSelection = function(field, start, end)
{
	if( field.createTextRange )
	{
		var selRange = field.createTextRange();
		selRange.collapse(true);
		selRange.moveStart("character", start);
		selRange.moveEnd("character", end);
		selRange.select();
	}
	else if( field.setSelectionRange )
	{
		field.setSelectionRange(start, end);
	}
	else
	{
		if( field.selectionStart )
		{
			field.selectionStart = start;
			field.selectionEnd = end;
		}
	}
	field.focus();
};

function midcom_helper_datamanager2_widget_chooser_format_item(item, options)
{
    var formatted = '';

    var item_parts = jQuery("<div>")
    .attr({ id: options.widget_id + '_result_item_'+item.id })
    .addClass("chooser_widget_result_item_parts");

    var item_content = jQuery("<div>")
        .addClass('chooser_widget_item_part')
        .attr({ id: 'chooser_widget_item_part_id' })
        .html( item.id )
        .hide()
        .appendTo(item_parts);

    jQuery.each( options.result_headers, function(i,n) {
        var value = null;
        
        if (   n.name == 'start'
            || n.name == 'end')
        {
            value = midcom_helper_datamanager2_widget_chooser_format_value('datetime', item[n.name]);
        }
        else
        {
            value = midcom_helper_datamanager2_widget_chooser_format_value('unescape', item[n.name]);
        }
        
        item_content = jQuery("<div>")
        .addClass('chooser_widget_item_part')
        .attr({ id: 'chooser_widget_item_part_'+n.name })
        .html( value )
        .appendTo(item_parts);
    });

    item_content = jQuery("<div>")
    .addClass('chooser_widget_item_part')
    .attr({ id: 'chooser_widget_item_part_status' })
    .html( "&nbsp;" )
    .appendTo(item_parts);    
    
    return item_parts;
}

function midcom_helper_datamanager2_widget_chooser_format_value(format, value)
{
    var format = format || 'unescape';
    var formated = null;
    
    switch(format)
    {
        case 'unescape':
            formatted = unescape(value);
            break;
        case 'datetime':
            var date = new Date();
            date.setTime((value*1000));
            var date_str = date.getFullYear() + '-' + date.getMonth() + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes();
            
            formatted = date_str;
            break;
        default:
            formatted = value;
            break;
    }
    
    if (formatted == null)
    {
        formatted = value;
    }
    
    return formatted;
}