/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){

    $.midcom.services.toolbars = {
        TYPE_MENU: 'menu',
        TYPE_FLOAT: 'float',
        TYPE_BLOCK: 'block',
        instances: {}
    };
    $.midcom.services.toolbars.config = {
        type: $.midcom.services.toolbars.TYPE_FLOAT,
        type_config: {},
        visible: true,
        create_root: false,
        allow_auto_create: false,
        enable_memory: false,
        show_logos: true
    };    
    $.extend($.midcom.services.toolbars, {
        generate: function(type, holder, options) {
    	    if (typeof jQuery.midcom.services.toolbars[type] == 'undefined') {
    	        return false;
    	    }
            
            var id = $.midcom.services.toolbars._gen_id();
            var instance = new jQuery.midcom.services.toolbars[type](id, holder, options);
            $.midcom.services.toolbars.instances[id] = instance;
            
    	    return instance;
        },
        _gen_id: function(type) {            
            var prefix = 'mst';
            if (typeof type != 'undefined') {
                prefix += '_' + type;
            }
            return $.midcom.helpers.generate_id(prefix);
        }
    });
    
    jQuery.fn.extend({
    	midcom_services_toolbars: function(type, options) {
    	    return new jQuery.midcom.services.toolbars.generate(type, jQuery(this), options);
    	}
    });
    
})(jQuery);