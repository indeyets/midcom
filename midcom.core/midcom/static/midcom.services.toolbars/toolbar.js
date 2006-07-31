/* <![CDATA[ */

var scrollX = 0;
var scrollY = 0;

var mouseX = 0;
var mouseY = 0;
var mouseMoved = false;

var mouseGesture_started = 0;
var mouseGesture_timeout = false;



function listenmouse(event) {
    mouseMoved = true;
    try {
        mouseX = Event.pointerX(event);
        mouseY = Event.pointerY(event);
    } catch(e) {
        //alert("error");
    }

/*     var wCnt = openedWindows.length;
    for(var i=0;i<wCnt;i++) {
        alert(openedWindows[i]);
        if(openedWindows[i] != '') {
            sideWindow_place( openedWindows[i], 'left' );
        }
    } */
    
    if(mouseY <= 10) {
        var tb_gesture = function(resp){timed_toolbar_toggle();}
        if(mouseGesture_started == 0) {
            mouseGesture_started = new Date();
            mouseGesture_timeout = setTimeout(tb_gesture, 500);
        }
    }
    
}
Event.observe(document, 'mousemove', listenmouse);

function detectspecialkeys(e){
    var evtobj= window.event ? window.event : e
    if (evtobj.altKey) {
        //alert("you pressed Alt and " + evtobj.keyCode + " key");
        toolbar_toggle( 'mfa-main-toolbar' );
    }

    if(evtobj.keyCode == evtobj.DOM_VK_F3 || evtobj.keyCode == evtobj.F03) {
        toolbar_toggle( 'mfa-main-toolbar' );
        evtobj.stopPropagation();
        evtobj.preventDefault();
    }
}
document.onkeypress = detectspecialkeys;

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

/* ]]> */


/* <![CDATA[ */

function midcom_services_toolbars_toolbar_root_toggle(element, display)
{
    list = element.getElementsByTagName('ul');
    
    if (!list)
    {
        return;
    }
    
    for (i = 0; i < list.length; i++)
    {
        list[i].style.display = display;
    }
}

var openedToolbars = new Array();

function toolbar_toggle( toolbarName )
{
        var toolbarElem = document.getElementById(toolbarName);
        
        if (   !toolbarElem
            || toolbarElem == false 
            || isNull(toolbarName)) 
        {
            //toolbar_create( toolbarName );
            return;
        }

        if(openedToolbars[toolbarName] == undefined || openedToolbars[toolbarName] == 0) {
            toolbar_place( toolbarName );
            toolbar_show( toolbarName );
            openedToolbars[toolbarName] = 1;
	} else if(openedToolbars[toolbarName] == 1) {
            toolbar_hide( toolbarName );
            openedToolbars[toolbarName] = 0;
	}
        if(mouseGesture_timeout!=false) {
            clearTimeout(mouseGesture_timeout);
            mouseGesture_started = 0;
        }
}

function toolbar_hide( toolbarName )
{
    var toolbarElem = document.getElementById(toolbarName);
    if(toolbarElem != false) {
        //if(toolbar_timeout) clearTimeout(toolbar_timeout);
    
        new Effect.Fade(toolbarName,{duration:0.50,queue:'end'});
        //toolbar_lastclosed = new Date();
    }
}

function toolbar_show( toolbarName )
{
    new Effect.Appear(toolbarName,{duration:0.50,queue:'end'});
}

function toolbar_cleardim( toolbarName )
{
    new Effect.Opacity(toolbarName,{duration:0.25,to:1.00});
}
function toolbar_dim( toolbarName )
{
    new Effect.Opacity(toolbarName,{duration:0.25,to:0.50});
}

function toolbar_place( toolbarName )
{
    var toolbarElem = document.getElementById(toolbarName);

    var tbY = 0;
    var tbX = 0;
    var eW = 300;
    var topPadding = 10;
    var middle = getViewportSize()[0]/2 + getScrollXY()[0] - eW/2;

    var ow = toolbarElem.offsetWidth;
    var oh = toolbarElem.offsetHeight;

    tbY = getScrollXY()[1] + oh + topPadding;
    tbX = middle;

    new Draggable(toolbarName,{snap:false,revert:false});

    if (window.navigator.userAgent.indexOf('MSIE'))
    {
        // No 'fixed' support in IE
        toolbarElem.style.position = 'absolute';
    }
    else
    {
        toolbarElem.style.position = 'fixed';
    }
    toolbarElem.style.top = tbY + "px";
    toolbarElem.style.left = tbX + "px";
}

function timed_toolbar_toggle()
{
    /*
    Remove the mouse gesture until it works in a more satisfying fashion
    if(mouseY <= 10) {
        for(var key in openedToolbars) {
            if(key != '') {
                toolbar_toggle( key );
            }
        }
    }
    */
    mouseGesture_started = 0;
}


function do_onload()
{

    toolbar_toggle( 'midcom_services_toolbars_toolbar' );
}

if(window.addEventListener)
    window.addEventListener("load", do_onload, false);
else if (window.attachEvent)
    window.attachEvent("onload", do_onload);
else if (document.getElementById)
    window.onload=do_onload;
/* ]]> */
