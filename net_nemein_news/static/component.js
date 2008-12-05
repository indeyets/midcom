/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.net = $.net || {};
    $.net.nemein = $.net.nemein || {};
    
    $.net.nemein.news = {
        config: {
            prefix: ''
        }
    };
    $.extend($.net.nemein.news, {
        init: function(options) {
            $.net.nemein.news.options = $.midcom.services.configuration.merge($.net.nemein.news.config, config);
        }
    });
    
    $.midcom.register_component('net.nemein.news', $.net.nemein.news);
    
})(jQuery);