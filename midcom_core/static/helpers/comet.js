/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

(function($){
    
    $.midcom = $.midcom || {};
    $.midcom.helpers = $.midcom.helpers || {};
    
    $.midcom.helpers.comet = {
        backend: 'midcom',
        backends: {},
        _backend_loaded: false,
        _callbacks: {},
        _cb_set: false,
        is_backend_loaded: function() {
            if ($.midcom.helpers.comet._backend_loaded) {
                return true;
            }
            
            if (typeof $.midcom.helpers.comet.backends[$.midcom.helpers.comet.backend] == 'undefined') {
                return false;
            }
            
            return $.midcom.helpers.comet.backends[$.midcom.helpers.comet.backend].available();
            
            return false;
        },
        load_backend: function(callback, callback_args) {            
            if ($.midcom.helpers.comet.is_backend_loaded()) {
                if (typeof callback == 'string') {
                    if (   typeof callback_args == 'undefined'
                        || typeof callback_args != 'object')
                    {
                        var callback_args = [];
                    }

                    setTimeout('eval("var fn = eval('+callback+'); fn.apply(fn, [\''+callback_args.join("','")+'\']);")', 200);
                }
            }
            
            $.midcom.helpers.comet._backend_loaded = $.midcom.helpers.comet.backends[$.midcom.helpers.comet.backend]._load();
        }
    };
    $.extend($.midcom.helpers.comet.backends, {
        pi: {
            available: function() {
                if (typeof pi != 'undefined') {
                    return true;
                }
                return false;
            },
            launch: function(type, url, callback, send_type, data) {
            	switch (type) {
            	    case 'send':
            			var tunnel = new pi.xhr;
            			tunnel.environment.setType(send_type.toString().toUpperCase());
            			tunnel.environment.setUrl(url);

            			if (typeof data == 'object') {
            			    $.each(data, function(i,n){
            			        tunnel.environment.addData(i, n);
            			    });
            			}

            			tunnel.send();

            			return tunnel;
            	    break;
            	    case 'listen':
            	    default:
            	        req = new pi.comet();
                    	req.environment.setUrl(url);
                    	if (! $.midcom.helpers.comet._cb_set)
                    	{
                        	req.event.push = callback(resp);
                        	$.midcom.helpers.comet._cb_set = true;
                    	}
                    	req.send();

                    	return req;
            	    break;
            	}
            },
            _load: function() {
                var url = $.midcom.config.MIDCOM_STATIC_URL + '/midcom_core/pi.js';
                $.midcom.utils.load_script(url, callback, callback_args);
                
                return true;
            }
        },
        midcom: {
            available: function() {
                if (typeof $.midcom.helpers.cometclass != 'undefined') {
                    return true;
                }
                return false;
            },
            launch: function(type, url, callback, send_type, data) {
                
                if (typeof $.midcom.helpers.cometclass[type] == 'undefined') {
                    return false;
                }
                
                var tunnel = new $.midcom.helpers.cometclass[type]();
                tunnel.env.setUrl(url);
                
                switch (type) {
                    case 'send':
                        tunnel.env.setType(send_type.toString().toUpperCase());                        
            			if (typeof data == 'object') {
            			    $.each(data, function(i,n){
            			        tunnel.env.addData(i, n);
            			    });
            			}
                    break;
                    case 'listen':
                    	tunnel.events.push = function(resp) {
                    	    callback(resp);
                    	};
                    break;
                }

    			tunnel.send();
    			
    			return tunnel;
            },
            _load: function() {
                return true;
            }
        }
    });    
    $.extend($.midcom.helpers.comet, {
        start: function() {            
            if (typeof $.midcom.helpers.comet.backends[$.midcom.helpers.comet.backend] == 'undefined') {
                return false;
            }
            
            if (arguments.length < 2) {
                return false;
            }
            
            var type = 'listen';

            var url = arguments[0];
            
            var cb = false;
            var send_type = null;
            var data = null;
            
            if (arguments.length == 3) {
                type = 'send';
                
                send_type = arguments[1];
                data = arguments[2];
            } else {
                cb = arguments[1];
            }
            
            var callback_id = false;
            if (cb) {
                callback_id = $.midcom.helpers.comet._register_callback(cb);
            }
            
            if (! $.midcom.helpers.comet.is_backend_loaded()) {
                var callback = "jQuery.midcom.helpers.comet._launch_comet";
                var args = [type, url, callback_id, send_type, data];
                $.midcom.helpers.comet.load_backend(callback, args);
            } else {
                return $.midcom.helpers.comet._launch_comet(type, url, callback_id, send_type, data);
            }
        },
        _register_callback: function(callback) {            
            var id = $.midcom.helpers.generate_id();
            
            $.midcom.helpers.comet._callbacks[id] = callback;
            
            return id;
        },
        _launch_comet: function(type, url, callback_id, send_type, data) {
            var callback = function(r){};
            if (typeof $.midcom.helpers.comet._callbacks[callback_id] != 'undefined') {
                callback = $.midcom.helpers.comet._callbacks[callback_id];
            }
            
            $.midcom.helpers.comet.backends[$.midcom.helpers.comet.backend].launch(type, url, callback, send_type, data);
        	
        	return false;
        }
    });

    $.midcom.helpers._cometclass = {
        _env: {
            _getsetters: ['api', 'callback', 'header', 'data', 'type', 'async', 'url', 'cache', 'mimeType', 'channel', 'multipart', 'timeout', 'name', 'tunnel'],
            _constructor: {
                api: null,
                callback: [],
                header: {},
                data: {},
                type: 'GET',                                                                
                async: true,
                url: '',
                cache: true,
                mimeType: null,                                                                
                channel: null,
                multipart: false,
                timeout: 0,
                name: '',
                tunnel: null,
                _parent_: null,
                
        		addCallback: function(funct, rs_val, stat_val) {
        		    if (! $.midcom.helpers.is_a(this.getCallback(), Array)) {
        		        this.callback = [];
        		    }
        			this.getCallback().push({
        			    fn: funct,
        			    readyState: rs_val || 4,
        			    status: stat_val || 200
        			});
        		},
        		addHeader: function(key, val) {
        			this.getHeader()[key] = val;
        		},
        		addData: function(key, val) {
        			this.getData()[key] = val;
        		},
        		setCache: function(val) {
        			if (val == false) {
        				this.addData("forceCache", Math.round(Math.random()*10000));
        			}
        			this.cache = val;
        		},
        		setType: function(val) {
        			if (val == "POST") {
        				this.addHeader("Content-Type","application/x-www-form-urlencoded");
        			}
        			this.type = val;
        		},
        		setTunnel: function(val) {
        			if (this.getType() == 1) {
        				val.env.addData('cometType', '1');
        				val.env.addCallback(this._parent_.events.change, 3);
        				val.env.setCache(false);
        			}

                    val._cometApi_ = this._parent_;
        			this.tunnel = val;
        		}
            },
            generate: function(parent) {
                var inst = $.midcom.helpers.clone($.midcom.helpers._cometclass._env._constructor);
                inst._parent_ = parent;
                
                for (var key in $.midcom.helpers._cometclass._env._getsetters) {
                    var item = $.midcom.helpers._cometclass._env._getsetters[key];
                    var name = item, title = name.substring(0, 1).toUpperCase()+name.substring(1);
                    
                    if (Boolean(inst['get' + title]) == false) {
                        inst['get' + title] = new Function('return this.' + name);
                    }
                    
                    if (Boolean(inst['set' + title]) == false) {
                        inst['set' + title] = new Function('val','this.' + name + ' = val;');
                    }
                }
                
                return inst;
            }
        },
        _xhr: function() {
            var api = window.XMLHttpRequest ? XMLHttpRequest : ActiveXObject("Microsoft.XMLHTTP");
            var _self = this;
                        
            this.env = $.midcom.helpers._cometclass._env.generate(this);
            
            this.events = {
                readystatechange: function() {
        			var ready_state = _self.env.getApi().readyState;
        			var callback = _self.env.getCallback();
                    for (var i = 0; i < callback.length; i++) {
                        if (callback[i].readyState == ready_state) {
                            callback[i].fn.apply(callback[i].fn, [_self]);
                        }
                    }
        		},
        		error: function() {
        		}
            };
            
            this.actions = {
        		abort: function() {
        			this.env.getApi().abort();
        		},
        		send: function() {
        			var url = this.env.getUrl(), data = this.env.getData(),dataUrl = ""; 

        			for (key in data) {
        			    dataUrl += "{0}={1}&".format(key, data[key]);
        			}

        			if (this.env.getType()=="GET"&&url.search("\\?") == -1) {
        			    url += "?{0}".format(dataUrl);
        			}

        			this.env.getApi().open(this.env.getType(), url, this.env.getAsync());

        			for (key in this.env.getHeader()) {
        			    this.env.getApi().setRequestHeader(key, this.env.getHeader()[key]);
        			}

        			this.env.getApi().send(this.env.getType() == "GET" ? "" : dataUrl);
        		}
            };
            
            this.env.setApi( new api() );

            this.env.getApi().onreadystatechange = this.events.readystatechange;
    		this.env.getApi().onerror = this.events.error;
        },
        _listener: function() {
            var _self = this;

            this.env = $.midcom.helpers._cometclass._env.generate(this);

            this.env.setUrl = function(val) {
    			if (this.getType() > 1) {
    				val = '{0}{1}cometType={2}&cometName={3}'.format(val, val.search("\\?") > -1 ? '&' : '?', this.getType(), this.getName());

    				if (this.getType() == 2) {
    				    this.getTunnel().setAttribute('src', val);
    				}
    			} else {
    			    this.getTunnel().env.setUrl(val);
    			}

    			this.url = val;
    		};
    		
            this.events = {
        		change: function(self) {
        			var response = null;
        			if (self.env.getType() == 2) {
        			    response = arguments[0].data
        			} else {
        				response = self.env.getApi().responseText.split("<end />");
        				response = response[response.length-1];
        			}
           			self._cometApi_.events.push(response);
        		},
        		push: function(resp){}
            };

            this.actions = {
        		abort: function() {
        			switch (this.env.getType()) {
        				case 1:
        					this.env.getTunnel().abort();
        				break;
        				case 2:
        					document.body.removeChild(this.env.getTunnel());
        				break;
        				case 3:
        					this.env.getTunnel().body.innerHTML = '<iframe src="about:blank"></iframe>';
        				break;
        			}
        		},
        		send: function() {
        			switch (this.env.getType()) {
        				case 1:
        					this.env.getTunnel().send();
        				break;
        				case 2:
        					document.body.appendChild(this.env.getTunnel());
        					this.env.getTunnel().addEventListener( this.env.getName(), this.events.change, false );
        				break;
        				case 3:
        					this.env.getTunnel().open();
        					this.env.getTunnel().write('<html><body></body></html>');
        					this.env.getTunnel().close();
        					this.env.getTunnel().parentWindow._cometObject = this;
        					this.env.getTunnel().body.innerHTML = '<iframe src="{0}"></iframe>'.format(this.env.getUrl());
        			}
        		}
            };
            
    		this.env.setName("midcomComet");
    		this.env.setType($.browser.ie ? 3 : $.browser.opera ? 2 : 1);
    		this.env.setTunnel(
    			this.env.getType() == 3 ? new ActiveXObject("htmlfile"):
    			this.env.getType() == 2 ? document.createElement("event-source"):
    			new $.midcom.helpers.cometclass._xhr()
    		);
        }
    };
    $.midcom.helpers.cometclass = {
    };
    $.extend($.midcom.helpers.cometclass, {
        listen: function() {
            return new $.midcom.helpers.cometclass._listener();
        },
        send: function() {
            return new $.midcom.helpers.cometclass._xhr();
        },
        _listener: function() {
            var inst = new $.midcom.helpers._cometclass._listener();
            inst.abort = inst.actions.abort;
            inst.send = inst.actions.send;
            
            return inst;
        },
        _xhr: function() {
            var inst = new $.midcom.helpers._cometclass._xhr();
            inst.abort = inst.actions.abort;
            inst.send = inst.actions.send;
            
            return inst;
        }
    });
    
})(jQuery);