/**
* EasyDrag 1.3 - Drag & Drop jQuery Plug-in
*
* For usage instructions please visit http://fromvega.com
*
* Copyright (c) 2007 fromvega
*/

(function(jQuery){

	// to track if the mouse button is pressed
	var isMouseDown    = false;

	// to track the current element being dragged
	var currentElement = null;

	// callback holders
	var dropCallbacks = {};
	var dragCallbacks = {};

	// global position records
	var lastMouseX;
	var lastMouseY;
	var lastElemTop;
	var lastElemLeft;

	// returns the mouse (cursor) current position
	jQuery.getMousePosition = function(e){
		var posx = 0;
		var posy = 0;

		if (!e) var e = window.event;

		if (e.pageX || e.pageY) {
			posx = e.pageX;
			posy = e.pageY;
		}
		else if (e.clientX || e.clientY) {
			posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
			posy = e.clientY + document.body.scrollTop  + document.documentElement.scrollTop;
		}

		return { 'x': posx, 'y': posy };
	}

	// updates the position of the current element being dragged
	jQuery.updatePosition = function(e) {
		var pos = jQuery.getMousePosition(e);

		var spanX = (pos.x - lastMouseX);
		var spanY = (pos.y - lastMouseY);

		jQuery(currentElement).css("top",  (lastElemTop + spanY));
		jQuery(currentElement).css("left", (lastElemLeft + spanX));
	}

	// when the mouse is moved while the mouse button is pressed
	jQuery(document).mousemove(function(e){
		if(isMouseDown){
			// update the position and call the registered function
			jQuery.updatePosition(e);
			if(dragCallbacks[currentElement.id] != undefined){
				dragCallbacks[currentElement.id](e);
			}

			return false;
		}
	});

	// when the mouse button is released
	jQuery(document).mouseup(function(e){
		if(isMouseDown){
			isMouseDown = false;
			if(dropCallbacks[currentElement.id] != undefined){
				dropCallbacks[currentElement.id](e);
			}

			return false;
		}
	});

	// register the function to be called while an element is being dragged
	jQuery.fn.ondrag = function(callback){
		return this.each(function(){
			dragCallbacks[this.id] = callback;
		});
	}

	// register the function to be called when an element is dropped
	jQuery.fn.ondrop = function(callback){
		return this.each(function(){
			dropCallbacks[this.id] = callback;
		});
	}

	// set an element as draggable - allowBubbling enables/disables event bubbling
	jQuery.fn.easydrag = function(allowBubbling){

		return this.each(function(){

			// if no id is defined assign a unique one
			if(undefined == this.id) this.id = 'easydrag'+time();

			// change the mouse pointer
			jQuery(this).css("cursor", "move");

			// when an element receives a mouse press
			jQuery(this).mousedown(function(e){			

				// set it as absolute positioned
				jQuery(this).css("position", "absolute");

				// set z-index
				jQuery(this).css("z-index", "10000");

				// update track variables
				isMouseDown    = true;
				currentElement = this;

				// retrieve positioning properties
				var pos    = jQuery.getMousePosition(e);
				lastMouseX = pos.x;
				lastMouseY = pos.y;

				lastElemTop  = this.offsetTop;
				lastElemLeft = this.offsetLeft;

				jQuery.updatePosition(e);

				return allowBubbling ? true : false;
			});
		});
	}

})(jQuery);