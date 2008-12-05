/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){

    $.midcom.services.uimessages = {};
    $.midcom.services.uimessages.midgard = {
        init: function(element, options) {
            
        }
    };

    jQuery.fn.extend({
    	midcom_services_uimessages_midgard: function(options) {
    	    return new jQuery.midcom.services.uimessages.midgard.init(jQuery(this), options);
    	}
    });

})(jQuery);