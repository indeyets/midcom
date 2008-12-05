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
        },
        registered_components: {}
    };
    $.extend($.midcom, {
        init: function(config) {
            $.midcom.config = $.extend({}, $.midcom.config, config || {});
            $.midcom.registered_components = {};
        },
        register_component: function(name, handler) {            
            if (typeof $.midcom.registered_components[name] == 'undefined') {
                $.midcom.registered_components[name] = handler;
            }
        }
    });
    
    $.midcom.dispatcher = {};
    
    $.extend($.midcom.dispatcher, {
        get: function(url, on_success, on_error) {
            var return_data = null;
            if (typeof on_success == 'undefined') {
                alert('No on_success method given for dispatcher');
                return;
            }
            if (typeof on_error == 'undefined') {
                var on_error = $.midcom.dispatcher._on_error;
            }
            
            $.ajax({
                url: url,
                type: "GET",
                global: false,
                cache: true,
                async: false,
                dataType: "xml",
                error: function(req) {
                    on_error(url, req);
                },
                success: function(data) {
                    if (typeof on_data == 'function') {
                        on_success(data, url);
                        return;
                    }
                    return_data = data;
                }
            });
            
            return return_data;
        },
        _on_success: function(url, req) {
            alert("Error retrieving data from "+url+" reason: "+req.responseText);
        }
    });
    
    $.midcom.utils = {};

    $.midcom.utils.load_script = function(url, callback, callback_args) {
        $('head').append('<script type="text/javascript" charset="utf-8" src="'+url+'"></script>');
        if (typeof callback == 'string') {
            if (   typeof callback_args == 'undefined'
                || typeof callback_args != 'object')
            {
                var callback_args = [];
            }
            
            setTimeout('eval("var fn = eval('+callback+'); fn.apply(fn, [\''+callback_args.join("','")+'\']);")', 300);
        }
    };
    
    $.midcom.helpers = {};
    
    /**
     * uses xmlObjectifier from http://www.terracoder.com/documentation.html
     */
    $.midcom.helpers.xml = {
        utils_loaded: false,
        is_utils_loaded: function() {
            if (   $.midcom.helpers.xml.utils_loaded
                && typeof $.xmlToJSON != 'undefined')
            {
                return true;
            }
            
            return false;
        },
        load_xml_utils: function(callback, callback_args) {
            if ($.midcom.helpers.xml.is_utils_loaded()) {
                return;
            }
            
            var url = $.midcom.config.MIDCOM_STATIC_URL + '/midcom_core/jQuery/jqXMLUtils.js';
            $.midcom.utils.load_script(url, callback, callback_args);
            
            $.midcom.helpers.xml.utils_loaded = true;
        }
    };
    $.extend($.midcom.helpers.xml, {
        to_JSON: function(data) {
            if (! $.midcom.helpers.xml.is_utils_loaded()) {
                var callback = "$.midcom.helpers.xml.to_JSON";
                var args = [data];
                
                $.midcom.helpers.xml.load_xml_utils(callback, [data]);
                
                return;
            }
            
            return $.xmlToJSON(data);
        },
        from_text: function(text) {
            if (! $.midcom.helpers.xml.is_utils_loaded()) {
                var callback = "$.midcom.helpers.xml.from_text";
                
                $.midcom.helpers.xml.load_xml_utils(callback, [text]);
                
                return;
            }
            
            return $.textToXML(text);
        }
    });
    
    
    
    $.midcom.services = {};
    
    $.midcom.services.configuration = {        
        merge: function(a,b) {
            var c = {};

            if (typeof a == 'undefined') {
                return c;
            }        
            if (   typeof b == 'undefined'
                || typeof b != 'object')
            {
                var b = {};
            }

            for (var ak in a) {
                if (typeof a[ak] != 'object') {
                    c[ak] = a[ak];
                    if (   typeof b[ak] != 'undefined'
                        && typeof b[ak] != 'object')
                    {
                        c[ak] = b[ak];
                    }
                } else {
                    if (typeof b[ak] == 'undefined') {
                        c[ak] = $.midcom.services.configuration.merge(a[ak], {});
                    } else {
                        c[ak] = $.midcom.services.configuration.merge(a[ak], b[ak]);
                    }                
                }
            }

            return c;
        }
    };
    
})(jQuery);