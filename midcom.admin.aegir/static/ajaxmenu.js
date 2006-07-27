
/* global request object

*/
var req;
var errtxt;
var currently_open;
var openNodes = new Array();
/**
* Utilityfunctions used by the edit area.
*/
function hidediv(div) {
	var obj;
	if (document.getElementById) { // DOM3 = IE5, NS6
		obj = document.getElementById(div);
		obj.style.display = 'none';
	} else {
		if (document.layers) { // Netscape 4
			document.thisdiv.display = 'none';
		} else { // IE 4
			document.all.thisdiv.style.display = 'none';
		}
	}
}

function showdiv(evt) {
	evt = (evt) ? evt: ( (window.event) ? event : null);
	var obj;
	var div,old,old_a;
	var dbg ; 
	dbg = document.getElementById("aegir_msg");
	if (evt) {
	
		obj = (evt.target) ? evt.target : evt.srcElement;
		
		if (document.getElementById) { // DOM3 = IE5, NS6
		
			div = document.getElementById("midcom_datamanager_fieldgroup_" + obj.getAttribute("id") );
			if (div.id == currently_open) {
				return false;
			}
			old = document.getElementById( currently_open );
			old_a = document.getElementById( old.id.substring(30, old.id.length));
			old.style.display = 'none';

			old_a.className= '';
			div.style.display = 'block';
			obj.className = 'enabled';
			currently_open = div.id;

			
			var i, pos ;
			var textareas;

			
			textareas = document.getElementsByTagName('TEXTAREA');
			for (i = 0; i < textareas.length; i++) {
				if (textareas[i].parentNode.parentNode.parentNode.id == currently_open) {
								pos = getElementPosition(textareas[i].id);
				textareas[i].style.height = window.innerHeight -pos.top -50 + "px";
				textareas[i].style.width = window.innerWidth -pos.left -50 + "px";
				textareas[i].style.border = "solid 1px black";

				}
			}
			
			
			for (i = 0 ;i <  xinha_editors.length ;i++) {
				dbg.innerHTML +=  xinha_editors[i]._textArea.id;
					xinha_editors[i].sizeEditor("100%", window.innerHeight -100 + "px");
					xinha_editors[i].activateEditor();
			}
			for (i = 0; i < textareas.length; i++) {
				if (textareas[i].parentNode.parentNode.parentNode.id == currently_open) {
					pos = getElementPosition(textareas[i].parentNode.parentNode.Id);
					textareas[i].style.height = window.innerHeight -pos.top -100 + "px";
					textareas[i].style.width = "100%";
				}
			}
			
		}
		
    } else {
    	document.write("<p>No event?");
    }

	return false;
}

/* array over nodes we have accessed, 
*/
var openNodes = Array();
/* Function for opening and closing nodes that are fully defined 
*/
function openCloseUl (evt) {
	evt = (evt) ? evt: ( (window.event) ? event : null);
	var obj;
	if (evt) {
		obj = (evt.target) ? evt.target : evt.srcElement;
	    obj.parentNode.className = (obj.parentNode.className=='nav_openFolder') ? "nav_closedFolder" : "nav_openFolder";
    	obj.className = (obj.className=='nav_openFolder') ? "nav_closedFolder" : "nav_openFolder";
    } else {
    	document.write("<p>No event?");
    }
    return false;
    
}
/*
 	Function for loading the menu subtree that has not been loaded yet.
*/
function getSubElements( strURL, liID,evt) {
	evt = (evt) ? evt: ( (window.event) ? event : null);
	var obj, dbg, obj_a;
	dbg = document.getElementById("aegir_msg");
	if (evt) {
		obj = (evt.target) ? evt.target : evt.srcElement;	
			
	    if (!openNodes[ obj.id ]) {
    		openNodes[ obj.id ] = 1;
			loadXMLDoc(strURL,obj.parentNode.id);
		} else {
		    obj.parentNode.className = (obj.parentNode.className=='nav_openFolder') ? "nav_closedFolder" : "nav_openFolder";
    		obj.className = (obj.className=='nav_openFolder') ? "nav_closedFolder" : "nav_openFolder";
		}	
	} else {
		dbg.innerHTML ="No evt";
	}
		
}
	
function loadXMLDoc(url,objId) {
	req = false;
    // branch for native XMLHttpRequest object
    if(window.XMLHttpRequest) {
    	try {
			req = new XMLHttpRequest();
        } catch(e) {
			req = false;
          		errtxt = e;			
        }
    // branch for IE/Windows ActiveX version
    } else if(window.ActiveXObject) {
       	try {
        	req = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		req = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		req = false;
          		errtxt = e;
        	}
		}
    }
 //   dbg = document.getElementById("aegir_msg");
    obj = document.getElementById(objId);
	if(req) {
		req.onreadystatechange = function handleCallback() {
				var obj_a;
			    // only if req shows "loaded"
			    if (req.readyState == 4) {
			        // only if "OK"
			        if (req.status == 200) {
			        	obj_a = document.getElementById("a" + obj.id);

        			    if (req.responseText.length == 38 ) // == the xml header.
        			    {
	        			    obj_a.className =  "nav_folder";
						    obj.className =  "nav_folder";
						    obj.removeChild(obj_a);
						    obj.innerHTML += "(empty)";
        			    } else {
        			    
						    obj_a.className =  "nav_openFolder";
						    obj.className =  "nav_openFolder";     
						    obj.innerHTML += req.responseText;
						}
						openNodes[obj.id] = 1;
			        } else {
			            obj.innerHTML += "There was a problem retrieving the XML data:\n" +
			                req.statusText;
			        }
			    }
		};
	    dbg = document.getElementById('aegir_msg');
		dbg.innerHTML = "<p><a href='" + url + "'>" + url +  "</a></p>";
		req.open("GET", url, true);
//		req.overrideMimeType('text/xml');
		req.send(null);
	}
}

/**
 getElementPosition this function gives you the correct left and top for a nonpositioned element.
 from: JS & DHTML Cookbook, Orielly.
*/
function getElementPosition(elemID) {
    var offsetTrail = document.getElementById(elemID);
    var offsetLeft = 0;
    var offsetTop = 0;
    while (offsetTrail) {
        offsetLeft += offsetTrail.offsetLeft;
        offsetTop += offsetTrail.offsetTop;
        offsetTrail = offsetTrail.offsetParent;
    }
    if (navigator.userAgent.indexOf("Mac") != -1 &&
        typeof document.body.leftMargin != "undefined") {
        offsetLeft += document.body.leftMargin;
        offsetTop += document.body.topMargin;
    }
    return {left:offsetLeft, top:offsetTop};
}
