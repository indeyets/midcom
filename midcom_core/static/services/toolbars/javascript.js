/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){

    $.midcom.services.toolbars = {
        TYPE_MENU: 'menu',
        TYPE_PALETTE: 'palette',
    };
    $.midcom.services.toolbars.config = {
        type: $.midcom.services.toolbars.TYPE_PALETTE,
        type_config: {},
        visible: true,
        create_root: false,
        allow_auto_create: false,
        enable_memory: false,
        class_name: 'midcom_services_toolbars_javascript',
        show_logos: true,
        debug: false
    };
    
    $.extend($.midcom.services.toolbars, {
        javascript: function(holder, config) {
            $.midcom.services.toolbars.config = $.midcom.services.configuration.merge(
                $.midcom.services.toolbars.config,
                config
            );
            
        }
    });
    
    jQuery.fn.extend({
    	midcom_services_toolbars: function(options) {
    	    return new jQuery.midcom.services.toolbars.javascript(this, options);
    	}
    });
    
})(jQuery);