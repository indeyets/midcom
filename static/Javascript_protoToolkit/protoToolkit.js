/* <![CDATA[ */

function protoToolkit() {
	this.version = '1.0.3';
}

protoToolkit.prototype.req = function(library_name) {
	document.write('<script type="text/javascript" src="'+library_name+'"></script>');
}
protoToolkit.prototype.start = function() {
	if((typeof Prototype=='undefined') ||
      parseFloat(Prototype.Version.split(".")[0] + "." +
                 Prototype.Version.split(".")[1]) < 1.4)
      throw("protoToolkit requires the Prototype JavaScript framework >= 1.4.0");

      $A(document.getElementsByTagName("script")).findAll( function(i) {
          if( i.src.match(/protoToolkit\.js(\?.*)?$/) ) {
              var path = i.src.replace(/protoToolkit\.js(\?.*)?$/,'');
              var incs = i.src.match(/\?.*load=([a-zA-Z,]*)/);
              (incs ? incs[1] : '').split(',').each(
                   function(include) { protoToolkit.req(path+include+'.js') } );
          }
      });
}


protoToolkit.prototype.parseJSON = function (json_str) {
	try {
        return !(/[^,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/.test(
                json_str.replace(/"(\\.|[^"\\])*"/g, ''))) &&
            eval('(' + json_str + ')');
    } catch (e) {
        return false;
    }
}
protoToolkit.prototype.toJSON = function (item,item_type) {
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
                    v = protoToolkit.prototype.toJSON(v);
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
                        f = s[typeof v];
                        if (f) {
                            v = f(v);
                            if (typeof v == 'string') {
                                if (b) {
                                    a[a.length] = ',';
                                }
                                a.push(s.str(i), ':', v);
                                b = true;
                            }
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

    // Array.prototype.toJSON = function () {
    //         return s.arr(this);
    //     };

	var itemtype = item_type || typeof item;
	switch(itemtype) {
		case "array":
		  return s.arr(item);
		  break;
		case "object":
		  return s.obj(item);
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
		  throw("Unknown type for protoToolkit.toJSON");
		}

}

var protoToolkit = new protoToolkit();
protoToolkit.start();

var scrollX = 0;
var scrollY = 0;

function getScrollXY() {
	var scrOfX = 0, scrOfY = 0;

	if(typeof(window.pageYOffset) == 'number') {
		//Netscape compliant
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;

	} else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
		//DOM compliant
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;

	} else if (document.documentElement &&
		(document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
		//IE6 standards compliant mode
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	}

	return [scrOfX, scrOfY];
}

function getViewportSize() {
	var myWidth = 0, myHeight = 0;

	if (typeof(window.innerWidth ) == 'number') {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;

	} else if (document.documentElement &&
		(document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;

	} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}

	return [myWidth, myHeight];
}

function findPosX(obj) {
	var curleft = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	} else if (obj.x) {
		curleft += obj.x;
	}
	return curleft;
}

function findPosY(obj) {
	var curtop = 0;
	if (obj.offsetParent) {
		while (obj.offsetParent) {
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	} else if (obj.y) {
		curtop += obj.y;
	}
	return curtop;
}

function isNull(a) {
    return typeof a == 'object' && !a;
}

Array.prototype.inArray = function (value)
// Returns true if the passed value is found in the
// array.  Returns false if it is not.
{
    var i;
    for (i=0; i < this.length; i++) {
        // Matches identical (===), not just similar (==).
        if (this[i] === value) {
            return true;
        }
    }
    return false;
};

/*
 * http://wiki.script.aculo.us/scriptaculous/show/Effect.KeepFixed
 * Following code snippet by: Michel Bohn
 */

/*    Position.Window = {
        //extended prototypes position to return
        //the scrolled window deltas
        getDeltas: function() {
            var deltaX =  getScrollXY[0];
            var deltaY =  getScrollXY[1];
            return [deltaX, deltaY];
        },
        //extended prototypes position to
        //return working window's size, 
        //copied this code from the 
        size: function() {
            var winWidth, winHeight, d=document;
            if (typeof window.innerWidth!='undefined') {
                winWidth = window.innerWidth;
                winHeight = window.innerHeight;
            } else {
                if (d.documentElement && typeof d.documentElement.clientWidth!='undefined' && d.documentElement.clientWidth!=0) {
                    winWidth = d.documentElement.clientWidth
                    winHeight = d.documentElement.clientHeight
                } else {
                    if (d.body && typeof d.body.clientWidth!='undefined') {
                        winWidth = d.body.clientWidth
                        winHeight = d.body.clientHeight
                    }
                }
            }
            return [winWidth, winHeight];
        }
    }
    //my own custom effect that basically
    //calls the Effect.Move Scriptaculous
    //effect with the correct window offsets
    Effect.KeepFixed = function(element, offsetx, offsety) {
        var _scroll = Position.Window.getDeltas();
        var _window = Position.Window.size();
        var elementDimensions = Element.getDimensions(element);
        var eWidth = elementDimensions.width;
        var eHeight = elementDimensions.height;
        var moveX = _window[0] - eWidth + _scroll[0] + offsetx;
        var moveY = _window[1] - eHeight + _scroll[1] + offsety;
        return new Effect.Move(element, { x: moveX, y: moveY, mode: 'absolute' });
    } */



/*
  prototype merging functions
*/

function copyPrototype(descendant, parent) {
    var sConstructor = parent.toString();
    var aMatch = sConstructor.match( /\s*function (.*)\(/ );
    if ( aMatch != null ) { descendant.prototype[aMatch[1]] = parent; }
    for (var m in parent.prototype) {
        descendant.prototype[m] = parent.prototype[m];
    }
};

/* ]]> */
