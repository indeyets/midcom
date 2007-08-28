function validate_fields(form)
{
    var inputs = jQuery(":input",form);
    var uname_passed = false;
    var pwd_passed = false;
    
    function validate_username(input)
    {
        console.log("validate_username");
    }
    
    function validate_password(input)
    {
        console.log("validate_password");
    }

    console.log("inputs: "+inputs);
    jQuery(inputs).each(function(i,n){
        console.log("i: "+i+" n: "+n);
        if (n.attr('name') == 'username')
        {
            validate_username(n);
        }
        if (n.attr('name') == 'password')
        {
            validate_password(n);
        }
    });
    
    if (   !uname_passed
        || !pwd_passed)
    {
        alert("Please enter username & password.");
        return false;
    }
    
    return true;
}

jQuery(function() {

    /**
     * Invite tabs
    **/        
    var itabs_containers = {};
    jQuery('//div/div.invite_tabs').each(function(i,c){
        var tabs = jQuery(c);
        var container = tabs.parent();
    
        var active_str = 'ay';
        var prev_active_str = active_str;
            
        var active_strings = {
            itab_item_yahoo: 'ay',
            itab_item_gmail: 'ag',
            itab_item_myspace: 'am',
            itab_item_hotmail: 'ah'
        };
    
        itabs_containers[container[0].id] = jQuery('div.tabs_content', container);
    
        jQuery('li', tabs).each(function(i,n){
            var tab = jQuery(n);

            tab.bind('click',function(e){
                var trgt = target(e);
                var link = jQuery(trgt).find('a').eq(0);
            
                prev_active_str = active_str;
                active_str = active_strings[trgt.id];
            
                if (active_str == prev_active_str)
                {
                    return;
                }
            
                jQuery("li", parent(e)).attr('class',active_str).index(trgt);
                jQuery(trgt).attr('class',active_str);
            
                var tab_content = jQuery(link[0].hash);
                if (! tab_content.is(":visible") )
                {
                    var to_hide = itabs_containers[container[0].id].filter(':visible');

                    var content_id = link[0].hash.replace('#', '');
                    tab_content.attr('id', '');
                    setTimeout(function() {
                        tab_content.attr('id', content_id);
                    }, 0);

                    tab_content.show();
                    to_hide.hide();                        
                }
                else
                {
                    var content_id = link[0].hash.replace('#', '');
                    tab_content.attr('id', '');
                    setTimeout(function() {
                        tab_content.attr('id', content_id);
                    }, 0);                        
                }
            });
    
        	function parent(event) {
        		var element = event.target;
        		if (element)
        		{
        		    if (element.tagName == "UL")
        		    {
        		        return element;
        		    }

            		while(element.tagName != "UL")
            		{
            			element = element.parentNode;
            		}
        		}
        		return element;
        	}

        	function target(event) {
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
    	});	
    });
    /**
     * Invite tabs end
    **/

});