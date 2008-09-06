var MIDCOM_SERVICES_UIMESSAGES_TYPE_INFO = 'info';
var MIDCOM_SERVICES_UIMESSAGES_TYPE_OK = 'ok';
var MIDCOM_SERVICES_UIMESSAGES_TYPE_WARNING = 'warning';
var MIDCOM_SERVICES_UIMESSAGES_TYPE_ERROR = 'error';
var MIDCOM_SERVICES_UIMESSAGES_TYPE_DEBUG = 'ok';

(function(jQuery) {
    
    jQuery.midcom_services_uimessages = function(options, root) {
        jQuery.midcom_services_uimessages.core.start(root, options);
    };
    jQuery.midcom_services_uimessages.debug = {
        add: function(msg, type)
        {
            jQuery.midcom_services_uimessages.core.debug(msg, type);
        }
    };
        
    jQuery.midcom_services_uimessages_read_contents = function()
    {
        jQuery.midcom_services_uimessages.core.read_contents();
    }

    jQuery.midcom_services_uimessages.defaults = {
        root_parent: null,
        debug: true,
        class_name: 'midcom_services_uimessages',
        item_class_name: 'midcom_services_uimessages_message',
        max_visible: 3,
        item_lifetime: 3600,
        pool_refresh_period: 0.9,
        enable_effects: true,
        auto_destroy: true,
        read_content: true        
    };
    
    jQuery.midcom_services_uimessages.core = {
        
        settings: jQuery.midcom_services_uimessages.defaults,
        root_element: null,
        initialized: false,
        
        start: function(root, options)
        {
            this.debug('core.start start', 'info');
            
            this.debug('root: '+root);
            this.debug('options: '+options);
            
            this.settings = jQuery.extend(
                this.settings,
                options
            );
            
            this.get_root();
            
            if (this.settings.read_content)
            {
                this.read_contents();
            }
            
            this.initialized = true;
            
            this.debug('core.start end', 'info');
        },
        get_root: function()
        {
            this.debug('core.get_root start', 'info');
            
            var parent = this.get_root_parent();
            
            this.root_element = jQuery('div.' + this.settings.class_name + '_holder', parent);
            
            if (!this.root_element[0])
            {
                this.debug('core.get_root root_element not found! creating...');
                
                this.root_element = jQuery('<div>').addClass(this.settings.class_name + '_holder');
                jQuery(parent).append(
                    this.root_element
                );
            }
            
            this.debug('core.get_root this.root_element: '+this.root_element);
            
            this.debug('core.get_root end', 'info');
        },
        get_root_parent: function()
        {
            var parent = null;
            
            if (typeof(this.settings.root_parent) == 'string')
            {
                parent = jQuery(this.settings.root_parent);
            }
            else if (this.settings.root_parent != null)
            {
                parent = this.settings.root_parent;

                if (!parent[0])
                {
                    parent = jQuery('body');
                }
            }
            else
            {
                parent = jQuery('body');
            }

            return parent;
        },
        read_contents: function()
        {
            this.debug('core.read_contents start', 'info');

            jQuery('div.'+this.settings.item_class_name, this.root_element).each(function(i,n){
                jQuery.midcom_services_uimessages.core.debug('i: '+i+' n: '+n);

                var item = new midcom_services_uimessage(this.settings);
                item.read(n);
                
                console.log("item: "+item);
                console.log("item.item_id: "+item.item_id);
                console.log("item.type: "+item.type);
                
                jQuery.midcom_services_uimessages.pool.register(item);
            });

            this.debug('core.read_contents end', 'info');
        },
        add: function(item)
        {
            this.debug('core.add item: '+item);
        },
        debug: function(msg, type)
        {
            var console_type = type || 'debug';
    
            if(this.settings.debug) {
                console[console_type]('midcom_services_uimessages: '+msg);
            }
        }
    };

    jQuery.midcom_services_uimessages.pool = {
        items: Array(),
        active_items: Array(),
        next_id: 1,
                
        register: function(item)
        {
            jQuery.midcom_services_uimessages.core.debug('pool.register item.item_id: '+item.item_id);
            
            item.registered = new Date().getTime();
            
            this.items.push(item);
            this.active_items.push(item);
            
            if(this.active_items.length < jQuery.midcom_services_uimessages.core.settings.max_visible) {
                this.move_up();
            }

            jQuery.midcom_services_uimessages.core.debug('pool.register items.length: '+this.items.length);
        },
        unregister: function(item)
        {
            jQuery.midcom_services_uimessages.core.debug('pool.unregister item.data().id: '+item.data().id);
            var item_id = item.data().id;
            
            this.items = jQuery.grep(this.items, function(n,i){
                if (n.item_id != item_id)
                {
                    return true;
                }
            });

            this.active_items = jQuery.grep(this.active_items,function(n,i){
                if (n.item_id != item_id)
                {
                    return true;
                }
            });
            
            jQuery.midcom_services_uimessages.core.debug('pool.unregister items.length: '+this.items.length);
            jQuery.midcom_services_uimessages.core.debug('pool.unregister active_items.length: '+this.active_items.length);
            
            item.trigger('destroy');
        },
        move_up: function()
        {
            
        },
        set_active: function(item)
        {
            
            this.active_items.push(item);
        },
        
        refresh: function()
        {
            jQuery(this.active_items).each(function(i,n){
                if (n.is_expired())
                {
                    n.trigger('expired');
                    jQuery.midcom_services_uimessages.pool.unregister(n);
                }
            });
            
            if (this.active_items.length < jQuery.midcom_services_uimessages.core.settings.max_visible)
            {
                jQuery.midcom_services_uimessages.pool.move_up();
            }
        }
        
    };
    
})(jQuery);

