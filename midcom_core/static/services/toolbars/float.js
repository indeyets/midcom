/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.midcom.services.toolbars = $.midcom.services.toolbars || {};
    
    $.midcom.services.toolbars.float = function(id, holder, config) {
        this._id = id;
    
        $.midcom.services.toolbars.config.type_config[$.midcom.services.toolbars.TYPE_FLOAT] = {
            height: 20,
            width: 300,
            draggable: true,
            class_name: 'midcom_services_toolbars_float'
        };
    
        this.holder = holder;
        this.config = $.midcom.services.configuration.merge(
            $.midcom.services.toolbars.config,
            config
        );
        this.type_config = this.config.type_config[$.midcom.services.toolbars.TYPE_FLOAT];

        this._register_elements();
        
        this.memory = new $.midcom.storage.cookies('midcom.services.toolbars.float');

        this.position = this._get_position();
        
        $.midcom.logger.log('midcom.services.toolbars.float inited with id '+this._id);
        $.midcom.logger.debug(this.config);
        
        this.enable_toolbar();
        
        $.midcom.events.signals.trigger('midcom.services.toolbars::float-ready');
    };
    $.extend($.midcom.services.toolbars.float.prototype, {
        enable_toolbar: function() {
            if (this.type_config.width > 0) {
                this.holder.css({
                    width: this.type_config.width
                });
            }
            
            this.holder.css({
                position: 'fixed',
                left: this.position.x,
                top: this.position.y
            });
            
            if ($.browser.ie) {
                this.holder.css({
                    position: 'absolute'
                });
            }
            
            if (   this.type_config.draggable
                && !$.browser.ie)
            {
                var _self = this;
                this.holder.easydrag(true);
                this.holder.ondrop(function(e){_self._save_position(e);});
                this.holder.css({ cursor: 'default' });
            } else {
                $('.' + this.type_config.class_name + '_dragbar',this.holder).hide();
            }
            
            this.show();
        },
        show: function() {
            this.holder.show();
        },
        hide: function() {
            this.holder.hide();
        },
        _register_elements: function() {
            this.section_holder = $('.' + this.type_config.class_name + '_sections', this.holder);
                        
            var section_elements = $('.' + this.type_config.class_name + '_section', this.section_holder);
            
            this.sections = {};
            var _self = this;
            $.each(section_elements, function(i,n){
                _self._register_section(n);
            });
        },
        _register_section: function(element) {
            var classes = $(element).attr('class').split(' '); 
            var section_name = classes[0].replace(this.type_config.class_name + '_section_', '');
            var item_holder = $('.' + this.type_config.class_name + '_section_items', element);
            var handle = $('.' + this.type_config.class_name + '_section_title', element);
            
            this.sections[section_name] = {                
                title: $('.' + this.type_config.class_name + '_section_title', element).html(),
                element: element,
                item_holder: item_holder,
                items: this._collect_section_items(section_name, item_holder),
                _is_hiding: false
            };
            
            if (this.sections[section_name].items.length > 0) {
                var _self = this;
                $(element).bind('mouseover',function(e){
                    $('.' + _self.type_config.class_name + '_section_title', _self.holder).removeClass("hover").index(handle);
                    
                    $.each(_self.sections, function(sect, data){
                        if (sect != section_name) {
                            data.item_holder.hide();
                        }
                    });
                    
                    handle.addClass("hover");
                    item_holder.show();
                    _self.sections[section_name]._is_hiding = false;
                });
                $(element).bind('mouseout',function(e){
                    _self.sections[section_name]._is_hiding = true;
                    var tid = null;
                    
                    var hide = function() {
                        if (_self.sections[section_name]._is_hiding) {
                            handle.removeClass("hover");
                            item_holder.hide();
                        }
                        clearTimeout(tid);
                    }
                    
                    tid = setTimeout(hide, 800);
                });                
            }
        },
        _collect_section_items: function(section_name, holder) {
            var items = [];
            var _self = this;
            var list_items = $('li', holder);
            
            $.each(list_items, function(i,n){
                if ($.browser.ie) {
                    $(n).css({ width: '9em' });
                }
                
                if (i == 0) {
                    $(n).addClass('first_item');
                }
                if (i == (list_items.length - 1)) {
                    $(n).addClass('last_item');
                }
                
                var item = {
                    title: $('.' + _self.type_config.class_name + '_section_' + section_name + '_item_label', holder).html(),
                    element: n                    
                };
                items.push(item);
            });
            return items;
        },
        _get_position: function() {
            var x = y = 20;
            
            if (this.memory.enabled) {
                var pos = this.memory.read("position");
                if (   pos != null
                    && typeof pos.x != 'undefined'
                    && typeof pos.y != 'undefined')
                {
                    return pos;
                }
            }
            
            var dw = $(document).width();
            var sl = $(document).scrollLeft();
            var st = $(document).scrollTop();

            var ew = this.type_config.width;

            if (ew == 0) {
                return {
                    x: 0,
                    y: 0
                };
            }

            y = y + st;
            x = (dw/2 + sl) - ew / 2;

            return {
                x: x,
                y: y
            };
        },
        _save_position: function(e) {            
            var current_position = this.holder.position();

            this.position = {
                x: current_position.left,
                y: current_position.top
            };
            
            if (this.memory.enabled) {
                this.memory.save("position", this.position);
            }
        }
    });

})(jQuery);