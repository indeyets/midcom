/**
 * @package net_nemein_notifications
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.net = $.net || {};
    $.net.nemein = $.net.nemein || {};
    
    $.net.nemein.notifications = {
        config: {
            prefix: ''
        }
    };
    $.extend($.net.nemein.notifications, {
        init: function(options) {
            $.net.nemein.notifications.options = $.midcom.services.configuration.merge($.net.nemein.notifications.config, config);
        }
    });
    
    $.midcom.register_component('net.nemein.notifications', $.net.nemein.notifications);
    
})(jQuery);