function midcom_services_uimessage(settings)
{
    this.parent = null;
    this.data = null;
    this.item = null;
    this.item_id = null;
    this.type = null;
    
    this.registered = null;
    this.lifetime = null;
    
    this.settings = jQuery.extend(
        jQuery.midcom_services_uimessages.defaults,
        settings
    );
};
midcom_services_uimessage.prototype.create = function(data)
{
    jQuery.midcom_services_uimessages.core.debug('midcom_services_uimessage create');

    this.data = jQuery.extend({
        type: this.settings.type,
        title: '',
        message: ''
    }, data);
    
    this.type = this.data.type;
    
    this.item = jQuery('<div>').addClass(this.settings.class_name);
    jQuery(this.item).append(
        jQuery('<div>').addClass('title').html(this.data.title)
    );
    jQuery(this.item).append(
        jQuery('<div>').addClass('message').html(this.data.message)
    );
    
    this.prepare_item();
}
midcom_services_uimessage.prototype.read = function(data)
{
    jQuery.midcom_services_uimessages.core.debug('midcom_services_uimessage read typeof(data): '+typeof(data));
    
    this.item = jQuery(data);
    
    this.type = jQuery('div.'+this.settings.item_class_name+'_type', this.item).html();
    
    jQuery.midcom_services_uimessages.core.debug('midcom_services_uimessage read this.type: '+this.type);

    this.prepare_item();
}
midcom_services_uimessage.prototype.prepare_item = function()
{
    jQuery.midcom_services_uimessages.core.debug('midcom_services_uimessage prepare_item this.item: '+this.item);
    
    this.generate_id();
    this.set_metadata();
        
    //.hide()
    var self = this;
    this.item.attr('id',this.item_id).css({
        position: 'absolute',
        zIndex: 6002
    }).bind('click', function(e){
        jQuery.midcom_services_uimessages.core.debug('id: '+self.item.data().id+' CLICK');        
        jQuery.midcom_services_uimessages.pool.unregister(self.item);
    }).bind('show', function(e){
        jQuery.midcom_services_uimessages.core.debug('id: '+self.item.data().id+' SHOW');        
        self.item.show();
    }).bind('expired', function(e){
        jQuery.midcom_services_uimessages.core.debug('id: '+self.item.data().id+' EXPIRED');        
    }).bind('hide', function(e){
        jQuery.midcom_services_uimessages.core.debug('id: '+self.item.data().id+' HIDE');
        self.item.hide();
    }).bind('destroy', function(e){
        jQuery.midcom_services_uimessages.core.debug('id: '+self.item.data().id+' DESTROY');
        self.item.remove();
    });
    
    var pos = this.get_default_position();
    this.set_position(pos.top, pos.left);
}
midcom_services_uimessage.prototype.set_metadata = function()
{
    console.log("set metadata");
    this.item.data().id = this.item_id;
    console.log(this.item.data());
}
midcom_services_uimessage.prototype.generate_id = function()
{
    random_key = Math.floor(Math.random()*4013);
    this.item_id = this.settings.class_name + "_message_" + (10016486 + (random_key * 22423));
}
midcom_services_uimessage.prototype.get_default_position = function()
{
    var wiw = jQuery(window).width();
    var wih = jQuery(window).height();
    var sl = jQuery(document).scrollLeft();
    var st = jQuery(document).scrollTop();
    var ew = jQuery(this.item).width();
    var eh = jQuery(this.item).height();
    
    var top = st + (wih - eh) - 15;
    var left = (wiw/2 + sl) - ew/2;
    
    return {
        top: top,
        left: left
    };
}
midcom_services_uimessage.prototype.set_position = function(top, left)
{
    this.item.css({ top: top, left: left });
}
midcom_services_uimessage.prototype.is_expired = function()
{
    // Error messages must be disposed by clicking
    if (this.type == 'error')
    {
        return false;
    }

    var timestamp = new Date().getTime();
    var expires = this.registered + this.settings.item_lifetime;
    
    if (expires <= timestamp)
    {
        return true;
    }
    
    return false;
}

