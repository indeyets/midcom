/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.midcom = {
        _inited: false,
        config: {
            MIDCOM_STATIC_URL: 'midcom-static',
            MIDCOM_PAGE_PREFIX: '/',
            enable_watchers: false,            
            debug: false
        },
        registered_components: {}
    };
    $.extend($.midcom, {
        init: function(config) {
            $.midcom.config = $.extend({}, $.midcom.config, config || {});
            $.midcom.registered_components = {};
            
            $.midcom.logger.init($.midcom.config.force_static_logger || true);
            
            if ($.midcom.config.enable_watchers) {
                $.midcom.watcher.init();
            }
            
            $.midcom._inited = true;

            $.midcom.logger.log('jsmidcom inited with config ' + $.midcom.helpers.pretty_print($.midcom.config));
            
            setTimeout("jQuery.midcom.logger.debug('this is debug message')", 1000);
            setTimeout("jQuery.midcom.logger.warning('this is warning message')", 2000);
            setTimeout("jQuery.midcom.logger.error('this is error message')", 3000);
            
            $.midcom.events.signals.trigger('jsmidcom::init-ready');
        },
        update_config: function(config) {            
            $.midcom.config = $.extend({}, $.midcom.config, config || {});
            $.midcom.logger.log('jsmidcom config updated to ' + $.midcom.helpers.pretty_print($.midcom.config));
        },
        register_component: function(name, handler) {
            if (typeof $.midcom.registered_components[name] == 'undefined') {
                if (! $.midcom._inited) {
                    $.midcom.events.signals.listen('jsmidcom::init-ready', $.midcom._register_component, [name, handler], false);
                } else {
                    $.midcom._register_component(name, handler);
                }
            }
        },
        _register_component: function(name, handler) {
            $.midcom.logger.log('registering component: '+name);
            
            $.midcom.registered_components[name] = handler;
        }
    });
    
    $.midcom.events = {};
    $.midcom.events.signals = {
        _listeners: null,
        trigger: function(signal, data) {
            if (   $.midcom.events.signals._listeners === null
                || typeof $.midcom.events.signals._listeners[signal] == 'undefined')
            {
                return;
            }
            
            if (   typeof data == 'undefined'
                || typeof data != 'object')
            {
                var data = [];
            }
            
            $.each($.midcom.events.signals._listeners[signal], function(i,listener){
                if (typeof listener != 'object') {
                    return;
                }
                
                if (typeof listener.func == 'function') {
                    var args = data;
                    if (typeof listener.args != 'undefined') {
                        args = listener.args;
                    }
                    
                    listener.func.apply(listener.func, args);
                    
                    if (! listener.keep) {
                        $.midcom.events.signals._listeners[signal][i] = null;
                    }
                }
            });
        },
        listen: function(signal, listener, data, keep) {
            if ($.midcom.events.signals._listeners === null) {
                $.midcom.events.signals._listeners = {};
            }
            if (typeof $.midcom.events.signals._listeners[signal] == 'undefined') {
                $.midcom.events.signals._listeners[signal] = [];
            }
            
            if (typeof keep == 'undefined') {
                var keep = true;
            }
            
            var lstnr = {
                func: listener,
                args: data,
                keep: keep
            };
            
            $.midcom.events.signals._listeners[signal].push(lstnr);
        }
    };
    
    $.midcom.logger = {
        row_num: 1,
        force_static: false,
        _static_holder: null,        
        _out: function(msg, type) {
            if (typeof type == 'undefined') {
                var type = 'log';
            }
            
            msg = $.midcom.logger.row_num + ": " + msg;
            $.midcom.logger.row_num += 1;
            
            if (   typeof window['console'] == 'undefined'
                || $.midcom.logger.force_static)
            {
                $.midcom.logger._static(msg, type);
            }
            else
            {
                $.midcom.logger._console(msg, type);
            }
        },
        _console: function(msg, type) {
            if (! $.midcom.config.debug) {
                return;
            }
            
            if (typeof console[type] != 'undefined') {
                console[type](msg);
            } else {
                console.log(msg);
            }
        },
        _static: function(msg, type) {
            if ($.midcom.logger._static_holder == null) {
                $.midcom.logger._generate_static();
            }
            
            $.midcom.logger._static_holder.trigger('add_message', [msg, type]);
        },
        _generate_static: function() {            
            $.midcom.logger._static_holder = $('<div id="jsmidcom_logger" />').hide();
            $.midcom.logger._static_holder.appendTo('body');
            
            if (! $.midcom.config.debug) {
                $.midcom.logger._static_holder.show();
            }
            
            var header = $('<div class="jsmidcom_logger_header" />').html('Logger');
            header.appendTo($.midcom.logger._static_holder);
            
            var messages_visible = true;
            header.bind('click', function(e){
                if (messages_visible) {
                    messages_visible = false;
                    messages.hide();
                } else {
                    messages_visible = true;
                    messages.show();
                }
            });

            var messages = $('<div class="jsmidcom_logger_messages" />');
            messages.appendTo($.midcom.logger._static_holder);
            
            $.midcom.logger._static_holder.bind('add_message', function(evt, msg, type){
                var message = $('<div class="jsmidcom_logger_message" />')
                .hide()
                .addClass(
                    'jsmidcom_logger_message_' + type
                ).html(
                    msg
                );
                message.prependTo(messages);
                
                message.fadeIn('normal');
            });
        }
    };
    $.extend($.midcom.logger, {
        init: function(force_static) {
            if (   typeof force_static
                && force_static)
            {
                $.midcom.logger.force_static = true;
            }
        },
        log: function(msg) {
            $.midcom.logger._out(msg, 'log');
        },
        debug: function(msg) {
            $.midcom.logger._out(msg, 'debug');
        },
        warning: function(msg) {
            $.midcom.logger._out(msg, 'warning');
        },
        error: function(msg) {
            $.midcom.logger._out(msg, 'error');
        }
    });
    
    $.midcom.watcher = {
        targets: []
    };
    $.extend($.midcom.watcher, {
        init: function() {
            $.midcom.watcher.targets = [];
        },
        register: function(url, callback) {
            
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
     * uses xmlObjectifier from http://www.terracoder.com/
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
    
    $.midcom.helpers.json = {};
    $.extend($.midcom.helpers.json, {
        /**
         * Parses and evaluates JSON string to Javascript
         * @param {String} json_str JSON String
         * @returns Parsed JSON string or false on failure
         */
        parse: function (json_str) {
        	try {
        	    var re = new RegExp('[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]');
                return !(re.test(json_str.replace(/"(\\.|[^"\\])*"/g, ''))) && eval('(' + json_str + ')');
            } catch (e) {
                return false;
            }
        },

        /**
         * Parses Javascript to JSON
         * @param {Mixed} item Javascript to be parsed
         * @param {String} type of the Javascript item to be parsed (Optional)
         * @returns JSON string
         * @type String
         */
        convert:  function(item, item_type) {
            var m = {
                '\b': '\\b',
                '\t': '\\t',
                '\n': '\\n',
                '\f': '\\f',
                '\r': '\\r',
                '"' : '\\"',
                '\\': '\\\\'
            },
            s = {
                arr: function (x) {
                    var a = ['['], b, f, i, l = x.length, v;
                    for (i = 0; i < l; i += 1) {
                        v = x[i];
                        v = conv(v);
                        if (typeof v == 'string') {
                            if (b) {
                                a[a.length] = ',';
                            }
                            a[a.length] = v;
                            b = true;
                        }
                    }
                    a[a.length] = ']';
                    return a.join('');
                },
                bool: function (x) {
                    return String(x);
                },
                nul: function (x) {
                    return "null";
                },
                num: function (x) {
                    return isFinite(x) ? String(x) : 'null';
                },
                obj: function (x) {
                    if (x) {
                        if (x instanceof Array) {
                            return s.arr(x);
                        }
                        var a = ['{'], b, f, i, v;
                        for (i in x) {
                            v = x[i];
                            v = conv(v);
                            if (typeof v == 'string') {
                                if (b) {
                                    a[a.length] = ',';
                                }
                                a.push(s.str(i), ':', v);
                                b = true;
                            }
                        }
                        a[a.length] = '}';
                        return a.join('');
                    }
                    return 'null';
                },
                str: function (x) {
                    if (/["\\\x00-\x1f]/.test(x)) {
                        x = x.replace(/([\x00-\x1f\\"])/g, function(a, b) {
                            var c = m[b];
                            if (c) {
                                return c;
                            }
                            c = b.charCodeAt();
                            return '\\u00' +
                                Math.floor(c / 16).toString(16) +
                                (c % 16).toString(16);
                        });
                    }
                    return '"' + x + '"';
                }
            };
            conv = function (x) {
                switch(typeof x) {
                    case "object":
                        if (is_a(x, Array)) {
                            return s.arr(x);
                        } else {
                            return s.obj(x);
                        }         
                    break;
                    case "string":
                        return s.str(x);
                    break;
                    case "number":
                        return s.num(x);
                    break;
                    case "null":
                        return s.nul(x);
                    break;
                    case "boolean":
                        return s.bool(x);
                    break;
                }
            }

            var itemtype = item_type || typeof item;
            switch (itemtype) {
                case "object":
                    if (is_a(item, Array)) {
                        return s.arr(item);
                    } else {
                        return s.obj(item);
                    }         
                break;
                case "string":
                    return s.str(item);
                break;
                case "number":
                    return s.num(item);
                break;
                case "null":
                    return s.nul(item);
                break;
                case "boolean":
                    return s.bool(item);
                break;
                default:
                    throw ("Unknown type for $.midcom.helpers.json.convert");
            }            
        }
    });
    
    /**
     * Renders pretty printed version from given value
     * Original pretty_print function by Damien Katz <damien_katz@yahoo.com>
     * Modified to work with Ajatus and jsMidCOM by Jerry Jalava <jerry.jalava@gmail.com>     
     * @param {Mixed} val Value to render
     * @param {Number} indent Current indent level (Default: 4)
     * @param {String} linesep Line break to be used (Default: "\n")
     * @param {Number} depth Current line depth (Default: 1)
     * @returns Pretty printed value
     * @type String
     */
    $.midcom.helpers.pretty_print = function(val, indent, linesep, depth) {
        var indent = typeof indent != 'undefined' ? indent : 4;
        var linesep = typeof linesep != 'undefined' ? linesep : "\n";
        var depth = typeof depth != 'undefined' ? depth : 1;
        
        var propsep = linesep.length ? "," + linesep : ", ";

        var tab = [];

        for (var i = 0; i < indent * depth; i++) {
            tab.push("")
        };
        tab = tab.join(" ");

        switch (typeof val) {
            case "boolean":
            case "number":
            case "string":
                return $.midcom.helpers.json.convert(val);
            case "object":
                if (val === null) {
                    return "null";
                }
                if (val.constructor == Date) {
                    return $.midcom.helpers.json.convert(val);
                }

                var buf = [];
                if (val.constructor == Array) {
                    buf.push("[");
                    for (var index = 0; index < val.length; index++) {
                        buf.push(index > 0 ? propsep : linesep);
                        buf.push(
                            tab, $.midcom.helpers.pretty_print(val[index], indent, linesep, depth + 1)
                        );
                    }
                    
                    if (index >= 0) {
                        buf.push(linesep, tab.substr(indent))
                    };
                    
                    buf.push("]");
                } else {
                    buf.push("{");
                    var index = 0;
                    for (var key in val) {                        
                        if (! val.hasOwnProperty(key)) {
                            continue;
                        };
                        
                        buf.push(index > 0 ? propsep : linesep);
                        buf.push(
                            tab, $.midcom.helpers.json.convert(key), ": ",
                            $.midcom.helpers.pretty_print(val[key], indent, linesep, depth + 1)
                        );
                        index++;
                    }
                    
                    if (index >= 0) {
                        buf.push(linesep, tab.substr(indent));
                    };
                    
                    buf.push("}");
                }
                
                return buf.join("");
            break;
        }
    };
    
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
    
    /**
     * Javascript extensions
    **/
    
    function is_a(source, constructor) {
        while (source != null) {
            if (source == constructor.prototype) {
                return true;
            }
            source = source.__proto__;
        }
        return false;
    }
    
})(jQuery);