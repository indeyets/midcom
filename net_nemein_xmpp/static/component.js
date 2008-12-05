/**
 * @package net_nemein_xmpp
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.net = $.net || {};
    $.net.nemein = $.net.nemein || {};
    
    $.net.nemein.xmpp = {
        config: {
            prefix: ''
        }
    };
    $.extend($.net.nemein.xmpp, {
        init: function(options) {
            $.net.nemein.xmpp.options = $.midcom.services.configuration.merge($.net.nemein.xmpp.config, config);
        }
    });
    
    $.midcom.register_component('net.nemein.xmpp', $.net.nemein.xmpp);
    
})(jQuery);