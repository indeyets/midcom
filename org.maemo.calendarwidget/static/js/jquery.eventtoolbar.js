/**
 * eventToolbar - jQuery plugin
 */

/**
 * Creates a event toolbar for event and attaches it to div.event-toolbar-button
 *   
 * @example jQuery('#cal[GUID] #event[GUID]').eventToolbar({});
 * @cat plugin
 * @type jQuery 
 *
 */

jQuery.fn.eventToolbar = function(settings) {
    settings = jQuery.extend({
        type: "menu", // float, menu
        event_type: "public",
        md: false, //is multiday event?
        visible: true
    }, settings);
    
    var element = this;
    var handler = jQuery(this).find('div.event-toolbar-button');
    var toolbar_div = null;
    var toolbar_items = new Array();
    var toolbar_visible = false;
    var toolbar_left = 0;
    var toolbar_top = 0;
    var insert_to = "div.event-toolbar-holder";
    var target = jQuery(insert_to);
    var toolbar_position_type = "absolute";
    var toolbar_id_prefix = element[0].id;
        
    if (settings.md)
    {
        element_id_parts = toolbar_id_prefix.split('_');
        toolbar_id_prefix = element_id_parts[0];
    }
    
    var toolbar_id = toolbar_id_prefix + '-toolbox';

    var _already_exists = false;

    if (settings.type == "menu")
    {
        toolbar_position_type = "relative";
    }

    var childs = target.children(); 
    for (var i=0; i<childs.length; i++)
    {   
        if (childs[i].id == toolbar_id)
        {
            // console.log("We found a existing toolbar for the event!");
            toolbar_div = jQuery(toolbar_id,target);
            var correct_child = childs[i];
            _already_exists = true;
        }
    }
    
    if (! _already_exists)
    {
        // console.log('Toolbar '+toolbar_id + ' doesnt exist. Create!');
        _create_toolbar();
        
        if (settings.type == "float")
        {
            _save_position();
        }
    }
    
    handler.click(toggle_toolbar);
    
    function toggle_toolbar()
    {
        var childs = target.children(); 
        for (var i=0; i<childs.length; i++)
        {   
            if (childs[i].id != toolbar_id)
            {
                if (childs[i].style.display == 'block')
                {
                    childs[i].style.display = 'none';
                }                   
            }
        }
        
        if (toolbar_div[0] == undefined)
        {                       
            if (correct_child.style.display == 'none')
            {
                correct_child.style.display = 'block';
            }
            else
            {
                correct_child.style.display = 'none';   
            }
        }
        else
        {
            toolbar_div.toggle();           
        }

        toolbar_visible = !toolbar_visible;
    }
    
    function _save_position() //event
    {
        //var tb_left = toolbar_div[0].offsetLeft;
        //var tb_top = toolbar_div[0].offsetTop;
        // if(event) {
        //  function pos(c) {
        //      var p = c == 'X' ? 'Left' : 'Top';
        //      return event['page' + c] || (event['client' + c] + (document.documentElement['scroll' + p] || document.body['scroll' + p])) || 0;
        //  }
        //  toolbar_left = pos('X') + 3;
        //  toolbar_top = pos('Y') - 5;
        //  toolbar_div.css({
        //      left: toolbar_left + 'px',
        //      top: toolbar_top + 'px'
        //  });
        // }
        
        function findPos(obj) {
            var curleft = curtop = 0;
            if (obj.offsetParent) {
                curleft = obj.offsetLeft
                curtop = obj.offsetTop
                while (obj = obj.offsetParent) {
                    curleft += obj.offsetLeft
                    curtop += obj.offsetTop
                }
            }
            return [curleft,curtop];
        }
        
        // var calendarlayer = element.parent()[0];
        // var clpos = findPos(calendarlayer);
        // 
        // var pos = findPos(handler[0]);
        // toolbar_left = (pos[0]+14) - clpos[0];
        // toolbar_top = (pos[1]+2) - clpos[1];

        var epos = findPos(element[0]);
        
        toolbar_left = epos[0] + element.width() - 10;
        toolbar_top = epos[1] - element.height() - 14;
        
        toolbar_div.css({
            left: toolbar_left + 'px',
            top: toolbar_top + 'px'
        });
    }
    
    function _create_toolbar()
    {   
        toolbar_div = jQuery("<div/>").attr({
            id: toolbar_id,
            title: element[0].title + ' - toolbox',
            className: "event-toolbar-" + settings.type
        }).hide().css({ position: toolbar_position_type, zIndex: 3000 });

        target.append( toolbar_div );
        
        /*.insertAfter( insert_to ).hide().css({ position: toolbar_position_type, zIndex: 3000 });
        
        if (settings.type == "menu")
        {
            res = target.add( toolbar_div );
            
            toolbar_div = res[1];
        }*/
        // if (settings.type == "menu")
        // {
        //  var target = jQuery(insert_to);
        //  toolbar_div.hide().css({ position: toolbar_position_type, zIndex: 3000 });
        //  //target.html( '' );
        //  target.html( toolbar_div );
        // }
        // else
        // {
            //toolbar_div.insertAfter( insert_to ).hide().css({ position: toolbar_position_type, zIndex: 3000 });
        // }

//appendTo('body').hide().css({ position: 'absolute', zIndex: 3000 });
        // var contentHolder = toolbar_div.append( "<div class='event-toolbar-content'>" );
        // var contentList = contentHolder.prepend( "<ul>" );
        // 
        // console.log("contentHolder: "+contentHolder[0]);
        // console.log("contentList: "+contentList[0]);
        
        var toolbar_div_content = document.createElement('div');
        toolbar_div_content.setAttribute('class', 'event-toolbar-content');
        toolbar_div[0].appendChild(toolbar_div_content);
        
        var toolbar_div_list = document.createElement('ul');
        toolbar_div_content.appendChild(toolbar_div_list);
        
        _create_toolbar_items();

        for(var i=0; i<toolbar_items.length; i++)
        {
            toolbar_div_list.appendChild(toolbar_items[i]);
        }
        
        // var contentHTML = "<div class='event-toolbar-content'>\n<ul>";
        // contentHTML += "\n<li><a href='#'>Edit</a></li>\n";
        // contentHTML += "\n<li><a href='#'>Move</a></li>\n";
        // contentHTML += "\n<li><a href='#'>Remove</a></li>\n";
        // contentHTML += "\n<li class='last'><a href='#'>Close</a></li>\n";
        // contentHTML += "</ul>\n</div>";
        // 
        // toolbar_div.append( contentHTML );
    }
    
    function _create_toolbar_items()
    {       
        var toolbar_div_list_item = document.createElement('li');
        toolbar_div_list_item.setAttribute('class', 'first');
        toolbar_div_list_item.innerHTML = 'Show';
        jQuery( toolbar_div_list_item ).click(toggle_toolbar);

        toolbar_items.push(toolbar_div_list_item);
        
        var toolbar_div_list_item = document.createElement('li');
        toolbar_div_list_item.setAttribute('class', 'last');
        toolbar_div_list_item.innerHTML = 'Close';
        jQuery( toolbar_div_list_item ).click(toggle_toolbar);
        
        // var link_element = document.createElement('a');
        // link_element.setAttribute('href', '#');
        // link_element.innerHTML = 'Close';
        // toolbar_div_list_item.appendChild(link_element);
        // jQuery( link_element ).click(test_bind);
        
        toolbar_items.push(toolbar_div_list_item);
    }
    
    function test_bind()
    {
        // console.log("Bind succesfull");
    }
    
};