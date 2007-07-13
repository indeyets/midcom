/**
 * text selection handling - jQuery plugin
 */

/**
 * Enables/Disables text selection, saves selected text
 *   
 * @example jQuery('p').textSelection('disable'); / jQuery('#selectable-area').textSelection('enable'); / onclick="jQuery('p').textSelection('save');"
 * @cat plugin
 * @type jQuery 
 *
 */

jQuery.fn.textSelection = function(action, settings) {
    settings = jQuery.extend({
    }, settings);
    
    return this.each(function() {
        
        var element = this;
        var selectedText = "";
        
        if (action == 'disable')
        {
            disable();
        }
        else if(action == 'enable')
        {
            enable();
        }
        else if(action == 'save')
        {
            return save_selection();
        }
        
        function disable()
        {   
            if (typeof(element.onselectstart) != "undefined")
            {
                element.onselectstart = function() { return false; };
            }
            else
            {
                element.onmousedown = function() { return false; };
            }
        }

        function enable()
        {   
            if (typeof(element.onselectstart) != "undefined")
            {
                element.onselectstart = function() { return true; };
            }
            else
            {
                element.onmousedown = function() { return true; };
            }           
        }
        
        function save_selection()
        {
            if(document.selection != undefined)
            {
                selectedText = document.selection;
                if (selectedText.type == 'Text')
                {
                    var newRange = selectedText.createRange();
                    console.log("selected text was: "+newRange.text);
                    return newRange.text;
                }
            }
        }
        
    });
    
};