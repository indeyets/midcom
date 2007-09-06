jQuery(function() {

    /**
     * Navigation
    **/        
    var nav_container = null;
    jQuery('//div.net_nemein_alphabeticalindex').each(function(i,c){
        var nav = jQuery(c);
        
        jQuery('li', nav).each(function(i,n){
        
            var item = jQuery(n);            
            if (item.attr("class") != "separator")
            {   
                item.bind('mouseover',function(e){
                    jQuery("li", parent(e)).removeClass("hover").index(target(e));
                    jQuery(target(e)).addClass("hover");
                });
                item.bind('mouseout',function(e){
                    jQuery(target(e)).removeClass("hover");
                });
                item.bind('click',function(e){
                    var trgt = target(e);
                    var link = jQuery(trgt).find('a').eq(0);
                    if (link[0])
                    {
                        jQuery("li", parent(e)).removeClass("selected").index(trgt);
                        jQuery(trgt).addClass("selected");

                        var url = link[0].href;
                        if (   url != ''
                            && url != '#')
                        {
                            window.location = url;                            
                        }
                    }
                });
            }
        
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
     * Navigation end
    **/

});