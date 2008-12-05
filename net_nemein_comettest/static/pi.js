(function(scope){
/*	
	# 	PI JAVASCRIPT LIBRARY (COMET&AJAX VERSION)
	#	Azer Koçulu <http://azer.kodfabrik.com>
	#	http://pi-js.googlecode.com
	#	11.03.2008 21:47
*/	
	
	scope.pi = Object(3.14159265358979323846), scope=scope.pi;
	scope.env = {
		ie: /MSIE/i.test(navigator.userAgent),
		ie6: /MSIE 6/i.test(navigator.userAgent),
		ie7: /MSIE 7/i.test(navigator.userAgent),
		ie8: /MSIE 8/i.test(navigator.userAgent),
		firefox: /Firefox/i.test(navigator.userAgent),
		opera: /Opera/i.test(navigator.userAgent),
		webkit: /Webkit/i.test(navigator.userAgent)
	};
	
	scope.interface = function(){
		this.constants = {};
		this.environment = {};
		this.events = {};
		this.body = {};
	}
	scope.interface.prototype = {
		"constant":{},
		"constructor":function(){},
		"environment":{},
		"event":{},
		"body":{},
		"extend":function(OBJECT){
			var body = Object.Clone(OBJECT.prototype);
			Object.Concat(this.body,body);
			Object.Concat(this.constants,body.constants);
			Object.Concat(this.environment,body.environment);
			Object.Concat(this.event,body.event);
		},
		"build":function(){
			var link = this;
			
			var fn = function(){
				this.constants = Object.Clone(this.constants);
				this.environment = Object.Clone(this.environment);
			
				this.environment._parent_ = this;
				this.event._parent_ = this;

				link.constructor.apply(this,arguments);
			};
			
			for(var item in this.environment){
				if(item.substring(0,1)!="_")continue;
				var name = item, title = name.substring(1,2).toUpperCase()+name.substring(2);
				
				if(Boolean(this.environment["get"+title])==false)
					this.environment["get"+title] = new Function("return this."+name);
				if(Boolean(this.environment["set"+title])==false)
					this.environment["set"+title] = new Function("VALUE","this."+name+" = VALUE;");
			}
			
			this.body.constants = this.constants;
			this.body.environment = this.environment;
			this.body.event = this.event;
			fn.prototype = this.body;
			return fn;
		}
	};
	
	scope.comet = new scope.interface;
	scope.comet.constructor = function(){
		this.environment.setName("piComet");
		this.environment.setType(scope.env.ie?3:scope.env.opera?2:1);
		this.environment.setTunnel(
			this.environment.getType()==3?new ActiveXObject("htmlfile"):
			this.environment.getType()==2?document.createElement("event-source"):
			new scope.xhr
		);
	}
	scope.comet.body = {
		"abort":function(){
			switch(this.environment.getType()){
				case 1:
					this.environment.getTunnel().abort();
					break;
				case 2:
					document.body.removeChild(this.environment.getTunnel());
					break;
				case 3:
					this.environment.getTunnel().body.innerHTML="<iframe src='about:blank'></iframe>";
			}
		},
		"send":function(){
			switch(this.environment.getType()){
				case 1:
					this.environment.getTunnel().send();
					break;
				case 2:
					document.body.appendChild(this.environment.getTunnel());
					this.environment.getTunnel().addEventListener(this.environment.getName(),this.event.change,false);
					break;
				case 3:
					this.environment.getTunnel().open();
					this.environment.getTunnel().write("<html><body></body></html>");
					this.environment.getTunnel().close();
					this.environment.getTunnel().parentWindow._cometObject = this;
					this.environment.getTunnel().body.innerHTML="<iframe src='{0}'></iframe>".format(this.environment.getUrl());
			}
		}
	}
	scope.comet.environment = {
		"_name":"", "_tunnel":null, "_type":"", "_url":"",
		"setTunnel":function(VALUE){
			if(this.getType()==1){
				VALUE.environment.addData("cometType","1");
				VALUE.environment.addCallback(this._parent_.event.change,3);
				VALUE.environment.setCache(false);
			}
			
			VALUE._cometApi_ = this._parent_;
			this._tunnel = VALUE;
		},
		"setUrl":function(VALUE){
			if(this.getType()>1)
			{
				VALUE = "{0}{1}cometType={2}&cometName={3}".format(VALUE,VALUE.search("\\?")>-1?"&":"?",this.getType(),this.getName());
				
				if(this.getType()==2)
					this.getTunnel().setAttribute("src",VALUE);
			} else
				this.getTunnel().environment.setUrl(VALUE);
			
			this._url = VALUE;
		}
	}
	scope.comet.event = {
		"change":function(){
			var response = null;
			if(this._cometApi_.environment.getType()==2)
				response = arguments[0].data
			else {
				response = this.environment.getApi().responseText.split("<end />");
				response = response[response.length-1];
			}
   			this._cometApi_.event.push(response);
		},
		"push":function(TEXT){}
	}
	scope.comet = scope.comet.build();

	scope.xhr = new scope.interface;
	scope.xhr.constructor = function(){
		var api = window.XMLHttpRequest?XMLHttpRequest:ActiveXObject("Microsoft.XMLHTTP");
		this.environment.setApi(
			new api()
		);
		this.environment.getApi().onreadystatechange=this.event.readystatechange.curry(this);
		this.environment.getApi().onerror=this.event.error.curry(this);
	}
	scope.xhr.body = {
		"abort":function(){
			this.environment.getApi().abort();
		},
		"send":function(){
			var url = this.environment.getUrl(), data = this.environment.getData(),dataUrl = ""; 

			for (key in data)
				dataUrl += "{0}={1}&".format(key, data[key]);
				
			if (this.environment.getType()=="GET"&&url.search("\\?") == -1)
				url += "?{0}".format(dataUrl);
			
			this.environment.getApi().open(this.environment.getType(),url,this.environment.getAsync());
			
			for(key in this.environment.getHeader())
				this.environment.getApi().setRequestHeader(key,this.environment.getHeader()[key]);

			this.environment.getApi().send(this.environment.getType()=="GET"?"":dataUrl);
		}
	},
	scope.xhr.environment = {
		"_async":true, "_api":null, "_cache":true, "_callback":[], "_channel":null, "_data":{}, "_header":{}, "_mimeType":null, "_multipart":false, "_type":"GET", "_timeout":0, "_url":"",
		"addCallback": function(FUNCTION,READYSTATE_VALUE,STATUS_VALUE){
			this.getCallback().push({ "fn":FUNCTION, "readyState":READYSTATE_VALUE||4, "status":STATUS_VALUE||200  });
		},
		"addHeader": function(KEY,VALUE){
			this.getHeader()[KEY] = VALUE;
		},
		"addData": function(KEY,VALUE){
			this.getData()[KEY] = VALUE;
		},
		"setCache":function(VALUE){
			if(VALUE==false){
				this.addData("forceCache",Math.round(Math.random()*10000));
			}
			this._cache = VALUE;
		},
		"setType": function(VALUE){
			if(VALUE=="POST"){
				this.addHeader("Content-Type","application/x-www-form-urlencoded");
			}
			this._type = VALUE;
		}
	}
	scope.xhr.event = {
		"readystatechange":function(){
			var readyState = this.environment.getApi().readyState
			var callback=this.environment.getCallback();

			for (var i = 0; i < callback.length; i++) {
				if (callback[i].readyState==readyState) 
					 callback[i].fn.apply(this);
			}
		},
		"error":function(){
		}
	}
	scope.xhr = scope.xhr.build();
	
	// additions to stractures
	Array.prototype.clone = function(){
		var tmp = [];
		Array.prototype.push.apply(tmp,this);
		for(var i=0; i<tmp.length; i++)
	    	if(tmp[i] instanceof Array)
	    		tmp[i] = tmp[i].clone()
	    return tmp;
	};
	
	Function.prototype.curry = function(scope){
		var fn = this;
		var scope = scope||window;
		var args = Array.prototype.slice.call(arguments,1);
		return function(){ 
			args.push.apply(args,Array.prototype.slice.call(arguments,0));
			return fn.apply(scope,args); 
		};
	};
	
	Object.Clone = function(OBJECT){
		var tmp = {};
		for(member in OBJECT)
		{
			tmp[member] =	(typeof OBJECT[member]=="object")?
							(	OBJECT[member] instanceof Array?
								OBJECT[member].clone():
					 	  		Object.Clone(OBJECT[member])
							):OBJECT[member];
		}
		return tmp;
	}

	String.prototype.format = function(){
		var values = arguments;
		return this.replace(/\{(\d)\}/g,function(){
			return values[arguments[1]];
		})
	};
	
})(window);
