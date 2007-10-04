/**
 * background mover - jQuery plugin
 */

/**
 * Moves elements background depending on given parameters
 *   
 * @example jQuery('a.image-link').moveBackground({startPos: 0, endPos: -16});
 * @cat plugin
 * @type jQuery 
 *
 */
jQuery.fn.moveBackground = function(settings) {
	settings = jQuery.extend({
		direction: "down",
		startPos: 0,
		endPos: 0,
		time: null,
		startLeft: null,
		startTop: null,
		callback: null,
		callbackArgs: [],
	}, settings);

	var element = this[0];
	var direction = settings.direction;
	var startPos = settings.startPos;
	var endPos = settings.endPos;
	var time = settings.time;

	if(direction == "down" || direction == "d")
	{
		endPos -= 1;
	}
	
	var left = settings.startLeft != null ? settings.startLeft : 0;
	var top = settings.startTop != null ? settings.startTop : 0;
	
	if(direction == "down" || direction == "d") {
		top = startPos;
	}
	
	function anim()
	{
		var leftPos = "px";
		var topPos = "px";
		
		if(direction == "down" || direction == "d")
		{
			if (endPos < top) {
				top -= 1;
			}
			
			if (top != endPos) {
				setTimeout(anim, time);
			} else {
				if(settings.callback != null) {
					element.style.backgroundPosition = "";
					settings.callback.call(settings.callbackArgs[0], settings.callbackArgs);
				}
				return this;
			}
		}
		
		leftPos = left + leftPos;
		topPos = top + topPos;
		
		var posStr = leftPos + " " + topPos;

		element.style.backgroundPosition = posStr;
	}

	anim();
};

/**
 * privilege renderer - jQuery plugin
 */

/**
 * Create a multiface checkbox interface out of a simple form structure.
 *   
 * @example jQuery('#select-holder').render_privilege();
 * @cat plugin
 * @type jQuery 
 *
 */
jQuery.fn.render_privilege = function(settings) {
	settings = jQuery.extend({
		imageWidth: 16,
		imageHeight: 16,
		maxIndex: 3,
		animate: false,
	}, settings);
	
    return this.each(function() {
        var div = jQuery("<div/>").insertAfter( this );
		
		var list_menu = jQuery(this).find("select")[0];
		var nextValue = 0;
		var selected_index = 0;
		
		jQuery(this).find('select').each(function(){
		    jQuery(this).bind('onchange', function(e, val){
                div.find('div.privilege_val').trigger('click', []);
            });
		});
		
        jQuery(this).find("select option").each(function(){
			//var classes = [ 'inherited', 'inherited-allow', 'inherited-deny', 'allow', 'deny'];
			var classes = [ null, 'allow', 'deny', 'inherited'];
			var block_style = this.selected ? "style='display: block;'" : "";
            div.append( "<div class='privilege_val' " + block_style + "><a class='" + classes[this.value] + "' href='#" + this.value + "' title='" + this.innerHTML + "'>" + this.innerHTML + "</a></div>" );
        });

        var selects = div.find('div.privilege_val').click(click);
        
        function click() {
            selected_index = selects.index(this) + 1;

			var href = jQuery(this).find('a')[0].href;
			var currentValue = href.charAt(href.length-1);
			
			if(prevValue == undefined) {
				var prevValue = currentValue;
			} else {
				prevValue = currentValue;
			}
			
			nextHref = jQuery(selects[(selected_index >= settings.maxIndex ? settings.maxIndex : selected_index)]).find('a')[0].href;
			nextValue = nextHref.charAt(nextHref.length-1);
			
			if(selected_index == settings.maxIndex) {
			 	selected_index = 0;
			 	var startPos = 0;
			} else {
				var startPos = 0 - (prevValue * settings.imageHeight);
			}

			var endPos = 0 - (nextValue * settings.imageHeight);
			
			if(settings.animate == true) {
				jQuery(jQuery(this).find('a')[0]).moveBackground({ startPos: startPos, endPos: endPos, time: 25, callback: showNext, callbackArgs: [this,selects[selected_index]] });
			} else {
				showNext([this]);
			}
			
			list_menu.selectedIndex = selected_index;
        	
			prevValue = currentValue;

            return false;
        }

		function showNext(args)
		{
			jQuery(args[0]).hide();
			jQuery(selects[selected_index]).show();			
		}

    }).hide();
};


jQuery.fn.privilege_actions = function(privilege_key) {
    return this.each(function() {
        var row = this;
        var actions_holder = jQuery('#privilege_row_actions_' + privilege_key, row);
        var clear_action = jQuery('<div class="privilege_action" />').insertAfter( actions_holder );
        var clear_action_icon = jQuery('<img src="' + MIDCOM_STATIC_URL + '/stock-icons/16x16/cancel.png" />').attr({
            alt: "Clear privileges",
            border: 0
        }).appendTo(clear_action);
        
        clear_action.bind('click', function(e){
            jQuery('select', row).each(function(i,n){
                jQuery(n).val(3);
                jQuery(n).trigger('onchange', [0]);
                jQuery(row).hide();
            });
        });
    });
};

// fix ie6 background flicker problem.
if ( jQuery.browser.msie == true )
    document.execCommand('BackgroundImageCache', false, true);