/*
jQuery.fn.extend({
 midcom_services_uimessage: function(options) {
     return new jQuery.midcom_services_uimessages(this, options);
 },
 msuim_add_item: function(data) {
     return this.trigger("add_item",[data]);
 },
 msuim_read_item: function(identifier, options) {
     return this.trigger("read_item",[identifier, options]);
 },
 msuim_remove_item: function(object_or_id) {
     return this.trigger("remove_item",[object_or_id]);
 },
 msuim_pool_purge: function() {
     return this.trigger("pool_purge",[]);
 },
 msuim_pool_show_all: function() {
     return this.trigger("pool_show_all",[]);
 }
});

jQuery.midcom_services_uimessages = function(root, settings) {
    settings = jQuery.extend({
        create_root: false,
        debug: true,
        class_name: 'midcom_services_uimessages',
        max_visible: 3,
        enable_effects: true,
        destroy_on_close: true,
        read_content: true
    }, settings);
    
    debug('Initializing start', 'info');
    
    var root_element = null;
    
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
        }
        else
        {
            debug("typeof(root): "+typeof(root));
            
            if (typeof(root) == 'string')
            {
                root_element = jQuery(root);
            }
            if (!root_element[0])
            {
                root_element = create_root();                
            }
        }
    }
    
    root_element.bind("add_item", function(event, data){
     debug('msuim add_item data: '+data);
 }).bind("read_item", function(event, identifier, options){
     debug('msuim read_item identifier: '+identifier);
     debug('msuim read_item options: '+options);
 }).bind("remove_item", function(event, object_or_id){
     debug('msuim remove_item object_or_id: '+object_or_id);
 }).bind("pool_purge", function(event){
     debug('msuim pool_purge');
 }).bind("pool_show_all", function(event){
     debug('msuim pool_show_all');
 });
    
    debug('Initializing finished', 'info');

    function create_root(target)
    {
        debug('create_root start', 'info');
        
        var target = target || jQuery('body');
        
        debug("target: "+target);
        
        var root_class = settings.class_name;

        var root = jQuery(target).append(
            jQuery('<div>').addClass(root_class)
        );
        
        debug('create_root fnished', 'info');
        
        return root;
    }

    function debug(msg, type)
    {
        var console_type = type || 'debug';

        if(settings.debug) {
            console[console_type]('midcom_services_uimessages: '+msg);
        }
    }
    
}*/