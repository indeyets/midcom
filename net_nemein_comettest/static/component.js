/**
 * @package net_nemein_comettest
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.net = $.net || {};
    $.net.nemein = $.net.nemein || {};
    
    $.net.nemein.comettest = {
        config: {
            prefix: ''
        }
    };
    $.extend($.net.nemein.comettest, {
        init: function(options) {
            $.net.nemein.comettest.options = $.midcom.services.configuration.merge($.net.nemein.comettest.config, config);
        }
    });
    
    $.midcom.register_component('net.nemein.comettest', $.net.nemein.comettest);
    
})(jQuery);