/**
 * @package ${component}
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.${component_parts_domain} = $.${component_parts_domain} || {};
    $.${component_parts_domain}.${component_parts_host} = $.${component_parts_domain}.${component_parts_host} || {};
    
    $.${component_parts_domain}.${component_parts_host}.${component_parts_name} = {
        config: {
            prefix: ''
        }
    };
    $.extend($.${component_parts_domain}.${component_parts_host}.${component_parts_name}, {
        init: function(options) {
            $.${component_parts_domain}.${component_parts_host}.${component_parts_name}.options = $.midcom.services.configuration.merge($.${component_parts_domain}.${component_parts_host}.${component_parts_name}.config, config);
        }
    });
    
    $.midcom.register_component('${component_parts_domain}.${component_parts_host}.${component_parts_name}', $.${component_parts_domain}.${component_parts_host}.${component_parts_name});
    
})(jQuery);