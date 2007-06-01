// Copy the default style element to the editor
function copy_to_edit(id, string)
{
    var editor = document.getElementById(id);
    if (!editor)
    {
        return;
    }
    
    editor.innerHTML += string;
}

// Toggles the visibility of the default style element
function toggle_visibility(object)
{
    var elements = object.getElementsByTagName('div');
    
    for (i = 0; i < elements.length; i++)
    {
        if (elements[i].className != 'content')
        {
            continue;
        }
        
        if (elements[i].style.display == 'none')
        {
            // Switch the legend sign from expanding (+) reducing (-)
            object.getElementsByTagName('legend')[0].className = 'visible';
            
            // Show the default style element
            new Effect.BlindDown(elements[i]);
        }
        else
        {
            // Switch the legend sign from reducing (-) to expanding (+)
            object.getElementsByTagName('legend')[0].className = 'hidden';
            
            // Hide the default style element
            new Effect.BlindUp(elements[i]);
        }
    }
}