/**
 * @package com_rohea_account
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.com = $.com || {};
    $.com.rohea = $.com.rohea || {};
    
    $.com.rohea.account = {
        config: {
            prefix: ''
        }
    };
    $.extend($.com.rohea.account, {
        init: function(options) {
            $.com.rohea.account.options = $.midcom.services.configuration.merge($.com.rohea.account.config, config);
        }
    });
    
    $.midcom.register_component('com.rohea.account', $.com.rohea.account);
    
})(jQuery);