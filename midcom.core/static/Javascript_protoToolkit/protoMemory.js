var protoMemory = Class.create();
protoMemory.prototype = {

    initialize: function()
    {
        this.debugON = false;

        this.hasMemSupport = false;
        this._supportCheck();

        var expdate = new Date();
        expdate = this.fixDate(expdate); // Correct for Mac date bug
        expdate.setTime (expdate.getTime() + (24 * 60 * 60 * 1000)); // 24 hrs from now

        this.current_domain = document.domain;
        this.current_path = "/";//null;//(location.pathname=="")?"/":location.pathname;
        var cookiename = arguments[0] || 'protoMemory';

        this.cookieSettings = { name: cookiename,
                                expires: expdate,
                                domain: this.current_domain,
                                path: this.current_path,
                                secure: false };

        //alert(document.cookie);
        if(document.cookie == "" || !this._cookieExists(this.cookieSettings.name)) {
            this.write("initialized",1,"Parameters");
        }
    },

    configure: function( parameters )
    {
        this.debugON = parameters.debug || false;

        var expdate = new Date ();
        expdate = this.fixDate(expdate); // Correct for Mac date bug

        if(parameters.expires) {
            expdate.setTime(expdate.getTime() + (parameters.expires * 1000));
        } else {
            expdate.setTime (expdate.getTime() + (3 * 60 * 1000)); // 24 hrs from now
        }

        var cookiename = (parameters.name) ? parameters.name : 'protoMemory';
        var domain = (parameters.domain) ? parameters.domain : document.domain;
        var path = (parameters.path) ? parameters.path : null; //(location.pathname=="")?"/":location.pathname;
        var secure = (parameters.secure) ? parameters.secure : false;

        this.cookieSettings = { name: cookiename,
                                expires: expdate,
                                domain: domain,
                                path: path,
                                secure: secure };
    },

    getConfig: function()
    {
        return this.cookieSettings;
    },

    hasSupport: function()
    {
        this.debug("mem support: " + this.hasMemSupport);
        return this.hasMemSupport;
    },

    read: function( p_key, p_section )
    {
        var section = (!isNull(p_section) && p_section != undefined)?p_section:"Data";
        this.debug("read, section: "+section);
        var rawdata = this.readSection( section );
        if(rawdata) {
            this.debug("read, rawdata: "+rawdata);
            var splittedraw = rawdata.split("|");
            this.debug("read, splittedraw: "+splittedraw);

            for(var i=0;i<splittedraw.length;i++) {
                var datarow = splittedraw[i];
                this.debug("datarow: "+datarow);
                if(p_key == datarow.split("#")[0]) {
                    this.debug("read, key '"+p_key+"' == "+datarow.split("#")[0]);
                    var value = datarow.split("#")[1];
                    var value = protoToolkit.parseJSON(value); //value.parseJSON();
                    return value;
                }
            }
        } else {
            return null;
        }
    },

    readSection: function( p_section, p_othersonly )
    {
        this.debug("readSection: "+p_section);
        var p_othersonly = (!isNull(p_othersonly) && p_othersonly != undefined)?p_othersonly:false;

        if(isNull(p_section) || p_section == undefined) {
            return "";
        }

        var memdata = this._readCookie();
        if(!memdata) {
            return "";
        }

        var parsed = memdata.split(":!:")
        this.debug("readSection, parsed: "+parsed);

        if(p_othersonly) {
            var sectionStr = "";
            for(var i=0;i<parsed.length;i++) {
                if(p_section != parsed[i].split("=")[0]) {
                    if(i!=parsed.length-1) {
                        sectionStr = sectionStr + parsed[i] + ":!:";
                    } else {
                        sectionStr = sectionStr + parsed[i];
                    }
                }
            }
            this.debug("readSection, sectionStr: "+sectionStr);
            return sectionStr;
        } else {
            for(var i=0;i<parsed.length;i++) {
                if(p_section == parsed[i].split("=")[0]) {
                    var valueStr = parsed[i].split("=")[1];
                    this.debug("readSection, valueStr: "+valueStr);
                    return valueStr;
                }
            }
        }
    },
    
    updateSection: function( p_section, p_key, p_value )
    {
        this.debug("updateSection: "+p_section);
        if(isNull(p_section) || p_section == undefined || p_key == undefined) {
            return null;
        }

        var sectiondata = this.readSection( p_section );

        if(sectiondata) {
            this.debug("updateSection, sectiondata: "+sectiondata);
            var sectiondata = sectiondata.split("|");
            var newsectiondata = new Array();
            this.debug("updateSection, sectiondata splitted: "+sectiondata);
            var sdlen = sectiondata.length;
            this.debug("updateSection, sectiondata length: "+sdlen);
            var keyUpdated = false;

            for(var i=0;i<sectiondata.length;i++) {
                var tmpkey = sectiondata[i].split("#")[0];
                var tmpval = sectiondata[i].split("#")[1];
                if(p_key == tmpkey) {
                    this.debug("updateSection, '"+tmpkey+"' old value: "+tmpval);
/*                     sectiondata[i].split("#")[1] = p_value;
                    debug("updateSection, new value: "+sectiondata[i].split("#")[1]); */
                    var tmparr = [ tmpkey, p_value ];
                    newsectiondata[i] = tmparr.join("#");
                    this.debug("updateSection, '"+tmpkey+"' new value: "+newsectiondata[i]);
                    keyUpdated = true;
                } else {
                    var tmparr = [ tmpkey, tmpval ];
                    newsectiondata[i] = tmparr.join("#");
                }
            }
            
            if(!keyUpdated) {
                var newdata = p_key + "#" + p_value;
                newsectiondata[sdlen] = newdata;
            }

            var newSectionData = newsectiondata.join("|");
            this.debug("updateSection, newSectionData: " + newSectionData );

            var otherSections = this.readSection( p_section, true );
            if(otherSections) {
                this._writeCookie(p_section + "=" + newSectionData+":!:"+otherSections);
            } else {
                this._writeCookie(p_section + "=" + newSectionData);
            }
        } else {
            this.debug("updateSection, create new");
            var sectionArr = new Array();

            //var data = new Array();
            //data[0] = "Username" + "#" + "debugger";
            //data[1] = "isAdmin" + "#" + "false";

            var newdata = new Array();
            newdata[0] = p_key + "#" + p_value;

            var sectionStr = p_section + "=" + newdata.join("|");
            sectionArr[0] = sectionStr;

            //var sectionStr2 = "Data=" + data.join("|");
            //sectionArr[1] = sectionStr2;

            var newSectionData = sectionStr;//sectionArr.join(":!:");
            this.debug("updateSection, newSectionData: "+newSectionData);

            var otherSections = this.readSection( p_section, true );
            if(otherSections) {
                this._writeCookie(newSectionData+":!:"+otherSections);
            } else {
                this._writeCookie(newSectionData);
            }
        }
    },

    sectionExists: function( p_section )
    {
        var memdata = this._readCookie();
        if(!memdata) {
            return false;
        }
        parsed = memdata.split("!")

        if(!isNull(p_section) && p_section != undefined) {
            for(var i=0;i<parsed.length;i++) {
                if(p_section == parsed[i].split("=")[0]) {
                    this.debug("section "+p_section+" exists");
                    return true;
                }
            }
        } else {
            this.debug("section "+p_section+" does not exists");
            return false;
        }
    },

    write: function( p_key, p_value, p_section )
    {
        var section = (!isNull(p_section) && p_section != undefined )?p_section:"Data";
        if((isNull(p_key) || p_key == undefined) || (isNull(p_value) || p_value == undefined)) {
            return null;
        }

        var value = p_value; // Pack array and make it as json string
        this.debug("write, value: "+value);
        //var rawdata = this.readSection( section );//this._readCookie();
        this.updateSection(section, p_key, value);

    },
    
    update: function( p_key, p_value, p_section )
    {
        if(isNull(p_key) || isNull(p_value)) {
            return 0;
        }
        
        var data = this.read( p_key, p_section );
    },

    remove: function()
    {
        this._removeCookie();
    },

    _parseInput: function()
    {
    },

    _parseOutput: function()
    {
    },

    _cookieExists: function( p_name )
    {
        var p_name = (!isNull(p_name) && p_name != undefined )?p_name:this.cookieSettings.name;

        this.debug("_cookieExists, cookie: "+document.cookie);
        var start = document.cookie.indexOf(p_name+"=");

        if((!start) && (p_name != document.cookie.substring(0,p_name.length))) {
            return false;
        }

        if(start == -1) {
            return false;
        }
        
        return true;
    },

    _readCookie: function( p_name )
    {
        var p_name = (!isNull(p_name) && p_name != undefined )?p_name:this.cookieSettings.name;

        var start = document.cookie.indexOf(p_name+"=");
        this.debug("readcookie, name: "+p_name);
        this.debug("readcookie, content: "+document.cookie);

        //debug("start: "+start);
        var len = start + p_name.length+1;
        //debug("len: "+len);

        if((!start) && (p_name != document.cookie.substring(0,p_name.length))) {
            this.debug("fail 1");
            return null;
        }

        if(start == -1) {
            this.debug("fail 2");
            return null;
        }

        var end = document.cookie.indexOf(";",len);
        end = (end == -1)?document.cookie.length:end;

        return this._normalize(document.cookie.substring(len,end));
    },

    _writeCookie: function( p_value, p_name, p_path, p_domain, p_secure, p_expires )
    {
        this.debug("_writeCookie, value: "+p_value);
        if(p_expires != undefined) {
            var expdate = new Date ();
            expdate = this.fixDate(expdate); // Correct for Mac date bug
            expdate.setTime (p_expires);
        }

        var cname = (!isNull(p_name) && p_name != undefined)?p_name:this.cookieSettings.name;

        if(!this._cookieExists(cname)) {
            var cpath = (p_path != undefined)?p_path:this.cookieSettings.path;
            var cdomain = (p_domain != undefined)?p_domain:this.cookieSettings.domain;
            var csecure = (p_secure != undefined)?p_secure:this.cookieSettings.secure;
            var cexpires = (p_expires != undefined)?true:false;
    
            this.debug("_writeCookie, cname: "+cname);
    
            var cookieStr = cname + "=" + this._serialize(p_value) +
            ( (cexpires) ? ";expires=" + expdate.toGMTString() : ";expires=" + this.cookieSettings.expires.toGMTString() ) +
            ( (cpath != null) ? ";path=" + cpath : "" ) +
            ( (cdomain) ? ";domain=" + cdomain : "") +
            ( (csecure) ? ";secure" : "");
    
            this.debug("_writeCookie, cookieStr: "+cookieStr);
            document.cookie = cookieStr;
            this.debug("_writeCookie, cookie: "+document.cookie);
        } else {
            this.debug("_writeCookie, exists cname: "+cname);

            var cpath = (p_path != undefined)?p_path:this.cookieSettings.path;
            var cdomain = (p_domain != undefined)?p_domain:this.cookieSettings.domain;
            var csecure = (p_secure != undefined)?p_secure:this.cookieSettings.secure;
            var cexpires = (p_expires != undefined)?true:false

            var cookieStr = cname + "=" + this._serialize(p_value) +
            ( (cexpires) ? ";expires=" + expdate.toGMTString() : ";expires=" + this.cookieSettings.expires.toGMTString() ) +
            ( (cpath != null) ? ";path=" + cpath : "" ) +
            ( (cdomain) ? ";domain=" + cdomain : "") +
            ( (csecure) ? ";secure" : "");
    
            this.debug("_writeCookie, cookieStr: "+cookieStr);
            document.cookie = cookieStr;
            this.debug("_writeCookie, cookie: "+document.cookie);
        }
    },

    _removeCookie: function( p_name, p_path, p_domain )
    {
        var cname = (!isNull(p_name) && p_name != undefined)?p_name:this.cookieSettings.name;
        var cpath = (p_path != undefined)?p_path:this.cookieSettings.path;
        var cdomain = (p_domain != undefined)?p_domain:this.cookieSettings.domain;

//        if(this._readCookie(cname)) {
            this.debug("remove: "+cname);
            document.cookie = cname + "=" +
            ";expires=Thu, 01-Jan-70 00:00:01 GMT" +
            ( (cpath) ? ";path=" + cpath : "") +
            ( (cdomain) ? ";domain=" + cdomain : "");
//        }
    },

    _supportCheck: function()
    {
        var cookieEnabled = (navigator.cookieEnabled)?true:false;

        if(typeof navigator.cookieEnabled==undefined && !cookieEnabled) {

            this._writeCookie("", "protoMemory_check", "/test", null);
            //document.cookie = "protoMemory_check";

            cookieEnabled=this._cookieExists("protoMemory_check");//(document.cookie.indexOf("protoMemory_check")!=-1)?true:false;
            this._removeCookie( "protoMemory_check", "/test", null );
        }

        if(cookieEnabled) {
            this.hasMemSupport = true;
        }
    },
    
    _normalize: function( p_string )
    {
        return unescape(p_string);
    },
    
    _serialize: function( p_string )
    {
        return escape(p_string);
    },

    fixDate: function( date )
    {
        var base = new Date(0);
        var skew = base.getTime();
        if(skew > 0)  // Except on the Mac - ahead of its time
            date.setTime (date.getTime() - skew);
        
        return date;
    },
    
    debug: function(p_msg, p_append, p_type)
    {
        if(p_append == undefined) {
            p_append = false;
        }

        if(this.debugON) {
            debug(p_msg, p_append);
        }
    }

};