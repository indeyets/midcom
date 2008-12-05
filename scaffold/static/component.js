/**
 * @package ${module}
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.${module_parts_domain} = $.${module_parts_domain} || {};
    $.${module_parts_domain}.${module_parts_host} = $.${module_parts_domain}.${module_parts_host} || {};
    
    $.${module_parts_domain}.${module_parts_host}.${module_parts_name} = {
        config: {
            prefix: ''
        }
    };
    $.extend($.${module_parts_domain}.${module_parts_host}.${module_parts_name}, {
        init: function(options) {
            $.${module_parts_domain}.${module_parts_host}.${module_parts_name}.options = $.midcom.services.configuration.merge($.${module_parts_domain}.${module_parts_host}.${module_parts_name}.config, config);
        }
    });
    
    $.midcom.register_component('${module_parts_domain}.${module_parts_host}.${module_parts_name}', $.${module_parts_domain}.${module_parts_host}.${module_parts_name});
    
})(jQuery);