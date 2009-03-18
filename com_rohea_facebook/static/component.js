/**
 * @package com_rohea_facebook
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.com = $.com || {};
    $.com.rohea = $.com.rohea || {};
    
    $.com.rohea.facebook = {
        config: {
            prefix: ''
        }
    };
    $.extend($.com.rohea.facebook, {
        init: function(options) {
            $.com.rohea.facebook.options = $.midcom.services.configuration.merge($.com.rohea.facebook.config, config);
        }
    });
    
    $.midcom.register_component('com.rohea.facebook', $.com.rohea.facebook);
    
})(jQuery);