var MIDCOM_SERVICES_TOOLBARS_TYPE_MENU = 'menu';
var MIDCOM_SERVICES_TOOLBARS_TYPE_PALETTE = 'palette';

jQuery.fn.extend({
	midcom_services_toolbar: function(options, items) {
	    return new jQuery.midcom_services_toolbars(this, options, items);
	},
	mst_add_item: function(data) {
		return this.trigger("add_item",[data]);
	},
	mst_read_item: function(identifier, options) {
		return this.trigger("read_item",[identifier, options]);
	},
	mst_remove_item: function(object_or_id, options) {
		return this.trigger("remove_item",[object_or_id, options]);
	},
	mst_hide: function() {
		return this.trigger("self_hide",[]);
	},
	mst_show: function() {
		return this.trigger("self_show",[]);
	}
});

jQuery.midcom_services_toolbars = function(root, settings, with_items) {
    settings = jQuery.extend({
        type: MIDCOM_SERVICES_TOOLBARS_TYPE_PALETTE,
        type_config: {},
        visible: true,
        create_root: false,
        debug: false,
        enable_memory: false,
        class_name: 'midcom_services_toolbars_fancy',
        show_logos: true,
        allow_auto_create: false
    }, settings);
    
    debug('Initializing', 'info');

    var default_logo = {
            title: 'Midgard',
            href: '/midcom-exec-midcom/about.php',
            target: '_blank',
            src: 'images/midgard-logo.png',
            width: '16',
            height: '16'
    };
    var all_logos = Array
    (
        default_logo
    );
    var logo_tpl = function() {
        return [
            'a', { href: this.href, title: this.title, target: this.target }, [
                'img', { src: this.src, width: this.width, height: this.height }, ''
            ]
        ];
    };
    
    var root_element = null;
    var item_holder = null;

    var type_configs = Array();
    type_configs[MIDCOM_SERVICES_TOOLBARS_TYPE_MENU] = {
        height: 25,
        width: 0,
        draggable: false
    };
    type_configs[MIDCOM_SERVICES_TOOLBARS_TYPE_PALETTE] = {
        height: 20,
        width: 300,
        draggable: true
    };
    type_configs[settings.type] = jQuery.extend(type_configs[settings.type], settings.type_config);
    
    if (settings.create_root)
    {
        root_element = create_root();
    }
    else
    {
        if (root[0])
        {   
            settings.class_name = root.attr('class');
            root_element = root;
            item_holder = jQuery('div.items',root_element);
        }
        else
        {
            if (settings.allow_auto_create)
            {
                root_element = create_root();
            }
            else
            {
                return;
            }
        }
    }
    
    debug('root_element: '+root_element, 'info');
    
    var menu_items = with_items || Array();
    
    var client_memory = null;
    if (settings.memory)
    {
        client_memory = new protoMemory( 'midcom.services.toolbars' );
    }
        
    var memorized_position = null;
    if (   (   client_memory != null
            && client_memory.hasSupport())
        && settings.type == MIDCOM_SERVICES_TOOLBARS_TYPE_PALETTE)
    {
        memorized_position = client_memory.read("position");
    }    
    var default_position = get_default_position();

    if(memorized_position != null) {
        debug("memorized_position.x: "+memorized_position.x);
        debug("memorized_position.y: "+memorized_position.y);
        var posX = (memorized_position.x != '' && memorized_position.x != undefined ? memorized_position.x : default_position.x) + 'px';
        var posY = (memorized_position.y != '' && memorized_position.y != undefined ? memorized_position.y : default_position.y) + 'px';
    } else {
        var posX = default_position.x + 'px';
        var posY = default_position.y + 'px';
    }
    
    debug('posX: '+posX);
    debug('posY: '+posY);
    
    debug('Initializing Finished', 'info');
    
    enable_toolbar();
    
    function create_root(target)
    {
        debug('create_root start', 'info');
        
        var target = target || jQuery('body');
        
        debug("target: "+target);
        
        var root_class = settings.class_name + ' type_' + settings.type;

        var root = jQuery(target).createAppend(
            'div', { className: root_class }, []
        ).hide().css({ zIndex: 6001, height: type_configs[settings.type].height });
        
        if (settings.show_logos)
        {
            var logo_holder = jQuery(root).createAppend(
                'div', { className: 'logos' }, ''
            );
            for (var i=0; i<all_logos.length;i++)
            {
                var logo = all_logos[i];
                jQuery(logo_holder).tplAppend(logo, logo_tpl);
            }
        }
        
        item_holder = jQuery('<div>').addClass('items');

        jQuery(root).append(item_holder);
        
        if (   type_configs[settings.type].draggable
            && !jQuery.browser.safari)
        {
            jQuery(root).append(
                jQuery('<div>').addClass('dragbar')
            );            
        }
        
        debug('create_root fnished', 'info');
        
        return root;
    }
    
    function enable_toolbar()
    {
        debug('enable_toolbar start', 'info');
        
        if (type_configs[settings.type].width > 0)
        {
            root_element.css({ width: type_configs[settings.type].width });
        }        
        root_element.css({ left: posX, top: posY });
        
        // if (jQuery.browser.safari)
        // {
        //     root_element.css({ position: 'fixed' });
        // }
        // if (jQuery.browser.ie)
        // {
        root_element.css({ position: 'absolute' });
        // }
        
        jQuery('div.item', item_holder).each(function(i,n){
            debug("i: "+i+" n: "+n);
            var item = jQuery(n);
            var handle = jQuery('.midcom_services_toolbars_topic_title',item);
            var children = jQuery('ul',item);
            
            if (jQuery.browser.ie)
            {
                jQuery('li', children).css({ width: '9em' });
            }
            
            item.bind('mouseover',function(e){
                jQuery('.midcom_services_toolbars_topic_title', item_holder).removeClass("hover").index(handle);
                handle.addClass("hover");
                children.show();
            });
            item.bind('mouseout',function(e){
                handle.removeClass("hover");
                children.hide();
            });
        });
        
        if (   type_configs[settings.type].draggable
            && !jQuery.browser.safari)
        {
            //var dragbar = jQuery('div.dragbar',root_element);
            root_element.easydrag(true);
            root_element.ondrop(function(e){save_position(e);});
            root_element.css({ cursor: 'default' });
        }
        else
        {
            jQuery('.dragbar',root_element).hide();
        }
        
        root_element.show();
        
        debug('enable_toolbar finished', 'info');
        
        init_auto_move();
    }
    
    function init_auto_move()
    {
        // jQuery('window').bind('scroll', function(e){
        //     console.log("Body scroll");
        // });
    }
    
    function save_position(event)
    {
        debug('save_position start', 'info');
        
        var new_pos = root_element.position();
        
        var pos = { x: new_pos.left,
                    y: new_pos.top };

        if (settings.memory)
        {
            client_memory.write("position",protoToolkit.toJSON(pos));
        }

        debug('save_position finished', 'info');
    }
    
    function get_default_position()
    {
        var x = 20;
        var y = 20;
        
        var dw = jQuery(document).width();
        var sl = jQuery(document).scrollLeft();
        var st = jQuery(document).scrollTop();
        
        var ew = type_configs[settings.type].width;

        if (ew == 0)
        {
            return {
                x: 0,
                y: 0
            };
        }
        
        var left = (dw/2 + sl) - ew/2;
        
        y = y + st;
        x = left;

        return {
            x: x,
            y: y
        };
    }
    
    function debug(msg, type)
    {
        // var console_type = 'debug';
        // 
        // if (type != "undefined")
        // {
        //     console_type = type;
        // }

        if(settings.debug) {
            console.log('midcom_services_toolbars: '+msg);
        }
    }
    
}