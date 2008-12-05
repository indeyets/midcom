/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.midcom = {
        config: {
            MIDCOM_STATIC_URL: 'midcom-static',
            MIDCOM_PAGE_PREFIX: '/'            
        }
    };
    $.extend($.midcom, {
        init: function(config) {
            $.midcom.config = $.extend({}, $.midcom.config, config || {});
        }
    });
    
})(jQuery);