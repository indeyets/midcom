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

jQuery.fn.eventToolbar = function(settings,items) {
    settings = jQuery.extend({
        type: "menu", // float, menu
        event_type: "public",
        md: false, //is multiday event?
        visible: true
    }, settings);
    
    var element = this;
    var handler = jQuery(this).find('div.event-toolbar-button');
    var toolbar_div = null;
    var toolbar_div_list = null;
    var menu_items = items || Array();
    var toolbar_visible = false;
    var toolbar_left = 0;
    var toolbar_top = 0;
    var insert_to = "div.event-toolbar-holder";
    var target = jQuery(insert_to);
    var toolbar_position_type = "absolute";
    var toolbar_id_prefix = element[0].id;
    var event_guid = element[0].id.split('-')[1];
    
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
        // toolbar_div = jQuery("<div/>").attr({
        //     id: toolbar_id,
        //     title: element[0].title + ' - toolbox',
        //     className: "event-toolbar-" + settings.type
        // }).hide().css({ position: toolbar_position_type, zIndex: 3000 });
        // 
        // target.append( toolbar_div );
        
        toolbar_div = jQuery(target).createAppend(
            'div', { id: toolbar_id, title: element[0].title + ' - toolbox', className: 'event-toolbar event-toolbar-type-' + settings.type }
        ).hide().css({ position: toolbar_position_type, zIndex: 3000 });        
        
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

        var toolbar_div_content = jQuery(toolbar_div).createAppend(
            'div', { className: 'event-toolbar-content' }
        );

        toolbar_div_list = jQuery(toolbar_div_content).createAppend(
            'ul', {}
        );
        
        _create_toolbar_items();
    }
    
    function _create_toolbar_items()
    {
        for (var i=0; i<menu_items.length; i++)
        {
            _add_menu_item(menu_items[i]);
        }
        
        jQuery(toolbar_div_list).createAppend(
            'li', { className: 'last', onclick: toggle_toolbar }, 'Close'
        );
    }
    
    function edit_event_action()
    {        
        window.location = APPLICATION_PREFIX + 'event/edit/' + event_guid;
    }
    
    function _add_menu_item(data)
    {
        data = jQuery.extend({
            className: '',
            onclick: '',
            name: ''
        }, data);
        
        var tpl = function() {
            return [
                'li', { className: this.className, onclick: this.action }, this.name
            ];
        };

        jQuery(toolbar_div_list).tplAppend(data, tpl);        
    }
};